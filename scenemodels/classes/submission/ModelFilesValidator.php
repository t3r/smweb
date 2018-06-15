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

namespace submission;

/**
 * Validator for model files
 *
 * @author Julien Nguyen
 */
class ModelFilesValidator implements Validator {
    
    static private $validDimension = array(1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048, 4096, 8192);
    
    private $folderPath;
    private $ac3dName;
    private $xmlName;
    private $pngNames;
    
    protected function __construct($folderPath, $ac3dName, $pngNames) {
        $this->folderPath = $folderPath;
        $this->ac3dName = $ac3dName;
        $this->pngNames = $pngNames;
    }
    
    static function instanceWithAC3DOnly($folderPath, $ac3dName, $pngNames) {
        return new self($folderPath, $ac3dName, $pngNames);
    }
    
    static function instanceWithXML($folderPath, $xmlName, $ac3dName, $pngNames) {
        $instance = new self($folderPath, $ac3dName, $pngNames);
        $instance->setXMLName($xmlName);
        return $instance;
    }
    
    public function validate() {
        $exceptions = array();
        
        // Check XML if set
        if (isset($this->xmlName)) {
            $xmlPath = $this->folderPath . $this->xmlName;
            if (file_exists($xmlPath)) {
                $exceptions += $this->checkXML($xmlPath);
            } else {
                $exceptions[] = new \model\ErrorInfo("XML file not found");
            }
        }

        // Check AC3D file
        $ac3dPath = $this->folderPath . $this->ac3dName;
        $exceptions += $this->checkAC3D($ac3dPath, $this->pngNames);

        // Check textures files
        for ($i=0; $i<12; $i++) {
            if (isset($this->pngNames[$i]) && ($this->pngNames[$i] != '')) {
                $pngPath  = $this->folderPath . $this->pngNames[$i];
                $pngName  = $this->pngNames[$i];

                $exceptions += $this->checkPNG($pngName, $pngPath);
            }
        }
        
        return $exceptions;
    }
    
    private function checkXML($xmlPath) {
        $errors = array();
        $this->depth = array();
        $xmlParser = xml_parser_create();

        xml_set_object($xmlParser, $this);
        xml_set_element_handler($xmlParser, "startElement", "endElement");

        $fp = fopen($xmlPath, "r");
        if (!$fp) {
            $errors[] = new \model\ErrorInfo("Could not open XML.");
        } else {
            while ($data = fread($fp, 4096)) {
                // check if tags are closed and if <PropertyList> is present
                if (!xml_parse($xmlParser, $data, feof($fp))) {
                    $errors[] = new \model\ErrorInfo("XML error : ".xml_error_string(xml_get_error_code($xmlParser))." at line ".xml_get_current_line_number($xmlParser));
                }
            }
            xml_parser_free($xmlParser);
        }

        if (empty($errors)) {
            // Check if <path> == $ac3dName
            $xmlcontent = simplexml_load_file($xmlPath);
            if ($this->ac3dName != $xmlcontent->path) {
                $errors[] = new \model\ErrorInfo("The value of the &lt;path&gt; tag in your XML file doesn't match the AC file you provided!");
            }

            // Check if the file begin with <?xml> tag
            $xmltag = str_replace(array("<", ">"), array("&lt;", "&gt;"), file_get_contents($xmlPath));
            if (!preg_match('#^&lt;\?xml +version="1\.0" +encoding="UTF-8" *\?&gt;#i', $xmltag)) {
                $errors[] = new \model\ErrorInfo("Your XML must start with &lt;?xml version=\"1.0\" encoding=\"UTF-8\" ?&gt; !");
            }
        }
        
        return $errors;
    }
    
    /**
     * (Used by checkXML method)
     * @param type $parser
     * @param string $name
     * @param type $attrs
     */
    private function startElement($parser, $name, $attrs) {
        $parserInt = intval($parser);
        if (!isset($this->depth[$parserInt])) {
            $this->depth[$parserInt] = 0;
        }
        $this->depth[$parserInt]++;
    }

    /**
     * (Used by checkXML method)
     * @param type $parser
     * @param string $name
     */
    private function endElement($parser, $name) {
        $parserInt = intval($parser);
        if (!isset($this->depth[$parserInt])) {
            $this->depth[$parserInt] = 0;
        }
        $this->depth[$parserInt]--;
    }
    
    private function checkAC3D($ac3dPath, $texturesNames) {
        $errors = array();
        $handle = fopen($ac3dPath, 'r');

        if (!$handle) {
            $errors[] = new \model\ErrorInfo("The AC file does not exist on the server. Please try to upload it again!");
            return $errors;
        }
        
        $i = 1;
        while (!feof($handle)) {
            $line = fgets($handle);
            $line = rtrim($line, "\r\n") . PHP_EOL;

            // Check if the file begins with the string "AC3D"
            if ($i == 1 && substr($line,0,4) != "AC3D") {
                $errors[] = new \model\ErrorInfo("The AC file does not seem to be a valid AC3D file. The first line must show \"AC3Dx\" with x = version");
            }

            // Check if the texture reference matches $texturesNames
            if (preg_match('#^texture#', $line)) {
                $data = preg_replace('#texture "(.+)"$#', '$1', $line);
                $data = substr($data, 0, -1);
                if (!in_array($data, $texturesNames)) {
                    $errors[] = new \model\ErrorInfo("The texture reference (".$data.") in your AC file at line ".$i." seems to have a different name than the PNG texture(s) file(s) name(s) you provided!");
                }
            }
            $i++;
        }
        fclose($handle);
        
        return $errors;
    }
    
    private function checkPNG($pngName, $pngPath) {
        $errors = array();
        
        if (file_exists($pngPath)) {
            $tmp    = getimagesize($pngPath);
            $width  = $tmp[0];
            $height = $tmp[1];
            $mime   = $tmp["mime"];

            // Check if PNG file is a valid PNG file (compare the type file)
            if ($mime != "image/png") {
                $errors[] = new \model\ErrorInfo("Your texture file does not seem to be a PNG file. Please upload a valid PNG file.");
            }

            // Check if PNG dimensions are a multiple of ^2
            if (!in_array($height, self::$validDimension) || !in_array($width, self::$validDimension)) {
                $errors[] = new \model\ErrorInfo("The size in pixels of your texture file (".$pngName.") appears not to be a power of 2.");
            }
        }
        else {
            $errors[] = new \model\ErrorInfo("The texture file does not exist on the server. Please try to upload it again.");
        }
        
        return $errors;
    }
    
    public function setXMLName($xmlName) {
        $this->xmlName = $xmlName;
    }
}
