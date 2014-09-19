<?php
/**
 * Author:    Adam Jaso <mkltwain@gmail.com>
 *
 */ 


class MvcAdmin extends Mvc {

    public $menuPages = array();
    public $settings = array();
    public $controller;
//    public $ajax;

    public function __construct(MvcController $controller) {
        $this->controller = $controller;
    }

    public function build() {
        Mvc::log('registering activation hook ' . Mvc::$pluginFile);
        register_activation_hook(Mvc::$pluginFile, array($this, 'on_activation'));
        register_deactivation_hook(Mvc::$pluginFile, array($this, 'on_deactivation'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('updated_option', array($this, 'updated_option'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    public function addMenuPage(MvcMenu $menuPage) {
        $this->menuPages[] = $menuPage;
    }

    public function addSetting($option_group, $option_name, $sanitize_callback = '') {
        $this->settings[] = array($option_group, $option_name, $sanitize_callback);
    }

    public function admin_menu() {
        $this->controller->admin_menu();
        foreach ($this->menuPages as $page) {
            /** @var $page MvcMenu */
            $page->init($this->controller);
        }
    }

    public function admin_init() {
        $this->setupAdminAjax();
        $this->addLocalJs('admin', 'Nonces', $this->nonces);
        $this->loadJsCss();
        $this->registerSettings();
        $this->controller->admin_init();
    }

    public function admin_notices() {
        if (method_exists($this->controller, 'admin_notices')) {
            $this->controller->admin_notices();
        }
    }

    public function admin_enqueue_scripts() {

    }

    public function updated_option($optionName) {
        $this->controller->updated_option($optionName);
    }

    public function on_activation() {
        if (current_user_can('administrator')) {

            $data = get_plugin_data(Mvc::$pluginFile);
            Mvc::$pluginVersion = $data['Version'];
            Mvc::log($data);

            $version = Mvc::option('version', '');
            if ($version != Mvc::$pluginVersion) {
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                $this->controller->on_upgrade($version, Mvc::$pluginVersion);
                Mvc::option('version', Mvc::$pluginVersion);
            }
            $this->controller->on_activation();
        }
    }

    public function on_deactivation() {
        $this->controller->on_deactivation();
    }

    private function registerSettings() {
        foreach ($this->settings as $set) {
            call_user_func_array('register_setting', $set);
        }
    }

    private function setupAdminAjax() {
        $actions = get_class_methods($this->controller);
        foreach ($actions as $act) {
            if (strpos($act, 'admin_ajax_') === 0) {
                $this->nonces[$act] = wp_create_nonce($act);
                add_action('wp_ajax_' . Mvc::$appPrefix . '-' . $act, array($this->controller, 'runAjax'));
            }
        }
    }


//    protected function admin_ajax() {
//        $actions = array_filter(get_class_methods($this->ajaxController), function($value) {
//            return preg_match('/(authorize|__construct)/i', $value) == 1;
//        });
//        $this->ajax = new $this->ajaxController();
//        foreach ($actions as $act) {
//            add_action('wp_ajax_' . self::$appPrefix . '-' . $act, array($this->ajax, 'run'));
//        }
//    }
} 