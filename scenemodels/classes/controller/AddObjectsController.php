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
 * Object addition controller
 *
 * @author Julien Nguyen
 */
class AddObjectsController extends RequestController {
    private $objectDaoRO;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->objectDaoRO = \dao\DAOFactory::getInstance()->getObjectDaoRO();
    }

    /**
     * Display form action
     */
    public function formAction() {
        parent::menu();
        
        // Show all the families other than the static family
        $modelsGroups = $this->getModelsGroups();
        $countries = $this->objectDaoRO->getCountries();
        $nbObjects = $this->objectDaoRO->countObjects();
        
        $defaultLat = "";
        if (\FormChecker::isLatitude($this->getVar('lat'))) {
            $defaultLat = $this->getVar('lat');
        }
        
        $defaultLon = "";
        if (\FormChecker::isLongitude($this->getVar('lon'))) {
            $defaultLon = $this->getVar('lon');
        }

        include 'view/submission/object/add_object_form.php';
    }
    
    public function massiveformAction() {
        parent::menu();
        
        // Show all the families other than the static family
        $modelsGroups = $this->getModelsGroups();
        $countries = $this->objectDaoRO->getCountries();
        $nbObjects = $this->objectDaoRO->countObjects();

        include 'view/submission/object/mass_add_object_form.php';
    }
    
    /**
     * Check submitted object action
     */
    public function checkAction() {
        // Check captcha
        if (!$this->checkCaptcha()) {
            $this->displayCaptchaError();
            return;
        }
        
        $error = false;
        $errors = array();
        $newObjects = array();
        $objectLinesRequests = array();
        $modelMDs = array();
        $requestDaoRW = \dao\DAOFactory::getInstance()->getRequestDaoRW();

        $i = 1;
        while ($this->getVar('modelId'.$i) != null) {
            $modelId = stripslashes($this->getVar('modelId'.$i));
            $lat = number_format(stripslashes($this->getVar('lat'.$i)),7,'.','');
            $long = number_format(stripslashes($this->getVar('long'.$i)),7,'.','');
            $countryId = $this->getVar('countryId'.$i);
            $offset = number_format(stripslashes($this->getVar('offset'.$i)),2,'.','');
            $heading = number_format(stripslashes($this->getVar('heading'.$i)),1,'.','');

            $objectValidator = \submission\ObjectValidator::getObjectValidator($modelId, $long, $lat, $countryId, $offset, $heading);
            $objErrors = $objectValidator->validate();
            
            if (empty($objErrors)) {
                $modelMD = $this->getModelDaoRO()->getModelMetadata($modelId);
                $modelMDs[$modelId] = $modelMD;

                $objectFactory = new \ObjectFactory($this->objectDaoRO);
                $newObject = $objectFactory->createObject(-1, $modelId, $long, $lat, $countryId, 
                $offset, \ObjectUtils::headingSTG2True($heading), 1, $modelMD->getName());

                // Detect if the object is already in the database
                if ($this->objectDaoRO->checkObjectAlreadyExists($newObject)) {
                    $objErrors[] = new \model\ErrorInfo("The object already exists in the database!");
                    $error = true;
                }
                
                $newObjects[] = $newObject;
                $objectLineRequest = new \model\ObjectLineRequest();
                $objectLineRequest->setObject($newObject);
                $objectLineRequest->setErrors($objErrors);
                $objectLinesRequests[$i] = $objectLineRequest;
            } else {
                $error = true;
            }
            
            $i++;
        }
        
        // Checking that comment exists. Just a small verification as it's not going into DB.
        $inputComment = stripslashes($this->getVar('comment'));
        if ($inputComment != '' && \FormChecker::isComment($inputComment)) {
            $sentComment = $inputComment;
        }
        else {
            $errors[] = new \model\ErrorInfo("Comment mismatch!");
            $error = true;
        }
        
        // Checking that email is valid.
        //(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        $inputEmail = $this->getVar('email');
        if ($inputEmail != '' && \FormChecker::isEmail($inputEmail)) {
            $safeEmail = htmlentities(stripslashes($inputEmail));
        }
        
        // If there is no error, insert the object to the pending requests table.
        if (!$error) {
            
            $request = new \model\RequestMassiveObjectsAdd();
            $request->setNewObjects($newObjects);
            if (isset($safeEmail)) {
                $request->setContributorEmail($safeEmail);
            }
            $request->setComment($sentComment);
            
            try {
                $updatedReq = $requestDaoRW->saveRequest($request);
            } catch (\Exception $e) {
                $pageTitle = "Objects addition Form";
                $errorText = "Sorry, but the query could not be processed. Please ask for help on the <a href='http://www.flightgear.org/forums/viewforum.php?f=5'>Scenery forum</a> or on the devel list.<br />";
                include 'view/error_page.php';
                return;
            }
            
            // Sending mail if there is no false and SQL was correctly inserted.
            // Sets the time to UTC.
            date_default_timezone_set('UTC');
            $dtg = date('l jS \of F Y h:i:s A');

            // Retrieving the IP address of the submitter (takes some time to resolve the IP address though).
            $ipaddr = $_SERVER["REMOTE_ADDR"];
            $host = gethostbyaddr($ipaddr);
            
            $emailSubmit = \email\EmailContentFactory::getObjectsAddRequestPendingEmailContent($dtg, $ipaddr, $host, $updatedReq);
            $emailSubmit->sendEmail("", true);

            // Mailing the submitter to tell that his submission has been sent for validation.
            if (isset($safeEmail)) {
                $emailSubmit = \email\EmailContentFactory::getObjectsAddSentForValidationEmailContent($ipaddr, $host, $dtg, $updatedReq);
                $emailSubmit->sendEmail($safeEmail, false);
            }
        }

        include 'view/submission/object/check_add.php';
    }
    
    /**
     * Checks if models exists in DB from a model name sent in parameter.
     * @global type $modelDaoRO
     * @param string $modelFullPath Model's path is composed of:
     *        OBJECT_SHARED Models/mg_path from fgs_modelgroups/mo_path from fgs_models
     *        ie : Models/Power/windturbine.xml
     * @return ModelMetadata
     * @throws Exception if model is not found
     */
    private function getModelFromSTG($modelFullPath) {
        // Explodes the fields of the string separated by /
        $tabPath = explode("/", $modelFullPath);

        // Returns the last field value.
        $filename = $tabPath[count($tabPath)-1];

        // Get the model (throw exception if not found)
        $modelMD = $this->getModelDaoRO()->getModelMetadataFromSTGName($filename);

        return $modelMD;
    }

    private function createObjectLineRequest($line, $objectFactory) {
        $objectLineRequest = new \model\ObjectLineRequest();
        $objectLineRequest->setStgLine($line);

        $elevoffset = 0;
        $lineValues = explode(" ", $line);

        $errors = array();
        $warnings = array();

        // TODO : Have also to check the number of tab_tags returned!

        // Checking Label (must contain only letters and be strictly labelled OBJECT_SHARED for now)
        if (strcmp($lineValues[0], "OBJECT_SHARED") != 0) {
            $errors[] = new \model\ErrorInfo("Only OBJECT_SHARED is supported!");
        }

        // Checking model (Contains only figures, letters, _/. and must exist in DB)
        $path = $lineValues[1];
        if (\FormChecker::isFilePath($path)) {
            try {
                $modelMD = $this->getModelFromSTG($path);
                $modelId = $modelMD->getId();
            } catch (\Exception $ex) {
                $errors[] = $ex;
            }
        }
        else {
            $errors[] = new \model\ErrorInfo("Model Error!");
        }

        // Longitude
        $long = $lineValues[2];

        // Latitude
        $lat = $lineValues[3];

        // Elevation (TODO: can be used to automatically compute offset!!)
        //$gndelev = $value_tag;

        // Orientation
        $orientation = $lineValues[5];

        //If 7 columns, it's the offset. if 8 columns, it's pitch
        if (count($lineValues) == 7) {
            $elevoffset = $lineValues[6];
        }

        // Country
        $countryId = $this->objectDaoRO->getCountryAt($long, $lat)->getCode();

        $objectValidator = \submission\ObjectValidator::getObjectValidator($modelId, $long, $lat, $countryId, $elevoffset, $orientation);
        $errors = array_merge($errors, $objectValidator->validate());

        if (empty($errors)) {
            $newObject = $objectFactory->createObject(-1, $modelId, $long, $lat, $countryId, 
                        $elevoffset, \ObjectUtils::headingSTG2True($orientation), 1, $modelMD->getName());
            $objectLineRequest->setObject($newObject);

            if ($this->objectDaoRO->checkObjectAlreadyExists($newObject)) {
                $errors[] = new \model\ErrorInfo('Object exists already!');
            } else if ($this->objectDaoRO->detectNearbyObjects($lat, $long, $modelId)) {
                $warnings[] = new \model\ErrorInfo('Nearby object');
            }
        }

        $objectLineRequest->setErrors($errors);
        $objectLineRequest->setWarnings($warnings);

        return $objectLineRequest;
    }
    
    function confirmMassAction() {
        // Checking that email is valid (if it exists).
        if (\FormChecker::isEmail($this->getVar('email'))) {
            $safeEmail = htmlentities(stripslashes($this->getVar('email')));
        }

        // Checking that comment exists. Just a small verification as it's not going into DB.
        if (\FormChecker::isComment($this->getVar('comment'))) {
            $sentComment = $this->getVar('comment');
        }
        
        // Checking that stg exists and is containing only letters or figures.
        if (isset($_POST['stg']) && \FormChecker::isStgLines($_POST['stg'])) {
            $pageTitle = "Objects addition form";

            $errorText = "I'm sorry, but it seems that the content of your STG file is not correct (bad characters?). Please check again.";
            include 'view/error_page.php';
            return;
        }
        
        // Exploding lines by carriage return (\n) in submission input.
        $tabLines = explode("\n", $_POST['stg']);
        // Trim lines.
        $tabLines = array_map('trim', $tabLines);
        // Removing blank lines.
        $tabLines = array_filter($tabLines);
        // Selects the 100 first elements of the tab (the 100 first lines not blank)
        $tabLines = array_slice($tabLines, 0, 100);

        $nb_lines = count($tabLines);
        
        if ($nb_lines < 1) {
            $pageTitle = "Objects addition form";
            
            $errorText = "Not enough lines were submitted: 1 line minimum per submission!";
            include 'view/error_page.php';
            exit;
        }
        $i = 1;
        $countries = $this->objectDaoRO->getCountries();
        $objectFactory = new \ObjectFactory($this->objectDaoRO);
        $objectLinesRequests = array();
        $modelMDs = array();

        // Check each line
        foreach ($tabLines as $line) {
            $objLineReq = $this->createObjectLineRequest($line, $objectFactory);
            if ($objLineReq->getObject() != null) {
                $modelId = $objLineReq->getObject()->getModelId();
                $modelMDs[$modelId] = $this->getModelDaoRO()->getModelMetadata($modelId);
            }
            
            $objectLinesRequests[$i] = $objLineReq;
            $i++;
        }
        
        if (!isset($sentComment)) {
            $pageTitle = "Objects addition form";
            
            $errorText = "Comment mismatch!";
            include 'view/error_page.php';
            exit;
        }
        
        include 'view/submission/object/mass_add_object_form_confirm.php';
    }
}
