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
        $VK->access_token = $settings["access_token"];

        $SQL->insert_message_new($data->group_id, $data->object->user_id, $data->object->body, $data->object->date);

        $HOOKS = new Hooks($settings["hooks"]);
        foreach ($HOOKS->hooks_array as $value) { // подключаем хуки
            include_once $value;
        }
        $SEARCH = new SphinxSearch();

        $answer = $SEARCH->search($data->group_id, $data->object->body);

        $FUNC = new Functions($settings["functions"]);
        // Проверяем функция ли это и есть ли такой файл
        if($func_name = $FUNC->is_function($answer) and $path_name = $FUNC->is_set($func_name)) {
            echo 123;
            include __DIR__ . "/Functions/$path_name.php";
            $func = new $path_name;
            $resp = $func->go();
            if(!$resp) {
                $VK->messages_send(
                    $data->object->user_id,
                    "",
                    $SEARCH->randomize($SEARCH->get_unfounded($data->group_id))
                ); // Отправляем сообщение
            }
        } else {
            $VK->messages_send(
                $data->object->user_id,
                "",
                $SEARCH->randomize($SEARCH->get_unfounded($data->group_id))
            ); // Отправляем сообщение
        }

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