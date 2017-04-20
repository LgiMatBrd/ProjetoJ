<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_ALL, 'pt_BR', 'ptb');

define('UTC_TIMEZONE', -3);

/*
$timezone  = -5; //(GMT -5:00) EST (U.S. & Canada)
echo gmdate("Y/m/j H:i:s", time() + 3600*($timezone+date("I"))); 
 * 
$date = new DateTime('2012-07-16 01:00:00 +00');
$date->setTimezone(new DateTimeZone('Europe/Moscow')); // +04

echo $date->format('Y-m-d H:i:s'); // 2012-07-15 05:00:00 
 */