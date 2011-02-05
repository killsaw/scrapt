<?php

class Scrapt_Webpage
{
    protected $data;
    protected $headers;
    protected $info;
    protected $url;
    protected $dom;
    
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    public function toPlaintext()
    {
        return preg_replace('/(\s+)+/', '\\1', 
                 strip_tags($this->data));
    }

    public function toString()
    {
    	return $this->data;
    }
    
    public function contains($string, $case_sensitive=false)
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
    
    public function getForms()
    {
    	$forms = array();
    	$dom = $this->findBySelector('form');
    	
    	foreach($dom as $f) {
    		$forms[] = Scrapt_Component_Form::fromDom($f, $this->url);
    	}
    	return $forms;
    }
    
    public function getForm($name=null)
    {
    	$forms = $this->getForms();
    	
    	if (count($forms) < 1) {
    		return false;
    	}
    	
    	if (is_null($name)) {
    		return $forms[0];
    	}

    	foreach($forms as $f) {
    		if ($f->getName() == $name) {
    			return $f;
    		}
    	}
    	return false;
    }
    
    public function getLinks()
    {
        $links = array();
        
        if (!is_object($this->dom)) {
            return false;
        }
        
        if ($this->dom instanceof simple_html_dom) {    
            $dom = $this->dom;
            
            if (!is_object($dom->root)) {
                $dom = str_get_html($this->data);
            }
            
            $dom_links = $dom->find('a');
            
            foreach($dom_links as $l) {
                $links[$l->innertext] = $l->href;
            }
            $this->dom->clear();        
        }
        return $links;
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
    
    public function setHeaders($headers)
    {
    	$this->headers = (array)$headers;
    }

    public function setInfo($info)
    {
    	$this->info = $info;
    }
    
    public function getURL()
    {
        return $this->url;
    }
    
    public function fixHTML()
    {
    	$doc = new DOMDocument;
		libxml_use_internal_errors(TRUE);
		$doc->loadHTML($this->data);
		libxml_use_internal_errors(FALSE);
		$doc->formatOutput = TRUE;
		$this->data = $doc->saveHTML();
		unset($doc);
    }
    
    public function __destruct()
    {
        $this->dom->clear();
    }
    
    public function __toString()
    {
    	return $this->toString();
    }
    
    public static function fromURL($url)
    {
        $scrapt = new Scrapt($url);
        return self::fromData($scrapt->cache(), $url);
    }

    public static function fromData($data, $url=null)
    {
        $page = new Scrapt_Webpage($url);
        $page->setData($data['data']);
        $page->setHeaders($data['headers']);
        $page->setInfo($data['info']);
        
        return $page;
    }
}
