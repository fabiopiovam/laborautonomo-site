<?php 
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/api.class.php';

$app['repositories'] = function() use ($app) {return new ApiClient\Repositories($app);};

$api = $app["controllers_factory"];


$api->get('get-repositories', function() use ($app) { return $app['repositories']->get_repositories(array('fork'=>false)); });


$api->get('/', function() use ($app) {
    $projects = $app['repositories']->get_repositories(array('fork'=>false));
    $languages  = $app['repositories']->get_languages_cloud();
    
    return $app['twig']->render('projects.twig',array('projects' => json_decode($projects,true), 'languages' => $languages));
})
->bind('projects');


$api->get('/q/{field}/{value}', function($field,$value) use ($app) {
    
    if($field == 'language'){
        $arr_projects = $app['repositories']->get_languages(array('languages' => $value));
        array_walk($arr_projects, function (&$item, $key) {$item = $item['name'];});
        
        $criteria = array('name' => $arr_projects);
    }
    else {
        $criteria = array($field => $value);
    }
    
    $projects   = $app['repositories']->get_repositories($criteria);
    $languages  = $app['repositories']->get_languages_cloud();
    
    return $app['twig']->render('projects.twig',array('projects' => json_decode($projects,true), 'languages' => $languages, 'param' => $value));
})
->bind('filter-projects');


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