<!-- paginate? -->
<div class="oc-report-filter">
	<label class="oc-report-filter-label">Inhalt</label>
	<select class="oc-report-filter-input" id="oc-report-filter-content">
		<!-- TODO Dynamically add available Post-Types -->
		<option value="" selected="selected">Alle</option>
		<option value="post">Post</option>
		<option value="page">Page</option>
		<option value="category">Kategorie</option>
		<option value="tag">Tag</option>
		<option value="home">Home</option>
	</select>
	<label class="oc-report-filter-label">Event</label>
	<select class="oc-report-filter-input" id="oc-report-filter-event">
		<option value="pageview" selected="selected">Pageview</option>
		<option value="rendered">Rendered</option>
		<option value="click">Click</option>
	</select>
	<label class="oc-report-filter-label">Zeitraum</label>
	<select class="oc-report-filter-input" id="oc-report-filter-step">
		<option value="minutes_5" selected="selected">diese 5 Minuten</option>
		<option value="minutes_5-1">letzte 5 Minuten</option>
		<option value="hour">diese Stunde</option>
		<option value="hour-1">letzte Stunde</option>
		<option value="day">heute</option>
		<option value="day-1">gestern</option>
		<?php /*TODO week and month and live option value="week">diese Woche</option>
		<option value="week-1">letzte Woche</option*/?>
	</select>
	<label class="oc-report-filter-label">Referrer</label>
	<select class="oc-report-filter-input" id="oc-report-filter-referrer">
		<option value="">All referrers</option>
		<option value="www.facebook.com">Facebook</option>
	</select>
	<!-- TODO limit -->
	<input type="submit" id="oc-report-filter-send" name="send" class="oc-report-filter-input button button-primary" value="Absenden">
	<div id="octavius-loading" class="spinner"></div>
</div>
<table class="wp-list-table widefat fixed striped" id="oc-result-table">
	<thead>
		<tr>
			<th scope="col" id="col_content" class="manage-column">Inhalt</th>
			<th scope="col" id="col_5_minutes" class="manage-column">Hits</th>
		</tr>
	</thead>
	<tbody id="octavius-top-links" data-wp-lists="list:wp_list_text_link">
	</tbody>
</table>
<!--input id="octavius-limit" type="hidden" value="<?php echo $limit; ?>" />
<input id="edit-post-link-template" value="<?php echo admin_url(); ?>post.php?action=edit&amp;post=" type="hidden" />
<p id="octavius-timestamp"></p-->
