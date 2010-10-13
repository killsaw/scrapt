<?php

class Scrapt_Webpage
{
	protected $data;
	protected $url;
	
	public function contains($string, $case_sensitive=true)
	{
		if ($case_sensitive) {
			return (strpos($this->data, $string) !== false);
		} else {
			return (stripos($this->data, $string) !== false);
		}
	}
	
	public function findByRegex($regex)
	{
		if (preg_match_all($regex, $this->data, $matches)) {
			array_shift($matches);
			return $matches;
		}
		return false;
	}
	
	public function findBySelector($selector)
	{
		return $this->dom->find($selector);
	}

	public function setURL($url)
	{
		$this->url = $url;
	}
	
	public function setData($data)
	{
		$this->data = $data;
		$this->dom = str_get_html($data);
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function getURL()
	{
		return $this->url;
	}

	public static function fromData($data, $url=null)
	{
		$page = new Scrapt_Webpage;
		$page->setURL($url);
		$page->setData($data);
		return $page;
	}
}
