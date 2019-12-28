<?php


class WoocommerceProductDetailsTab {

	public function __construct() {

		add_filter( 'woocommerce_product_tabs', [$this,'woo_new_product_tab'] );

		add_filter( 'woocommerce_product_tabs', [$this,'woo_reorder_tabs'], 98 );
	}

	/**
	 * Add a custom product data tab
	 */

	public function woo_new_product_tab( $tabs ) {

		// Adds the new tab

		$tabs['product_tab_data'] = array(
			'title' 	=> __( 'Details', 'woocommerce' ),
			'priority' 	=> 50,
			'callback' 	=>[$this, 'woo_new_product_tab_content']
		);

		return $tabs;

	}
	public function woo_new_product_tab_content() {

		global $product;

		$wineac_product_details = get_post_meta( $product->get_id(), 'wineac_product_details', true );

		echo $wineac_product_details;

	}
	/**
	 * Reorder product data tabs
	 */

	public function woo_reorder_tabs( $tabs ) {

		$tabs['product_tab_data']['priority'] = 5;			// Details first
		$tabs['additional_information']['priority'] =10;	// Additional information third
		$tabs['reviews']['priority'] = 15;			        // Reviews first
		$tabs['description']['priority'] = 15;		    	// Description second

		return $tabs;
	}

}