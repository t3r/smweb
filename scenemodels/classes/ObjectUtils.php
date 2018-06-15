<?php

/*
 * Copyright (C) 2015 FlightGear team
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
 * ObjectUtils
 *
 * @author Julien Nguyen
 */
class ObjectUtils {

    /**
     * Computes the STG heading into a true heading before submission to the database.
     * @param float $stgHeading STG heading to convert
     * @return float true heading
     */
    public static function headingSTG2True($stgHeading) {
        if ($stgHeading > 180) {
            $trueHeading = 540 - $stgHeading;
        }
        else {
            $trueHeading = 180 - $stgHeading;
        }
        return $trueHeading;
    }
    
    /**
     * Computes the true heading into a STG heading (for edition purposes).
     * @param float $trueHeading true heading to convert
     * @return float STG heading
     */
    public static function headingTrue2STG($trueHeading) {
        if ($trueHeading > 180) {
            $stgHeading = 540 - $trueHeading;
        }
        else {
            $stgHeading = 180 - $trueHeading;
        }
        return $stgHeading;
    }
}
