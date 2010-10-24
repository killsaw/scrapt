<?php

class Scrapt_Component_Input
{
    protected $name;
    protected $type;
    protected $value;
    protected $options = array();
    protected $originalValue;
    
    public function __construct($name=null, $value=null)
    {
        $this->name = $name;
        $this->value = $value;
    }
    
    public function setType($type)
    {
    	$this->type = $type;
    }
    
    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    static public function fromDom($dom)
    {
		$input = new Scrapt_Component_Input;
		$input->setValue($dom->value);
		$input->setName($dom->name);
		
		if (isset($dom->type)) {
			$input->setType($dom->type);
		} else {
			// How do I get an element type?
			$input->setType($dom->tag);
		}
		return $input;
    }
}