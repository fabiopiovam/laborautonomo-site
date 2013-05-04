<?php
// web/index.php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

// definitions
$app['debug'] = true;
$app->get('/',function(){return 'Hello World!';});

$app->run();
