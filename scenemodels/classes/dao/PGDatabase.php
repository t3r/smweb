<?php

namespace dao;

class PGDatabase implements Database {
    private $connection;

    public function __construct($dbname, $dbhost, $dbport, $dbuser, $dbpass) {

        $this->connection = pg_connect('dbname='.$dbname.' host='.$dbhost.' port='.$dbport.
                         ' user='.$dbuser.' password='.$dbpass.' sslmode=require');
    }
    
    public function query($query) {
        return pg_query($this->connection, $query);
    }
}

?>
