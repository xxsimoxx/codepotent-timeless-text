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

		// Load constants.
		require_once plugin_dir_path(__FILE__).'includes/constants.php';

		// Load update client.
		require_once(PATH_CLASSES.'/UpdateClient.class.php');

		// Process shortcodes.
		add_shortcode('timeless-text', [$this, 'process_shortcode']);

		// Register activation method.
		register_activation_hook(__FILE__, [$this, 'activate_plugin']);

		// Register deactivation method.
		register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);

		// Register deletion method. This is a static method; use __CLASS__.
		register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall_plugin']);

		// POST-ADOPTION: Remove these actions before pushing your next update.
		add_action('upgrader_process_complete', [$this, 'enable_adoption_notice'], 10, 2);
		add_action('admin_notices', [$this, 'display_adoption_notice']);

	}

	// POST-ADOPTION: Remove this method before pushing your next update.
	public function enable_adoption_notice($upgrader_object, $options) {
		if ($options['action'] === 'update') {
			if ($options['type'] === 'plugin') {
				if (!empty($options['plugins'])) {
					if (in_array(plugin_basename(__FILE__), $options['plugins'])) {
						set_transient(PLUGIN_PREFIX.'_adoption_complete', 1);
					}
				}
			}
		}
	}

	// POST-ADOPTION: Remove this method before pushing your next update.
	public function display_adoption_notice() {
		if (get_transient(PLUGIN_PREFIX.'_adoption_complete')) {
			delete_transient(PLUGIN_PREFIX.'_adoption_complete');
			echo '<div class="notice notice-success is-dismissible">';
			echo '<h3 style="margin:25px 0 15px;padding:0;color:#e53935;">IMPORTANT <span style="color:#aaa;">information about the <strong style="color:#333;">'.PLUGIN_NAME.'</strong> plugin</h3>';
			echo '<p style="margin:0 0 15px;padding:0;font-size:14px;">The <strong>'.PLUGIN_NAME.'</strong> plugin has been officially adopted and is now managed by <a href="'.PLUGIN_AUTHOR_URL.'" rel="noopener" target="_blank" style="text-decoration:none;">'.PLUGIN_AUTHOR.'<span class="dashicons dashicons-external" style="display:inline;font-size:98%;"></span></a>, a longstanding and trusted ClassicPress developer and community member. While it has been wonderful to serve the ClassicPress community with free plugins, tutorials, and resources for nearly 3 years, it\'s time that I move on to other endeavors. This notice is to inform you of the change, and to assure you that the plugin remains in good hands. I\'d like to extend my heartfelt thanks to you for making my plugins a staple within the community, and wish you great success with ClassicPress!</p>';
			echo '<p style="margin:0 0 15px;padding:0;font-size:14px;font-weight:600;">All the best!</p>';
			echo '<p style="margin:0 0 15px;padding:0;font-size:14px;">~ John Alarcon <span style="color:#aaa;">(Code Potent)</span></p>';
			echo '</div>';
		}
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
				esc_html__('%d year', 'codepotent-timeless-text'),
				esc_html__('%d years', 'codepotent-timeless-text'),
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
		if (empty($atts['y']) || strlen($atts['y']) !== 4 || $atts['y'] > date('Y')) {
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