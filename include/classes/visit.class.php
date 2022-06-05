<?php

namespace Affilinet;

class Visit {
	
	protected $entry_visit = false;
	protected $visitor;
	protected $affiliate;
	
	public function __construct () {

		$this->visitor = new Visitor();
		
		$affiliate_id = $this->get_affiliate_id();
		if ($affiliate_id) $this->affiliate = new Affiliate($affiliate_id);
		
	}
	
	public function get_affiliate_id () {
		
		if (!is_null($this->affiliate)) return $this->affiliate->get_id();
		
		//PATCH
		$old_affiliate_id = $this->visitor->read_old_cookie();
		if ($old_affiliate_id) $this->visitor->add_cookie($old_affiliate_id);
		//

		$affiliate_id = $this->visitor->read_cookie();

		if (!$affiliate_id) {

			$affiliate_id = $this->check_query_string();
		
			if ($affiliate_id) {
				
				$this->entry_visit = true;
				
				$campaign_name = Affilinet()->get_option('campaign');
				$remote = new Remote();
				$remote->add_visit($affiliate_id, $this, $campaign_name);	
			
			}

		}


		/* $affiliate_id = $this->check_query_string();
		
		if ($affiliate_id) {
			
			$this->entry_visit = true;
			
			$campaign_name = Affilinet()->get_option('campaign');
			$remote = new Remote();
			$remote->add_visit($affiliate_id, $this, $campaign_name);
			
		} else {
			
			$affiliate_id = $this->visitor->read_cookie();
			
		} */
		
		return $affiliate_id;
		
	}
	
	public function set_affiliate ($affiliate_id) {
		
		$this->affiliate = new Affiliate($affiliate_id);
		
	}
	
	public function has_affiliate () {
		
		return !is_null($this->affiliate);
		
	}
	
	public function get_affiliate () {
		
		return $this->affiliate;
		
	}
	
	public function check_query_string () {
		
		if (!isset($_GET[$this->get_referral_key()])) return null;
		
		$affiliate_id = $_GET[$this->get_referral_key()];
		$this->visitor->add_cookie($affiliate_id);
		
		return $affiliate_id;
		
	}
	
	public function get_referral_key () {
		
		return Affilinet()->get_option('referral_variable');
		
		//return "ref";
		
	}
	
	public function get_url () {
		
		return $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
	}
	
	
	public function get_ip () {
		
		return $_SERVER['REMOTE_ADDR'];
		
	}
	

	
}