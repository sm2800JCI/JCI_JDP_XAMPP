<?php
declare(strict_types=1);
define('ROOT',dirname(__DIR__));
if(!is_file(ROOT.'/config.php')){http_response_code(503);exit('Copy config.example.php to config.php and configure the database.');}
$config=require ROOT.'/config.php';if(strlen((string)$config['app_key'])<32||str_contains($config['app_key'],'REPLACE_')){http_response_code(503);exit('Set a unique random app_key in config.php.');}
date_default_timezone_set('Asia/Kolkata');ini_set('session.use_strict_mode','1');ini_set('session.cookie_httponly','1');ini_set('session.cookie_samesite','Lax');session_start();require ROOT.'/app/helpers.php';
try{$d=$config['db'];$pdo=new PDO("mysql:host={$d['host']};port={$d['port']};dbname={$d['name']};charset={$d['charset']}",$d['user'],$d['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}catch(Throwable $e){error_log($e->getMessage());http_response_code(503);exit('Database unavailable. Check configuration and import database/schema.sql.');}

require ROOT.'/app/migrations.php';
migrate_app($pdo);
