<?php

namespace Controllers;

use PDOException;

class DB 
{
    protected $dbHost;
    protected $dbName;
    protected $dbPass;
    protected $dbUser;

    var $connect;
    var $query;
    var $data;
    var $statement;
    var $filedata;

    var $error;

    public function __construct(){
        $this->dbHost = env('MYSQL_HOST');
        $this->dbName = env('MYSQL_DB');
        $this->dbPass = env('MYSQL_PASS');
        $this->dbUser = env('MYSQL_USER');

        $con = 'mysql:host='.$this->dbHost . ';dbname=' . $this->dbName;

        $options = array(
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );

        try{
            $this->connect = new \PDO($con, $this->dbUser, $this->dbPass, $options);
        }catch(\PDOException $e){
            $this->error = $e->getMessage();
            exit($this->error);
        }
    }

    public function execute(){            
        $this->statement = $this->connect->prepare($this->query);
        return $this->statement->execute($this->data);
    }

    public function fetchAll(){
        $this->execute();
        return $this->statement->fetchAll(\PDO::FETCH_OBJ);
    }

    public function fetch(){
        $this->execute();
        return $this->statement->fetch(\PDO::FETCH_OBJ);
    }

    public function rowCount(){
        $this->execute();
        return $this->statement->rowCount();
    }

    public function lastInsertId(){
        return $this->connect->lastInsertId();
    }

    public function beginTransaction(){
        return $this->connect->beginTransaction();
    }

    public function endTransaction(){
        return $this->connect->commit();
    }

    public function cancelTransaction(){
        return $this->connect->rollBack();
    }

    public function verifyInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}