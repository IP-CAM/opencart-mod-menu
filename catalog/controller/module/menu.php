<?php  
class ControllerModuleMenu extends Controller {
	protected function index($setting) {
		static $module = 0;
		
		// Load
		$this->load->model('design/menu');

		// Database
		$menu_code = $this->model_design_menu->getMenuCode($setting['menu_id']);
		

		// Locat variables
		$this->data['module'] = $module++;
		$this->data['menu_code'] = $menu_code;

		// krevnyi
		$this->data['position'] = $setting['position'];
		
		// Render
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/menu.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/menu.tpl';
		} else {
			$this->template = 'default/template/module/menu.tpl';
		}
		
		$this->render();
	}
}
?>