<?php 
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/api.class.php';

$app['repositories'] = function() use ($app) {return new ApiClient\Repositories($app);};

$api = $app["controllers_factory"];

$api->get('get-repositories', function() use ($app) { return $app['repositories']->get_repositories(array('fork'=>false)); });

$api->get('/', function() use ($app) {
    $projects = $app['repositories']->get_repositories(array('fork'=>false));
    return $app['twig']->render('projects.twig',array('projects' => json_decode($projects,true)));
})
->bind('projects');

$api->get('/{name}', function($name) use ($app) {
    $readme_html    = $app['repositories']->get_readme($name);
    $releases       = json_decode($app['repositories']->get_releases($name), true);
    $details        = json_decode($app['repositories']->get_repositories(array('name' => $name)),true);
    
    $langs          = $app['repositories']->get_languages(array('name' => $name));
    $langs          = $langs[0]["languages"];
    
    uasort($langs,function ($a,$b) {
        if ($a[''] == $b)
            return 0;
        return ($a < $b) ? 1 : -1; 
    });
    
    return $app['twig']->render(
        'project.twig',
        array(
            'project'       => $name, 
            'readme_html'   => $readme_html, 
            'releases'      => $releases, 
            'details'       => $details[0], 
            'langs'         => $langs
    ));
})
->bind('project');

return $api;