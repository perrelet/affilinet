<?php

namespace Affilinet;

//https://docs.woocommerce.com/document/subscriptions/develop/action-reference/

class Woocommerce_Subscriptions extends Integration {
	
	public function __construct () {
		
		add_action('woocommerce_subscription_payment_complete', [$this, 'subscription_payment_complete'], 10, 1);
		//add_action('woocommerce_subscription_renewal_payment_complete', [$this, 'subscription_payment_complete'], -1, 2);
		add_action('affilinet-payment-description', 			[$this, 'payment_description'], 10, 2);
		
		add_action('init', [$this, 'init']);
		
	}
	
	public function init () {
		
		//if (get_current_user_id() != 2) return;
		
		//dprint(wcs_order_contains_subscription(275, 'any') ? "Y" : "N");
		//dprint(wcs_get_subscriptions_for_order(275, ['order_type' => ['any']]));
		

		/*$subscription = wcs_get_subscription(177);
		$parent_order = wc_get_order($subscription->get_parent_id());
		
		dprint($parent_order->get_meta('_affiliate_id')); */

		
		
	}
	
	public function subscription_payment_complete ($subscription) {
		
		$last_order = $subscription->get_last_order('all', 'any');
		$last_order_id = $last_order->get_id();
		
		$parent_order_id = $subscription->get_parent_id();
		$parent_order = wc_get_order($parent_order_id);
		
		if ($last_order_id === $parent_order_id) {
			
			//New Subscription Payment:
			
			$affiliate_id = Affilinet()->get_visit()->get_affiliate_id();
			
			error_log("New Subscription Payment");
			error_log("Subscription: " . $subscription->get_id());
			error_log("Affiliate_id: " . $affiliate_id);

			//if ($affiliate_id) {
			//	$subscription->add_meta_data('_affiliate_id', $affiliate_id);
			//}
			
		} else {
			
			//Renewal Payment:

			//$affiliate_id = $subscription->get_meta('_affiliate_id');
			$affiliate_id = $parent_order->get_meta('_affiliate_id');
			
			error_log("Renewal Payment");
			error_log("Subscription: " . $subscription->get_id());
			error_log("Affiliate_id: " . $affiliate_id);
		
			if ($affiliate_id) {
				
				error_log("Affilinet()->get_visit()->set_affiliate");
				Affilinet()->get_visit()->set_affiliate($affiliate_id);
				
				//$referral = Affilinet()->get_integration('woocommerce')->create_referral(/* $order_id */, $affiliate_id);
			
			}
			
		}
		
	}
	
	//public function subscription_renewal_payment_complete ($wc_subscription, $last_wc_order) {
		

		
		//Fires on 
		
		/* $last_wc_order = $subscription->get_last_order('all');
		$last_order = new Order($last_wc_order);
		
		$affiliate_id = $last_order->get_affiliate_id();
		if (!$affiliate_id) return;
		
		$referral = Affilinet()->get_integration('woocommerce')->create_referral($order_id, $affiliate_id); */
		
	//}
	
	//public function subscription_payment_complete ($subscription) {
		
		//Might not be required - perhaps no referral because no affiliate on sub!...
		//$order = $subscription->get_parent();
		//$order_id = $order->get_id();
		//
		//Affilinet()->get_integration('woocommerce')->payment_complete($order_id);
		
	//}
	
	public function payment_description ($description, $order_id) {
		
		$subscriptions = wcs_get_subscriptions_for_order($order_id, ['order_type' => ['any']]);
		if (!$subscriptions) return $description;
		
		$description = [];
		foreach ($subscriptions as $subscription) {
			
			$items = $subscription->get_items();
			
			foreach ($items as $item) {
				
				$item_description = $item->get_quantity() . " x " . $item->get_name();
				$item_description .= " Subscription " . $subscription->get_id() . " (Payment #" . $subscription->get_payment_count() . ")";
				
				$description[] = $item_description;
			}
			
		}
		
		$description = implode(", ", $description);
		
		return $description;
		
	}
	
}