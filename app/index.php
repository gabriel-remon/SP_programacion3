<?php

use Slim\Handlers\Strategies\RequestHandler;
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__.'/utils/jwtController.php';
require_once __DIR__.'/utils/AccesoDatos.php';
require_once __DIR__."/routers/index.router.php";

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

//convierte todos los datos de string en el body a minuscula


// Routes
$app->group('', \indexRouter::class );

$app->run();
