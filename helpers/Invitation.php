<?php

class Invitation extends DB{

  function __construct(){
    $this->table = "invitations";

    parent::__construct();
  }

  /**
   * Method fetched user invitation based on user_id, employer_id and type
   *
   * @param  integer  $user_id     [description]
   * @param  integer  $employer_id [description]
   * @param  integer  $type        [description]
   * @return array               [description]
   */
  public function getUserInvitations($user_id, $employer_id, $type = 0){
    $invitations = [];

    if($type == 1){
      $types = array(1,3);
    }

    $statement = $this->db->select(
                    [
                      $this->table . '.id as invitation_id',
                      'CONCAT(users.firstname, " ", users.lastname) as invited_from',
                      'courses.course_name as invited_to_course',
                      "DATE_FORMAT(FROM_UNIXTIME($this->table.created), '%e %b %Y') as invited_on"
                    ]
                  )
                  ->join('users', 'users.id','=', $this->table . '.employer_id')
                  ->join('courses', 'courses.id','=', $this->table . '.course_id')
                  ->from($this->table)
                  ->where($this->table . '.user_id', '=', $user_id)
                  ->where($this->table . '.employer_id', '=', $employer_id);
    if($types){
      $statement->whereIn('acceptation', $types);
    } else {
      $statement->where('acceptation', '=', $type);
    }

    $stmt = $statement->execute();
    $invitations = $stmt->fetchAll();

    return $invitations;
  }

  /**
   * Invitaion action (accept/reject)
   *
   * @param  integer $invitation_id [description]
   * @param  string $action        [description]
   * @return bool                [description]
   */
  public function action($invitation_id, $action){
    $statement = $this->db->select()
                  ->from($this->table)
                  ->where('id', '=', $invitation_id);

    $stmt = $statement->execute();
    $invitation = $stmt->fetch();

    if($action == 'accept'){

      return $this->accept($invitation);

    } else if($action == 'reject'){

      return $this->reject($invitation);

    }

    return [
      "message" => "Action not valid!!",
      "success" => false
    ];
  }

  /**
   * Private method to perform invitation accept action
   *
   * @param  array $invitation [description]
   * @return bool             [description]
   */
  private function accept($invitation) {
    $invitation["acceptation"] = 1;
    //print_r($invitation);die;
    $statement = $this->db->update($invitation)
                          ->table($this->table)
                          ->where('id', '=', $invitation["id"]);
    $affectedRows = $statement->execute();

    if(!$affectedRows){
      $status = false;
      $message = "Invitation not found!";

      return [
        "success" => $status,
        "message" => $message,
        "data" => null
      ];
    }

    //Make an entry in association table
    $columns = array('emp_id', 'group_id', 'quiz_id', 'course_id', 'user_id');
    $values = array($invitation['user_id'], null, null, $invitation['course_id'], $invitation['employer_id']);
    $insertAssociation = $this->db->insert($columns)
                                  ->into('associations')
                                  ->values($values);
    $insertAssociation->execute();

    //Add Quiz Association entry
    $statement = $this->db->select()
                          ->from('coursequizassociations')
                          ->where('user_id', '=', $invitation['employer_id'])
                          ->where('course_id', '=', $invitation['course_id'])
                          ->whereNull('emp_id');

    $stmt = $statement->execute();

    $quizes = $stmt->fetchAll();

    //Inserting new entries in coursequizassociations table for the given user.
    foreach($quizes as $quiz){
      $quiz['id'] = "";
      $quiz['emp_id'] = $invitation['user_id'];
      $quiz['created_by'] = 2;
      //print_r($quizes);die;
      $keys = array();
      $values = array();
      foreach ($quiz as $key => $value) {
        //Add back-ticks to keys
        $keys[] = '`' . $key . '`';
        $values[] = ($value || $value == 0)?$value:"";
      }

      try{
        $insert = $this->db->insert($keys)
                           ->into('coursequizassociations')
                           ->values($values);
        $insert->execute(false);
      } catch(Exception $e) {
        $message = $e->getMessage();
        $status = false;
      }
    }

    //Add Course Material Association Entry
    $statement = $this->db->select()
                          ->from('coursematerialassociations')
                          ->where('user_id', '=', $invitation['employer_id'])
                          ->where('course_id', '=', $invitation['course_id'])
                          ->whereNull('emp_id');
    $stmt = $statement->execute();
    $materials = $stmt->fetchAll();

    //Inserting new entries in coursematerialassociations table for the given user.
    foreach($materials as $material){
      $material['id'] = "";
      $material['emp_id'] = $invitation['user_id'];
      $material['created_by'] = 2;

      $keys = array();
      $values = array();
      foreach ($material as $key => $value) {
        //Add back-ticks to keys
        $keys[] = '`' . $key . '`';
        $values[] = ($value || $value == 0)?$value:"";
      }

      try {
        $insert = $this->db->insert($keys)
                           ->into('coursematerialassociations')
                           ->values($values);
        $insert->execute(false);
      } catch(Exception $e){
        $message =  $e->getMessage();
        $status = false;
      }
    }

    //TODO: Send Mail
    //Get user details
    $user = (new User)->get($invitation['user_id']);

    $params = array();
    $params['firstname'] = $user['firstname'];
    $params['lastname'] = $user['lastname'];
    $params['email'] = $user['email'];

    if((new SendMail)->dispatch($user['email'], 'admin@mindmaplms.com', 4, $params)){
      $message = "Mail sent!!";
      $status = true;
    } else {
      $message = "Mail not Sent!!";
      $status = false;
    }

    return [
      "success" => $status,
      "message" => $message,
      "data"    => null
    ];
  }


  /**
   * Private method to perform invitation reject action
   *
   * @param  array $invitation [description]
   * @return bool             [description]
   */
  private function reject($invitation) {
    $invitation["acceptation"] = 2;

    $statement = $this->db->update($invitation)
                          ->table($this->table)
                          ->where('id', '=', $invitation["id"]);
    $affectedRows = $statement->execute();

    if(!$affectedRows){
      return false;
    }
    //TODO Send mail
    //Get user details
    $user = (new User)->get($invitation['user_id']);

    $params = array();
    $params['firstname'] = $user['firstname'];
    $params['lastname'] = $user['lastname'];
    $params['email'] = $user['email'];

    if((new SendMail)->dispatch($user['email'], 'admin@mindmaplms.com', 6, $params)){
      $message = "Mail sent!!";
      $status = true;
    } else {
      $message = "Mail not sent!!";
      $status = false;
    }

    return [
      "success" => $status,
      "message" => $message,
      "data"    => null
    ];
  }

}

 ?>
