<?php

/**
 * Interface for Model Files
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

interface IModelFiles {

    public function getPackage();

    /**
     * Gets AC3D file content
     * @return string AC file content
     */
    public function getACFile();
    
    /**
     * Gets XML file content, or null if there is no XML
     * @return string XML file content, or null if there is no XML
     */
    public function getXMLFile();
    
    /**
     * Gets an array of all textures filenames
     * @return array[string] array of filenames
     */
    public function getTexturesNames();
    
    /**
     * Gets the file content
     * @param string $filename filename
     */
    public function getFile($filename);
}

?>