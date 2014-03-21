<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button">Сохранить</a><a href="<?php echo $cancel; ?>" class="button">Отмена</a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table id="module" class="list">
          <thead>
            <tr>
              <td class="left">Меню:</td>
              <td class="left">Схема:</td>
              <td class="left">Расположение:</td>
              <td class="left">Статус:</td>
              <td class="right">Порядок сортировки:</td>
              <td></td>
            </tr>
          </thead>
          <?php $module_row = 0; ?>
          <?php foreach ($modules as $key => $module): ?>
	          <tbody id="module-row<?php echo $module_row; ?>">
	            <tr>
					<td class="left">
						<select name="menu_module[<?=$key?>][menu_id]">
							<?php foreach ($menu_list as $menu_item) { ?>
								<?php if ($module['menu_id'] == $menu_item['id']) { ?>
									<option value="<?=$menu_item['id']?>" selected="selected"><?=$menu_item['name']?></option>
								<?php } else { ?>
									<option value="<?=$menu_item['id']?>"><?=$menu_item['name']?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</td>
					<td class="left">
						<select name="menu_module[<?=$key?>][layout_id]">
							<?php foreach ($layouts as $layout) { ?>
								<?php if ($module['layout_id'] == $layout['layout_id']) { ?>
									<option value="<?=$layout['layout_id']?>" selected="selected"><?=$layout['name']?></option>
								<?php } else { ?>
									<option value="<?=$layout['layout_id']?>"><?=$layout['name']?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</td>
					<td class="left">
						<select name="menu_module[<?=$key?>][position]">
							<?php if ($module['position'] == "content_top") { ?>
								<option value="content_top" selected="selected">Верх страницы</option>
							<?php } else { ?>
								<option value="content_top">Верх страницы</option>
							<?php } ?>

							<?php if ($module['position'] == "content_bottom") { ?>
								<option value="content_bottom" selected="selected">Низ страницы</option>
							<?php } else { ?>
								<option value="content_bottom">Низ страницы</option>
							<?php } ?>

							<?php if ($module['position'] == "column_left") { ?>
								<option value="column_left" selected="selected">Левая колонка</option>
							<?php } else { ?>
								<option value="column_left">Левая колонка</option>
							<?php } ?>

							<?php if ($module['position'] == "column_right") { ?>
								<option value="column_right" selected="selected">Правая колонка</option>
							<?php } else { ?>
								<option value="column_right">Правая колонка</option>
							<?php } ?>
						</select>
					</td>
					<td class="left">
						<select name="menu_module[<?=$key?>][status]">
							<?php if ($module['status']) { ?>
								<option value="1" selected="selected">Включено</option>
								<option value="0">Отключено</option>
							<?php } else { ?>
								<option value="1">Включено</option>
								<option value="0" selected="selected">Отключено</option>
							<?php } ?>
						</select>
					</td>
					<td class="right"><input type="text" name="menu_module[<?=$key?>][sort_order]" value="<?=isset($module['sort_order']) ? $module['sort_order'] : 0?>" size="3"></td>
					<td class="left"><a class="button" onclick="$('#module-row<?php echo $module_row; ?>').remove();">Удалить</a></td>
				</tr>
	          </tbody>
	          <?php $module_row++; ?>
	        <?php endforeach ?>
          <tfoot>
            <tr>
              <td colspan="5"></td>
              <td class="left"><a onclick="addModule();" class="button">Добавить</a></td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
var module_row = <?php echo $module_row; ?>;

function addModule() {	
	html  = '<tbody id="module-row' + module_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><select name="menu_module[' + module_row + '][menu_id]">';
	<?php foreach ($menu_list as $menu_item) { ?>
	html += '      <option value="<?=$menu_item['id']?>" selected="selected"><?=$menu_item['name']?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="menu_module[' + module_row + '][layout_id]">';
	<?php foreach ($layouts as $layout) { ?>
	html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
	<?php } ?>
	html += '    </select></td>';
	html += '    <td class="left"><select name="menu_module[' + module_row + '][position]">';
	html += '      <option value="content_top">Верх Страници</option>';
	html += '      <option value="content_bottom">Низ страници</option>';
	html += '      <option value="column_left">Левая колонка</option>';
	html += '      <option value="column_right">Правая колонка</option>';
	html += '    </select></td>';
	html += '    <td class="left"><select name="menu_module[' + module_row + '][status]">';
    html += '      <option value="1" selected="selected">Включено</option>';
    html += '      <option value="0">Отключено</option>';
    html += '    </select></td>';
	html += '    <td class="right"><input type="text" name="menu_module[' + module_row + '][sort_order]" value="" size="3" /></td>';
	html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button">Удалить</a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#module tfoot').before(html);
	
	module_row++;
}
//--></script> 
<?php echo $footer; ?>