<?php

/**
 * Interface for News Data Access Object
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

namespace dao;

interface INewsPostDAO {

    public function addNewsPost($newsPost);

    public function updateNewsPost($newsPost);
    
    public function getNewsPost($newsPostId);
    
    public function getNewsPosts($offset, $pagesize);
}

?>
