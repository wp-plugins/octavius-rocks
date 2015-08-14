
<div class="octavius rocks-ab-results-controls">
	<select class="octavius-rocks-select-event-type">
		<option value="">All hits</option>
		<option selected="selected" value="pageview">Pageviews</option>
		<option value="click">Clicks</option>
	</select>
	of
	<select class="octavius-rocks-select-referrer">
		<option value="">All referrers</option>
		<option value="www.facebook.com">Facebook</option>
	</select>
	<a class="button-secondary octavius-rocks-refresh">Refresh</a>
</div>


<div class="octavius-rocks-ab-results" 
data-post-id="<?php echo $post->ID; ?>" 
data-selected-slug="<?php echo $this->variants->get_variant($post->ID); ?>">
	<p>Chart is loading...</p>
</div>