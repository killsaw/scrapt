<?php

class Scrapt_Agent_cURL extends Scrapt_Agent
{
    public function request($method, $url, array $params=array())
    {
        $s = curl_init();     
        
        $headers = array();
		$header[] = "Accept: text/xml,application/xml,application/xhtml+xml,".
					 "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		if ($method == self::METHOD_POST) {
			$header[] = 'Content-type: application/x-www-form-urlencoded';
		}
		$header[] = "Pragma: "; // browsers keep this blank. 
        
        // Set payload.
        if ($method == self::METHOD_POST) {
        	curl_setopt($s, CURLOPT_POST, true); 
            curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        
        if ($method == self::METHOD_GET) {
        	if (count($params) > 0) {
        		$url .= http_build_query($params);
        	}
        }
        
        curl_setopt($s, CURLOPT_URL, $url); 
        curl_setopt($s, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($s, CURLOPT_TIMEOUT, 900);
 		curl_setopt($s, CURLOPT_MAXREDIRS, $this->maxRedirects); 
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($s, CURLOPT_FOLLOWLOCATION, $this->followLocation); 
        curl_setopt($s, CURLOPT_COOKIEJAR, $this->cookieFile); 
        curl_setopt($s, CURLOPT_COOKIEFILE, $this->cookieFile); 
		curl_setopt($s, CURLOPT_AUTOREFERER, true);        
        curl_setopt($s, CURLOPT_HEADER, true); 
		curl_setopt($s, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);         

        // Authenticate, if needed.
        if (!empty($this->username) && !empty($this->password)) { 
            curl_setopt($s, CURLOPT_USERPWD, 
                $this->username.':'.$this->password); 
        } 
        
        curl_setopt($s, CURLOPT_USERAGENT, $this->userAgent); 
        
        if (!empty($this->referer)) {
        	curl_setopt($s, CURLOPT_REFERER, $this->referer); 
        	curl_setopt($s, CURLOPT_AUTOREFERER, false);
        }
        
        $data = curl_exec($s);
        $info = curl_getinfo($s);
        
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