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
 * Controller for update model request validation
 *
 * @author Julien Nguyen
 */
class UpdateModelValidatorController extends ValidatorController {
    public function viewRequestAction() {
        $request = $this->getRequest();
        $sig = $request->getSig();
        
        if ($request != null) {
            include 'view/submission/model/validator/view_upd_model_request.php';
        }
    }

    protected function sendAcceptedRequestEmails($request, $comment) {
        // Sending mail if SQL was correctly inserted and entry deleted.
        // Sets the time to UTC.
        date_default_timezone_set('UTC');
        $dtg = date('l jS \of F Y h:i:s A');

        // OK, let's start with the mail redaction.
        // Who will receive it ?
        $to = $request->getContributorEmail();
        $to = (isset($to)) ? $to : '';

        // Email to contributor
        $emailSubmit = \email\EmailContentFactory::getModelUpdateRequestAcceptedEmailContent($dtg, $request, $comment);
        $emailSubmit->sendEmail($to, true);
    }
    
    protected function sendRejectedRequestEmails($request, $comment) {
        // Sending mail if entry was correctly deleted.
        // Sets the time to UTC.

        date_default_timezone_set('UTC');
        $dtg = date('l jS \of F Y h:i:s A');

        $to = $request->getContributorEmail();
        $to = (isset($to)) ? $to : '';

        // Email to contributor
        $emailSubmit = \email\EmailContentFactory::getModelUpdateRequestRejectedEmailContent($dtg, $request, $comment);
        $emailSubmit->sendEmail($to, true);
    }
    
    public function modelViewerAction() {
        $sig = $this->getVar('sig');
        if (empty($sig) || !\FormChecker::isSig($sig)) {
            return;
        }
        
        $ac3DFile = "app.php?c=UpdateModelValidator&a=getNewModelAC3D&sig=".$sig;
        $texturePrefix = 'app.php?c=UpdateModelValidator&a=getNewModelTexture&sig='.$sig.'&name=';
        include 'view/model_viewer.php';
    }
    
    public function getOldModelTextureAction() {
        $request = $this->getRequest();
        $filename = $this->getVar('name');
        
        if ($request != null) {
            $modelfiles = $request->getOldModel()->getModelFiles();

            header("Content-type: image/png");
            echo $modelfiles->getFile($filename);
        }
    }
    
    public function getOldModelTextureTNAction() {
        $request = $this->getRequest();
        $filename = $this->getVar('name');
        
        if ($request != null) {
            $modelfiles = $request->getOldModel()->getModelFiles();

            header('Content-Type: image/png');
            $this->displayThumbnail($modelfiles->getFile($filename));
        }
    }
}
