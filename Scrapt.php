<?php

require_once __DIR__.'/Scrapt/Webpage.php';
require_once __DIR__.'/vendor/simple_html_dom.php';

class Scrapt
{
	protected $url;
	protected $cacheDuration = 600; // 10 minutes.

	public function __construct($url=null)
	{
		$this->url = $url;
	}
	
	public function setURL($url)
	{
		$this->url = $url;
	}
	
	public function cache()
	{
		$cache_name = sprintf("%s/cache/%s.cache.html", __DIR__, md5($this->url));
		
		if (file_exists($cache_name) && !$this->cacheIsOld($cache_name)) {
			$this->data = file_get_contents($cache_name);
		} else {
			$this->data = file_get_contents($this->url);
			file_put_contents($cache_name, $this->data);
		}
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