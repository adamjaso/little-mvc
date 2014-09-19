<?php
/**
 * Author:    Adam Jaso <mkltwain@gmail.com>
 *
 */ 


class MvcModel {

    public static $table = '';
    public static $primaryId = '';

    public function __construct($row = null) {
        if ($row) {
            foreach (get_object_vars($row) as $name => $value) {
                $this->{$name} = $value;
            }
        }
    }

    public function __toString() {
        return json_encode($this);
    }
}