<?php

/**
 * Object Data Access Object implementation for PostgreSQL
 *
 * Database layer to access objects from PostgreSQL database
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

namespace dao;

class ObjectDAO extends PgSqlDAO implements IObjectDAO {    
    public function addObject(\model\TheObject $obj) {
        $objPos = $obj->getPosition();
        $obOffset = $objPos->getElevationOffset();
        
        $query = "INSERT INTO fgs_objects (ob_id, ob_text, wkb_geometry, ob_gndelev, ob_elevoffset, ob_heading, ob_country, ob_model, ob_group) ".
                "VALUES (DEFAULT, '".pg_escape_string($obj->getDescription())."', ST_PointFromText('POINT(".pg_escape_string($objPos->getLongitude())." ".pg_escape_string($objPos->getLatitude()).")', 4326), -9999, ".
                (($obOffset == 0 || $obOffset == '')?"NULL":pg_escape_string($obOffset)) .
                ", ".pg_escape_string($objPos->getOrientation()).", '".pg_escape_string($obj->getCountry()->getCode())."', ".pg_escape_string($obj->getModelId()).", 1) RETURNING ob_id;";
    
        $result = $this->database->query($query);
        
        if (!$result) {
            throw new \Exception("Adding object failed!");
        }
        
        $returnRow = pg_fetch_row($result);
        $obj->setId($returnRow[0]);
        return $obj;
    }

    public function updateObject(\model\TheObject $object) {
        $objPos = $object->getPosition();
        
        $query = "UPDATE fgs_objects ".
                 "SET ob_text=$$".pg_escape_string($object->getDescription())."$$, ".
                 "wkb_geometry=ST_PointFromText('POINT(".pg_escape_string($objPos->getLongitude())." ".pg_escape_string($objPos->getLatitude()).")', 4326),".
                 "ob_country='".pg_escape_string($object->getCountry()->getCode())."',".
                 "ob_gndelev=-9999, ob_elevoffset=".pg_escape_string($objPos->getElevationOffset()).", ob_heading=".pg_escape_string($objPos->getOrientation()).", ob_model=".pg_escape_string($object->getModelId()).", ob_group=1 ".
                 "WHERE ob_id=".pg_escape_string($object->getId()).";";
        
        $result = $this->database->query($query);
        
        if (!$result) {
            throw new \Exception('Updating object failed!');
        }
    }
    
    public function deleteObject($objectId) {
        $result = $this->database->query("DELETE FROM fgs_objects WHERE ob_id=".pg_escape_string($objectId).";");
        
        if (!$result) {
            throw new \Exception('Deleting object id '.$objectId.' failed!');
        }
    }
    
    public function getObject($objectId) {
        $result = $this->database->query("SELECT *, ST_Y(wkb_geometry) AS ob_lat, ST_X(wkb_geometry) AS ob_lon, fn_SceneDir(wkb_geometry) AS ob_dir FROM fgs_objects ".
                                         "LEFT JOIN fgs_countries ON ob_country = co_code WHERE ob_id=".pg_escape_string($objectId));
        
        $objectRow = pg_fetch_assoc($result);
        
        if (!$objectRow) {
            throw new \Exception('No object with id '. $objectId. ' was found!');
        }
        
        return $this->getObjectFromRow($objectRow);
    }
    
    public function getObjectsAt($long, $lat) {
        $result = $this->database->query("SELECT *, ST_Y(wkb_geometry) AS ob_lat, ST_X(wkb_geometry) AS ob_lon, fn_SceneDir(wkb_geometry) AS ob_dir ".
                                         "FROM fgs_objects, fgs_countries WHERE wkb_geometry = ST_PointFromText('POINT(".pg_escape_string($long)." ".pg_escape_string($lat).")', 4326) AND ob_country = co_code;");
    
        $resultArray = array();
                           
        while ($row = pg_fetch_assoc($result)) {
            $resultArray[] = $this->getObjectFromRow($row);
        }
        
        return $resultArray;
    }
    
    public function getObjects($pagesize, $offset, $criteria=null, $orderby="ob_modified", $order="DESC") {
        $whereClause = $this->generateWhereClauseCriteria($criteria);
        
        if ($whereClause != "") {
            $whereClause .= ' AND'; 
        }
    
        $result = $this->database->query('SELECT *, ST_Y(wkb_geometry) AS ob_lat, ST_X(wkb_geometry) AS ob_lon, fn_SceneDir(wkb_geometry) AS ob_dir '.
                                         'FROM fgs_objects, fgs_countries WHERE '.$whereClause.' ob_country = co_code '.
                                         'ORDER BY '.pg_escape_string($orderby).' '.pg_escape_string($order).' LIMIT '.pg_escape_string($pagesize).' OFFSET '.pg_escape_string($offset).';');
        $resultArray = array();
        
        while ($row = pg_fetch_assoc($result)) {
            $resultArray[] = $this->getObjectFromRow($row);
        }
        
        return $resultArray;
    }
    
    public function getObjectsByModel($modelId) {
        $result = $this->database->query('SELECT *, ST_Y(wkb_geometry) AS ob_lat, ST_X(wkb_geometry) AS ob_lon, fn_SceneDir(wkb_geometry) AS ob_dir '.
                                         'FROM fgs_objects, fgs_countries WHERE ob_model='.pg_escape_string($modelId).' AND ob_country = co_code '.
                                         'ORDER BY ob_modified DESC;');
        $resultArray = array();
                           
        while ($row = pg_fetch_assoc($result)) {
            $resultArray[] = $this->getObjectFromRow($row);
        }
        
        return $resultArray;
    }
    
    public function getObjectsGroup($objectGroupId) {
        $result = $this->database->query('SELECT gp_id, gp_name FROM fgs_groups '.
                                         'WHERE gp_id='.pg_escape_string($objectGroupId).';');
        
        $row = pg_fetch_assoc($result);
        return $this->getObjectGroupFromRow($row);
    }
    
    public function getObjectsGroups() {
        $result = $this->database->query('SELECT gp_id, gp_name FROM fgs_groups;');
        
        $resultArray = array();
        
        while ($row = pg_fetch_assoc($result)) {
            $resultArray[] = $this->getObjectGroupFromRow($row);
        }
        
        return $resultArray;
    }
    
    public function getCountry($countryCode) {
        $result = $this->database->query("SELECT * FROM fgs_countries WHERE co_code='". pg_escape_string($countryCode) ."';");
              
        $row = pg_fetch_assoc($result);
        return $this->getCountryFromRow($row);
    }
    
    public function getCountryAt($long, $lat) {
        $query = 'SELECT co_code, co_name, co_three FROM gadm2, fgs_countries ' .
                 "WHERE ST_Within(ST_PointFromText('POINT(".
                 pg_escape_string($long)." ".pg_escape_string($lat).")', 4326), gadm2.wkb_geometry) AND gadm2.iso ILIKE fgs_countries.co_three;";
        $result = $this->database->query($query);

        $row = pg_fetch_assoc($result);
        // If not found, return Unknown
        if (!$row) {
            return $this->getCountry('zz');
        }
        
        return $this->getCountryFromRow($row);
    }
    
    public function getCountries() {
        $result = $this->database->query('SELECT * FROM fgs_countries ORDER BY co_name;');
        
        $resultArray = array();
                           
        while ($row = pg_fetch_assoc($result)) {
            $country = $this->getCountryFromRow($row);
            $resultArray[$country->getCode()] = $country;
        }
        
        return $resultArray;
    }
    
    public function countObjects() {
        $result = $this->database->query('SELECT count(*) AS number FROM fgs_objects;');
        $row = pg_fetch_assoc($result);
        
        return $row['number'];
    }
    
    public function countObjectsByModel($modelId) {
        $result = $this->database->query('SELECT COUNT(*) AS number ' .
                                        'FROM fgs_objects ' .
                                        'WHERE ob_model='.pg_escape_string($modelId).';');
        $row = pg_fetch_assoc($result);
        
        return $row['number'];
    }
    
    private function getObjectFromRow($objectRow) {
        $country = $this->getCountryFromRow($objectRow);
        
        $object = new \model\TheObject();
        $object->setId($objectRow['ob_id']);
        $object->setModelId($objectRow['ob_model']);
        $object->getPosition()->setLongitude($objectRow['ob_lon']);
        $object->getPosition()->setLatitude($objectRow['ob_lat']);
        $object->setDir($objectRow['ob_dir']);
        $object->setCountry($country);
        $object->getPosition()->setGroundElevation($objectRow['ob_gndelev']);
        $object->getPosition()->setElevationOffset($objectRow['ob_elevoffset']);
        $object->getPosition()->setOrientation($objectRow['ob_heading']);
        $object->setDescription($objectRow['ob_text']);
        $object->setGroupId($objectRow['ob_group']);
        $object->setLastUpdated(new \DateTime($objectRow['ob_modified']));
        
        return $object;
    }
    
    private function getCountryFromRow($countryRow) {
        $country = new \model\Country();
        $country->setCode($countryRow['co_code']);
        $country->setName($countryRow['co_name']);
        $country->setCodeThree($countryRow['co_three']);
        
        return $country;
    }
    
    private function getObjectGroupFromRow($objGroupRow) {
        $objectsGroup = new \model\ObjectsGroup();
        $objectsGroup->setId($objGroupRow['gp_id']);
        $objectsGroup->setName($objGroupRow['gp_name']);
        
        return $objectsGroup;
    }

    public function checkObjectAlreadyExists($object) {
        $objPos = $object->getPosition();
        
        // Querying...
        $query = "SELECT count(*) AS number FROM fgs_objects WHERE wkb_geometry = ST_PointFromText('POINT(".pg_escape_string($objPos->getLongitude())." ".pg_escape_string($objPos->getLatitude()).")', 4326) AND ";
        if ($objPos->getElevationOffset() == 0) {
            $query .= 'ob_elevoffset IS NULL ';
        } else {
            $query .= 'ob_elevoffset = '.$objPos->getElevationOffset().' ';
        }
        $query .= 'AND ob_heading = '.pg_escape_string($objPos->getOrientation()).
                ' AND ob_model = '.pg_escape_string($object->getModelId()).';';
        
        $result = $this->database->query($query);
        $row = pg_fetch_assoc($result);

        return $row['number'] > 0;
    }
    
    public function detectNearbyObjects($lat, $lon, $obModelId, $dist = 15) {
        // Querying...
        $query = "SELECT fn_getnearestobject(".pg_escape_string($obModelId).","
                                              .pg_escape_string($lon).","
                                              .pg_escape_string($lat).")";
        return ($row[0] > 0);
    }
}

?>
