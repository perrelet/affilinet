<?php

namespace Affilinet;

class Woocommerce extends Integration {
	
	public function __construct () {
		
		add_action('woocommerce_payment_complete', [$this, 'payment_complete'], 10, 1);
		add_action('add_meta_boxes', [$this, 'admin_order_meta']);
		add_action('init', [$this, 'maybe_refer']);
		add_action('init', [$this, 'maybe_unrefer']);

		//

		add_action('woocommerce_after_order_notes', [$this, 'checkout_fields']);
		add_action('woocommerce_checkout_update_order_meta', [$this, 'checkout_update_order_meta']);

		//

		add_action('init', [$this, 'nnn']);


		

	}

	public function nnn () {
		if (isset($_GET["nnn"])) {
		
			
			$orders = wc_get_orders( [
				'numberposts' => -1,
				'status' => ['completed', 'processing']
				] );

			echo "<table>";



			foreach ($orders as $order) {
				
				echo "<tr>";

				echo "<td>" . $order->get_id() . "</td>";
				echo "<td>" . get_post_meta( $order->get_id(), '_customer_ip_address', true ) . "</td>";
				
				echo "</tr>";

				//echo $order->get_post_meta("ID");
			
			}

			echo "</table>";
	
		}
	}
	
	public function payment_complete ($order_id) {
		
		//if (WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment) return;		
		
		//update_post_meta( $subscription_id, '_order_key', wcs_generate_order_key() );
		
		error_log("PAYMENT COMPLETE");
		error_log("AFFILIATE: " . print_r(Affilinet()->get_visit()->get_affiliate(), true));
		error_log("ORDER ID: " . $order_id);
		
		if (!Affilinet()->get_visit()->has_affiliate()) return;
		$affiliate_id = Affilinet()->get_visit()->get_affiliate()->get_id();
		
		//$referral = $this->create_referral($order_id, $affiliate_id);
		//$referral->send($affiliate_id);

		$this->new_referral($order_id, $affiliate_id);
		
	}
	
	public function new_referral ($order_id, $affiliate_id) {

		$referral = $this->create_referral($order_id, $affiliate_id);
		$response = $referral->send($affiliate_id);

		if ($response && is_array($response) && isset($response['referral_id'])) {

			$referral_id = $response['referral_id'];

			$order = wc_get_order($order_id);
			error_log("AFFILIATE ID _affiliate_id: " . $affiliate_id);
			$order->update_meta_data('_affiliate_id', $affiliate_id);
			$order->update_meta_data('_referral_id', $referral_id);
			$order->save();

		}

		return $response;

	}

	public function delete_referral ($order_id) {

		$order = wc_get_order($order_id);
		$referral_id = $order->get_meta('_referral_id');
		if (!$referral_id) return;

		$remote = new Remote();
		$response = $remote->delete_referral($referral_id);

		//check

		$order->update_meta_data('_referral_id', null);
		$order->update_meta_data('_affiliate_id', null);
		$order->save();

		return $response;
	}

	public function create_referral ($order_id, $affiliate_id) {
		
		$order = wc_get_order($order_id);
		$items = $order->get_items();
		
		$description = [];
		foreach ($items as $item) $description[] = $item->get_quantity() . " x " . $item->get_name();
		
		$description = implode(", ", $description);
		$description = apply_filters('affilinet_payment_description', $description, $order_id);
		$description = Affilinet()->get_option('campaign') . ": " . $description;

		//SUBSCRIPTIONS

		$subscriptions_subtotal = 0;

		if (is_callable('wcs_get_subscriptions_for_order')) {

			$subscriptions = wcs_get_subscriptions_for_order($order_id);
			if ($subscriptions) foreach ($subscriptions as $subscription) {

				$related_order_ids = $subscription->get_related_orders();

				if ($related_order_ids) foreach ($related_order_ids as $related_order_id) {

					$related_order = wc_get_order($related_order_id);
					$status = $related_order->get_status();
					if ($status != 'completed') continue;

					$subscriptions_subtotal += $related_order->get_total();

				}

			} 

		}

		// END SUBSCRIPTIONS

		$subtotal = $subscriptions_subtotal ? $subscriptions_subtotal : $order->get_total();

		/* echo "<pre>";
		print_r($subtotal);
		die;  */

		//

		return new Referral(
			$subtotal,
			$order->get_currency(),
			$order_id,
			$description
		);		
		
	}

	public function admin_order_meta () {
				   
		add_meta_box(
		   'affilinet_referral_meta',   			
		   'Affilinet Referral',      			
		   [$this, 'render_referral_box'],  
		   'shop_order',                 		
		   'normal',                  			
		   'low'                     			
		);
		
	}

	public function render_referral_box () {

		$order_id = get_the_ID();
		$order = wc_get_order($order_id);

		if (!in_array($order->get_status(), ['completed', 'processing'])) return;

		$html = "<div class='affilinet-meta-box affilinet-referral-box'>";

			$nomination = get_post_meta($order_id, "affilinet_referral_nomination", true);
			if ($nomination) echo "<div>The customer noted during the checkout that they heard about this via: '{$nomination}'</div>";

			$html .= "<input type='hidden' value='{$order_id}' name='order_id'>";
			

			$html .= "<div class='affilinet-meta-box-row'>";
			$html .= "<a href='" . trailingslashit(Affilinet()->get_option('url')) . "wp-admin/admin.php?page=affiliate-wp-affiliates' target='_blank'>Browse Affiliate Directory</a>";
			$html .= "</div>";

			if ($order->get_meta('_affiliate_id')) {

				if ($order->get_meta('_referral_id')) {

					$html .= wp_nonce_field(AFFILINET_PATH, 'affilinet_unrefer_nonce', true, false);

					$html .= "This order has already been awarded to affiliate " . $order->get_meta('_affiliate_id');

					$html .= "<div class='affilinet-meta-box-row'>";
					$html .= "<input type='checkbox' value='1' name='referral_confirm'>";
					$html .= "<label class='confirm' for='referral_confirm'>Please check to confirm action.</label>";
					$html .= "</div>";

					$html .= "<div class='affilinet-meta-box-row'>";
					$html .= "<input type='submit' value='Delete Referral' name='affilinet_delete_referral'>";
					$html .= "</div>";

				}

			} else {

				$html .= wp_nonce_field(AFFILINET_PATH, 'affilinet_refer_nonce', true, false);

				$html .= "<div class='affilinet-meta-box-row'>";
				$html .= "<label for='affiliate_id'>Affiliate ID:</label>";
				$html .= "<input type='number' name='affiliate_id' value='' min='1'>";
				$html .= "</div>";

				$html .= "<div class='affilinet-meta-box-row'>";
				$html .= "<input type='checkbox' value='1' name='referral_confirm'>";
				$html .= "<label class='confirm' for='referral_confirm'>I confirm that this referral is valid. (This action is <u>not</u> reversible.)</label>";
				$html .= "</div>";

				$html .= "<div class='affilinet-meta-box-row'>";
				$html .= "<input type='submit' value='Send Referral' name='affilinet_add_referral'>";
				$html .= "</div>";

			}

		$html .= "</div>";

		echo $html;

	}

	public function maybe_refer () {

		if (!isset($_POST['affilinet_refer_nonce']) || !wp_verify_nonce($_POST['affilinet_refer_nonce'], AFFILINET_PATH)) return;
		if (!isset($_POST['order_id'])) return;
		if (!isset($_POST['affiliate_id'])) return;
		if (!isset($_POST['referral_confirm'])) return;
		
		$affiliate_id = $_POST['affiliate_id'];
		$order_id = $_POST['order_id'];

		//$referral = $this->create_referral($order_id, $affiliate_id);
		//$referral->send($affiliate_id);

		$this->new_referral($order_id, $affiliate_id);

	}

	public function maybe_unrefer () {

		if (!isset($_POST['affilinet_unrefer_nonce']) || !wp_verify_nonce($_POST['affilinet_unrefer_nonce'], AFFILINET_PATH)) return;
		if (!isset($_POST['order_id'])) return;
		if (!isset($_POST['referral_confirm'])) return;

		$order_id = $_POST['order_id'];

		$this->delete_referral($order_id);

	}

	public function checkout_fields ($checkout) {

		//echo '<div id="customise_checkout_field"><h2>' . __('Heading') . '</h2>';

		woocommerce_form_field('affilinet_referral_nomination',
		[
		  'type' 		=> 'text',
		  'class' 		=> ['my-field-class form-row-wide'],
		  'label' 		=> __(Affilinet()->get_option('nomination')) ,
		  'placeholder' => __('') ,
		  'required' 	=> false,
		],
		$checkout->get_value('affilinet_referral_nomination'));

		//echo '</div>';

	}

	public function checkout_update_order_meta ($order_id) {

		if (!empty($_POST['affilinet_referral_nomination'])) {
			update_post_meta($order_id, 'affilinet_referral_nomination', sanitize_text_field($_POST['affilinet_referral_nomination']));
		}

	}

}

/* if (isset($_GET['xxx111xxx'])) {


	add_action('init', function () {
		
		$args = array(
			'post_type' => 'shop_order',
			'posts_per_page'  => -1,
			'post_status'     => 'any',

		);
	
		$posts = get_posts($args);

		foreach ($posts as $post) {

			$nomination = get_post_meta($order_id, "affilinet_referral_nomination", true);

			if ($nomination) echo $post->ID . " : " . $nomination . "<br>";
		}

	});

} */

