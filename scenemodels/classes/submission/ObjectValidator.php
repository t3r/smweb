<?php

/*
 * Copyright (C) 2015 Flightgear Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace submission;

/**
 * Object validator
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 */
class ObjectValidator implements Validator {
    
    private $modelId;
    private $longitude;
    private $latitude;
    private $countryId;
    private $offset;
    private $heading;
    
    public static function getObjectValidator($modelId, $longitude, $latitude, $countryId, $offset, $heading) {
        $instance = new self();
        $instance->setModelId($modelId);
        $instance->setPosition($longitude, $latitude, $countryId, $offset, $heading);
        
        return $instance;
    }
    
    public static function getPositionValidator($longitude, $latitude, $countryId, $offset, $heading) {
        $instance = new self();
        $instance->setPosition($longitude, $latitude, $countryId, $offset, $heading);
        
        return $instance;
    }
    
    private function setPosition($longitude, $latitude, $countryId, $offset, $heading) {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->countryId = $countryId;
        $this->offset = $offset;
        $this->heading = $heading;
    }
    
    private function setModelId($modelId) {
        $this->modelId = $modelId;
    }
    
    public function validate() {
        $exceptions = array();
        
        if (isset($this->modelId) && !\FormChecker::isModelId($this->modelId)) {
            $exceptions[] = new \model\ErrorInfo("Model ID is wrong!");
        }
        
        if (!\FormChecker::isLatitude($this->latitude)) {
            $exceptions[] = new \model\ErrorInfo("Latitude is wrong!");
        }
        
        if (!\FormChecker::isLongitude($this->longitude)) {
            $exceptions[] = new \model\ErrorInfo("Longitude is wrong!");
        }
        
        if (!\FormChecker::isCountryId($this->countryId)) {
            $exceptions[] = new \model\ErrorInfo("Country ID is wrong!");
        }

        if (!\FormChecker::isOffset($this->offset)) {
            $exceptions[] = new \model\ErrorInfo("Elevation offset is wrong!");
        }
        
        if (!\FormChecker::isHeading($this->heading)) {
            $exceptions[] = new \model\ErrorInfo("Heading is wrong!");
        }

        return $exceptions;
    }
}
