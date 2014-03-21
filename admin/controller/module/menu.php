<?php  
class ControllerModuleMenu extends Controller {
	public function index() {
		$this->load->model('design/menu');
		$this->load->model('design/layout');
		$this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/banner', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/banner', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		// Request
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_setting_setting->editSetting('menu', $this->request->post);

			$this->session->data['success'] = $this->language->get('Изменения сохранены!');
			$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		// Database
		$menu_list = $this->model_design_menu->getMenuList();
		$layouts = $this->model_design_layout->getLayouts();
		$settings  = $this->model_setting_setting->getSetting('menu');

		// Local variables
		$this->data['action'] = $this->url->link('module/menu', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['menu_list'] = $menu_list;
		$this->data['layouts'] = $layouts;
		$this->data['modules'] = (isset($settings['menu_module']) AND $settings['menu_module']) ? $settings['menu_module'] : array();
		// print_r($this->data['menu_list']);
		
		// Template
		$this->template = 'module/menu.tpl';
		
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}
}
?>