define([
        'knockout', 'text!./AirportInfo.html', 'TheMap', 'jquery', 'WaypointDialog'
], function(ko, htmlString, map, jquery, WaypointDialog ) {

        var waypointDialog = new WaypointDialog();
 
        var proceduresLayer = L.layerGroup().addTo( map );

        var WaypointViewModel = function( props ) {
          var self = this;
          props = props || {};

          self.name = ko.observable( props.name );
          self.type = ko.observable( props.type );
          self.position = ko.observable( props.latlng );
          if( props.lat && props.lng ) {
            self.position( [ props.lat, props.lng ] );
          }

          self.clickWaypoint = function() {
            waypointDialog.show( {
              name: self.name(),
              type: self.type(),
              latlng: self.position(),
            }, function( props ) {
              jquery.ajax({
                type: "post",
                dataType: "json",
                url: '/svc/getapt',
                data: JSON.stringify({
                    'command' : 'updateWaypoint',
                    'id_token' : window.id_token || 0,
                    'id': self.id,
                    'name': props.name,
                    'type': props.type,
                    'lat': props.latlng[0],
                    'lng': props.latlng[1],
                }),
              })
              .done(function() {
                self.name( props.name );
                self.type( props.type );
                self.position( props.latlng );
              })
              .fail(function() {
                  console.log('failed to save waypoint data');
              });
            });
          }
        }

        var ProcedureViewModel = function( props ) {
          var self = this;
          var polyline = L.polyline([]);

          props = props || {
            id: -1,
            name: 'unnamed procedure',
            runways: 'All',
            type: 'Star',
          }

          self.id = props.id;
          self.name = ko.observable(props.name);
          self.runways = ko.observable(props.runways);
          self.type = ko.observable(props.type);
          self.waypoints = ko.observableArray([]);
          self.expanded = ko.observable(false);

          self.waypoints.subscribe( function(newValue) {
            var latlngs = [];
            newValue.forEach( function(wpt) {
              latlngs.push( wpt.position() );
            });
            polyline.setLatLngs( latlngs );
          });

          self.expanded.subscribe( function(newValue) {
            if( newValue ) {
              polyline.addTo( map );
              if( polyline.getLatLngs().length > 1 )
                map.fitBounds(polyline.getBounds());
            } else map.removeLayer( polyline );
          });

          props.waypoints.forEach( function(wpt) {
            self.waypoints.push( new WaypointViewModel(wpt) );
          });

          self.clickProcedure = function( val, evt ) {
            self.expanded( !self.expanded());
          }

          self.addWpt = function( val, evt ) {
            waypointDialog.setProcedureName( self.name() );
            waypointDialog.show( {
              name: '',
              type: '',
              latlng: [ '', '' ],
            }, function( props ) {
              jquery.ajax({
                type: "post",
                dataType: "json",
                url: '/svc/getapt',
                data: JSON.stringify({
                    'command' : 'newWaypoint',
                    'id_token' : window.id_token || 0,
                    'procedure': self.id,
                    'name': props.name,
                    'type': props.type,
                    'lat': props.latlng[0],
                    'lng': props.latlng[1],
                }),
              })
              .done(function() {
                  self.waypoints.push( new WaypointViewModel( props ) );
              })
              .fail(function() {
                  console.log('failed to save waypoint data');
              });
            }); // show
          }

        }

        var ViewModel = function(params) {

          var self = this;
          var geoJson = null;

          self.airportId = ko.observable("");
          self.airportId.subscribe(function(newValue) {
            loadAirport(newValue);
          });

          function loadAirport(icao) {

            if( geoJson ) map.removeLayer( geoJson );
            geoJson = null;
            self.procedures.removeAll();

            var url = "https://api.flightgear.org/scenemodels/navdb/airport/" + icao;
            var jqxhr = $.get(url).done(function(data) {
              geoJson = L.geoJson( data.runwaysGeometry, {
                style: {
                  'color': '#404040',
                  'weight': 2,
                  'opacity': 0.5,
                  'fill': true,
                  'fillColor': '#c0c0c0',
                  'fillOpacity': 0.5,
                },
              }).addTo(map);
              map.fitBounds(geoJson.getBounds());

              if( data.procedures ) {
                var ps = [];
                data.procedures.forEach( function(p) {
                  ps.push( new ProcedureViewModel(p) );
                });
                self.procedures( ps );
              }

            }).fail(function() {
              console.log('failed to load airport data');
            }).always(function() {
            });
          }

          self.showAirportInfo = ko.observable(true);
          self.procedures = ko.observableArray([]);

          self.addProcedure = function( obj, evt ) {

            var inplaceEditor = jquery(jquery('#inplace-editor-template').html());

            var elem = jquery(evt.target);
            elem.hide();
            elem.after(inplaceEditor);
            inplaceEditor.val(elem.text()).focus().select();

            function endEdit(val) {
                inplaceEditor.remove();
                elem.show();

                if (typeof (val) === 'undefined')
                    return;
                var val = val.trim();
                var jqxhr = jquery.post('/svc/getapt', JSON.stringify({
                    'command' : 'newProcedure',
                    'id_token' : window.id_token || 0,
                    'icao': self.airportId(),
                    'name': val,
                    'type': 'Star',
                    'runways': 'All',
                })).done(function(data){
                     // trigger a data reload
                     loadAirport(self.airportId());
                });
           }

            inplaceEditor.on('keyup', function(evt) {
                switch (evt.keyCode) {
                case 27:
                    endEdit();
                    break;
                case 13:
                    endEdit(inplaceEditor.val());
                    break;
                }
            });

            inplaceEditor.blur(function() {
                endEdit(inplaceEditor.val());
            });
          }

          self.approachExpanded = ko.observable(false);
          self.clickApproach = function() {
            self.approachExpanded(!self.approachExpanded());
            self.sidExpanded(false);
            self.starExpanded(false);
            self.sidTransitionExpanded(false);
            self.starTransitionExpanded(false);
            self.rwyTransitionExpanded(false);
          }

          self.sidExpanded = ko.observable(false);
          self.clickSid = function() {
            self.approachExpanded(false);
            self.sidExpanded(!self.sidExpanded());
            self.starExpanded(false);
            self.sidTransitionExpanded(false);
            self.starTransitionExpanded(false);
            self.rwyTransitionExpanded(false);
          }

          self.starExpanded = ko.observable(false);
          self.clickStar = function() {
            self.approachExpanded(false);
            self.sidExpanded(false);
            self.starExpanded(!self.starExpanded());
            self.sidTransitionExpanded(false);
            self.starTransitionExpanded(false);
            self.rwyTransitionExpanded(false);
          }

          self.sidTransitionExpanded = ko.observable(false);
          self.clickSidTransition = function() {
            self.approachExpanded(false);
            self.sidExpanded(false);
            self.starExpanded(false);
            self.sidTransitionExpanded(!self.sidTransitionExpanded());
            self.starTransitionExpanded(false);
            self.rwyTransitionExpanded(false);
          }

          self.starTransitionExpanded = ko.observable(false);
          self.clickStarTransition = function() {
            self.approachExpanded(false);
            self.sidExpanded(false);
            self.starExpanded(false);
            self.sidTransitionExpanded(false);
            self.starTransitionExpanded(!self.starTransitionExpanded());
            self.rwyTransitionExpanded(false);
          }

          self.rwyTransitionExpanded = ko.observable(false);
          self.clickRwyTransition = function() {
            self.approachExpanded(false);
            self.sidExpanded(false);
            self.starExpanded(false);
            self.sidTransitionExpanded(false);
            self.starTransitionExpanded(false);
            self.rwyTransitionExpanded(!self.rwyTransitionExpanded());
          }

          if( params && params.icao ) self.airportId( params.icao);
        }

    ViewModel.prototype.dispose = function() {
    }

    // Return component definition
    return {
        viewModel : ViewModel,
        template : htmlString
    };
});
