<?php

/*
 * Copyright (C) 2014 Flightgear Team
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

/**
 * ObjectFactory
 *
 * @author Julien Nguyen
 */
class ObjectFactory {
    private $objectDaoRO;
    
    public function __construct($objectDaoRO) {
        $this->objectDaoRO = $objectDaoRO;
    }
    
    public function createObject($id, $modelId, $lon, $lat, $countryCode,
            $elevOffset, $orientation, $group, $desc) {
        $country = $this->objectDaoRO->getCountry($countryCode);
        
        $object = new \model\Object();
        $object->setId($id);
        $object->setModelId($modelId);
        $object->getPosition()->setLongitude($lon);
        $object->getPosition()->setLatitude($lat);
        $object->setCountry($country);
        $object->getPosition()->setGroundElevation(-9999);
        $object->getPosition()->setElevationOffset($elevOffset);
        $object->getPosition()->setOrientation($orientation);
        $object->setDescription($desc);
        $object->setGroupId($group);
        
        return $object;
    }
}
