<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

DB::$host = DB_HOST;
DB::$dbName = DB_NAME;
DB::$user = DB_USER;
DB::$password = DB_PASS;
DB::$encoding = 'utf8mb4';