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
 * Controller for index page.
 *
 * @author Julien Nguyen
 */
class IndexController extends ControllerMenu {
    public function indexAction() {
        parent::menu();
        $modelsGroups = $this->getModelsGroups();
        $objectDaoRO = \dao\DAOFactory::getInstance()->getObjectDaoRO();
        $objects = $objectDaoRO->getObjects(5, 0);
        $models = $this->getModelDaoRO()->getModelMetadatas(0, 5);
        
        include 'view/welcome.php';
    }
    
}