# WooCommerce Media API Plugin by WooPOS

WooCommerce Media API is an extension of WooCommerce API with new endpoint media(/wp-json/wc/v3/media). This is a wrapper of existing WordPress REST API. This plugin will help you bypass WordPress REST API authentication settings and JWT, and use WooCommerce API to upload medias and images directly.

Developed by [WooPOS](https://woopos.com) and customized by me.


## Requirements
- Wordpress 4.4+
- WooCommerce 3.5+

## Usage
- Install and activate this plugin; 
- Send a POST request to **/wp-json/wc/v3/media** with following required data:

<table>
  <thead>
    <tr>
      <td>
        Parameters
      </td>
      <td>
        Description
      </td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        file (multipart)
      </td>
      <td>
        The file to upload. Array is not supported.
      </td>
    </tr>
  </tbody>
  </table>
  
  Additional media properties can be found [here](https://developer.wordpress.org/rest-api/reference/media/#schema).

