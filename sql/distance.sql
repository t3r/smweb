select count(*) from fgs_objects where ST_DWithin(wkb_geometry,'SRID=4326;POINT(10.1 53.5)',0.1);
