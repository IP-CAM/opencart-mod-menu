<?php 
class ModelDesignMenu extends Model { 
	
	/*
	 * Get all the menus
	 * 
	 * @return - array of menus
	 */
	public function getMenuList()
	{
		$query = "SELECT * FROM `menu`";
		
		$result = $this->db->query($query)->rows;
		
		foreach ($result as $key => $value)
		{
			// Create link to menu
			$result[$key]['href'] = $this->url->link('design/menu', 'token=' . $this->session->data['token'] . '&id=' . $value['id'], 'SSL');
			
			// Checking for active menu
			$active = isset($this->request->get['id']) ? $this->request->get['id'] : $this->getFirstMenuId();
			$active = $active ? $active : $this->getFirstMenuId();
			
			if ($result[$key]['id'] == $active)
				$result[$key]['active'] = 1;
			else
				$result[$key]['active'] = 0;
		}
		
		return $result;
	}
	

	/*
	 * Returns the number of menus
	 */
	public function getMenuNumber()
	{
		$result = $this->db->query("SELECT COUNT(`id`) as 'menus' FROM `menu`")->row;

		return $result['menus'];
	}
	

	/*
	 * Get all the menu items by menu code
	 */
	public function getMenuItems()
	{
		// Id of current menu
		$id = isset($this->request->get['id']) ? $this->request->get['id'] : $this->getFirstMenuId();
		// FIX ---> id = 0, then get first menu id
		$id = $id ? $id : $this->getFirstMenuId();
		
		// Code(identifer) of current menu
		$code = $this->getMenuCodeById($id);
		
		// Get the current lang id
		$lang = $this->getAdminLang();

		// Get all items which code is $code
		// $q = "SELECT * FROM `menu_items` 
		//       WHERE `code` = '" . $code . "' 
		//       ORDER BY `sort_order` ASC";
		$q = "SELECT *
			  FROM `menu_items`
			  JOIN `menu_items_lang` ON(`menu_items`.`id` = `menu_items_lang`.`menu_item_id`)
			  WHERE `menu_items`.`code` = '" . $code . "'
			  AND `menu_items_lang`.`language_id` = '" . (int) $lang['language_id'] . "'
			  ORDER BY `menu_items`.`sort_order` ASC";

		$query = $this->db->query($q)->rows;
		
		// Convert id to menu_item_id
		foreach ($query as $key => $value)
		{
			$query[$key]['id'] = $query[$key]['menu_item_id'];
			unset($query[$key]['menu_item_id']);
		}

		// Build HTML
		$result = $this->menuBuilder($query);
		
		return $result;
	}
	
	
	/*
	 * Get menu item by id
	 * ---
	 * 0 ---> link
	 * 1 ---> category
	 * 2 ---> manufactorer
	 * 3 ---> product
	 */
	public function getMenuItem($id)
	{
		$this->load->model('tool/image');

		$id = (int) $id;
		$data = array();
		$query = "SELECT *
				  FROM `menu_items`
				  JOIN `menu_items_lang` ON (`menu_items`.`id` = `menu_items_lang`.menu_item_id)
				  WHERE `menu_items`.`id` = '" . $id . "' 
				  ORDER BY `menu_items_lang`.`language_id`";

		$results = $this->db->query($query)->rows;

		// Copy needle params
		$data['code']            = $results[0]['code'];
		$data['developer_mode']  = isset($this->session->data['teil_menu_developer_mode']) ? $this->session->data['teil_menu_developer_mode'] : 0;
		$data['href']            = $results[0]['href'];
		$data['self_class']      = $results[0]['self_class'];
		$data['params']          = htmlspecialchars_decode($results[0]['params']);
		$data['id']              = $results[0]['menu_item_id'];
		$data['parent']          = $results[0]['parent'];
		$data['view_type']       = $results[0]['view_type'];
		$data['sort_order']      = $results[0]['sort_order'];
		$data['target']          = $results[0]['target'];
		$data['type']            = $results[0]['type'];
		$data['thumb']           = 0;

		// Set image
		if (!empty($results[0]) && $results[0]['image'] && file_exists(DIR_IMAGE . $results[0]['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($results[0]['image'], 100, 100);
			$data['image'] = $results[0]['image'];
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
			$data['image'] = 0;
		}

		// Each name/title values with diff langs
		foreach ($results as $key => $result)
		{
			$lang_id = 'language_' . $result['language_id'];

			$data['names'][$lang_id] = htmlspecialchars_decode($result['name']);
			$data['titles'][$lang_id] = $result['title'];
		}
		
		return $data;
	}
	
	
	/*
	 * Creates new menu item
	 * @return - new item id
	 */
	public function createMenuItem($menu_id)
	{
		// Id of current menu
		$id = ( ! $menu_id) ? $this->getFirstMenuId() : (int) $menu_id;
		
		// Get the menu code
		$code = $this->getMenuCodeById($id);
		
		// Create new menu item
		$this->db->query("INSERT INTO `menu_items` SET `code` = '" . $code . "', `created` = NOW()");
		
		// Get latest id
		$last_item_id = $this->db->query("SELECT `id` FROM `menu_items` ORDER BY `created` DESC LIMIT 1")->row;
		
		// Langs list
		$languages = $this->db->query("SELECT `language_id` AS 'id' FROM `language`")->rows;

		// Insert names
		foreach ($languages as $language)
		{
			$this->db->query("INSERT INTO `menu_items_lang` 
							  SET 
								  `name` = '-', 
								  `language_id` = '" . $language['id'] . "', 
								  `menu_item_id` = '" . (int) $last_item_id['id'] . "'");
		}
		
		return $last_item_id;
	}
	
	
	/*
	 * Updates menu item by id
	 */
	public function updateMenuItem($data)
	{
		// Validate valiables
		$id = isset($data['id']) ? (int) $data['id'] : 0;
		$itemId = isset($data['itemId']) ? (int) $data['itemId'] : 0;
		$type = isset($data['linkType']) ? (int) $data['linkType'] : 0;
		$target = isset($data['target']) ? (int) $data['target'] : 0;

		$name = isset($data['name']) ? $this->check_string($data['name']) : '';
		$title = isset($data['title']) ? $this->check_string($data['title']) : '';
		$href = isset($data['href']) ? $this->check_string($data['href']) : '/';
		$image = isset($data['image']) ? $this->check_string($data['image']) : '';
		$params = isset($data['params']) ? $this->check_string($data['params']) : '';
		$self_class = isset($data['self_class']) ? $this->check_string($data['self_class']) : '';
		$link_view_type = isset($data['linkViewType']) ? $this->check_string($data['linkViewType']) : '';

		$names = explode('&amp;', $name);
		$titles = explode('&amp;', $title);

		$dataToSend = array();

		switch ($type) {
			case 0:
				// Simple link
				$dataToSend['href'] = $href;
			break;
			case 1:
				// Category link
				$dataToSend['href'] = '/index.php?route=product/category&path=' . $itemId;
			break;
			case 2:
				// Manufacturer link
				$dataToSend['href'] = '/index.php?route=product/manufacturer/product&manufacturer_id=' . $itemId;
			break;
			case 3:
				// Product link
				$dataToSend['href'] = '/index.php?route=product/product&product_id=' . $itemId;
			break;
			case 4:
				// Information link
				$dataToSend['href'] = '/index.php?route=information/information&information_id=' . $itemId;
			break;
		}

		// Update name + href + title
		$query = "UPDATE `menu_items` 
				  SET `type` = '" . $type . "', 
					  `href` = '" . $dataToSend['href'] . "', 
					  `image` = '" . $image . "', 
					  `params` = '" . $params . "', 
					  `self_class` = '" . $self_class . "', 
					  `view_type` = '" . $link_view_type . "', 
					  `target` = '" . $target . "' 
				  WHERE `id` = '" . $id . "'";

		$this->db->query($query);

		/* -Explode name string to languages 
		 * 
		 *   language_1=name1&language_2=name2
		 *   [0] => language_1=name1, [1] => language_2=name2
		 *   [0] => 1 , [1] => name1
		 *
		*/
		foreach ($names as $name)
		{
			$lang_val = explode('=', $name);
			$lang = str_replace('language_', '', $lang_val[0]);
			$val = $lang_val[1];

			$query = "UPDATE `menu_items_lang`
					  SET `name` = '" . $val . "' 
					  WHERE
						  `menu_item_id`  = '" . $id . "' AND
						  `language_id` = '" . $lang . "'";

			$this->db->query($query);
		}

		// -Explode title string to languages 
		foreach ($titles as $title)
		{
			$lang_val = explode('=', $title);
			// var_dump($lang_val);
			// var_dump($title);
			$lang = str_replace('language_', '', $lang_val[0]);
			$val = $lang_val[1];

			$query = "UPDATE `menu_items_lang`
					  SET `title` = '" . $val . "' 
					  WHERE
						  `menu_item_id`  = '" . $id . "' AND
						  `language_id` = '" . $lang . "'";

			$this->db->query($query);
		}
	}
	
	
	/*
	 * Deletes menu item by id
	 */
	public function deleteMenuItem($id)
	{
		$query = "DELETE FROM `menu_items` WHERE `id` = '" . $id . "'";
		$this->db->query($query);
	}


	/*
	 * Updates the menu item order
	 */
	public function updateMenuOrder($results)
	{
		$results = empty($results) ? 0 : $results;
		$results_array = array();
		$sort_order = 0;
		
		// If results exists
		if ($results)
		{
			// Result ---> [ list[1]=1 ], [ list[2]=2 ]
			$exploded = explode('&amp;', $results);
			
			foreach ($exploded as $key => $value)
			{
				// Result ---> [ list[1] ], [ 1 ]
				$exploded_val = explode('=', $value);
				
				// Final results
				$id = preg_replace('|[^\d]|', '', $exploded_val[0]);
				$parent_id = $exploded_val[1];
				$sort_order++;
				
				$results_array[$key]['id'] = $id;
				$results_array[$key]['sort_order'] = $sort_order;
				$results_array[$key]['parent_id'] = ($parent_id === 'null') ? 0 : $parent_id;
			}
			
			// Terrible query loop here !!!
			foreach ($results_array as $key => $value)
			{
				$query = "UPDATE `menu_items` 
						  SET `parent` = '" . $value['parent_id'] . "', 
							  `sort_order` = '" . $value['sort_order'] . " '
						  WHERE `id` = '" . $value['id'] . "'";
				
				$this->db->query($query);
			}
		}
	}
	
	
	/*
	 * Get all the categories, manufacturers, products...
	 */
	public function getCatalogInfo()
	{
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('catalog/information');
		$this->load->model('catalog/manufacturer');


		$result['categories'] = $this->model_catalog_category->getCategories();
		$result['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
		$result['information'] = $this->model_catalog_information->getInformations();
		$result['products'] = $this->model_catalog_product->getProducts();

		return $result;
	}


	/*
	 * Creates new menu
	 */
	public function createMenu()
	{
		$name = $this->request->post['menu_name'];
		$code = $this->request->post['menu_code'];

		$template_wrapper_responsive = $this->db->escape($this->request->post['menu_wrapper_responsive']);
		$template_wrapper = $this->db->escape($this->request->post['menu_wrapper']);

		// Item views templates responsive
		$heading_template_responsive = $this->db->escape($this->request->post['heading_template_responsive']);
		$link_template_responsive = $this->db->escape($this->request->post['link_template_responsive']);
		$banner_template_responsive = $this->db->escape($this->request->post['banner_template_responsive']);
		
		// Item views templates
		$heading_template = $this->db->escape($this->request->post['heading_template']);
		$link_template = $this->db->escape($this->request->post['link_template']);
		$banner_template = $this->db->escape($this->request->post['banner_template']);
		
		
		$que = "INSERT INTO `menu`
				SET 
					`name` = '" . $name . "',
					`code` = '" . $code . "',

					`template_wrapper` = '" . $template_wrapper . "',
					`template_wrapper_responsive` = '" . $template_wrapper_responsive . "',

					`heading_template_responsive` = '" . $heading_template_responsive . "' ,
					`link_template_responsive` = '" . $link_template_responsive . "' ,
					`banner_template_responsive` = '" . $banner_template_responsive . "' ,

					`heading_template` = '" . $heading_template . "' ,
					`link_template` = '" . $link_template . "' ,
					`banner_template` = '" . $banner_template . "'";
		
		$this->db->query($que);
	}
	
	
	/*
	 * Edit menu by code(identifer)
	 */
	public function editMenu()
	{
		// Id of current menu
		$id = isset($this->request->get['id']) ? $this->request->get['id'] : $this->getFirstMenuId();
		// FIX ---> id = 0, then get first menu id
		$id = $id ? $id : $this->getFirstMenuId();
		
		// Get the menu code
		$code = $this->getMenuCodeById($id);
		
		// Information
		$name = $this->request->post['menu_name'];
		$code = $this->request->post['menu_code'];
		$template_wrapper_responsive = $this->db->escape($this->request->post['menu_wrapper_responsive']);
		$template_wrapper = $this->db->escape($this->request->post['menu_wrapper']);

		// Item views templates responsive
		$heading_template_responsive = $this->db->escape($this->request->post['heading_template_responsive']);
		$link_template_responsive = $this->db->escape($this->request->post['link_template_responsive']);
		$banner_template_responsive = $this->db->escape($this->request->post['banner_template_responsive']);
		
		// Item views templates
		$heading_template = $this->db->escape($this->request->post['heading_template']);
		$link_template = $this->db->escape($this->request->post['link_template']);
		$banner_template = $this->db->escape($this->request->post['banner_template']);

		// Set developer mode
		if (isset($this->request->post['menu_name']))
		{
			$this->session->data['teil_menu_developer_mode'] = !!$this->request->post['developer_mode'];
		}
		
		// Query
		$que = "UPDATE `menu`
				SET 
					`name` = '" . $name . "',
					`template_wrapper` = '" . $template_wrapper . "',
					`template_wrapper_responsive` = '" . $template_wrapper_responsive . "',

					`heading_template_responsive` = '" . $heading_template_responsive . "' ,
					`link_template_responsive` = '" . $link_template_responsive . "' ,
					`banner_template_responsive` = '" . $banner_template_responsive . "' ,

					`heading_template` = '" . $heading_template . "' ,
					`link_template` = '" . $link_template . "' ,
					`banner_template` = '" . $banner_template . "' 
				WHERE `code` = '" . $code . "'";
		//echo $que; die();
		$this->db->query($que);
	}
	
	
	/*
	 * Deletes menu by code(identifer)
	 */
	public function deleteMenu($id)
	{
		// Id of current menu
		$id = isset($this->request->get['id']) ? (int) $this->request->get['id'] : $this->getFirstMenuId();
		// FIX ---> id = 0, then get first menu id
		$id = $id ? $id : $this->getFirstMenuId();
		
		// Get the menu code
		$code = $this->getMenuCodeById($id);
		
		$query = "DELETE FROM `menu` WHERE `code` = '" . $code . "'";
		$this->db->query($query);
	}
	
	
	/*
	 * Gets menu information(name, identifer, template...) by menu code
	 */
	public function getMenuInfo()
	{
		// Id of current menu
		$id = isset($this->request->get['id']) ? $this->request->get['id'] : $this->getFirstMenuId();
		// FIX ---> id = 0, then get first menu id
		$id = $id ? $id : $this->getFirstMenuId();
		
		// Get the menu code
		$code = $this->getMenuCodeById($id);
		
		$menuInfo = $this->db->query("SELECT * FROM `menu` WHERE `code` = '" . $code . "'");

		return $menuInfo->row;
	}
	
	
	/*
	 * Checks for existing unique code
	 * 
	 * @return - true if exist
	 * @return - false if not exist
	 */
	public function ckechUniqueCode($code)
	{
		$query = "SELECT `code` FROM `menu` WHERE `code` = '" . $code . "'";
		
		$result = $this->db->query($query)->row;
		
		if (empty($result))
			return false;
		else
			return true;
	}
	
	
	/*
	 * Checks if table `menu` exists.
	 * Otherwise creates it and `menu_items`.
	 * 
	 * @return true - if need to create
	 * @return false - if table exists
	 */
	public function needInstall()
	{
		$query = array();
		// Checks if table `menu` exists
		$query['check'] = "SELECT `TABLE_NAME` 
							FROM `information_schema`.`TABLES` 
							WHERE `information_schema`.`TABLES`.`TABLE_SCHEMA` = '" . DB_DATABASE . "' 
							AND `information_schema`.`TABLES`.`TABLE_NAME` = 'menu'";
		
		// Create table `menu`
		$query['create'] = "CREATE TABLE `menu` (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`code` VARCHAR(20) NOT NULL,
			`name` VARCHAR(50) NOT NULL,
			`template_wrapper` TEXT NOT NULL,
			`template_wrapper_responsive` TEXT NOT NULL,
			`heading_template` TEXT NOT NULL,
			`link_template` TEXT NOT NULL,
			`banner_template` TEXT NOT NULL,
			`heading_template_responsive` TEXT NOT NULL,
			`link_template_responsive` TEXT NOT NULL,
			`banner_template_responsive` TEXT NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE INDEX `code` (`code`)
		)
		COMMENT='Opencart menu manager by Teil(Yurii Krevnyi)'
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		AUTO_INCREMENT=10;";

		// Create table `menu_items`
		$query['create_child'] = "CREATE TABLE `menu_items` ( 
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`code` VARCHAR(20) NOT NULL,
			`href` TEXT NOT NULL,
			`image` TEXT NOT NULL,
			`params` TEXT NOT NULL,
			`view_type` VARCHAR(100) NOT NULL DEFAULT 'heading',
			`self_class` TEXT NOT NULL,
			`parent` INT(11) NOT NULL,
			`target` TINYINT(1) NOT NULL,
			`sort_order` INT(10) NOT NULL,
			`type` TINYINT(1) NOT NULL,
			`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			INDEX `code` (`code`),
			CONSTRAINT `FK__menu` FOREIGN KEY (`code`) REFERENCES `menu` (`code`) ON DELETE CASCADE
		)
		COMMENT='Menu manager by Teil(Yurii Krevnyi)\r\nChildren of menu table'
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		AUTO_INCREMENT=208;";

		// Create table `menu_items_lang`
		$query['create_child_lang'] = "CREATE TABLE `menu_items_lang` (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`menu_item_id` INT(10) NOT NULL,
			`language_id` INT(10) NOT NULL,
			`name` VARCHAR(50) NOT NULL,
			`title` TEXT NOT NULL,
			PRIMARY KEY (`id`),
			INDEX `menu_item_id` (`menu_item_id`),
			CONSTRAINT `FK_menu_items_lang_menu_items` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		AUTO_INCREMENT=147;";
		
		$result = $this->db->query($query['check'])->row;
		
		if (empty($result))
		{
			$this->db->query($query['create']);
			$this->db->query($query['create_child']);
			$this->db->query($query['create_child_lang']);
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	/*
	 * If not defined $_GET['id'], then gets a first menu to display
	 */
	private function getFirstMenuId()
	{
		$result = $this->db->query("SELECT `id` FROM `menu` ORDER BY `id` ASC LIMIT 1")->row;
		
		return $result['id'];
	}
	
	
	/*
	 * Gets menu code by id
	 */
	private function getMenuCodeById($id)
	{
		$result = $this->db->query("SELECT `code` FROM `menu` WHERE `id` = '" . $id . "'")->row;
		
		return $result['code'];
	}
	
	
	/*
	 * Checks if menu item has children
	 */
	private function menuItemHasChildren($rows, $id)
	{
		foreach ($rows as $row)
		{
			if ($row['parent'] == $id)
				return true;
		}
		
		return false;
	}
	
	
	/*
	 * Build menu HTML
	 * 
	 * Array(
	 *  'id' => 1,
	 *  'parent_id' => 0,
	 *  'name' = 'name'
	 * )
	 * 
	 */
	private function menuBuilder($rows, $parent = 0)
	{  
		$this->load->language('module/menu');

		$result = "";
		$structure = "<li id='list_{{id}}'><div><span>{{name}}</span><a href='#' class='delete_menu_item fR'>[ <span>" . $this->language->get('delete_btn_text') . "</span> ]</a><a href='#' class='edit_menu_item fR'>[ <span>" . $this->language->get('edit_btn_text') . "</span> ]</a><img src='/admin/view/image/teilMenuLoader.gif' class='teilLoader fR'></div>";
		
		foreach ($rows as $row)
		{
			if ($row['parent'] == $parent)
			{
				$r = str_replace('{{name}}', $row['name'], $structure);
				$r = str_replace('{{id}}', $row['id'], $r);
				
				$result .= $r;
				
				if ($this->menuItemHasChildren($rows, $row['id']))
				{
					$result .= "<ol>";
					$result .= $this->menuBuilder($rows, $row['id']);
					$result .= "</ol>";
				}
				
				$result .= "</li>";
			}
		}

		return $result;
	}
	
	
	/*
	 * Gets the language id by name(code)
	 */
	private function getLangId($lang_name)
	{
		$query = "SELECT `language_id` 
				  FROM `language` 
				  WHERE `code` = '" . $this->db->escape($lang_name) . "' 
				  LIMIT 1";

		return $this->db->query($query)->row;
	}


	/*
	 * Gets the default admin language
	 */
	public function getAdminLang()
	{
		$result = $this->db->query("SELECT `value` FROM `setting` WHERE `key` = 'config_admin_language'")->row;


		return $this->getLangId($result['value']);
	}

	private function check_string($str)
	{
		$str = trim($str);
		$str = strip_tags($str);
		$str = $this->db->escape($str);
		
		return $str;
	}
}
?>