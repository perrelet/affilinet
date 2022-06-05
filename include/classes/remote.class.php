<?php

namespace Affilinet;

class Remote {
	
	protected $logging = true;
	
	protected $curl;
	
	public function __construct () {
		
	}
	
	public function get_url ($endpoint) {
		
		return Affilinet()->get_option('url') . "/wp-json/affwp/v1/" . $endpoint;
		
	}
	
	public function get_auth () {
		
		return Affilinet()->get_option('public_key') . ":" . Affilinet()->get_option('token');
		
	}
	
	public function call ($endpoint, $method = 'get', $data = []) {
		
		$this->open_request($endpoint, $method, $data);
		$response = $this->run_request();
		$this->close_request();
		
		return $response;
		
	}
	
	public function open_request ($endpoint, $method = 'get', $data = []) {
		
		error_log("this->get_url(endpoint)");
		error_log($this->get_url($endpoint));
		
		$headers = [];
		
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_URL, $this->get_url($endpoint));
		curl_setopt($this->curl, CURLOPT_USERPWD, $this->get_auth());
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		
		switch (strtolower($method)) {
			
			case 'post':
				$headers[] = 'Content-Type: application/json';
				curl_setopt($this->curl, CURLOPT_POST, 1);
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
				break;
				
			case 'put':
				$headers[] = 'Content-Type: application/json';
				$headers[] = 'Content-Length: ' . strlen($data);
				curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
				
			case 'delete':
				curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
				
		}

	}
	
	public function run_request () {
		
		$response = json_decode(curl_exec($this->curl), true);
		
		if ($this->logging) error_log("Affiliate Response: " . print_r($response, true));
		
		return $response;
		
	}
	
	public function close_request () {
		
		curl_close($this->curl);
		
	}
	
	public function get_referral ($id) {
		
		return $this->call("referrals/" . $id);
		
	}
	
	public function add_referral ($affiliate_id, $referral, $status = 'unpaid') {
		
		$data = [
			'affiliate_id' => $affiliate_id,
			'amount' => $referral->get('amount'),
			'currency' => $referral->get('currency'),
			'description' => $referral->get('description'),
			'reference' => $referral->get('reference'),
			'context ' => $referral->get('context'),
			'status' => $status
		]; 
		
		return $this->call("referrals", "post", $data);
		
	}

	public function delete_referral ($referral_id) {

		//since delete wont work lets just set amount = 0
		//return $this->call("referrals/" . $referral_id, "delete"); //dont work? :(

		return $this->call("referrals/" . $referral_id . "?amount=0&status=rejected", "post");

	}
	
	public function get_visit ($id) {
		
		return $this->call("visits/" . $id);
		
	}
	
	public function add_visit ($affiliate_id, $visit, $campaign = false, $referrer = false) {
		
		$data = [
			'affiliate_id' => $affiliate_id,
			'url' => $visit->get_url(),
			'referrer' => $referrer,
			'campaign' => $campaign,
			'ip' => $visit->get_ip()
		]; 
		
		return $this->call("visits", "post", $data);
		
	}
	
	public function delete_visit ($visit_id) {
		
		return $this->call("visits/" . $visit_id, "delete"); //dont work? :(
		
	}
	
	
}