==== WooCommerce Media API ====
Contributors: woopos,zulmamwe
Tags: woocommerce api, wordpress rest api, media library, woocommerce point of sale, woopos
Requires at least: 4.0
Tested up to: 5.3
Stable tag: 2.0
Requires PHP: 5.2.4
Donate link: https://woopos.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Media endpoint for WooCommerce API. Upload and list media file by WooCommerce REST API. 

== Description ==
WooCommerce Media API is an extension of [WooCommerce API](http://woocommerce.github.io/woocommerce-rest-api-docs) with new endpoint `media`(/wp-json/wc/v2/media). This is a wrapper of existing [WordPress REST API](https://developer.wordpress.org/rest-api). This plugin will help you bypass WordPress REST API authentication settings and [JWT](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/), and use WooCommerce API to upload medias and images directly.

Media properties can be found [here](https://developer.wordpress.org/rest-api/reference/media/#schema).
Two additional properties have been added to create media file:
media_path (string write-only): relative path folder (under wp-content/uploads) of the file to create. eg: 2018/05/department/brand.
media_attachment (string write-only): base64 string of media binary file. eg: `R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==`.

List all media: available parameters [here](https://developer.wordpress.org/rest-api/reference/media/#list-media)

[WooPOS](https://woopos.com) (WooCommerce Point Of Sale and Inventory Management desktop app) user: please install this plugin to manage images from WooPOS.

== Installation ==
1. Install the WooCommerce Media API plugin
2. Activate the plugin


== Frequently Asked Questions ==
Q: Do you offer support if I need help?
A: Yes! Please go our support [forum](https://support.woopos.com/forums/) for help.

== Changelog ==
= 1.0 =
* Initial release.

== Upgrade Notice ==
No Upgrade notice, because it is initial version


