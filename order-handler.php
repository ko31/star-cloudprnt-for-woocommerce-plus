<?php
	function star_cloudprnt_get_column_separated_data($columns, $max_chars)
	{
		//$max_chars = STAR_CLOUDPRNT_MAX_CHARACTERS_TWO_INCH;
		$total_columns = count($columns);

		if ($total_columns == 0) return "";
		if ($total_columns == 1) return $columns[0];
		if ($total_columns == 2)
		{
			$total_characters = strlen($columns[0])+strlen($columns[1]);
			$total_whitespace = $max_chars - $total_characters;
			if ($total_whitespace < 0) return "";
			return $columns[0].str_repeat(" ", $total_whitespace).$columns[1];
		}

		$total_characters = 0;
		foreach ($columns as $column)
		{
			$total_characters += strlen($column);
		}
		$total_whitespace = $max_chars - $total_characters;
		if ($total_whitespace < 0) return "";
		$total_spaces = $total_columns-1;
		$space_width = floor($total_whitespace / $total_spaces);
		$result = $columns[0].str_repeat(" ", $space_width);
		for ($i = 1; $i < ($total_columns-1); $i++)
		{
			$result .= $columns[$i].str_repeat(" ", $space_width);
		}
		$result .= $columns[$total_columns-1];

		return $result;
	}

	function star_cloudprnt_get_seperator($max_chars)
	{
		//$max_chars = STAR_CLOUDPRNT_MAX_CHARACTERS_TWO_INCH;
		return str_repeat('_', $max_chars);
	}

	function star_cloudprnt_parse_order_status($status)
	{
		if ($status === 'wc-pending') return __( 'Pending Payment', 'star-cloudprnt-for-woocommerce-plus' );
		else if ($status === 'wc-processing') return __( 'Processing', 'star-cloudprnt-for-woocommerce-plus' );
		else if ($status === 'wc-on-hold') return __( 'On Hold', 'star-cloudprnt-for-woocommerce-plus' );
		else if ($status === 'wc-completed') return __( 'Completed', 'star-cloudprnt-for-woocommerce-plus' );
		else if ($status === 'wc-cancelled') return __( 'Cancelled', 'star-cloudprnt-for-woocommerce-plus' );
		else if ($status === 'wc-refunded') return __( 'Refunded', 'star-cloudprnt-for-woocommerce-plus' );
		else if ($status === 'wc-failed') return __( 'Failed', 'star-cloudprnt-for-woocommerce-plus' );
		else return "Unknown";
	}

	function star_cloudprnt_get_codepage_currency_symbol()
	{
		$encoding = get_option('star-cloudprnt-printer-encoding-select');
		$symbol = get_woocommerce_currency_symbol();

		/**
		 * Filters currency symbol.
		 */
		$currency_symbol = apply_filters( 'scfwp_print_order_currency_symbol', '', $encoding, $symbol );
		if ( $currency_symbol ) {
			return $currency_symbol;
		}

		if ($encoding === "UTF-8") {
			if ($symbol === "&pound;") return "�"; // � pound
			else if ($symbol === "&#36;") return "$"; // $ dollar
			else if ($symbol === "&euro;") return "�"; // � euro
			else if ($symbol === "&yen;") return "¥"; // ¥ yen
		} elseif ($encoding == "1252"){
			if ($symbol === "&pound;") return "\xA3"; // � pound
			else if ($symbol === "&#36;") return "\x24"; // $ dollar
			else if ($symbol === "&euro;") return "\x80"; // � euro
			else if ($symbol === "&yen;") return "\xA5"; // ¥ yen
		} else {
			if ($symbol === "&pound;") return "GBP"; // � pound
			else if ($symbol === "&#36;") return ""; // $ dollar
			else if ($symbol === "&euro;") return "EUR"; // � euro
			else if ($symbol === "&yen;") return "YEN"; // ¥ yen
		}

		return ""; // return blank by default
	}

	function star_cloudprnt_get_formatted_variation($variation, $order, $item_id)
	{
		$return = '';
		if (is_array($variation))
		{
			$variation_list = array();
			foreach ($variation as $name => $value)
			{
				// If the value is missing, get the value from the item
				if (!$value)
				{
					$meta_name = esc_attr(str_replace('attribute_', '', $name));
					$value = $order->get_item_meta($item_id, $meta_name, true);
				}

				// If this is a term slug, get the term's nice name
				if (taxonomy_exists(esc_attr(str_replace('attribute_', '', $name))))
				{
					$term = get_term_by('slug', $value, esc_attr(str_replace('attribute_', '', $name)));
					if (!is_wp_error($term) && ! empty($term->name))
					{
						$value = $term->name;
					}
				}
				else
				{
					$value = ucwords(str_replace( '-', ' ', $value ));
				}
				$variation_list[] = wc_attribute_label(str_replace('attribute_', '', $name)) . ': ' . rawurldecode($value);
			}
			$return .= implode('||', $variation_list);
		}
		return $return;
	}

	function star_cloudprnt_create_receipt_items($order, &$printer, $max_chars)
	{
		$order_items = $order->get_items();
		foreach ($order_items as $item_id => $item_data)
		{
			$product_name = $item_data['name'];
			$product_id = $item_data['product_id'];
			$variation_id = $item_data['variation_id'];

			$item_qty = $order->get_item_meta($item_id, "_qty", true);
			$item_total_price = floatval($order->get_item_meta($item_id, "_line_total", true))
							+floatval($order->get_item_meta($item_id, "_line_tax", true));
			$item_price = floatval($item_total_price) / intval($item_qty);
			$currencyHex = star_cloudprnt_get_codepage_currency_symbol();
			/**
			 * Filters number format decimals.
			 */
			$number_format_decimals = apply_filters( 'scfwp_print_order_summary_number_format_decimals', 2 );
			/**
			 * Filters number format decimal point.
			 */
			$number_format_dec_point = apply_filters( 'scfwp_print_order_summary_number_format_dec_point', '.' );
			/**
			 * Filters number format thousands separator.
			 */
			$number_format_thousands_sep = apply_filters( 'scfwp_print_order_summary_number_format_thousands_sep', '' );
			$formatted_item_price = number_format($item_price, $number_format_decimals, $number_format_dec_point , $number_format_thousands_sep);
			$formatted_total_price = number_format($item_total_price, $number_format_decimals, $number_format_dec_point, $number_format_thousands_sep);

			$printer->set_text_emphasized();
			$printer->add_text_line(str_replace('&ndash;', '-', $product_name).__( ' - ID: ', 'star-cloudprnt-for-woocommerce-plus' ).$product_id."");
			$printer->cancel_text_emphasized();

			if ($variation_id != 0)
			{
				$product_variation = new WC_Product_Variation( $variation_id );
				$variation_data = $product_variation->get_variation_attributes();
				$variation_detail = star_cloudprnt_get_formatted_variation($variation_data, $order, $item_id);
				$exploded = explode("||", $variation_detail);
				foreach($exploded as $exploded_variation)
				{
					$printer->add_text_line(" ".ucwords($exploded_variation));
				}
			}
			$printer->add_text_line(star_cloudprnt_get_column_separated_data(array(__( ' Qty: ', 'star-cloudprnt-for-woocommerce-plus' ).
						$item_qty.__( ' x Cost: ', 'star-cloudprnt-for-woocommerce-plus' ).$currencyHex.$formatted_item_price,
						$currencyHex.$formatted_total_price), $max_chars));
			/**
			 * Filters item appendix.
			 */
			$item_appendix = apply_filters( 'scfwp_print_order_summary_item_appendix', '', $order, $item_id, $item_data );
			if ($item_appendix){
				star_cloudprnt_add_text_line($item_appendix, $printer);
			}
		}
	}

	function star_cloudprnt_create_address($order, $order_meta, &$printer)
	{
		$fname = $order_meta['_shipping_first_name'][0];
		$lname = $order_meta['_shipping_last_name'][0];
		$a1 = $order_meta['_shipping_address_1'][0];
		$a2 = $order_meta['_shipping_address_2'][0];
		$city = $order_meta['_shipping_city'][0];
		$state = $order_meta['_shipping_state'][0];
		$postcode = $order_meta['_shipping_postcode'][0];
		$tel = $order_meta['_billing_phone'][0];

		$printer->set_text_emphasized();
		if ($a1 == '')
		{
			$printer->add_text_line(__( 'Billing Address:', 'star-cloudprnt-for-woocommerce-plus' ));
			$printer->cancel_text_emphasized();
			$fname = $order_meta['_billing_first_name'][0];
			$lname = $order_meta['_billing_last_name'][0];
			$a1 = $order_meta['_billing_address_1'][0];
			$a2 = $order_meta['_billing_address_2'][0];
			$city = $order_meta['_billing_city'][0];
			$state = $order_meta['_billing_state'][0];
			$postcode = $order_meta['_billing_postcode'][0];
		}
		else
		{
			$printer->add_text_line(__( 'Shipping Address:', 'star-cloudprnt-for-woocommerce-plus' ));
			$printer->cancel_text_emphasized();
		}

		/**
		 * Filters for overwriting address.
		 */
		$overwrite_address = apply_filters( 'scfwp_print_order_summary_overwrite_address', '', $order, $order_meta );
		if ($overwrite_address){
			star_cloudprnt_add_text_line($overwrite_address, $printer);
		} else {
			$printer->add_text_line($fname." ".$lname);
			$printer->add_text_line($a1);
			if ($a2 != '') $printer->add_text_line($a2);
			if ($city != '') $printer->add_text_line($city);
			if ($state != '') $printer->add_text_line($state);
			if ($postcode != '') $printer->add_text_line($postcode);
		}
		$printer->add_text_line(__( 'Tel: ', 'star-cloudprnt-for-woocommerce-plus' ).$tel);
	}

	function star_cloudprnt_print_order_summary($selectedPrinter, $file, $order_id)
	{
		$order = wc_get_order($order_id);
		$shipping_items = @array_shift($order->get_items('shipping'));
		$order_meta = get_post_meta($order_id);

		if ($selectedPrinter['format'] == "txt") {
			$printer = new Star_CloudPRNT_Text_Plain_Job($selectedPrinter, $file);
		} else if ($selectedPrinter['format'] == "slt") {
			$printer = new Star_CloudPRNT_Star_Line_Mode_Job($selectedPrinter, $file);
		} else if ($selectedPrinter['format'] == "slm") {
			$printer = new Star_CloudPRNT_Star_Line_Mode_Job($selectedPrinter, $file);
		} else if ($selectedPrinter['format'] == "spt") {
			$printer = new Star_CloudPRNT_Star_Prnt_Job($selectedPrinter, $file);

		} else {
			$printer = new Star_CloudPRNT_Text_Plain_Job($selectedPrinter, $file);
		}

		$printer->set_codepage(get_option('star-cloudprnt-printer-encoding-select'));
		if (get_option('star-cloudprnt-print-logo-top-input')) $printer->add_nv_logo(esc_attr(get_option('star-cloudprnt-print-logo-top-input')));
		$printer->set_text_emphasized();
		$printer->set_text_center_align();
		$printer->set_font_magnification(2, 2);
		if($selectedPrinter['columns'] < 40) {
			$printer->add_text_line(__( 'ORDER', 'star-cloudprnt-for-woocommerce-plus' ));
			$printer->add_text_line(__( 'NOTIFICATION', 'star-cloudprnt-for-woocommerce-plus' ));
		} else {
			$printer->add_text_line(__( 'ORDER NOTIFICATION', 'star-cloudprnt-for-woocommerce-plus' ));
		}
		$printer->set_text_left_align();
		$printer->cancel_text_emphasized();
		$printer->set_font_magnification(1, 1);
		$printer->add_new_line(1);

		/**
		 * Filters after title.
		 */
		$after_title = apply_filters( 'scfwp_print_order_summary_after_title', '', $order_id, $order);
		if ($after_title){
			star_cloudprnt_add_text_line($after_title, $printer);
			$printer->add_new_line(1);
		}

		/**
		 * Filters date format.
		 */
		$date_format = apply_filters( 'scfwp_print_order_summary_date_format', 'd-m-y H:i:s' );
		$printer->add_text_line(star_cloudprnt_get_column_separated_data(array(__( 'Order #', 'star-cloudprnt-for-woocommerce-plus' ).$order_id, date_i18n($date_format, time())), $selectedPrinter['columns']));
		$printer->add_new_line(1);
		$printer->add_text_line(__( 'Order Status: ', 'star-cloudprnt-for-woocommerce-plus' ).star_cloudprnt_parse_order_status($order->post->post_status));
		$printer->add_text_line(__( 'Order Date: ', 'star-cloudprnt-for-woocommerce-plus' ).$order->order_date);
		if (isset($shipping_items['name']))
		{
			$printer->add_new_line(1);
			$printer->add_text_line(__( 'Shipping Method: ', 'star-cloudprnt-for-woocommerce-plus' ).$shipping_items['name']);
		}
		$printer->add_text_line(__( 'Payment Method: ', 'star-cloudprnt-for-woocommerce-plus' ).$order_meta['_payment_method_title'][0]);
		$printer->add_new_line(1);

		/**
		 * Filters after method.
		 */
		$after_method = apply_filters( 'scfwp_print_order_summary_after_method', '', $order_id, $order);
		if ($after_method){
			star_cloudprnt_add_text_line($after_method, $printer);
			$printer->add_new_line(1);
		}

		$printer->add_text_line(star_cloudprnt_get_column_separated_data(array(__( 'ITEM', 'star-cloudprnt-for-woocommerce-plus' ), __( 'TOTAL', 'star-cloudprnt-for-woocommerce-plus' )), $selectedPrinter['columns']));
		$printer->add_text_line(star_cloudprnt_get_seperator($selectedPrinter['columns']));

		star_cloudprnt_create_receipt_items($order, $printer, $selectedPrinter['columns']);

		$printer->add_new_line(1);
		$printer->set_text_right_align();
		/**
		 * Filters number format decimals.
		 */
		$number_format_decimals = apply_filters( 'scfwp_print_order_summary_number_format_decimals', 2 );
		/**
		 * Filters number format decimal point.
		 */
		$number_format_dec_point = apply_filters( 'scfwp_print_order_summary_number_format_dec_point', '.' );
		/**
		 * Filters number format thousands separator.
		 */
		$number_format_thousands_sep = apply_filters( 'scfwp_print_order_summary_number_format_thousands_sep', '' );
		$formatted_overall_total_price = number_format($order_meta['_order_total'][0], $number_format_decimals, $number_format_dec_point, $number_format_thousands_sep);
		$printer->add_text_line(__( 'TOTAL     ', 'star-cloudprnt-for-woocommerce-plus' ).star_cloudprnt_get_codepage_currency_symbol().$formatted_overall_total_price);
		$printer->set_text_left_align();
		$printer->add_new_line(1);
		$printer->add_text_line(__( 'All prices are inclusive of tax (if applicable).', 'star-cloudprnt-for-woocommerce-plus' ));
		$printer->add_new_line(1);

		/**
		 * Filters after items.
		 */
		$after_items = apply_filters( 'scfwp_print_order_summary_after_items', '', $order_id, $order);
		if ($after_items){
			star_cloudprnt_add_text_line($after_items, $printer);
			$printer->add_new_line(1);
		}

		star_cloudprnt_create_address($order, $order_meta, $printer);

		/**
		 * Filters after address.
		 */
		$after_address = apply_filters( 'scfwp_print_order_summary_after_address', '', $order_id, $order);
		if ($after_address){
			$printer->add_new_line(1);
			star_cloudprnt_add_text_line($after_address, $printer);
			$printer->add_new_line(1);
		}

		$printer->add_new_line(1);
		$printer->set_text_emphasized();
		$printer->add_text_line(__( 'Customer Provided Notes:', 'star-cloudprnt-for-woocommerce-plus' ));
		$printer->cancel_text_emphasized();
		$printer->add_text_line(empty($order->post->post_excerpt) ? __( 'None', 'star-cloudprnt-for-woocommerce-plus' ) : $order->post->post_excerpt);

		/**
		 * Filters after notes.
		 */
		$after_notes = apply_filters( 'scfwp_print_order_summary_after_notes', '', $order_id, $order);
		if ($after_notes){
			$printer->add_new_line(1);
			star_cloudprnt_add_text_line($after_notes, $printer);
			$printer->add_new_line(1);
		}

		if (get_option('star-cloudprnt-print-logo-bottom-input')) $printer->add_nv_logo(esc_attr(get_option('star-cloudprnt-print-logo-bottom-input')));

		$printer->printjob();
	}

	function star_cloudprnt_woo_on_thankyou($order_id)
	{
		$extension = STAR_CLOUDPRNT_SPOOL_FILE_FORMAT;

		$selectedPrinterMac = "";
		$selectedPrinter = array();
		$printerList = star_cloudprnt_get_printer_list();
		if (!empty($printerList))
		{

			foreach ($printerList as $printer)
			{
				if (get_option('star-cloudprnt-printer-select') == $printer['name'])
				{
					$selectedPrinter = $printer;
					$selectedPrinterMac = $printer['printerMAC'];
					break;
				}
			}

			if (sizeof($selectedPrinter) == 0) {
				$selectedPrinter = $printerList[0];
			}

			/* Decide best printer emulation and print width as far as possible
			   NOTE: this is not the ideal way, but suits the existing
			   code structure. Will be reviewed.
			   */

			$encodings = $selectedPrinter['Encodings'];
			$columns = STAR_CLOUDPRNT_MAX_CHARACTERS_THREE_INCH;
			if (strpos($encodings, "application/vnd.star.line;") !== false) {
				/* There is no guarantee that printers will always return zero spacing between
				   the encoding name and separating semi-colon. But, definitely the HIX does, socket_accept
				   this is enough to ensure that thermal print mode is always used on HIX printers
				   with pre 1.5 firmware. This matches older plugin behaviour and therefore
				   avoids breaking customer sites.
				*/
				$extension = "slt";
			} else if (strpos($encodings, "application/vnd.star.linematrix") !== false) {
				$extension = "slm";
				$columns = STAR_CLOUDPRNT_MAX_CHARACTERS_DOT_THREE_INCH;
			} else if (strpos($encodings, "application/vnd.star.line") !== false) {
				// a second check for Line mode - just in case the above one didn't catch item
				// and after the "linemodematrix" check, to avoid a false match.
				$extension = "slt";
			} else if (strpos($encodings, 'application/vnd.star.starprnt') !== false) {
				$extension = "spt";
			} else if (strpos($encodings, "text/plain") !== false) {
				$extension = "txt";
			}

			if ($selectedPrinter['ClientType'] == "Star mC-Print2") {
				$columns = STAR_CLOUDPRNT_MAX_CHARACTERS_TWO_INCH;
			}
			//var_dump($selectedPrinter);
			//print("Chosen Print Format:".$extension.", Columns:".$columns. "<br/>");

			$selectedPrinter['format'] = $extension;
			$selectedPrinter['columns'] = $columns;

			$file = STAR_CLOUDPRNT_PRINTER_PENDING_SAVE_PATH.star_cloudprnt_get_os_path("/order_".$order_id."_".time().".".$extension);



			if ($selectedPrinter !== "") star_cloudprnt_print_order_summary($selectedPrinter, $file, $order_id);
		}
	}

	function star_cloudprnt_setup_order_handler()
	{
		if (selected(get_option('star-cloudprnt-select'), "enable", false) !== "" && star_cloudprnt_is_woo_activated())
		{
			add_action('woocommerce_thankyou', 'star_cloudprnt_woo_on_thankyou', 1, 1);
		}
	}

	function star_cloudprnt_add_text_line($text, &$printer)
	{
		if ( $text ) {
			$exploded = explode( "\n", $text );
			foreach ( $exploded as $line ) {
				$printer->add_text_line( $line );
			}
		}
	}
?>
