<?php
namespace submission;

/*
 * Copyright (C) 2014 FlightGear Team
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
 * Description of RequestExecutor
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 */
class RequestExecutor {
    private $modelDAO;
    private $objectDAO;
    private $authorDAO;
    
    public function __construct($modelDAO, $objectDAO, $authorDAO) {
        $this->modelDAO = $modelDAO;
        $this->objectDAO = $objectDAO;
        $this->authorDAO = $authorDAO;
    }
    
    public function executeRequest($request) {
        switch (get_class($request)) {
        case "model\RequestObjectUpdate":
            $this->executeRequestObjectUpdate($request);
            break;
        
        case "model\RequestObjectDelete":
            $this->executeRequestObjectDelete($request);
            break;
        
        case "model\RequestMassiveObjectsAdd":
            return $this->executeRequestMassiveObjectsAdd($request);
            break;
        
        case "model\RequestModelAdd":
            return $this->executeRequestModelAdd($request);
            break;
        
        case "model\RequestModelUpdate":
            $this->executeRequestModelUpdate($request);
            break;
        
        default:
            throw new \Exception("Not a request!");
        }
    }
    
    private function executeRequestObjectUpdate($request) {
        $newObj = $request->getNewObject();
        $this->objectDAO->updateObject($newObj);
    }
    
    private function executeRequestObjectDelete($request) {
        $objId = $request->getObjectToDelete()->getId();
        $this->objectDAO->deleteObject($objId);
    }
    
    private function executeRequestMassiveObjectsAdd($request) {
        $objsWithId = array();
        
        foreach ($request->getNewObjects() as $newObj) {
            $objsWithId[] = $this->objectDAO->addObject($newObj);
        }
        
        return $objsWithId;
    }
    
    private function executeRequestModelAdd($request) {
        $newModel = $request->getNewModel();
        
        $newAuthor = $request->getNewAuthor();
        // If it is a new author
        if ($newAuthor != null) {
            $newAuthorWithId = $this->authorDAO->addAuthor($newAuthor);
            $newModel->getMetadata()->setAuthor($newAuthorWithId);
        }
        
        $newModelWithId = $this->modelDAO->addModel($newModel);
        
        $newObject = $request->getNewObject();
        $newObject->setModelId($newModelWithId->getMetadata()->getId());
        $newObjectWithId = $this->objectDAO->addObject($newObject);
        
        $request->setNewModel($newModelWithId);
        $request->setNewObject($newObjectWithId);
        
        return $request;
    }
    
    private function executeRequestModelUpdate($request) {
        $newModel = $request->getNewModel();
        $this->modelDAO->updateModel($newModel);
    }
}
