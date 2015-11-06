<input type="hidden" id="octavius-rocks-ab-limit" value="<?php echo $limit; ?>" />
<div id="octavius-rocks-ab-button-template" style="visibility: hidden"><?php echo submit_button(__("Use variant"), "small"); ?></div>
<table class="wp-list-table widefat fixed striped">
	<thead>
		<tr>
			<th scope="col" class="manage-column">Inhalt</th>
			<th scope="col" class="manage-column">Variante</th>
			<th scope="col" class="manage-column"><div id="octavius-loading-ab" class="spinner"></div></th>
		</tr>
	</thead>

	<tbody id="octavius-ab-results" data-wp-lists="list:wp_list_text_link">
	</tbody>
</table>
