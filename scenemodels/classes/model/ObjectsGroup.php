<?php

namespace model;

/**
 * Class representing a group that objects can be identified with.
 */
class ObjectsGroup {
    private $id;
    private $name;
    
    /**
     * Gets the group id.
     * 
     * @return int id of the group
     */
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
}

?>