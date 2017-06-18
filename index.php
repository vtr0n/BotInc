<?php

include "MySQL.php";
include "VkAPI.php";
include "SphinxSearch.php";
include "Functions.php";
include "Hooks.php";

$VK = new VkAPI;
$SQL = new MySQL;

$data = json_decode(file_get_contents('php://input'));
//$data = json_decode('{"type":"message_new","object":{"id":882844,"date":1491131033,"out":0,"user_id":1,"read_state":0,"title":" ... ","body":"третья"},"group_id":1,"secret":""}');
$settings = $SQL->get_settings($data->group_id);
if(!$settings) {
    exit("ok"); // Если не нашли такого бота
}

switch ($data->type) {

    case 'confirmation':
        $code = $settings["confirmation_code"];
        exit($code);

        break;

    case 'message_new':
        //Записываем во входящее
        $SQL->insert_message_new($data->group_id, $data->object->user_id, $data->object->body, $data->object->date);

        $HOOKS = new Hooks($settings["hooks"]);
        foreach ($HOOKS->hooks_array as $value) { // подключаем хуки
            include_once $value;
        }
        $SEARCH = new SphinxSearch();

        $answer = $SEARCH->search($data->group_id, $data->object->body);

        //var_dump($answer);
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