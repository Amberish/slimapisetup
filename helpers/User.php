<?php

require 'helpers/DB.php';

class User extends DB{

  public $user;
  private $table;

  private $passHash = 'DYhG93b0qyJcfIxfs2guVoUubWwvniR2G0FgaC9mi';

  function __construct(){
    $this->table = "users";

    parent::__construct();
  }

  /**
   * Method to authenticate, validate and login user
   * @param  Array $input Contain user credention to check for.
   * @return json        response objects
   */
  public function login($input){
    $email = $input['email'];
    $password = $input['password'];

    $statement = $this->db->select()
                  ->from($this->table)
                  ->where('email', '=', $email)
                  ->where('password', '=', $this->hash($password));

    $stmt = $statement->execute();
    $this->user = $stmt->fetch();
    //If no user found
    if(count($this->user) == 0){
      return [
        "success" => false,
        "message" => "User doesn't exist!"
      ];
    }

    //If user not validated
    if(!$this->validate()){
      return [
        "success" => false,
        "message" => "User is not validated!",
      ];
    }

    return [
      "success" => true,
      "message" => "User found!",
      "data"    => $this->user
    ];
  }

  //Generate hash as per cake php hashing.
  private function hash($password){
    return sha1( $this->passHash . $password);
  }

  private function validate(){
    if($this->isStudent()){
      return true;
    }
    return false;
  }

  private function isStudent(){
    //Role for student is 3
    if($this->user["role"] == 3){
      return true;
    }
    return false;
  }

}

 ?>
