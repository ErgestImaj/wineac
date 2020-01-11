<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WoocommerceProductDetailsTab {

	public function __construct() {
        // create new product tab
		add_filter( 'woocommerce_product_tabs', [$this,'woo_new_product_tab'] );

		// reorder product tabs
		add_filter( 'woocommerce_product_tabs', [$this,'woo_reorder_tabs'], 98 );

        // hide details tab if empty
        add_filter( 'woocommerce_product_tabs', [$this,'woo_remove_empty_tabs'], 20, 1 );

        // check if product is purchasable
		add_filter( 'woocommerce_is_purchasable', [$this,'wc_product_is_purchasable'], 10, 2 );
	}

	/**
	 * Add a custom product data tab
	 */

	public function woo_new_product_tab( $tabs ) {

		// Adds the new tab

		$tabs['product_tab_data'] = array(
			'title' 	=> __( 'Details', 'wineac' ),
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

     public  function woo_remove_empty_tabs( $tabs ) {
         global $product;
            if ( ! empty( $tabs ) ) {
                foreach ( $tabs as $title => $tab ) {

                    if ( strtolower($tab['title'] ) == 'details' ) {

                        $wineac_product_details = get_post_meta( $product->get_id(), 'wineac_product_details', true );

                        if (empty(  $wineac_product_details)){
                            unset($tabs[$title]);
                        }

                    }
                }
            }
            return $tabs;
        }

      public function wc_product_is_purchasable( $purchasable, $product ){
	      if( $product->get_price() == 0 )
		      $purchasable = false;
	      return $purchasable;
      }


}