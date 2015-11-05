<?php

/**
 * template file for Octavius Settings Page
 *
 * $submit_button_text		Text for submit button
 * $submit_button 			Submit button identifier
 */

?>

<div class="wrap">
	<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>?page=octavius-rocks&amp;tab=server">

		<table class="form-table">
			<tr>
				<th scope="row"><label for="<?php echo $api_key_id; ?>">API Key</label></th>
				<td><input type="text" id="<?php echo $api_key_id; ?>" name="<?php echo $api_key_id; ?>" value="<?php echo $api_key; ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="<?php echo $server_id; ?>">Server</label></th>
				<td><input type="text" id="<?php echo $server_id; ?>" name="<?php echo $server_id; ?>" value="<?php echo $server; ?>" class="regular-text" /></td>
			</tr>
		</table>

		<?php submit_button(); ?>

	</form>
</div>