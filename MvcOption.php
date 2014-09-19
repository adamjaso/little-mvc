<?php
/**
 * Author:    Adam Jaso <mkltwain@gmail.com>
 *
 */ 


class MvcOption {
    protected $key;
    public $value;

    public function __construct($key, $default = null) {
        $this->key = Mvc::$appPrefix . '_' . $key;
        $this->value = Mvc::option($this->key, null, $default);
    }

    public function save() {
        Mvc::log('saving', $this->key, $this->value);
        return Mvc::option($this->key, $this->value);
    }
} 