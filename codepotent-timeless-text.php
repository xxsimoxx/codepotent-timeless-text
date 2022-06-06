<?php

/**
 * -----------------------------------------------------------------------------
 * Plugin Name: Timeless Text
 * Description: For phrases like "I have worked for n years in web dev" or "We have n years of combined experience" – this plugin keeps the numbers updated each year automatically.
 * Version: 2.1.0
 * Author: Simone Fioravanti
 * Author URI: https://software.gieffeedizioni.it
 * Plugin URI: https://software.gieffeedizioni.it
 * Text Domain: codepotent-timeless-text
 * Domain Path: /languages
 * -----------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * -----------------------------------------------------------------------------
 * Copyright 2021, John Alarcon (Code Potent)
 * -----------------------------------------------------------------------------
 * Adopted by Simone Fioravanti, 06/01/2021
 * -----------------------------------------------------------------------------
 */

// Declare the namespace.
namespace CodePotent\TimelessText;

// Prevent direct access.
if (!defined('ABSPATH')) {
	die();
}

class TimelessText {

	/**
	 * Constructor.
	 *
	 * No properties to set; move straight to initialization.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->init();

	}

	/**
	 * Plugin initialization.
	 *
	 * Register actions and filters to hook the plugin into the system.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 */
	public function init() {

		// Load update client.
		require_once('classes/UpdateClient.class.php');

		// Process shortcodes.
		add_shortcode('timeless-text', [$this, 'process_shortcode']);

		// Register activation method.
		register_activation_hook(__FILE__, [$this, 'activate_plugin']);

		// Register deactivation method.
		register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);

		// Register deletion method. This is a static method; use __CLASS__.
		register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall_plugin']);

	}

	/**
	 * Process shortcode
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function process_shortcode($atts) {

		// Get the integer.
		if (!empty($atts['combined'])) {
			$n = $this->calculate_years_combined($atts);
		} else {
			$n = $this->calculate_years($atts);
		}

		// If $n is not an integer, it's a a text error message. Bail.
		if (!is_int($n)) {
			return $n;
		}

		// If shortcode contains text=false, return only the integer.
		if (isset($atts['text'])) {
			return $n;
		}

		// Otherwise, return the translated string.
		return sprintf(
			_n(
				'%d year',
				'%d years',
				$n,
				'codepotent-timeless-text'
			),
			number_format_i18n($n)
		);

	}

	/**
	 * Calculate years
	 *
	 * This method accepts arguments for year and, optionally, month and day, to
	 * determine how many full years have passed since that date.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function calculate_years($atts) {

		// Year not present? Bail.
		if (empty($atts['y']) || strlen($atts['y']) !== 4 || $atts['y'] > gmdate('Y')) {
			return esc_html__('The year must be a 4-digit number and may not be greater than the current year.', 'codepotent-timeless-text');
		}

		// Cast the year value as an integer.
		$y = (int)$atts['y'];

		// Ensure month and day values are integers, if set.
		$m = (!empty($atts['m'])) ? (int)$atts['m'] : 1;
		$d = (!empty($atts['d'])) ? (int)$atts['d'] : 1;

		// Calculate span between start date and today.
		$diff = date_diff(date_create("$y-$m-$d"), date_create());

		// If only the number is needed, return it here.
		return $diff->y;

	}

	/**
	 * Calculate combined years
	 *
	 * This method accepts a string of comma-separated dates and turns them into
	 * an integer totalling the full years that have passed since each date.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string|integer
	 */
	public function calculate_years_combined($atts) {

		// No comma present? Bail.
		if (!strpos($atts['combined'], ',')) {
			return esc_html__('Dates must be in the format: YYYY-MM-DD, YYYY-MM-DD, YYYY-MM-DD', 'codepotent-timeless-text');
		}

		// Convert forward-slashes to hyphens; just in case.
		$atts['combined'] = str_replace('/', '-', $atts['combined']);

		// Split the dates.
		$dates = explode(',', $atts['combined']);

		// Initialization.
		$years = 0;

		// Add the number of years for each date.
		foreach ($dates as $date) {
			$parts = explode('-', $date);
			$years += $this->calculate_years(['y'=>trim($parts[0]),'m'=>trim($parts[1]),'d'=>trim($parts[2])]);
		}

		// Return the cumulative years.
		return $years;

	}

	/**
	 * Plugin activation.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 */
	public function activate_plugin() {

		// No permission to activate plugins? Bail.
		if (!current_user_can('activate_plugins')) {
			return;
		}

	}

	/**
	 * Plugin deactivation.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 */
	public function deactivate_plugin() {

		// No permission to activate plugins? None to deactivate either. Bail.
		if (!current_user_can('activate_plugins')) {
			return;
		}

	}

	/**
	 * Plugin deletion.
	 *
	 * @author John Alarcon
	 *
	 * @since 2.0.0
	 */
	public static function uninstall_plugin() {

		// No permission to delete plugins? Bail.
		if (!current_user_can('delete_plugins')) {
			return;
		}

	}

}

// Make timeless the text.
new TimelessText;