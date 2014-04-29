<?php 
namespace ApiClient;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Repositories {
    
    private $_app;
    public  $repos;
    
    function __construct(\Silex\Application $app){
        $this->_app = $app;
        $this->repos = "https://api.github.com/users/{$this->_app['repos.config']['github']['user']}/repos?sort=updated&direction=desc";
        $this->repos_list_filename = 'repos-' . date('Y-m-d') . '.json';
    }
    
    private function _get_json($url){
        $cURL = curl_init($url);
        curl_setopt($cURL, CURLOPT_USERAGENT, $this->_app['repos.config']['github']['repo']);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($cURL);
        curl_close($cURL);
        
        return $json;
    }
    
    public function list_all(){
        if (file_exists($this->_app['repos.config']['storage'] . $this->repos_list_filename)) 
            return file_get_contents($this->_app['repos.config']['storage'] . $this->repos_list_filename);
        
        $this->garbage_collection();
        
        $data = $this->_get_json($this->repos);
        file_put_contents($this->_app['repos.config']['storage'] . $this->repos_list_filename, $data);
        
        return $data;
    }
    
    public function get_repositories($criteria = array()){
            
        $repos = json_decode($this->list_all(),true);
        
        if($criteria) {
            $repos = array_filter($repos, function($obj) use ($criteria){
                $return = true;
                
                foreach ($criteria as $k => $v) {
                    $return = ($obj[$k] == $v);
                }
                
                return $return;
            });
        }
        
        return json_encode(array_values($repos));
    }
    
    public function garbage_collection(){
        $dir = opendir($this->_app['repos.config']['storage']);

        while ($file = readdir($dir)) {
            if (in_array($file, array('.', '..'))) continue;
            unlink($this->_app['repos.config']['storage'] . $file);
        }
        
        unset($dir);
    }
}

$app['repositories'] = function() use ($app) {return new Repositories($app);};

$api = $app["controllers_factory"];
$api->get('get-repositories', function() use ($app) { return $app['repositories']->get_repositories(array('fork'=>false)); });

$api->get('projects', function() use ($app) {
    $projects = $app['repositories']->get_repositories(array('fork'=>false));
    return $app['twig']->render('projects.twig',array('projects' => json_decode($projects,true)));
});

return $api;