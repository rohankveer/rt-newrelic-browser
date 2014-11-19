<?php

/**
 * Plugin Name: New Relic Browser
 * Plugin URI: http://www.rtcamp.com
 * Description: New Relic Browser Monitoring plugin.
 * Version: 1.0
 * Author: rtCamp
 * Author URI: http://www.rtcamp.com
 * License:  rt-newrelic-browser
 */
function rtp_relic_register_settings() {
	register_setting( 'relic_options_settings', 'relic_options', 'rtp_relic_options_validate' );
}

function rtp_relic_option_page() {
	add_options_page( 'New Relic Options', 'New Relic Browser', 'manage_options', 'new-relic-browser', 'new_relic_options' );
}

function new_relic_options() {
	include('admin/new-relic-admin.php');
}

function rtp_relic_load_js() {
	wp_enqueue_script( 'rtp_relic_js', plugins_url( '/js/rtp-relic.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_style( 'rtp_relic_css', plugins_url( '/css/rtp-new-relic.css', __FILE__ ) );
}

function rtp_relic_options_validate( $input ) {
	$relic_password = 'rtcamp.com@1';
	$option_name = 'rtp_relic_account_details';
	$data = array(
		account => array(
			"name" => $_POST['relic-account-name'],
			"users" => array(
				array(
					"email" => $_POST['relic-account-email'],
					"password" => $relic_password,
					"first_name" => $_POST['relic-first-name'],
					"last_name" => $_POST['relic-last-name'],
					"role" => "admin",
					"owner" => "true"
				)
			)
		)
	);
	$dataString = json_encode( $data );
	$curl = curl_init();
	curl_setopt_array( $curl, array(
		CURLOPT_URL => 'https://staging.newrelic.com/api/v2/partners/191/accounts',
		CURLOPT_POST => 1,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HTTPHEADER => array( 'x-api-key:0118286cc87aca4eef6723d567a94b3916167fc4cf91177', 'Content-Type:application/json' ),
		CURLOPT_POSTFIELDS => $dataString
	) );

	$response = curl_exec( $curl );
	var_dump( $response );
	curl_close( $curl );
	$json_data = json_decode( $response );
	if ( $json_data->error != '' ) {
		print_r( $json_data->error );
		die();
	} else {
		$option_name = 'rtp_relic_account_details';
		if ( get_option( $option_name ) !== false ) {
			$main_array = get_option( $option_name );
			$new_value = array(
				'relic_account_name' => $json_data->name,
				'relic_api_key' => $json_data->api_key,
				'relic_id' => $json_data->id,
				'relic_password' => $relic_password
			);
			array_push( $main_array, $new_value );
			update_option( $option_name, $main_array );
		} else {
			$deprecated = null;
			$autoload = 'no';
			$main_array = array(
				array(
					'relic_account_name' => $json_data->name,
					'relic_api_key' => $json_data->api_key,
					'relic_id' => $json_data->id,
					'relic_password' => $relic_password
				)
			);
			add_option( $option_name, $main_array, $deprecated, $autoload );
		}

		// Now create the app
		$app_data = array(
			browser_application => array(
				"name" => $_POST['relic-account-name']
			)
		);
		$app_dataString = json_encode( $app_data );
		var_dump( $app_dataString );
		$app_curl = curl_init();
		curl_setopt_array( $app_curl, array(
			CURLOPT_URL => 'https://api.newrelic.com/v2/browser_applications.json',
			CURLOPT_POST => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPHEADER => array( 'x-api-key:e27051463209a817222b245f66e517a5af3d28b396c1a61' /* . $json_data->api_key */, 'Content-Type:application/json' ),
			CURLOPT_POSTFIELDS => $app_dataString
		) );

		$app_response = curl_exec( $app_curl );
		var_dump( $app_response );
		curl_close( $app_curl );
		$app_json_data = json_decode( $app_response );
		if ( empty( $app_json_data->loader_script ) ) {
			print_r( 'error while creating app' );
			die();
		} else {
			$app_option_name = 'rtp_relic_browser_details';
			if ( get_option( $app_option_name ) !== false ) {
				$main_array = get_option( $app_option_name );
				$new_value = array(
					'relic_app_name' => $app_json_data->name,
					'relic_app_key' => $app_json_data->browser_monitoring_key,
					'relic_app_id' => $app_json_data->id,
					'relic_app_script' => $app_json_data->loader_script
				);
				array_push( $main_array, $new_value );
				update_option( $app_option_name, $main_array );
			} else {
				$deprecated = null;
				$autoload = 'no';
				$main_array = array(
					array(
						'relic_app_name' => $app_json_data->name,
						'relic_app_key' => $app_json_data->browser_monitoring_key,
						'relic_app_id' => $app_json_data->id,
						'relic_app_script' => $app_json_data->loader_script
					)
				);
				add_option( $app_option_name, $main_array, $deprecated, $autoload );
			}
		}
		die();
	}
}

add_action( 'admin_enqueue_scripts', 'rtp_relic_load_js' );
add_action( 'admin_menu', 'rtp_relic_option_page' );
add_action( 'admin_init', 'rtp_relic_register_settings' );


