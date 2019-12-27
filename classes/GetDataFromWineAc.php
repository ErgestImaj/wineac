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

    const DIRECTORY_SEPARATOR = '/';
    const DIRECTORY_IMAGES ='temp';
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
	 * https://devnetwork.io/add-woocommerce-product-programmatically/
	 */
    public function getStockPhoto($image_url){

        $directory_path = WINEAC_DIR_PATH.self::DIRECTORY_IMAGES;
        $file_path = WINEAC_DIR_PATH.self::DIRECTORY_IMAGES.self::DIRECTORY_SEPARATOR.self::IMAGE_NAME;

        if (!file_exists($directory_path)) {
            mkdir($directory_path, 0777, true);
        }

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $html = file_get_contents($image_url, false, stream_context_create($arrContextOptions));
        preg_match_all( '|<img.*? src=[\'"](.*?)[\'"].*?>|i',$html, $matches );
        echo $html;
        die();
       // file_put_contents( $file_path, $matches );


     //   die();

        $fp =fopen($file_path , 'wb');

        $ch = curl_init($image_url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //avoid SSL issues, we need to fetch from https
        curl_setopt($ch,  CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch,  CURLOPT_SSL_VERIFYPEER, 0);
       $results =  curl_exec($ch);
//        var_dump( curl_exec($ch));
//        die();
        curl_close($ch);
        fclose($fp);
        echo $results;
        die();
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

    public function execute($ch,$auth = false){

    	if ($auth){
		    curl_setopt($ch, CURLOPT_USERPWD, $this->user . ":" . $this->pass);
		    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    }


	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	    //avoid SSL issues, we need to fetch from https
	    curl_setopt($ch,  CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch,  CURLOPT_SSL_VERIFYPEER, 0);

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