<?php

/*
 * Copyright (C) 2015 FlightGear Team
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

namespace controller;

/**
 * Common RequestController
 *
 * @author Julien Nguyen
 */
class RequestController extends ControllerMenu {
    
    public function getGroupModelsMDXMLAction() {
        $mgId = $this->getVar('mg_id');
        if (\FormChecker::isModelGroupId($mgId)) {
            $modelMDs = $this->getModelDaoRO()->getModelMetadatasByGroup($mgId, 0, "ALL", "mo_path", "ASC");
            include 'view/submission/models_xml.php';
        }
    }
    
    /**
     * Get model information Action.
     */
    public function getModelInfoXMLAction() {
        $moId = $this->getVar('mo_id');
        
        if (!empty($moId)) {
            $modelMD = $this->getModelDaoRO()->getModelMetadata($moId);
            include 'view/submission/model_info_xml.php';
        }
    }
    
    /**
     * Get country code at given position Action.
     */
    public function getCountryCodeAtXMLAction() {
        $long = $this->getVar('lg');
        $lat = $this->getVar('lt');
        
        $objectDaoRO = \dao\DAOFactory::getInstance()->getObjectDaoRO();
        $code = $objectDaoRO->getCountryAt($long, $lat)->getCode();
        include 'view/submission/country_xml.php';
    }
}
