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

namespace submission;

/**
 * AuthorValidator
 *
 * @author Julien Nguyen
 */
class AuthorValidator implements Validator {
    private $name;
    private $email;
    
    public static function getAuthorValidator($name, $email) {
        $instance = new self();
        $instance->setName($name);
        $instance->setEmail($email);
        
        return $instance;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    
    public function validate() {
        $exceptions = array();
        
        if (!\FormChecker::isEmail($this->email)) {
            $exceptions[] = new \model\ErrorInfo("Please check email.");
        }

        if (!\FormChecker::isComment($this->name)) {
            $exceptions[] = new \model\ErrorInfo("Please check your name.");
        }
        
        return $exceptions;
    }
}
