<?php
use Symfony\Component\HttpFoundation\Response;


//Bootstrapping
require_once __DIR__.'/../vendor/autoload.php';
$app = new Silex\Application();


//SETTINGS
$app['debug']               = (preg_match("/192\.168\.[0-9]{1,3}\.[0-9]{1,3}/", $_SERVER['SERVER_ADDR']) 
                                || $_SERVER['SERVER_ADDR'] == "127.0.0.1");
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', $app['debug']);
error_reporting(E_ALL ^ E_NOTICE);
$app['twig.content_path']   = __DIR__.'/../views';
$app['translator.locales']  = array('pt_BR','en','es');
$app['translator.path']     = __DIR__ . '/../locales';
$app['cache.path']          = __DIR__ . '/../cache';
$app['cache.max_age']       = $app['cache.expires'] = 3600 * 24 * 90;
$app['smtp.options']        = (file_exists(__DIR__.'/../etc/swiftmailer.options.php')) ? require_once __DIR__.'/../etc/swiftmailer.options.php' : array(
    'host' => 'mail.your-domain.com',
    'port' => '587',
    'username' => 'user@your-domain.com',
    'password' => '******'
    //'encryption' => 'ssl',
    //'auth_mode' => 'login'
);


//I18N
$app->register(new Silex\Provider\TranslationServiceProvider(), array_flip(array_fill_keys($app['translator.locales'], 'locale_fallback')));
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    foreach ($app['translator.locales'] as $value) {
        $translator->addResource('xliff', "{$app['translator.path']}/{$value}.xlf", $value);
    }
    
    return $translator;
}));
$app['translator']->setLocale('pt_BR');


//FORM
use Silex\Provider\FormServiceProvider;
$app->register(new FormServiceProvider());


//TWIG
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'     => $app['twig.content_path'],
    'twig.options'  => array(
        'cache' => (!$app['debug']) ? $app['cache.path'] : false, 
        'debug' => $app['debug']),
));
$app['twig']->addFilter('nl2br', new Twig_Filter_Function('nl2br', array('is_safe' => array('html'))));


//SWIFTMAILER
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app['swiftmailer.options'] = $app['smtp.options'];


// Registers Monolog extension
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile'       => __DIR__ . '/../log/app.log',
    'monolog.name'          => 'app',
    'monolog.level'         => 300 //WARNING
));


//UrlGenerator
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());


//Registers Symfony Session component extension
$app->register(new Silex\Provider\SessionServiceProvider());
$app['session']->start();


//REGISTER ERROR HANDLERS
if(!$app['debug']){
    $app->error(function (\Exception $e, $code) use ($app) {
        switch ($code) {
            case 404:
                $app['monolog']->addError(sprintf('%s Error on %s', $code, $app['request']->server->get('REQUEST_URI')));
                $message = $app['translator']->trans('A página solicitada não pôde ser encontrado');
                break;
            default:
                $app['monolog']->addCritical(sprintf('%s Error on %s', $code, $app['request']->server->get('REQUEST_URI')));
                $message = $app['translator']->trans('Ops! Ocorreu um terrível erro :(');
        }

        return $app['twig']->render('error.twig', array('code' => $code, 'message' => $message,));
    });
}


return $app;