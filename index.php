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
    
    // set password to new account
    
    $relic_password = 'rtcamp.com@1';
    $option_name = 'rtp_relic_account_details';
    $app_option_name = 'rtp_relic_browser_details';
    
    // if the account data is already stored then delete the account
    if ( get_option( $option_name ) !== false ) {
	// curl request to remove account
	if ( isset( $_POST['rtp-relic-account-id'] ) ) {
	    $account_id = $_POST['rtp-relic-account-id'];
	    $delete_curl = curl_init();
	    curl_setopt_array( $delete_curl, array( CURLOPT_URL => 'https://staging.newrelic.com/api/v2/partners/191/accounts/'.$account_id,
		CURLOPT_CUSTOMREQUEST => "DELETE",
		CURLOPT_HTTPHEADER => array( 'x-api-key:0118286cc87aca4eef6723d567a94b3916167fc4cf91177', 'Content-Type:application/json' )
	    ) );
	    curl_exec( $delete_curl );
	    curl_close( $delete_curl );
	    
	    // delete the stored data
	    
	    delete_option($option_name);
	    delete_option($app_option_name);
	}
    } else {
	
	// always set allow_api_access to true while creating account
	// start of API 1
	
	$data = array(
	    account => array(
		"name" => $_POST['relic-account-name'],
		"allow_api_access" => true,
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
	
	// for this api data is to be pass in json
	
	$dataString = json_encode( $data );
	$curl = curl_init();
	curl_setopt_array( $curl, array( CURLOPT_URL => 'https://staging.newrelic.com/api/v2/partners/191/accounts',
	    CURLOPT_POST => 1,
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_HTTPHEADER => array( 'x-api-key:0118286cc87aca4eef6723d567a94b3916167fc4cf91177', 'Content-Type:application/json' ),
	    CURLOPT_POSTFIELDS => $dataString
	) );

	$response = curl_exec( $curl );
	curl_close( $curl );
	$json_data = json_decode( $response );
	if ( $json_data->error != '' ) {
	    add_settings_error( 'relic_options', 'relic_options_error', $json_data->error );
	} else {
	    
	    // store the received data
	    
	    $deprecated = null;
	    $autoload = 'no';
	    $main_array = array(
		'relic_account_name' => $json_data->name,
		'relic_api_key' => $json_data->api_key,
		'relic_id' => $json_data->id,
		'relic_password' => $relic_password
	    );
	    add_option( $option_name, $main_array, $deprecated, $autoload );

	    // end of API 1
	    // Now create the browser app
	    
	    $app_data = array(
		browser_application => array(
		    "name" => $_POST['relic-account-name']
		)
	    );
	    $app_dataString = json_encode( $app_data );
	    $app_curl = curl_init();
	    curl_setopt_array( $app_curl, array( CURLOPT_URL => 'https://staging-api.newrelic.com/v2/browser_applications.json',
		CURLOPT_POST => 1,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HTTPHEADER => array( 'x-api-key:' . $json_data->api_key, 'Content-Type:application/json' ),
		CURLOPT_POSTFIELDS => $app_dataString
	    ) );

	    $app_response = curl_exec( $app_curl );
	    curl_close( $app_curl );
	    $app_json_data = json_decode( $app_response );
	    if ( empty( $app_json_data->browser_application->loader_script ) ) {
		add_settings_error( 'relic_options', 'relic_options_error', $app_json_data->error->title );
	    } else {
		
		// stored the received data
		
		$deprecated = null;
		$autoload = 'no';
		$main_array = array(
		    'relic_app_name' => $app_json_data->browser_application->name,
		    'relic_app_key' => $app_json_data->browser_application->browser_monitoring_key,
		    'relic_app_id' => $app_json_data->browser_application->id,
		    'relic_app_script' => $app_json_data->browser_application->loader_script
		);
		add_option( $app_option_name, $main_array, $deprecated, $autoload );
	    }
	}
    }
}

function _insert_head_n() {
    
    // fetched the script stored in metadata and insert in head
    
    $app_option_name = 'rtp_relic_browser_details';
    if ( get_option( $app_option_name ) !== false ) {
	$relic_browser_options_data = get_option( $app_option_name );
	$output = $relic_browser_options_data['relic_app_script'];
	echo $output;
    }
}

add_action( 'wp_head', '_insert_head_n', 1 );
add_action( 'admin_enqueue_scripts', 'rtp_relic_load_js' );
add_action( 'admin_menu', 'rtp_relic_option_page' );
add_action( 'admin_init', 'rtp_relic_register_settings' );


