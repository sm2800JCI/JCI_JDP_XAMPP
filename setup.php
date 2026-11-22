<?php
declare(strict_types=1);
$root=__DIR__;
if(is_file($root.'/config.php')){http_response_code(403);exit('JCI JDP is already installed. Remove config.php only if you intend to reinstall.');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $host=trim($_POST['db_host']??'127.0.0.1');$port=(int)($_POST['db_port']??3306);$dbName=trim($_POST['db_name']??'jci_jdp');$dbUser=trim($_POST['db_user']??'root');$dbPass=(string)($_POST['db_password']??'');$name=trim($_POST['admin_name']??'');$email=strtolower(trim($_POST['admin_email']??''));$password=(string)($_POST['admin_password']??'');
  if(!preg_match('/^[A-Za-z0-9_]+$/',$dbName)||$port<1||$port>65535)$error='Database name or port is invalid.';
  elseif(strlen($name)<2||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<14)$error='Enter an administrator name, valid email, and password of at least 14 characters.';
  else try{
    $pdo=new PDO("mysql:host={$host};port={$port};charset=utf8mb4",$dbUser,$dbPass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");
    $schema=file_get_contents($root.'/database/schema.sql');
    foreach(explode(';',$schema) as $statement){$statement=trim($statement);if($statement!=='')$pdo->exec($statement);}
    $q=$pdo->prepare("INSERT INTO users(name,email,password_hash,role_id,office_id) VALUES(?,?,?,(SELECT id FROM roles WHERE code='ADMIN'),(SELECT id FROM offices WHERE type='HO' LIMIT 1))");
    $q->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
    $base=rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/')),'/');if($base==='.')$base='';
    $config=['app_name'=>'JCI Enterprise JDP Platform','app_key'=>bin2hex(random_bytes(32)),'debug'=>false,'base_url'=>$base,'db'=>['host'=>$host,'port'=>$port,'name'=>$dbName,'user'=>$dbUser,'password'=>$dbPass,'charset'=>'utf8mb4']];
    $contents="<?php\nreturn ".var_export($config,true).";\n";
    if(file_put_contents($root.'/config.php',$contents,LOCK_EX)===false)throw new RuntimeException('Cannot write config.php. Ensure the folder is writable by Apache during installation.');
    header('Location: '.$base.'/');exit;
  }catch(Throwable $e){$error='Installation failed: '.htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Install JCI JDP</title><link rel="stylesheet" href="assets/app.css"><style>.installer{max-width:650px;margin:40px auto;background:#fff;padding:32px;border:1px solid #e8eeeb;border-radius:12px}.installer h1{font:700 25px Manrope,Arial}.installer form{display:grid;grid-template-columns:1fr 1fr;gap:14px}.installer label{display:grid;gap:6px;font-size:11px}.installer input{border:1px solid #dfe7e2;border-radius:6px;padding:10px}.installer .wide{grid-column:1/-1}.installer .btn{grid-column:1/-1}.help{color:#78867f;font-size:12px;line-height:1.6}.error{color:#a33;background:#fff0ef;padding:10px}.installer h2{font-size:14px;margin:21px 0 5px}.installer .btn{margin-top:8px;cursor:pointer}@media(max-width:600px){.installer form{grid-template-columns:1fr}.installer .wide,.installer .btn{grid-column:auto}}</style></head><body><main class="auth"><div class="installer"><div class="eyebrow">JUTE CORPORATION OF INDIA LIMITED</div><h1>Set up JCI JDP</h1><p class="help">This one-time setup creates the MySQL database tables and your administrator account. XAMPP defaults are prefilled: MySQL user <b>root</b>, blank password.</p><?php if($error):?><p class="error"><?=$error?></p><?php endif;?><form method="post"><h2 class="wide">MySQL connection</h2><label>Host<input name="db_host" value="<?=htmlspecialchars($_POST['db_host']??'127.0.0.1')?>" required></label><label>Port<input type="number" name="db_port" value="<?=htmlspecialchars($_POST['db_port']??'3306')?>" required></label><label>Database name<input name="db_name" value="<?=htmlspecialchars($_POST['db_name']??'jci_jdp')?>" required></label><label>MySQL username<input name="db_user" value="<?=htmlspecialchars($_POST['db_user']??'root')?>" required></label><label class="wide">MySQL password<input type="password" name="db_password" value=""></label><h2 class="wide">First administrator</h2><label>Full name<input name="admin_name" value="<?=htmlspecialchars($_POST['admin_name']??'')?>" required></label><label>Work email<input type="email" name="admin_email" value="<?=htmlspecialchars($_POST['admin_email']??'')?>" required></label><label class="wide">Password (minimum 14 characters)<input type="password" name="admin_password" minlength="14" required></label><button class="btn">Install JCI JDP →</button></form></div></main></body></html>
