<?php

/*
 * Copyright (C) 2014 Flightgear Team
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

/**
 * ModelFactory class
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class ModelFactory {
    
    public $modelDaoRo;
    public $authorDaoRo;
    
    public function __construct($modelDaoRo, $authorDaoRo) {
        $this->modelDaoRo = $modelDaoRo;
        $this->authorDaoRo = $authorDaoRo;
    }
    
    /**
     * 
     * @param string $id
     * @param string $authorId id of the model's author
     * @param string $filename model filename
     * @param string $name model name
     * @param string $desc model description
     * @param string $modelsGroupId
     * @return \ModelMetadata
     */
    public function createModelMetadata($id, $authorId, $filename, $name, $desc, $modelsGroupId) {
        $modelMetadata = new \model\ModelMetadata();
        
        $author = $this->authorDaoRo->getAuthor($authorId);
        $modelsGroup = $this->modelDaoRo->getModelsGroup($modelsGroupId);
        
        $modelMetadata->setId($id);
        $modelMetadata->setAuthor($author);
        $modelMetadata->setFilename($filename);
        $modelMetadata->setName($name);
        $modelMetadata->setDescription($desc);
        $modelMetadata->setModelsGroup($modelsGroup);

        return $modelMetadata;
    }
}
