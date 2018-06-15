<?php

namespace model;

/**
 * Request
 * @author Julien Nguyen <julien.nguyen3@gmail.com>
 */
abstract class Request {
    protected $id;
    protected $sig;
    protected $contributorEmail;
    protected $comment;
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setSig($sig) {
        $this->sig = $sig;
    }
    
    public function getSig() {
        return $this->sig;
    }
    
    public function setContributorEmail($contributorEmail) {
        $this->contributorEmail = $contributorEmail;
    }
    
    public function getContributorEmail() {
        return $this->contributorEmail;
    }
    
    public function setComment($comment) {
        $this->comment = $comment;
    }
    
    public function getComment() {
        return $this->comment;
    }
}

?>