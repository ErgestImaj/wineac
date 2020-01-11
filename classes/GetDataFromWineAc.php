<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GetDataFromWineAc
{
    /**
     * API to get data.
     * @access    private
     */
    private $api_url = 'https://www.wineac.com:12443/wineactb/rest/wineac/stocks';

    private $user = 'apitest';

    private $pass = 'dest2019';

    const IMAGE_NAME ='image.jpg';

    /**
     * Get all stock items for sale No Auth required
     * limit - limit the number of record. 0:no limit.
     * showAllStock - Y:show all stocks, include unavailable stock. N:show available stocks only. (Default N)
     * @return json data
     *  "header": {
				    "st_irg": "Ref No",
				    "st_icode": "Item Code",
				    "st_iname": "Item Name",
				    "st_oicode": "Alt Item Code",
				    "st_photoid": "Photo Id",
				    "st_photofmt": "Photo Format",
				    "st_vintage": "Vintage",
				    "st_msize1": "Bot/Case",
				    "st_msize2": "Volume(ml)",
				    "st_extraimg": "Extra Imgs",
				    "st_cdate": "Create On",
				    "sttp_name": "Category",
				    "mt_tpname": "Type",
				    "stbd_name": "Wine Name",
				    "storg_name": "Region",
				    "st_class": "Classification",
				    "st_appellation": "Appellation",
				    "st_core": "Score",
				    "st_maturity": "Maturity"
				    },
     */
    public  function getStocks(){

        // Prepare new cURL resource
        $ch = curl_init($this->api_url.'/?limit=0&showAllStock=N');
        return  $this->execute($ch);
    }
	/*
	* Get storage items of particular user Basic Auth required
	*/
    public function getStorages(){

    }
	/*
	 * Get stock item photo The URL can obtain from /stocks/itemList/photoURLs No Auth required
	 *
	 */
    public function getStockPhoto($image_url,$product_id,$flag=0){

	    $upload_dir = wp_upload_dir(); // Set upload folder
	    $unique_file_name = wp_unique_filename( $upload_dir['path'],  self::IMAGE_NAME ); //    Generate unique name
	    $filename = basename( $unique_file_name ); // Create image file name
	    // Check folder permission and define file location
	    if( wp_mkdir_p( $upload_dir['path'] ) ) {
		    $file = $upload_dir['path'] . '/' . $filename;
	    } else {
		    $file = $upload_dir['basedir'] . '/' . $filename;
	    }
	    // Create the image file on the server
	    $this->fp = fopen($file , 'wb');

	    $this->execute(curl_init($image_url),false,true);

	    // Check image file type
	    $wp_filetype = wp_check_filetype( $filename, null );

	    // Set attachment data
	    $attachment = array(
		    'post_mime_type' => $wp_filetype['type'],
		    'post_title' => sanitize_file_name( $filename ),
		    'post_content' => '',
		    'post_status' => 'inherit'
	    );
	    // Create the attachment
	    $attach_id = wp_insert_attachment( $attachment, $file, $product_id );

	    // Check image file type
	    $wp_filetype = wp_check_filetype(  $filename, null );

	    // Include image.php
	    require_once(ABSPATH . 'wp-admin/includes/image.php');
	    // Define attachment metadata
	    $attach_data = wp_generate_attachment_metadata( $attach_id,$file );
	    // Assign metadata to attachment
	    wp_update_attachment_metadata( $attach_id, $attach_data );
	    // asign to feature image
	    if( $flag == 0){
		    // And finally assign featured image to post
		    set_post_thumbnail( $product_id, $attach_id );
	    }

	    // assign to the product gallery
	    if( $flag == 1 ) {
		    // Add gallery image to product
		    $attach_id_array = get_post_meta( $product_id, '_product_image_gallery', true );
		    $attach_id_array .= ',' . $attach_id;
		    update_post_meta( $product_id, '_product_image_gallery', $attach_id_array );
	    }

    }
    /*
     * Get stock item detail, include lot information.
     * $st_irg =>  Ref No
     */
    public function getStockDetails($st_irg){
	    // Prepare new cURL resource
	    $ch = curl_init($this->api_url.'/'.$st_irg);
	    return  $this->execute($ch,true);
    }

    public function execute($ch,$auth = false,$isFile = false){

    	if ($auth){
		    curl_setopt($ch, CURLOPT_USERPWD, $this->user . ":" . $this->pass);
		    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    }

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	    //avoid SSL issues, we need to fetch from https
	    curl_setopt($ch,  CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch,  CURLOPT_SSL_VERIFYPEER, 0);

	    if ($isFile){
		    curl_setopt($ch, CURLOPT_FILE, $this->fp);
	    }

	    try {
		    $result = curl_exec($ch);
		    return json_decode($result,true);
	    }catch (Exception $exception){
		    Logger::writeLog([
			    $exception->getMessage(),
			    'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch)
		    ]);
	    }

	    // Close cURL session handle
	    curl_close($ch);
    }
}