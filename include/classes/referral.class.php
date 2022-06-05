<?php

namespace Affilinet;

class Referral extends Obj {
	
	protected $order_amount;
	protected $amount;
	protected $currency;
	protected $description;
	protected $reference;
	protected $context;
	
	public function __construct ($order_amount, $currency, $order_id, $description = '') {

		$this->order_amount = $order_amount;
		$this->currency = $currency;
		$this->description = $description;
		$this->reference = "net-" . Affilinet()->get_option('campaign') . "-" . $order_id;
		$this->context = get_site_url();
		
		$this->amount = $this->get_referral_amount();
		
	}
	
	public function get_referral_amount () {

		return apply_filters('affilinet-referral-amount', $this->order_amount * $this->get_referral_rate(), $this);
		
	}
	
	public function get_referral_rate () {
		
		return apply_filters('affilinet-referral-rate', floatval(Affilinet()->get_option('rate')) / 100, $this);
		
	}
	
	public function send ($affiliate_id) {
		
		$remote = new Remote();
		return $remote->add_referral($affiliate_id, apply_filters('affilinet-referral-send', $this), 'unpaid');
		
	}
	
}