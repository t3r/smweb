require.config({
    shim: {
      'jquery-ui': {
        depends: 'jquery',
      },
      'leaflet': {
        exports: 'L',
      },
      'leaflet-tilegrid': {
        deps: [ 'leaflet' ]
      },
      'leaflet-coordinates': {
        deps: [ 'leaflet' ]
      },
      'leaflet-contextmenu': {
        deps: [ 'leaflet' ]
      },
    },
    baseUrl : '.',
    paths : {
      'leaflet': '3rdparty/leaflet',
      'jquery': 'https://code.jquery.com/jquery-1.11.3.min',
      'jquery-ui': 'https://code.jquery.com/ui/1.11.4/jquery-ui.min',
      'leaflet-coordinates': '3rdparty/Leaflet.Coordinates-0.1.5.min',
      'leaflet-contextmenu': '3rdparty/leaflet.contextmenu',
      'leaflet-tilegrid': 'tilegrid',
      'knockout': 'https://cdnjs.cloudflare.com/ajax/libs/knockout/3.4.0/knockout-min',
      'text': 'https://cdnjs.cloudflare.com/ajax/libs/require-text/2.0.12/text.min',
    },          
    waitSeconds : 30,
});     

require([           
        'knockout', 'TheMap', 
], function(ko, map ) {

        var ViewModel = function() {
          var self = this;

          self.stylesheets = ko.observableArray([
            "3rdparty/leaflet.css",
            "3rdparty/Leaflet.Coordinates-0.1.5.css",
            "3rdparty/leaflet.contextmenu.css",
            "https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css",
          ]);
        }

        ko.extenders.uppercase = function(target, option) {
          target.subscribe(function(newValue) {
             target(newValue.toUpperCase());
          });
          return target;
        };

        ko.components.register('AirportInfo', {
          require: 'AirportInfo'
        });
        //ko.applyBindings( new ViewModel, document.getElementById('map') );
        ko.applyBindings( new ViewModel() );
});
