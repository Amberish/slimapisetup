<?php

class SendMail extends DB {

  function __construct(){
    $this->table = "emailtemplates";

    parent::__construct();
  }

  public function dispatch( $to, $from, $message_id, $params = null){
    $statement = $this->db->select()
                          ->from($this->table)
                          ->where('id', '=', $message_id);
    $result = $statement->execute()->fetch();

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
    $headers .= 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion();

    //$headers = "From: " . $from;

    $subject = $result['template_name'];
    if(!$params)
      $message = $result['description'];
    else
      $message = $this->textReplace($result['description'], $params);


    if(mail($to, $subject, $message, $headers)){
      return true;
    }

    return false;
  }

  /**
   * Replace text with variable in email temlates.
   * @param  string $content [description]
   * @param  array $params  [description]
   * @return string          [description]
   */
  public function textReplace($content, $params){

    foreach($params as $key => $value){
      $content = str_replace("[$key]", $value, $content);
    }

    return $content;

  }

}

?>
