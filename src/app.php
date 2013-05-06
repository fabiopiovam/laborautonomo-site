<?php 
$app = require __DIR__.'/bootstrap.php';

//APP DEFINITION
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig');
})
->bind('homepage');

$app->get('/contact', function () use ($app) {
    return $app['twig']->render('index.twig');
});

return $app;