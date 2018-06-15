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
 * Common controller for validator
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 */
abstract class ValidatorController extends ControllerMenu {
    protected function getRequest() {
        $sig = $this->getVar('sig');
        if (empty($sig) || !\FormChecker::isSig($sig)) {
            return;
        }
        
        try {
            $requestDaoRO = \dao\DAOFactory::getInstance()->getRequestDaoRO();
            return $requestDaoRO->getRequestFromSig($sig);
            
        } catch (\dao\RequestNotFoundException $e) {
            $errorText = "Sorry but the requests you are asking for do not exist into the database. Maybe they have already been validated by someone else?";
            $adviseText = "Else, please report to fg-devel ML or FG Scenery forum.";
            include 'view/error_page.php';
            return;
        }
    }
    
    public function actionOnRequestAction() {
        if (isset($_POST["reject"])) {
            $this->rejectRequestAction();
        } else if (isset($_POST["accept"])) {
            $this->validateRequestAction();
        }
    }
    
    /**
     * Generic validation without request modification
     */
    public function validateRequestAction() {
        $request = $this->getRequest();
        if ($request == null) {
            return;
        }
        
        $this->validateRequest($request);
    }
    
    public function validateRequest($request) {
        $sig = $request->getSig();
        
        $modelDaoRW = \dao\DAOFactory::getInstance()->getModelDaoRW();
        $objectDaoRW = \dao\DAOFactory::getInstance()->getObjectDaoRW();
        $requestDaoRW = \dao\DAOFactory::getInstance()->getRequestDaoRW();
        $authorDaoRW = \dao\DAOFactory::getInstance()->getAuthorDaoRW();
        $reqExecutor = new \submission\RequestExecutor($modelDaoRW, $objectDaoRW, $authorDaoRW);

        // Executes request
        try {
            $updatedReq = $reqExecutor->executeRequest($request);
        } catch (\Exception $ex) {
            $errorText = "Sorry, but the INSERT queries could not be processed.";
            $adviseText = "Please ask for help on the <a href=\"http://www.flightgear.org/forums/viewforum.php?f=5\">Scenery forum</a> or on the devel list.";
            include 'view/error_page.php';
            return;
        }
        
        // Delete the entries from the pending query table.
        try {
            $resultDel = $requestDaoRW->deleteRequest($sig);
        } catch(\dao\RequestNotFoundException $e) {
            $errorText = "Sorry, but the pending requests DELETE queries could not be processed. Please ask for help on the <a href=\"http://www.flightgear.org/forums/viewforum.php?f=5\">Scenery forum</a> or on the devel list.";
            include 'view/error_page.php';
            return;
        }

        $comment = $_POST["maintainer_comment"];
        
        include 'view/submission/accept_submission.php';
        $this->sendAcceptedRequestEmails($request, $comment);
    }
    
    public function rejectRequestAction() {
        $request = $this->getRequest();

        if ($request == null) {
            return;
        }
        
        $sig = $request->getSig();
        $requestDaoRW = \dao\DAOFactory::getInstance()->getRequestDaoRW();

        try {
            $resultDel = $requestDaoRW->deleteRequest($sig);
        } catch(\dao\RequestNotFoundException $e) {
            $processText = "Deleting corresponding pending query.";
            $errorText   = "Sorry but the requests you are asking for do not exist into the database. Maybe they have already been validated by someone else?";
            $adviseText  = "Else, please report to fg-devel ML or FG Scenery forum.";
            include 'view/error_page.php';
            return;
        }

        if (!$resultDel) {
            $processText = "Deleting corresponding pending query.<br/>Signature found.<br /> Now deleting request #". $request->getId();
            $errorText   = "Sorry, but the DELETE query could not be processed. Please ask for help on the <a href=\"http://www.flightgear.org/forums/viewforum.php?f=5\">Scenery forum</a> or on the devel list.";
            include 'view/error_page.php';
            return;
        }

        $comment = $_POST["maintainer_comment"];

        include 'view/submission/reject_submission.php';

        $this->sendRejectedRequestEmails($request, $comment);

    }
    
    public function getNewModelPackAction() {
        $request = $this->getRequest();
        
        if ($request != null) {
            header("Content-type: application/x-gtar");
            header("Content-Disposition: inline; filename=newModel.tgz");
            $modelfiles = $request->getNewModel()->getModelFiles();
            echo $modelfiles->getPackage();
        }
    }
    
    public function getNewModelAC3DAction() {
        $request = $this->getRequest();
        
        if ($request != null) {
            header("Content-type: application/octet-stream");
            $modelfiles = $request->getNewModel()->getModelFiles();
            echo $modelfiles->getACFile();
        }
    }
    
    public function getNewModelThumbAction() {
        $request = $this->getRequest();
        
        if ($request != null) {
            header("Content-type: image/jpg");
            echo $request->getNewModel()->getThumbnail();
        }
    }
    
    public function getNewModelTextureAction() {
        $request = $this->getRequest();
        $filename = $this->getVar('name');
        
        if ($request != null) {
            $modelfiles = $request->getNewModel()->getModelFiles();

            header("Content-type: image/png");
            echo $modelfiles->getFile($filename);
        }
    }
    
    public function getNewModelTextureTNAction() {
        $request = $this->getRequest();
        $filename = $this->getVar('name');
        
        if ($request != null) {
            $modelfiles = $request->getNewModel()->getModelFiles();

            header('Content-Type: image/png');
            $this->displayThumbnail($modelfiles->getFile($filename));
        }
    }
    
    protected function displayThumbnail($textureContent) {
        $img = imagecreatefromstring($textureContent);

        $width = imagesx( $img );
        $height = imagesy( $img );

        if ($width>256) {
            // calculate thumbnail size
            $newWidth = 256;
            $newHeight = floor( $height * $newWidth / $width );

            // create a new temporary image
            $tmpImg = imagecreatetruecolor( $newWidth, $newHeight );

            // copy and resize old image into new image 
            imagecopyresized( $tmpImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height );

            // Display the PNG directly to the browser
            imagepng($tmpImg);
            imagedestroy($tmpImg);
        } else {
            echo $textureContent;
        }
    }
    
    abstract protected function sendRejectedRequestEmails($request, $comment);
    abstract protected function sendAcceptedRequestEmails($request, $comment);
}
