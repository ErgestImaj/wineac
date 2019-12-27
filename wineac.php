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


    private function __construct() {
        $this->version= '1.0';

        spl_autoload_register( array( $this, 'load' ) );

        $this->initialize_hooks();
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
     * @access    private
     */
    private function initialize_hooks() {
     $products = new GetDataFromWineAc;
    // $stocks = $products->getStocks();
     $stockDetails = $products->getStockDetails(835);
     error_log(print_r( $stockDetails,true));
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

