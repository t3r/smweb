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

/**
 * Config constants
 *
 * @author Julien Nguyen
 */
class Config {
    private static $CAPTCHA_PUBLIC_KEY = "6Len6skSAAAAAB1mCVkP3H8sfqqDiWbgjxOmYm_4";
    private static $TERRASYNC_DATA_UPD_SERVER_URL = "http://scenery.flightgear.org/websvn/log.php?repname=repos+1&path=%2F&&isdir=1";
    private static $CAPTCHA_ENABLED = false;
    
    public static function getCaptchaPublicKey() {
        return self::$CAPTCHA_PUBLIC_KEY;
    }
    
    public static function getTerrasyncDataUpdServerURL() {
        return self::$TERRASYNC_DATA_UPD_SERVER_URL;
    }
    
    public static function isCaptchaEnabled() {
        return self::$CAPTCHA_ENABLED;
    }
}
