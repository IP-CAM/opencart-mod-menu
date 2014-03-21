<?php 
class ControllerDesignMenuAjax extends Controller { 
 
	public function index() {
            $this->load->model('design/menu');
            $this->template = 'design/menu_list.tpl';
            

            $method = $this->request->post['method'];
            
            switch ($method) {
                case 'get_item_info':
                    $result = $this->model_design_menu->getMenuItem($this->request->post['id']);

                    echo json_encode($result);
                break;

                case 'get_catalog_info':
                    $result = $this->model_design_menu->getCatalogInfo();

                    echo json_encode($result);
                break;

                case 'create_menu_item':
                    $result = $this->model_design_menu->createMenuItem($this->request->post['menuId']);

                    echo json_encode($result);
                break;
                
                case 'update_item_info':
                    $result = $this->model_design_menu->updateMenuItem($this->request->post);
                break;
                
                case 'delete_menu_item':
                    $result = $this->model_design_menu->deleteMenuItem($this->request->post['id']);
                break;
                
                case 'save_data':
                    $result = $this->model_design_menu->updateMenuItem($this->request->post['data']);
                break;

                case 'save_order':
                    $result = $this->model_design_menu->updateMenuOrder($this->request->post['data']);
                break;
            }
            
	}
}