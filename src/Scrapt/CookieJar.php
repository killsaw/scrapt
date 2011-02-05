<?php

class Scrapt_CookieJar
{
	static $cookie_file;
	static $cookies = array();
	
	static protected function parseDomain($domain)
	{
		$parts = explode('.', strtolower($domain));
		
		if (count($parts) > 1) {
			$domain = join('.', array_slice($parts, -2));
			$subdomain = join('.', array_slice($parts, 0, count($parts)-2));
		} else {
			$domain = $parts[0];
			$subdomain = false;
		}
		return array('domain'=>$domain, 'subdomain'=>$subdomain);
	}
	
	static public function getCookiesForURL($url)
	{
		$matches = array();
		$url_info = parse_url($url);		

		$find_domain = self::parseDomain($url_info['host']);
		$find_subdomains = explode('.', $find_domain['subdomain']);
		$find_subdomains = array_reverse($find_subdomains);
		
		foreach(self::$cookies as $domain=>$cookies) {
			$cookie_domain = self::parseDomain($domain);
			
			// Remove obvious mismatches.
			if ($cookie_domain['domain'] != $find_domain['domain']) {
				continue;
			}
			
			// Check the subdomain
			// file: .star2star.com
			// find: portal.star2star.com
			$cookie_domain['subdomain'] = 'portal';
			 
			$cookie_subdomains = explode('.', $cookie_domain['subdomain']);
			$cookie_subdomains = array_reverse($cookie_subdomains);
			
			foreach($cookie_subdomains as $k=>$v) {
				if (!empty($v)) {
					// Cookie subdomain is more specific than search.
					if (!isset($find_subdomains[$k])) {
						continue 2;
					}
					// Cookie subdomain doesn't match search.
					if ($find_subdomains[$k] != $v) {
						continue 2;
					}
				}
				
				foreach($cookies as $c) {
					// Check paths and SSL
					if (!$c->isExpired()) {
						if ($url_info['scheme'] == 'https') {
							// Check cookie security. Not sure how this should work.
						}
						if ($c->path !== '/') {
							// Hard stuff here.
						}
						$matches[] = $c;
					}
				}
				break;
			}
		}
		return $matches;
	}
	
	static public function setCookieFile($file)
	{
		self::$cookie_file = $file;
		$lines = file($file);
		foreach($lines as $l) {
			$l = trim($l);
			
			// Skip comments and empty lines.
			if (empty($l) || $l[0] == '#') {
				continue;
			}
			$fields = preg_split('/\s+/', $l);

			$domain = strtolower($fields[0]);
			if (!isset(self::$cookies[$domain])) {
				self::$cookies[$domain] = array();
			}
			$cookie = new Scrapt_Cookie;
			$cookie->domain = $domain;
			$cookie->global = ($fields[1] == 'TRUE')?true:false;
			$cookie->path = $fields[2];
			$cookie->secure = ($fields[3] == 'TRUE')?true:false;
			$cookie->expiry_time = $fields[4];
			$cookie->name = $fields[5];
			$cookie->value = $fields[6];
			
			self::$cookies[$domain][] = $cookie;
		}
	}
}
