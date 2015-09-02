<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>?page=octavius-rocks&amp;tab=ab">
	
	<h2>Settings</h2>
	<table class="form-table">
		<tbody>
			<tr>
				<tr>
					<th scope="row"><?php echo __("Turn on A/B tests for posts"); ?></th>
					<td><input type="checkbox" id="octavius_rocks_ab_enabled" value="1"
					<?php if($ab_enabled) echo "checked='checked'"; ?>
					name="octavius_rocks_ab_enabled"> <?php 
					if($ab_enabled) {
						echo __("Enabled"); 
					} else {
						echo __("Disabled");
					}
					?></td>
				</tr>
			</tr>
		</tbody>
	</table>
	
	<h2>Variants</h2>
	<?php
	if($ab_enabled):
	?>
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
	<?php
	else:
		?>
	<p><?php echo __("Enable A/B Tests to see more options"); ?></p>
	<?php
	endif; 
	?>
	

	<?php submit_button("Save"); ?>
</form>