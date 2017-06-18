<?php

/**
 * Created by PhpStorm.
 * User: bad-day
 * Date: 16.06.17
 * Time: 23:34
 */
class Hooks
{
    public
        $hooks_array;

    function __construct($hooks)
    {
        $hooks = explode(";", $hooks);
        $valid_hooks = scandir( __DIR__ . "/Hooks");

        $return_arr = array();
        for($i = 0; $i < count($hooks); $i++) {
            if(in_array($hooks[$i], $valid_hooks)) {
                array_push($return_arr, __DIR__ . "/Hooks/" . $hooks[$i]);
            }
        }

        $this->hooks_array = $return_arr;
    }
}