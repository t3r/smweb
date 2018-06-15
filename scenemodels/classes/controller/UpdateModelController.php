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
 * Controller for update model
 *
 * @author Julien Nguyen
 */
class UpdateModelController extends ModelRequestController {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->authorDaoRO = \dao\DAOFactory::getInstance()->getAuthorDaoRO();
    }
    
    public function selectModelFormAction() {
        $modelsGroups = $this->getModelDaoRO()->getModelsGroups();
        include 'view/submission/model/select_model_upd_form.php';
    }
    
    public function modelUpdateFormAction() {
        // Populate fields when a model id is given in the url
        if (isset($_REQUEST['modelId'])
                && \FormChecker::isModelId($_REQUEST['modelId'])) {
            $idToUpdate = stripslashes($_REQUEST['modelId']);
        } else {
            return;
        }

        $modelMD = $this->getModelDaoRO()->getModelMetadata($idToUpdate);
        
        $modelsGroups = $this->getModelDaoRO()->getModelsGroups();
        $authors = $this->authorDaoRO->getAllAuthors(0, "ALL");
        include 'view/submission/model/model_update_form.php';
    }
    
    /**
     * Check and add new model update request 
     */
    public function addRequestAction() {
        $ajaxCheck = $this->getVar('ajaxCheck') == 1;

        /** STEP 1 : CHECK IF ALL FILES WERE RECEIVED */
        $exceptions = $this->checkFilesArray();
        
        /** STEP 2 : MOVE THUMBNAIL, AC3D, PNG AND XML FILES IN TMP DIRECTORY (Will be removed later on) */
        $thumbName = $_FILES['mo_thumbfile']['name'];
        $ac3dName  = $_FILES['ac3d_file']['name'];
        if (isset($_FILES['xml_file'])) {
            $xmlName = $_FILES['xml_file']['name']; 
        } else {
            $xmlName = null;
        }
        
        if (isset($_FILES["png_file"])) {
            $pngNames = $_FILES["png_file"]["name"];
        } else {
            $pngNames = array();
        }
        
        if (empty($exceptions)) {
            try {
                // Open working directory
                $targetPath = $this->getModelChecker()->openWorkingDirectory(sys_get_temp_dir());
                $exceptions = $this->moveFilesToTMPDir($targetPath, $xmlName, $ac3dName, $thumbName);
            } catch (\Exception $ex) {
                $exceptions[] = $ex;
            }
        }
        
        /** IF ERRORS ARE DETECTED : STOP NOW AND PRINT ERRORS */
        if (!empty($exceptions)) {
            if (isset($targetPath)) {
                \FileSystemUtils::clearDir($targetPath);
            }
            
            $this->displayModelErrors($exceptions, $ajaxCheck);
            return;
        }
        
        /** STEP 4 : CHECK FILES */
        $exceptions = $this->checkFiles($targetPath, $xmlName, $ac3dName, $thumbName, $pngNames);
        
        // If an XML file is used for the model, the mo_path has to point to it, otherwise use AC3D
        $pathToUse = $ac3dName;
        if (!empty($xmlName)) {
            $pathToUse = $xmlName;
        }

        // Check if path is already used
        $modelId = $this->getVar('modelId');
        if (\FormChecker::isModelId($modelId)) {
            $modelToUpdateOld = $this->getModelDaoRO()->getModelMetadata($modelId);
            if ($pathToUse != $modelToUpdateOld->getFilename() && $this->pathExists($pathToUse)) {
                $exceptions[] = new \model\ErrorInfo("Filename \"".$pathToUse."\" is already used by another model");
            }
        } else {
            $exceptions[] = new \model\ErrorInfo("Please check the original model selected.");
        }
        
        /** STEP 9 : CHECK MODEL INFORMATION */
        $name    = $this->getVar('mo_name');
        $notes   = $this->getVar('notes');
        $authorId  = $this->getVar('mo_author');
        $moGroupId = $this->getVar('model_group_id');
            
        if (empty($notes)) {
            $notes = "";
        }

        $modelMDValidator = \submission\ModelMetadataValidator::getModelMDValidator($name, $notes, $authorId, $moGroupId);
        $exceptions = array_merge($exceptions, $modelMDValidator->validate());

        if (empty($this->getVar('gpl'))) {
            $exceptions[] = new \model\ErrorInfo("You did not accept the GNU GENERAL PUBLIC LICENSE Version 2, June 1991. As all the models shipped with FlightGear must wear this license, your contribution can't be accepted in our database. Please try to find GPLed textures and/or data.");
        }

        // Checking that comment exists. Just a small verification as it's not going into DB.
        $sentComment = $this->getVar('comment');
        if (!\FormChecker::isComment($sentComment)) {
            $exceptions[] = new \model\ErrorInfo("Please add a comment to the maintainer.");
        }
        
        $contrEmail = htmlentities(stripslashes($this->getVar('email')));
        if (!\FormChecker::isEmail($contrEmail)) {
            $exceptions[] = new \model\ErrorInfo("Your email is mandatory.");
        }
        
        if (!empty($exceptions)) {
            \FileSystemUtils::clearDir($targetPath);
            $this->displayModelErrors($exceptions, $ajaxCheck);
            return;
        }
        
        /** STEP 8 : ARCHIVE AND COMPRESS FILES */
        $thumbFile = $this->prepareThumbFile($targetPath.$thumbName);
        // Has to be deleted, because it's not put into the .tar.gz
        unlink($targetPath.$thumbName);
        
        $modelFile = $this->prepareModelFile($targetPath, $xmlName, $ac3dName);
        // Delete temporary model directory
        \FileSystemUtils::clearDir($targetPath);
        
        $modelFactory = new \ModelFactory($this->getModelDaoRO(), $this->authorDaoRO);
        $newModel = new \model\Model();
        $newModelMD = $modelFactory->createModelMetadata($modelId, $authorId, $pathToUse,
                $name, $notes, $moGroupId);
        $newModel->setMetadata($newModelMD);
        $newModel->setModelFiles($modelFile);
        $newModel->setThumbnail($thumbFile);
        
        $oldModel = $this->getModelDaoRO()->getModel($modelId);
        
        if (!$this->checkCaptcha()) {
            $this->displayCaptchaError($ajaxCheck);
            return;
        }
        
        try {
            $updatedReq = $this->addRequest($newModel, $oldModel, $contrEmail, $sentComment);

            $this->sendEmailsRequestPending($updatedReq);
            $this->displaySuccess($updatedReq, $ajaxCheck);
        } catch (\Exception $ex) {
            $pageTitle = "Models update form";
            $errorText = "Sorry, but the query could not be processed. Please ask for help on the <a href='http://www.flightgear.org/forums/viewforum.php?f=5'>Scenery forum</a> or on the devel list.";
            include 'view/error_page.php';
            return;
        }
    }
    
    private function displaySuccess($updatedReq, $ajaxCheck) {
        if ($ajaxCheck) {
            include 'view/submission/model/model_success_xml.php';
        } else {
            include 'view/submission/model/model_update_queued.php';
        }
    }
    
    private function addRequest($newModel, $oldModel, $contrEmail, $sentComment) {
        $request = new \model\RequestModelUpdate();
        $request->setNewModel($newModel);
        $request->setContributorEmail($contrEmail);
        $request->setComment($sentComment);
        $request->setOldModel($oldModel);
        
        $requestDaoRW = \dao\DAOFactory::getInstance()->getRequestDaoRW();
        
        return $requestDaoRW->saveRequest($request);
    }
    
    private function sendEmailsRequestPending($updatedReq) {
        // Sending mail if there is no false and SQL was correctly inserted.
        // Sets the time to UTC.
        date_default_timezone_set('UTC');
        $dtg = date('l jS \of F Y h:i:s A');
        // Retrieving the IP address of the submitter (takes some time to resolve the IP address though).
        $ipaddr = $_SERVER["REMOTE_ADDR"];
        $host = gethostbyaddr($ipaddr);

        $emailSubmit = \email\EmailContentFactory::getModelUpdateRequestPendingEmailContent($dtg, $ipaddr, $host, $updatedReq);
        $emailSubmit->sendEmail("", true);

        $contrEmail = $updatedReq->getContributorEmail();
        $auEmail = $updatedReq->getOldModel()->getMetadata()->getAuthor()->getEmail();
        if (!empty($contrEmail)) {
            // Mailing the submitter to tell him that his submission has been sent for validation
            $emailSubmit = \email\EmailContentFactory::getModelUpdateRequestSentForValidationEmailContent($dtg, $ipaddr, $host, $updatedReq);
            $emailSubmit->sendEmail($contrEmail, false);

            // If the author's email is different from the submitter's, an email is also sent to the author
            if (\FormChecker::isEmail($auEmail) && $auEmail != $contrEmail) {
                $emailSubmit = \email\EmailContentFactory::getModelUpdateRequestSentForValidationAuthorEmailContent($dtg, $ipaddr, $host, $updatedReq);
                $emailSubmit->sendEmail($auEmail, false);
            }
        }
    }
    
    public function successAction() {
        $id = $this->getVar('id');
        $requestDaoRO = \dao\DAOFactory::getInstance()->getRequestDaoRO();
        $updatedReq = $requestDaoRO->getRequest($id);
        include 'view/submission/model/model_update_queued.php';
    }
}
