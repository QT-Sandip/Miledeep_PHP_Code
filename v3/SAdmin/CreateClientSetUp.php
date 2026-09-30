<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
 $QueryParmDup=$_GET['Q'];
  $QueryParms=$_GET['Q'];
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET); 
  }    
  
  $ClientID=$_GET['ClientID'];
  
  
$SqlConfig="SELECT * FROM `AdminRegisteredClients` as ARC left join AdminConfigDetails on 1=1 WHERE ARC.ClientID='$ClientID'";  
$StmtConfig=$iconn->query($SqlConfig);
$RowConfig=$StmtConfig->fetch_array();
$CurrentProjectPath=$RowConfig['CurrentProjectPath'];
$ClientPath=$RowConfig['ClientPath'];
$ClientShortName=$RowConfig['ClientShortName'];
$Source=$ClientPath.$CurrentProjectPath;
$Destination=$ClientPath.$ClientShortName;


  $sourceDirectory=$_SERVER['DOCUMENT_ROOT']."/".$Source;  
  $destinationDirectory=$_SERVER['DOCUMENT_ROOT']."/".$Destination ;
  
  echo "Initilise Process.....<br>";
  echo "Preparing For Copy...<br>";
  echo "Writing Files.....<br>";
 
 if(is_dir($destinationDirectory)){
  echo "Unable to Copy....Folder Already Exist";
	$Q=urlEncode64('ClientID='.$ClientID);
	header('Location:CreateDb.php?Q='.$Q); 
 }else{
	$IsCopied=copy_recursive_dirs($sourceDirectory, $destinationDirectory);
	
 }
 
 


 function copy_recursive_dirs($dirsource, $dirdest)
{ 
global $ClientID;

 if(is_dir($dirsource))
 {
  $dir_handle=opendir($dirsource);


 }
 
 
 if(!is_dir($dirdest))
 {
    mkdir($dirdest, 0777);
 }
 while($file=readdir($dir_handle)) 
 {
	 echo "<pre>" ;
	 print_r($file);
     if($file!="." && $file!="..")
     {
         if(!is_dir($dirsource.DIRECTORY_SEPARATOR.$file)) 
         {
            //Copy the file at the same level in the destination folder
            copy ($dirsource.DIRECTORY_SEPARATOR.$file, $dirdest.DIRECTORY_SEPARATOR.$file);
         }
         else{
            //Copy the dir at the same lavel in the destination folder
            copy_recursive_dirs($dirsource.DIRECTORY_SEPARATOR.$file, $dirdest.DIRECTORY_SEPARATOR.$file);
         }
     }
  }
 closedir($dir_handle);
 
	$Q=urlEncode64('ClientID='.$ClientID);
	header('Location:CreateDb.php?Q='.$Q); 
 return true;
}


?>