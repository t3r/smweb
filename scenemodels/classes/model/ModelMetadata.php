<?php

namespace model;

/**
 * Model metadata
 *
 * Contains metadata of a model only (Model files are not included).
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class ModelMetadata {
    private $modelId = 0;
    private $name;
    private $description;
    private $filename;
    private $author;
    private $modelsGroup;
    private $lastUpdated;
    
    /**
     * Gets the model's author
     * 
     * @return Author the model's author
     */
    public function getAuthor() {
        return $this->author;
    }
    
    public function setAuthor($author) {
        $this->author = $author;
    }
    
    public function getFilename() {
        return $this->filename;
    }
    
    public function setFilename($filename) {
        $this->filename = $filename;
    }
    
    /**
     * Gets the model id
     * 
     * @return int model id
     */
    public function getId() {
        return $this->modelId;
    }
    
    public function setId($modelId) {
        $this->modelId = $modelId;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function getModelsGroup() {
        return $this->modelsGroup;
    }
    
    public function setModelsGroup($modelsGroup) {
        $this->modelsGroup = $modelsGroup;
    }
    
    public function getLastUpdated() {
        return $this->lastUpdated;
    }
    
    public function setLastUpdated($lastUpdated) {
        $this->lastUpdated = $lastUpdated;
    }
}

?>