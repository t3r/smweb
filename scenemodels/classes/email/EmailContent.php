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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

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

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Host = getenv('SMTPHOST');
        $mail->Port = getenv('SMTPPORT');
        $mail->Username = getenv('SMTPUSER');
        $mail->Password = getenv('SMTPPASSWORD');

        $mail->setFrom('no-reply@flightgear.org', 'FlightGear Scenery Database');

        if( isset($to) ) {
            $mail->addAddress($to);
        }

        if ($backend) {
            $maintainers = getenv('MAINTAINERS');
            if( isset($to) ) {
                $mail->addCC($maintainers);
            } else {
                $mail->addAddress($maintainers);
            }
        } else {
        }

        $mail->Subject = $this->subject;
        $mail->Body = $this->message;

        if (!$mail->send()) {
           error_log( 'Mailer Error: ' . $mail->ErrorInfo );
        }

    }
}
