<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>?page=octavius-rocks&amp;tab=ab">
	
	<h2>Variants</h2>
	<table>
		<tbody>
			<tr>
				<th>Slug</th>
				<th>Name</th>
				<th>Delete</th>
			</tr>
			<?php
			$i = 0;
			$new = 0;
			foreach ($all as $slug => $name) {
				?>
				<tr>
					<td><input class="form-text" name="octavius_rocks[<?php echo $i; ?>][slug]" type="text" value="<?php echo $slug; ?>"></td>
					<td><input class="form-text" name="octavius_rocks[<?php echo $i; ?>][name]" type="text" value="<?php echo $name; ?>"></td>
					<td><input type="checkbox" name="octavius_rocks[<?php echo $i; ?>][delete]" value="1"></td>
				</tr>
				<?php
				$i++;
				$new = $i;
			}
			?>
			<tr>
				<td><input class="form-text" name="octavius_rocks[<?php echo $new; ?>][slug]" type="text"></td>
				<td><input class="form-text" name="octavius_rocks[<?php echo $new; ?>][name]" type="text"></td>
				<td></td>
			</tr>
		</tbody>
	</table>


	<h2>Settings</h2>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="octavius_rocks_ab_min_hits">Show on Dashboard after minimun of x Hits</label></th>
				<td><input type="text" id="octavius_rocks_ab_min_hits" name="octavius_rocks_ab_min_hits" value="<?php echo $min_hits; ?>" 
				class="regular-text" placeholder="Number of Hits"></td>
			</tr>
		</tbody>
	</table>

	<?php submit_button("Save"); ?>
</form>