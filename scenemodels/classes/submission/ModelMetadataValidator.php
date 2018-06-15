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
 * Validator for model's metadata
 *
 * @author Julien Nguyen
 */
class ModelMetadataValidator implements Validator {
    private $name;
    private $description;
    private $authorId;
    private $moGroupId;
    
    public static function getModelMDValidator($name, $description, $authorId, $moGroupId) {
        $instance = new self();
        $instance->setName($name);
        $instance->setDescription($description);
        $instance->setAuthorId($authorId);
        $instance->setModelGroupId($moGroupId); 
        
        return $instance;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function setAuthorId($authorId) {
        $this->authorId = $authorId;
    }
    
    public function setModelGroupId($moGroupId) {
        $this->moGroupId = $moGroupId;
    }
    
    public function validate() {
        $exceptions = array();
        
        if (!\FormChecker::isModelName($this->name)) {
            $exceptions[] = new \model\ErrorInfo("Please check the model name.");
        }
        
        if (!empty($this->description) && !\FormChecker::isComment($this->description)) {
            $exceptions[] = new \model\ErrorInfo("Please check the model description.");
        }

        if (!\FormChecker::isModelGroupId($this->moGroupId)) {
            $exceptions[] = new \model\ErrorInfo("Please check the model group.");
        }

        if (!\FormChecker::isAuthorId($this->authorId)) {
            $exceptions[] = new \model\ErrorInfo("Please check the author value.");
        }
        
        return $exceptions;
    }
}
