<?php

/**
 * Model Data Access Object implementation for PostgreSQL
 *
 * Database layer to access models from PostgreSQL database
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

namespace dao;

class ModelDAO extends PgSqlDAO implements IModelDAO {    
    public function addModel($model) {
        $modelMD = $model->getMetadata();
        
        $query  = 'INSERT INTO fgs_models ';
        $query .= "(mo_id, mo_path, mo_author, mo_name, mo_notes, mo_thumbfile, mo_modelfile, mo_shared) ";
        $query .= "VALUES (";
        $query .= "DEFAULT, ";             // mo_id
        $query .= "'".pg_escape_string($modelMD->getFilename())."', ";  // mo_path
        $query .= pg_escape_string($modelMD->getAuthor()->getId()).", ";          // mo_author
        $query .= "'".pg_escape_string($modelMD->getName())."', ";         // mo_name
        $query .= "'".pg_escape_string($modelMD->getDescription())."', ";        // mo_notes
        $query .= "'".base64_encode($model->getThumbnail())."', ";    // mo_thumbfile
        $query .= "'".base64_encode($model->getModelFiles()->getPackage())."', ";    // mo_modelfile
        $query .= pg_escape_string($modelMD->getModelsGroup()->getId());              // mo_shared
        $query .= ") ";
        $query .= "RETURNING mo_id";
        
        $result = $this->database->query($query);
        
        $returnRow = pg_fetch_row($result);
        $modelMD->setId($returnRow[0]);
        $model->setMetadata($modelMD);
        
        return $model;
    }

    public function updateModel($model) {
        $modelMD = $model->getMetadata();
        
        $query  = 'UPDATE fgs_models ';
        $query .= "SET mo_path = '".pg_escape_string($modelMD->getFilename())."', ".
                  "mo_author = ".pg_escape_string($modelMD->getAuthor()->getId()).", ".
                  "mo_name = '".pg_escape_string($modelMD->getName())."', ".
                  "mo_notes = '".pg_escape_string($modelMD->getDescription())."', ".
                  "mo_thumbfile = '".base64_encode($model->getThumbnail())."', ".
                  "mo_modelfile = '".base64_encode($model->getModelFiles()->getPackage())."', ".
                  "mo_shared = ".pg_escape_string($modelMD->getModelsGroup()->getId());
        $query .= " WHERE mo_id = ".$modelMD->getId();
        
        $result = $this->database->query($query);
        
        if (!$result) {
            throw new \Exception('Updating model failed!');
        }
    }
    
    public function getModel($modelId) {
        $result = $this->database->query("SELECT * FROM fgs_models, fgs_authors, fgs_modelgroups ".
                                         "WHERE mo_id = ".pg_escape_string($modelId)." AND au_id = mo_author AND mg_id = mo_shared");
        if (!$result) {
            throw new \Exception('Model '.$modelId.' not found!');
        }
        
        $row = pg_fetch_assoc($result);

        return $this->getModelFromRow($row);
    }
    
    public function addModelMetadata($modelMetadata) {
        //TODO
    }

    public function updateModelMetadata($modelMetadata){
        //TODO
    }
    
    public function getModelMetadata($modelId){
        $result = $this->database->query('SELECT mo_id, mo_path, mo_name, mo_notes, mo_modified, '.
                                         'mg_id, mg_name, mg_path, au_id, au_name, au_email, au_notes '.
                                         'FROM fgs_models, fgs_authors, fgs_modelgroups '.
                                         'WHERE mo_id = '.pg_escape_string($modelId).' AND au_id = mo_author AND mg_id = mo_shared');
        $row = pg_fetch_assoc($result);
        
        return $this->getModelMetadataFromRow($row);
    }
    
    public function getModelMetadataFromPath($modelPath) {
        $result = $this->database->query("SELECT mo_id, mo_path, mo_name, mo_notes, mo_modified, ".
                                   "mg_id, mg_name, mg_path, au_id, au_name, au_email, au_notes ".
                                   "FROM fgs_models, fgs_authors, fgs_modelgroups ".
                                   "WHERE mo_path='".pg_escape_string($modelPath)."' AND au_id = mo_author AND mg_id = mo_shared;");
        $row = pg_fetch_assoc($result);
        
        if (!$row) {
            throw new \Exception('Model '.$modelPath.' not found!');
        }
        
        return $this->getModelMetadataFromRow($row);
    }
    
    public function getModelMetadataFromSTGName($modelName) {
        // Explodes the fields of the string separated by /
        $tabPath = explode('/',$modelName);
        // Returns the last field value.
        $queriedModelPath = $tabPath[count($tabPath)-1];
        
        return $this->getModelMetadataFromPath($queriedModelPath);
    }
    
    public function countTotalModels() {
        $result = $this->database->query('SELECT COUNT(*) AS number ' .
                                        'FROM fgs_models;');
        $row = pg_fetch_assoc($result);
        
        return $row['number'];
    }
    
    public function countModelsNoThumb() {
        $result = $this->database->query('SELECT COUNT(*) AS number ' .
                                         'FROM fgs_models ' .
                                         'WHERE mo_thumbfile IS NULL;');
                                         
        $row = pg_fetch_assoc($result);
        
        return $row['number'];
    }
    
    private function getModelFromRow($row) {
        $modelMetadata = $this->getModelMetadataFromRow($row);
        
        $model = new \model\Model();
        $model->setMetadata($modelMetadata);
        $model->setModelFiles(new \ModelFilesTar(base64_decode($row['mo_modelfile'])));
        $model->setThumbnail(base64_decode($row['mo_thumbfile']));
        
        return $model;
    }
    
    private function getModelMetadataFromRow($row) {
        $author = new \model\Author();
        $author->setId($row['au_id']);
        $author->setName($row['au_name']);
        $author->setEmail($row['au_email']);
        $author->setDescription($row['au_notes']);

        $modelsGroup = $this->getModelsGroupFromRow($row);
        
        $modelMetadata = new \model\ModelMetadata();
        $modelMetadata->setId($row['mo_id']);
        $modelMetadata->setAuthor($author);
        $modelMetadata->setFilename($row['mo_path']);
        $modelMetadata->setName($row['mo_name']);
        $modelMetadata->setDescription($row['mo_notes']);
        $modelMetadata->setModelsGroup($modelsGroup);
        $modelMetadata->setLastUpdated(new \DateTime($row['mo_modified']));

        return $modelMetadata;
    }
    
    private function getModelsGroupFromRow($row) {
        $modelsGroup = new \model\ModelsGroup();
        $modelsGroup->setId($row['mg_id']);
        $modelsGroup->setName($row['mg_name']);
        $modelsGroup->setPath($row['mg_path']);
        
        return $modelsGroup;
    }
    
    
    public function getModelMetadatas($offset, $pagesize, $criteria=null, $orderby="mo_modified", $order="DESC") {
        // Generating WHERE clause from criteria
        $whereClause = $this->generateWhereClauseCriteria($criteria);
        if ($whereClause != "") {
            $whereClause .= " AND"; 
        }
        
        $result = $this->database->query("SELECT mo_id, mo_path, mo_name, mo_notes, mo_modified, ".
                                         "mg_id, mg_name, mg_path, au_id, au_name, au_email, au_notes ".
                                         "FROM fgs_models, fgs_authors, fgs_modelgroups ".
                                         "WHERE $whereClause au_id = mo_author AND mg_id = mo_shared ".
                                         "ORDER BY ".$orderby." ".$order." LIMIT ".$pagesize." OFFSET ".$offset.";");
        
        $resultArray = array();
                           
        while ($row = pg_fetch_assoc($result)) {
            $resultArray[] = $this->getModelMetadataFromRow($row);
        }
        
        return $resultArray;
    }
    
    public function getModelMetadatasNoThumb($offset, $pagesize) {
        $criteria = array();
        $criteria[] = new Criterion("mo_thumbfile", Criterion::OPERATION_EQ, NULL, Criterion::STRINGTYPE);
        return $this->getModelMetadatas($offset, $pagesize, $criteria);
    }
    
    public function getModelMetadatasByAuthor($authorId) {
        $criteria = array();
        $criteria[] = new Criterion("mo_author", Criterion::OPERATION_EQ, $authorId, Criterion::INTTYPE);
        return $this->getModelMetadatas(0, "ALL", $criteria);
    }
    
    public function getModelMetadatasByGroup($modelGroupId, $offset, $pagesize, $orderby="mo_modified", $order="ASC") {
        $criteria = array();
        $criteria[] = new Criterion("mo_shared", Criterion::OPERATION_EQ, $modelGroupId, Criterion::INTTYPE);
        return $this->getModelMetadatas($offset, $pagesize, $criteria, $orderby, $order);
    }
    
    public function getPaths() {
        $result = $this->database->query("SELECT mo_id, mo_path ".
                                         "FROM fgs_models ".
                                         "ORDER BY mo_path ASC;");
        
        $resultArray = array();
                           
        while ($row = pg_fetch_assoc($result)) {
            $resultArray[$row["mo_id"]] = $row["mo_path"];
        }
        
        return $resultArray;
    }
    
    public function getModelsGroup($groupId) {
        $result = $this->database->query("SELECT mg_id, mg_name, mg_path ".
                                         "FROM fgs_modelgroups ".
                                         "WHERE mg_id = ".pg_escape_string($groupId).";");
        $row = pg_fetch_assoc($result);
        
        if (!$row) {
            throw new \Exception("Models group ".$groupId." not found!");
        }
        
        return $this->getModelsGroupFromRow($row);
    }
    
    public function getModelsGroupByPath($groupPath) {
        $result = $this->database->query("SELECT mg_id, mg_name, mg_path ".
                                         "FROM fgs_modelgroups ".
                                         "WHERE mg_path = '".pg_escape_string($groupPath)."';");
        
        $row = pg_fetch_assoc($result);
        
        if (!$row) {
            throw new \Exception("Models group ".$groupPath." not found!");
        }
        
        return $this->getModelsGroupFromRow($row);
    }
    
    public function getModelsGroups() {
        $result = $this->database->query("SELECT mg_id, mg_name, mg_path ".
                                         "FROM fgs_modelgroups ".
                                         "ORDER BY mg_name;");
        
        $resultArray = array();
                           
        while ($row = pg_fetch_assoc($result)) {
            $resultArray[] = $this->getModelsGroupFromRow($row);
        }
        
        return $resultArray;
    }
    
    public function getModelFiles($modelId) {
        $result = $this->database->query("SELECT mo_modelfile FROM fgs_models WHERE mo_id=".$modelId.";");
        $row = pg_fetch_assoc($result);
        
        return new \ModelFilesTar(base64_decode($row["mo_modelfile"]));
    }

    public function getThumbnail($modelId) {
        $result = $this->database->query("SELECT mo_thumbfile FROM fgs_models WHERE mo_id=".$modelId.";");
        $row = pg_fetch_assoc($result);
        
        return base64_decode($row["mo_thumbfile"]);
    }
}

?>