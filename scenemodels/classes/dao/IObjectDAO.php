<?php

/**
 * Interface for Object instance Data Access Object
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

namespace dao;

interface IObjectDAO {
    /**
     * Adds the given object to database.
     * @param Object $newObject
     * @return object with id
     */
    public function addObject(\model\TheObject $newObject);

    public function updateObject(\model\TheObject $newObject);
    
    public function deleteObject($objectId);
    
    public function getObject($objectId);
    
    public function getObjectsAt($long, $lat);
    
    public function getObjects($pagesize, $offset, $criteria, $orderby, $order);
    
    public function getObjectsByModel($modelId);
    
    public function getObjectsGroup($objectGroupId);
    
    public function getObjectsGroups();
    
    public function getCountry($countryCode);
    
    public function getCountryAt($long, $lat);
    
    public function getCountries();
    
    public function countObjects();
    
    public function countObjectsByModel($modelId);

    /**
     * Detects if a submitted object already exists in the database f(lat, lon, ob_gndelev, ob_heading, ob_model).
     * @param Object $object
     * @return true if the given object exists, false otherwise
     */
    public function checkObjectAlreadyExists($object);
    
    /**
     * Detects if an object exists in the database that is located (suspiciously) close to the submitted position
     * Nearby means (at the moment) within 15 meters.
     * 
     * @param double $lat
     * @param double $lon
     * @param int $obModelId
     * @param int $dist distance in meters (default 15)
     */
    public function detectNearbyObjects($lat, $lon, $obModelId, $dist = 15);
}

?>
