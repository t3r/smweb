CREATE OR REPLACE FUNCTION fn_getnearestobject(model_id integer, lon numeric, lat numeric ) 
RETURNS bigint
    LANGUAGE sql
    AS $$
      SELECT 
        COUNT(*) 
        FROM fgs_objects 
        WHERE ob_model=model_id AND ST_DWithin(wkb_geometry,ST_PointFromText('POINT('||lon::text||' '||lat::text||')', 4326),0.000135,false);

$$;

select fn_getnearestobject(565,10.0,53.5);
