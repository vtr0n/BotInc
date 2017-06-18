<?php

class MySQL
{
    var
        $link;

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

    public function get_settings($group_id)
    {
        $resp = $this->query("SELECT * FROM settings WHERE group_id = ?s", $group_id);
        $resp = mysqli_fetch_assoc($resp);

        if (!$resp) {
            return false;
        } else {
            return $resp;
        }

    }

    /** Пишем в базу ***/
    public function add_repost($group_id, $wall_id, $wall_owner_id, $vk_id)
    {
        $this->query("INSERT INTO wall_repost(group_id, wall_id, wall_owner_id, vk_id) VALUES(?s, ?s, ?s, ?s)",
            $group_id, $wall_id, $wall_owner_id, $vk_id);
    }

    public function insert_message_new($group_id, $user_id, $body, $date)
    {
        $this->query("INSERT INTO message_new(group_id, vk_id, message, date) VALUES(?s, ?s, ?s, ?s)",
            $group_id,
            $user_id,
            $body,
            $date
        );
    }

    public function insert_group_join($group_id, $user_id, $date)
    {
        $this->query("INSERT INTO group_join(group_id, vk_id, date) VALUES(?s, ?s, ?s)", $group_id, $user_id, $date);
    }

    public function insert_group_leave($group_id, $user_id, $date)
    {
        $this->query("INSERT INTO group_leave(group_id, vk_id, date) VALUES(?s, ?s, ?s)", $group_id, $user_id, $date);
    }

    /** Методы защиты ***/
    private function prepareQuery($args)
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
            $part = $this->escapeVar($value);
            $query .= $part;
        }

        //$query = str_replace('\\\\', ' ', $query);
        return $query;
    }

    private function escapeVar($value)
    {
        if ($value === NULL)
            return 'NULL';

        return "'" . mysqli_real_escape_string($this->link, $value) . "'";
    }
}