<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WoocommerceProductDetailsTab {

	public function __construct() {

		add_filter( 'woocommerce_product_tabs', [$this,'woo_new_product_tab'] );

		add_filter( 'woocommerce_product_tabs', [$this,'woo_reorder_tabs'], 98 );

        add_filter( 'woocommerce_product_tabs', [$this,'woo_remove_empty_tabs'], 20, 1 );

        // Displayed formatted regular price + sale price
        add_filter( 'woocommerce_product_get_regular_price', [$this,'custom_dynamic_regular_price'], 10, 2 );
        add_filter( 'woocommerce_product_variation_get_regular_price', [$this,'custom_dynamic_regular_price'], 10, 2 );
        add_filter( 'woocommerce_product_get_sale_price', [$this,'custom_dynamic_sale_price'], 10, 2 );
        add_filter( 'woocommerce_product_variation_get_sale_price',[$this, 'custom_dynamic_sale_price'], 10, 2 );
        add_filter( 'woocommerce_get_price_html', [$this,'custom_dynamic_sale_price_html'], 20, 2 );
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
    // Generating dynamically the product "regular price"

    public function custom_dynamic_regular_price( $regular_price, $product ) {
        if( empty($regular_price) || $regular_price == 0 )
            return $product->get_price();
        else
            return $regular_price;
    }


// Generating dynamically the product "sale price"

    public function custom_dynamic_sale_price( $sale_price, $product ) {

        if( empty($sale_price) || $sale_price == 0 )
            return $product->get_regular_price();
        else
            return $sale_price;
    }


    public function custom_dynamic_sale_price_html( $price_html, $product ) {
        if( $product->is_type('variable') ) return $price_html;

        $price_html = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display(  $product, array( 'price' => $product->get_sale_price() ) ) ) . $product->get_price_suffix();

        return $price_html;
    }
}