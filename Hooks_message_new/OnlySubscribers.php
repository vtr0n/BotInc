<?php
/**
 * Created by PhpStorm.
 * User: bad-day
 * Date: 19.06.17
 * Time: 22:42
 * Хука, определяющая подписан ли человек на паблик
 */

if (!$SQL->is_subscriber($data->group_id, $data->object->user_id)) { // Если в базе нет
    if ($VK->is_subscriber($data->group_id, $data->object->user_id)) { // Если вк говорит, подписан
        $SQL->add_subscriber($data->group_id, $data->object->user_id);
    } else {
        $please_subscribe_text = "Пожалуйста, подпишитесь на паблик"; // Можно, конечно, через бд настраивать
        $VK->messages_send($data->object->user_id, "", $please_subscribe_text); // Отправляем сообщение

        exit("ok");
    }

}