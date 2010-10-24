<?php

require_once __DIR__.'/Scrapt/Webpage.php';
require_once __DIR__.'/Scrapt/Agent.php';
require_once __DIR__.'/Scrapt/Agent/cURL.php';
require_once __DIR__.'/Scrapt/Agent/Basic.php';
require_once __DIR__.'/Scrapt/Component/Form.php';
require_once __DIR__.'/Scrapt/Component/Input.php';
require_once __DIR__.'/Scrapt/Component/Link.php';
require_once __DIR__.'/vendor/simple_html_dom.php';

class Scrapt
{
    static protected $agent;
    static protected $cacheDuration = 1200; // 20 minutes.

    static public function setAgent(Scrapt_Agent $a)
    {
        self::$agent = $a;
    }
    
    static public function validateURL($url)
    {
        if (preg_match('/^mailto:/i', $url)) {
            throw new Exception("Email address is not supported.");
        }
        if (preg_match('/\.(gif|png|jpe?g|swf|mpe?g|avi|wmv|mov)$/', $url)) {
            throw new Exception("Images are not supported.");
        }
    }
    
    static public function setCacheDuration($duration)
    {
        self::$cacheDuration = intval($duration);
    }
    
    static public function cacheURL($url)
    {
        $cache_path = sprintf("%s/cache/%s.cache.html", 
                        __DIR__, md5($url));
        
        if (!file_exists($cache_path) || self::cacheIsOld($cache_path)) {
            $reply = self::$agent->get($url);
            $data = $reply['data'];
            file_put_contents($cache_path, $data);
        }
		return file_get_contents($cache_path);
    }
    
    static protected function cacheIsOld($cache_file)
    {
        $age = time() - filectime($cache_file);        
        return ($age > self::$cacheDuration);
    }
    
    // Crawl method.
    static public function crawlURL($url)
    {
        $pages = array();
        $page = self::get($url);
        $page->getLinks();
        
        foreach($page->getLinks() as $link) {
            try {
                $pages[] = Scrapt_Webpage::fromURL($link);
            }
            catch (Exception $e){}
        }
        return $pages;
    }
    
    static public function get($url, $params=array(), $cache=true)
    {
		if (!isset(self::$agent)) {
			self::setAgent(new Scrapt_Agent_cURL);
		}
		
		self::validateURL($url);

    	if ($cache) {
    		$data = self::cacheURL($url);
    	} else {
    		$data = self::$agent->get($url);
    	}
        return Scrapt_Webpage::fromData($data, $url);
    }
    
    static public function resolveURL($base_url, $url)
    {
    	echo "BASE: $base_url\nURL: $url\n";
    	
    	if (preg_match('/^http[s]?:\/\//i', $url)) {
    		return $url;
    	}
    	
    	// It's relative.
    	$path_parts = explode('/', $base_url);
		$base_host = join('/', array_slice($path_parts, 0, 3));
		$base_path = array_slice($path_parts, 3);
		
		if ($url[0] == '/') {
			$full_path = $base_host.$url;
		} else {
			$full_path = $base_host;
			$full_path .= join('/', array_slice($base_path, 0, -1));
			$full_path .= $url;
		}
		echo "Full path: $full_path\n\n";
		
		return $full_path;
    }
    
    static public function submit(Scrapt_Component_Form $form, $withButton=null, $cache=false)
    {
    	$payload = $form->getPayload();
    	$action = $form->getAction();
    	$method = $form->getMethod();

    	$action = Scrapt::resolveURL($form->getPageURL(), $action);    	
		self::validateURL($action);


    	$data = self::$agent->request($method, $action, $payload);
        return Scrapt_Webpage::fromData($data['data'], $action);
    }
}