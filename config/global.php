<?php
session_start();
include_once __DIR__.'/../config/BaseUrl.php';
$BaseUrlConfig = new BaseUrl;
$BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
$host = $BaseUrlConfig->protocol."://$BaseUrlConfig->host";


if ($host == 'https://dev.camaroo.in/') {

    $root = $_SERVER['DOCUMENT_ROOT'] . "/";
    $workingUrl = $host . '/';
    $ROOT_URL = $host . '/';
    $workingDir = $_SESSION['Directory'] . '/';
    $workingModule = $_SERVER['DOCUMENT_ROOT'] . "/" . $_SESSION['Directory'] . '/';

    define("ROOT", $root);                      // Hosting Folder 
    define("ROOT_URL", $ROOT_URL);                      // Hosting Folder 
    define("WORKING_DIR", $workingDir);         //CurrentPath ex 'Clients/DFL'
    define("WORKING_MODULE", $workingModule);   // Module level
    define("WORKING_URL", $workingUrl);         // Module level
} else {

    $root = $_SERVER['DOCUMENT_ROOT'] . '/Clients/DFL/';
    $workingUrl = $host . '/Clients/DFL/';
    $workingDir = $_SESSION['IncludePath'] . '/';
     $workingModule = $_SERVER['DOCUMENT_ROOT'] .'/'. $_SESSION['IncludePath'] . '/';

    $root = $_SERVER['DOCUMENT_ROOT'] . '/';
    $workingUrl = $_SESSION['SideBarPath'];
    $workingDir = $_SESSION['IncludePath'] . '/';
    $workingModule = $_SERVER['DOCUMENT_ROOT'] .'/'. $_SESSION['IncludePath'] . '';
    $ROOT_URL = $host . '/';


/* comment if issue*/
$urlArray = explode('/',$_SERVER["REQUEST_URI"]);

 $workingModule= $_SERVER['DOCUMENT_ROOT'].'/'.$urlArray[1].'/'.$urlArray[2].'/';

/* comment if issue*/

//var_dump($test);
   
    define("ROOT", $root); // Hosting Folder 
    define("ROOT_URL", $ROOT_URL); // Hosting Folder 
    define("WORKING_DIR", $workingDir); //CurrentPath ex 'Clients/DFL'
    define("WORKING_MODULE", $workingModule); // Module level
    define("WORKING_URL", $workingUrl); // Module level

}
include_once ROOT . "/config/check-login.php";
?>