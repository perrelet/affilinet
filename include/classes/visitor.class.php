<?php

namespace Affilinet;

class Visitor {
	
	public function __construct () {
		
	}

	//PATCH
	public function read_old_cookie () {
		
		$affiliate_id = isset($_COOKIE["affwp_erl_id"]) ? $_COOKIE["affwp_erl_id"] : null;
		
		return $affiliate_id;
		
	}
	//
	
	public function read_cookie () {
		
		$affiliate_id = isset($_COOKIE[$this->get_cookie_name()]) ? $_COOKIE[$this->get_cookie_name()] : null;
		
		return $affiliate_id;
		
	}
	
	public function add_cookie ($affiliate_id, $expires = 30) {
		
		if (!$expires) $expires = Affilinet()->get_option('cookie_expiration');
		
		preg_match('#[^\.]+[\.]{1}[^\.]+$#', $_SERVER['SERVER_NAME'] , $matches);
		$domain = $matches[0]; 

		setcookie($this->get_cookie_name(), $affiliate_id, time() + (60 * 60 * 24 * $expires), "/", $domain);
		
		return $affiliate_id;
		
	}
	
	protected function get_cookie_name () {
		
		return "affwp_ref";
		
	}
	
}