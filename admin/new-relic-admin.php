<div class="rtp-relic-settings-page">
	<div class="relic-page-title">New Relic Browser</div>
	<h3>Do you have a New Relic account?</h3>
	<div class="rtp-relic-checkbox">
		<input type="radio" class="rtp-relic-radio" name="rtp-relic-account-avaiable" id="rtp-relic-yes" value="yes" />
		<label for="rtp-relic-yes">Yes</label>
	</div>
	<div class="rtp-relic-checkbox">
		<input type="radio" class="rtp-relic-radio" name="rtp-relic-account-avaiable" id="rtp-relic-no" value="no" />
		<label for="rtp-relic-no">No</label>
	</div>
	<div class="rtp-relic-form">
		<form id="rtp-relic-add-account" action="options.php" method="POST" enctype="multipart/form-data">
			<?php
			settings_fields( 'relic_options_settings' );
			?>
			<div class="relic-form-field">
				<label for="relic-account-name">Account Name:</label>
				<input type="text" name="relic-account-name" id="relic-account-name" value="<?php echo $_SERVER['SERVER_NAME']; ?>">
				<span id="relic-account-name_error" class="form_error"></span>
			</div>
			<div class="relic-form-field">
				<label for="relic-account-email">Email:</label>
				<input type="email" name="relic-account-email" id="relic-account-email">
				<span id="relic-account-email_error" class="form_error"></span>
			</div>
			<div class="relic-form-field">
				<label for="relic-first-name">First Name:</label>
				<input type="text" name="relic-first-name" id="relic-first-name">
				<span id="relic-first-name_error" class="form_error"></span>
			</div>
			<div class="relic-form-field">
				<label for="relic-last-name">Last Name:</label>
				<input type="text" name="relic-last-name" id="relic-last-name">
				<span id="relic-last-name_error" class="form_error"></span>
			</div>
			<div class="relic-form-field">
				<input type="submit" value="Submit" name="rtp-relic-form-submit">
			</div>
		</form>
	</div>
</div>
<?php
$option_name = 'rtp_relic_account_details';
if ( get_option( $option_name ) !== false ) {
	$relic_options_data = get_option( $option_name );
	?>
	<div class="rtp-relic-settings-page-details">
		<h3>Account details:</h3>
		<?php
		foreach ( $relic_options_data as $value ) {
			?>
			<p> api key = <?php echo $value['relic_api_key']; ?></p>	
			<p> api id = <?php echo $value['relic_id']; ?></p>
			<?php
		}
		?>
	</div>
<?php
}
$app_option_name = 'rtp_relic_browser_details';
if ( get_option( $app_option_name ) !== false ) {
	$relic_browser_options_data = get_option( $app_option_name );
	?>
	<div class="rtp-relic-settings-page-details">
		<h3>Account details:</h3>
		<?php
		foreach ( $relic_browser_options_data as $value ) {
			?>
			<p> browser api key = <?php echo $value['relic_app_key']; ?></p>	
			<p> browser api id = <?php echo $value['relic_app_id']; ?></p>
			<p> browser script = <?php echo $value['relic_app_script']; ?></p>
			<?php
		}
		?>
	</div>
<?php } ?>

