<?php 
use Silex\Application;

namespace ApiClient;

class Repositories {
    
    private $_app;
    public  $repos;
    public  $repos_url;
    public  $readme_store;
    public  $releases_store;
    public  $repos_list_filename;
    public  $repos_languages;
    public  $repos_token;
    
    function __construct(\Silex\Application $app){
        $this->_app                 = $app;
        //$this->repos                = "https://api.github.com/users/{$this->_app['repos.config']['github']['user']}/repos?sort=updated&direction=desc&per_page=200";
        $this->repos                = "https://api.github.com/search/repositories?q=user:{$this->_app['repos.config']['github']['user']}&sort=updated&order=desc";
        $this->repos_url            = "https://api.github.com/repos/{$this->_app['repos.config']['github']['user']}/";
        $this->readme_store         = $this->_app['repos.config']['storage'] . 'pages/';
        $this->releases_store       = $this->_app['repos.config']['storage'] . 'releases/';
        $this->repos_list_filename  = 'repos-' . date('Y-m-d') . '.json';
        $this->repos_languages      = $this->_app['repos.config']['storage'] . 'repos-languages-' . date('Y-m-d') . '.json';
		$this->repos_token			= $this->_app['repos.config']['github']['token'];
    }
    
    private function _get_json($url,$params = array()){
        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $url); 
        curl_setopt($cURL, CURLOPT_USERAGENT, $this->_app['repos.config']['github']['repo']);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURL, CURLOPT_USERPWD,"{$this->repos_token}:x-oauth-basic");
        
        foreach ($params as $name => $value) curl_setopt($cURL, $name, $value);
        
        $json = curl_exec($cURL);
        curl_close($cURL);
        
        return $json;
    }
    
    public function list_all(){
        if (file_exists($this->_app['repos.config']['storage'] . $this->repos_list_filename)) 
            return file_get_contents($this->_app['repos.config']['storage'] . $this->repos_list_filename);
        
        $this->garbage_collection();
        
        $data = json_decode($this->_get_json($this->repos));
		$data = json_encode($data->items);
        file_put_contents($this->_app['repos.config']['storage'] . $this->repos_list_filename, $data);
        
        return $data;
    }
    
    public function get_repositories($criteria = array()){
            
        $repos = json_decode($this->list_all(),true);
        
        if($criteria) {
            $repos = array_filter($repos, function($obj) use ($criteria){
                $return = true;
                
                foreach ($criteria as $k => $v) {
                    if(is_array($v)) {
                        $return = (in_array($obj[$k], $v));
                    }
                    else {
                        $return = ($obj[$k] == $v);
                    }
                }
                
                return $return;
            });
        }
        
        return json_encode(array_values($repos));
    }
    
    public function garbage_collection(){
        $dir = opendir($this->_app['repos.config']['storage']);

        while ($file = readdir($dir)) {
            if (preg_match('/^\./i', $file) || is_dir($this->_app['repos.config']['storage'] . $file)) 
                continue;
            
            unlink($this->_app['repos.config']['storage'] . $file);
        }
        
        unset($dir);
    }
    
    public function get_languages($criteria = array()) {
        if (file_exists($this->repos_languages)) 
            $repos = file_get_contents($this->repos_languages);
        else
            $repos = $this->mount_languages_file(true);
        
        $repos = json_decode($repos,true);
        $repos = $repos[0]['projects'];
        
        if($criteria) {
            $repos = array_filter($repos, function($obj) use ($criteria){
                $return = true;
                
                foreach ($criteria as $k => $v) {
                    if(is_array($obj[$k])) {
                        $arr = array_map('strtolower', array_keys($obj[$k]));
                        $return = (in_array(strtolower($v),$arr));
                    }
                    else {
                        $return = ($obj[$k] == $v);
                    }
                }
                
                return $return;
            });
        }
        
        return array_values($repos);
    }
    
    public function get_languages_total() {
        if (file_exists($this->repos_languages)) 
            $langs = file_get_contents($this->repos_languages);
        else
            $langs = $this->mount_languages_file(true);
        
        $langs = json_decode($langs,true);
        
        return $langs[1]['total'];
    }
    
    
    
    public function get_languages_cloud() {
        $arr    = $this->get_languages_total();
        
        $min    = min($arr);
        $max    = max($arr);
        $tags   = array();
        
        $smallest   = 12;
        $largest    = 26;
        $unit       = 'pt';
        
        $spread = $max - $min;
        if ( $spread <= 0 )
            $spread = 1;
        
        $font_spread = $largest - $smallest;
        if ( $font_spread < 0 )
            $font_spread = 1;
        
        $font_step = $font_spread / $spread;
        
        foreach ($arr as $lang => $num) {
            $tags[] = "<a href='/projects/q/language/{$lang}' title='" . $this->_app['translator']->trans('projetos que utilizam') . " {$lang}' style='font-size:" . 
                str_replace( ',', '.', number_format(($smallest + ( ($num - $min ) * $font_step ) ), 1) ) . 
                "$unit;'>$lang</a> &nbsp; &nbsp; ";
        }
        
        shuffle($tags);
        
        return $tags;
    }
    
    public function mount_languages_file($return_json=false) {
            
        $json_langs = '[{"projects":[';
        $repos      = json_decode($this->get_repositories(array('fork' => false)),true);
        $total      = array();
        
        foreach ($repos as $key => $repo) {
            $data       = $this->_get_json($this->repos_url . $repo["name"] . '/languages');
            $json_langs .= '{"name":"' . $repo["name"] . '",';
            $json_langs .= '"languages":' . $data . '},';
            
            $arr_data = json_decode($data,true);
            foreach ($arr_data as $lang => $num) {
                if(!isset($total[$lang])) $total[$lang] = 0;
                $total[$lang] += $num;
            }
        }
        
        $json_langs = substr($json_langs, 0, -1) . ']},';
        $json_langs .= '{"total":' . json_encode($total) . '}]';
        
        file_put_contents($this->repos_languages, $json_langs);
        
        return ($return_json) ? $json_langs : true;
    }
    
    /*
     * Solution found in http://subinsb.com/php-check-if-string-is-json
     * */
    public function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    } 
    
    public function get_readme($name) {
        if (file_exists($this->readme_store . $name . ".{$this->_app['locale']}.html")) 
            return file_get_contents($this->readme_store . $name . ".{$this->_app['locale']}.html");
        
        if (file_exists($this->readme_store . $name . '.html')) 
            return file_get_contents($this->readme_store . $name . '.html');
        
        return $this->download_readme(array('name' => $name), true);
    }
    
    public function download_readme($criteria = array('fork' => false), $return_html=false) {
        if(!is_dir($this->readme_store)) mkdir($this->readme_store, 0777, true);
        
        $repos              = json_decode($this->get_repositories($criteria),true);
        $locale_optional    = $this->_app['repos.config']['github']['locale']['optional'];
        $locale_default     = $this->_app['repos.config']['github']['locale']['default'];
        $arr_locale         = array_merge($locale_optional, array($locale_default));
        
        foreach ($repos as $key => $repo) {
            foreach ($arr_locale as $lang) {
                $lang = ($lang == $locale_default) ? "" : '.' . trim($lang);
                
                $readme_html = $this->_get_json(
                    $this->repos_url . $repo["name"] . "/contents/README{$lang}.md", 
                    array(CURLOPT_HTTPHEADER => array('Accept: application/vnd.github.v3.html+json'))
                );
                
                if (!$this->is_json($readme_html)) //if has error the $readme_html variable is json with message content
                    file_put_contents($this->readme_store . $repo["name"] . $lang . '.html', $readme_html);
            }
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
    
    public function get_releases($project) {
        if (file_exists($this->releases_store . $project . '.json')) 
            return file_get_contents($this->releases_store . $project . '.json');
        
        return $this->download_releases(array('name' => $project), true);
    }
    
    public function download_releases($criteria = array('fork' => false), $return_content=false) {
        if(!is_dir($this->releases_store)) mkdir($this->releases_store, 0777, true);
        
        $repos = json_decode($this->get_repositories($criteria),true);
        
        foreach ($repos as $key => $repo) {
            $content = $this->_get_json($this->repos_url . $repo["name"] . '/releases');
            
            file_put_contents($this->releases_store . $repo["name"] . '.json', $content);
        }
        
        return ($return_content) ? $content : true;
    }
    
    public function update_releases() {
        $dir = opendir($this->releases_store);

        while ($file = readdir($dir)) {
            if (preg_match('/^\./i', $file)) continue;
            unlink($this->releases_store . $file);
        }
        
        unset($dir);
        
        $this->download_releases();
        
        return true;
    }
}