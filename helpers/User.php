<?php

class User extends DB{

  public $user;
  private $table;

  private $passHash = 'DYhG93b0qyJcfIxfs2guVoUubWwvniR2G0FgaC9mi';

  function __construct(){
    $this->table = "users";

    parent::__construct();
  }

  /**
   * Fetch a user
   * @param  integer  $user_id [description]
   * @param  integer $type    [description]
   * @return array           [description]
   */
  public function get($user_id, $type = 3){
    return $this->db->select()
             ->from($this->table)
             ->where('id', '=', $user_id)
             ->execute()
             ->fetch();
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

  public function courses($inputs){

    $course = new Course;
    $user_courses = $course->getUserCourses($inputs['user_id'], $inputs['employer_id']);
    //Percentage Calculation
    $material_attempt = '';
    //$material_attempt = $this->Coursematerialassociation->find('count',array('conditions'=>array('Coursematerialassociation.course_id'=>$data[0]['Course']['id'],'Coursematerialassociation.attempt_status'=>1,'Coursematerialassociation.emp_id'=>$emp_id),'recursive'=>2));

    //print_r($coursedata);die;
    //$quizatempt = $this->Coursequizassociation->find('count',array('conditions'=>array('Coursequizassociation.course_id'=>$data[0]['Course']['id'],'Coursequizassociation.attempt_status'=>1,'Coursequizassociation.emp_id'=>$emp_id),'recursive'=>2));


    if(!$user_courses){
      return [
        "success" => false,
        "message" => "No courses found"
      ];
    }

    return [
      "success" => true,
      "message" => "Courses found!",
      "data"    => $user_courses
    ];

  }

  /**
   * This method fetches invitations for the user.
   *
   * @param  array  $inputs [description]
   * @param  integer $type   Type of invitation: outstanding(0), accepted(1/3), rejected(2)
   * @return array          [description]
   */
  public function invitations($inputs, $type = 0){
    $user_id = $inputs['user_id'];
    $employer_id = $inputs['employer_id'];
    $obj = new Invitation;
    $invitations = $obj->getUserInvitations( $user_id, $employer_id, $type);

    if(!$invitations){
      return [
        "success" => false,
        "message" => "No invitations found"
      ];
    } else {
      return [
        "success" => true,
        "message" => "Invitations found",
        "data"    => $invitations
      ];
    }
  }

  public function invitationAction($inputs){
    $invitation_id = $inputs['invitation_id'];
    $action = $inputs['action'];
    $invitation = new Invitation;

    $response = $invitation->action($invitation_id, $action);

    return [
      "success" => $response['success'],
      "message" => $response['message']
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
