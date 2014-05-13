<div class="menu">
	<h2><?php Menu::getMenuName($menu_code); ?></h2>

	<div class="visible-lg visible-md menu">
		<?php Menu::call($menu_code)?>
	</div>
	<?php /* ?>
	<!-- <div class="visible-sm visible-xs menu" id="dl-menu">
		<button class="dl-trigger">Open Menu</button>
		<?php Menu::call($menu_code, 'responsive')?>
	</div> -->
	<?php */ ?>
	<div id="dl-menu" class="visible-sm visible-xs dl-menuwrapper">
    	<button class="dl-trigger">Open Menu</button>
    <?php Menu::call($menu_code, 'dl-menud')?>
	</div><!-- /dl-menuwrapper -->

</div>