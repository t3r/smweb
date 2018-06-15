<?php

namespace model;

/**
 * Model
 *
 * Contains metadata and model files.
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class Model {
    private $modelMetadata;
    private $modelFiles;
    private $thumbnail;
    
    function __construct() {
    }
    
    public function getModelFiles() {
        return $this->modelFiles;
    }
    
    public function setModelFiles($modelFiles) {
        $this->modelFiles = $modelFiles;
    }
    
    public function getMetadata() {
        return $this->modelMetadata;
    }
    
    public function setMetadata(ModelMetadata $modelMetadata) {
        $this->modelMetadata = $modelMetadata;
    }
    
    public function getThumbnail() {
        return $this->thumbnail;
    }
    
    public function setThumbnail($thumbnail) {
        $this->thumbnail = $thumbnail;
    }
}

?>
