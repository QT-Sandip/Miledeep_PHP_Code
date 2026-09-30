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
  
  
 $SqlConfig="SELECT * FROM `AdminRegisteredClients` as ARC  
left join AdminConfigDetails on 1=1 
left join AdminClientDbDetails as Db on Db.ClientID=ARC.ClientAccountID 
WHERE ARC.ClientID='$ClientID'";  
$StmtConfig=$iconn->query($SqlConfig);
$RowConfig=$StmtConfig->fetch_array();
$CurrentProjectPath=$RowConfig['CurrentProjectPath'];
$ClientPath=$RowConfig['ClientPath'];
$ClientShortName=$RowConfig['ClientShortName'];
$CurrentDbPath=$RowConfig['CurrentDbPath'];

$DbHost=$RowConfig['DbHost'];
$DbName=$RowConfig['DbName'];
$DbUserName=$RowConfig['DbUserName'];
$DbPassword=$RowConfig['DbPassword'];

//$Source=$ClientPath.$CurrentDbPath;  
$Source='v3/SAdmin/BaseDb/u514614285_SaiME_V3-1.sql';

 echo $sourceDirectory=$_SERVER['DOCUMENT_ROOT']."/".$Source;   echo '<br>'; //Get Default Path


 
if ($connection = mysqli_connect($DbHost, $DbUserName, $DbPassword, $DbName)) {
 echo executeScriptFile($sourceDirectory, $connection);
} else {
  die('Can not connect to MySQL');
} 
 
 
 function executeScriptFile($fileName, $dbConnection) {
	echo "iniy";
 echo $script = file_get_contents($fileName);
  $statements = parseScript($script);
  foreach($statements as $statement) {
    mysqli_query($dbConnection, $statement);
  }
}







function parseScript($script) {

  $result = array();
  $delimiter = ';';
  while(strlen($script) && preg_match('/((DELIMITER)[ ]+([^\n\r])|[' . $delimiter . ']|$)/is', $script, $matches, PREG_OFFSET_CAPTURE)) {
    if (count($matches) > 2) {
      $delimiter = $matches[3][0];
      $script = substr($script, $matches[3][1] + 1);
    } else {
      if (strlen($statement = trim(substr($script, 0, $matches[0][1])))) {
        $result[] = $statement;
      }
      $script = substr($script, $matches[0][1] + 1);
    }
  }

  return $result;

}



?>