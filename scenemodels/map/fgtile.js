define( [], function() {

  function tileWidth(lat)
  {
    lat = Math.abs(lat);
    if( lat < 22 ) return 1.0/8.0;
    if( lat < 62 ) return 1.0/4.0;
    if( lat < 76 ) return 1.0/2.0;
    if( lat < 83 ) return 1.0/1.0;
    if( lat < 86 ) return 2.0/1.0;
    if( lat < 88 ) return 4.0/1.0;
    if( lat < 89 ) return 8.0/1.0;
    return 360.0;
  }

  function tileIndexFromCoordinate (lat,lon)
  {
    var base_y    = Math.floor(lat);
    var y         = Math.trunc((lat-base_y)*8);
    var tilewidth = tileWidth(lat);
    var base_x    = Math.floor(Math.floor( lon / tilewidth )* tilewidth );
    if( base_x < -180) {
      base_x=-180;
    };
    var x         = Math.trunc(Math.floor((lon-base_x)/tilewidth));
    var tile = Math.trunc(((Math.trunc(Math.floor(lon))+180)<<14) + ((Math.trunc(Math.floor(lat))+ 90) << 6) + (y << 3) + x);
    return tile;
  }

  return {
    'tileWidth': tileWidth,
    'tileIndexFromCoordinate': tileIndexFromCoordinate,
  }
});
