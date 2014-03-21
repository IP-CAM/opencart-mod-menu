<?php echo $header; ?>

<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
            <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <div class="box">
	<div class="heading">
	    <h1><img src="view/image/category.png" alt=""><?=$heading_title?></h1>
	    <div class="buttons">
            <a class="button" href="<?=$create_menu?>"><?=$create_menu_text?></a>
	    </div>
	</div><!-- end .heading -->
	<div class="content">
	    <h2><?=$welcome_heading_text?></h2>
	    <h3><a href="<?=$create_menu?>" title="<?=$create_menu_text?>"><?=$welcome_text?></a></h3>
	</div><!-- end .content -->
    </div><!-- end .box -->
</div><!-- end #content -->

<?php echo $footer; ?>