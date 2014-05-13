<?php

class Menu {

    static private 
            $code = 0,
            $responsive = 0,
            $templates = array(),
            $wrapper = "",
            $currentLanguageId = 0;
    
    
    /*
     * Get menu items by code(identifer)
     * 
     * ! Initialization function
     * ! USAGE
     * ! Menu::call('mainmenu', 'responsive');
     * 
     * @param $layout_type Type of the menu to generate
     * 
     */
    static public function call($menu_code, $layout_type = 0)
    {
        self::$responsive = !! $layout_type;
        self::$code = $menu_code;
        self::$templates = self::getTemplates();
        self::$wrapper = self::getWrapper();

        self::$currentLanguageId = self::getCurrentLanguageId();
        
        if (self::check($menu_code))
        {
            // WHERE `menu_items`.`code` = '" . $menu_code . "' AND `menu_items_lang`.`language_id` = '" . self::$currentLanguageId . "'
            $result = self::query("SELECT * FROM `menu_items_lang` 
                                    JOIN `menu_items` ON (`menu_items_lang`.`menu_item_id` = `menu_items`.`id`)
                                    WHERE `menu_items`.`code` = '" . $menu_code . "' AND `menu_items_lang`.`language_id` = '" . self::$currentLanguageId . "'
                                    ORDER BY `menu_items`.`sort_order`")->rows;

            echo self::renderMenu($result);
        }
        else
        {
            print("Menu '<b>" . self::$code . "</b>' doesn't exist!");
        }
    }

    /*
     * Get menu name by code(identifer)
     * 
     * ! Initialization function
     * ! USAGE
     * ! Menu::getMenuName('mainmenu');
     */
    static public function getMenuName($menu_code)
    {
        self::$code = $menu_code;
        
        if (self::check($menu_code))
        {
            $result = self::query("SELECT `name` FROM `menu` 
                                    WHERE `code` = '" . $menu_code . "'")->row;

            echo $result['name'];
        }
        else
        {
            print("Menu '<b>" . self::$code . "</b>' doesn't exist!");
        }
    }
    
    
    /*
     * Wraps menu with template_wrapper
     */
    static private function renderMenu($result)
    {
        $wrapper = (self::$responsive) ? self::$wrapper['template_wrapper_responsive'] : self::$wrapper['template_wrapper'];
        $html = self::buildMenu($result);
        
        // Wrap result with template wrapper
        $result = str_replace('{{content}}', $html, $wrapper);
        
        return htmlspecialchars_decode($result);
    }
    
    
    /*
     * Builds tree menu
     */
    static private function buildMenu($rows, $parent = 0)
    {
        $result = "";
        // Replace last </li> to avoid errors
        $template = "";
        
        foreach ($rows as $row)
        {
            if (self::$responsive)
            {
                $template = self::$templates[ $row['view_type'] . '_template_responsive' ];
            }
            else
            {
                $template = self::$templates[ $row['view_type'] . '_template' ];
            }

            // $structure = $template;
            $structure = str_replace(htmlspecialchars('</li>'), '', $template);

            // Define how mutch children has current menu item
            $num_children = 0;


            foreach ($rows as $menu_item)
            {
                if ($menu_item['parent'] == $row['id'])
                {
                    $num_children++;
                }
            }


            if ($row['parent'] == $parent)
            {
                // Append class depending on link view type
                $row['self_class'] .= " " . $row['view_type'];

                // If link name is empty (at the responsive menu) ---> continue
                if (self::$responsive AND (! isset($row['name']) OR ! trim($row['name'])))
                {
                    continue;
                }

                // Replacing template values
                $r = str_replace('{{id}}', $row['id'], $structure);
                $r = str_replace('{{num_children}}', $num_children, $r);
                $r = str_replace('{{name}}', $row['name'], $r);
                $r = str_replace('{{href}}', $row['href'], $r);
                $r = str_replace('{{params}}', $row['params'], $r);
                $r = str_replace('{{self_class}}', $row['self_class'], $r);
                $r = str_replace('{{title}}', $row['title'], $r);
                $r = str_replace('{{target}}', $row['target'] ? '_blank' : '_self', $r);

                if ($row['image'])
                {
                    $r = str_replace('{{image}}', "<img style='float: left;' src='/image/" . $row['image'] . "'>", $r);
                }
                else
                {
                    $r = str_replace('{{image}}', "", $r);
                }

                // Get active href + params
                if ($row['href'] . $row['params'] == $_SERVER["REQUEST_URI"])
                // if (preg_match('/^' . preg_quote($row['href'] . $row['params'], '/') . '/i', $_SERVER["REQUEST_URI"]))
                {
                    $r = str_replace('{{active}}', 'active', $r);
                }
                // Get active ONLY href
                elseif ($row['href'] == $_SERVER["REQUEST_URI"])
                // elseif (preg_match('/^' . preg_quote($row['href'], '/') . '/i', $_SERVER["REQUEST_URI"]))
                {
                    $r = str_replace('{{active}}', 'active', $r);
                }
                // If page is news ( if in page exists '/news/' --> this page is news page :) )
                elseif (preg_match('/news/i', $row['href']) AND preg_match('/^' . preg_quote($row['href'], '/') . '/i', $_SERVER["REQUEST_URI"]))
                {
                    $r = str_replace('{{active}}', 'active', $r);
                }
                // Remove {{active}} label
                else
                {
                    $r = str_replace('{{active}}', '', $r);
                }
                
                $result .= $r;
                
                // Set diffrent layout to static/responsive menus
                if (self::menuItemHasChildren($rows, $row['id']))
                {
                    if (self::$responsive)
                    {
                        // Static view
                        $result .= "<ul class='dl-submenu'>";
                        $result .= self::buildMenu($rows, $row['id']);
                        $result .= "</ul>";
                    }
                    else
                    {
                        // Responsive view
                        $result .= "<div><ul>";
                        $result .= self::buildMenu($rows, $row['id']);
                        $result .= "</ul></div>";
                    }
                }
                
                $result .= "</li>";
            }
        }
        
        return $result;
    }

    
    /*
     * Checks if menu item has children
     */
    static private function menuItemHasChildren($rows, $id)
    {
        foreach ($rows as $row)
        {
            if ($row['parent'] == $id)
                return true;
        }
        
        return false;
    }
    

    /*
     * Checks for existing menu code(identifer)
     */
    static public function check($menu_code)
    {
        $query = "SELECT `code` FROM `menu` WHERE `code` = '" . $menu_code . "'";
        
        $result = self::query($query)->row;
        
        if (empty($result))
            return false;
        else
            return true;
    }


    /*
     * Gets current catalog language_id by lang_code OR by default admin values
     */
    static private function getLanguageId($lang_code = NULL)
    {
        if ( ! isset($lang_code))
        {
            $result = self::query("SELECT `value` FROM `setting` WHERE `key` = 'config_language' LIMIT 1")->row;
            return $result['value'];
        }
        else
        {
            $result = self::query("SELECT `language_id` FROM `language` WHERE `code` = '" . mysql_real_escape_string($lang_code) . "' LIMIT 1")->row;
            return $result['language_id'];
        }
    }


    /*
     * Gets current catalog language OR $_cookies lang
     */
    static private function getCurrentLanguageId()
    {
        $lang_id = isset($_SESSION['language']) ? self::getLanguageId($_SESSION['language']) : self::getLanguageId();

        return (int) $lang_id;
    }

    
    /*
     * Gets menu template to render
     */
    static private function getTemplates()
    {
        $template_field_name = '`heading_template`, `link_template`, `banner_template`';

        // Check if user want to generate responsive menu structure
        if (self::$responsive)
        {
            $template_field_name = '`heading_template_responsive`, `link_template_responsive`, `banner_template_responsive`';
        }

        return self::query("SELECT " . $template_field_name . " FROM `menu` WHERE `code` = '" . self::$code . "'")->row;
    }
    
    
    /*
     * Gets menu wrapper to render
     */
    static private function getWrapper()
    {
        $template_field_name = 'template_wrapper';

        // Check if user want to generate responsive menu structure
        if (self::$responsive)
        {
            $template_field_name = 'template_wrapper_responsive';
        }

        return self::query("SELECT `" . $template_field_name . "` FROM `menu` WHERE `code` = '" . self::$code . "'")->row;
    }
    
    
    /*
     * Query a string
     */
    static private function query($sql) {
        $resource = mysql_query($sql);

        if ($resource)
        {
            if (is_resource($resource)) 
            {
                $i = 0;

                $data = array();

                while ($result = mysql_fetch_assoc($resource)) {
                        $data[$i] = $result;

                        $i++;
                }

                mysql_free_result($resource);

                $query = new stdClass();
                $query->row = isset($data[0]) ? $data[0] : array();
                $query->rows = $data;
                $query->num_rows = $i;

                unset($data);

                return $query;  
            }
            else 
            {
                return true;
            }
        }
        else
        {
            echo 'Error!\r\n' . $sql;

            exit();
        }
    }
    
}