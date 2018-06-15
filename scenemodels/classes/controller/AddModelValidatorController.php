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
 * Controller for model addition validation
 *
 * @author Julien Nguyen
 */
class AddModelValidatorController extends ValidatorController {
    
    private $authorDaoRO;
    
    public function __construct() {
        $this->authorDaoRO = \dao\DAOFactory::getInstance()->getAuthorDaoRO();
    }
    
    /**
     * View request page action
     */
    public function viewRequestAction() {
        $request = $this->getRequest();
        
        if ($request != null) {
            $sig = $request->getSig();
            
            $authorExist = false;
            $newAuthor = $request->getNewAuthor();
            if ($newAuthor != null) {
                // Check first if the author already exist
                $authorExist = $this->authorDaoRO->getAuthorByEmail($newAuthor->getEmail()) != null;
            }
            
            include 'view/submission/model/validator/view_add_model_request.php';
        }
    }

    protected function sendAcceptedRequestEmails($updatedReq, $comment) {
        // Sending mail if SQL was correctly inserted and entry deleted.
        // Sets the time to UTC.
        date_default_timezone_set('UTC');
        $dtg = date('l jS \of F Y h:i:s A');

        // OK, let's start with the mail redaction.
        // Who will receive it ?
        $to = $updatedReq->getContributorEmail();
        $to = (isset($to)) ? $to : '';

        // Email to contributor
        $emailSubmit = \email\EmailContentFactory::getAddModelRequestAcceptedEmailContent($dtg, $updatedReq, $comment);
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
        $emailSubmit = \email\EmailContentFactory::getAddModelRequestRejectedEmailContent($dtg, $request, $comment);
        $emailSubmit->sendEmail($to, true);
    }

    public function modelViewerAction() {
        $sig = $this->getVar('sig');
        if (empty($sig) || !\FormChecker::isSig($sig)) {
            return;
        }
        
        $ac3DFile = "app.php?c=AddModelValidator&a=getNewModelAC3D&sig=".$sig;
        $texturePrefix = 'app.php?c=AddModelValidator&a=getNewModelTexture&sig='.$sig.'&name=';
        include 'view/model_viewer.php';
    }
    
    
    /**
     * Validation action
     */
    public function validateRequestAction() {
        $request = $this->getRequest();
        if ($request == null) {
            return;
        }
        
        $this->updateRequestAuthor($request);
        
        $this->validateRequest($request);
    }
    
    /**
     * Update request if a new author is defined
     */
    private function updateRequestAuthor($request) {
        // If someone want to create a new author
        $newAuthor = $request->getNewAuthor();
        if ($newAuthor != null) {
            // Check first if the author already exist
            $existingAuthor = $this->authorDaoRO->getAuthorByEmail($newAuthor->getEmail());
            
            if ($existingAuthor != null) {
                // If author exist
                $request->setNewAuthor(null);
                $request->getNewModel()->getMetadata()->setAuthor($existingAuthor);
            } else {
                // Author does not exist, check if the new author must be added.
                $mustAddAuthor = $this->getVar('au_add');
                if ($mustAddAuthor == "false") {
                    $request->setNewAuthor(null);
                }
            }
        }
    }
    
}
