<?php
/**
 * Plugin Name: Wineac
 * Plugin URI: https://wineac.com
 * Description: This plugin does not have a configuration page and will run automatically with cron job. His job is to get products with API
 * Version: 1.0
 * Author: Ergest Imaj.
 * Author URI: https://wineac.com
 * Text Domain: wineac
 * Domain Path: /languages
 **/

/** Exit if accessed directly **/
if ( ! defined( 'ABSPATH' ) ) exit;

define("WINEAC_PLUGIN_URL", plugin_dir_url(__FILE__));
define("WINEAC_DIR_PATH", plugin_dir_path(__FILE__));



final class Wineac{

    /**
     * Check if class is instantiated.
     *
     * @access    private
     * @var       string
     */
    private static $instance = null;

    /**
     * Represents the current version of this plugin.
     *
     * @access    private
     * @var       string
     */
    protected $version;


    public function __construct() {
        $this->version= '1.0';

        spl_autoload_register( array( $this, 'load' ) );

	    add_filter( 'cron_schedules', [$this,'wineac_add_every_five_minutes'] );

	    add_action( 'init', [$this,'initialize_hooks'] );

    }

    /**
     * This function returns the main plugin object
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class Loader.
     * @param string $class - Class name to load.
     * @return null - Do not return anything.
     */
    public function load( $class ) {
        if ( is_readable( trailingslashit( WINEAC_DIR_PATH. '/classes' ) . $class . '.php' ) ) {
            require_once trailingslashit( WINEAC_DIR_PATH. '/classes' ) . $class . '.php';
        }
        return;
    }

    /**
     * Initializes this plugin and the dependency loader to include
     * the assets necessary for the plugin to function.
     *
     * @access   public
     */
    public function initialize_hooks() {
	    //register metabox
		 new RegisterMetaBox;
		 //Register custom woocommerce tab
		 new WoocommerceProductDetailsTab();


	    if ( ! wp_next_scheduled( 'sync_products_with_api' ) ) {
		    wp_schedule_event( time(), 'every_five_minutes', 'sync_products_with_api' );
	    }
		 add_action('sync_products_with_api', [$this,'sync_products_with_api_hook']);


    }
    /*
     * schedule event.
     */

	public function wineac_add_every_five_minutes( $schedules ) {
		$schedules['every_five_minutes'] = array(
			'interval'  => 300,
			'display'   => __( 'Every 5 Minutes')
		);
		return $schedules;
	}

    /*
     * Sync products
     * define('DISABLE_WP_CRON', true);
     * wget -O /dev/null https://hkdpl.com/wineac/wp-cron.php
     */
    public function sync_products_with_api_hook(){

	    $apiproducts = new GetDataFromWineAc;

	    $stocks = $apiproducts->getStocks();

	    if (is_array($stocks) && isset($stocks['itemList'])){
		    $items = new ItemList($stocks['itemList'],$apiproducts);
		     $items->saveProducts();
	    }
    }


    /**
     * Return the current version of plugin
     * @return string
     */
    public function getVersion(){
        return $this->version;
    }
}
Wineac::get_instance();
