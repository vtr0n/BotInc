<?php

/**
 * Created by PhpStorm.
 * User: bad-day
 * Date: 16.06.17
 * Time: 23:34
 */
class SphinxSearch
{
    function __construct($config = NULL)
    {
        include_once(__DIR__ . "/config.php");
        $this->link = mysqli_connect(
            config\MYSQL_HOST,
            config\MYSQL_USERNAME,
            config\MYSQL_PASSWORD,
            config\MYSQL_DB
        );
        mysqli_set_charset($this->link, "utf8mb4");
    }

    public function query()
    {
        //var_dump($this->prepareQuery(func_get_args()));
        return mysqli_query($this->link, $this->prepareQuery(func_get_args()));
    }

    public function search($group_id, $message)
    {
        $resp = $this->query("SELECT * FROM answers WHERE group_id = ?i AND MATCH (output) AGAINST (?s) LIMIT 1",
            $group_id, $message);
        $resp = mysqli_fetch_assoc($resp);

        if ($resp) {
            return $this->randomize($resp["output"]);
        } else {
            return $this->randomize($this->get_unfounded($group_id));
        }
    }

    public function get_unfounded($group_id) // 1 сообщение в базе - общие
    {
        $resp = $this->query("SELECT output FROM answers WHERE group_id = ?i AND id = 1", $group_id);
        $resp = mysqli_fetch_assoc($resp);

        if ($resp) {
            return $resp["output"];
        } else {
            return "not founded";
        }
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
}