/*
 * L.TileGrid displays the flightgear tile grid 
*  based on https://github.com/jieter/Leaflet.Grid
 */

require(['./fgtile'], function(fgtile) {
});

L.TileGrid = L.LayerGroup.extend({
	options: {
		xticks: 8,
		yticks: 5,

		// 'decimal' or one of the templates below
		coordStyle: 'MinDec',
		coordTemplates: {
			'MinDec': '{degAbs}&deg;&nbsp;{minDec}\'{dir}',
			'DMS': '{degAbs}{dir}{min}\'{sec}"'
		},

		// Path style for the grid lines
		lineStyle: {
			stroke: true,
			color: '#111',
			opacity: 0.6,
			weight: 1
		},
		
		// Path style for the tile lines
		tileStyle: {
			stroke: true,
			color: 'blue',
			opacity: 0.6,
			weight: 1
		},
		
		// Redraw on move or moveend
		redraw: 'move',

                minHeightForTiles: 1.5,
	},

	initialize: function (options) {
		L.LayerGroup.prototype.initialize.call(this);
		L.Util.setOptions(this, options);

	},

	onAdd: function (map) {
		this._map = map;

		var grid = this.redraw();
		this._map.on('viewreset '+ this.options.redraw, function () {
			grid.redraw();
		});

		this.eachLayer(map.addLayer, map);
	},
	
	onRemove: function (map) {
		// remove layer listeners and elements
		map.off('viewreset '+ this.options.redraw, this.map);
		this.eachLayer(this.removeLayer, this);
	},

	redraw: function () {
		// pad the bounds to make sure we draw the lines a little longer
                var b = this._map.getBounds();
                var drawTiles = (b.getNorth() - b.getSouth() < this.options.minHeightForTiles );

		this._bounds = b.pad(0.1);

		var grid = [];
		var i;


                if( drawTiles ) {
                  var high = this._snap(this._bounds.getNorth(),1/8);
                  var low = this._snap(this._bounds.getSouth(),1/8);
                  for( ; low <= high; low += 1/8 ) {
			grid.push(this._horizontalLine(low, this.options.tileStyle));

                        var right = this._snap(this._bounds.getEast(),this._tileWidth(low));
                        var left = this._snap(this._bounds.getWest(),this._tileWidth(low));
                        for( ; left <= right; left += this._tileWidth(low) ) {
  		          var line= L.polyline([
			    [low+1/8, left],
			    [low, left]
		          ], this.options.tileStyle);
                          grid.push( line );
                        }
                  }
                }

		var latLines = this._latLines();
		for (i in latLines) {
			if (Math.abs(latLines[i]) > 90) {
				continue;
			}
			grid.push(this._horizontalLine(latLines[i]));
			grid.push(this._label('lat', latLines[i]));
		}

		var lngLines = this._lngLines();
		for (i in lngLines) {
			grid.push(this._verticalLine(lngLines[i]));
			grid.push(this._label('lng', lngLines[i]));
		}

		this.eachLayer(this.removeLayer, this);

		for (i in grid) {
			this.addLayer(grid[i]);
		}
		return this;
	},

	_latLines: function () {
		return this._lines(
			this._bounds.getSouth(),
			this._bounds.getNorth(),
			this.options.yticks * 2,
			this._containsEquator()
		);
	},
	_lngLines: function () {
		return this._lines(
			this._bounds.getWest(),
			this._bounds.getEast(),
			this.options.xticks * 2,
			this._containsIRM()
		);
	},

	_lines: function (low, high, ticks, containsZero) {
		var delta = low - high,
			tick = this._round(delta / ticks, delta);

		if (containsZero) {
			low = Math.floor(low / tick) * tick;
		} else {
			low = this._snap(low, tick);
		}

		var lines = [];
		for (var i = -1; i <= ticks; i++) {
			lines.push(low - (i * tick));
		}
		return lines;
	},

	_containsEquator: function () {
		var bounds = this._map.getBounds();
		return bounds.getSouth() < 0 && bounds.getNorth() > 0;
	},

	_containsIRM: function () {
		var bounds = this._map.getBounds();
		return bounds.getWest() < 0 && bounds.getEast() > 0;
	},

	_verticalLine: function (lng, opts) {
		return new L.Polyline([
			[this._bounds.getNorth(), lng],
			[this._bounds.getSouth(), lng]
		], opts || this.options.lineStyle);
	},
	_horizontalLine: function (lat, opts) {
		return new L.Polyline([
			[lat, this._bounds.getWest()],
			[lat, this._bounds.getEast()]
		], opts || this.options.lineStyle);
	},

	_snap: function (num, gridSize) {
		return Math.floor(num / gridSize) * gridSize;
	},

	_round: function (num, delta) {
		var ret;

		delta = Math.abs(delta);
		if (delta >= 1) {
			if (Math.abs(num) > 1) {
				ret = Math.round(num);
			} else {
				ret = (num < 0) ? Math.floor(num) : Math.ceil(num);
			}
		} else {
			var dms = this._dec2dms(delta);
			if (dms.min >= 1) {
				ret = Math.ceil(dms.min) * 60;
			} else {
				ret = Math.ceil(dms.minDec * 60);
			}
		}

		return ret;
	},

	_label: function (axis, num) {
		var latlng;
		var bounds = this._map.getBounds().pad(-0.005);

		if (axis == 'lng') {
			latlng = L.latLng(bounds.getNorth(), num);
		} else {
			latlng = L.latLng(num, bounds.getWest());
		}

		return L.marker(latlng, {
			icon: L.divIcon({
				iconSize: [0, 0],
				className: 'leaflet-grid-label',
				html: '<div class="' + axis + '">' + this.formatCoord(num, axis) + '</div>'
			})
		});
	},

	_dec2dms: function (num) {
		var deg = Math.floor(num);
		var min = ((num - deg) * 60);
		var sec = Math.floor((min - Math.floor(min)) * 60);
		return {
			deg: deg,
			degAbs: Math.abs(deg),
			min: Math.floor(min),
			minDec: min,
			sec: sec
		};
	},

        _tileWidth: function(lat) {
          lat = Math.abs(lat);
          if( lat < 22 ) return 1.0/8.0;
          if( lat < 62 ) return 1.0/4.0;
          if( lat < 76 ) return 1.0/2.0;
          if( lat < 83 ) return 1.0/1.0;
          if( lat < 86 ) return 2.0/1.0;
          if( lat < 88 ) return 4.0/1.0;
          if( lat < 89 ) return 8.0/1.0;
          return 360.0;
        },

	formatCoord: function (num, axis, style) {
		if (!style) {
			style = this.options.coordStyle;
		}
		if (style == 'decimal') {
			var digits;
			if (num >= 10) {
				digits = 2;
			} else if (num >= 1) {
				digits = 3;
			} else {
				digits = 4;
			}
			return num.toFixed(digits);
		} else {
			// Calculate some values to allow flexible templating
			var dms = this._dec2dms(num);

			var dir;
			if (dms.deg === 0) {
				dir = '&nbsp;';
			} else {
				if (axis == 'lat') {
					dir = (dms.deg > 0 ? 'N' : 'S');
				} else {
					dir = (dms.deg > 0 ? 'E' : 'W');
				}
			}

			return L.Util.template(
				this.options.coordTemplates[style],
				L.Util.extend(dms, {
					dir: dir,
					minDec: Math.round(dms.minDec, 2)
				})
			);
		}
	}

});

L.tileGrid = function (options) {
	return new L.TileGrid(options);
};

