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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        //avoid SSL issues, we need to fetch from https
        curl_setopt($ch,  CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch,  CURLOPT_SSL_VERIFYPEER, 0);


	    if (!$result =curl_exec($ch)) {
		    error_log(print_r('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch),true));
	    }

        // Close cURL session handle
        curl_close($ch);

       return $result;

    }
	/*
	* Get storage items of particular user Basic Auth required
	*/
    public function getStorages(){

    }
	/*
	 * Get stock item photo The URL can obtain from /stocks/itemList/photoURLs No Auth required
	 */
    public function getStockPhoto(){

    }
    /*
     * Get stock item detail, include lot information.
     */
    public function getStockDetails(){

    }
}