<?php
/**
 * Author:    Adam Jaso <mkltwain@gmail.com>
 *
 */ 


class Mvc {

    private static $instance;

    private static $classesDir;

    public static $appPrefix = 'mvc';

    public static $cssVersion = 1;

    public static $jsVersion = 3;

    public static $faviconUrl = '';

    public static $pluginVersion = '';

    public static $pluginFile = '';

    public static $pluginUrl = '';

    public static $pluginRoot = '';

    public $nonces = array();

    public $css = array();

    public $js = array();

    public $localizedJs = array();

    public $upgradeData = null;

    /**
     * @var MvcController
     */
    public $controller;

//    public $ajaxController;

    /**
     * @var MvcAdmin
     */
    public $admin;

    public function __construct(MvcController $controller) {
        $this->controller = $controller;
        $this->admin = new MvcAdmin($controller);
    }

    public function build() {
        add_action('init', array($this, 'wp_init'));
        add_action('wp_login', array($this, 'wp_login'), 10, 2);
        add_action('wp_logout', array($this, 'wp_logout'));
        add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
        add_action('save_post', array($this->controller, 'save_post'));
        $this->setupAjax();
        $this->setupShortcodes();
        $this->admin->build();
    }

    public function addCss($handle, $url, $deps = array(), $media = 'all') {
        $this->css[] = array(self::$appPrefix . '-' . $handle, self::$pluginUrl . 'css' . $url . '.css', $deps, self::$cssVersion, $media);
    }

    public function addJs($handle, $url, $deps = array(), $inFooter = true) {
        $handle = self::$appPrefix . '-' . $handle;
        $url = self::$pluginUrl . 'js' . $url . '.js';
        wp_register_script($handle, $url, $deps, self::$jsVersion, $inFooter);
        $this->js[] = array($handle, $url, $deps, self::$jsVersion, $inFooter);
    }

    public function addLocalJs($handle, $name, $value) {
        $this->localizedJs[] = array(self::$appPrefix . '-' . $handle, self::$appPrefix . $name, $value);
    }

    public function wp_init() {
        $this->controller->wp_init();
    }

    public function wp_logout() {
        $this->controller->wp_logout();
    }

    public function wp_login($user_login, $user) {
        $this->controller->wp_login($user_login, $user);
    }

    public function wp_enqueue_scripts() {
        $this->loadJsCss();
    }

    protected function loadJsCss() {
        foreach ($this->css as $css) {
            call_user_func_array('wp_enqueue_style', $css);
        }

        foreach ($this->js as $js) {
            call_user_func_array('wp_enqueue_script', $js);
        }

        foreach ($this->localizedJs as $js) {
            call_user_func_array('wp_localize_script', $js);
        }
    }

    private function setupAjax() {
        $actions = get_class_methods($this->controller);
        foreach ($actions as $act) {
            if (strpos($act, 'ajax_') === 0) {
//                self::log('wp_ajax_nopriv_' . self::$appPrefix . '-' . $act);
                add_action('wp_ajax_' . self::$appPrefix . '-' . $act, array($this->controller, 'runAjax'));
                add_action('wp_ajax_nopriv_' . self::$appPrefix . '-' . $act, array($this->controller, 'runAjax'));
            }
        }
    }

    private function setupShortcodes() {
        $actions = get_class_methods($this->controller);
        foreach ($actions as $act) {
            if (strpos($act, 'shortcode_') === 0) {
                add_shortcode(substr($act, 10), array($this->controller, $act));
            }
        }
    }

    public function createShortcode($name) {
        $func = 'shortcode_' . $name;
        if (method_exists($this->controller, $func)) {
            add_shortcode($name, array($this->controller, $func));
        }
    }

    public static function param($name, $default = '') {
        return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default);
    }

    protected static function autoload($className) {
        $className = str_replace('\\', '/', $className);
        if (!is_array(self::$classesDir)) {
            $classFile = self::$pluginRoot . (self::$classesDir ? self::$classesDir . '/' : '') . $className . '.php';
            if (file_exists($classFile)) {
                require_once($classFile);
            }
        } else {
            foreach (self::$classesDir as $dir) {
                $classFile = self::$pluginRoot . ($dir ? $dir . '/' : '') . $className . '.php';
                if (file_exists($classFile)) {
                    require_once($classFile);
                    break;
                }
            }
        }

    }

    public static function init($pluginUrl, $pluginRoot, $pluginFile, $classesDir = '') {
        self::$classesDir = $classesDir;
        self::$pluginRoot = $pluginRoot;
        self::$pluginUrl = $pluginUrl;
        self::$pluginFile = $pluginFile;

        if (!isset(self::$classesDir) || !self::$classesDir) {
            self::$classesDir = dirname(__FILE__);
        }

        if (function_exists('spl_autoload_register')) {
            spl_autoload_register('Mvc::autoload');
        } else {
            wp_die('This version of PHP is too old: ' . PHP_VERSION);
        }
    }

    public static function get(MvcController $controller = null) {
        if ($controller) {
            self::$instance = new Mvc($controller);
        }
        return self::$instance;
    }

    public static function option($name, $value = null, $default = null) {
        $key = self::$appPrefix . '_' . $name;
        if ($value !== null) {
            return update_option($key, $value);
        } else if ($default !== null) {
            return get_option($key, $default);
        } else {
            return get_option($key);
        }
    }

    /**
     * Merges dependencies for js and css and applies the app prefix and a '-' to the beginning of all deps in $customDeps
     */
    public static function mergeDeps($stdDeps = array(), $customDeps = array()) {
        foreach ($customDeps as $index => $dep) $customDeps[$index] = self::$appPrefix . '-' . $dep;
        return array_merge($stdDeps, $customDeps);
    }

    public static function paramReplace($name, $value, $url = '') {
        $regex = "/($name=[^&\?\/]+)/";
        Mvc::log($regex);
        $url = $url ? $url : $_SERVER['REQUEST_URI'];
        if ($value === '') {
            return preg_replace($regex, '', $url);
        } else if (preg_match($regex, $url) == 1) {
            return preg_replace($regex, $name . '=' . $value, $url);
        } else {
            return $url . "$name=$value";
        }

    }

    public static function session($name, $value = null, $default = null) {
        if ($value !== null) {
            return $_SESSION[self::$appPrefix . '_pLuGiN'][$name] = $value;
        } else {
            return isset($_SESSION[self::$appPrefix . '_pLuGiN'][$name]) ? $_SESSION[self::$appPrefix . '_pLuGiN'][$name] : $default;
        }
    }

    public static function log() {
        $message = '';
        foreach (func_get_args() as $arg) {
            $message .= print_r($arg, true) . ' ';
        }
        syslog(LOG_INFO, $message);
    }
}