<?php
session_start();

 $myVar = $_SERVER[REQUEST_URI];
 $myVar=explode('/',$myVar);
 $BaseUrl = "https://$_SERVER[HTTP_HOST]";
 
 $CurrentLoginUrl = str_replace($BaseUrl, '', $_SESSION["SideBarPath"]);
  
  $CurrentUrl= "/".$myVar[1]."/".$myVar[2]."/";
  
  if($CurrentUrl!=$CurrentLoginUrl){
	  header('Location: '. $BaseUrl);
  }
?> 