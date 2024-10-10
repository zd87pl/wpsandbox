<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Source_File_Interceptor
 * @subpackage Source_File_Interceptor/admin
 */

class Source_File_Interceptor_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/source-file-interceptor-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/source-file-interceptor-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function add_options_page() {
		add_options_page(
			'Source File Interceptor Settings', 
			'Source File Interceptor', 
			'manage_options', 
			$this->plugin_name, 
			array($this, 'display_options_page')
		);
	}

	public function display_options_page() {
		include_once 'partials/source-file-interceptor-admin-display.php';
	}

	public function register_setting() {
		add_settings_section(
			'source_file_interceptor_general',
			__( 'General Settings', 'source-file-interceptor' ),
			array( $this, 'source_file_interceptor_section_callback' ),
			$this->plugin_name
		);

		add_settings_field(
			'source_file_interceptor_virustotal_api_key',
			__( 'VirusTotal API Key', 'source-file-interceptor' ),
			array( $this, 'virustotal_api_key_field_callback' ),
			$this->plugin_name,
			'source_file_interceptor_general'
		);

		register_setting( $this->plugin_name, 'source_file_interceptor_virustotal_api_key', array( $this, 'sanitize_api_key' ) );
	}

	public function source_file_interceptor_section_callback() {
		echo '<p>' . __( 'Enter your VirusTotal API key to enable domain reputation checking.', 'source-file-interceptor' ) . '</p>';
	}

	public function virustotal_api_key_field_callback() {
		$api_key = get_option( 'source_file_interceptor_virustotal_api_key' );
		echo '<input type="text" name="source_file_interceptor_virustotal_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text">';
	}

	public function sanitize_api_key( $api_key ) {
		return sanitize_text_field( $api_key );
	}
}
