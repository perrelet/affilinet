<?php

namespace Affilinet;

class Affiliate {
	
	protected $affiliate_id;
	
	public function __construct ($affiliate_id) {
		
		$this->affiliate_id = $affiliate_id;
		
	}
	
	public function get_id () {
		
		return $this->affiliate_id;
		
	}
	
}