<?php

namespace model;

/**
 * Massive objects addition request
 * 
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class RequestMassiveObjectsAdd extends Request {
    private $newObjects;
    
    public function getNewObjects() {
        return $this->newObjects;
    }
    
    public function setNewObjects($newObjects) {
        $this->newObjects = $newObjects;
    }

}

?>
