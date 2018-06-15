<?php

namespace model;

/**
 * Country class
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class Country {
    private $code;
    private $name;
    private $codeThree;
    
    /**
     * Gets the country's code
     * 
     * @return string
     */
    public function getCode() {
        return $this->code;
    }
    
    public function setCode($code) {
        $this->code = $code;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getCodeThree() {
        return $this->codeThree;
    }
    
    public function setCodeThree($codeThree) {
        $this->codeThree = $codeThree;
    }
}

?>