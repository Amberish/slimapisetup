<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'helpers/User.php';

$app = new \Slim\App;

/**
 * API Routes Start Here
 */
$app->post('/login', function (Request $request, Response $response) use ($app) {
    $params = $request->getParsedBody();
    $email = $params['email'];
    $password = $params['password'];

    $obj = new User;
    $user = $obj->login(array('email' => $email, 'password' => $password));

    if($user["success"]){

      $response->withJson($user["data"], 200);

    } else{
        $response->withJson([
          "success" => "false",
          "message" => $user["message"]
        ])->withStatus(404);
    }

    return $response;
});

$app->run();

?>
