<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app['debug'] = (preg_match("/192\.168\.[0-9]{1,3}\.[0-9]{1,3}/", $_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] == "127.0.0.1");

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

//I18N
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => 'pt_BR',
    'locale_fallback' => 'en',
    'locale_fallback' => 'es',
));
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addResource('xliff', __DIR__.'/../locales/pt_BR.xlf', 'pt_BR');
    $translator->addResource('xliff', __DIR__.'/../locales/en.xlf', 'en');
    $translator->addResource('xliff', __DIR__.'/../locales/es.xlf', 'es');

    return $translator;
}));

var_dump($app['translator']->getLocale());
$app['translator']->setLocale('pt_BR');

//TWIG
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
    'twig.options' => array('cache' => __DIR__.'/../cache', 'debug' => $app['debug']),
));
$app['twig']->addFilter('nl2br', new Twig_Filter_Function('nl2br', array('is_safe' => array('html'))));

//REGISTER ERROR HANDLERS
if(!$app['debug']){
    $app->error(function (\Exception $e, $code) use ($app) {
        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';
        }

        return $app['twig']->render('error.twig', array('code' => $code, 'message' => $message,));
    });
}
return $app;