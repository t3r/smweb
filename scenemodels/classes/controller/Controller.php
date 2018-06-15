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
 * Abstract controller
 */
abstract class Controller {
    protected function getVar($varName) {
        if (isset($_REQUEST[$varName])) {
            return $_REQUEST[$varName];
        } else {
            return null;
        }
    }
    
    /**
     * Check captcha
     * 
     * @return response
     */
    protected function checkCaptcha() {
        if (\Config::isCaptchaEnabled()) {
            // Captcha stuff
            require_once 'inc/captcha/recaptchalib.php';

            // Private key is needed for the server-to-Google auth.
            $privatekey = "6Len6skSAAAAACnlhKXCda8vzn01y6P9VbpA5iqi";
            $resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
            
            return $resp->is_valid;
        }
        
        return true;
    }
    
    protected function displayCaptchaError($xml = false) {
        if ($xml) {
            $errors = array();
            $errors[] = new \model\ErrorInfo("The CAPTCHA is not correct. Please refresh it and try again.");
            include 'view/submission/errors_xml.php';
        } else {
            $pageTitle = "Automated Submission Form";

            $errorText = "Sorry but the reCAPTCHA wasn't entered correctly. <a href='javascript:history.go(-1)'>Go back and try it again</a>" .
                     "<br />".
                     "Don't forget to feed the Captcha, it's a mandatory item as well. Don't know what a Captcha is or what its goal is? Learn more <a href=\"http://en.wikipedia.org/wiki/Captcha\">here</a>.";
            include 'view/error_page.php';
        }
    }
}
