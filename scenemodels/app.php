<?php

/* 
 * Copyright (C) 2015 FlightGear team
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
require_once 'autoload.php';

if( array_key_exists( 'c', $_GET ) && array_key_exists( 'a', $_GET ) ) {
  $controllerName = '\\controller\\'.ucfirst($_GET['c']).'Controller';
  $actionName = strtolower($_GET['a']).'Action';
  // Call the action
  $controller = new $controllerName;
  $controller->$actionName();
} else {
  $controller = new \controller\IndexController;
  $controller->indexAction();
}

/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPDebug = SMTP::DEBUG_LOWLEVEL;
$mail->Host = 'email-smtp.us-east-1.amazonaws.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Username = 'AKIAI3CYIYVHSZGHAZ2Q';
$mail->Password = 'AtFlOFnATsn6iOwCWT0PNGdpUFBS1BZT/X+lAzuKMql7';

$mail->setFrom('noreply@flightgear.org', 'Noreply at FlightGear');
$mail->addAddress('torsten@t3r.de', 'Torsten Dreyer');
$mail->Subject = 'PHPMailer test';
$mail->Body = 'These are great news. Howdy.';

if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}
*/
