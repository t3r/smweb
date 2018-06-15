<?php
namespace dao;

/**
 * Interface for Author Data Access Object
 *
 * @author     Julien Nguyen <julien.nguyen3@gmail.com>
 * @copyright  2014 - FlightGear Team
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */
interface IAuthorDAO {

    /**
     * Adds author to the database.
     * 
     * @param \model\Author $author new author to add.
     * @return newly added author, with ID.
     */
    public function addAuthor(\model\Author $author);

    /**
     * Updates author.
     * 
     * @param \model\Author $author author to update
     */
    public function updateAuthor(\model\Author $author);
    
    /**
     * Gets author.
     * 
     * @param type $authorId id of the author to get.
     * @return author to get.
     */
    public function getAuthor($authorId);
    
    public function getAuthorByEmail($email);
    
    public function getAllAuthors($offset, $pagesize);
}

?>
