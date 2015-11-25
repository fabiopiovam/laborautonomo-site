<?php
use Symfony\Component\HttpFoundation\Response;


//Bootstrapping
require_once __DIR__.'/../vendor/autoload.php';
$app = new Silex\Application();


//SETTINGS
$app['debug']               = false;
$app['twig.content_path']   = __DIR__.'/../views';
$app['translator.locales']  = array('es','en');
$app['translator.path']     = __DIR__ . '/../locales';
$app['cache.path']          = __DIR__ . '/../cache';
$app['cache.max_age']       = $app['cache.expires'] = 3600 * 24 * 90;
$app['mail.to']             = array('fabio@laborautonomo.org' => 'Fabio - LaborAutonomo.org');
$app['smtp.options']        = array(
    'host' => 'mail.your-domain.com',
    'port' => '587',
    'username' => 'user@your-domain.com',
    'password' => '******'
    //'encryption' => 'ssl',
    //'auth_mode' => 'login'
);

//REPOSITORIES CONFIGURATION
$app['repos.config'] = array(
    'storage'   =>  __DIR__ . '/../storage/', //required permission 777
    'github'    => array(
        'user'      => 'laborautonomo',
        'token'		=> 'your_token', //more information: https://developer.github.com/v3/auth/#basic-authentication
        'repo'      => 'laborautonomo-site',
        'version'   => 'v3',
        'locale'   => array(
            'default' => 'en',
            'optional' => array('pt_BR','es')
        )
    )
);

$google_analytics = 'UA-99999999-9';

if (file_exists(__DIR__.'/../etc/settings_production.php')) 
	require_once __DIR__.'/../etc/settings_production.php';

if (file_exists(__DIR__.'/../etc/settings_local.php')) 
	require_once __DIR__.'/../etc/settings_local.php';

date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', $app['debug']);
error_reporting(E_ALL ^ E_NOTICE);

//Registers Symfony Session component extension
$app->register(new Silex\Provider\SessionServiceProvider());
$app['session']->start();


//I18N
$http_accept_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if ($app['session']->has('user-locale'))
    $app['locale'] = $app['session']->get('user-locale');
else
    $app['locale'] = in_array($http_accept_language, $app['translator.locales']) ? $http_accept_language : 'pt_BR';

$app->register(new Silex\Provider\TranslationServiceProvider(), array('locale_fallbacks' => array('pt_BR')));
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('xliff', new Symfony\Component\Translation\Loader\XliffFileLoader());
    
    foreach ($app['translator.locales'] as $value) {
        $translator->addResource('xliff', "{$app['translator.path']}/{$value}.xlf", $value);
    }
    
    return $translator;
}));


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
$app['twig']->addGlobal('GOOGLE_ANALYTICS', $google_analytics);

//SWIFTMAILER
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app['swiftmailer.options'] = $app['smtp.options'];


// Registers Monolog extension
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile'       => __DIR__ . '/../storage/app.log',
    'monolog.name'          => 'app',
    'monolog.level'         => 300 //WARNING
));


//UrlGenerator
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());


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