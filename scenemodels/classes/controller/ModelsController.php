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

class ModelsController extends ControllerMenu {
    private $objectDaoRO;

    public function __construct() {
        parent::__construct();
        $this->objectDaoRO = \dao\DAOFactory::getInstance()->getObjectDaoRO();
    }
    
    /**
     * Action for models browsing
     */
    public function browseAction() {
        $modelGroupId = $this->getVar('shared');
        $offset = $this->getVar('offset');
        $pagesize = 99;
        
        if ($offset == null || !preg_match(\FormChecker::$regex['pageoffset'],$offset)) {
            $offset = 0;
        }
        
        if ($modelGroupId != null && $modelGroupId >= 0) {
            $group = $this->getModelDaoRO()->getModelsGroup($modelGroupId);
            $title = "Model Browser: ".$group->getName();
            $modelMetadatas = $this->getModelDaoRO()->getModelMetadatasByGroup($modelGroupId, $offset, $pagesize);
        } else {
            $modelMetadatas = $this->getModelDaoRO()->getModelMetadatas($offset, $pagesize);
            $title = "FlightGear Scenery Model Browser";
        }
        
        include 'view/modelbrowser.php';
    }
    
    public function browseRecentAction() {
        $offset = $this->getVar('offset');
        if($offset == null || !preg_match(\FormChecker::$regex['pageoffset'],$offset)){
            $offset=0;
        }

        $pagesize = 10;
        
        $modelMetadatas = $this->getModelDaoRO()->getModelMetadatas($offset, $pagesize);
        
        $objectsByModel = array();
        foreach ($modelMetadatas as $modelMetadata) {
            if ($modelMetadata->getModelsGroup()->isStatic()) {
                $objectsByModel[$modelMetadata->getName()] = $this->objectDaoRO->getObjectsByModel($modelMetadata->getId());
            }
        }
        
        include 'view/models.php';
    }
    
    /**
     * Action for model view
     */
    public function viewAction() {
        $id = $this->getVar('id');
        if (\FormChecker::isModelId($id)) {
            $modelMetadata = $this->getModelDaoRO()->getModelMetadata($id);
            $occurences = $this->objectDaoRO->countObjectsByModel($id);
            
            include 'view/modelview.php';
        } else {
            $pageTitle = "Model ID not valid";
            $errorText = "Sorry, but the model ID you are asking is not valid.";
            include 'view/error_page.php';
        }
    }
    
    /**
     * Display model WebGL viewer action
     */
    public function modelViewerAction() {
        $id = $this->getVar('id');
        if (empty($id) || !\FormChecker::isModelId($id)) {
            return;
        }
        
        $ac3DFile = "app.php?c=Models&a=getAC3D&id=".$id;
        $texturePrefix = 'app.php?c=Models&a=getTexture&id='.$id.'&name=';
        include 'view/model_viewer.php';
    }
    
    /**
     * Gets model thumbnail
     */
    public function thumbnailAction() {
        $id = $this->getVar('id');
        if (\FormChecker::isModelId($id)) {
            $thumbnail = $this->getModelDaoRO()->getThumbnail($id);
            header("Content-type: image/jpg");
            header("Content-Disposition: inline; filename=".$id.".jpg");

            if ($thumbnail != "") {
                echo $thumbnail;
            } else {
                readfile("/img/nothumb.jpg");
            }
        }
    }
    
    public function contentFilesInfosAction() {
        $modelfiles = $this->getModelFiles();
        if ($modelfiles != null) {
            $filesInfos = $modelfiles->getModelFilesInfos();
            include 'view/files_infos_xml.php';
        }
    }
    
    public function getAC3DAction() {
        $modelfiles = $this->getModelFiles();
        if ($modelfiles != null) {
            header("Content-type: application/octet-stream");
            echo $modelfiles->getACFile();
        }
    }
    
    public function getPackageAction() {
        $id = $this->getVar('id');
        if (\FormChecker::isModelId($id)) {
            $modelfiles = $this->getModelDaoRO()->getModelFiles($id);
            header("Content-type: application/x-gtar");
            header("Content-Disposition: inline; filename=".$id.".tgz");
            echo $modelfiles->getPackage();
        }
    }
    
    public function getTextureAction() {
        header("Content-type: image/png");
        echo $this->getRawFile();
    }
    
    public function getRawFile() {
        $modelfiles = $this->getModelFiles();
        $dirArray = preg_split("/\//", $this->getVar('name'));
        $filename = $dirArray[count($dirArray)-1];
        
        return $modelfiles->getFile($filename);
    }
    
    /**
     * Gets file (content type will be guessed from content)
     */
    public function getFileAction() {
        $finfo = new \finfo(FILEINFO_MIME);
        $content = $this->getRawFile();
        header("Content-type: ".$finfo->buffer($content));
        echo $content;
    }
    
    /**
     * Gets model files from id.
     * @return model files.
     */
    private function getModelFiles() {
        $modelfiles = null;
        $id = $this->getVar('id');
        if (\FormChecker::isModelId($id)) {
            $modelfiles = $this->getModelDaoRO()->getModelFiles($id);
        }
        
        return $modelfiles;
    }
}
