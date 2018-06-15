<?php

/**
 * News Data Access Object implementation for PostgreSQL
 *
 * Database layer to access news from PostgreSQL database
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

namespace dao;

class NewsPostDAO extends PgSqlDAO implements INewsPostDAO {

    public function addNewsPost($newsPost) {
        // TODO
    }

    public function updateNewsPost($newsPost) {
        // TODO
    }
    
    public function getNewsPost($newsPostId) {
        $result = $this->database->query("SELECT *, date_trunc('seconds',ne_timestamp) AS formdate ".
                                         "FROM fgs_news, fgs_authors WHERE ne_id=".$newsPostId.";");
        $row = pg_fetch_assoc($result);
        
        return $this->getNewsPostFromRow($row);
    }
    
    public function getNewsPosts($offset, $pagesize) {
        $result = $this->database->query("SELECT *".
                                         "FROM fgs_news, fgs_authors ".
                                         "WHERE au_id = ne_author ".
                                         "ORDER BY ne_timestamp DESC ".
                                         "LIMIT ".$pagesize." OFFSET ".$offset);
        
        $resultArray = array();
                           
        while ($row = pg_fetch_assoc($result)) {
            $resultArray[] = $this->getNewsPostFromRow($row);
        }
        
        return $resultArray;
    }
    
    private function getNewsPostFromRow($row) {
        $author = new \model\Author();
        $author->setId($row["au_id"]);
        $author->setName($row["au_name"]);
        $author->setEmail($row["au_email"]);
        $author->setDescription($row["au_notes"]);
    
        $newsPost = new \model\NewsPost();
        $newsPost->setId($row["ne_id"]);
        $newsPost->setDate(new \DateTime($row["ne_timestamp"]));
        $newsPost->setAuthor($author);
        $newsPost->setText($row["ne_text"]);
        
        return $newsPost;
    }
}

?>