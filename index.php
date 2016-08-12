<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

//Autoloading "helper" classes
spl_autoload_register(function ($classname) {
    require ("helpers/" . $classname . ".php");
});

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

/**
 * API Routes Start Here
 */

/**
 * API for user login
 *
 * @var [type]
 */
$app->post('/login', function (Request $request, Response $response) use ($app) {
    $params = $request->getParsedBody();
    $email = $params['email'];
    $password = $params['password'];

    $obj = new User;
    $user = $obj->login(array('email' => $email, 'password' => $password));

    if($user["success"]){

      $response->withJson([
        "success" => "true",
        "message" => "User found!!",
        "data"    => $user["data"]
      ], 200);

    } else{
        $response->withJson([
          "success" => "false",
          "message" => $user["message"]
        ])->withStatus(404);
    }

    return $response;
});

/**
 * API for User Courses
 *
 * @var [type]
 */
$app->post('/user-courses', function (Request $request, Response $response) use ($app) {
  $params = $request->getParsedBody();
  $user_id = $params['user_id'];
  $employer_id = $params['employer_id'];

  $obj = new User;

  $courses = $obj->courses(array('user_id' => $user_id, 'employer_id' => $employer_id));

  if($courses['success']){
    $response->withJson([
      "success" => "true",
      "message" => $courses['message'],
      "data" => $courses['data']
    ])->withStatus(200);
  } else{
    $response->withJson([
      "success" => "false",
      "message" => $courses['message'],
      "data" => $courses['data']
    ])->withStatus(404);
  }

  return $response;

});

/**
 * API for listing invitations
 *
 */
$app->post('/user-invitations', function (Request $request, Response $response) use ($app) {
  $params = $request->getParsedBody();
  $user_id = $params['user_id'];
  $employer_id = $params['employer_id'];
  $type = $params['type'];

  $obj = new User;

  $invitations = $obj->invitations(array('user_id' => $user_id, 'employer_id' => $employer_id), $type);

  if($invitations['success']){
    $response->withJson([
      "success" => "true",
      "message" => $invitations['message'],
      "data" => $invitations['data']
    ])->withStatus(200);
  } else{
    $response->withJson([
      "success" => "false",
      "message" => $invitations['message'],
      "data" => $invitations['data']
    ])->withStatus(404);
  }

  return $response;
});

$app->post('/invitation/action', function (Request $request, Response $response) use ($app) {
  $params = $request->getParsedBody();
  $invitation_id = $params['invitation_id'];
  $action = $params['action'];

  $obj = new User;
  $out = $obj->invitationAction(array('invitation_id' => $invitation_id, 'action' => $action));

  if($out['success']){
    $response->withJson([
      "success" => "true",
      "message" => $out['message']
    ])->withStatus(200);
  } else{
    $response->withJson([
      "success" => "false",
      "message" => $out['message']
    ])->withStatus(404);
  }

});

$app->run();

?>
