<?php

namespace Affilinet;

class Affilinet {
	
	protected $admin;
	protected $visit;
	protected $integrations = [];
	
	public function run() {
		
		$this->load_admin();
		$this->load_classes();
		$this->load_integrations();
		
		add_action('init', [$this, 'init']);
		
	}
	
	public function load_admin () {
		
		require_once AFFILINET_PATH . 'include/admin/admin.class.php';
		
		$this->admin = new Admin();
		
	}
	
	public function load_classes () {
		
		foreach (glob(AFFILINET_PATH . 'include/classes/*.php') as $class) require_once $class;

	}
	
	public function load_integrations () {
		
		require_once AFFILINET_PATH . 'include/integrations/integration.class.php';
		require_once AFFILINET_PATH . 'include/integrations/woocommerce.class.php';
		require_once AFFILINET_PATH . 'include/integrations/woocommerce-subscriptions.class.php';
		
		$this->integrations['woocommerce'] = new Woocommerce();
		$this->integrations['woocommerce-subscriptions'] = new Woocommerce_Subscriptions();
		
	}
	
	public function get_integration ($integration) {
		
		return isset($this->integrations[$integration]) ? $this->integrations[$integration] : null;
		
	}
	
	public function init () {
		
		if (wp_doing_cron() || wp_doing_ajax()) return;
		
		$this->get_visit();
		
		//$this->visit = new Visit();
		//$remote = new Remote();
		//print_r($remote->add_referral(5, 10, 'GBP', 'test', 'product: 123', 'test'));
		/* $campaign_name = $this->get_option('campaign');
		print_r($remote->add_visit(5, $visit, $campaign_name)); */
		//print_r($remote->delete_visit(3930));
		
	}
	
	public function get_option ($option = false) {
		
		$options = get_option(AFFILINET_OPTIONS_SLUG);

		return isset($options[$option]) ? $options[$option] : false;
		
	}
	
	public function get_visit () {
		
		if (is_null($this->visit)) $this->visit = new Visit();
		return $this->visit;
		
	}
	
}