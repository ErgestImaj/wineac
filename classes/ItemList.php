<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ItemList
{

    private $products;
    private $apiproducts;

    public function __construct($products, GetDataFromWineAc $apiproducts) {

        $this->products = $products;
        $this->apiproducts = $apiproducts;
    }
    public function saveProducts(){

        foreach ($this->products as $product):


            $stockDetails = $this->apiproducts->getStockDetails($product['st_irg']);

            $stock_qty = $stockDetails['lotList'][0]['pdls_stockqty'] ?? 0;

            $stock_status = 'instock';

            if ($stock_qty == 0) {
                $stock_status = 'outofstock';
            }

            /*
             * check if product exists, i can use even wc_get_product_id_by_sku
             * but maybe they add the same sku from backend
             * just to be safe
             */
	        $wp_product = get_posts(array(
		        'numberposts' => 1,
		        'meta_key'    => 'wineac_api_key',
		        'meta_value'  => $product['st_irg'],
		        'post_type' => 'product',
	        ));


            if (empty($wp_product)){
	            $product_id = wp_insert_post( array(
		            'post_author' => 1,
		            'post_title' => $product['st_iname'],
		            'post_content' => $product['sd_desc'],
		            'post_status' => 'publish',
		            'post_type' => "product",
	            ) );

            }else{
	            $product_id = $wp_product[0]->ID;

            }

            //delete old images if
            $wc_product = wc_get_product( $product_id );

            if(!empty($wc_product->get_image_id())){
                wp_delete_attachment($wc_product->get_image_id(),true);
            }

             if (is_array($wc_product->get_gallery_image_ids())){
                 foreach ($wc_product->get_gallery_image_ids() as $id){
                     wp_delete_attachment($id,true);
                 }
             }


            // set product is simple/variable/grouped
            wp_set_object_terms( $product_id, 'simple', 'product_type' );

            wp_set_object_terms( $product_id, $this->getProductCategoryIDByName($product['sttp_name']), 'product_cat' );

            //save featured image and gallery
            $flag = 0;
            if (is_array($product['photoURLs']) && !empty($product['photoURLs'])){
            	foreach ($product['photoURLs'] as $image){
            		if ($flag == 0){
			            $this->apiproducts->getStockPhoto($image, $product_id);
		            }
		            else{
			            $this->apiproducts->getStockPhoto($image, $product_id,1);
		            }
		            ++$flag;
	            }
            }

			//Product details tab
	        $details  = '<div class="product-details-main"><ul class="winec_product_details">';
            if (!empty($product['stbd_name']) && $product['stbd_name'] != 'Unknown')
	            $details .= '<li><span class="winec-key wine-name">'.__('Wine Name').'</span><span class="winec-dettails">'.$product['stbd_name'].'</span></li>';

            if (!empty($product['st_vintage']) && $product['st_vintage'] != 'Unknown')
	            $details .= '<li><span class="winec-key vintage">'.__('Vintage').'</span><span class="winec-dettails">'.$product['st_vintage'].'</span></li>';

            if (!empty($product['storg_name']) && $product['storg_name'] != 'Unknown')
	            $details .= '<li><span class="winec-key region">'.__('Region').'</span><span class="winec-dettails">'.$product['storg_name'].'</span></li>';

            if (!empty($product['st_appellation']) && $product['st_appellation'] != 'Unknown')
	            $details .= '<li><span class="winec-key appellation">'.__('Appellation').'</span><span class="winec-dettails">'.$product['st_appellation'].'</span></li>';

            if (!empty($product['st_class']) && $product['st_class'] != 'Unknown')
	            $details .= '<li><span class="winec-key classification">'.__('Classification').'</span><span class="winec-dettails">'.$product['st_class'].'</span></li>';

            if (!empty($product['st_core']) && $product['st_core'] != 'Unknown')
	            $details .= '<li><span class="winec-key score">'.__('Score').'</span><span class="winec-dettails">'.$product['st_core'].'</span></li>';

            if (!empty($product['st_maturity']) && $product['st_maturity'] != 'Unknown')
	            $details .= '<li><span class="winec-key maturaty">'.__('Maturity').'</span><span class="winec-dettails">'.$product['st_maturity'].'</span></li>';

            if (!empty($product['st_msize1']) && $product['st_msize1'] != 'Unknown')
	            $details .= '<li><span class="winec-key packing">'.__('Packing').'</span><span class="winec-dettails">'.$product['st_msize1'].'x'.$product['st_msize2'].'ml</span></li>';

	        $details .= '</ul></div>';

            $metas = array(
                '_visibility' => 'visible',
                '_stock_status' => $stock_status,
               // 'total_sales' => '0',
                '_downloadable' => 'no',
                //'_virtual' => 'no',
                '_regular_price' => (int)$product['st_standardprice'],
                '_sale_price' => '',
                '_purchase_note' => '',
                '_featured' => 'no',
                '_weight' => '',
                '_length' => '',
                '_width' => '',
                '_height' => '',
                '_sku' => $product['st_irg'],
                '_product_attributes' => array(),
                '_sale_price_dates_from' => '',
                '_sale_price_dates_to' => '',
                '_price' => '',
                '_sold_individually' => '',
                '_manage_stock' => 'yes',
                '_backorders' => 'no',
	            'wineac_api_key'=>$product['st_irg'],
	            'wineac_product_details'=>$details,
            );
            foreach ($metas as $key => $value) {
                update_post_meta($product_id, $key, $value);
            }
	        //Update a product's stock amount.
	        wc_update_product_stock($product_id,$stock_qty,'set');



        endforeach;
    }

    public function getProductCategoryIDByName($prod_cat){

        if(!term_exists($prod_cat, 'product_cat')){

            $term = wp_insert_term($prod_cat, 'product_cat');

            if ( is_wp_error( $term ) ) {
               return $term->error_data['term_exists'] ?? null;
            }
              return  $term['term_id'];

        }

        $term_s = get_term_by( 'name', $prod_cat, 'product_cat' );

        return $term_s->term_id ?? null;


    }


}