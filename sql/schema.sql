--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.6
-- Dumped by pg_dump version 11.1 (Debian 11.1-1.pgdg90+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: hstore; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS hstore WITH SCHEMA public;


--
-- Name: EXTENSION hstore; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION hstore IS 'data type for storing sets of (key, value) pairs';


--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';


--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: fgs_navtype; Type: TYPE; Schema: public; Owner: flightgear
--

CREATE TYPE public.fgs_navtype AS ENUM (
    'NDB',
    'VOR',
    'LOC',
    'LOC-ILS',
    'GS',
    'OM',
    'MM',
    'IM',
    'DME-ILS',
    'DME'
);


ALTER TYPE public.fgs_navtype OWNER TO flightgear;

--
-- Name: fgs_procedure_types; Type: TYPE; Schema: public; Owner: flightgear
--

CREATE TYPE public.fgs_procedure_types AS ENUM (
    'Sid',
    'Star',
    'Approach',
    'Sid_Transition',
    'Star_Transition',
    'Runway_Transition'
);


ALTER TYPE public.fgs_procedure_types OWNER TO flightgear;

--
-- Name: fgs_waypoint_altitude_restriction; Type: TYPE; Schema: public; Owner: flightgear
--

CREATE TYPE public.fgs_waypoint_altitude_restriction AS ENUM (
    'at',
    'above',
    'below',
    'none'
);


ALTER TYPE public.fgs_waypoint_altitude_restriction OWNER TO flightgear;

--
-- Name: fgs_waypoint_type; Type: TYPE; Schema: public; Owner: flightgear
--

CREATE TYPE public.fgs_waypoint_type AS ENUM (
    'Normal',
    'Runway',
    'Hold',
    'Vectors',
    'Intc',
    'VorRadialIntc',
    'DmeIntc',
    'ConstHdgtoAlt',
    'PBD'
);


ALTER TYPE public.fgs_waypoint_type OWNER TO flightgear;

--
-- Name: fn_alignendpylon(public.geometry, public.geometry); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_alignendpylon(p1 public.geometry, p2 public.geometry) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
DECLARE
BEGIN
    RETURN degrees(ST_Azimuth(p1,p2));
END;
$$;


ALTER FUNCTION public.fn_alignendpylon(p1 public.geometry, p2 public.geometry) OWNER TO flightgear;

--
-- Name: fn_alignmiddlepylon(public.geometry, public.geometry, public.geometry); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_alignmiddlepylon(p1 public.geometry, p2 public.geometry, p3 public.geometry) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
DECLARE
BEGIN
    RETURN (degrees(ST_Azimuth(p1,p2))+degrees(ST_Azimuth(p2,p3)))/2;
END;
$$;


ALTER FUNCTION public.fn_alignmiddlepylon(p1 public.geometry, p2 public.geometry, p3 public.geometry) OWNER TO flightgear;

--
-- Name: fn_boundingbox(public.geometry); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_boundingbox(public.geometry) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        min_lon integer;
        min_lat integer;
        max_lon integer;
        max_lat integer;
    BEGIN
        min_lon := floor(floor(ST_X($1)) / 10) * 10;
        min_lat := floor(floor(ST_Y($1)) / 10) * 10;
--        max_lon := ceil(ceil(ST_X($1)) / 10) * 10;
--        max_lat := ceil(ceil(ST_Y($1)) / 10) * 10;
        max_lon := min_lon + 10;
        max_lat := min_lat + 10;
        return concat('ST_SetSRID(''BOX3D(', min_lon, ' ',  min_lat, ', ', max_lon, ' ', max_lat, ')''::BOX3D, 4326)');
    END
$_$;


ALTER FUNCTION public.fn_boundingbox(public.geometry) OWNER TO flightgear;

--
-- Name: fn_csmerge(character varying); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_csmerge(grasslayer character varying) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        getcslayers varchar := $$SELECT f_table_name FROM geometry_columns WHERE f_table_name LIKE 'cs_%' AND type LIKE 'POLYGON' ORDER BY f_table_name;$$;
        bboxtest varchar;
        xstest varchar;
        intest varchar;
        delobj varchar;
        diffobj varchar;
        backdiff varchar;
        newcslayers varchar := 'SELECT DISTINCT pglayer FROM newcs_full ORDER BY pglayer;';
        addnewlayer varchar;
        intersects bool;
        within bool;
        cslayer record;
        ogcfid record;
        pglayer record;
    BEGIN
        DROP TABLE IF EXISTS newcs_hole;
        CREATE TABLE newcs_hole AS SELECT ST_MakeValid(ST_Collect(wkb_geometry)) AS wkb_geometry FROM newcs_collect;
        ALTER TABLE newcs_hole ADD COLUMN ogc_fid serial NOT NULL;
        ALTER TABLE newcs_hole ADD CONSTRAINT "enforce_dims_wkb_geometry" CHECK (ST_NDims(wkb_geometry) = 2);
        ALTER TABLE newcs_hole ADD CONSTRAINT "enforce_geotype_wkb_geometry" CHECK (GeometryType(wkb_geometry) = 'MULTIPOLYGON'::text);
        ALTER TABLE newcs_hole ADD CONSTRAINT "enforce_srid_wkb_geometry" CHECK (ST_SRID(wkb_geometry) = 4326);
        ALTER TABLE newcs_hole ADD CONSTRAINT "enforce_valid_wkb_geometry" CHECK (ST_IsValid(wkb_geometry));

        FOR cslayer IN
            EXECUTE getcslayers
        LOOP  -- through layers
            bboxtest := concat('SELECT ogc_fid FROM ', quote_ident(cslayer.f_table_name), ' WHERE wkb_geometry && (SELECT wkb_geometry FROM newcs_hole) ORDER BY ogc_fid;');
            FOR ogcfid IN
                EXECUTE bboxtest
            LOOP  -- through candidate objects
                xstest := concat('SELECT ST_Intersects((SELECT wkb_geometry FROM newcs_hole), (SELECT wkb_geometry FROM ', quote_ident(cslayer.f_table_name), ' WHERE ogc_fid = ', ogcfid.ogc_fid, '));');
                EXECUTE xstest INTO intersects;
                CASE WHEN intersects IS TRUE THEN
                    intest := concat('SELECT ST_Within((SELECT wkb_geometry FROM ', quote_ident(cslayer.f_table_name), ' WHERE ogc_fid = ', ogcfid.ogc_fid, '), (SELECT wkb_geometry FROM newcs_hole));');
                    EXECUTE intest INTO within;
                    CASE WHEN within IS FALSE THEN
                        DROP TABLE IF EXISTS newcs_diff;
                        diffobj := concat('CREATE TABLE newcs_diff AS SELECT (ST_Dump(ST_MakeValid(ST_Difference((SELECT ST_MakeValid(wkb_geometry) FROM ', quote_ident(cslayer.f_table_name), ' WHERE ogc_fid = ', ogcfid.ogc_fid, '), (SELECT wkb_geometry FROM newcs_hole))))).geom AS wkb_geometry;');
                        RAISE NOTICE '%', diffobj;
                        EXECUTE diffobj;
                        ALTER TABLE newcs_diff ADD COLUMN ogc_fid serial NOT NULL;
                        ALTER TABLE newcs_diff ADD CONSTRAINT "enforce_valid_wkb_geometry" CHECK (ST_IsValid(wkb_geometry));
                        backdiff := concat('INSERT INTO ', quote_ident(cslayer.f_table_name), ' (wkb_geometry) (SELECT wkb_geometry FROM newcs_diff);');
--                        RAISE NOTICE '%', backdiff;
                        EXECUTE backdiff;
                    ELSE NULL;
                    END CASE;
                    delobj := concat('DELETE FROM ', quote_ident(cslayer.f_table_name), ' WHERE ogc_fid = ', ogcfid.ogc_fid, ';');
--                    RAISE NOTICE '%', delobj;
                    EXECUTE delobj;
                ELSE NULL;
                END CASE;
            END LOOP;
        END LOOP;

        FOR pglayer IN
            EXECUTE newcslayers
        LOOP
            addnewlayer := concat('INSERT INTO ', quote_ident(pglayer.pglayer), $$ (wkb_geometry) (SELECT wkb_geometry FROM newcs_full WHERE pglayer LIKE '$$, quote_ident(pglayer.pglayer), $$');$$);
--            RAISE NOTICE '%', addnewlayer;
            EXECUTE addnewlayer;
        END LOOP;
    END;
$_$;


ALTER FUNCTION public.fn_csmerge(grasslayer character varying) OWNER TO flightgear;

--
-- Name: fn_dlaction(character varying, character varying); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_dlaction(character varying, character varying) RETURNS void
    LANGUAGE plpgsql
    AS $_$ 
    DECLARE
        pattern varchar;
        myuuid varchar;
        recordlayer record;
        temptable varchar;
        worklayer varchar;
        selectsql varchar;
        dropsql varchar;
        copysql varchar;
    BEGIN
        pattern := $1;
        myuuid := $2;
        selectsql := (SELECT * FROM geometry_columns WHERE f_table_name LIKE pattern);
        FOR recordlayer IN
            EXECUTE selectsql
        LOOP
            worklayer := recordlayer.f_table_name;
            temptable := concat(myuuid, '_', worklayer);
            dropsql := concat('DROP TABLE IF EXISTS "', temptable, '";');
            EXECUTE dropsql;
            copysql := concat('CREATE TABLE "', temptable, '" AS SELECT * FROM ', worklayer, $$ WHERE wkb_geometry && (SELECT wkb_geometry FROM download WHERE uuid LIKE '$$, myuuid, $$');$$);
            EXECUTE copysql;
        END LOOP;
    END;
$_$;


ALTER FUNCTION public.fn_dlaction(character varying, character varying) OWNER TO flightgear;

--
-- Name: fn_dltable(uuid); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_dltable(uuid) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        tab record;
        item varchar;
        selectsql varchar;
        countsql varchar;
    BEGIN
        item := feature FROM download WHERE uuid = $1;
        selectsql := concat('SELECT * FROM geometry_columns WHERE f_table_name LIKE $$', item, '_%$$;');
        FOR tab IN
            EXECUTE selectsql
        LOOP
            countsql := concat('SELECT CASE WHEN COUNT(wkb_geometry)::integer > 0 THEN $$', quote_ident(tab.f_table_name), '$$ ELSE NULL END FROM ', quote_ident(tab.f_table_name), ' WHERE wkb_geometry && (SELECT wkb_geometry FROM download WHERE uuid = $$', $1, '$$);');
            RETURN QUERY EXECUTE countsql;
        END LOOP;
    RETURN;
    END;
$_$;


ALTER FUNCTION public.fn_dltable(uuid) OWNER TO flightgear;

--
-- Name: fn_dumpstgrows(integer); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_dumpstgrows(integer) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        tileno integer = $1;
    BEGIN
        RETURN QUERY
        WITH modelitems AS (SELECT mo_id AS id,
            (CASE WHEN mo_shared > 0 THEN 1 ELSE 0 END) AS shared,
            mg_path AS path,
            mo_path AS name,
            trim(trailing '.' FROM to_char(ST_X(wkb_geometry), 'FM990D999999999')) AS lon,
            trim(trailing '.' FROM to_char(ST_Y(wkb_geometry), 'FM990D999999999')) AS lat,
            trim(trailing '.' FROM to_char(fn_StgElevation(ob_gndelev, ob_elevoffset)::float, 'FM99990D999999999')) AS stgelev,
            trim(trailing '.' FROM to_char(fn_StgHeading(ob_heading)::float, 'FM990D999999999')) AS stgheading
        FROM fgs_objects, fgs_models, fgs_modelgroups
        WHERE ob_tile = tileno
            AND ob_valid IS TRUE AND ob_tile IS NOT NULL
            AND ob_model = mo_id AND ob_gndelev > -9999
            AND mo_shared = mg_id),

        signitems AS (SELECT si_definition AS name,
            trim(trailing '.' FROM to_char(ST_X(wkb_geometry), 'FM990D999999999')) AS lon,
            trim(trailing '.' FROM to_char(ST_Y(wkb_geometry), 'FM990D999999999')) AS lat,
            trim(trailing '.' FROM to_char(si_gndelev::float, 'FM99990D999999999')) AS stgelev,
            trim(trailing '.' FROM to_char(fn_StgHeading(si_heading)::float, 'FM990D999999999')) AS stgheading
        FROM fgs_signs
        WHERE si_tile = tileno
            AND si_valid IS TRUE AND si_tile IS NOT NULL
            AND si_gndelev > -9999),

        modelrow AS (SELECT concat((CASE WHEN shared > 0 THEN concat('OBJECT_SHARED Models/', path) ELSE 'OBJECT_STATIC '  END),
            name, ' ', lon, ' ', lat, ' ', stgelev, ' ', stgheading)::text AS object
        FROM modelitems
        ORDER BY shared DESC, id, lon::float, lat::float,
            stgelev::float, stgheading::float),

        signrow AS (SELECT concat('OBJECT_SIGN ',
            name, ' ', lon, ' ', lat, ' ', stgelev, ' ', stgheading)::text AS object
        FROM signitems
        ORDER BY lon::float, lat::float,
            stgelev::float, stgheading::float),

        mo AS (SELECT string_agg(object, E'\n') AS mo FROM modelrow),
        si AS (SELECT string_agg(object, E'\n') AS si FROM signrow)

        SELECT (CASE
            WHEN COUNT(mo) = 1 AND COUNT(si) = 1 THEN concat(mo, E'\n', si)
            WHEN COUNT(mo) = 1 AND COUNT(si) = 0 THEN mo
            WHEN COUNT(mo) = 0 AND COUNT(si) = 1 THEN si
        END) AS ret
        FROM mo, si
        WHERE (SELECT COUNT(mo) FROM mo) > 0
            OR (SELECT COUNT(si) FROM si) > 0
        GROUP BY mo, si;

    END;
$_$;


ALTER FUNCTION public.fn_dumpstgrows(integer) OWNER TO flightgear;

--
-- Name: fn_freqrange(numeric, numeric, numeric); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_freqrange(numeric, numeric, numeric) RETURNS SETOF json
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        lon numeric := $1;
        lat numeric := $2;
        range numeric := $3;
    BEGIN
        RETURN QUERY
        WITH res AS (SELECT icao,
            CAST(
                ST_Distance_Spheroid(
                    ST_PointFromText(concat('POINT(6.5 51.5)'), 4326),
                    wkb_geometry,
                    'SPHEROID["WGS84",6378137.000,298.257223563]')
                AS numeric) AS dist
        FROM apt_airfield)

        SELECT array_to_json(array_agg(row_to_json(t))) AS freq
        FROM (
            SELECT f.icao,
                f.freq_name,
                f.freq_mhz,
                round(res.dist / 1852.01, 1)
                AS dist
            FROM apt_freq AS f,
                res
            WHERE res.dist < range * 1852.01
            AND f.icao = res.icao
            ORDER BY res.dist, f.icao, f.freq_name)
        AS t;
    END;
$_$;


ALTER FUNCTION public.fn_freqrange(numeric, numeric, numeric) OWNER TO flightgear;

--
-- Name: fn_getcountrycodetwo(public.geometry); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_getcountrycodetwo(lg public.geometry) RETURNS character
    LANGUAGE sql
    AS $$
    SELECT co_code FROM gadm2, fgs_countries WHERE ST_Within(lg, gadm2.wkb_geometry) AND gadm2.iso ILIKE fgs_countries.co_three;
$$;


ALTER FUNCTION public.fn_getcountrycodetwo(lg public.geometry) OWNER TO flightgear;

--
-- Name: fn_getdistanceinmeters(public.geometry, public.geometry); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_getdistanceinmeters(lg1 public.geometry, lg2 public.geometry) RETURNS double precision
    LANGUAGE sql
    AS $$
    SELECT ST_Distance(lg1::geography,lg2::geography);
$$;


ALTER FUNCTION public.fn_getdistanceinmeters(lg1 public.geometry, lg2 public.geometry) OWNER TO flightgear;

--
-- Name: fn_getmodelpath(integer); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_getmodelpath(model integer) RETURNS character
    LANGUAGE plpgsql
    AS $$
DECLARE
    r RECORD;
BEGIN
    SELECT INTO r mg_path,mo_path FROM fgs_models  LEFT OUTER JOIN fgs_modelgroups  ON mo_shared=mg_id WHERE mo_id=model;
    IF NOT FOUND THEN
       RETURN '';
    ELSE
       RETURN 'Models/'||r.mg_path||r.mo_path;
    END IF;
END;
$$;


ALTER FUNCTION public.fn_getmodelpath(model integer) OWNER TO flightgear;

--
-- Name: fn_gettilenumber(public.geometry); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_gettilenumber(lg public.geometry) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    epsilon CONSTANT float := 0.0000001;
    dlon float;
    dlat float;
    lon integer;
    lat integer;
    difflon float;
    difflat float;
    bx integer;
    a integer;
    b integer;
    l float;
    r integer;
    w float;
    x integer;
    y integer;
BEGIN
    dlon := ST_X(lg);
    dlat := ST_Y(lg);

    IF abs(difflon) < epsilon THEN
       lon := trunc(dlon);
    ELSIF dlon >= 0 THEN
       lon := trunc(dlon);
    ELSE
       lon := floor(dlon);
    END IF;
       difflon := (dlon-lon);

    IF abs(difflat) < epsilon THEN
       lat := trunc(dlat);
    ELSIF dlat >= 0 THEN
       lat := trunc(dlat);
    ELSE
       lat := floor(dlat);
    END IF;
       difflat := (dlat-lat);

    IF    dlat >= 89.0 THEN
       w := 12.0;
    ELSIF dlat >= 86.0 THEN 
       w := 4.0;
    ELSIF dlat >= 83.0 THEN 
       w := 2.0;
    ELSIF dlat >= 76.0 THEN 
       w := 1.0;
    ELSIF dlat >= 62.0 THEN 
       w := 0.5;
    ELSIF dlat >= 22.0 THEN 
       w := 0.25;
    ELSIF dlat >= -22.0 THEN
       w := 0.125;
    ELSIF dlat >= -62.0 THEN 
       w := 0.25;
    ELSIF dlat >= -76.0 THEN 
       w := 0.5;
    ELSIF dlat >= -83.0 THEN 
       w := 1.0;
    ELSIF dlat >= -86.0 THEN 
       w := 2.0;
    ELSIF dlat >= -89.0 THEN 
       w := 4.0;
    ELSE
       w := 12.0;
    END IF;
	
    IF w <= 1.0 THEN 
       x := trunc(difflon/w);
    ELSE
       lon := floor(floor((lon + epsilon)/w)*w);
       IF lon < -180 THEN
          lon := -180;
       END IF;
       x := 0;
    END IF;
	
    y := trunc(difflat*8);
    y := y<<3;

    a := (lon+180)<<14;
    b := (lat+90)<<6;
    r := a+b+y+x;

    RETURN r;
END;
$$;


ALTER FUNCTION public.fn_gettilenumber(lg public.geometry) OWNER TO flightgear;

--
-- Name: fn_gettilenumberxy(double precision, double precision); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_gettilenumberxy(lon double precision, lat double precision) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    x text;
    n integer;
BEGIN
    x := 'SRID=4326;POINT('||lon::text||' '||lat::text||')';
    n := fn_GetTileNumber(ST_GeomFromEWKT(x));
    RETURN n;
END;
$$;


ALTER FUNCTION public.fn_gettilenumberxy(lon double precision, lat double precision) OWNER TO flightgear;

--
-- Name: fn_importrecordposttrigger(); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_importrecordposttrigger() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF (TG_OP = 'UPDATE') OR (TG_OP = 'INSERT') THEN
       NEW.ob_country:=fn_GetCountryCodeTwo(NEW.wkb_geometry);
       NEW.ob_tile:=fn_GetTileNumber(NEW.wkb_geometry);
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.fn_importrecordposttrigger() OWNER TO flightgear;

--
-- Name: fn_scenedir(public.geometry); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_scenedir(public.geometry) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        min_lon integer;
        min_lat integer;
        lon_char char(1);
        lat_char char(1);
    BEGIN
        min_lon := Abs(floor(floor(ST_X($1)) / 10) * 10);
        min_lat := Abs(floor(floor(ST_Y($1)) / 10) * 10);
        lon_char := (CASE WHEN (ST_X($1)) < 0 THEN 'w' ELSE 'e' END);
        lat_char := (CASE WHEN (ST_Y($1)) < 0 THEN 's' ELSE 'n' END);
        return concat(lon_char, lpad(CAST(min_lon AS varchar), 3, '0'), lat_char, lpad(CAST(min_lat AS varchar), 2, '0'));
    END
$_$;


ALTER FUNCTION public.fn_scenedir(public.geometry) OWNER TO flightgear;

--
-- Name: fn_scenesubdir(public.geometry); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_scenesubdir(public.geometry) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        min_lon integer;
        min_lat integer;
        lon_char char(1);
        lat_char char(1);
    BEGIN
        min_lon := Abs(floor(ST_X($1)));
        min_lat := Abs(floor(ST_Y($1)));
        lon_char := (CASE WHEN (ST_X($1)) < 0 THEN 'w' ELSE 'e' END);
        lat_char := (CASE WHEN (ST_Y($1)) < 0 THEN 's' ELSE 'n' END);
        return concat(lon_char, lpad(CAST(min_lon AS varchar), 3, '0'), lat_char, lpad(CAST(min_lat AS varchar), 2, '0'));
    END
$_$;


ALTER FUNCTION public.fn_scenesubdir(public.geometry) OWNER TO flightgear;

--
-- Name: fn_setcsmodtime(); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_setcsmodtime() RETURNS trigger
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
  NEW.ch_date = now();
  RETURN NEW;
END
$$;


ALTER FUNCTION public.fn_setcsmodtime() OWNER TO flightgear;

--
-- Name: fn_setdate(); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_setdate() RETURNS trigger
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
  NEW.date = now();
  RETURN NEW;
END
$$;


ALTER FUNCTION public.fn_setdate() OWNER TO flightgear;

--
-- Name: fn_setmodelmodtime(); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_setmodelmodtime() RETURNS trigger
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
  NEW.mo_modified = now();
  RETURN NEW;
END
$$;


ALTER FUNCTION public.fn_setmodelmodtime() OWNER TO flightgear;

--
-- Name: fn_setnewsmodtime(); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_setnewsmodtime() RETURNS trigger
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
  NEW.ne_timestamp = now();
  RETURN NEW;
END
$$;


ALTER FUNCTION public.fn_setnewsmodtime() OWNER TO flightgear;

--
-- Name: fn_setobjectmodtime(); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_setobjectmodtime() RETURNS trigger
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
  NEW.ob_modified = now();
  RETURN NEW;
END
$$;


ALTER FUNCTION public.fn_setobjectmodtime() OWNER TO flightgear;

--
-- Name: fn_setsignmodtime(); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_setsignmodtime() RETURNS trigger
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
  NEW.si_modified = now();
  RETURN NEW;
END
$$;


ALTER FUNCTION public.fn_setsignmodtime() OWNER TO flightgear;

--
-- Name: fn_stgelevation(numeric, numeric); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_stgelevation(numeric, numeric) RETURNS numeric
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        stgelevation numeric(7,2);
    BEGIN
        stgelevation := CASE WHEN $2 IS NOT NULL THEN ($1 + $2) ELSE $1 END;
        return stgelevation;
    END
$_$;


ALTER FUNCTION public.fn_stgelevation(numeric, numeric) OWNER TO flightgear;

--
-- Name: fn_stgheading(numeric); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_stgheading(numeric) RETURNS numeric
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        stgheading numeric(5,2);
    BEGIN
        stgheading := CASE WHEN $1 > 180 THEN (540 - $1) ELSE (180 - $1) END;
        return stgheading;
    END
$_$;


ALTER FUNCTION public.fn_stgheading(numeric) OWNER TO flightgear;

--
-- Name: fn_unrollmulti(character varying); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.fn_unrollmulti(layer character varying) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        getpkey varchar := $$SELECT a.attname AS pkey FROM pg_index AS i JOIN pg_attribute AS a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) WHERE i.indrelid = 'osm_naturalwater'::regclass AND i.indisprimary;$$;
        testmulti varchar;
        unrollmulti varchar;
        delmulti varchar;
        pkey varchar;
        multifid record;
    BEGIN
        EXECUTE getpkey INTO pkey;
--        RAISE NOTICE '%', pkey;
        testmulti := concat('SELECT ', pkey, ' AS pkey FROM osm_naturalwater WHERE ST_NumGeometries(wkb_geometry) IS NOT NULL ORDER BY ', pkey, ';');
        RAISE NOTICE '%', testmulti;
        FOR multifid IN
            EXECUTE testmulti
        LOOP
            unrollmulti := concat('INSERT INTO osm_naturalwater (wkb_geometry) (SELECT (ST_Dump(wkb_geometry)).geom FROM osm_naturalwater WHERE ', pkey, ' = ', multifid.pkey, ');');
            RAISE NOTICE '%', unrollmulti;
            delmulti := concat('DELETE FROM osm_naturalwater WHERE ', pkey, ' = ', multifid.pkey, ';');
            RAISE NOTICE '%', delmulti;
            EXECUTE unrollmulti;
            EXECUTE delmulti;
        END LOOP;
    END;
$_$;


ALTER FUNCTION public.fn_unrollmulti(layer character varying) OWNER TO flightgear;

--
-- Name: icaorange(); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.icaorange() RETURNS SETOF text
    LANGUAGE plpgsql
    AS $$

DECLARE
    searchsql text := '';
    distsql text := '';
    myvar text := '';

BEGIN
    searchsql := 'SELECT icao FROM apt_airfield WHERE
        ST_DWithin(
            (SELECT ST_Transform(wkb_geometry, 900913) FROM apt_airfield WHERE icao LIKE ''LSZH''),
            ST_Transform(wkb_geometry, 900913),
            50*1000*1.85201
        )';

    distsql := 'SELECT (ST_Distance_Spheroid(
            (SELECT wkb_geometry FROM apt_airfield WHERE icao LIKE ''LSZH''),
            (SELECT wkb_geometry FROM apt_airfield WHERE icao LIKE myvar),
            ''SPHEROID["WGS84",6378137.000,298.257223563]''
        )/1000)::decimal(9,3) AS Km';

    FOR myvar IN EXECUTE(searchsql) LOOP
        
    RETURN NEXT myvar;
    END LOOP;

END;
$$;


ALTER FUNCTION public.icaorange() OWNER TO flightgear;

--
-- Name: icaorange(text); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.icaorange(text) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $$
    DECLARE
        searchsql text := '';
        distsql text := '';
        myvar text := '';
    BEGIN
        searchsql := 'SELECT icao FROM apt_airfield WHERE
            ST_DWithin(
                (SELECT ST_Transform(wkb_geometry, 3857) FROM apt_airfield WHERE icao LIKE ''LSZH''),
                ST_Transform(wkb_geometry, 3857),
                50*1000*1.85201
            )';
        distsql := 'SELECT (ST_Distance_Spheroid(
                (SELECT wkb_geometry FROM apt_airfield WHERE icao LIKE ''LSZH''),
                (SELECT wkb_geometry FROM apt_airfield WHERE icao LIKE myvar),
                ''SPHEROID["WGS84",6378137.000,298.257223563]''
            )/1000)::decimal(9,3) AS Km';

        FOR myvar IN EXECUTE(searchsql()) LOOP
        RETURN NEXT myvar;
        END LOOP;
    END;
$$;


ALTER FUNCTION public.icaorange(text) OWNER TO flightgear;

--
-- Name: icaorange(character varying); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.icaorange(character varying) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $_$
    DECLARE
        searchsql text := '';
        distsql text := '';
        myvar text := '';
    BEGIN
        searchsql := 'SELECT icao FROM apt_airfield WHERE
            ST_DWithin(
                (SELECT ST_Transform(wkb_geometry, 900913) FROM apt_airfield WHERE icao LIKE $1),
                ST_Transform(wkb_geometry, 900913),
                50*1000*1.85201
            )';
        distsql := 'SELECT (ST_Distance_Spheroid(
                (SELECT wkb_geometry FROM apt_airfield WHERE icao LIKE ''LSZH''),
                (SELECT wkb_geometry FROM apt_airfield WHERE icao LIKE myvar),
                ''SPHEROID["WGS84",6378137.000,298.257223563]''
            )/1000)::decimal(9,3) AS Km';

        FOR myvar IN EXECUTE(searchsql($1)) LOOP
        RETURN NEXT myvar;
        END LOOP;
    END;
$_$;


ALTER FUNCTION public.icaorange(character varying) OWNER TO flightgear;

--
-- Name: next_mo_id(integer); Type: FUNCTION; Schema: public; Owner: flightgear
--

CREATE FUNCTION public.next_mo_id(integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
  DECLARE
    new_id integer;
  BEGIN
    SELECT nextval(fgs_models_mo_id_seq);
  END;
$$;


ALTER FUNCTION public.next_mo_id(integer) OWNER TO flightgear;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: apt_runway; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.apt_runway (
    wkb_geometry public.geometry(Polygon,4326) NOT NULL,
    icao character varying,
    atype integer,
    rwy_num1 character varying(3),
    rwy_num2 character varying(3),
    length_m double precision,
    width_m double precision,
    true_heading_deg numeric(6,2),
    surface character varying(11),
    smoothness numeric(4,2),
    shoulder character varying(8),
    centerline_lights numeric(1,0),
    edge_lighting character(6),
    distance_remaining_signs numeric(1,0),
    ogc_fid integer NOT NULL,
    CONSTRAINT enforce_dims_wkb_geometry CHECK ((public.st_ndims(wkb_geometry) = 2)),
    CONSTRAINT enforce_geotype_wkb_geometry CHECK ((public.geometrytype(wkb_geometry) = 'POLYGON'::text)),
    CONSTRAINT enforce_srid_wkb_geometry CHECK ((public.st_srid(wkb_geometry) = 4326))
);


ALTER TABLE public.apt_runway OWNER TO flightgear;

--
-- Name: apt_runway_ogc_fid_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.apt_runway_ogc_fid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.apt_runway_ogc_fid_seq OWNER TO flightgear;

--
-- Name: apt_runway_ogc_fid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.apt_runway_ogc_fid_seq OWNED BY public.apt_runway.ogc_fid;


SET default_with_oids = true;

--
-- Name: country_codes; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.country_codes (
    vmap character(2),
    fibs character(2),
    iso3166 character(2),
    name character varying NOT NULL,
    comment character varying,
    src_id numeric(5,0),
    maint_id numeric(5,0)
);


ALTER TABLE public.country_codes OWNER TO flightgear;

SET default_with_oids = false;

--
-- Name: fgs_aircraft; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_aircraft (
    ac_id integer NOT NULL,
    ac_model character(80),
    ac_livery character(20),
    ac_airline character(4),
    ac_type character(20),
    ac_offset integer,
    ac_radius integer,
    ac_performance character(20),
    ac_heavy boolean,
    ac_reqcode character(15)
);


ALTER TABLE public.fgs_aircraft OWNER TO flightgear;

--
-- Name: fgs_aircraft_ac_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_aircraft_ac_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_aircraft_ac_id_seq OWNER TO flightgear;

--
-- Name: fgs_aircraft_ac_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_aircraft_ac_id_seq OWNED BY public.fgs_aircraft.ac_id;


--
-- Name: fgs_airline; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_airline (
    al_id integer NOT NULL,
    al_icao character(4),
    al_name character(60),
    al_callsign character varying(15)
);


ALTER TABLE public.fgs_airline OWNER TO flightgear;

--
-- Name: fgs_airline_al_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_airline_al_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_airline_al_id_seq OWNER TO flightgear;

--
-- Name: fgs_airline_al_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_airline_al_id_seq OWNED BY public.fgs_airline.al_id;


--
-- Name: fgs_airport; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_airport (
    ap_id integer NOT NULL,
    ap_icao character(4) NOT NULL,
    ap_name character(40) NOT NULL
);


ALTER TABLE public.fgs_airport OWNER TO flightgear;

--
-- Name: fgs_airport_ap_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_airport_ap_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_airport_ap_id_seq OWNER TO flightgear;

--
-- Name: fgs_airport_ap_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_airport_ap_id_seq OWNED BY public.fgs_airport.ap_id;


--
-- Name: fgs_authors; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_authors (
    au_id integer NOT NULL,
    au_name character varying(40),
    au_email character varying(40),
    au_notes character varying,
    au_modeldir character(3)
);


ALTER TABLE public.fgs_authors OWNER TO flightgear;

--
-- Name: fgs_authors_au_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_authors_au_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_authors_au_id_seq OWNER TO flightgear;

--
-- Name: fgs_authors_au_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_authors_au_id_seq OWNED BY public.fgs_authors.au_id;


--
-- Name: fgs_clean; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_clean (
    ob_id integer NOT NULL,
    ob_modified timestamp without time zone,
    ob_deleted timestamp without time zone DEFAULT '1970-01-01 00:00:01'::timestamp without time zone NOT NULL,
    ob_text character varying(100),
    wkb_geometry public.geometry(Point,4326) NOT NULL,
    ob_gndelev numeric(7,2) DEFAULT '-9999'::integer,
    ob_elevoffset numeric(5,2) DEFAULT NULL::numeric,
    ob_peakelev numeric(7,2),
    ob_heading numeric(5,2) DEFAULT 0,
    ob_country character(2) DEFAULT NULL::bpchar,
    ob_model integer,
    ob_group integer,
    ob_tile integer,
    ob_reference character varying(20) DEFAULT NULL::character varying,
    ob_submitter character varying(16) DEFAULT 'unknown'::character varying,
    ob_valid boolean DEFAULT true,
    ob_class character varying(10),
    CONSTRAINT enforce_dims_wkb_geometry CHECK ((public.st_ndims(wkb_geometry) = 2)),
    CONSTRAINT enforce_geotype_wkb_geometry CHECK ((public.geometrytype(wkb_geometry) = 'POINT'::text)),
    CONSTRAINT enforce_srid_wkb_geometry CHECK ((public.st_srid(wkb_geometry) = 4326))
);


ALTER TABLE public.fgs_clean OWNER TO flightgear;

--
-- Name: fgs_clean_ob_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_clean_ob_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_clean_ob_id_seq OWNER TO flightgear;

--
-- Name: fgs_clean_ob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_clean_ob_id_seq OWNED BY public.fgs_clean.ob_id;


--
-- Name: fgs_countries; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_countries (
    co_code character(2) NOT NULL,
    co_name character(50),
    co_three character(3)
);


ALTER TABLE public.fgs_countries OWNER TO flightgear;

--
-- Name: fgs_extuserids; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_extuserids (
    eu_authority integer,
    eu_external_id text,
    eu_author_id integer,
    eu_lastlogin timestamp without time zone
);


ALTER TABLE public.fgs_extuserids OWNER TO flightgear;

--
-- Name: TABLE fgs_extuserids; Type: COMMENT; Schema: public; Owner: flightgear
--

COMMENT ON TABLE public.fgs_extuserids IS 'External user-ids for oauth logins';


--
-- Name: COLUMN fgs_extuserids.eu_authority; Type: COMMENT; Schema: public; Owner: flightgear
--

COMMENT ON COLUMN public.fgs_extuserids.eu_authority IS '1: github, 2: google, 3: facebook, 4:twitter';


--
-- Name: fgs_fixes; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_fixes (
    fx_name character varying(32) NOT NULL,
    wkb_geometry public.geometry(Point,4326) NOT NULL,
    CONSTRAINT enforce_dims_wkb_geometry CHECK ((public.st_ndims(wkb_geometry) = 2)),
    CONSTRAINT enforce_geotype_wkb_geometry CHECK ((public.geometrytype(wkb_geometry) = 'POINT'::text)),
    CONSTRAINT enforce_srid_wkb_geometry CHECK ((public.st_srid(wkb_geometry) = 4326)),
    CONSTRAINT enforce_valid_wkb_geometry CHECK (public.st_isvalid(wkb_geometry))
);


ALTER TABLE public.fgs_fixes OWNER TO flightgear;

--
-- Name: fgs_fleet; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_fleet (
    fl_id integer NOT NULL,
    fl_airline character(4),
    fl_livery character(20),
    fl_reqcode character(20),
    fl_homeapt character(4),
    fl_actype character(15),
    fl_reg character(8),
    fl_flighttype character(6) DEFAULT 'gate'::bpchar
);


ALTER TABLE public.fgs_fleet OWNER TO flightgear;

--
-- Name: fgs_fleet_fl_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_fleet_fl_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_fleet_fl_id_seq OWNER TO flightgear;

--
-- Name: fgs_fleet_fl_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_fleet_fl_id_seq OWNED BY public.fgs_fleet.fl_id;


--
-- Name: fgs_flight; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_flight (
    ft_id integer NOT NULL,
    ft_callsign character(30),
    ft_airline character(4),
    ft_reqcode character(20),
    ft_ifr boolean,
    ft_origapt character(4),
    ft_origday integer,
    ft_origtime time without time zone,
    ft_cruiselevel integer,
    ft_destapt character(4),
    ft_destday integer,
    ft_desttime time without time zone,
    ft_repeat character(5)
);


ALTER TABLE public.fgs_flight OWNER TO flightgear;

--
-- Name: fgs_flight_ft_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_flight_ft_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_flight_ft_id_seq OWNER TO flightgear;

--
-- Name: fgs_flight_ft_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_flight_ft_id_seq OWNED BY public.fgs_flight.ft_id;


--
-- Name: fgs_groups; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_groups (
    gp_id integer NOT NULL,
    gp_name character varying(16) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.fgs_groups OWNER TO flightgear;

--
-- Name: fgs_groups_gp_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_groups_gp_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_groups_gp_id_seq OWNER TO flightgear;

--
-- Name: fgs_groups_gp_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_groups_gp_id_seq OWNED BY public.fgs_groups.gp_id;


--
-- Name: fgs_modelclass; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_modelclass (
    mc_id integer NOT NULL,
    mc_model integer NOT NULL,
    mc_class character(10),
    mc_minheight numeric(7,2),
    mc_maxheight numeric(7,2)
);


ALTER TABLE public.fgs_modelclass OWNER TO flightgear;

--
-- Name: fgs_modelclass_mc_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_modelclass_mc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_modelclass_mc_id_seq OWNER TO flightgear;

--
-- Name: fgs_modelclass_mc_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_modelclass_mc_id_seq OWNED BY public.fgs_modelclass.mc_id;


--
-- Name: fgs_modelgroups; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_modelgroups (
    mg_id integer NOT NULL,
    mg_name character varying(40),
    mg_path character varying(30)
);


ALTER TABLE public.fgs_modelgroups OWNER TO flightgear;

--
-- Name: fgs_modelgroups_mg_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_modelgroups_mg_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_modelgroups_mg_id_seq OWNER TO flightgear;

--
-- Name: fgs_modelgroups_mg_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_modelgroups_mg_id_seq OWNED BY public.fgs_modelgroups.mg_id;


--
-- Name: fgs_models_mo_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_models_mo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_models_mo_id_seq OWNER TO flightgear;

--
-- Name: fgs_models; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_models (
    mo_id integer DEFAULT nextval('public.fgs_models_mo_id_seq'::regclass) NOT NULL,
    mo_path character varying(100) NOT NULL,
    mo_modified timestamp without time zone,
    mo_author integer,
    mo_name character varying(100),
    mo_notes character varying,
    mo_thumbfile character varying,
    mo_modelfile character varying NOT NULL,
    mo_shared integer,
    mo_modified_by integer
);


ALTER TABLE public.fgs_models OWNER TO flightgear;

--
-- Name: fgs_navaids; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_navaids (
    na_id integer NOT NULL,
    na_type public.fgs_navtype,
    na_position public.geometry(Point,4326),
    na_elevation numeric,
    na_frequency integer,
    na_range numeric,
    na_multiuse numeric,
    na_ident text,
    na_name text,
    na_airport_id text,
    na_runway text,
    CONSTRAINT enforce_dims_wkb_geometry CHECK ((public.st_ndims(na_position) = 2)),
    CONSTRAINT enforce_geotype_wkb_geometry CHECK ((public.geometrytype(na_position) = 'POINT'::text)),
    CONSTRAINT enforce_srid_wkb_geometry CHECK ((public.st_srid(na_position) = 4326)),
    CONSTRAINT enforce_valid_wkb_geometry CHECK (public.st_isvalid(na_position))
);


ALTER TABLE public.fgs_navaids OWNER TO flightgear;

--
-- Name: TABLE fgs_navaids; Type: COMMENT; Schema: public; Owner: flightgear
--

COMMENT ON TABLE public.fgs_navaids IS 'Navaids, origially created from nav.dat';


--
-- Name: fgs_navaids_na_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_navaids_na_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_navaids_na_id_seq OWNER TO flightgear;

--
-- Name: fgs_navaids_na_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_navaids_na_id_seq OWNED BY public.fgs_navaids.na_id;


--
-- Name: fgs_news; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_news (
    ne_id integer NOT NULL,
    ne_timestamp timestamp without time zone NOT NULL,
    ne_author integer DEFAULT 0 NOT NULL,
    ne_text text NOT NULL
);


ALTER TABLE public.fgs_news OWNER TO flightgear;

--
-- Name: fgs_news_ne_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_news_ne_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_news_ne_id_seq OWNER TO flightgear;

--
-- Name: fgs_news_ne_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_news_ne_id_seq OWNED BY public.fgs_news.ne_id;


--
-- Name: fgs_objects; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_objects (
    ob_id integer NOT NULL,
    ob_modified timestamp without time zone,
    ob_deleted timestamp without time zone DEFAULT '1970-01-01 00:00:01'::timestamp without time zone NOT NULL,
    ob_text character varying(100),
    wkb_geometry public.geometry(Point,4326) NOT NULL,
    ob_gndelev numeric(7,2) DEFAULT '-9999'::integer,
    ob_elevoffset numeric(5,2) DEFAULT NULL::numeric,
    ob_peakelev numeric(7,2),
    ob_heading numeric(5,2) DEFAULT 0,
    ob_country character(2) DEFAULT NULL::bpchar,
    ob_model integer,
    ob_group integer,
    ob_tile integer,
    ob_reference character varying(20) DEFAULT NULL::character varying,
    ob_submitter character varying(16) DEFAULT 'unknown'::character varying,
    ob_valid boolean DEFAULT true,
    ob_class character varying(10),
    ob_modified_by integer,
    CONSTRAINT enforce_dims_wkb_geometry CHECK ((public.st_ndims(wkb_geometry) = 2)),
    CONSTRAINT enforce_geotype_wkb_geometry CHECK ((public.geometrytype(wkb_geometry) = 'POINT'::text)),
    CONSTRAINT enforce_srid_wkb_geometry CHECK ((public.st_srid(wkb_geometry) = 4326)),
    CONSTRAINT enforce_valid_wkb_geometry CHECK (public.st_isvalid(wkb_geometry))
);


ALTER TABLE public.fgs_objects OWNER TO flightgear;

--
-- Name: fgs_objects_ob_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_objects_ob_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_objects_ob_id_seq OWNER TO flightgear;

--
-- Name: fgs_objects_ob_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_objects_ob_id_seq OWNED BY public.fgs_objects.ob_id;


--
-- Name: fgs_position_requests; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_position_requests (
    spr_id integer NOT NULL,
    spr_hash character varying,
    spr_base64_sqlz character varying
);


ALTER TABLE public.fgs_position_requests OWNER TO flightgear;

--
-- Name: fgs_position_requests_spr_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_position_requests_spr_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_position_requests_spr_id_seq OWNER TO flightgear;

--
-- Name: fgs_position_requests_spr_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_position_requests_spr_id_seq OWNED BY public.fgs_position_requests.spr_id;


--
-- Name: fgs_procedures_pr_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_procedures_pr_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_procedures_pr_id_seq OWNER TO flightgear;

--
-- Name: fgs_procedures; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_procedures (
    pr_id integer DEFAULT nextval('public.fgs_procedures_pr_id_seq'::regclass) NOT NULL,
    pr_airport character varying(32) NOT NULL,
    pr_runways character varying(128),
    pr_name character varying(32) NOT NULL,
    pr_type public.fgs_procedure_types NOT NULL
);


ALTER TABLE public.fgs_procedures OWNER TO flightgear;

--
-- Name: fgs_signs; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_signs (
    si_id integer NOT NULL,
    si_modified timestamp without time zone,
    si_text character varying(100),
    wkb_geometry public.geometry NOT NULL,
    si_icao character(4) NOT NULL,
    si_gndelev numeric(7,2) DEFAULT '-9999.00'::numeric,
    si_heading numeric(5,2) DEFAULT 0.00,
    si_country character(2),
    si_definition character varying(60),
    si_tile integer,
    si_submitter character varying(16),
    si_valid boolean DEFAULT true,
    CONSTRAINT enforce_dims_wkb_geometry CHECK ((public.st_ndims(wkb_geometry) = 2)),
    CONSTRAINT enforce_geotype_wkb_geometry CHECK ((public.geometrytype(wkb_geometry) = 'POINT'::text)),
    CONSTRAINT enforce_srid_wkb_geometry CHECK ((public.st_srid(wkb_geometry) = 4326))
);


ALTER TABLE public.fgs_signs OWNER TO flightgear;

--
-- Name: fgs_signs_si_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_signs_si_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_signs_si_id_seq OWNER TO flightgear;

--
-- Name: fgs_signs_si_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.fgs_signs_si_id_seq OWNED BY public.fgs_signs.si_id;


--
-- Name: fgs_statistics; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_statistics (
    st_date date,
    st_objects bigint,
    st_models bigint,
    st_authors bigint,
    st_navaids bigint,
    st_signs bigint
);


ALTER TABLE public.fgs_statistics OWNER TO flightgear;

--
-- Name: fgs_timestamps; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_timestamps (
    ti_type integer,
    ti_stamp timestamp without time zone
);


ALTER TABLE public.fgs_timestamps OWNER TO flightgear;

--
-- Name: fgs_waypoints_wp_id_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.fgs_waypoints_wp_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fgs_waypoints_wp_id_seq OWNER TO flightgear;

--
-- Name: fgs_waypoints; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.fgs_waypoints (
    wp_id integer DEFAULT nextval('public.fgs_waypoints_wp_id_seq'::regclass) NOT NULL,
    wp_prid integer,
    wp_name character(32) NOT NULL,
    wp_type public.fgs_waypoint_type NOT NULL,
    wkb_geometry public.geometry(Point,4326) NOT NULL,
    wp_speed integer,
    wp_altitude integer,
    wp_altitude_cons integer,
    wp_altitude_restriction public.fgs_waypoint_altitude_restriction,
    wp_hold_inbound boolean,
    wp_hold_distance boolean,
    wp_hold_radial integer,
    wp_hold_righthand boolean,
    wp_hold_td numeric(4,1),
    wp_course_heading integer,
    wp_dme_distance numeric(4,1),
    wp_radial integer,
    wp_fly_over boolean,
    CONSTRAINT enforce_dims_wkb_geometry CHECK ((public.st_ndims(wkb_geometry) = 2)),
    CONSTRAINT enforce_geotype_wkb_geometry CHECK ((public.geometrytype(wkb_geometry) = 'POINT'::text)),
    CONSTRAINT enforce_srid_wkb_geometry CHECK ((public.st_srid(wkb_geometry) = 4326)),
    CONSTRAINT enforce_valid_wkb_geometry CHECK (public.st_isvalid(wkb_geometry))
);


ALTER TABLE public.fgs_waypoints OWNER TO flightgear;

--
-- Name: gadm2; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.gadm2 (
    ogc_fid integer NOT NULL,
    wkb_geometry public.geometry NOT NULL,
    objectid numeric(9,0),
    id_0 numeric(9,0),
    iso character varying(3),
    name_0 character varying(75),
    id_1 numeric(9,0),
    name_1 character varying(75),
    varname_1 character varying(150),
    nl_name_1 character varying(50),
    hasc_1 character varying(15),
    cc_1 character varying(15),
    type_1 character varying(50),
    engtype_1 character varying(50),
    validfr_1 character varying(25),
    validto_1 character varying(25),
    remarks_1 character varying(125),
    id_2 numeric(9,0),
    name_2 character varying(75),
    varname_2 character varying(150),
    nl_name_2 character varying(75),
    hasc_2 character varying(15),
    cc_2 character varying(15),
    type_2 character varying(50),
    engtype_2 character varying(50),
    validfr_2 character varying(25),
    validto_2 character varying(25),
    remarks_2 character varying(100),
    id_3 numeric(9,0),
    name_3 character varying(75),
    varname_3 character varying(100),
    nl_name_3 character varying(75),
    hasc_3 character varying(25),
    type_3 character varying(50),
    engtype_3 character varying(50),
    validfr_3 character varying(25),
    validto_3 character varying(25),
    remarks_3 character varying(50),
    id_4 numeric(9,0),
    name_4 character varying(100),
    varname_4 character varying(100),
    type4 character varying(25),
    engtype4 character varying(25),
    type_4 character varying(35),
    engtype_4 character varying(35),
    validfr_4 character varying(25),
    validto_4 character varying(25),
    remarks_4 character varying(50),
    id_5 numeric(9,0),
    name_5 character varying(75),
    type_5 character varying(25),
    engtype_5 character varying(25),
    shape_leng numeric(19,11),
    shape_area numeric(19,11),
    CONSTRAINT enforce_dims_wkb_geometry CHECK ((public.st_ndims(wkb_geometry) = 2)),
    CONSTRAINT enforce_geotype_wkb_geometry CHECK (((public.geometrytype(wkb_geometry) = 'POLYGON'::text) OR (public.geometrytype(wkb_geometry) = 'MULTIPOLYGON'::text))),
    CONSTRAINT enforce_srid_wkb_geometry CHECK ((public.st_srid(wkb_geometry) = 4326))
);


ALTER TABLE public.gadm2 OWNER TO flightgear;

--
-- Name: gadm2_meta; Type: TABLE; Schema: public; Owner: flightgear
--

CREATE TABLE public.gadm2_meta (
    iso character varying(3) NOT NULL,
    shape_sqm double precision
);


ALTER TABLE public.gadm2_meta OWNER TO flightgear;

--
-- Name: gadm2_ogc_fid_seq; Type: SEQUENCE; Schema: public; Owner: flightgear
--

CREATE SEQUENCE public.gadm2_ogc_fid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gadm2_ogc_fid_seq OWNER TO flightgear;

--
-- Name: gadm2_ogc_fid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: flightgear
--

ALTER SEQUENCE public.gadm2_ogc_fid_seq OWNED BY public.gadm2.ogc_fid;


--
-- Name: apt_runway ogc_fid; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.apt_runway ALTER COLUMN ogc_fid SET DEFAULT nextval('public.apt_runway_ogc_fid_seq'::regclass);


--
-- Name: fgs_aircraft ac_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_aircraft ALTER COLUMN ac_id SET DEFAULT nextval('public.fgs_aircraft_ac_id_seq'::regclass);


--
-- Name: fgs_airline al_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_airline ALTER COLUMN al_id SET DEFAULT nextval('public.fgs_airline_al_id_seq'::regclass);


--
-- Name: fgs_airport ap_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_airport ALTER COLUMN ap_id SET DEFAULT nextval('public.fgs_airport_ap_id_seq'::regclass);


--
-- Name: fgs_authors au_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_authors ALTER COLUMN au_id SET DEFAULT nextval('public.fgs_authors_au_id_seq'::regclass);


--
-- Name: fgs_clean ob_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_clean ALTER COLUMN ob_id SET DEFAULT nextval('public.fgs_clean_ob_id_seq'::regclass);


--
-- Name: fgs_fleet fl_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_fleet ALTER COLUMN fl_id SET DEFAULT nextval('public.fgs_fleet_fl_id_seq'::regclass);


--
-- Name: fgs_flight ft_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_flight ALTER COLUMN ft_id SET DEFAULT nextval('public.fgs_flight_ft_id_seq'::regclass);


--
-- Name: fgs_groups gp_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_groups ALTER COLUMN gp_id SET DEFAULT nextval('public.fgs_groups_gp_id_seq'::regclass);


--
-- Name: fgs_modelclass mc_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_modelclass ALTER COLUMN mc_id SET DEFAULT nextval('public.fgs_modelclass_mc_id_seq'::regclass);


--
-- Name: fgs_modelgroups mg_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_modelgroups ALTER COLUMN mg_id SET DEFAULT nextval('public.fgs_modelgroups_mg_id_seq'::regclass);


--
-- Name: fgs_navaids na_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_navaids ALTER COLUMN na_id SET DEFAULT nextval('public.fgs_navaids_na_id_seq'::regclass);


--
-- Name: fgs_news ne_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_news ALTER COLUMN ne_id SET DEFAULT nextval('public.fgs_news_ne_id_seq'::regclass);


--
-- Name: fgs_objects ob_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_objects ALTER COLUMN ob_id SET DEFAULT nextval('public.fgs_objects_ob_id_seq'::regclass);


--
-- Name: fgs_position_requests spr_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_position_requests ALTER COLUMN spr_id SET DEFAULT nextval('public.fgs_position_requests_spr_id_seq'::regclass);


--
-- Name: fgs_signs si_id; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_signs ALTER COLUMN si_id SET DEFAULT nextval('public.fgs_signs_si_id_seq'::regclass);


--
-- Name: gadm2 ogc_fid; Type: DEFAULT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.gadm2 ALTER COLUMN ogc_fid SET DEFAULT nextval('public.gadm2_ogc_fid_seq'::regclass);


--
-- Name: apt_runway apt_runway_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.apt_runway
    ADD CONSTRAINT apt_runway_pkey PRIMARY KEY (ogc_fid);


--
-- Name: country_codes country_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.country_codes
    ADD CONSTRAINT country_codes_pkey PRIMARY KEY (oid);


--
-- Name: fgs_authors fgs_authors_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_authors
    ADD CONSTRAINT fgs_authors_pkey PRIMARY KEY (au_id);


--
-- Name: fgs_clean fgs_clean_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_clean
    ADD CONSTRAINT fgs_clean_pkey PRIMARY KEY (ob_id);


--
-- Name: fgs_countries fgs_countries_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_countries
    ADD CONSTRAINT fgs_countries_pkey PRIMARY KEY (co_code);


--
-- Name: fgs_extuserids fgs_ext_auth_id_key; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_extuserids
    ADD CONSTRAINT fgs_ext_auth_id_key UNIQUE (eu_authority, eu_external_id);


--
-- Name: fgs_groups fgs_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_groups
    ADD CONSTRAINT fgs_groups_pkey PRIMARY KEY (gp_id);


--
-- Name: fgs_modelgroups fgs_modelgroups_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_modelgroups
    ADD CONSTRAINT fgs_modelgroups_pkey PRIMARY KEY (mg_id);


--
-- Name: fgs_models fgs_models_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_models
    ADD CONSTRAINT fgs_models_pkey PRIMARY KEY (mo_id);


--
-- Name: fgs_navaids fgs_navaids_na_id_key; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_navaids
    ADD CONSTRAINT fgs_navaids_na_id_key UNIQUE (na_id);


--
-- Name: fgs_news fgs_news_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_news
    ADD CONSTRAINT fgs_news_pkey PRIMARY KEY (ne_id);


--
-- Name: fgs_objects fgs_objects_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_objects
    ADD CONSTRAINT fgs_objects_pkey PRIMARY KEY (ob_id);


--
-- Name: fgs_position_requests fgs_position_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_position_requests
    ADD CONSTRAINT fgs_position_requests_pkey PRIMARY KEY (spr_id);


--
-- Name: fgs_signs fgs_signs_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.fgs_signs
    ADD CONSTRAINT fgs_signs_pkey PRIMARY KEY (si_id);


--
-- Name: gadm2_meta gadm2_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.gadm2_meta
    ADD CONSTRAINT gadm2_meta_pkey PRIMARY KEY (iso);


--
-- Name: gadm2 gadm2_pk; Type: CONSTRAINT; Schema: public; Owner: flightgear
--

ALTER TABLE ONLY public.gadm2
    ADD CONSTRAINT gadm2_pk PRIMARY KEY (ogc_fid);


--
-- Name: apt_runway_gindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX apt_runway_gindex ON public.apt_runway USING gist (wkb_geometry);

ALTER TABLE public.apt_runway CLUSTER ON apt_runway_gindex;


--
-- Name: apt_runway_icindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX apt_runway_icindex ON public.apt_runway USING btree (icao);


--
-- Name: apt_runway_rwyindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX apt_runway_rwyindex ON public.apt_runway USING btree (rwy_num1, rwy_num2);


--
-- Name: country_codes_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX country_codes_uindex ON public.country_codes USING btree (oid);


--
-- Name: dumpstg_objects; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX dumpstg_objects ON public.fgs_objects USING btree (ob_tile, ob_model, ob_gndelev, ob_modified);


--
-- Name: dumpstg_tiles; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX dumpstg_tiles ON public.fgs_signs USING btree (si_tile, si_valid, si_gndelev);


--
-- Name: fgs_authors_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_authors_uindex ON public.fgs_authors USING btree (au_id);

ALTER TABLE public.fgs_authors CLUSTER ON fgs_authors_uindex;


--
-- Name: fgs_clean_clindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_clindex ON public.fgs_clean USING btree (ob_class);


--
-- Name: fgs_clean_coindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_coindex ON public.fgs_clean USING btree (ob_country);


--
-- Name: fgs_clean_elindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_elindex ON public.fgs_clean USING btree (ob_gndelev);


--
-- Name: fgs_clean_gindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_gindex ON public.fgs_clean USING gist (wkb_geometry);


--
-- Name: fgs_clean_grindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_grindex ON public.fgs_clean USING btree (ob_group);


--
-- Name: fgs_clean_mdindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_mdindex ON public.fgs_clean USING btree (ob_model);


--
-- Name: fgs_clean_moindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_moindex ON public.fgs_clean USING btree (ob_modified);


--
-- Name: fgs_clean_rindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_rindex ON public.fgs_clean USING btree (ob_reference);


--
-- Name: fgs_clean_tindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_tindex ON public.fgs_clean USING btree (ob_tile);


--
-- Name: fgs_clean_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_clean_uindex ON public.fgs_clean USING btree (ob_id);


--
-- Name: fgs_clean_vindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_clean_vindex ON public.fgs_clean USING btree (ob_valid);


--
-- Name: fgs_countries_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_countries_uindex ON public.fgs_countries USING btree (co_code);

ALTER TABLE public.fgs_countries CLUSTER ON fgs_countries_uindex;


--
-- Name: fgs_extern_authority_id_index; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_extern_authority_id_index ON public.fgs_extuserids USING btree (eu_authority, eu_external_id);


--
-- Name: fgs_fixes_gindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_fixes_gindex ON public.fgs_fixes USING gist (wkb_geometry);


--
-- Name: fgs_fixes_nindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_fixes_nindex ON public.fgs_fixes USING btree (fx_name);


--
-- Name: fgs_groups_gpindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_groups_gpindex ON public.fgs_groups USING btree (gp_id);


--
-- Name: fgs_groups_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_groups_uindex ON public.fgs_groups USING btree (gp_id);

ALTER TABLE public.fgs_groups CLUSTER ON fgs_groups_uindex;


--
-- Name: fgs_modelgroups_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_modelgroups_uindex ON public.fgs_modelgroups USING btree (mg_id);

ALTER TABLE public.fgs_modelgroups CLUSTER ON fgs_modelgroups_uindex;


--
-- Name: fgs_models_auindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_models_auindex ON public.fgs_models USING btree (mo_author);


--
-- Name: fgs_models_moindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_models_moindex ON public.fgs_models USING btree (mo_modified);


--
-- Name: fgs_models_moshared; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_models_moshared ON public.fgs_models USING btree (mo_shared);


--
-- Name: fgs_models_paindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_models_paindex ON public.fgs_models USING btree (mo_path);


--
-- Name: fgs_models_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_models_uindex ON public.fgs_models USING btree (mo_id);

ALTER TABLE public.fgs_models CLUSTER ON fgs_models_uindex;


--
-- Name: fgs_news_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_news_uindex ON public.fgs_news USING btree (ne_timestamp);

ALTER TABLE public.fgs_news CLUSTER ON fgs_news_uindex;


--
-- Name: fgs_objects_clindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_clindex ON public.fgs_objects USING btree (ob_class);


--
-- Name: fgs_objects_coindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_coindex ON public.fgs_objects USING btree (ob_country);


--
-- Name: fgs_objects_elindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_elindex ON public.fgs_objects USING btree (ob_gndelev);


--
-- Name: fgs_objects_gindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_gindex ON public.fgs_objects USING gist (wkb_geometry);


--
-- Name: fgs_objects_grindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_grindex ON public.fgs_objects USING btree (ob_group);


--
-- Name: fgs_objects_mdindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_mdindex ON public.fgs_objects USING btree (ob_model);


--
-- Name: fgs_objects_moindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_moindex ON public.fgs_objects USING btree (ob_modified);


--
-- Name: fgs_objects_rindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_rindex ON public.fgs_objects USING btree (ob_reference);


--
-- Name: fgs_objects_tindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_tindex ON public.fgs_objects USING btree (ob_tile);


--
-- Name: fgs_objects_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_objects_uindex ON public.fgs_objects USING btree (ob_id);


--
-- Name: fgs_objects_vindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_objects_vindex ON public.fgs_objects USING btree (ob_valid);


--
-- Name: fgs_signs_coindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_signs_coindex ON public.fgs_signs USING btree (si_country);


--
-- Name: fgs_signs_elindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_signs_elindex ON public.fgs_signs USING btree (si_gndelev);


--
-- Name: fgs_signs_gindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_signs_gindex ON public.fgs_signs USING gist (wkb_geometry);

ALTER TABLE public.fgs_signs CLUSTER ON fgs_signs_gindex;


--
-- Name: fgs_signs_icindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_signs_icindex ON public.fgs_signs USING btree (si_icao);


--
-- Name: fgs_signs_moindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_signs_moindex ON public.fgs_signs USING btree (si_modified);


--
-- Name: fgs_signs_tindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX fgs_signs_tindex ON public.fgs_signs USING btree (si_tile);


--
-- Name: fgs_signs_uindex; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE UNIQUE INDEX fgs_signs_uindex ON public.fgs_signs USING btree (si_id);


--
-- Name: gadm2_geom_idx; Type: INDEX; Schema: public; Owner: flightgear
--

CREATE INDEX gadm2_geom_idx ON public.gadm2 USING gist (wkb_geometry);


--
-- Name: fgs_clean fgs_clean_modtime; Type: TRIGGER; Schema: public; Owner: flightgear
--

CREATE TRIGGER fgs_clean_modtime BEFORE INSERT OR UPDATE ON public.fgs_clean FOR EACH ROW EXECUTE PROCEDURE public.fn_setobjectmodtime();


--
-- Name: fgs_models fgs_models_modtime; Type: TRIGGER; Schema: public; Owner: flightgear
--

CREATE TRIGGER fgs_models_modtime BEFORE INSERT OR UPDATE ON public.fgs_models FOR EACH ROW EXECUTE PROCEDURE public.fn_setmodelmodtime();


--
-- Name: fgs_news fgs_news_modtime; Type: TRIGGER; Schema: public; Owner: flightgear
--

CREATE TRIGGER fgs_news_modtime BEFORE INSERT OR UPDATE ON public.fgs_news FOR EACH ROW EXECUTE PROCEDURE public.fn_setnewsmodtime();


--
-- Name: fgs_objects fgs_objects_modtime; Type: TRIGGER; Schema: public; Owner: flightgear
--

CREATE TRIGGER fgs_objects_modtime BEFORE INSERT OR UPDATE ON public.fgs_objects FOR EACH ROW EXECUTE PROCEDURE public.fn_setobjectmodtime();


--
-- Name: fgs_signs fgs_signs_modtime; Type: TRIGGER; Schema: public; Owner: flightgear
--

CREATE TRIGGER fgs_signs_modtime BEFORE INSERT OR UPDATE ON public.fgs_signs FOR EACH ROW EXECUTE PROCEDURE public.fn_setsignmodtime();


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: flightgear
--

REVOKE ALL ON SCHEMA public FROM rdsadmin;
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO flightgear;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: TABLE country_codes; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.country_codes TO updateuser;
GRANT SELECT ON TABLE public.country_codes TO webuser;


--
-- Name: TABLE fgs_aircraft; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_aircraft TO updateuser;
GRANT SELECT ON TABLE public.fgs_aircraft TO webuser;


--
-- Name: SEQUENCE fgs_aircraft_ac_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_aircraft_ac_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_aircraft_ac_id_seq TO webuser;


--
-- Name: TABLE fgs_airline; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_airline TO updateuser;
GRANT SELECT ON TABLE public.fgs_airline TO webuser;


--
-- Name: SEQUENCE fgs_airline_al_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_airline_al_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_airline_al_id_seq TO webuser;


--
-- Name: TABLE fgs_airport; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_airport TO updateuser;
GRANT SELECT ON TABLE public.fgs_airport TO webuser;


--
-- Name: SEQUENCE fgs_airport_ap_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_airport_ap_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_airport_ap_id_seq TO webuser;


--
-- Name: TABLE fgs_authors; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_authors TO updateuser;
GRANT SELECT,INSERT ON TABLE public.fgs_authors TO webuser;


--
-- Name: SEQUENCE fgs_authors_au_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_authors_au_id_seq TO updateuser;
GRANT SELECT,USAGE ON SEQUENCE public.fgs_authors_au_id_seq TO webuser;


--
-- Name: TABLE fgs_clean; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_clean TO updateuser;
GRANT SELECT ON TABLE public.fgs_clean TO webuser;


--
-- Name: SEQUENCE fgs_clean_ob_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_clean_ob_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_clean_ob_id_seq TO webuser;


--
-- Name: TABLE fgs_countries; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_countries TO updateuser;
GRANT SELECT ON TABLE public.fgs_countries TO webuser;


--
-- Name: TABLE fgs_extuserids; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_extuserids TO updateuser;
GRANT SELECT,INSERT,UPDATE ON TABLE public.fgs_extuserids TO webuser;


--
-- Name: TABLE fgs_fleet; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_fleet TO updateuser;
GRANT SELECT ON TABLE public.fgs_fleet TO webuser;


--
-- Name: SEQUENCE fgs_fleet_fl_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_fleet_fl_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_fleet_fl_id_seq TO webuser;


--
-- Name: TABLE fgs_flight; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_flight TO updateuser;
GRANT SELECT ON TABLE public.fgs_flight TO webuser;


--
-- Name: SEQUENCE fgs_flight_ft_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_flight_ft_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_flight_ft_id_seq TO webuser;


--
-- Name: TABLE fgs_groups; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_groups TO updateuser;
GRANT SELECT ON TABLE public.fgs_groups TO webuser;


--
-- Name: SEQUENCE fgs_groups_gp_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_groups_gp_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_groups_gp_id_seq TO webuser;


--
-- Name: TABLE fgs_modelclass; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_modelclass TO updateuser;
GRANT SELECT ON TABLE public.fgs_modelclass TO webuser;


--
-- Name: SEQUENCE fgs_modelclass_mc_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_modelclass_mc_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_modelclass_mc_id_seq TO webuser;


--
-- Name: TABLE fgs_modelgroups; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_modelgroups TO updateuser;
GRANT SELECT ON TABLE public.fgs_modelgroups TO webuser;


--
-- Name: SEQUENCE fgs_modelgroups_mg_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_modelgroups_mg_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_modelgroups_mg_id_seq TO webuser;


--
-- Name: SEQUENCE fgs_models_mo_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_models_mo_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_models_mo_id_seq TO webuser;


--
-- Name: TABLE fgs_models; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_models TO updateuser;
GRANT SELECT ON TABLE public.fgs_models TO webuser;


--
-- Name: TABLE fgs_navaids; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_navaids TO updateuser;
GRANT SELECT ON TABLE public.fgs_navaids TO webuser;


--
-- Name: TABLE fgs_news; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_news TO updateuser;
GRANT SELECT ON TABLE public.fgs_news TO webuser;


--
-- Name: SEQUENCE fgs_news_ne_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_news_ne_id_seq TO updateuser;


--
-- Name: TABLE fgs_objects; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_objects TO updateuser;
GRANT SELECT ON TABLE public.fgs_objects TO webuser;


--
-- Name: SEQUENCE fgs_objects_ob_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_objects_ob_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_objects_ob_id_seq TO webuser;


--
-- Name: TABLE fgs_position_requests; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_position_requests TO updateuser;
GRANT SELECT ON TABLE public.fgs_position_requests TO webuser;


--
-- Name: SEQUENCE fgs_position_requests_spr_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_position_requests_spr_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_position_requests_spr_id_seq TO webuser;


--
-- Name: TABLE fgs_signs; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_signs TO updateuser;
GRANT SELECT ON TABLE public.fgs_signs TO webuser;


--
-- Name: SEQUENCE fgs_signs_si_id_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.fgs_signs_si_id_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.fgs_signs_si_id_seq TO webuser;


--
-- Name: TABLE fgs_statistics; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.fgs_statistics TO updateuser;
GRANT SELECT ON TABLE public.fgs_statistics TO webuser;


--
-- Name: TABLE gadm2; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.gadm2 TO updateuser;
GRANT SELECT ON TABLE public.gadm2 TO webuser;


--
-- Name: TABLE gadm2_meta; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON TABLE public.gadm2_meta TO updateuser;
GRANT SELECT ON TABLE public.gadm2_meta TO webuser;


--
-- Name: SEQUENCE gadm2_ogc_fid_seq; Type: ACL; Schema: public; Owner: flightgear
--

GRANT ALL ON SEQUENCE public.gadm2_ogc_fid_seq TO updateuser;
GRANT SELECT ON SEQUENCE public.gadm2_ogc_fid_seq TO webuser;


--
-- PostgreSQL database dump complete
--

