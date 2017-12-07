<?php

namespace config;

//include_once "my_config.php";

/*
 * Вместо чистого MySQL можно использовать его форки
 * Без ошибок работет Percona server и MariaDB
 */

const MYSQL_HOST = "127.0.0.1";
const MYSQL_USERNAME = "";
const MYSQL_PASSWORD = "";
const MYSQL_DB = "VkChatBot";
const MYSQL_PORT = 3306;

/*
 * Для уменьшения нагрузки репомендуется использовать Sphinx
 * Конфиги лежат в /install/sphinx.conf
 * По умолчанию используется sphinx
 * Если нет возможности его поставить, просто скопируйте конфиги MySQL в нижние строки.
 * Важно! При использовании Mysql как двигла для поиска, поставьте полнотекстовый индекс:
 * ALTER TABLE `answers` ADD FULLTEXT(`output`);
 */

const SEARCH_DB_HOST = "127.0.0.1";
const SEARCH_DB_USERNAME = "";
const SEARCH_DB_PASSWORD = "";
const SEARCH_DB_DB = "VkChatBot";
const SEARCH_DB_PORT = 9306;