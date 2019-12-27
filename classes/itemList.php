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

        foreach ($this->readProducts() as $product):

            $stockDetails = $this->apiproducts->getStockDetails($product['st_irg']);

            $stock_qty = $stockDetails['lotList']['pdls_stockqty'] ?? 0;

            $stock_status = 'instock';

            if ($stock_qty == 0) {
                $stock_status = 'outofstock';
            }
            $cats = get_terms('product_cat', array('hide_empty' => 0, 'orderby' => 'ASC',  'parent' =>0));
            print_r($cats);
            die();
            var_dump($this->getProductCategoryIDByName($product['sttp_name']));
            die();
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

          die();
        endforeach;
    }

    public function getProductCategoryIDByName($prod_cat){

      var_dump(taxonomy_exists('product_type'));
        if(!term_exists($prod_cat, 'product_cat')){

            $term = wp_insert_term($prod_cat, 'product_cat');
            var_dump( $term);
            if ( is_wp_error( $term ) ) {
               return $term->error_data['term_exists'] ?? null;
            }
              return  $term['term_id'];

        }
         var_dump(1);
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