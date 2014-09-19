<?php
/**
 * Author:    Adam Jaso <mkltwain@gmail.com>
 *
 */ 


class MvcController {


    public function __construct() {

    }

    public final function runAjax() {
        $action = Mvc::param('action');
        $action = explode('-', $action, 2);
        $action = $action[1];
        $nonce = Mvc::param('nonce');
        if (method_exists($this, $action)) {
            if (strstr($action, 'admin') && !wp_verify_nonce($nonce, $action)) {
                self::send(array('error' => true, 'message' => 'You are not authorized.'));
            }
            $this->{$action}();
        } else {
            self::send(array('error' => true, 'message' => 'This action does not exist.'));
        }
    }

    public function admin_notices() {

    }

    public function wp_init() {

    }

    public function wp_login($user_login, $user) {

    }

    public function wp_logout() {

    }

    public function admin_init() {

    }

    public function admin_menu() {

    }

    public function updated_option($optionName) {

    }

    public function save_post($postId) {

    }

    public function on_upgrade($oldVersion, $newVersion) {

    }

    public function on_activation() {

    }

    public function on_deactivation() {

    }


    public static function send($args = array('error' => false, 'message' => 'No error'), $contentType = 'application/json') {
        if (stristr($contentType, 'application/json')) {
            $args = json_encode($args);
        }
        header('Content-Type: ' . $contentType);
        die($args);
    }
} 