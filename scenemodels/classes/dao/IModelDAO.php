<?php

/**
 * Interface for Model Data Access Object
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

namespace dao;

interface IModelDAO {

    public function addModel($model);

    public function updateModel($model);
    
    /**
     * Gets all model (metadata + files) using the id
     * 
     * @param type $modelId Id of the model
     * @return the model
     */
    public function getModel($modelId);

    public function countTotalModels();
    
    public function countModelsNoThumb();
    
    public function addModelMetadata($modelMetadata);

    public function updateModelMetadata($modelMetadata);
    
    public function getModelMetadata($modelId);
    
    public function getModelMetadataFromPath($modelPath);
    
    /**
     * Get model metadata using STG name
     * @param string $modelName model name (= STG filename)
     */
    public function getModelMetadataFromSTGName($modelName);
    
    public function getModelMetadatas($offset, $pagesize, $criteria, $orderby);

    public function getModelMetadatasByAuthor($authorId);
    
    public function getModelMetadatasByGroup($modelsGroupId, $offset, $pagesize, $orderby);
    
    public function getModelMetadatasNoThumb($offset, $pagesize);
    
    public function getPaths();
    
    public function getModelsGroup($groupId);
    
    public function getModelsGroupByPath($groupPath);
    
    public function getModelsGroups();
    
    public function getModelFiles($modelId);
    
    public function getThumbnail($modelId);
}

?>