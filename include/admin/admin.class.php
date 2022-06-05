<?php

namespace Affilinet;

class Admin {
	
	public function __construct() {
		
		add_action('admin_menu', [$this, 'register_menu']);
		add_action('admin_init', [$this, 'settings']);
		add_filter('affilinet_sanitize', [$this, 'sanitize_url_field'], 10, 2);
		
	}

	public function register_menu() {
		
		add_options_page( 
			__('Affiliates', AFFILINET_TEXT_DOMAIN), 
			__('Affiliates', AFFILINET_TEXT_DOMAIN), 
			'manage_options', 
			AFFILINET_OPTIONS_SLUG, 
			[$this, 'admin_page']
		);	
		
	}

	public function admin_page() {

		echo "<div class='wrap'>";
		
			screen_icon('plugins');
			
			echo "<h2>Affiliate Connection Settings</h2>";
			echo "<form action='options.php' method='POST'>";
			
				settings_fields(AFFILINET_OPTIONS_SLUG);
	            do_settings_sections(AFFILINET_OPTIONS_SLUG);
				submit_button();
				
			echo "</form>";
		echo "</div>";
		
	}

	public function default_options() {
		
		$defaults = array(
			'campaign'			=> get_bloginfo('name'),
			'rate'				=> 10,
			'cookie_expiration'	=> '30',
			'referral_variable' => 'ref',
			'url'               => '',
			'public_key'        => '',
			'token'             => '',
			'nomination'        => 'How did you discover this?',
		);
		
		return apply_filters('affilinet_default_options', $defaults);
		
	}

	public function settings() {

		if (false == get_option(AFFILINET_OPTIONS_SLUG)) add_option(AFFILINET_OPTIONS_SLUG, $this->default_options());

		add_settings_section(
			'affilinet_section',
			'',
			'',
			AFFILINET_OPTIONS_SLUG
		);
		
		// Campaign Name
		add_settings_field(	
			'Campaign',						
			__('Campaign', AFFILINET_OPTIONS_SLUG),							
			[$this, 'callback_input'],	
			AFFILINET_OPTIONS_SLUG,	
			'affilinet_section',
			[ 
				'name'        => 'campaign', 
				'id'          => 'campaign', 
				'description' => __('Campaign name for this website.', AFFILINET_TEXT_DOMAIN)
			]		
		);
		
		// Rate
		add_settings_field(	
			'Referral Rate',						
			__('Referral Rate', AFFILINET_OPTIONS_SLUG),							
			[$this, 'callback_input'],	
			AFFILINET_OPTIONS_SLUG,	
			'affilinet_section',
			[ 
				'name'        => 'rate', 
				'id'          => 'rate', 
				'description' => __('The default referral rate. (%)', AFFILINET_TEXT_DOMAIN),
				'type'		  => 'number'
			]			
		);
		
		// URL to search for
		add_settings_field(	
			'Site URL',						
			__('Site URL', AFFILINET_OPTIONS_SLUG),							
			[$this, 'callback_input'],	
			AFFILINET_OPTIONS_SLUG,	
			'affilinet_section',
			[ 
				'name'        => 'url', 
				'id'          => 'url', 
				'description' => __('The site URL where AffiliateWP is installed.', AFFILINET_TEXT_DOMAIN)
			]		
		);

		// Referral Variable
		add_settings_field(	
			'Referral Variable',						
			__('Referral Variable', AFFILINET_OPTIONS_SLUG),							
			[$this, 'callback_input'],	
			AFFILINET_OPTIONS_SLUG,	
			'affilinet_section',
			[ 
				'name'        => 'referral_variable', 
				'id'          => 'referral-variable', 
				'description' => __( 'The referral variable you have set in AffiliateWP at the site URL above. It must match exactly.', AFFILINET_TEXT_DOMAIN )
			]		
		);

		// Cookie Expiration
		add_settings_field(	
			'Cookie Expiration',						
			__('Cookie Expiration', AFFILINET_OPTIONS_SLUG),							
			[$this, 'callback_input'],	
			AFFILINET_OPTIONS_SLUG,	
			'affilinet_section',
			[ 
				'name'        => 'cookie_expiration', 
				'id'          => 'cookie-expiration', 
				'description' => __('How many days should the referral tracking cookie be valid for?', AFFILINET_TEXT_DOMAIN),
				'type'		  => 'number'
			]			
		);
		
		// Public Key
		add_settings_field(	
			'Public Key',						
			__('Public Key', AFFILINET_OPTIONS_SLUG),							
			[$this, 'callback_input'],	
			AFFILINET_OPTIONS_SLUG,	
			'affilinet_section',
			[ 
				'name'        => 'public_key', 
				'id'          => 'public-key', 
				'description' => __('Public key for AffilateWP API', AFFILINET_TEXT_DOMAIN),
			]			
		);
		
		// Token
		add_settings_field(	
			'Token',						
			__('Token', AFFILINET_OPTIONS_SLUG),							
			[$this, 'callback_input'],	
			AFFILINET_OPTIONS_SLUG,	
			'affilinet_section',
			[ 
				'name'        => 'token', 
				'id'          => 'token', 
				'description' => __('Token for AffilateWP API', AFFILINET_TEXT_DOMAIN),
			]			
		);

		// URL to search for
		add_settings_field(	
			'Nomination Question',						
			__('Nomination Question', AFFILINET_OPTIONS_SLUG),							
			[$this, 'callback_input'],	
			AFFILINET_OPTIONS_SLUG,	
			'affilinet_section',
			[ 
				'name'        => 'nomination', 
				'id'          => 'nomination', 
				'description' => __('Question for customers to nominate an affiliate during checkout process.', AFFILINET_TEXT_DOMAIN)
			]		
		);
		
		register_setting(
			AFFILINET_OPTIONS_SLUG,
			AFFILINET_OPTIONS_SLUG,
			[$this, 'sanitize']
		);

	}

	public function callback_input ($args) {
		
		$options = get_option(AFFILINET_OPTIONS_SLUG);
		$value = isset($options[$args['name']]) ? $options[$args['name']] : '';
		$type = (isset($args['type']) && isset($options[$args['type']])) ? $options[$args['type']] : 'text';
		
		echo "<input type='{$type}' id='{$args['id']}' name='" . AFFILINET_OPTIONS_SLUG . "[{$args['name']}]' value='{$value}' />";
		
		if (isset($args['description'])) echo "<p class='description'>{$args['description']}</p>";
		
	}

	public function sanitize ($input) {

		$output = [];
		
		if ($input) {
			foreach ($input as $key => $value) {
				
				if (isset($input[$key])) $output[$key] = strip_tags(stripslashes($input[$key]));
				
			}
		}
		
		return apply_filters('affilinet_sanitize', $output, $input);

	}


	public function sanitize_url_field( $output, $input ) {

		// remove the trailing slash if present and sanitize URL
		if (isset($input['url'])) $output['url'] = untrailingslashit(esc_url_raw($output['url']));

		return $output;

	}
	
}