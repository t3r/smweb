<?php

namespace model;

/**
 * Object deletion request
 * 
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class RequestObjectDelete extends Request {
    private $objToDelete;
    
    public function getObjectToDelete() {
        return $this->objToDelete;
    }
    
    public function setObjectToDelete($objToDelete) {
        $this->objToDelete = $objToDelete;
    }
}

?>
