<?php 
class ControllerDesignMenu extends Controller { 
 
	public function index() {
            $this->load->model('design/menu');
            $this->load->model('localisation/language');
            $this->load->language('module/menu');

            $this->template = 'design/menu_list.tpl';
            
            // If form submited
            if ($this->request->server['REQUEST_METHOD'] == 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            {
                // ID to redirect the menu that was updated
                $current_menu = empty($this->request->get['id']) ? 0 : $this->request->get['id'];
                
                $this->model_design_menu->updateMenuOrder($this->request->post['results']);
                $this->session->data['success'] = 'Меню успешно отредактировано!';
                $this->redirect($this->url->link('design/menu', 'token=' . $this->session->data['token'] . '&id=' . $current_menu, 'SSL'));
            }
            
            // DOM elements
            $this->document->addStyle('view/stylesheet/menu/menu.css');
            $this->document->setTitle('Менеджер меню');
            
            // Breadcrumbs
            $this->data['breadcrumbs'] = array();
            $this->data['breadcrumbs'][] = array(
                'text' => 'Главная',
                'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false
            );
            $this->data['breadcrumbs'][] = array(
                'text' => 'Менеджер меню',
                'href' => $this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );
            
            // Success and warning
            $this->getActionStatuses();
            
            // Install
            if ($this->model_design_menu->needInstall())
                $this->redirect($this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'));
            
            // If there isn't any menus
            if ( ! $this->model_design_menu->getMenuNumber())
                $this->redirect($this->url->link('design/menu/welcome', 'token=' . $this->session->data['token'], 'SSL'));

            // Database
            $menus = $this->model_design_menu->getMenuList();
            $items = $this->model_design_menu->getMenuItems();
            $languages = $this->model_localisation_language->getLanguages();
            $adminLang = $this->model_design_menu->getAdminLang();

            // Local variables
            $this->data['current_menu_id'] = empty($this->request->get['id']) ? 0 : $this->request->get['id'];
            $this->data['create_menu'] = $this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL');
            $this->data['delete_menu'] = $this->url->link('design/menu/delete', 'token=' . $this->session->data['token'] . '&id=' . $this->data['current_menu_id'], 'SSL');
            $this->data['edit_menu'] = $this->url->link('design/menu/edit', 'token=' . $this->session->data['token'] . '&id=' . $this->data['current_menu_id'], 'SSL');
            $this->data['cancel'] = $this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL');
            $this->data['token'] = $this->session->data['token'];
            $this->data['languages'] = $languages;
            $this->data['menus'] = $menus;
            $this->data['items'] = $items;
            $this->data['adminLang'] = $adminLang['language_id'];
            $this->data['menuId'] = isset($this->request->get['id']) ? $this->request->get['id'] : 0;

            // Multi languages
            $this->data['heading_title'] = $this->language->get('heading_title');
            $this->data['menu_saved_text'] = $this->language->get('menu_saved_text');
            $this->data['menu_name_error_text'] = $this->language->get('menu_name_error_text');
            $this->data['menu_identifer_error_text'] = $this->language->get('menu_identifer_error_text');
            $this->data['item_name_text'] = $this->language->get('item_name_text');
            $this->data['item_title_text'] = $this->language->get('item_title_text');
            $this->data['item_link_type_text'] = $this->language->get('item_link_type_text');
            $this->data['item_link_type_href_text'] = $this->language->get('item_link_type_href_text');
            $this->data['item_link_type_params_text'] = $this->language->get('item_link_type_params_text');
            $this->data['item_self_class_text'] = $this->language->get('item_self_class_text');
            $this->data['item_link_type_category_text'] = $this->language->get('item_link_type_category_text');
            $this->data['item_link_type_product_text'] = $this->language->get('item_link_type_product_text');
            $this->data['item_link_type_manufacturer_text'] = $this->language->get('item_link_type_manufacturer_text');
            $this->data['item_link_type_information_text'] = $this->language->get('item_link_type_information_text');
            $this->data['item_target_link_text'] = $this->language->get('item_target_link_text');
            $this->data['save_btn_text'] = $this->language->get('save_btn_text');
            $this->data['cancel_btn_text'] = $this->language->get('cancel_btn_text');
            $this->data['delete_btn_text'] = $this->language->get('delete_btn_text');
            $this->data['edit_btn_text'] = $this->language->get('edit_btn_text');
            $this->data['new_menu_item_btn_text'] = $this->language->get('new_menu_item_btn_text');
            $this->data['save_btn_title_text'] = $this->language->get('save_btn_title_text');
            $this->data['delete_btn_title_text'] = $this->language->get('delete_btn_title_text');
            $this->data['edit_btn_title_text'] = $this->language->get('edit_btn_title_text');
            $this->data['new_menu_item_btn_title_text'] = $this->language->get('new_menu_item_btn_title_text');
            $this->data['create_menu_text'] = $this->language->get('create_menu_text');
            $this->data['delete_menu_item_confirm_text'] = $this->language->get('delete_menu_item_confirm_text');
            $this->data['delete_menu_confirm_text'] = $this->language->get('delete_menu_confirm_text');
            $this->data['text_menu_template_static'] = $this->language->get('text_menu_template_static');
            $this->data['text_menu_template_responsive'] = $this->language->get('text_menu_template_responsive');
            
            $this->data['text_image_field'] = $this->language->get('text_image_field');
            $this->data['text_developer_mode'] = $this->language->get('text_developer_mode');
            $this->data['text_link_view_type'] = $this->language->get('text_link_view_type');
            $this->data['text_link_view_type_link'] = $this->language->get('text_link_view_type_link');
            $this->data['text_link_view_type_heading'] = $this->language->get('text_link_view_type_heading');
            $this->data['text_link_view_type_banner'] = $this->language->get('text_link_view_type_banner');

            $this->data['item_name_text_description'] = $this->language->get('item_name_text_description');
            $this->data['item_title_text_description'] = $this->language->get('item_title_text_description');
            $this->data['text_link_view_type_description'] = $this->language->get('text_link_view_type_description');
            $this->data['item_link_type_text_description'] = $this->language->get('item_link_type_text_description');
            $this->data['item_link_type_href_text_description'] = $this->language->get('item_link_type_href_text_description');
            $this->data['item_link_type_params_text_description'] = $this->language->get('item_link_type_params_text_description');
            $this->data['item_self_class_text_description'] = $this->language->get('item_self_class_text_description');
            $this->data['item_target_link_text_description'] = $this->language->get('item_target_link_text_description');
            $this->data['text_developer_mode_description'] = $this->language->get('text_developer_mode_description');

            // Render
            $this->children = array(
                'common/header',
                'common/footer'
            );
            
            $this->response->setOutput($this->render());
	}
        
        
        /*
         * Creates new menu
         */
        public function create()
        {
            $this->load->model('design/menu');
            $this->load->language('module/menu');
            $this->template = 'design/menu_form.tpl';
            
            // Request
            if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateNewMenu())
            {
                $this->model_design_menu->createMenu();
                $this->redirect($this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'));
            }
            
            // DOM elements
            $this->document->addStyle('view/stylesheet/menu/menu.css');
            $this->document->setTitle('Создать новое меню');
            
            // Breadcrumbs
            $this->data['breadcrumbs'] = array();
            $this->data['breadcrumbs'][] = array(
                'text' => 'Главная',
                'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false
            );
            $this->data['breadcrumbs'][] = array(
                'text' => 'Менеджер меню',
                'href' => $this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );
            $this->data['breadcrumbs'][] = array(
                'text' => 'Создать меню',
                'href' => $this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );
            
            // Success and warning
            $this->getActionStatuses();
            
            // Database
            $menus = $this->model_design_menu->getMenuList();
            
            // Local variables
            $this->data['create_menu'] = $this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL');
            $this->data['action'] = $this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL');
            $this->data['delete_menu'] = $this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL');
            $this->data['cancel'] = $this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL');
            $this->data['heading_title'] = 'Heading title';
            $this->data['menus'] = $menus;
            
            // Multi languages
            $this->data['heading_title'] = $this->language->get('heading_title');
            $this->data['menu_saved_text'] = $this->language->get('menu_saved_text');
            $this->data['menu_name_error_text'] = $this->language->get('menu_name_error_text');
            $this->data['menu_identifer_error_text'] = $this->language->get('menu_identifer_error_text');
            $this->data['edit_name_text'] = $this->language->get('edit_name_text');
            $this->data['edit_identifer_text'] = $this->language->get('edit_identifer_text');
            $this->data['edit_wrapper_text'] = $this->language->get('edit_wrapper_text');
            $this->data['edit_template'] = $this->language->get('edit_template');
            $this->data['heading_view_template_text'] = $this->language->get('heading_view_template_text');
            $this->data['link_view_template_text'] = $this->language->get('link_view_template_text');
            $this->data['banner_view_template_text'] = $this->language->get('banner_view_template_text');
            $this->data['view_templates_text'] = $this->language->get('banner_view_template_text');
            $this->data['edit_identifer_hint_text'] = $this->language->get('edit_identifer_hint_text');
            $this->data['edit_wrapper_hint_text'] = $this->language->get('edit_wrapper_hint_text');
            $this->data['edit_template_hint_text'] = $this->language->get('edit_template_hint_text');
            $this->data['save_btn_text'] = $this->language->get('save_btn_text');
            $this->data['cancel_btn_text'] = $this->language->get('cancel_btn_text');
            $this->data['delete_btn_text'] = $this->language->get('delete_btn_text');
            $this->data['edit_btn_text'] = $this->language->get('edit_btn_text');
            $this->data['new_menu_item_btn_text'] = $this->language->get('new_menu_item_btn_text');
            $this->data['save_btn_title_text'] = $this->language->get('save_btn_title_text');
            $this->data['delete_btn_title_text'] = $this->language->get('delete_btn_title_text');
            $this->data['edit_btn_title_text'] = $this->language->get('edit_btn_title_text');
            $this->data['new_menu_item_btn_title_text'] = $this->language->get('new_menu_item_btn_title_text');
            $this->data['create_menu_text'] = $this->language->get('create_menu_text');
            $this->data['text_developer_mode'] = $this->language->get('text_developer_mode');
            $this->data['text_menu_template_static'] = $this->language->get('text_menu_template_static');
            $this->data['text_menu_template_responsive'] = $this->language->get('text_menu_template_responsive');

            // Default varialbles values
            $this->data['show_identifer'] = true;
            $this->data['default_name'] = isset($menuInfo['name']) ? $menuInfo['name'] : '';
            $this->data['default_identifer'] = isset($menuInfo['code']) ? $menuInfo['code'] : '';
            
            // Menu wrappers
            $this->data['default_template_wrapper_responsive'] = MenuHelper::getTemplate(null, 'template_wrapper_responsive');
            $this->data['default_template_wrapper'] = MenuHelper::getTemplate(null, 'template_wrapper');

            // Item view templates
            $this->data['heading_template'] = MenuHelper::getTemplate(null, 'heading_template');
            $this->data['link_template'] = MenuHelper::getTemplate(null, 'link_template');
            $this->data['banner_template'] = MenuHelper::getTemplate(null, 'banner_template');

            // Item view templates responsive
            $this->data['heading_template_responsive'] = MenuHelper::getTemplate(null, 'heading_template_responsive');
            $this->data['link_template_responsive'] = MenuHelper::getTemplate(null, 'link_template_responsive');
            $this->data['banner_template_responsive'] = MenuHelper::getTemplate(null, 'banner_template_responsive');
        
            // If user dont wanna to use developer mode ---> add class `hidden`
            if (isset($this->session->data['teil_menu_developer_mode']) AND 
                $this->session->data['teil_menu_developer_mode'])
            {
                $this->data['dev_mode_class'] = '';
            }
            else
            {
                $this->data['dev_mode_class'] = 'hidden';
            }
            
            // Render
            $this->children = array(
                'common/header',
                'common/footer'
            );
            
            $this->response->setOutput($this->render());
        }
        
        
        /*
         * Edit menu information
         */
        public function edit()
        {
            $this->load->model('design/menu');
            $this->load->language('module/menu');
            $this->template = 'design/menu_form.tpl';
            
            // Request
            if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateNewMenu())
            {
                $this->model_design_menu->editMenu();
                
                // Curent menu id to redirect
                $current_menu_id = empty($this->request->get['id']) ? 0 : $this->request->get['id'];
                $this->redirect($this->url->link('design/menu', 'token=' . $this->session->data['token'] . '&id=' . $current_menu_id, 'SSL'));
            }
            
            // DOM elements
            $this->document->addStyle('view/stylesheet/menu/menu.css');
            $this->document->setTitle('Редактрование меню');
            
            // Breadcrumbs
            $this->data['breadcrumbs'] = array();
            $this->data['breadcrumbs'][] = array(
                'text' => 'Главная',
                'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false
            );
            $this->data['breadcrumbs'][] = array(
                'text' => 'Менеджер меню',
                'href' => $this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );
            $this->data['breadcrumbs'][] = array(
                'text' => 'Редактирование меню',
                'href' => $this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );
            
            // Success and warning
            $this->getActionStatuses();
            
            // Database
            $menus = $this->model_design_menu->getMenuList();
            $menuInfo = $this->model_design_menu->getMenuInfo();
            
            // Local variables
            $this->data['current_menu_id'] = empty($this->request->get['id']) ? 0 : $this->request->get['id'];
            $this->data['create_menu'] = $this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL');
            $this->data['cancel'] = $this->url->link('design/menu', 'token=' . $this->session->data['token'] . '&id=' . $this->data['current_menu_id'], 'SSL');
            $this->data['heading_title'] = 'Heading title';
            $this->data['menus'] = $menus;
            
            // Default varialbles values
            $this->data['show_identifer'] = false;
            $this->data['default_name'] = isset($menuInfo['name']) ? $menuInfo['name'] : '';
            $this->data['default_identifer'] = isset($menuInfo['code']) ? $menuInfo['code'] : '';

            // Menu wrappers
            $this->data['default_template_wrapper_responsive'] = empty($menuInfo['template_wrapper_responsive']) ? "<li>\n\t<a href='{{href}}' target='{{target}}' title='{{title}}'>{{name}}</a>\n</li>" : $menuInfo['template_wrapper_responsive'];
            $this->data['default_template_wrapper'] = empty($menuInfo['template_wrapper']) ? "<ul>{{content}}</ul>" : $menuInfo['template_wrapper'];

            // Item view templates
            $this->data['heading_template'] = empty($menuInfo['heading_template']) ? null : $menuInfo['heading_template'];
            $this->data['link_template'] = empty($menuInfo['link_template']) ? null : $menuInfo['link_template'];
            $this->data['banner_template'] = empty($menuInfo['banner_template']) ? null : $menuInfo['banner_template'];

            // Item view templates responsive
            $this->data['heading_template_responsive'] = empty($menuInfo['heading_template_responsive']) ? null : $menuInfo['heading_template_responsive'];
            $this->data['link_template_responsive'] = empty($menuInfo['link_template_responsive']) ? null : $menuInfo['link_template_responsive'];
            $this->data['banner_template_responsive'] = empty($menuInfo['banner_template_responsive']) ? null : $menuInfo['banner_template_responsive'];
            

            // Multi languages
            $this->data['heading_title'] = $this->language->get('heading_title');
            $this->data['menu_saved_text'] = $this->language->get('menu_saved_text');
            $this->data['menu_name_error_text'] = $this->language->get('menu_name_error_text');
            $this->data['menu_identifer_error_text'] = $this->language->get('menu_identifer_error_text');
            $this->data['edit_name_text'] = $this->language->get('edit_name_text');
            $this->data['edit_identifer_text'] = $this->language->get('edit_identifer_text');
            $this->data['edit_wrapper_text'] = $this->language->get('edit_wrapper_text');
            $this->data['edit_template'] = $this->language->get('edit_template');
            $this->data['heading_view_template_text'] = $this->language->get('heading_view_template_text');
            $this->data['link_view_template_text'] = $this->language->get('link_view_template_text');
            $this->data['banner_view_template_text'] = $this->language->get('banner_view_template_text');
            $this->data['view_templates_text'] = $this->language->get('view_templates_text');
            $this->data['edit_identifer_hint_text'] = $this->language->get('edit_identifer_hint_text');
            $this->data['edit_wrapper_hint_text'] = $this->language->get('edit_wrapper_hint_text');
            $this->data['edit_template_hint_text'] = $this->language->get('edit_template_hint_text');
            $this->data['save_btn_text'] = $this->language->get('save_btn_text');
            $this->data['cancel_btn_text'] = $this->language->get('cancel_btn_text');
            $this->data['delete_btn_text'] = $this->language->get('delete_btn_text');
            $this->data['edit_btn_text'] = $this->language->get('edit_btn_text');
            $this->data['new_menu_item_btn_text'] = $this->language->get('new_menu_item_btn_text');
            $this->data['save_btn_title_text'] = $this->language->get('save_btn_title_text');
            $this->data['delete_btn_title_text'] = $this->language->get('delete_btn_title_text');
            $this->data['edit_btn_title_text'] = $this->language->get('edit_btn_title_text');
            $this->data['new_menu_item_btn_title_text'] = $this->language->get('new_menu_item_btn_title_text');
            $this->data['create_menu_text'] = $this->language->get('create_menu_text');
            $this->data['text_developer_mode'] = $this->language->get('text_developer_mode');
            $this->data['text_menu_template_static'] = $this->language->get('text_menu_template_static');
            $this->data['text_menu_template_responsive'] = $this->language->get('text_menu_template_responsive');

            // If user dont wanna to use developer mode ---> add class `hidden`
            if (isset($this->session->data['teil_menu_developer_mode']) AND 
                $this->session->data['teil_menu_developer_mode'])
            {
                $this->data['dev_mode_class'] = '';
            }
            else
            {
                $this->data['dev_mode_class'] = 'hidden';
            }

            // Render
            $this->children = array(
                'common/header',
                'common/footer'
            );
            
            $this->response->setOutput($this->render());
        }
        
        
        /*
         * Deletes menu
         */
        public function delete()
        {
            $this->load->model('design/menu');
            
            // Variables
            $id = empty($this->request->get['id']) ? 0 : (int) $this->request->get['id'];
            
            $this->model_design_menu->deleteMenu($id);
            $this->session->data['success'] = 'Меню успешно удалено!';
            $this->redirect($this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'));
        }
        

        /*
         * Welcome screen(no menus)
         */
        public function welcome()
        {
            $this->load->model('design/menu');
            $this->load->language('module/menu');
            $this->template = 'design/menu_welcome.tpl';
            
            // DOM elements
            $this->document->addStyle('view/stylesheet/menu/menu.css');
            $this->document->setTitle('Менеджер меню');
            
            // Breadcrumbs
            $this->data['breadcrumbs'] = array();
            $this->data['breadcrumbs'][] = array(
                'text' => 'Главная',
                'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => false
            );
            $this->data['breadcrumbs'][] = array(
                'text' => 'Менеджер меню',
                'href' => $this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => ' :: '
            );
            
            // Install
            if ($this->model_design_menu->needInstall())
                $this->redirect($this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'));
            
            // If there is some menus
            if ($this->model_design_menu->getMenuNumber())
                $this->redirect($this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'));

            // Local variables
            $this->data['create_menu'] = $this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL');
            
            // Multi languages
            $this->data['heading_title'] = $this->language->get('heading_title');
            $this->data['welcome_text'] = $this->language->get('welcome_text');
            $this->data['welcome_heading_text'] = $this->language->get('welcome_heading_text');
            $this->data['create_menu_text'] = $this->language->get('create_menu_text');

            // Render
            $this->children = array(
                'common/header',
                'common/footer'
            );
            
            $this->response->setOutput($this->render());
        }


        /*
         * Deletes menu item
         */
        public function deleteMenuItem()
        {
            $this->load->model('design/menu');
            
            // Variables
            $id = empty($this->request->get['id']) ? 0 : (int) $this->request->get['id'];
            
            $this->model_design_menu->deleteMenuItem($id);
            $this->session->data['success'] = 'Пункт меню успешно удален!';
            $this->redirect($this->url->link('design/menu', 'token=' . $this->session->data['token'], 'SSL'));
        }
        
        
        /*
         * Validates user input for creating new menu
         */
        private function validateNewMenu()
        {
            // Clear all html tags and whitespaces
            $this->request->post['menu_name'] = trim(strip_tags($this->request->post['menu_name']));
            $this->request->post['menu_code'] = trim(strip_tags($this->request->post['menu_code']));
            
            $name_len = strlen($this->request->post['menu_name']);
            // If edit page 'menu_code' doesnt support
            $code_len = strlen($this->request->post['menu_code']);
            
            // Check for name length
            if ($name_len < 3 || $name_len > 50)
            {
                $this->session->data['warning'] = 'Название меню должно быть от 3 до 50 символов!';
                
                // Curent menu id to redirect
                $current_menu_id = empty($this->request->get['id']) ? 0 : $this->request->get['id'];
                
                // If edit page - redirect
                if ($this->request->get['route'] === 'design/menu/edit')
                    $this->redirect($this->url->link('design/menu/edit', 'token=' . $this->session->data['token'] . '&id=' . $current_menu_id, 'SSL'));
                else
                    $this->redirect($this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL'));
                
                return false;
            }
            
            // We shouldn't check code on edit page
            if ($this->request->get['route'] === 'design/menu/edit')
            {
                $this->session->data['success'] = 'Меню успешно отредактировано!';
                return true;
            }
            
            // Check for code length
            if ($code_len < 3 || $code_len > 20)
            {
                $this->session->data['warning'] = 'Идентификатор должен быть от 3 до 20 символов!';
                $this->redirect($this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL'));
                return false;
            }
            
            // Checks for existing unique code
            if ($this->model_design_menu->ckechUniqueCode($this->request->post['menu_code']))
            {
                $this->session->data['warning'] = 'Меню с таким идентифокатором уже существует!';
                $this->redirect($this->url->link('design/menu/create', 'token=' . $this->session->data['token'], 'SSL'));
                return false;
            }
            else
            {
                $this->session->data['success'] = 'Меню успешно создано!';
                return true;
            }
        }
        
        
        /*
         * Get success and warning statuses
         */
        private function getActionStatuses()
        {
            // Success
            if (isset($this->session->data['success']))
            {
                $this->data['success'] = $this->session->data['success'];
                $this->session->data['success'] = '';
            }
            else $this->data['success'] = '';
            
            // Warning
            if (isset($this->session->data['warning']))
            {
                $this->data['warning'] = $this->session->data['warning'];
                $this->session->data['warning'] = '';
            }
            else $this->data['warning'] = '';
        }
}
?>