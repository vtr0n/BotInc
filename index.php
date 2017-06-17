<?php

include "config.php";
include "MySQL.php";
include "VkAPI.php";
include "Search.php";
include "Functions.php";
include "Hooks.php";

$VK = new VkAPI;
$SQL = new MySQL;

$data = json_decode(file_get_contents('php://input'));
//$data = json_decode('{"type":"group_leave","object":{"id":882844,"date":1491131033,"out":0,"user_id":1,"read_state":0,"title":" ... ","body":"😃😃😃"},"group_id":1,"secret":""}');

switch ($data->type) {

    case 'confirmation':
        $code = $SQL->get_confirmation_code($data->group_id);
        exit($code);

        break;

    case 'message_new':
        //Записываем в входящее
        $SQL->insert_message_new($data->group_id, $data->object->user_id, $data->object->body, $data->object->date);

        // Вносим в класс хуки
        // Хуки. Выполняются до поиска. Можно делать всякие чатики анонимные, проверки на подписку, репосты
        // Подумай насчет приоритетизации
        // Функции. Вызываются после поиска по базе

        exit("ok");
        break;

    case 'group_join':
        $SQL->insert_group_join($data->group_id, $data->object->user_id, strtotime("now"));

        exit("ok");
        break;

    case 'group_leave':
        $SQL->insert_group_leave($data->group_id, $data->object->user_id, strtotime("now"));

        exit("ok");
        break;

    case 'wall_repost':
        $SQL->add_repost(
            $data->group_id,
            $data->object->copy_history[0]->id,
            $data->object->copy_history[0]->owner_id,
            $data->object->from_id
        );

        exit("ok");
        break;

    default:
        exit("ok");
        break;
}