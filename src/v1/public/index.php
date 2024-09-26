<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../../../vendor/autoload.php';
require_once 'include/DbHandler.php';
$app = AppFactory::create();

// Set the base path if the app is running in a subdirectory
$app->setBasePath(basePath: '/tslim/src/v1/public');

// Add routes
$app->get('/ll', function ($request, $response, $args) {
    $db = new DbHandler();
    $result = $db->getVersionInformation();
    $response->getBody()->write(json_encode($result));

    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
});

$app->get('/lld', function ($request, $response, $args) {

     $feedback = array();
    
    
    $db = new DbHandler();
    $result = $db->getVersionInformation();
   
    if ($result != NULL) {
        $feedback['error'] = false;
        $feedback['msg'] = "Version Found.";
        $feedback['data'] = $result;

        $response->getBody()->write(json_encode($feedback));

    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
    } else {
        $feedback['error'] = true;
        $feedback['msg'] = "No Version Found!";
        $feedback['version_no'] = null;
        $response->getBody()->write(json_encode($feedback));

        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    }

   
});

// Run the app
$app->run();
