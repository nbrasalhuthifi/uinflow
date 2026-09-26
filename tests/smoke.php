<?php
declare(strict_types=1);
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../app/Helpers/helpers.php';
$checks=[];
$checks['csrf']=strlen(csrf_token())===64;
$checks['status labels']=status_label('ACTIVE')==='نشط' && status_label('SUPERVISOR')==='مشرف';
$checks['password hashing']=password_verify('secret',password_hash('secret',PASSWORD_DEFAULT));
$checks['env fallback']=APP_NAME==='UniFlow';
foreach($checks as $name=>$ok) echo ($ok?'PASS':'FAIL').' '.$name.PHP_EOL;
if(in_array(false,$checks,true)) exit(1);
echo 'Static smoke checks passed.'.PHP_EOL;
