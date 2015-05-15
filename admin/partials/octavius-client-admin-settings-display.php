<?php

/**
 * template file for Octavius Settings Page
 *
 * $submit_button_text		Text for submit button
 * $submit_button 			Submit button identifier
 */

?>

<div class="wrap">
	<h2>Octavius 2.0 Settings</h2>
	<form method="post" action="<?php echo $_SERVER["PHP_SELF"]."?page=".$this->plugin_name; ?>">

		<table class="form-table">
			<tr>
				<th scope="row"><label for="<?php echo $api_key_id; ?>">API Key</label></th>
				<td><input type="text" id="<?php echo $api_key_id; ?>" name="<?php echo $api_key_id; ?>" value="<?php echo $api_key; ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo $server_id; ?>">Server</label></th>
				<td><input type="text" id="<?php echo $server_id; ?>" name="<?php echo $server_id; ?>" value="<?php echo $server; ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo $port_id; ?>">Port</label></th>
				<td><input type="text" id="<?php echo $port_id; ?>" name="<?php echo $port_id; ?>" value="<?php echo $port; ?>" class="regular-text" /></td>
			</tr>
		</table>

		<?php submit_button(); ?>

	</form>
</div>