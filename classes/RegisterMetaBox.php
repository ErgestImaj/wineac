<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class RegisterMetaBox {

	public function __construct() {
		add_action( 'add_meta_boxes', [$this,'register_details_tab'] );
		add_action( 'save_post', [$this,'save_product_tab_content'] );
	}
	public function register_details_tab(){
		add_meta_box(
			'register_details_tab',
			__('Product Details','wineac'),
			[$this,'details_tab_form'],
			'product',
			'normal',
			'high'
		);
	}
	public function details_tab_form(){
		// $post is already set, and contains an object: the WordPress post
		global $post;

		$wineac_product_details = get_post_meta( get_the_ID(), 'wineac_product_details', true );

		wp_editor($wineac_product_details, 'wineac_product_details',[
			'wineac_product_details'=> 'wineac_product_details',
			'tinymce' => true,
			'editor_height' => 100,
		]);
	}

	public  function save_product_tab_content( $post_id )
	{

		// Make sure your data is set before trying to save it
		if( isset( $_POST['wineac_product_details'] ) )
			update_post_meta( $post_id, 'wineac_product_details',  $_POST['wineac_product_details'] );

	}
}