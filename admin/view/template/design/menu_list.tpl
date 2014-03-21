<?php echo $header; ?>

<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
			<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<?php if ($success) { ?>
		<div class="success">
			<?=$success?>
		</div>
	<?php } ?>
	<div class="box">
	<div class="heading">
		<h1><img src="view/image/category.png" alt=""><?=$heading_title?></h1>
		<div class="buttons">
			<a class="button" title="<?=$new_menu_item_btn_title_text?>" id="create_menu_item"><?=$new_menu_item_btn_text?></a>
			<a class="button" title="<?=$edit_btn_title_text?>" href="<?=$edit_menu?>"><?=$edit_btn_text?></a>
			<a class="button" title="<?=$delete_btn_title_text?>" onclick="onDeleteMenu('<?=$delete_menu?>')"><?=$delete_btn_text?></a>
		</div>
	</div><!-- end .heading -->
	<div class="content">
		<div id="tabs" class="htabs">
				<?php foreach($menus as $menu) { ?>
					<?php if ($menu['active']) { ?>
						<a href="<?=$menu['href']?>" class="selected"><?=$menu['name']?></a>
					<?php } else { ?>
						<a href="<?=$menu['href']?>"><?=$menu['name']?></a>
					<?php } ?>
				<?php } ?>
		<a href="<?=$create_menu?>" title="<?=$create_menu_text?>">+</a>
		</div><!-- end #tabs -->
		<form action="" method="post" enctype="multipart/form-data" id="form">
			<div id="tab-general">
				<ol class="sortable ui-sortable" id="sortable">
					<?=$items;?>
					<input type="hidden" id="results_sortable" name="results">
					<input type="hidden" id="current_menu_id" value="<?=$current_menu_id?>">
					<input type="hidden" id="token_key" value="<?=$token?>">
				</ol><!-- end .sortable -->
			</div><!-- end #tab-general -->
		</form>
	</div><!-- end .content -->
	</div><!-- end .box -->
</div><!-- end #content -->

<script lang="javascript" src="/admin/view/javascript/jquery/teilMenu/nestedsortable.js"></script>
<script lang="javascript" src="/admin/view/javascript/jquery/teilMenu/handlebars.js"></script>
<script lang="javascript" src="/admin/view/javascript/jquery/teilMenu/teilMenu.js"></script>

<script id="menuItemTemplate" type="text/x-handlebars-template">
	<li id="list_{{id}}">
		<div>
			<span><?=$new_menu_item_btn_text?></span>
			<a href="#" class="delete_menu_item fR">[ <span><?=$delete_btn_text?></span> ]</a>
			<a href="#" class="edit_menu_item fR">[ <span><?=$edit_btn_text?></span> ]</a>
			<img src="/admin/view/image/teilMenuLoader.gif" class="teilLoader fR">
		</div>
	</li>
</script>

<script id="menuTemplate" type="text/x-handlebars-template">
	<form id='form_table'>
		<table>
			<tr>
				<td><span class="help-text-container"><?=$item_name_text?><span class="hover-help"><?=$item_name_text_description?></span></span></td>
				<td>
					<?php foreach ($languages as $key => $language) { ?>
						<img src="/image/flags/<?=$language['image']?>">
						<input type='text' value="{{getLangValue item.names 'language_<?=$language['language_id']?>'}}" class="form_name" data-lang="<?=$language['language_id']?>">
						<br>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td><span class="help-text-container"><?=$item_title_text?><span class="hover-help"><?=$item_title_text_description?></span></span></td>
				<td>
					<?php foreach ($languages as $key => $language) { ?>
						<img src="/image/flags/<?=$language['image']?>">
						<input type='text' value="{{getLangValue item.titles 'language_<?=$language['language_id']?>'}}" class="form_title" data-lang="<?=$language['language_id']?>">
						<br>
					<?php } ?>
				</td>
			</tr>

			<tr>
				<td><span class="help-text-container"><?=$text_link_view_type?><span class="hover-help"><?=$text_link_view_type_description?></span></span></td>
				<td>
					<select id='link_view_type' name='link_view_type'>
						<option value='link' class="link" data-show-dependings="depends-on-link"><?=$text_link_view_type_link;?></option>
						<option value='heading' class="heading" data-show-dependings="depends-on-heading"><?=$text_link_view_type_heading;?></option>
						<option value='banner' class="banner" data-show-dependings="depends-on-banner"><?=$text_link_view_type_banner;?></option>
					</select>
				</td>
			</tr>
			
			<tr class="depending-field depends-on-link depends-on-banner">
				<td><span class="help-text-container"><?=$item_link_type_text?><span class="hover-help"><?=$item_link_type_text_description?></span></span></td>
				<td>
					<select id='link_type'>
						<option value='link'><?=$item_link_type_href_text?></option>
						{{#if categoryId}}
							<option selected="selected" value='category'><?=$item_link_type_category_text?></option>
						{{else}}
							<option value='category'><?=$item_link_type_category_text?></option>
						{{/if}}
						
						{{#if manufactorerId}}
							<option selected="selected" value='manufacturer'><?=$item_link_type_manufacturer_text?></option>
						{{else}}
							<option value='manufacturer'><?=$item_link_type_manufacturer_text?></option>
						{{/if}}
						
						{{#if productId}}
							<option selected="selected" value='product'><?=$item_link_type_product_text?></option>
						{{else}}
							<option value='product'><?=$item_link_type_product_text?></option>
						{{/if}}

						{{#if informationId}}
							<option selected="selected" value='information'><?=$item_link_type_information_text?></option>
						{{else}}
							<option value='information'><?=$item_link_type_information_text?></option>
						{{/if}}
					</select>
				</td>
			</tr>

			<tr class="link depending-field depends-on-link depends-on-banner {{#unless link}}hidden{{/unless}}">
				<td><?=$item_link_type_href_text?></td>
				<td><input type="text" value="{{item.href}}" id="form_href"></td>
			</tr>

			<tr class="category depending-field depends-on-banner {{#unless categoryId}}hidden{{/unless}}">
				<td><?=$item_link_type_category_text?></td>
				<td>
					<select>
						{{#each categories}}
							<option value="{{this.category_id}}">{{escaped this.name}}</option>
						{{/each}}
					</select>
				</td>
			</tr>
			
			<tr class="product depending-field depends-on-banner {{#unless productId}}hidden{{/unless}}">
				<td><?=$item_link_type_product_text?></td>
				<td>
					<select>
						{{#each products}}
							<option value="{{this.product_id}}">{{escaped this.name}}</option>
						{{/each}}
					</select>
				</td>
			</tr>
			
			<tr class="manufacturer depending-field depends-on-banner {{#unless manufactorerId}}hidden{{/unless}}">
				<td><?=$item_link_type_manufacturer_text?></td>
				<td>
					<select>
						{{#each manufacturers}}
							<option value="{{this.manufacturer_id}}">{{escaped this.name}}</option>
						{{/each}}
					</select>
				</td>
			</tr>
			
			<tr class="information depending-field depends-on-banner {{#unless informationId}}hidden{{/unless}}">
				<td><?=$item_link_type_information_text?></td>
				<td>
					<select>
						{{#each information}}
							<option value="{{this.information_id}}">{{escaped this.title}}</option>
						{{/each}}
					</select>
				</td>
			</tr>

			<tr class="depending-field depends-on-banner hidden">
				<td><?=$text_image_field;?></td>
				<td>
					<div class="image"><img src="{{item.thumb}}" alt="" id="thumb" /><br /><input type="hidden" name="image" value="{{item.image}}" id="image" />
						<a onclick="image_upload('image', 'thumb');">
							Get image</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb').attr('src', '/image/no_image.jpg'); $('#image').attr('value', '');">Clear
						</a>
					</div>
				</td>
			</tr>

			<tr class="depending-field depends-on-link depends-on-banner">
				<td><span class="help-text-container"><?=$item_target_link_text?><span class="hover-help"><?=$item_target_link_text_description?></span></span></td>
				<td><input type="checkbox" id="form_target" {{pageTarget item.target}}></td>
			</tr>
			
			{{#if item.developer_mode}}
				<tr class="self_class">
					<td><span class="help-text-container"><?=$item_self_class_text?><span class="hover-help"><?=$item_self_class_text_description?></span></span></td>
					<td><input type="text" value="{{item.self_class}}" id="form_self_class"></td>
				</tr>

				<tr class="params">
					<td><span class="help-text-container"><?=$item_link_type_params_text?><span class="hover-help"><?=$item_link_type_params_text_description?></span></span></td>
					<td><input type="text" value="{{item.params}}" id="form_params"></td>
				</tr>
			{{/if}}

			<tr>
				<td><a href='#' id='form_save' class='button'><?=$save_btn_text?></a><img src="/admin/view/image/teilMenuLoader.gif" class="teilLoader fL" style="display: none; "></td>
				<td><a href='#' id='form_calcel' class='button'><?=$cancel_btn_text?></a></td>
			</tr>
		</table>
	</form>
</script>

<script>
	(function(){
		// Sortable menu
		$('.sortable').nestedSortable({
			disableNesting: 'no-nest',
			forcePlaceholderSize: true,
			handle: 'div',
			helper: 'clone',
			items: 'li',
			maxLevels: 4,
			opacity: .6,
			placeholder: 'placeholder',
			revert: 250,
			tabSize: 25,
			tolerance: 'pointer',
			toleranceElement: '> div',
			stop: function() {
				var results = $('#sortable').nestedSortable('serialize');
			
				$.ajax({
					url: '/admin/index.php?route=design/menu_ajax&token=<?=$token?>',
					type: 'POST',
					dataType: 'json',
					data: {
						method: 'save_order',
		                data: results
					},
				})
				.fail(function() {
					alert('Error while saving menu order!');
				});
			}
		});

		// Language tabs
		$('#languages a').tabs();
		
		// Edit form initialization
		teilMenu.init({
			container: $('#sortable'),
			template: $('#menuTemplate').html(),
			itemTemplate: $('#menuItemTemplate').html(),
			menuId: '<?=$menuId?>',
			adminLang: '<?=$adminLang?>',
			token: '<?=$token?>',
			delete_menu_confirm: '<?=$delete_menu_confirm_text?>',
			delete_menu_item_confirm: '<?=$delete_menu_item_confirm_text?>'
		});
	})();

	// Image upload funtion
	function image_upload(field, thumb) {
		$('#dialog').remove();
		
		$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
		
		$('#dialog').dialog({
			title: 'File manager',
			close: function (event, ui) {
				if ($('#' + field).attr('value')) {
					$.ajax({
						url: 'index.php?route=common/filemanager/image&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).attr('value')),
						dataType: 'text',
						success: function(text) {
							$('#' + thumb).replaceWith('<img src="' + text + '" alt="" id="' + thumb + '" />');
						}
					});
				}
			},  
			bgiframe: false,
			width: 800,
			height: 400,
			resizable: false,
			modal: false
		});
	};
</script>

<?php echo $footer; ?>