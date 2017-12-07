<?php

include "MySQL.php";
include "VkAPI.php";
include "MyVkAPI.php";
include "Search.php";
include "Functions.php";
include "Hooks.php";

$Vk = new MyVkAPI;
$Sql = new MySQL;

$data = json_decode(file_get_contents('php://input'));

$settings = $Sql->get_settings($data->group_id);
if (!$settings or (!empty($data->secret) and $settings["uniqid"] != $data->secret)) {
	exit("ok"); // Если не нашли такого бота
}

switch ($data->type) {

	case 'confirmation':
		$code = $settings["confirmation_code"];
		exit($code);

		break;

	case 'message_new':
		// Устанавиваем ключи
		$Vk->access_token = $settings["access_token"];
		$Vk->service_token = $settings["service_token"];

		//Записываем во входящее
		$Sql->insert_message_new($data->group_id, $data->object->user_id, $data->object->body, $data->object->date);

		$Hooks = new Hooks($settings["hooks"], "Hooks_message_new");
		foreach ($Hooks->hooks_array as $value) { // подключаем хуки
			include_once $value . ".php";
		}
		$Search = new Search();

		$answer = $Search->search($data->group_id, $data->object->body);

		$Func = new Functions($settings["functions"]);
		// Проверяем функция ли это и есть ли такой файл
		if ($func_name = $Func->is_function($answer)) {
			if ($path_name = $Func->is_set($func_name)) {
				include __DIR__ . "/Functions/$path_name.php";
				$func = new $path_name;
				$resp = $func->go();
				if (!$resp) {
					$Vk->messages_send(
						$data->object->user_id, "", $Search->randomize($Search->get_unfounded($data->group_id)));
				}
			}
			else {
				$Vk->messages_send(
					$data->object->user_id, "", $Search->randomize($Search->get_unfounded($data->group_id)));
			}
		}
		else {
			$Vk->messages_send($data->object->user_id, "", $answer);
		}

		exit("ok");

		break;

	case 'group_join':
		$Sql->insert_group_join($data->group_id, $data->object->user_id, strtotime("now"));

		// В принципе можно из бд цеплять разные данные, а не общие хуки
		$Hooks = new Hooks($settings["hooks"], "Hooks_group_join");
		foreach ($Hooks->hooks_array as $value) { // подключаем хуки
			include_once $value . ".php";
		}

		exit("ok");
		break;

	case 'group_leave':
		$Sql->insert_group_leave($data->group_id, $data->object->user_id, strtotime("now"));

		$Hooks = new Hooks($settings["hooks"], "Hooks_group_leave");
		foreach ($Hooks->hooks_array as $value) { // подключаем хуки
			include_once $value . ".php";
		}

		exit("ok");
		break;

	case 'wall_repost':
		$Sql->add_repost(
			$data->group_id, $data->object->copy_history[0]->id, $data->object->copy_history[0]->owner_id,
			$data->object->from_id);

		$Hooks = new Hooks($settings["hooks"], "Hooks_wall_repost");
		foreach ($Hooks->hooks_array as $value) { // подключаем хуки
			include_once $value . ".php";
		}

		exit("ok");
		break;

	default:
		exit("ok");
		break;
}