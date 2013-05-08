<?php 
$app = require __DIR__.'/bootstrap.php';

//APP DEFINITION
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig',array('msg' => $app['translator']->trans('Ola Mundo')));
})
->bind('homepage');

return $app;