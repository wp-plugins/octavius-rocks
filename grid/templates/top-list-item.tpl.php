
<?php

$attr = octavius_client_data_builder(array("content_id" => get_the_ID()));

?>

<li class="octavius-top-item" <?php echo $attr; ?>>
	<h2><a href="<?php echo get_the_permalink(); ?>" title="<?php echo get_the_title(); ?>"><?php echo get_the_title(); ?></a></h2>
</li>