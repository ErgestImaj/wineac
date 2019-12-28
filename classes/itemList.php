<?php


class itemList
{

    private $products;
    private $apiproducts;

    public function __construct($products, GetDataFromWineAc $apiproducts) {

        $this->products = $products;
        $this->apiproducts = $apiproducts;
    }
    public function saveProducts(){
        $products =$this->readProducts();
        foreach ( $products as $product):

            $stockDetails = $this->apiproducts->getStockDetails($product['st_irg']);

            $stock_qty = $stockDetails['lotList'][0]['pdls_stockqty'] ?? 0;

            $stock_status = 'instock';

            if ($stock_qty == 0) {
                $stock_status = 'outofstock';
            }

            $product_id = wp_insert_post( array(
                'post_author' => 1,
                'post_title' => $product['st_iname'],
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => "product",
            ) );

            // set product is simple/variable/grouped
            wp_set_object_terms( $product_id, 'simple', 'product_type' );

            wp_set_object_terms( $product_id, $this->getProductCategoryIDByName($product['sttp_name']), 'product_cat' );
            if (is_array($product['photoURLs']) && !empty($product['photoURLs'])){
            	foreach ($product['photoURLs'] as $image){
		            $this->apiproducts->getStockPhoto($image, $product_id);
	            }
            }


            $metas = array(
                '_visibility' => 'visible',
                '_stock_status' => $stock_status,
                'total_sales' => '0',
                '_downloadable' => 'no',
                '_virtual' => 'yes',
                '_regular_price' => '',
                '_sale_price' => '',
                '_purchase_note' => '',
                '_featured' => 'no',
                '_weight' => '',
                '_length' => '',
                '_width' => '',
                '_height' => '',
                '_sku' => $product['st_icode'],
                '_product_attributes' => array(),
                '_sale_price_dates_from' => '',
                '_sale_price_dates_to' => '',
                '_price' => '',
                '_sold_individually' => '',
                '_manage_stock' => 'no',
                '_backorders' => 'no',
                '_stock' =>  $stock_qty
            );
            foreach ($metas as $key => $value) {
                update_post_meta($product_id, $key, $value);
            }


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
    /**
     * @return Generator
     */
    public function readProducts(){

        foreach ($this->products as $product)
            yield $product;

     }



}