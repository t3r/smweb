<?php

namespace model;

/**
 * Models group
 *
 * Contains information about a group of models
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class ModelsGroup {
    private $id;
    private $name;
    private $path;
    
    function __construct() {
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        return $this->id = $id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        return $this->name = $name;
    }
    
    public function getPath() {
        return $this->path;
    }
    
    public function setPath($path) {
        return $this->path = $path;
    }
    
    public function isStatic() {
        return $this->id == 0;
    }
}

?>