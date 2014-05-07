<?php 
namespace ApiClient;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Repositories {
    
    private $_app;
    public  $repos;
    public  $readme_url;
    public  $readme_store;
    public  $repos_list_filename;
    
    function __construct(\Silex\Application $app){
        $this->_app                 = $app;
        $this->repos                = "https://api.github.com/users/{$this->_app['repos.config']['github']['user']}/repos?sort=updated&direction=desc";
        $this->readme_url           = "https://api.github.com/repos/{$this->_app['repos.config']['github']['user']}/";
        $this->readme_store         = $this->_app['repos.config']['storage'] . 'pages/';
        $this->repos_list_filename  = 'repos-' . date('Y-m-d') . '.json';
    }
    
    private function _get_json($url,$params = array()){
        $cURL = curl_init($url);
        curl_setopt($cURL, CURLOPT_USERAGENT, $this->_app['repos.config']['github']['repo']);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        
        foreach ($params as $name => $value) curl_setopt($cURL, $name, $value);
        
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
            if (preg_match('/^\./i', $file)) continue;
            unlink($this->_app['repos.config']['storage'] . $file);
        }
        
        unset($dir);
    }
    
    public function get_readme($name) {
        if (file_exists($this->readme_store . $name . '.html')) 
            return file_get_contents($this->readme_store . $name . '.html');
        
        return $this->download_readme(array('name' => $name), true);
    }
    
    public function download_readme($criteria = array('fork' => false), $return_html=false) {
        if(!is_dir($this->readme_store)) mkdir($this->readme_store, 0777, true);
        
        $repos = json_decode($this->get_repositories($criteria),true);
        
        foreach ($repos as $key => $repo) {
            $readme_html = $this->_get_json(
                $this->readme_url . $repo["name"] . '/readme', 
                array(CURLOPT_HTTPHEADER => array('Accept: application/vnd.github.v3.html+json'))
            );
            
            file_put_contents($this->readme_store . $repo["name"] . '.html', $readme_html);
        }
        
        return ($return_html) ? $readme_html : true;
    }
    
    public function update_project_pages() {
        $dir = opendir($this->readme_store);

        while ($file = readdir($dir)) {
            if (preg_match('/^\./i', $file)) continue;
            unlink($this->readme_store . $file);
        }
        
        unset($dir);
        
        $this->download_readme();
        
        return true;
    }
}

$app['repositories'] = function() use ($app) {return new Repositories($app);};

$api = $app["controllers_factory"];
$api->get('get-repositories', function() use ($app) { return $app['repositories']->get_repositories(array('fork'=>false)); });
$api->get('update-pages', function() use ($app) { return $app['repositories']->update_project_pages(); });

$api->get('/', function() use ($app) {
    $projects = $app['repositories']->get_repositories(array('fork'=>false));
    return $app['twig']->render('projects.twig',array('projects' => json_decode($projects,true)));
})
->bind('projects');

$api->get('/{name}', function($name) use ($app) {
    $readme_html = $app['repositories']->get_readme($name);
    return $app['twig']->render('project.twig',array('project' => $name, 'readme_html' => $readme_html));
})
->bind('project');

return $api;