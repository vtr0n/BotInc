<?php

/**
 * Created by PhpStorm.
 * User: bad-day
 * Date: 19.06.17
 * Time: 22:58
 */
class MyVkAPI extends VkAPI
{
   // public
    //    $service_token; // Нужен в некоторых методах

    public function is_subscriber($group_id, $user_id)
    {
        $resp = $this->groups_isMember($group_id, $user_id);
        if(!isset($resp->response)) {
            return false;
        } elseif ($resp->response == 1) {
            return true;
        } else {
            return false;
        }
    }
}