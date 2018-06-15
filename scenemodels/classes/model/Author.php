<?php

namespace model;

/**
 * Author (for models and news)
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class Author {
    private $authorId;
    private $name;
    private $email;
    private $description;
    
    function __construct() {
    }
    
    public function getId() {
        return $this->authorId;
    }
    
    public function setId($authorId) {
        return $this->authorId = $authorId;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        return $this->name = $name;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        return $this->email = $email;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setDescription($description) {
        return $this->description = $description;
    }
}

?>