<?php
/**
 * WC_Report_Sales_By_Location
 *
 * @author      ChuckMac (chuck@chuckmac.info)
 * @category    Admin
 * @package     WooCommerce/Admin/Reports
 * @version     1.1
 */

class WC_Report_Sales_By_Location extends WC_Admin_Report {

	public $chart_colours = array();

	public $location_data;
	public $location_by;
	public $totals_by;

	private $report_data;


	/**
	 * Get report data
	 * @return array
	 */
	public function get_report_data() {
		if ( empty( $this->report_data ) ) {
			$this->query_report_data();
		}
		return $this->report_data;
	}

	/**
	 * Get all data needed for this report and store in the class
	 */
	private function query_report_data() {

		$this->report_data = new stdClass;

		$this->report_data->orders = (array) $this->get_order_report_data( array(
			'data' => array(
				'_' . $this->location_by . '_country' => array(
					'type'     => 'meta',
					'name'     => 'countries_data',
					'function' => null
				),
				'_order_total' => array(
					'type'     => 'meta',
					'function' => 'SUM',
					'name'     => 'total_sales'
				),
				'post_date' => array(
					'type'     => 'post_data',
					'function' => '',
					'name'     => 'post_date'
				),
			),
			'group_by'            => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), meta__' . $this->location_by . '_country.meta_value',
			'order_by'            => 'post_date ASC',
			'query_type'          => 'get_results',
			'filter_range'        => true,
			'order_types'         => array_merge( array( 'shop_order_refund' ), wc_get_order_types( 'sales-reports' ) ),
			'order_status'        => array( 'completed', 'processing', 'on-hold' ),
			'parent_order_status' => array( 'completed', 'processing', 'on-hold' ),
		) );

		$this->report_data->order_counts = (array) $this->get_order_report_data( array(
			'data' => array(
				'_' . $this->location_by . '_country' => array(
					'type'     => 'meta',
					'name'     => 'countries_data',
					'function' => null
				),
				'ID' => array(
					'type'     => 'post_data',
					'function' => 'COUNT',
					'name'     => 'count',
					'distinct' => true,
				),
				'post_date' => array(
					'type'     => 'post_data',
					'function' => '',
					'name'     => 'post_date'
				)
			),
			'group_by'            => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), meta__' . $this->location_by . '_country.meta_value',
			'order_by'            => 'post_date ASC',
			'query_type'          => 'get_results',
			'filter_range'        => true,
			'order_types'         => wc_get_order_types( 'order-count' ),
			'order_status'        => array( 'completed', 'processing', 'on-hold' )
		) );

	}

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array Array of report legend data
	 * @since 1.0
	 */
	public function get_chart_legend() {

		$this->location_by   = ( isset($_REQUEST['location_filter']) ? $_REQUEST['location_filter'] : 'shipping' );
		$this->totals_by     = ( isset($_REQUEST['report_by']) ? $_REQUEST['report_by'] : 'number-orders' );


		$data = $this->get_report_data();

		add_filter( 'woocommerce_reports_get_order_report_query', array( $this, 'location_report_add_count' ) );


		//Loop through the returned data and set depending on sales or order totals
		$country_data = array();
		$export_data = array();

		if ( 'number-orders' == $this->totals_by ) {
			foreach ( $data->order_counts as $location_values ) {
				if ( '' == $location_values->countries_data ) {
					$location_values->countries_data = 'UNDEFINED';
				}
			
				$country_data[$location_values->countries_data] = ( isset( $country_data[$location_values->countries_data] ) ) ? $location_values->count + $country_data[$location_values->countries_data] : $location_values->count;

				if ( 'UNDEFINED' != $location_values->countries_data ) {
					$export_data[$location_values->countries_data][] = $location_values;
				}
			}
		} elseif ( 'order-total' == $this->totals_by ) {
			foreach ( $data->orders as $location_values ) {
	
				if ( '' == $location_values->countries_data ) {
					$location_values->countries_data = 'UNDEFINED';
				}
					
				$country_data[$location_values->countries_data] = ( isset( $country_data[$location_values->countries_data] ) ) ? $location_values->total_sales + $country_data[$location_values->countries_data] : $location_values->total_sales;
	
				if ( 'UNDEFINED' != $location_values->countries_data ) {
					$export_data[$location_values->countries_data][] = $location_values;
				}
			}
		}


		//Pass the data to the screen.
		$this->location_data = $country_data;
		wp_localize_script('jvectormap', 'map_data', $this->location_data);


		//If we are using price, then create another set of data with the price set (map does not like adding with price)
		if ( 'order-total' == $this->totals_by ) {
			$sales_data = $this->location_data;
			array_walk($sales_data, function(&$value, $index){
				$value = strip_tags( wc_price( $value ));
			});
			wp_localize_script('jvectormap', 'map_price_data', $sales_data);
		}

		$legend = array();

		// Remove data with no value
		if( isset( $country_data['UNDEFINED'] ) ) {
			unset( $country_data['UNDEFINED'] );
		}

		$total = array_sum( $country_data );

		if ( 'order-total' == $this->totals_by ) {
			$total = wc_price( $total );
		}

		$legend[] = array(
			'title' => sprintf( __( '%s orders in this period', 'woocommerce-location-report' ), '<strong>' . $total . '</strong>' ),
			'color' => $this->chart_colours['order_total'],
			'highlight_series' => 1
		);

		$legend[] = array(
			'title' => sprintf( __( '%s countries in this period', 'woocommerce-location-report' ), '<strong>' . count( $country_data ) . '</strong>' ),
			'color' => $this->chart_colours['individual_total'],
			'highlight_series' => 2
		);

		/* Export Code */
		$export_array = array();
		$report_type = ( 'number-orders' == $this->totals_by ) ? 'countries_data_count' : 'total_sales';
		foreach ($export_data as $country => $data) {
			$export_prep = $this->prepare_chart_data( $data, 'post_date', $report_type, $this->chart_interval, $this->start_date, $this->chart_groupby );
			$export_array[$country] = array_values( $export_prep );
		}

		// Encode in json format
		$chart_data = json_encode( $export_array );

		?>
		<div class="chart-container" style="display:none !important;">
			<div class="chart-placeholder main" style="display:none !important;"></div>
		</div>
		<script type="text/javascript">
			var main_chart;

			jQuery(function(){
				var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

				var series = [
					<?php
					foreach ( $export_array as $country => $data ) {
						echo "{\n     label: \"$country\",\n     data: order_data.$country\n },";
					}
					?>
				];

				main_chart = jQuery.plot(
						jQuery('.chart-placeholder.main'),
						series
						);
			});

		</script>
		<?php

		/* / Export Code */

		return $legend;
	}

	/**
	 * Add our map widgets to the report screen
	 *
	 * @return array Array of location report widgets
	 * @since 1.0
	 */
	public function get_chart_widgets() {

		$widgets = array();

		$widgets[] = array(
			'title'    => __( 'Showing reports for:', 'woocommerce-location-report' ),
			'callback' => array( $this, 'current_filters' )
		);

		$widgets[] = array(
			'title'    => '',
			'callback' => array( $this, 'location_widget' )
		);

		return $widgets;
	}

	/**
	 * Widget : Show current filters
	 *
	 * @since 1.0
	 */
	public function current_filters() {

		echo '<p><strong>' . ( ($this->location_by == 'billing' ) ? __( 'Billing Address', 'woocommerce-location-report' ) : __( 'Shipping Address', 'woocommerce-location-report' ) ) . '</strong></p>';
		echo '<p><strong>' . ( ($this->totals_by == 'order-total' ) ? __( 'Order total', 'woocommerce-location-report' ) : __( 'Number of orders', 'woocommerce-location-report' ) ) . '</strong></p>';

	}

	/**
	 * Widget : Report filter options
	 *
	 * @since 1.0
	 */
	public function location_widget() {
		?>
		<h4 class="section_title"><span><?php _e( 'Report By', 'woocommerce' ); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<tr class="active">
					<td class="count"></td>
					<td class="name"><a href="<?php echo add_query_arg( 'report_by', 'number-orders' ); ?>"><?php _e( 'Number of orders', 'woocommerce-location-report' ); ?></a></td>
					<td class="sparkline"></td>
				</tr>
				<tr class="active">
					<td class="count"></td>
					<td class="name"><a href="<?php echo add_query_arg( 'report_by', 'order-total' ); ?>"><?php _e( 'Order total', 'woocommerce-location-report' ); ?></a></td>
					<td class="sparkline"></td>
				</tr>
			</table>
		</div>
		<h4 class="section_title"><span><?php _e( 'Location Filter', 'woocommerce' ); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<tr class="active">
					<td class="count"></td>
					<td class="name"><a href="<?php echo add_query_arg( 'location_filter', 'shipping' ); ?>"><?php _e( 'Shipping Address', 'woocommerce-location-report' ); ?></a></td>
					<td class="sparkline"></td>
				</tr>
				<tr class="active">
					<td class="count"></td>
					<td class="name"><a href="<?php echo add_query_arg( 'location_filter', 'billing' ); ?>"><?php _e( 'Billing Address', 'woocommerce-location-report' ); ?></a></td>
					<td class="sparkline"></td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Output the report
	 *
	 * @since 1.0
	 */
	public function output_report() {

		$ranges = array(
			'year'         => __( 'Year', 'woocommerce' ),
			'last_month'   => __( 'Last Month', 'woocommerce' ),
			'month'        => __( 'This Month', 'woocommerce' ),
			'7day'         => __( 'Last 7 Days', 'woocommerce' )
		);

		$this->chart_colours = array(
			'order_total' 		=> '#3498db',
			'individual_total'  => '#75b9e7'
		);

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) ) {
			$current_range = '7day';
		}

		$this->calculate_current_range( $current_range );

		include( WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php');

	}

	/**
	 * Output an export link
	 *
	 * @since 1.0
	 */
	public function get_export_button() {
		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';
		?>
		<a
			href="#"
			download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo date_i18n( 'Y-m-d', current_time('timestamp') ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php _e( 'Date', 'woocommerce' ); ?>"
			data-groupby="<?php echo $this->chart_groupby; ?>"
		>
			<?php _e( 'Export CSV', 'woocommerce' ); ?>
		</a>
		<?php
	}

	/**
	 * Main Chart : Add the placeholder javascript /div for the location report
	 *
	 * @since 1.0
	 */
	public function get_main_chart() {
		global $wp_locale;
		?>

		<div class="jvectormap jvectormap-mill" id="world-map" style="height: 500px;">
		<script type="text/javascript">
			jQuery(function($){
				$('#world-map').vectorMap( {
					map: 'world_mill_en',
					backgroundColor: "transparent",
					regionStyle: {
						initial:  {	fill: "#d2d2d2"}
					},
					onRegionTipShow: function(e, el, code) {
						<?php
						if ( isset($_REQUEST['report_by']) && 'order-total' == $_REQUEST['report_by'] ) { // show formatted price for order totals ?>
							el.html('<strong>'+(map_price_data[code] ? map_price_data[code] : 0)+' - </strong> '+el.html());
						<?php
						} else { ?>
							el.html('<strong>'+(map_data[code] ? map_data[code] : 0)+' <?php _e('orders', 'woocommerce-location-report'); ?> - '+'</strong> '+el.html());
						<?php
						} ?>
					},
						series: {
						  regions: [{
						values: map_data,
						scale: ['#F0C7E8', '#A46497'],
						normalizeFunction: 'polynomial'
						  }]
						},
				});
			});
		</script>

		<?php
	}

	/**
	 * Add the address count to the sql query
	 *
	 * @return string sql query data
	 * @since 1.0
	 */
	public function location_report_add_count( $query ) {

		$sql = preg_replace('/^SELECT /', 'SELECT COUNT(meta__' . $this->location_by . '_country.meta_value) as countries_data_count, ', $query);
		return $sql;

	}

}
