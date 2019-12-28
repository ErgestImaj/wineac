<?php


class ItemList
{

    private $products;
    private $apiproducts;

    public function __construct($products, GetDataFromWineAc $apiproducts) {

        $this->products = $products;
        $this->apiproducts = $apiproducts;
    }
    public function saveProducts(){
        $products = $this->readProducts();
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

            //Product details tab
	        $details  = '<div class="table-details"><table class="winec_product_details"><tbody>';
	        $details .= '<tr><td>'.__('Wine Name').'</td><td>'.$product['stbd_name'].'</td></tr>';
	        $details .= '<tr><td>'.__('Vintage').'</td><td>'.$product['st_vintage'].'</td></tr>';
	        $details .= '<tr><td>'.__('Region').'</td><td>'.$product['storg_name'].'</td></tr>';
	        $details .= '<tr><td>'.__('Appellation').'</td><td>'.$product['st_appellation'].'</td></tr>';
	        $details .= '<tr><td>'.__('Classification').'</td><td>'.$product['st_class'].'</td></tr>';
	        $details .= '<tr><td>'.__('Score').'</td><td>'.$product['st_core'].'</td></tr>';
	        $details .= '<tr><td>'.__('Maturity').'</td><td>'.$product['st_maturity'].'</td></tr>';
	        $details .= '<tr><td>'.__('Packing').'</td><td>'.$product['st_msize1'].'x'.$product['st_msize2'].'ml</td></tr>';
            $details .= '</tbody></table></div>';

	        update_post_meta($product_id,'wineac_product_details',$details);

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