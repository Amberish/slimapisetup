<?php

class Course extends DB {

  private $table;

  function __construct(){
    $this->table = "courses";

    parent::__construct();
  }

  /**
   * Fetches User courses based on user_id and employer_id(related to user)
   *
   * @param  integer $user_id     [description]
   * @param  integer $employer_id [description]
   * @return [type]              [description]
   */
  function getUserCourses($user_id, $employer_id) {
    $user_courses = [];
    $statement = $this->db->select()
                  ->from($this->table)
                  ->where('user_id', '=', $employer_id);

    $stmt = $statement->execute();
    $courses = $stmt->fetchAll();
    //Get count of material and quizes
    foreach($courses as $course){
      //Materials
      $material = $this->db->select()
                    ->from('coursematerialassociations')
                    ->where('course_id', '=', $course['id'])
                    ->where('emp_id', '=', $user_id);
      $materials = $material->execute()->fetchAll();

      //Quizes
      $quiz = $this->db->select()
                    ->from('coursequizassociations')
                    ->where('course_id', '=', $course['id'])
                    ->where('emp_id', '=', $user_id);
      $quizes = $quiz->execute()->fetchAll();

      $total_count = count($materials) + count($quizes);

      $material_attempted = array_filter($materials, function($material){
        return $material['attempt_status'] == '1';
      });

      $quiz_attempted = array_filter($quizes, function($quiz){
        return $quiz['attempt_status'] == '1';
      });

      //Attempt_count
      $material_attempt_count = count($material_attempted);
      $quiz_attempt_count = count($quiz_attempted);
      $attempted = $material_attempt_count + $quiz_attempt_count;

      if($total_count != 0){
        //Percentage
        $percentage = ($attempted / $total_count) * 100;
        $course['percentage'] = $percentage;
        if(strtotime($course['end_date']) < time()){
          $course['status'] = 'Expired';
        } else {
          $course['status'] = 'Active';
        }
        $user_courses[] = $course;
      }
    }
    return $user_courses;
  }
}

?>
