<?php
namespace submission;

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
 * Validator for thumbnail checking
 *
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 */
class ThumbValidator implements Validator {
    private $thumbPath;
    
    function __construct($thumbPath) {
        $this->thumbPath = $thumbPath;
    }
    
    public function validate() {
        $errors = array();
        
        if (file_exists($this->thumbPath)) {
            $tmp    = getimagesize($this->thumbPath);
            $width  = $tmp[0];
            $height = $tmp[1];
            $mime   = $tmp["mime"];

            // Check if JPEG file is a valid JPEG file (compare the type file)
            if ($mime != "image/jpeg") {
                $errors[] = new \model\ErrorInfo("Your thumbnail file does not seem to be a JPEG file. Please upload a valid JPEG file.");
            }

            // Check if thumbnail dimension is 320x240
            if ($height != 240 || $width != 320) {
                $errors[] = new \model\ErrorInfo("The dimension in pixels of your thumbnail file (".$width."x".$height.") does not seem to be 320x240.");
            }
        }
        else {
            $errors[] = new \model\ErrorInfo("The thumbnail file does not exist on the server. Please try to upload it again.");
        }
        
        return $errors;
    }
}
