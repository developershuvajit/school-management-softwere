<?php

define('BASE_URL', 'http://localhost:8888/elexa/october/school_india/' );
date_default_timezone_set('Asia/Kolkata');
define('SITE_NAME', 'School India Junior Softwere');
define('DEBUG_MODE', true);
if(DEBUG_MODE){
    error_reporting(E_ALL);
    ini_set('display_errors',1);
}else{
    error_reporting(0);
    ini_set('display_errors',0);
}
?>