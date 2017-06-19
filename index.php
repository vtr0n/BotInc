<?php

include "MySQL.php";
include "VkAPI.php";
include "MyVkAPI.php";
include "SphinxSearch.php";
include "Functions.php";
include "Hooks.php";

$VK = new MyVkAPI;
$SQL = new MySQL;

$data = json_decode(file_get_contents('php://input'));

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
        // Устанавиваем ключи
        $VK->access_token = $settings["access_token"];
        $VK->service_token = $settings["service_token"];

        //Записываем во входящее
        $SQL->insert_message_new($data->group_id, $data->object->user_id, $data->object->body, $data->object->date);

        $HOOKS = new Hooks($settings["hooks"]);
        foreach ($HOOKS->hooks_array as $value) { // подключаем хуки
            include_once $value . ".php";
        }
        $SEARCH = new SphinxSearch();

        $answer = $SEARCH->search($data->group_id, $data->object->body);

        $FUNC = new Functions($settings["functions"]);
        // Проверяем функция ли это и есть ли такой файл
        if($func_name = $FUNC->is_function($answer)) {
            if($path_name = $FUNC->is_set($func_name)) {
                include __DIR__ . "/Functions/$path_name.php";
                $func = new $path_name;
                $resp = $func->go();
                if(!$resp) {
                    $VK->messages_send(
                        $data->object->user_id,
                        "",
                        $SEARCH->randomize($SEARCH->get_unfounded($data->group_id))
                    );
                }
            } else {
                $VK->messages_send(
                    $data->object->user_id,
                    "",
                    $SEARCH->randomize($SEARCH->get_unfounded($data->group_id))
                );
            }
        } else {
            $VK->messages_send($data->object->user_id, "", $answer);
        }

        exit("ok");
        break;

    case 'group_join':
        // Надо тоже организовать хуки
        $SQL->add_subscriber($data->group_id, $data->object->user_id);
        $SQL->insert_group_join($data->group_id, $data->object->user_id, strtotime("now"));

        exit("ok");
        break;

    case 'group_leave':
        // Надо тоже организовать хуки
        $SQL->delete_subscriber($data->group_id, $data->object->user_id);
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