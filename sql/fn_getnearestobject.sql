CREATE OR REPLACE FUNCTION fn_getnearestobject(model_id integer, lon numeric, lat numeric ) 
RETURNS integer
    LANGUAGE sql
    AS $$
      SELECT (
        ST_DistanceSpheroid((
          SELECT wkb_geometry FROM fgs_objects 
            WHERE ob_model = model_id 
            ORDER BY 
              ABS( ST_DistanceSpheroid( (wkb_geometry), (ST_PointFromText('POINT('||lon::text||' '||lat::text||')', 4326)), 'SPHEROID["WGS84",6378137.000,298.257223563]') ) 
          ASC LIMIT 1
        ),(
          ST_PointFromText('POINT('||lon::text||' '||lat::text||')', 4326)
        ), 'SPHEROID["WGS84",6378137.000,298.257223563]'
        )
      )::integer;

$$;

select fn_getnearestobject(565,10.0,53.5);
