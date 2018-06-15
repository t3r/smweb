<?php

/*
 * Copyright (C) 2014 - FlightGear Team
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

namespace email;

/**
 * Email content factory
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 */
class EmailContentFactory {

    static protected function format($bodyMessage) {
        $message = "Hi,\r\n\r\n".
                   $bodyMessage.
                   "Sincerely,\r\n\r\n" .
                   "FlightGear Scenery Team\r\n\r\n" .
                   "-----------------\r\n" .
                   "This process has gone through antispam measures. However, if this email is not sollicited, please excuse us and report at http://www.flightgear.org/forums/viewtopic.php?f=5&t=14671";
        
        return wordwrap($message, 70, "\r\n");
    }
    
    static public function getObjectsAddRequestAcceptedEmailContent($dtg, $request, $comment) {
        $subject = "Object(s) import accepted";
        $message = "On ".$dtg." UTC, you issued an object(s) import request (#".$request->getId().").\r\n\r\n" .
                   "We are glad to let you know that this request has been accepted!\r\n\r\n";

        if (!empty($comment)) {
            $message .= "The screener left a comment for you: '" . $comment . "'\r\n\r\n";
        }
        $message .= "Thanks for your help in making FlightGear better!\r\n\r\n";
        
        return new \email\EmailContent($subject, static::format($message));
    }
    
    
    static public function getObjectsAddRequestPendingEmailContent($dtg, $ipaddr, $host, $request) {
        $subject = "Object(s) import needs validation";
        $message = "We would like to let you know that a new object(s) import request is pending. " .
                   "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host.") ";
        $contrEmail = $request->getContributorEmail();
        if (!empty($contrEmail)) {
            $message .= "and with email address ".$contrEmail." ";
        }
        $message .= "issued an object(s) import request (#".$request->getId().").\r\n\r\n" .
                    "Comment by user: ".strip_tags($request->getComment())."\r\n\r\n" .
                    "Now please click the following link to check and confirm ".
                    "or reject the submission: http://".$_SERVER['SERVER_NAME']."/app.php?c=AddObjectsValidator&a=viewRequest&sig=". $request->getSig() ."\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getObjectsAddRequestRejectedEmailContent($dtg, $request, $comment) {
        $subject = "Object(s) import rejected";
        $message = "On ".$dtg." UTC, you issued an object(s) import request (#".$request->getId().").\r\n\r\n" .
                   "We are sorry to let you know that this request has been rejected.\r\n\r\n";

        if (!empty($comment)) {
            $message .= "The screener left a comment for you: '" . $comment . "'\r\n\r\n";
        }
        $message .= "Please do not let this stop you from sending us corrected object locations or models.\r\n\r\n";
        
        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getObjectsAddSentForValidationEmailContent($ipaddr, $host, $dtg, $request) {
        $subject = "Object(s) import";
        $message = "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host."), which is thought to be you, issued a object(s) addition request (#".$request->getId().").\r\n\r\n" .
                   "We would like to let you know that this request has been sent for validation. Allow up to a few days for your request to be processed.\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getModelUpdateRequestAcceptedEmailContent($dtg, $request, $comment) {
        $modelMD = $request->getNewModel()->getMetadata();
        $subject = "3D model update accepted";
        $message = "On ".$dtg." UTC, you issued a 3D model update request (#".$request->getId(). " named '". $modelMD->getName() ."').\r\n\r\n" .
                   "We are glad to let you know that this request has been accepted!\r\n\r\n";
        if (!empty($comment)) {
            $message .= "The screener left a comment for you: '" . $comment . "'\r\n\r\n";
        }
        $message .= "You can check the model at http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=view&id=".$modelMD->getId()."\r\n\r\n" .
                "Thanks for your help in making FlightGear better!\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getModelUpdateRequestPendingEmailContent($dtg, $ipaddr, $host, $request) {
        $newModelMD = $request->getNewModel()->getMetadata();
        $safeContrEmail = $request->getContributorEmail();
        
        $subject = "3D model update needs validation.";
        $message = "We would like to let you know that an update for a 3D model request is pending (#".$request->getId()."). " .
                   "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host.") ";
        if (!empty($safeContrEmail)) {
            $message .= "and with email address ".$safeContrEmail." ";
        }
        $message .= "issued the following request:\r\n\r\n" .
                    "Family:           ". $newModelMD->getModelsGroup()->getName() . "\r\n[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=browse&shared=".$newModelMD->getModelsGroup()->getId()." ]\r\n" .
                    "Path:             ". $newModelMD->getFilename() . "\r\n" .
                    "Author:           ". $newModelMD->getAuthor()->getName() ."\r\n" .
                    "Contributor email ". $safeContrEmail ."\r\n" .
                    "Model name:       ". $newModelMD->getName() ."\r\n" .
                    "Description:      ". strip_tags($newModelMD->getDescription()) ."\r\n" .
                    "Comment by user:  ". strip_tags($request->getComment()) . "\r\n\r\n" .
                    "Now please click the following link to view and confirm/reject the submission: " . "http://".$_SERVER['SERVER_NAME']."/app.php?c=UpdateModelValidator&a=viewRequest&sig=". $request->getSig() ."\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getModelUpdateRequestRejectedEmailContent($dtg, $request, $comment) {
        $newModelMD = $request->getNewModel()->getMetadata();
        
        $subject = "3D model update rejected";
        $message = "On ".$dtg." UTC, you issued a 3D model update request (#".$request->getId(). " named '". $newModelMD->getName() ."').\r\n\r\n" .
                   "We are sorry to let you know that this request has been rejected.\r\n\r\n";
        if (!empty($comment)) {
            $message .= "The screener left a comment for you: '" . $comment . "'\r\n\r\n";
        }
        $message .=  "Please do not let this stop you from sending us an improved version of this model or other models.\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getModelUpdateRequestSentForValidationEmailContent($dtg, $ipaddr, $host, $request) {
        $newModelMD = $request->getNewModel()->getMetadata();
        $subject = "3D model update request";
        $message = "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host."), which is thought to be you, issued a 3D model update request (#".$request->getId().").\r\n\r\n" .
                   "We would like to let you know that this request has been sent for validation. Allow up to a few days for your request to be processed.\r\n\r\n" .
                   "Family:           ". $newModelMD->getModelsGroup()->getName() . "\r\n" . "[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=browse&shared=".$newModelMD->getModelsGroup()->getId()." ]\r\n" .
                   "Path:             ". $newModelMD->getFilename() . "\r\n" .
                   "Author:           ". $newModelMD->getAuthor()->getName() ."\r\n" .
                   "Contributor email ". $request->getContributorEmail() ."\r\n" .
                   "Model name:       ". $newModelMD->getName() ."\r\n" .
                   "Description:      ". strip_tags($newModelMD->getDescription()) ."\r\n" .
                   "Comment by user:  ". strip_tags($request->getComment()) . "\r\n\r\n";
        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getModelUpdateRequestSentForValidationAuthorEmailContent($dtg, $ipaddr, $host, $request) {
        $newModelMD = $request->getNewModel()->getMetadata();
        
        $subject = "3D model update request";
        $message = "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host."), issued a 3D model update request for your model (#".$request->getId(). ").\r\n\r\n" .
                   "We would like to let you know that this request has been sent for validation.\r\n\r\n" .
                   "Family:           ". $newModelMD->getModelsGroup()->getName() . "\r\n" . "[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=browse&shared=".$newModelMD->getModelsGroup()->getId()." ]\r\n" .
                   "Path:             ". $newModelMD->getFilename() . "\r\n" .
                   "Author:           ". $newModelMD->getAuthor()->getName() ."\r\n" .
                   "Contributor email ". $request->getContributorEmail() ."\r\n" .
                   "Model name:       ". $newModelMD->getName() ."\r\n" .
                   "Description:      ". strip_tags($newModelMD->getDescription()) ."\r\n" .
                   "Comment by user:  ". strip_tags($request->getComment()) . "\r\n\r\n";
            
        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getObjectRequestAcceptedEmailContent($request, $comment) {
        $subject = "Object request accepted";
        $message = "We would like to let you know that the object (update, deletion) request #".$request->getId(). " was successfully treated.\r\n\r\n";
        if (!empty($comment)) {
            $message .= "The screener left a comment for you: '" . $comment . "'\r\n\r\n";
        }
        $message .= "Please don't forget to use the massive import form rather than the single one if you have many objects to add!\r\n\r\n";
            
        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getObjectRejectedEmailContent($request, $comment) {
        $subject = "Object request rejected";
        $message = "We are sorry to let you know that the object request #".$request->getId(). " was rejected.\r\n\r\n";
        if (!empty($comment)) {
            $message .= "The screener left a comment for you: '" . $comment . "'\r\n\r\n";
        }
        
        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getObjectDeleteRequestPendingEmailContent($dtg, $ipaddr, $host, $modelMD, $request) {
        $safeEmail = $request->getContributorEmail();
        $objectToDel = $request->getObjectToDelete();
        $objectToDelPos = $objectToDel->getPosition();
        
        $subject = "Object deletion needs validation";
        $message = "We would like to let you know that a new object deletion request is pending (#".$request->getId()."). " .
                   "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host.") ";
        if (!empty($safeEmail)) {
            $message .= "and with email address ".$safeEmail." ";
        }
        $message .= "issued the following request:\r\n\r\n" .
                    "Object #:         " .$objectToDel->getId(). "\r\n" .
                    "Family:           " .$modelMD->getModelsGroup()->getName(). "\r\n" .
                    "Model:            " .$modelMD->getName(). "\r\n" .
                    "Ob. text/metadata:" .$objectToDel->getDescription(). "\r\n" .
                    "Latitude:         " .$objectToDelPos->getLatitude(). "\r\n" .
                    "Longitude:        " .$objectToDelPos->getLongitude(). "\r\n" .
                    "Ground elevation: " .$objectToDelPos->getGroundElevation(). "\r\n" .
                    "Elevation offset: " .$objectToDelPos->getElevationOffset(). "\r\n" .
                    "True orientation: " .$objectToDelPos->getOrientation(). "\r\n" .
                    "Comment:          " .strip_tags($request->getComment()) . "\r\n" .
                    "Map:              http://".$_SERVER['SERVER_NAME']."/map/?lon=". $objectToDelPos->getLongitude() ."&lat=". $objectToDelPos->getLatitude() ."&z=14\r\n\r\n" .
                    "Now please click the following link to view and confirm/reject the submission: http://".$_SERVER['SERVER_NAME']."/app.php?c=ObjectValidator&a=viewRequest&sig=". $request->getSig() . "\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getObjectDeleteRequestSentForValidationEmailContent($dtg, $ipaddr, $host, $request, $modelMD) {
        $objectToDel = $request->getObjectToDelete();
        $objectToDelPos = $objectToDel->getPosition();
        
        $subject = "Object deletion";
        $message = "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host."), which is thought to be you, issued an object deletion request (#".$request->getId(). ").\r\n\r\n" .
                   "We would like to let you know that this request was sent for validation. Allow up to a few days for your request to be processed.\r\n\r\n" .
                   "Family:           " .$modelMD->getModelsGroup()->getName(). "\r\n" .
                   "Model:            " .$modelMD->getName(). "\r\n" .
                   "Latitude:         " .$objectToDelPos->getLatitude(). "\r\n" .
                   "Longitude:        " .$objectToDelPos->getLongitude(). "\r\n" .
                   "Ground elevation: " .$objectToDelPos->getGroundElevation(). "\r\n" .
                   "Elevation offset: " .$objectToDelPos->getElevationOffset(). "\r\n" .
                   "True orientation: " .$objectToDelPos->getOrientation(). "\r\n" .
                   "Comment:          " .strip_tags($request->getComment()) . "\r\n".
                   "Map:              http://".$_SERVER['SERVER_NAME']."/map/?lon=". $objectToDelPos->getLongitude() ."&lat=". $objectToDelPos->getLatitude() ."&z=14\r\n\r\n";
        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getObjectUpdateRequestPendingEmailContent($dtg, $ipaddr, $host, $oldModelMD, $newModelMD, $request) {
        $safeEmail = $request->getContributorEmail();
        $oldObject = $request->getOldObject();
        $newObject = $request->getNewObject();
        $oldObjPos = $oldObject->getPosition();
        $newObjPos = $newObject->getPosition();
        
        $subject = "Object update needs validation";
        $message = "We would like to let you know that an object update request is pending (#".$request->getId()."). " .
                   "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host.") ";
        if (!empty($safeEmail)) {
            $message .= "and with email address ".$safeEmail." ";
        }
        $message .= "issued the following request:\r\n\r\n" .
                    "Object #:          ". $oldObject->getId()."\r\n" .
                    "Family:            ". $oldModelMD->getModelsGroup()->getName() ." => ".$newModelMD->getModelsGroup()->getName()."\r\n" .
                    "[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=browse&shared=".$newModelMD->getModelsGroup()->getId()." ]" . "\r\n" .
                    "Model:             ". $oldModelMD->getName() ." => ".$newModelMD->getName()."\r\n" .
                    "[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=view&id=".$newModelMD->getId()." ]" . "\r\n" .
                    "Latitude:          ". $oldObjPos->getLatitude() . "  => ".$newObjPos->getLatitude()."\r\n" .
                    "Longitude:         ". $oldObjPos->getLongitude() . " => ".$newObjPos->getLongitude()."\r\n" .
                    "Ground elevation:  ". $oldObjPos->getGroundElevation() . " => ".$newObjPos->getGroundElevation()."\r\n" .
                    "Elevation offset:  ". $oldObjPos->getElevationOffset() . " => ".$newObjPos->getElevationOffset()."\r\n" .
                    "True orientation:  ". $oldObjPos->getOrientation() . " => ".$newObjPos->getOrientation()."\r\n" .
                    "Map (new position): http://".$_SERVER['SERVER_NAME']."/map/?lon=". $newObjPos->getLongitude() ."&lat=". $newObjPos->getLatitude() ."&z=14" . "\r\n" .
                    "Comment:           ". strip_tags($request->getComment()) ."\r\n\r\n" .
                    "Now please click the following link to view and confirm/reject the submission: http://".$_SERVER['SERVER_NAME']."/app.php?c=ObjectValidator&a=viewRequest&sig=". $request->getSig() . "\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getObjectUpdateRequestSentForValidationEmailContent($dtg, $ipaddr, $host, $request, $oldModelMD, $newModelMD) {
        $oldObject = $request->getOldObject();
        $newObject = $request->getNewObject();
        $oldObjPos = $oldObject->getPosition();
        $newObjPos = $newObject->getPosition();
        
        $subject = "Object update";
        $message = "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host."), which is thought to be you, issued an object update request (#".$request->getId().")." . "\r\n\r\n" .
                   "We would like to let you know that this request was sent for validation. Allow up to a few days for your request to be processed." . "\r\n\r\n" .
                   "Object #:          ".$oldObject->getId()."\r\n" .
                   "Family:            ". $oldModelMD->getModelsGroup()->getName() ." => ".$newModelMD->getModelsGroup()->getName()."\r\n" .
                   "[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=browse&shared=".$newModelMD->getModelsGroup()->getId()." ]\r\n" .
                   "Model:             ". $oldModelMD->getName() ." => ".$newModelMD->getName()."\r\n" .
                   "[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=view&id=".$newModelMD->getId()." ]\r\n" .
                   "Latitude:          ". $oldObjPos->getLatitude() . "  => ".$newObjPos->getLatitude()."\r\n" .
                   "Longitude:         ". $oldObjPos->getLongitude() . " => ".$newObjPos->getLongitude()."\r\n" .
                   "Ground elevation:  ". $oldObjPos->getGroundElevation() . " => will be recomputed\r\n" .
                   "Elevation offset:  ". $oldObjPos->getElevationOffset() . " => ".$newObjPos->getElevationOffset()."\r\n" .
                   "True rientation:   ". $oldObjPos->getOrientation() . " => ".$newObjPos->getOrientation()."\r\n" .
                   "Comment:           ". strip_tags($request->getComment()) ."\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getAddModelRequestAcceptedEmailContent($dtg, $request, $comment) {
        $newModelMD = $request->getNewModel()->getMetadata();
        
        $subject = "3D model import accepted";
        $message = "On ".$dtg." UTC, you issued a 3D model import request.\r\n\r\n" .
                   "We are glad to let you know that this request was accepted!\r\n\r\n" .
                   "For reference, the ID of this request is '".$request->getId(). "' (model and object) and it is named '". $newModelMD->getName() ."'.\r\n\r\n";
        if (!empty($comment)) {
            $message .= "The screener left a comment for you: '" . $comment . "'\r\n\r\n";
        }
        $message .= "Thanks for your help in making FlightGear better!\r\n\r\n";
            
        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getAddModelRequestPendingEmailContent($dtg, $ipaddr, $host, $request) {
        $contrEmail = $request->getContributorEmail();
        $newModelMD = $request->getNewModel()->getMetadata();
        $newObject = $request->getNewObject();
        $newObjPos = $newObject->getPosition();
        
        $subject = "3D model import needs validation.";
        $message = "We would like to let you know that a new 3D model request is pending (#".$request->getId()."). " .
                   "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host.") ".
                   "and with email address ".$contrEmail." ".
                   "issued the following request:" . "\r\n\r\n" .
                   "Family:           ". $newModelMD->getModelsGroup()->getName() . "\r\n" . "[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=browse&shared=".$newModelMD->getModelsGroup()->getId()." ]" . "\r\n" .
                   "Path:             ". $newModelMD->getFilename() . "\r\n" .
                   "Author:           ". $newModelMD->getAuthor()->getName();
        if ($newModelMD->getAuthor()->getId() == 1) {
            $message .= " (must be added first)";
        }
        $message .="\r\n" .
                   "Model name:       ". $newModelMD->getName() ."\r\n" .
                   "Description:      ". strip_tags($newModelMD->getDescription()) ."\r\n" .
                   "Latitude:         ". $newObjPos->getLatitude() . "\r\n" .
                   "Longitude:        ". $newObjPos->getLongitude() . "\r\n" .
                   "Country:          ". $newObject->getCountry()->getName() . "\r\n" .
                   "Elevation offset: ". $newObjPos->getElevationOffset() . "\r\n" .
                   "True orientation: ". $newObjPos->getOrientation() . "\r\n" .
                   "Map:              http://".$_SERVER['SERVER_NAME']."/map/?lon=". $newObjPos->getLongitude() ."&lat=". $newObjPos->getLatitude() ."&z=14\r\n\r\n" .
                   "Now please click the following link to view and confirm/reject the submission: " . "http://".$_SERVER['SERVER_NAME']."/app.php?c=AddModelValidator&a=viewRequest&sig=". $request->getSig() . "\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getAddModelRequestRejectedEmailContent($dtg, $request, $comment) {
        $modelMD = $request->getNewModel()->getMetadata();
        
        $subject = "3D model import rejected";
        $message = "On ".$dtg." UTC, you issued a 3D model import request.\r\n\r\n" .
                   "We are sorry to let you know that this request was rejected.\r\n\r\n" .
                   "For reference, the ID of this request was '".$request->getId(). "' (model and object) and it was named '". $modelMD->getName() ."'.\r\n\r\n";
        if (!empty($comment)) {
            $message .= "The screener left a comment for you: '" . $comment . "'\r\n\r\n";
        }
        $message .=  "Please do not let this stop you from sending us an improved version of this model or other models." . "\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
    
    static public function getAddModelRequestSentForValidationEmailContent($dtg, $ipaddr, $host, $request) {
        $newModelMD = $request->getNewModel()->getMetadata();
        $newObject = $request->getNewObject();
        $newObjPos = $newObject->getPosition();
        
        $subject = "3D model import";
        $message = "On ".$dtg." UTC, someone from the IP address ".$ipaddr." (".$host."), which is thought to be you, issued a 3D model import request.\r\n\r\n" .
                   "We would like to let you know that this request was sent for validation. Allow up to a few days for your request to be processed.\r\n\r\n" .
                   "For reference, the ID of this request is '".$request->getId(). "' (model and object)\r\n\r\n" .
                   "Family:           ". $newModelMD->getModelsGroup()->getName() . "\r\n" . "[ http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=browse&shared=".$newModelMD->getModelsGroup()->getId()." ]" . "\r\n" .
                   "Path:             ". $newModelMD->getFilename() . "\r\n" .
                   "Author:           ". $newModelMD->getAuthor()->getName() ."\r\n" .
                   "Model name:       ". $newModelMD->getName() ."\r\n" .
                   "Description:      ". strip_tags($newModelMD->getDescription()) ."\r\n" .
                   "Latitude:         ". $newObjPos->getLatitude() . "\r\n" .
                   "Longitude:        ". $newObjPos->getLongitude() . "\r\n" .
                   "Country:          ". $newObject->getCountry()->getName() . "\r\n" .
                   "Elevation offset: ". $newObjPos->getElevationOffset() . "\r\n" .
                   "True orientation: ". \ObjectUtils::headingSTG2True($newObjPos->getOrientation()) . "\r\n" .
                   "Map:              http://".$_SERVER['SERVER_NAME']."/map/?lon=". $newObjPos->getLongitude() ."&lat=". $newObjPos->getLatitude() ."&z=14\r\n\r\n";

        return new \email\EmailContent($subject, static::format($message));
    }
}
