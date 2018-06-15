<?php

/*
 * Copyright (C) 2016 FlightGear Team
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
 * News controller
 *
 * @author Julien Nguyen
 */
class NewsController extends ControllerMenu {
    private $newsPostDaoRO;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->newsPostDaoRO = \dao\DAOFactory::getInstance()->getNewsPostDaoRO();
    }
    
    public function displayAction() {
        
        $offset = $this->getVar('offset');
        if ($offset == null || !preg_match(\FormChecker::$regex['pageoffset'], $offset)){
            $offset = 0;
        }
        
        $pagesize = 10;
        $newsPosts = $this->newsPostDaoRO->getNewsPosts($offset, $pagesize);
        include 'view/news.php'; 
    }
}
