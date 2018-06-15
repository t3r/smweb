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
 * AddObjectsValidatorController
 *
 * @author Julien Nguyen
 */
class AddObjectsValidatorController extends ValidatorController {
    public function viewRequestAction() {
        $request = $this->getRequest();
        
        if ($request != null) {
            $modelMDs[] = array();
            foreach ($request->getNewObjects() as $newObj) {
                $id = $newObj->getModelId();
                $modelMDs[$id] = $this->getModelDaoRO()->getModelMetadata($id);
            }
            
            include 'view/submission/object/validator/view_add_objects_request.php';
        }
    }
    
    protected function sendRejectedRequestEmails($request, $comment) {
        // Sending mail if entry was correctly deleted.
        // Sets the time to UTC.
        date_default_timezone_set('UTC');
        $dtg = date('l jS \of F Y h:i:s A');

        // email destination
        $to = $request->getContributorEmail();
        $to = (isset($to)) ? $to : '';

        $emailSubmit = \email\EmailContentFactory::getObjectsAddRequestRejectedEmailContent($dtg, $request, $comment);
        $emailSubmit->sendEmail($to, true);
    }
    
    protected function sendAcceptedRequestEmails($request, $comment) {
        // Sending mail if SQL was correctly inserted and entry deleted.
        // Sets the time to UTC.
        date_default_timezone_set('UTC');
        $dtg = date('l jS \of F Y h:i:s A');

        // email destination
        $to = $request->getContributorEmail();
        $to = (isset($to)) ? $to : '';

        $emailSubmit = \email\EmailContentFactory::getObjectsAddRequestAcceptedEmailContent($dtg, $request, $comment);
        $emailSubmit->sendEmail($to, true);
    }
}
