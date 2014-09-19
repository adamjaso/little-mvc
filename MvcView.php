<?php
/**
 * Author:    Adam Jaso <mkltwain@gmail.com>
 *
 */ 


class MvcView {

    public $file;
    public $vars;

    public function __construct($file, array $vars = array()) {
        $this->file = Mvc::$pluginRoot . '/views' . $file . '.php';
        $this->vars = $vars;
    }

    public function render() {
        ob_start();
        if (file_exists($this->file)) {
            extract($this->vars);
            include $this->file;
        }
        return ob_get_clean();
    }

    public function __toString() {
        return $this->render();
    }
} 