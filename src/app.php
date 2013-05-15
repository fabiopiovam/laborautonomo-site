<?php 
$app = require __DIR__.'/bootstrap.php';

//APP DEFINITION
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig');
})
->bind('homepage');

$app->get($app['translator']->trans('sobre'), function () use ($app) {
    return $app['twig']->render('about.twig');
})
->bind('about');

$app->get($app['translator']->trans('projetos'), function () use ($app) {
    return $app['twig']->render('projects.twig');
})
->bind('projects');

$app->get($app['translator']->trans('contato'), function () use ($app) {
    return $app['twig']->render('contact.twig');
})
->bind('contact');

return $app;