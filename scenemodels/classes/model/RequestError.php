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

namespace model;

/**
 * RequestError
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 */
class RequestError {
    private $id;
    private $sig;
    private $comment;
    
    public function __construct($id, $sig, $comment) {
        $this->id = $id;
        $this->sig = $sig;
        $this->comment = $comment;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setSig($sig) {
        $this->sig = $sig;
    }
    
    public function getSig() {
        return $this->sig;
    }
    
    public function setComment($comment) {
        $this->comment = $comment;
    }
    
    public function getComment() {
        return $this->comment;
    }
}
