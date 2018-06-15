<?php
namespace dao;

class DAOFactory {
    private static $instance;

    private function __construct() {
    }
    
    private function getDBReadOnly() {
        return new PGDatabase(getenv("PGDATABASE"), getenv("PGHOST"), getenv("PGPORT"), getenv("PGUSER"), getenv("PGPASSWORD") );
    }
    
    private function getDBReadWrite() {
        return new PGDatabase(getenv("PGDATABASE"), getenv("PGHOST"), getenv("PGPORT"), getenv("PGUSER"), getenv("PGPASSWORD") );
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DAOFactory();
        }
        
        return self::$instance;
    }
    
    public function getModelDaoRO() {
        return new ModelDAO($this->getDBReadOnly());
    }
    
    public function getModelDaoRW() {
        return new ModelDAO($this->getDBReadWrite());
    }
    
    public function getObjectDaoRO() {
        return new ObjectDAO($this->getDBReadOnly());
    }
    
    public function getObjectDaoRW() {
        return new ObjectDAO($this->getDBReadWrite());
    }
    
    public function getAuthorDaoRO() {
        return new AuthorDAO($this->getDBReadOnly());
    }
    
    public function getAuthorDaoRW() {
        return new AuthorDAO($this->getDBReadWrite());
    }
    
    public function getNewsPostDaoRO() {
        return new NewsPostDAO($this->getDBReadOnly());
    }
    
    public function getNewsPostDaoRW() {
        return new NewsPostDAO($this->getDBReadWrite());
    }
    
    public function getRequestDaoRO() {
        return new RequestDAO($this->getDBReadOnly(), $this->getObjectDaoRO(),
                $this->getModelDaoRO(), $this->getAuthorDaoRO());
    }
    
    public function getRequestDaoRW() {
        return new RequestDAO($this->getDBReadWrite(), $this->getObjectDaoRW(),
                $this->getModelDaoRW(), $this->getAuthorDaoRW());
    }
}

?>
