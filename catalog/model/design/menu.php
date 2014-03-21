<?php
class ModelDesignMenu extends Model {


	/*
	 * Gets menu code by id
	 */
	public function getMenuCode($menu_id)
	{
		$id = (int) $menu_id;

		$result =  $this->db->query("SELECT `code` FROM `menu` WHERE `id` = '" . $id . "'")->row;

		return $result['code'];
	}
}
?>