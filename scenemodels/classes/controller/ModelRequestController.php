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
 * Common controller for model request controllers
 *
 * @author Julien Nguyen
 */
class ModelRequestController extends RequestController {
    private $modelChecker;
    
    public function __construct() {
        parent::__construct();
        $this->modelChecker = new \ModelChecker();
    }
    
    protected function checkFilesArray() {
        if (isset($_FILES['xml_file'])) {
            $exceptions = array_merge($this->modelChecker->checkAC3DFileArray($_FILES['ac3d_file']),
                array_merge($this->modelChecker->checkXMLFileArray($_FILES['xml_file']),
                $this->modelChecker->checkThumbFileArray($_FILES['mo_thumbfile'])));
        } else {
            $exceptions = array_merge($this->modelChecker->checkAC3DFileArray($_FILES['ac3d_file']),
                $this->modelChecker->checkThumbFileArray($_FILES['mo_thumbfile']));
        }
        

        // PNG Files
        if (isset($_FILES['png_file'])) {
            for ($i=0; $i<count($_FILES['png_file']['name']); $i++) {
                if (!empty($_FILES['png_file']['name'][$i])) {
                    $arrayPNG = array();
                    $arrayPNG['name'] = $_FILES['png_file']['name'][$i];
                    $arrayPNG['type'] = $_FILES['png_file']['type'][$i];
                    $arrayPNG['size'] = $_FILES['png_file']['size'][$i];
                    $arrayPNG['error'] = $_FILES['png_file']['error'][$i];
                    $arrayPNG['tmp_name'] = $_FILES['png_file']['tmp_name'][$i];

                    $exceptionsPNG = $this->modelChecker->checkPNGArray($arrayPNG);
                    $exceptions = array_merge($exceptions, $exceptionsPNG);
                }
            }
        }
        
        return $exceptions;
    }
    
    protected function moveFilesToTMPDir($targetPath, $xmlName, $ac3dName, $thumbName) {
        $exceptions = array();
        
        if (!empty($xmlName)) {
            $xmlPath = $targetPath.$xmlName;
            // move XML file to temp dir
            if (!move_uploaded_file($_FILES['xml_file']['tmp_name'], $xmlPath)) {
                $exceptions[] = new \model\ErrorInfo("There has been an error while moving the file \"".$xmlName."\" on the server.");
            }
        }
        $thumbPath = $targetPath.$thumbName;
        $ac3dPath  = $targetPath.$ac3dName;

        // move A3CD file to temp dir
        if (!move_uploaded_file($_FILES['ac3d_file']['tmp_name'], $ac3dPath)) {
            $exceptions[] = new \model\ErrorInfo("There has been an error while moving the file \"".$ac3dName."\" on the server.");
        }

        // move Thumbnail file to temp dir
        if (!move_uploaded_file($_FILES['mo_thumbfile']['tmp_name'], $thumbPath)) {
            $exceptions[] = new \model\ErrorInfo("There has been an error while moving the file \"".$thumbName."\" on the server.");
        }

        // move PNG files to temp dir
        if (isset($_FILES['png_file'])) {
            for ($i=0; $i<count($_FILES['png_file']['name']); $i++) {
                if (!empty($_FILES['png_file']['name'][$i])
                        && !move_uploaded_file($_FILES['png_file']['tmp_name'][$i], $targetPath.$_FILES['png_file']['name'][$i])) {
                    $exceptions[] = new \model\ErrorInfo("There has been an error while moving the file \"".$_FILES['png_file']['name'][$i]."\" on the server."); 
                }
            }
        }
        
        return $exceptions;
    }
    
    protected function checkFiles($targetPath, $xmlName, $ac3dName, $thumbName, $pngNames) {
        /** STEP 4 : CHECK FILES */
        $validatorsSet = new \submission\ValidatorsSet();
        if (!empty($xmlName) != "") {
            $modelFilesValidator = \submission\ModelFilesValidator::instanceWithXML($targetPath, $xmlName, $ac3dName, $pngNames);
        } else {
            $modelFilesValidator = \submission\ModelFilesValidator::instanceWithAC3DOnly($targetPath, $ac3dName, $pngNames);
        }
        $thumbValidator = new \submission\ThumbValidator($targetPath.$thumbName);
        $filenamesValidator = new \submission\FilenamesValidator($ac3dName, $xmlName, $pngNames);
        $validatorsSet->addValidator($modelFilesValidator);
        $validatorsSet->addValidator($thumbValidator);
        $validatorsSet->addValidator($filenamesValidator);

        return $validatorsSet->validate();
    }
    
    protected function displayModelErrors($errors, $xml = false) {
        if ($xml) {
            include 'view/submission/errors_xml.php';
        } else {
            $this->displayModelErrorsHTML($errors);
        }
    }
    
    private function displayModelErrorsHTML($errors) {
        include 'view/header.php';
        $errormsg = "";
        foreach ($errors as $error) {
            $errormsg .= "<li>".$error->getMessage()."</li>";
        }

        echo "<h2>Oops, something went wrong</h2>" .
             "Error message(s)  : <br/>" .
             "<ul>".$errormsg."</ul><br/>" .
             "<a href='javascript:history.go(-1)'>Go back and correct your mistakes</a>.<br/><br/>" .
             "You can also ask the <a href=\"http://sourceforge.net/mailarchive/forum.php?forum_name=flightgear-devel\">mailing list</a> " .
             "or the <a href=\"http://www.flightgear.org/forums/viewtopic.php?f=5&t=14671\">forum</a> for help!";

        include 'view/footer.php';
    }
    
    protected function prepareThumbFile($thumbPath) {
        $handle    = fopen($thumbPath, "r");
        $contents  = fread($handle, filesize($thumbPath));
        fclose($handle);
        return base64_encode($contents);             // Dump & encode the file
    }
    
    protected function prepareModelFile($targetPath, $xmlName, $ac3dName) {
        if (!empty($xmlName)) {
            $this->modelChecker->dos2Unix($targetPath.$xmlName);
        }
        $this->modelChecker->dos2Unix($targetPath.$ac3dName);
        
        return $this->modelChecker->archiveModel($targetPath);
    }
    
    public function getModelChecker() {
        return $this->modelChecker;
    }
    
    /**
     * Checks if the model path already exists in DB.
     * @param string $proposedPath
     * @return bool true if the path already exists, false otherwise
     */
    public function pathExists($proposedPath) {
        try {
            $this->getModelDaoRO()->getModelMetadataFromPath($proposedPath);
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
