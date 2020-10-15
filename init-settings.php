<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class init
 */
class init {
	/**
	 * init constructor.
	 */
	public function __construct() {
		$this->set_locale();
	}

	/**
	 * Run.
	 */
	public function run() {
		$this->set_locale();
	}

	/**
	 * Load translated strings.
	 */
	public function set_locale() {
		load_plugin_textdomain(
			'star-cloudprnt-for-woocommerce-plus',
			false,
			basename( dirname( __FILE__ ) ) . '/languages'
		);
	}
}

new init();
