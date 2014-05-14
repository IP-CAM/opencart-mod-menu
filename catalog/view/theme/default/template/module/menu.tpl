<div class="menu-wrap">
	<?php /* ?>

	<h2><?php Menu::getMenuName($menu_code); ?></h2>
	*/ ?>
	<div class="visible-lg visible-md visible-sm menu clearfix">
		<?php Menu::call($menu_code)?>
	</div>
	<?php /* ?>
	<!-- <div class="visible-sm visible-xs menu" id="dl-menu">
		<button class="dl-trigger">Open Menu</button>
		<?php Menu::call($menu_code, 'responsive')?>
	</div> -->
	<?php */ ?>
	<div id="dl-menu" class="visible-xs dl-menuwrapper">
    	<button class="dl-trigger">Open Menu</button>
    <?php Menu::call($menu_code, 'dl-menu')?>
	</div><!-- /dl-menuwrapper -->

</div>