<?php

/**
 * Created by PhpStorm.
 * User: bad-day
 * Date: 16.06.17
 * Time: 23:34
 * Класс для работы с поисковой базой данных
 */
class Search
{
	var $is_sphinx = 0;

	function __construct($config = NULL)
	{
		include_once(__DIR__ . "/config.php");

		if (config\SEARCH_IS_SPHINX) {
			$this->is_sphinx = 1;
		}

		$this->link = mysqli_connect(
			config\SEARCH_DB_HOST,
			config\SEARCH_DB_USERNAME,
			config\SEARCH_DB_PASSWORD,
			config\SEARCH_DB_DB,
			config\SEARCH_DB_PORT
		);

		mysqli_set_charset($this->link, "utf8mb4");
	}

	public function search($group_id, $message)
	{
		if ($this->is_sphinx) {
			$resp = $this->query(
				"SELECT * FROM VkChatBot WHERE group_id = ?i AND MATCH(?s) LIMIT 1",
				$group_id,
				$message
			);
		}
		else {
			if (iconv_strlen($message) < 3) {
				$resp = $this->query(
					"SELECT * FROM answers WHERE group_id = ?i input = ?s LIMIT 1",
					$group_id,
					$message
				);
			}
			else {
				$resp = $this->query(
					"SELECT * FROM answers WHERE group_id = ?i AND MATCH(input) AGAINST(?s) LIMIT 1",
					$group_id,
					$message
				);
			}
		}
		$resp = mysqli_fetch_assoc($resp);

		if ($resp) {
			return $this->randomize($resp["output"]);
		}
		else {
			return $this->randomize($this->get_unfounded($group_id));
		}
	}

	public function query()
	{
		//var_dump($this->prepareQuery(func_get_args()));
		return mysqli_query($this->link, $this->prepareQuery(func_get_args()));
	}

	/** Методы защиты ***/
	protected function prepareQuery($args)
	{
		$query = '';
		$raw = array_shift($args);
		$array = preg_split('~(\?[nsiuap])~u', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

		foreach ($array as $i => $part) {
			if (($i % 2) == 0) {
				$query .= $part;
				continue;
			}
			$value = array_shift($args);

			switch ($part) {
				case '?i':
					$part = $this->escapeInt($value);
					break;

				case '?s':
					$part = $this->escapeString($value);
					break;
			}
			$query .= $part;
		}

		return $query;
	}

	protected function escapeInt($value)
	{
		if ($value === NULL) {
			return 'NULL';
		}
		if (!is_numeric($value)) {
			$this->error("Integer (?i) placeholder expects numeric value, " . gettype($value) . " given");
			return FALSE;
		}
		if (is_float($value)) {
			$value = number_format($value, 0, '.', '');
		}
		return $value;
	}

	protected function escapeString($value)
	{
		if ($value === NULL) {
			return 'NULL';
		}

		return "'" . mysqli_real_escape_string($this->link, $value) . "'";
	}

	public function randomize($str)
	{
		$str = str_replace("\;", "<{TZ}>", $str);
		$str = str_replace("\\n", "<{NN}>", $str);

		$arr = explode(";", $str);
		$rand_str = $arr[rand(0, count($arr) - 1)];

		$rand_str = str_replace("<{TZ}>", ";", $rand_str);
		$rand_str = str_replace("<{NN}>", "<br>", $rand_str);
		return $rand_str;
	}


	/**
	 * Если прямым запросом к поисковой бд вернулся null, то запрос перепавляется сюда
	 * В этом методе мы делаем запрос на полученя первой строкой, добавленной в базу
	 * Логика движка такова, что если мы не нашли точного соответствия, мы выбираем первую строку
	 * Если не нужно отвечать первым сообщением, то надо просто оставить пустую строку
	 * @param $group_id
	 * @return string
	 */
	public function get_unfounded($group_id)
	{
		if ($this->is_sphinx) {
			$resp = $this->query("SELECT output FROM VkChatBot WHERE group_id = ?i ORDER by id LIMIT 1", $group_id);
		}
		else {
			$resp = $this->query("SELECT output FROM answers WHERE group_id = ?i ORDER by id LIMIT 1", $group_id);
		}
		$resp = mysqli_fetch_assoc($resp);

		if ($resp) {
			return $resp["output"];
		}
		else {
			return "not founded, please, contact to admin";
		}
	}
}