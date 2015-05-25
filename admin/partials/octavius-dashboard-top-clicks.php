<table class="wp-list-table widefat fixed striped">
	<thead>
		<tr>
			<th scope="col" id="col_content" class="manage-column">Inhalt</th>
			<th scope="col" id="col_5_minutes" class="manage-column">
			<select id="octavius-top-links-step">
				<option value="live">Live</option>
				<option value="minutes_5">5 Minuten</option>
				<option value="hour" selected="selected">60 Minuten</option>
				<option value="day">24 Stunden</option>
				<option value="week">7 Tage</option>
				<option value="month">Monat</option>
			</select><div id="octavius-loading" class="spinner"></div></th>	
		</tr>
	</thead>

	<tbody id="octavius-top-links" data-wp-lists="list:wp_list_text_link">
		
	</tbody>
</table>
<input id="octavius-limit" type="hidden" value="<?php echo $limit; ?>" />
<input id="edit-post-link-template" value="<?php echo admin_url(); ?>post.php?action=edit&amp;post=" type="hidden" />
<p id="octavius-timestamp"></p>
