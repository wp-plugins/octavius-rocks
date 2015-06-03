<?php 
/**
 * @author Palasthotel by Edward Bock <eb@palasthotel.de>
 * @copyright Copyright (c) 2015, Palasthotel
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package Palasthotel\Octavius-Rocks
 */
?>
<div class="grid-box<?php echo ($this->style)? " ".$this->style." ": " "; echo implode($this->classes," ")?>">
	<?php
	if ($this->title!=""){

		if ($this->titleurl !=""){
		?>
			<h2 class="grid-box-title"><a href="<?php echo $this->titleurl?>"><?php echo $this->title?></a></h2>
		<?php }else{?>
			<h2 class="grid-box-title"><?php echo $this->title?></h2>
		<?php }?>
	<?php }?>
	
	<?php if($this->prolog != "") { ?>
	<div class="grid-box-prolog">
		<?php echo $this->prolog?>
	</div>
	<?php } ?>
	<h1>hier plugin</h1>
	<ul class="octavius-top-live-list">
		<?php 
		echo $content;
		?>
	</ul>
	<?php if($this->epilog != ""){ ?>
	<div class="grid-box-epilog">
		<?php echo $this->epilog?>
	</div>
	<?php } ?>

  	<?php
	if ($this->readmore!=""){?>
	<a href="<?php echo $this->readmoreurl?>" class="grid-box-readmore-link"><?php echo $this->readmore?></a>
	<?php }?>
	
</div>
