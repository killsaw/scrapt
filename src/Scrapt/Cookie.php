<?php

class Scrapt_Cookie
{
	public $name;
	public $value;

	public $domain;
	public $path = '/';
	public $secure = false;
	public $expiry_time = 0;
	
	public function isExpired()
	{
		// Skip "no expire" cookies.
		if ($this->expiry_time == 0) {
			return false;
		}
		
		if ($this->expiry_time > time()) {
			return false;
		} else {
			return true;
		}
	}
}
