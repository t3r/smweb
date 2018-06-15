<?php

namespace model;

/**
 * Object update request
 * 
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class RequestObjectUpdate extends Request {
    private $newObject;
    private $oldObject;
    
    public function getNewObject() {
        return $this->newObject;
    }
    
    public function setNewObject($newObject) {
        $this->newObject = $newObject;
    }
    
    public function getOldObject() {
        return $this->oldObject;
    }
    
    public function setOldObject($oldObject) {
        $this->oldObject = $oldObject;
    }
}

?>