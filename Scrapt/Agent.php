<?php

abstract class Scrapt_Agent
{
    protected $cookies = array();
    protected $userAgent = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8';
    protected $referer = null;
    
    protected $followLocation = true;
    protected $cookieFile = '/tmp/cookies.txt';
    protected $maxRedirects = 10;
    protected $timeOut = 60;

    const METHOD_POST = 'post',
          METHOD_GET  = 'get';
    
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
        $header_str = trim($header_str);
        
        if (empty($header_str)) {
            return false;
        }
        
        $parts = preg_split("/[\r\n]+/", $header_str, 2);
        
        if (count($parts) < 2) {
        	return $parts;
        }
        
        list($status, $header_str) = $parts;
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
