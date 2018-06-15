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
 * Description of GenericValidatorController
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 */
class GenericValidatorController extends ValidatorController {
    protected function sendRejectedRequestEmails($request, $comment) {
        // TODO
    }
    protected function sendAcceptedRequestEmails($request, $comment) {
        // TODO
    }
    
    public function rejectRequestAction() {
        $sig = $this->getVar('sig');
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

        //$comment = $_POST["maintainer_comment"];

        include 'view/submission/reject_submission.php';

        //$this->sendRejectedRequestEmails($request, $comment);

    }
}
