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
use \dao\Criterion as Criterion;

/**
 * Controller for object view
 */
class ObjectsController extends ControllerMenu {
    private $objectDaoRO;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->objectDaoRO = \dao\DAOFactory::getInstance()->getObjectDaoRO();
    }
    
    /**
     * Action for object view
     */
    public function viewAction() {
        $id = $this->getVar('id');
        if (\FormChecker::isObjectId($id)) {
            $object = $this->objectDaoRO->getObject($id);
            $modelMetadata = $this->getModelDaoRO()->getModelMetadata($object->getModelId());
            $group = $this->objectDaoRO->getObjectsGroup($object->getGroupId());
            include 'view/objectview.php';
        } else {
            $pageTitle = "Object ID not valid";
            $errorText = "Sorry, but the object ID you are asking is not valid.";
            include 'view/error_page.php';
        }
    }
    
    public function searchAction() {
        $criteria = array();
        $pagesize = 20;
        
        $offset = $this->getVar('offset');
        if ($offset == null || !preg_match(\FormChecker::$regex['pageoffset'], $offset)){
            $offset = 0;
        }

        $model = $this->getVar('model');
        if ($model != null && \FormChecker::isModelId($model)){
            $criteria[] = new Criterion("ob_model", Criterion::OPERATION_EQ, $model, Criterion::INTTYPE);
        } else {
            $model = "";
        }

        $groupid = $this->getVar('groupid');
        if ($groupid != null && \FormChecker::isObjectGroupId($groupid)){
            $criteria[] = new Criterion("ob_group", Criterion::OPERATION_EQ, $groupid, Criterion::INTTYPE);
        } else {
            $groupid = "";
        }

        $elevation = $this->getVar('elevation');
        if ($elevation != null && \FormChecker::isGndElevation($elevation)){
            $min = $elevation-25;
            $max = $elevation+25;

            $criteria[] = new Criterion("ob_gndelev", Criterion::OPERATION_GT, $min, Criterion::INTTYPE);
            $criteria[] = new Criterion("ob_gndelev", Criterion::OPERATION_LT, $max, Criterion::INTTYPE);
        } else {
            $elevation = "";
        }

        $elevoffset = $this->getVar('elevoffset');
        if ($elevoffset != null && \FormChecker::isOffset($elevoffset)){
            $min = $elevoffset-25;
            $max = $elevoffset+25;

            $criteria[] = new Criterion("ob_elevoffset", Criterion::OPERATION_GT, $min, Criterion::INTTYPE);
            $criteria[] = new Criterion("ob_elevoffset", Criterion::OPERATION_LT, $max, Criterion::INTTYPE);
        } else {
            $elevoffset = "";
        }

        $heading = $this->getVar('heading');
        if ($heading != null && \FormChecker::isHeading($heading)){
            $min = $heading-5;
            $max = $heading+5;

            $criteria[] = new Criterion("ob_heading", Criterion::OPERATION_GT, $min, Criterion::INTTYPE);
            $criteria[] = new Criterion("ob_heading", Criterion::OPERATION_LT, $max, Criterion::INTTYPE);
        } else {
            $heading = "";
        }

        $lat = $this->getVar('lat');
        if ($lat != null && \FormChecker::isLatitude($lat)){
            $criteria[] = new Criterion("CAST (ST_Y(wkb_geometry) AS text)", Criterion::OPERATION_LIKE_BEGIN, $lat, Criterion::INTTYPE);
        } else {
            $lat = "";
        }

        $lon = $this->getVar('lon');
        if ($lon != null && \FormChecker::isLongitude($lon)){
            $criteria[] = new Criterion("CAST (ST_X(wkb_geometry) AS text)", Criterion::OPERATION_LIKE_BEGIN, $lon, Criterion::INTTYPE);
        } else {
            $lon = "";
        }

        $countryId = $this->getVar('country');
        if ($countryId != null && \FormChecker::isCountryId($countryId)){
            $criteria[] = new Criterion("ob_country", Criterion::OPERATION_EQ, $countryId, Criterion::STRINGTYPE);
        } else {
            $countryId = "";
        }

        $description = $this->getVar('description');
        if ($description != null && \FormChecker::isObtext($description)){
            $criteria[] = new Criterion("ob_text", Criterion::OPERATION_LIKE, $_REQUEST['description'], Criterion::STRINGTYPE);
        } else {
            $description = "";
        }
        
        $modelPaths = $this->getModelDaoRO()->getPaths();
        $objectsGroups = $this->objectDaoRO->getObjectsGroups();
        $countries = $this->objectDaoRO->getCountries();
        $objects = $this->objectDaoRO->getObjects($pagesize, $offset, $criteria);
        
        $modelIsStaticMap = array();
        foreach ($objects as $object) {
            $modelIsStaticMap[$object->getModelId()] =
                    $this->getModelDaoRO()->getModelMetadata($object->getModelId())->getModelsGroup()->isStatic();
        }
        
        include 'view/objects.php';
    }
}