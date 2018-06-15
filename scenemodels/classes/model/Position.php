<?php

namespace model;

/**
 * Position
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2015 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class Position {
    private $longitude;
    private $latitude;
    private $orientation;
    private $groundElevation;
    private $elevationOffset;
    
    /**
     * Gets longitude
     * @return float longitude
     */
    public function getLongitude() {
        return $this->longitude;
    }
    
    /**
     * Sets longitude
     * @param float $longitude
     */
    public function setLongitude($longitude) {
        $this->longitude = $longitude;
    }
    
    /**
     * Gets latitude
     * @return float latitude
     */
    public function getLatitude() {
        return $this->latitude;
    }
    
    /**
     * Sets latitude
     * @param float $latitude
     */
    public function setLatitude($latitude) {
        $this->latitude = $latitude;
    }
    
    /**
     * Gets true orientation
     * @return true orientation
     */
    public function getOrientation() {
        return $this->orientation;
    }
    
    public function setOrientation($orientation) {
        $this->orientation = $orientation;
    }
    
    public function getGroundElevation() {
        return $this->groundElevation;
    }
    
    public function setGroundElevation($groundElevation) {
        $this->groundElevation = $groundElevation;
    }
    
    /**
     * Get the elevation offset.
     * and ground elevation.
     * @return int elevation offset
     */
    public function getElevationOffset() {
        if (!$this->elevationOffset) {
            return 0;
        } else {
            return $this->elevationOffset;
        }
    }
    
    public function setElevationOffset($elevationOffset) {
        $this->elevationOffset = $elevationOffset;
    }
}
