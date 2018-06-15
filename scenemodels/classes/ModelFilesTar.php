<?php

/**
 * Model files in a TAR format
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

class ModelFilesTar implements IModelFiles {

    private $modelfile;

    public function __construct($modelfile) {
        $this->modelfile = $modelfile;
    }
    
    public function getPackage() {
        return $this->modelfile;
    }
    
    /**
     * Returns the extension of a file sent in parameter
     * @param string $filepath file path.
     * @return string extension.
     */
    private function showFileExtension($filepath) {
        return pathinfo($filepath, PATHINFO_EXTENSION);
    }

    /**
     * Extracts a tgz file into a temporary directory and returns its path.
     * @param type $archive
     * @return string directory path
     */
    private function openTGZ($archive) {
        // Managing possible concurrent accesses on the maintainer side.
        $targetPath = sys_get_temp_dir() .'/submission_'.rand();

        // Makes concurrent access impossible: the script has to wait if this directory already exists.
        while (file_exists($targetPath)) {
            usleep(500);
        }

        if (mkdir($targetPath)) {
            if (file_exists($targetPath) && is_dir($targetPath)) {
                // Writes the content of $file into submitted_files.tar.gz
                $file = $targetPath.'/submitted_files.tar.gz';
                file_put_contents($file, $archive);                

                $detarCommand = 'tar xvzf '.$file.' -C '.$targetPath. '> /dev/null';
                system($detarCommand);
                
                // Deletes compressed file
                unlink($file);
            }
        } else {
            error_log("Impossible to create ".$targetPath." directory!");
        }

        return $targetPath;
    }
    
    /**
     * Close a temporary directory opened for a tgz file.
     * @param string $targetPath
     */
    private function closeTGZ($targetPath) {
        // Deletes temporary submission directory
        \FileSystemUtils::clearDir($targetPath);
    }

    /**
     * Gets the content of the AC3D file.
     * @return AC3D file content.
     */
    public function getACFile() {
        $targetPath = $this->openTGZ($this->modelfile);
        $dir = opendir($targetPath);
        $content = null;

        while ($file = readdir($dir)) {
            if ($this->showFileExtension($file) == 'ac') {
                $filepath = $targetPath."/".$file;
                $content = file_get_contents($filepath);
                break;
            }
        }
        
        $this->closeTGZ($targetPath);
        
        return $content;
    }
    
    /**
     * Gets the content of the XML file.
     * @return XML file content.
     */
    public function getXMLFile() {
        $targetPath = $this->openTGZ($this->modelfile);
        $dir = opendir($targetPath);
        $content = null;
        
        while ($file = readdir($dir)) {
            if ($this->showFileExtension($file) == 'xml') {
                $filepath = $targetPath."/".$file;
                $content = file_get_contents($filepath);
                break;
            }
        }
        
        $this->closeTGZ($targetPath);
        
        return $content;
    }
    
    public function getTexturesNames() {
        $targetPath = $this->openTGZ($this->modelfile);
        $dir = opendir($targetPath);
        
        $names = array();
        
        while ($filename = readdir($dir)) {
            $extension = $this->showFileExtension($filename);
            if ($extension == 'png' || $extension == 'rgb') {
                $names[] = $filename;
            }
        }
        
        $this->closeTGZ($targetPath);
        
        return $names;
    }
    
    public function getFile($filename) {
        $targetPath = $this->openTGZ($this->modelfile);
        $content = file_get_contents($targetPath."/".$filename);
        
        $this->closeTGZ($targetPath);
        
        return $content;
    }

    public function getModelFilesInfos() {
        $filesInfos = array();
        $targetPath = $this->openTGZ($this->modelfile);
        $dir = opendir($targetPath);

        while ($filename = readdir($dir)) {
            $filepath = $targetPath."/".$filename;
            
            if (is_dir($filepath)) {
                continue;
            }
            
            $fileinfo = new \model\ModelFileInfo();
            $fileinfo->setFilename($filename);
            $fileinfo->setSize(filesize($filepath));
            $filesInfos[] = $fileinfo;
        }
        
        $this->closeTGZ($targetPath);
        
        return $filesInfos;
    }
    
    public function getFileImageInfos($imageName) {
        $targetPath = $this->openTGZ($this->modelfile);
        $infos = getimagesize($targetPath."/".$imageName);
        
        $this->closeTGZ($targetPath);
        
        return $infos;
    }
}

?>
