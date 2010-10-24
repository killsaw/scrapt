<?php

class Scrapt_Agent_cURL extends Scrapt_Agent
{
    public function request($method, $url, array $params=array())
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
            curl_setopt($s, CURLOPT_POST, true); 
            curl_setopt($s, CURLOPT_POSTFIELDS, $params);        
	        curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded')); 
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
        
        if ($headers === false) {
            return false;
        }
        $data = $parts[1];
        $info = curl_getinfo($s);
        
        curl_close($s); 
        
        return array('headers'=>$headers,
                     'data'=>trim($data), 
                     'info'=>$info, 
                     'redirects'=>$redirect_headers);
    }
}