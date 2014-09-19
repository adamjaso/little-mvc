<?php
/**
 * Author:    Adam Jaso <mkltwain@gmail.com>
 *
 */ 


class MvcMenu {

    const NONFUNC = '/[\W\-]+/i';

    public $menuArgs = array();
    public $submenuArgs = array();

    //$page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position
    public function __construct($page_title, $menu_title, $capability, $menu_slug, $icon_url = '', $position = '') {
        $this->menuArgs = func_get_args();
    }


    //$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function
    public function addSubmenu($parent_slug, $page_title, $menu_title, $capability, $menu_slug) {
        $this->submenuArgs[] = func_get_args();
    }

    public function init(MvcController $controller) {
        $func = 'menu_' . preg_replace(self::NONFUNC, '', $this->menuArgs[3]);
        if (method_exists($controller, $func)) {
            $args = $this->menuArgs;
            array_splice($args, 4, 0, array(array($controller, $func)));
            call_user_func_array('add_menu_page', $args);
            foreach ($this->submenuArgs as $args) {
                $func = 'submenu_' . preg_replace(self::NONFUNC, '', $args[4]);
                if (method_exists($controller, $func)) {
                    array_splice($args, 5, 0, array(array($controller, $func)));
                    call_user_func_array('add_submenu_page', $args);
                }
            }
        }
    }
} 