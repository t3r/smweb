define([
        'knockout', 'text!./WaypointDialog.html', 'jquery', 'jquery-ui'
], function(ko, htmlString, jquery, jqui ) {

  var WaypointDialog= function( params ) {

    var self = this;

    var ViewModel = function( props ) {
      var self = this;

      props = props || {}

      self.name = ko.observable( props.name || "" );
      self.name.subscribe( function( newValue ) {
        if( 0 < (self.latitude().toString().length + self.longitude().toString().length) ) return;
        if( 0 == newValue.toString().length ) return;

        var url = "/svc/getapt?fix=" + newValue;
        var jqxhr = $.get(url).done(function(data) {
          if( data && data.features && data.features.length > 0 ) {
            self.longitude( data.features[0].geometry.coordinates[0] );
            self.latitude( data.features[0].geometry.coordinates[1] );
            self.name( data.features[0].id );
          }
        }).fail(function() {
          console.log('failed to load fix data');
        }).always(function() {
        });
      });

      self.type = ko.observable(props.type || "");
      self.latitude = ko.observable(props.latlng ? props.latlng[0] : '');
      self.longitude = ko.observable(props.latlng ? props.latlng[1] : '');
    }

    self.show = function( props, callback ) {
      var dialog = jquery( htmlString  ).dialog({
        autoOpen: true,
        height: 300,
        width: 600,
        modal: true,
        show: true,
        buttons: {
          Ok: function() {
            var viewModel = ko.dataFor( dialog[0] );
            var props = {
              name: viewModel.name(),
              type: viewModel.type(),
              latlng: [ Number(viewModel.latitude()), Number(viewModel.longitude()) ],
            }
            dialog.dialog( "close" );
            callback(props);
          },
          Cancel: function() {
            dialog.dialog( "close" );
          },
        },
        close: function(evt,ui) {
          ko.cleanNode( dialog[0] );
        }
      });

      ko.applyBindings(new ViewModel(props), dialog[0] );
      jquery('.make-selectmenu').selectmenu();
    }

//    var title = dialog.dialog("option", "title");

    self.setProcedureName = function( name ) {
//      dialog.dialog("option", "title", title + " (" + name + ")");
    }
  }

  WaypointDialog.prototype.dispose = function() {
  }

  return WaypointDialog;
});
