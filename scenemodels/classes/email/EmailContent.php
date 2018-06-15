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
 * Email content
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 */
class EmailContent {
    private $subject;
    private $message;
    
    public function __construct($subject, $message) {
        $this->subject = $subject;
        $this->message = $message;
    }
    
    public function getSubject() {
        return $this->subject;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function sendEmail($to, $backend) {
        $from = "\"FlightGear Scenery Database\" <no-reply@flightgear.org>";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "From: " . $from . "\r\n";
        if ($backend) {
            // Setting maintainers (will have to be moved somewhere on sphere)
//            include "/srv/sceneryweb/maintainers";
            if( $to !== '' ) {
                // sourceforge mailing-list does not like to be in Bcc
                $maintainers = "Cc: FlightGear Scenemodels Review <flightgear-scenemodels-review@lists.sourceforge.net>" ."\r\n";
                $headers .= $maintainers;
            } else {
                $to = "FlightGear Scenemodels Review <flightgear-scenemodels-review@lists.sourceforge.net>" ."\r\n";
            }
        }
        $headers .= "X-Mailer: PHP-" . phpversion() . "\r\n";
//error_log($this->message);
        mail($to, $this->subject, $this->message, $headers);
    }
}
