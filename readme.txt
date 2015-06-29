=== WooCommerce Sales By Location Report ===
Contributors: chuckmac
Tags: woocommerce, extension, reporting, analytics
Requires at least: 3.8
Tested up to: 4.2
Stable tag: 1.0
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
* WooCommerce 2.3 or greater

= Instructions =

1. Upload the entire 'woocommerce-location-report' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Screenshots ==

1. Example number of order report map.
2. Example sales volume report map.


== Changelog ==

= 1.2 - 2015.06.29 =
* Enhancement - Update to jVectorMap 2.0.2
* Fix - Fix order total reporting issue with WooCommerce 2.3

= 1.1.1 - 2015.01.11 =
* Fix - add .js suffix to world map 

= 1.1 - 2014.10.20 = 
* Fix - WooCommerce active check
* Tweak - yoda coding style

= 1.0.0 - 10/15/2014 =
* Initial Release (!)