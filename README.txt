=== J&T Express Malaysia ===
Contributors: jtexpress
Donate link: https://www.jtexpress.ae/
Tags: jtexpress, j&t express, j&t malaysia, jnt
Requires at least: 5.1
Tested up to: 5.7
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

J&T Plugin is J&T's official extension for WooCommerce on WordPress. Manage your shipments easily.

== Description ==

J&T Plugin is J&T's official extension for WooCommerce on WordPress. Manage your shipments easily.

== FEATURES ==

* Fast and easy consignment note creation of your orders.

* Easily print out consignment note.

* Bulk Consignment Note Creation allows you to create consignment notes for multiple orders.

* Bulk Consignment Note Printing allows you to print multiple consignment notes.

* Tracking Parcel Status function.

== Installation ==

1. Upload the files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins.

2. Activate the plugin through the `Plugins` menu in WordPress.

3. Done and Ready.

== Frequently Asked Questions ==

= Can I Use This Plugin Without Being A J&T VIP customer? =

No. You need to be a valid J&T VIP customer to use this plugin. You can register to be J&T VIP customer at [here](https://jtexpress.my/vip-account)

= What if I have an existing VIP account with J&T? How do I get the API Key?=

Please request the API Key from the person in charge that helped you to create the VIP account.

== Screenshots ==

1. J&T Profile Setting
2. Orders Creation
3. Consignment Note Creation

== Changelog ==

= 2.0.0 = 
- added image to menu item
- added setting menu item to woocommerce sub menu (admin.php?page=wc-settings&tab=shipping&section=jnt)
- added setting link in tracking page (admin.php?page=jt_main_page)
- changed tracking page url to avoid conflicts, from admin.php?page=main_page to admin.php?page=jnt_main_page
- added error handling when you want to cancel an order and it fails due to j&t error (e.g. "Order status prohibits cancellation" )
- added name and extension to output pdf file so when you save using phone you don't need to add .pdf manually to filename. Changed filename is: print-document.pdf
- changed Province setting field in woocommerce general settings page and make it clear that this item belongs to jnt express, set default value to: Makkah
- set correct current menu item as selected when you are in J&T Express setting page ( by default Settings submenu of Wc is active, but not JnT Express is selected)
- added options in jnt express setting page, dependant and independent options, grouped options
- added order's shipments information at edit order page, Last Delivery Status Change, Dispatcher Contact, Current Delivery Status
- fixed compatibility with latest woocommerce version
- fixed a lot of codes with errors and being not standard
- made setting page translate-able and also everything added are translate-ready

= 1.0.0 =
* First release.

= 1.0.1 =
* Cancel Function, Tracking Function, New Consignment Note Format.