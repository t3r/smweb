<?php

/*
 * Copyright (C) 2014-2015 FlightGear Team
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
 * Utility class containing checkers for forms
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
class FormChecker {
    static public $regex = array(
        'comment' => "/^[^|]+$/u",
        'stg' => '/^[a-zA-Z0-9\_\.\-\,\/]+$/u',
        'model_filepath' => '/^[a-z0-9_\/.-]+$/i',
        'modelid' => '/^[0-9]+$/u',
        'modelgroupid' => '/^[0-9]+$/',
        'objgroupid' => '/^[1-9][0-9]*$/',
        'modelname' => '/^.+$/',
        'filename' => '/^[a-zA-Z0-9_.-]*$/u',
        'png_filename' => '/^[a-zA-Z0-9_.-]+\.(png|PNG)$/u',
        'ac3d_filename' => '/^[a-zA-Z0-9_.-]+\.(ac|AC)$/u',
        'xml_filename' => '/^[a-zA-Z0-9_.-]+\.(xml|XML)$/u',
        'thumb_filename' => '/^[a-zA-Z0-9_.-]+\.(jpg|JPG|jpeg|JPEG)$/u',
        'authorid' => '#^[0-9]{1,3}$#',
        'email' => '/^[0-9a-zA-Z_\-.]+@[0-9a-z_\-]+\.[0-9a-zA-Z_\-.]+$/u',
        'objectid' => '/^[0-9]+$/u',
        'countryid' => '#^[a-zA-Z]{1,3}$#',
        'long_lat' => '/^[-+]?([0-9]*\.[0-9]+|[0-9]+)$/u',
        'gndelevation' => '/^[-+]?([0-9]*\.[0-9]+|[0-9]+)$/u',
        'offset' => '/^[-+]?([0-9]*\.[0-9]+|[0-9]+)$/u',
        'heading' => '/^[-+]?([0-9]*\.[0-9]+|[0-9]+)$/u',
        'obtext' => '/^.+$/u',
        'sig' => '/[0-9a-z]/',
        'pageoffset' => '/^[0-9]+$/u'
       );

    /**
     * Checks if the id is a valid model group id
     * @param string $idToCheck id to check
     * @return bool true if the given id is a model group id, false otherwise
     */
    static public function isModelGroupId($idToCheck) {
        return preg_match(static::$regex['modelgroupid'], $idToCheck);
    }
    
    static public function isObjectGroupId($idToCheck) {
        return preg_match(static::$regex['objgroupid'], $idToCheck);
    }

    /**
     * Checks if the id is a valid model id
     * @param string $idToCheck id to check
     * @return bool true if the given id is a model id, false otherwise
     */
    static public function isModelId($idToCheck) {
        return preg_match(static::$regex['modelid'], $idToCheck)
               && $idToCheck > 0;
    }

    /**
     * Checks if the name is a valid model name (checks if it has only allowed
     * characters)
     * @param string $name name to check
     * @return bool true if the name is a model name, false otherwise
     */
    static public function isModelName($name) {
        return preg_match(static::$regex['modelname'], $name);
    }

    /**
     * Checks if the id is an valid object id value
     * @param string $idToCheck
     * @return bool true if the id can be an object
     */
    static public function isObjectId($idToCheck) {
        return $idToCheck > 0
               && preg_match(static::$regex['objectid'], $idToCheck);
    }

    /**
     * Checks if the id is a valid author id
     * @param string $idToCheck id to check
     * @return bool true if the id can be an author id, false otherwise
     */
    static public function isAuthorId($idToCheck) {
        return $idToCheck > 0
               && preg_match(static::$regex['authorid'], $idToCheck);
    }

    /**
     * Checks if the given variable is a valid latitude
     * @param string $value value to check
     * @return bool true if the value is a valid latitude, false otherwise
     */
    static public function isLatitude($value) {
        return strlen($value) <= 20
               && $value <= 90
               && $value >= -90
               && preg_match(static::$regex['long_lat'], $value);
    }

    /**
     * Checks if the given variable is a valid longitude
     * @param string $value value to check
     * @return bool true if the value is a valid longitude, false otherwise
     */
    static public function isLongitude($value) {
        return strlen($value) <= 20
               && $value <= 180
               && $value >= -180
               && preg_match(static::$regex['long_lat'], $value);
    }

    /**
     * Checks if the given parameter is a valid country id
     * @param string $value value to check
     * @return bool true if the given parameter is a valid country id, false otherwise
     */
    static public function isCountryId($value) {
        return $value != ""
               && preg_match(static::$regex['countryid'], $value);
    }

    /**
     * Checks if the given variable is a valid ground elevation
     * @param string $value value to check
     * @return bool true if the value is a valid ground elevation
     */
    static public function isGndElevation($value) {
        return strlen($value) <= 20
               && preg_match(static::$regex['gndelevation'], $value);
    }

    /**
     * Checks if the given variable is a valid offset elevation
     * @param string $value value to check
     * @return bool  if the value is a valid offset elevation
     */
    static public function isOffset($value) {
        return strlen($value) <= 20
               && preg_match(static::$regex['offset'], $value)
               && $value < 1000
               && $value > -1000;
    }

    // Checks if the given variable is a valid heading
    // ================================================
    static public function isHeading($value) {
        return strlen($value) <= 20
               && preg_match(static::$regex['heading'], $value)
               && $value < 360
               && $value >= 0;
    }

    // Checks if the given variable is a valid comment
    // ================================================
    static public function isComment($value) {
        return strlen($value) <= 100
               && preg_match(static::$regex['comment'], $value);
    }

    // Checks if the given variable is a valid email address
    // ================================================
    static public function isEmail($value) {
        return strlen($value) <= 50
               && preg_match(static::$regex['email'], $value);
    }

    /**
     * Checks if the given variable is a valid sig id
     * @param string $value
     * @return bool true if value is a sig, false otherwise
     */
    static public function isSig($value) {
        return strlen($value) == 64
               && preg_match(static::$regex['sig'], $value);
    }
   
    // Checks if the given variable is a AC3D filename
    // ================================================
    static public function isAC3DFilename($filename) {
        return preg_match(static::$regex['ac3d_filename'], $filename);
    }
   
    // Checks if the given variable is a PNG filename
    // ================================================
    static public function isPNGFilename($filename) {
        return preg_match(static::$regex['png_filename'], $filename);
    }
   
    // Checks if the given variable is a XML filename
    // ================================================
    static public function isXMLFilename($filename) {
        return preg_match(static::$regex['xml_filename'], $filename);
    }
   
    // Checks if the given variable is a filename
    // ================================================
    static public function isFilename($filename) {
        return preg_match(static::$regex['filename'], $filename);
    }
   
    /**
     * Checks if the given variable is a valid object text
     * @param string $value value to check
     * @return bool true if the value is a valid object text, false otherwise
     */
    static public function isObtext($value) {
        return strlen($value) > 0
                && strlen($value) <= 100
                && preg_match(static::$regex['obtext'], $value);
    }

    /**
     * Checks if the given value is a filepath
     * @param string $value value to check
     * @return bool true if the value is a filepath, false otherwise
     */
    static public function isFilePath($value) {
        return preg_match(static::$regex['model_filepath'], $value);
    }
    
    static public function isStgLines($value) {
        return preg_match(static::$regex['stg'], $value);
    }
}
