<?php

abstract class Scrapt_Agent
{
	protected $cookies = array();
	protected $userAgent = 'Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)';
	protected $referer = null;
	
	protected $followLocation = true;
	protected $cookieFile = '/tmp/cookies.txt';
	protected $maxRedirects = 10;
	protected $timeOut = 60;

	const METHOD_POST = 'POST',
		  METHOD_GET  = 'GET';
	
	public function get($url, array $params=array())
	{
		return $this->request(self::METHOD_GET, $url, $params);
	}

	public function post($url, array $params=array())
	{
		return $this->request(self::METHOD_POST, $url, $params);
	}
	
	abstract public function request($method, $url, array $params=array());
	
	protected function parseHeaders($header_str)
	{
		$headers = array();

		list($status, $header_str) = explode("\r\n", $header_str, 2);
		$headers['HTTP-status'] = $status;
		
		if (preg_match_all('/(.+): (.+)/', $header_str, $matches)) {
			foreach($matches[1] as $k=>$header_name) {
				$header_name = ucfirst(strtolower($header_name));
				$header_value = $matches[2][$k];
				
				if (isset($headers[$header_name])) {
					$header_entry = &$headers[$header_name];
					if (!is_array($header_entry)) {
						settype($header_entry, 'array');
					}
					$header_entry[] = $header_value;
				} else {
					$headers[$header_name] = $header_value;
				}
			}
		}
		return $headers;
	}

	public static function getInstance()
	{
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new Scrapt_Agent;
		}
		return $instance;
	}
}
