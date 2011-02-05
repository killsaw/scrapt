<?php

class Scrapt_Agent_Basic extends Scrapt_Agent
{
    public function request($method, $url, array $params=array())
    {
        $options = array('http'=>array(
            'user_agent'     => $this->userAgent,
            'max_redirects' => $this->maxRedirects,
            'timeout'         => $this->timeOut
        ));
        $context = stream_context_create( $options );
        $page     = @file_get_contents( $url, false, $context );
     
        if ($method == self::METHOD_GET) {
            
        }
        $data = file_get_contents($url, null, $options);
        
        return array('headers'=>$http_response_header,
             'data'=>trim($data), 
             'info'=>array(),
             'redirects'=>$this->redirect_headers
             );
    }
}