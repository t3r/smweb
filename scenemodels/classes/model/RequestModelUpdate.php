<?php

namespace model;

/**
 * Model update request
 * 
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class RequestModelUpdate extends Request {
    private $newModel;
    private $oldModel;
    
    /**
     * Gets the proposed new model
     * @return Model new updated model
     */
    public function getNewModel() {
        return $this->newModel;
    }
    
    /**
     * Sets the new model
     * @param Model $newModel new model to set
     */
    public function setNewModel($newModel) {
        $this->newModel = $newModel;
    }
    
    /**
     * Gets the old model that may be updated
     * @return Model old model
     */
    public function getOldModel() {
        return $this->oldModel;
    }
    
    /**
     * Sets the old model
     * @param Model $oldModel old model to set
     */
    public function setOldModel($oldModel) {
        $this->oldModel = $oldModel;
    }
}

?>
