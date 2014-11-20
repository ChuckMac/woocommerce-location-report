=== WooCommerce Sales By Location Report ===
Contributors: chuckmac
Tags: woocommerce, extension, reporting, analytics
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WooCommerce reporting extension to elegantly display customer orders by location.

== Description ==

This plugin adds a new section in the WooCommerce Reports -> Orders area called 'Sales by location'.

The report visualizes the customer purchases by location into a Choropleth map to show where your orders are being placed.

* Display by shipping address or billing address
* Display by number of orders or order amount.

= Get involved =

Developers can contribute to the source code on the [GitHub Repository](https://github.com/chuckmac/woocommerce-location-report).


== Frequently Asked Questions ==

= The sales report says I have more orders in the time period than the location report shows... why? =

The location map only displays orders that have a recognizable address associated with the order.  For example, if there is no shipping address for the order then the map will not display the order if you are on the Shipping Address filter. 


== Installation ==

= Minimum Requirements =

* WordPress 3.8 or greater
* WooCommerce 2.2 or greater

= Instructions =

1. Upload the entire 'woocommerce-location-report' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Screenshots ==

1. Example number of order report map.
2. Example sales volume report map.


== Changelog ==
= 1.1 - 2014/11/20
* Fix - WooCommerce active check
* Tweak - yoda coding style
* Enhancement - Export functionality in report

= 1.0.0 - 10/15/2014 =
* Initial Release (!)