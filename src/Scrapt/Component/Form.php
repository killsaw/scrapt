<?php

class Scrapt_Component_Form
{
    protected $inputs = array();
    protected $method = null;
    protected $action = null;
    protected $name   = null;
    protected $pageURL = null;
    
    const METHOD_GET='GET',
          METHOD_POST='POST';
    
    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setAction($action)
    {
        $this->action = html_entity_decode($action);
    }
    
    public function getMethod()
    {
    	return strtolower($this->method);
    }
    
    public function getAction()
    {
    	return $this->action;
    }
    
    public function getPageURL()
    {
    	return $this->pageURL;
    }
        
    public function setName($name)
    {
		$this->name = $name;
    }

    public function getName()
    {
		return $this->name;
    }
    
    public function setPageURL($url)
    {
    	$this->pageURL = $url;
    }
    
    public function addInput(Scrapt_Component_Input $i)
    {
        $this->inputs[$i->getName()] = $i;
    }
    
    public function __set($name, $value)
    {
        $this->inputs[$name]->setValue($value);
    }

    public function __get($name)
    {
        return $this->inputs[$name]->getValue();
    }
    
     public function __isset($name) {
        return isset($this->inputs[$name]);
    }

    public function __unset($name) {
        unset($this->inputs[$name]);
    }
    
    static public function fromDom($dom_form, $onURL=null)
	{
		if (!($dom_form instanceof simple_html_dom_node)) {
			var_dump($dom_form); exit;
		}
		$f = $dom_form;
				
		$form = new Scrapt_Component_Form;
		$form->setName($f->name);
		$form->setMethod($f->method);
		$form->setAction($f->action);
		$form->setPageURL($onURL);
		
		// Find inputs.
		$inputs = $f->find('select,input,textarea');
		foreach($inputs as $i) {
			$form->addInput(Scrapt_Component_Input::fromDom($i));
		}
		return $form;
    }
    
    public function getPayload()
    {
        $payload = array();
        foreach((array)$this->inputs as $name=>$input) {
        	if (!empty($name)) {
            	$payload[$name] = $input->getValue();
            }
        }
        return $payload;
    }
}