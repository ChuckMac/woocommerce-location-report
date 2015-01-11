<?php
/**
 * Plugin Name: WooCommerce Sales By Location Report
 * Plugin URI: http://www.chuckmac.info
 * Description: WooCommerce report to visualize sales by location.
 * Author: Chuck Mac
 * Author URI: http://www.chuckmac.info
 * Version: 1.1
 * Text Domain: wc_location_report
 * Domain Path: /languages/
 *
 * Copyright: (c) 2014 ChuckMac Development LLC
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Location-Report
 * @author    WooThemes
 * @category  Reports
 * @copyright Copyright (c) 2014, ChuckMac Development LLC
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}


/**
 * # WooCommerce Location Report Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin adds a new section in the WooCommerce Reports -> Orders area called 'Sales by location'.
 * The report visualizes the customer purchases by location into a Choropleth map to show where the orders
 * are being placed.
 *
 * This plugin utilizes jVectorMap (http://jvectormap.com) for its map functions.
 *
 */
class WC_Location_Report {

	/** plugin version number */
	public static $version = '1.1';

	/** @var string the plugin file */
	public static $plugin_file = __FILE__;

	/** @var string the plugin file */
	public static $plugin_dir;

	
	/**
	 * Initializes the plugin
	 *
	 * @since 1.0
	 */
	public function init() {

		global $wpdb;

		self::$plugin_dir = dirname( __FILE__ );

		// Add any necessary css / scripts
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::location_report_admin_css_scripts' );

		// Add the reports layout to the WooCommerce -> Reports admin section
		add_filter( 'woocommerce_admin_reports',  __CLASS__ . '::initialize_location_admin_report', 12, 1 );

		// Add the path to the report class so WooCommerce can parse it
		add_filter( 'wc_admin_reports_path',  __CLASS__ . '::initialize_location_admin_reports_path', 12, 3 );

		// Load translation files
		add_action( 'plugins_loaded', __CLASS__ . '::load_plugin_textdomain' );

	}


	/**
	 * Add any location report javascript & css to the admin pages.  Only 
	 * add it to our specific report areas.
	 *
	 * @since 1.0
	 */
	public static function location_report_admin_css_scripts() {

		$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
		$screen = get_current_screen();

		if ( in_array( $screen->id, apply_filters( 'woocommerce_geo_reports_screen_ids', array( $wc_screen_id . '_page_wc-reports' ) ) ) && isset( $_REQUEST['report'] ) && in_array($_REQUEST['report'], apply_filters( 'woocommerce_geo_reports_report_ids', array( 'sales_by_location' ) )) ) {

			//jVector includes - needs to be done in the footer so we can localize data as part of the report generation
			wp_enqueue_script( 'jvectormap', plugins_url( '/lib/jquery-jvectormap-1.2.2.min.js', self::$plugin_file ), array( 'jquery' ), self::$version, true );
			wp_enqueue_script( 'jvectormap-world', plugins_url( '/lib/map-data/jquery-jvectormap-world-mill-en.js', self::$plugin_file ), array( 'jquery', 'jvectormap' ), self::$version, true );

			//jVector css
			wp_enqueue_style( 'jvectormap', plugins_url( '/lib/jquery-jvectormap-1.2.2.css', self::$plugin_file ), array( 'woocommerce_admin_styles' ), self::$version );

		}

	}


	/**
	 * Add our location report to the WooCommerce order reports array.
	 *
	 * @param array Array of All Report types & their labels
	 * @return array Array of All Report types & their labels, including the 'Sales by location' report.
	 * @since 1.0
	 */
	public static function initialize_location_admin_report ( $report ) {

		$report['orders']['reports']['sales_by_location'] = array (
															'title'       => __( 'Sales by location', 'woocommerce-location-report' ),
															'description' => '',
															'hide_title'  => true,
															'callback'    => array( 'WC_Admin_Reports', 'get_report' )
															);

		return $report;
		
	}


	/**
	 * If we hit one of our reports in the WC get_report function, change the path to our dir.
	 *
	 * @param array Array of Report types & their labels
	 * @return array Array of Report types & their labels, including the Subscription product type.
	 * @since 1.0
	 */
	public static function initialize_location_admin_reports_path( $report_path, $name, $class ) {

		if ( 'WC_Report_sales_by_location' == $class ) {
			$report_path = self::$plugin_dir . '/classes/class-wc-report-' . $name . '.php';
		}
		
		return $report_path;

	}


	/**
	 * Load our language settings for internationalization
	 *
	 * @since 1.0
	 */
	public static function load_plugin_textdomain( ) {

		load_plugin_textdomain( 'woocommerce-location-report', false, basename( self::$plugin_dir ) . '/languages' );

	}

} // end \WC_Location_Report class


WC_Location_Report::init();
