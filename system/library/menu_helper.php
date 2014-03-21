<?php 

class MenuHelper
{
	static private $template_wrapper = "<ul>{{content}}</ul>";
	static private $template_wrapper_responsive = "<ul class='responsive'>{{content}}</ul>";

	static private $heading_template = "<li class='{{self_class}}'>{{name}}</li>";
	static private $link_template = "<li class='{{self_class}}'><a href='{{name}}'>{{name}}</a></li>";
	static private $banner_template = "<li class='{{self_class}}'><a href='{{name}}'>{{name}}</a></li>";

	static private $heading_template_responsive = "<li class='{{self_class}}'>{{name}}</li>";
	static private $link_template_responsive = "<li class='{{self_class}}'><a href='{{name}}'>{{name}}</a></li>";
	static private $banner_template_responsive = "<li class='{{self_class}}'><a href='{{name}}'>{{name}}</a></li>";


	/**
	 * Simply get template by its key OR return the default value
	 *
	 * @return mixed
	 */
	static public function getTemplate($menuInfo, $key)
	{
		if (isset($menuInfo[$key]) AND ! empty($menuInfo[$key]) AND $menuInfo[$key])
		{
			return $menuInfo[$key];
		}
		else
		{
			return static::$$key;
		}
	}
}