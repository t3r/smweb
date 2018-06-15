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
 * Description of ObjectValidatorController
 *
 * @author Julien Nguyen
 */
class ObjectValidatorController extends ValidatorController {
    public function viewRequestAction() {
        $request = $this->getRequest();
        if ($request != null) {
            include 'view/submission/object/validator/object_submission.php';
        }
    }
    
    protected function sendRejectedRequestEmails($request, $comment) {
        // Sending mail if entry was correctly deleted.

        // email destination
        $to = $request->getContributorEmail();
        $to = (isset($to)) ? $to : '';

        $emailSubmit = \email\EmailContentFactory::getObjectRejectedEmailContent($request, $comment);
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

        $emailSubmit = \email\EmailContentFactory::getObjectRequestAcceptedEmailContent($request, $comment);
        $emailSubmit->sendEmail($to, true);
    }
}
