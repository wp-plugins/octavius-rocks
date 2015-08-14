<?php 
/**
 * @author Palasthotel <rezeption@palasthotel.de>
 * @copyright Copyright (c) 2014, Palasthotel
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package Palasthotel\Octavius
 */
?>
<div class="grid-box-editmode">
	<p>Top clicks last 24 hours</p>
	<p>Limit: <?php echo $this->content->limit; ?> Offset: <?php echo $this->content->offset; ?></p>
	<?php
	if($this->content->cat_name){
		?>
		<p>Cat: <?php echo $this->content->cat_name; ?></p>
		<?php
	}
	?>
</div>
