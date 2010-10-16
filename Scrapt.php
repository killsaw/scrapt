<?php

require_once __DIR__.'/Scrapt/Webpage.php';
require_once __DIR__.'/Scrapt/Agent.php';
require_once __DIR__.'/Scrapt/Agent/cURL.php';
require_once __DIR__.'/Scrapt/Agent/Basic.php';
require_once __DIR__.'/vendor/simple_html_dom.php';

class Scrapt
{
	protected $url;
	protected $data;
	protected $cacheDuration = 1200; // 20 minutes.

	public function __construct($url=null)
	{
		$this->url = $url;
		$this->setAgent(new Scrapt_Agent_cURL);
	}
	
	public function setAgent(Scrapt_Agent $a)
	{
		$this->agent = $a;
	}
	
	public function setURL($url)
	{
		$this->url = $url;
	}
	
	public function setCacheDuration($duration)
	{
		$this->cacheDuration = intval($duration);
	}
	
	public function cache()
	{
		$cache_path = sprintf("%s/cache/%s.cache.html", 
						__DIR__, md5($this->url));
		
		if (!file_exists($cache_path) || $this->cacheIsOld($cache_path)) {
			$reply = $this->agent->get($this->url);
			$this->data = $reply['data'];
			file_put_contents($cache_path, $this->data);
		}
		$this->data = file_get_contents($cache_path);
		return $this->data;
	}
	
	protected function cacheIsOld($cache_file)
	{
		$age = time() - filectime($cache_file);
		
		if ($age > $this->cacheDuration) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getPage()
	{
		return Scrapt_Webpage::fromData($this->data, $this->url);
	}
}