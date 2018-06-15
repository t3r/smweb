<?php
namespace dao;

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
 * Request DAO implementation for PostGreSQL
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 */
class RequestDAO extends PgSqlDAO implements IRequestDAO {
    private $objectDao;
    private $modelDao;
    private $authorDao;
    
    public function __construct(PGDatabase $database, ObjectDAO $objectDao,
            ModelDAO $modelDAO, AuthorDAO $authorDAO) {
        parent::__construct($database);
        $this->objectDao = $objectDao;
        $this->modelDao = $modelDAO;
        $this->authorDao = $authorDAO;
    }
    
    public function getRequest($id) {
        $result = $this->database->query("SELECT spr_id, spr_hash, spr_base64_sqlz ".
                                         "FROM fgs_position_requests ".
                                         "WHERE spr_id = ". pg_escape_string($id) .";");
        $row = pg_fetch_assoc($result);
        
        if (!$row) {
            throw new RequestNotFoundException('No request with id '. $id. ' was found!');
        }
        
        return $this->getRequestFromRow($row);
    }
    
    public function getRequestFromSig($sig) {
        $result = $this->database->query("SELECT spr_id, spr_hash, spr_base64_sqlz ".
                                         "FROM fgs_position_requests ".
                                         "WHERE spr_hash = '". pg_escape_string($sig) ."';");
        $row = pg_fetch_assoc($result);
        
        if (!$row) {
            throw new RequestNotFoundException('No request with sig '. $sig. ' was found!');
        }
        
        return $this->getRequestFromRow($row);
    }
    
    public function saveRequest($request) {
        $reqStr = $this->serializeRequest($request);
        
        $zippedQuery = gzcompress($reqStr,8);
        $encodedReqStr = base64_encode($zippedQuery);
        
        $shaToCompute = '<'.microtime().'><'.$_SERVER['REMOTE_ADDR'].'><'.$encodedReqStr.'>';
        $sig = hash('sha256', $shaToCompute);
        
        $query = "INSERT INTO fgs_position_requests (spr_id, spr_hash, spr_base64_sqlz) VALUES (DEFAULT, '".$sig."', '".$encodedReqStr."') RETURNING spr_id;";
        
        $result = $this->database->query($query);
        
        if (!$result) {
            throw new \Exception('Adding object failed!');
        }
        
        $returnRow = pg_fetch_row($result);
        $request->setId($returnRow[0]);
        $request->setSig($sig);
        return $request;
    }
    
    private function serializeRequest($request) {
        switch (get_class($request)) {
        case 'model\RequestObjectUpdate':
            $type = 'OBJECT_UPDATE';
            $reqContentArray = $this->arrayRequestObjectUpdate($request);
            break;
        
        case 'model\RequestObjectDelete':
            $type = 'OBJECT_DELETE';
            $reqContentArray = $this->arrayRequestObjectDelete($request);
            break;
        
        case 'model\RequestMassiveObjectsAdd':
            $type = 'OBJECTS_ADD';
            $reqContentArray = $this->arrayRequestMassiveObjectsAdd($request);
            break;
        
        case 'model\RequestModelAdd':
            $type = 'MODEL_ADD';
            $reqContentArray = $this->arrayRequestModelAdd($request);
            break;
        
        case 'model\RequestModelUpdate':
            $type = 'MODEL_UPDATE';
            $reqContentArray = $this->arrayRequestModelUpdate($request);
            break;
        
        default:
            throw new \Exception('Not a request!');
        }
        
        $reqArray = array('type'=>$type,
                     'email'=>$request->getContributorEmail(),
                     'comment'=>$request->getComment(),
                     'content'=>$reqContentArray);
        
        return json_encode($reqArray);
    }
    
    private function arrayObject(\model\Object $object) {
        $objPos = $object->getPosition();
        $offset = $objPos->getElevationOffset();
        
        return array('description'=>$object->getDescription(),
                     'longitude'=>$objPos->getLongitude(),
                     'latitude'=>$objPos->getLatitude(),
                     'offset'=>(empty($offset)?'NULL':$offset),
                     'orientation'=>$objPos->getOrientation(),
                     'country'=>$object->getCountry()->getCode(),
                     'modelId'=>$object->getModelId());
    }
    
    private function arrayRequestObjectUpdate($request) {
        $newObj = $request->getNewObject();
        $newObjPos = $newObj->getPosition();
        $offset = $newObjPos->getElevationOffset();
        
        return array('description'=>$newObj->getDescription(),
                     'longitude'=>$newObjPos->getLongitude(),
                     'latitude'=>$newObjPos->getLatitude(),
                     'offset'=>(empty($offset)?'NULL':$offset),
                     'orientation'=>$newObjPos->getOrientation(),
                     'country'=>$newObj->getCountry()->getCode(),
                     'modelId'=>$newObj->getModelId(),
                     'objectId'=>$newObj->getId());
    }
    
    private function arrayRequestObjectDelete($request) {
        $objToDel = $request->getObjectToDelete();
        
        return array("objId"=>$objToDel->getId());
    }
    
    private function arrayRequestMassiveObjectsAdd($request) {
        $newObjects = $request->getNewObjects();
        
        // Proceed on with the request generation
        $reqArray = array();
        
        // For each line, add the data content to the request
        foreach ($newObjects as $newObj) {
            $reqArray[] = $this->arrayObject($newObj);
        }
        
        return $reqArray;
    }
    
    private function arrayRequestModelAdd($request) {
        $newModel = $request->getNewModel();
        $newModelMD = $newModel->getMetadata();
        $newObject = $request->getNewObject();
        $newAuthor = $request->getNewAuthor();
        
        $moArray = array('filename'=>$newModelMD->getFilename(),
                     'author'=>$newModelMD->getAuthor()->getId(),
                     'name'=>$newModelMD->getName(),
                     'description'=>$newModelMD->getDescription(),
                     'thumbnail'=>$newModel->getThumbnail(),
                     'modelfiles'=>$newModel->getModelFiles(),
                     'modelgroup'=>$newModelMD->getModelsGroup()->getId());

        // object
        $obArray = $this->arrayObject($newObject);
        
        // possible new author
        if ($newAuthor != null) {
            $authorArray = array('name'=>$newModelMD->getAuthor()->getName(),
                            'email'=>$newModelMD->getAuthor()->getEmail());
            
            return array('model'=>$moArray,
                    'object'=>$obArray,
                    'author'=>$authorArray);
        }

        return array('model'=>$moArray,
                    'object'=>$obArray);
    }
    
    private function arrayRequestModelUpdate($request) {
        $newModel = $request->getNewModel();
        $newModelMD = $newModel->getMetadata();
        
        return array('filename'=>$newModelMD->getFilename(),
                     'author'=>$newModelMD->getAuthor()->getId(),
                     'name'=>$newModelMD->getName(),
                     'description'=>$newModelMD->getDescription(),
                     'thumbnail'=>$newModel->getThumbnail(),
                     'modelfiles'=>$newModel->getModelFiles(),
                     'modelgroup'=>$newModelMD->getModelsGroup()->getId(),
                     'modelid'=>$newModelMD->getId());
    }
    
    public function deleteRequest($sig) {
        // Checking the presence of sig into the database
        $result = $this->database->query("SELECT 1 FROM fgs_position_requests WHERE spr_hash = '". pg_escape_string($sig) ."';");
        $row = pg_fetch_assoc($result);
        // If not ok...
        if (!$row) {
            throw new RequestNotFoundException('No request with sig '. $sig. ' was found!');
        }
        
        // Delete the entry from the pending query table.
        $resultdel = $this->database->query("DELETE FROM fgs_position_requests WHERE spr_hash = '". pg_escape_string($sig) ."';");

        return $resultdel != FALSE;
    }
    
    public function getPendingRequests() {
        $result = $this->database->query('SELECT spr_id, spr_hash, spr_base64_sqlz '.
                                         'FROM fgs_position_requests '.
                                         'ORDER BY spr_id ASC;');
        $resultArray = array();
        $okArray = array();
        $failedArray = array();
                           
        while ($row = pg_fetch_assoc($result)) {
            try {
                $okArray[] = $this->getRequestFromRow($row);
            } catch (\Exception $ex) {
                error_log('Error with request '.$row['spr_id'].': '. $ex->getMessage());
                $failedArray[] = new \model\RequestError($row['spr_id'], $row['spr_hash'], $ex->getMessage());
            }
        }
        
        $resultArray['ok'] = $okArray;
        $resultArray['failed'] = $failedArray;
        
        return $resultArray;
    }
    
    private function getRequestFromRow($requestRow) {
        // Decoding in Base64. Dezipping the Base64'd request.
        $requestJson = gzuncompress(base64_decode($requestRow['spr_base64_sqlz']));
        
        $requestArray = json_decode($requestJson, true);
        $requestType = $requestArray['type'];
        $requestContentArray = $requestArray['content'];
        
        // Delete object request
        if ($requestType == 'OBJECT_DELETE') {
            $request = $this->getRequestObjectDeleteFromRow($requestContentArray);
        }
        
        // Update object request
        if ($requestType == 'OBJECT_UPDATE') {
            $request = $this->getRequestObjectUpdateFromRow($requestContentArray);
        }

        // Add objects request
        if ($requestType == 'OBJECTS_ADD') {
            $request = $this->getRequestMassiveObjectsAddFromRow($requestContentArray);
        }
        
        // Add model request
        if ($requestType == 'MODEL_ADD') {
            $request = $this->getRequestModelAddFromRow($requestContentArray);
        }
        
        // Update model request
        if ($requestType == 'MODEL_UPDATE') {
            $request = $this->getRequestModelUpdateFromRow($requestContentArray);
        }
        
        if (isset($request)) {
            $request->setId($requestRow['spr_id']);
            $request->setSig($requestRow['spr_hash']);
            $request->setComment($requestArray['comment']);
            $request->setContributorEmail($requestArray['email']);
            
            return $request;
        } else {
            throw new \Exception('Error reading request: '. $requestQuery);
        }
    }
    
    private function getRequestModelAddFromRow($reqArr) {
        $modelArr = $reqArr['model'];
        $objArr = $reqArr['object'];
        
        $modelFactory = new \ModelFactory($this->modelDao, $this->authorDao);
        $modelMD = $modelFactory->createModelMetadata(-1, $modelArr['author'],
                $modelArr['filename'], $modelArr['name'],$modelArr['description'], $modelArr['modelgroup']);
        $newModel = new \model\Model();
        $newModel->setMetadata($modelMD);
        $newModel->setModelFiles(new \ModelFilesTar(base64_decode($modelArr['modelfiles'])));
        $newModel->setThumbnail(base64_decode($modelArr['thumbnail']));

        // Retrieve OBJECT data from query
        $newObject = $this->getObjectFromRow($objArr);
        
        $requestModelAdd = new \model\RequestModelAdd();
        
        // Retrieve new author if exists
        if (isset($reqArr['author']) || array_key_exists('author', $reqArr)) {
            $authorArr = $reqArr['author'];
            $newAuthor = new \model\Author();
            $newAuthor->setId($modelArr['author']);
            $newAuthor->setName($authorArr["name"]);
            $newAuthor->setEmail($authorArr["email"]);
            
            $requestModelAdd->setNewAuthor($newAuthor);
            $newModel->getMetadata()->setAuthor($newAuthor);
        }
        
        
        $requestModelAdd->setNewModel($newModel);
        $requestModelAdd->setNewObject($newObject);
        
        return $requestModelAdd;
    }
    
    private function getRequestModelUpdateFromRow($modelArr) {
        // Retrieve data from query
        $modelFactory = new \ModelFactory($this->modelDao, $this->authorDao);
        $modelMD = $modelFactory->createModelMetadata($modelArr['modelid'],
                $modelArr['author'], $modelArr['filename'], $modelArr['name'],
                $modelArr['description'], $modelArr['modelgroup']);
        
        $newModel = new \model\Model();
        $newModel->setMetadata($modelMD);
        $newModel->setModelFiles(new \ModelFilesTar(base64_decode($modelArr['modelfiles'])));
        $newModel->setThumbnail(base64_decode($modelArr['thumbnail']));

        // Retrieve old model
        $oldModel = $this->modelDao->getModel($modelMD->getId());
        
        $requestModelUpd = new \model\RequestModelUpdate();
        $requestModelUpd->setNewModel($newModel);
        $requestModelUpd->setOldModel($oldModel);
        
        return $requestModelUpd;
    }
    
    private function getObjectFromRow($addReqArray) {
        $objectFactory = new \ObjectFactory($this->objectDao);
        
        return $objectFactory->createObject(-1, $addReqArray['modelId'],
               $addReqArray['longitude'], $addReqArray['latitude'], $addReqArray['country'], 
               $addReqArray['offset'], $addReqArray['orientation'], 1, $addReqArray['description']);
    }
    
    private function getRequestMassiveObjectsAddFromRow($objRequests) {
        $newObjects = array();
        
        foreach ($objRequests as $objRequest) {
            $newObject = $this->getObjectFromRow($objRequest);
            $newObjects[] = $newObject;
        }
        
        $requestMassObjAdd = new \model\RequestMassiveObjectsAdd();
        $requestMassObjAdd->setNewObjects($newObjects);
        
        return $requestMassObjAdd;
    }
    
    private function getRequestObjectUpdateFromRow($updReqArray) {
        $objectFactory = new \ObjectFactory($this->objectDao);

        $newObject = $objectFactory->createObject($updReqArray['objectId'], $updReqArray['modelId'],
               $updReqArray['longitude'], $updReqArray['latitude'], $updReqArray['country'], 
               $updReqArray['offset'], $updReqArray['orientation'], 1, $updReqArray['description']);

        $requestObjUp = new \model\RequestObjectUpdate();
        $requestObjUp->setContributorEmail('');
        $requestObjUp->setComment('');
        $requestObjUp->setNewObject($newObject);
        $requestObjUp->setOldObject($this->objectDao->getObject($updReqArray['objectId']));
        
        return $requestObjUp;
    }
    
    private function getRequestObjectDeleteFromRow($delRequestArray) {
        $objectToDel = $this->objectDao->getObject($delRequestArray['objId']);

        $requestObjDel = new \model\RequestObjectDelete();
        
        // Not available with actual DAO
        $requestObjDel->setContributorEmail('');
        $requestObjDel->setComment('');
        
        $requestObjDel->setObjectToDelete($objectToDel);

        return $requestObjDel;
    }
}
?>