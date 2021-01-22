=== Star CloudPRNT for WooCommerce Plus ===
Contributors: ko31
Tags: star, printing, printers, automated, e-commerce, store, sales, downloadable, downloads, woocommerce, restaurant, order, receipt
Requires at least: 4.7.0
Tested up to: 5.4.0
Requires PHP: 5.6
Stable tag: 0.9.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Star CloudPRNT for WooCommerce enables Cloud printing technology with your Star Receipt printer.

== Description ==

This is a plugin that extends [Star CloudPRNT for WooCommerce](https://wordpress.org/plugins/star-cloudprnt-for-woocommerce/)(As of version 1.1.2).

The basic behavior is the same as Star CloudPRNT for WooCommerce, with the following added features.

* i18n support
  * You can use [Poedit](https://poedit.net/), [Loco Translate](https://wordpress.org/plugins/loco-translate/), etc. for translation.
* Some hooks
  * With the added filter hooks, you can customize the text to be printed.

== Installation ==

1. Download the plugin zip file [here](https://github.com/ko31/star-cloudprnt-for-woocommerce-plus/releases/latest).
2. Install and activate the plugin.
3. For more information on how to use it, please visit the following links.
  * [Star CloudPRNT for WooCommerce – WordPress plugin \| WordPress\.org](https://wordpress.org/plugins/star-cloudprnt-for-woocommerce/)
  * [Star CloudPRNT for WooCommerce\|Star EMEA](https://star-emea.com/products/star-cloudprnt-for-woocommerce/)

== Hook Reference ==

= scfwp_print_order_summary_number_format_decimals =

Filters number format decimals.

- @param int Sets the number of decimal points. 2 by default.
- @return int - the number of decimal points

= scfwp_print_order_summary_number_format_dec_point =

Filters number format decimal point.

- @param string Sets the separator for the decimal point. `.` by default.
- @return string - the separator

= scfwp_print_order_summary_number_format_thousands_sep =

Filters number format thousands separator.

- @param string Sets the thousands separator. '' by default.
- @return string - the thousands separator

= scfwp_print_order_summary_item_appendix =

Filters to be added after the item.

- @param string|array Sets the text to add. '' by default.
- @param \WC_Order $order
- @param int $item_id
- @param array $item_data
- @return string - the text to add

= scfwp_print_order_summary_overwrite_address =

Filters for overwriting address.

- @param string|array Sets the overwriting address. '' by default.
- @param \WC_Order $order
- @param array $order_data
- @return string - the overwriting address

= scfwp_print_order_summary_after_title =

Filters to be added after title.

- @param string|array Sets the text to add. '' by default.
- @param int $order_id
- @param \WC_Order $order
- @return string - the text to add

= scfwp_print_order_summary_date_format =

Filters header date format.

- @param string Sets the date format. 'd-m-y H:i:s' by default.
- @return string - the date format

= scfwp_print_order_summary_after_method =

Filters to be added after method.

- @param string|array Sets the text to add. '' by default.
- @param int $order_id
- @param \WC_Order $order
- @return string - the text to add

= scfwp_print_order_summary_after_items =

Filters to be added after items.

- @param string|array Sets the text to add. '' by default.
- @param int $order_id
- @param \WC_Order $order
- @return string - the text to add

= scfwp_print_order_summary_after_address =

Filters to be added after address.

- @param string|array Sets the text to add. '' by default.
- @param int $order_id
- @param \WC_Order $order
- @return string - the text to add

= scfwp_print_order_summary_after_notes =

Filters to be added after notes.

- @param string|array Sets the text to add. '' by default.
- @param int $order_id
- @param \WC_Order $order
- @return string - the text to add

= scfwp_print_order_currency_symbol =

Filters currency symbol.

- @param string Sets the currency symbol. '' by default.
- @return string $encoding
- @return string $symbol
- @return string - the currency symbol

= scfwp_print_order_summary_item_prefix_character =

Filters to add characters for item prefix.

- @param string Sets the prefix character. '' by default.
- @return string - the prefix character
