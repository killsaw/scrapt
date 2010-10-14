<?php

class Scrapt_Agent
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
	
	public function request($method, $url, $params)
	{
		$s = curl_init(); 		
		
		curl_setopt($s, CURLOPT_URL, $url); 
		curl_setopt($s, CURLOPT_TIMEOUT, $this->timeOut); 
		curl_setopt($s, CURLOPT_MAXREDIRS, $this->maxRedirects); 
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($s, CURLOPT_FOLLOWLOCATION, $this->followLocation); 
		curl_setopt($s, CURLOPT_COOKIEJAR, $this->cookieFile); 
		curl_setopt($s, CURLOPT_COOKIEFILE, $this->cookieFile); 
		curl_setopt($s, CURLOPT_AUTOREFERER, true);
		curl_setopt($s, CURLOPT_HEADER, true); 
		
		// Authenticate, if needed.
		if (!empty($this->username) && !empty($this->password)) { 
			curl_setopt($s, CURLOPT_USERPWD, 
				$this->username.':'.$this->password); 
		} 
		
		// Set payload.
		if ($method == self::METHOD_POST) {
			curl_setopt($s,CURLOPT_POST, true); 
			curl_setopt($s,CURLOPT_POSTFIELDS, $params);		
		}
		
		curl_setopt($s, CURLOPT_USERAGENT, $this->userAgent); 
		curl_setopt($s, CURLOPT_REFERER, $this->referer); 
		
		$data = curl_exec($s);
		
		// Split headers from body.
		$parts = explode("\r\n\r\n", $data, 2);
		
		// Check if cURL smashed multiple header blocks into one.
		$redirect_headers = array();
		for($i=0; $i < $this->maxRedirects; $i++) {
			if (preg_match('/^HTTP\/1\.[01] 302/', $parts[0])) {
				$redirect_header = array_shift($parts);
				$redirect_headers[] = $this->parseHeaders($redirect_header); 
				$parts = explode("\r\n\r\n", $parts[0], 2);
			} else {
				break;
			}
		}
		
		// Parse headers.
		$headers = $this->parseHeaders($parts[0]);
		$data = $parts[1];
		$info = curl_getinfo($s);
		
		curl_close($s); 
		
		return array('headers'=>$headers,
					 'data'=>trim($data), 
					 'info'=>$info, 
					 'redirects'=>$redirect_headers);
	}
	
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

	public function getInstance()
	{
		static $instance = null;
		
		if (is_null($instance)) {
			$instance = new Scrapt_Agent;
		}
		return $instance;
	}
}
