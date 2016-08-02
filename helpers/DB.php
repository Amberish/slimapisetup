<?php

//use \Slim\PDO\Database as Database;
require 'Interface/DBInterface.php';

class DB implements DBInterface{

  protected $db;

  function __construct(){
    //Generate PDO object
    $host = "localhost";
    $username = "root";
    $password = "inveera";
    $database_name = "multilingual";
    $this->db = $this->connect($host, $username, $password, $database_name);
  }

  public function connect($host, $username, $password, $database_name){
    return new \Slim\PDO\Database("mysql:host=$host;dbname=$database_name;charset=utf8", $username, $password);
  }

}

?>
