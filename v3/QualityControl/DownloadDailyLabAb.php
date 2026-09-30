<?php 
include "../includes/config.php";
/* 
include "config.php";
$SQL_CODE_LIST="SELECT * FROM `DAILY_LAB_REPORT_LIST`";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);


while($row=$STMT_CODE_LIST->fetch_array()){
	
	$LOADING_STATUS=$row['LOADING_STATUS'];
	 $LOT_NUMBER=$row['LOT_NUMBER'];
	 $LOT_NUMBER = str_replace("0","O",$LOT_NUMBER);
	
	//$h = str_replace("’","'",$s);
	
	//exit;
	
	if($LOADING_STATUS=='GRAY'){
	$ANTI_BIOTIC_STATUS='0';
}else if($LOADING_STATUS=='RED'){
	$ANTI_BIOTIC_STATUS='1';
}else if($LOADING_STATUS=='GREEN'){
	$ANTI_BIOTIC_STATUS='2';
}if($LOADING_STATUS=='BLUE'){
	$ANTI_BIOTIC_STATUS='3';
}


 $sql_org_insert="UPDATE `inputmaterial_details` SET 
`ANTI_BIOTIC_STATUS`='$ANTI_BIOTIC_STATUS'  WHERE `LOT_NO`='$LOT_NUMBER'";
$stmt1=$iconn->query($sql_org_insert);

}

*/

$ANALYSIS_DATE_S = '2020-03-20';
$ANALYSIS_DATE_E = '2020-04-05';



ob_end_clean();

 // config(['excel.export.generate_heading_by_indices' => false]);
  $filename = "DAILY_DATA" . date('Ymd') . ".xls";
  header("Content-Disposition: attachment; filename=\"$filename\"");
  header("Content-Type: application/vnd.ms-excel");
 //config(['excel.export.generate_heading_by_indices' => false]);


//$user_query = mysql_query('select name,work from info');



	 $sql_code_list="SELECT ANALYSIS_DATE,PURCHASE_DATE,LOT_NUMBER,QUANTITY,d.COUNT,ADDRESS,AGENT_NAME,RESULT,PARAMETER FROM `DAILY_LAB_REPORT_LIST` as d left join `inputmaterial_details` as i
on d.LOT_NUMBER = i.LOT_NO  WHERE d.ANALYSIS_DATE BETWEEN '$ANALYSIS_DATE_S' AND '$ANALYSIS_DATE_E'  and i.SAMPLE_TYPE !='PRE HARVEST'
 ORDER BY d.ID DESC
	";
	

	
	
	
	$stmt_code_list=$iconn->query($sql_code_list);
// Write data to file
$flag = false;
while ($row = $stmt_code_list->fetch_assoc()) {
    if (!$flag) {
        // display field/column names as first row
        echo implode("\t", array_keys($row)) . "\r\n";
        $flag = true;
    }
	 array_walk($row, __NAMESPACE__ . '\cleanData');
    echo implode("\t", array_values($row)) . "\r\n";
}

 function cleanData(&$str)
  {
    // escape tab characters
    $str = preg_replace("/\t/", "\\t", $str);

    // escape new lines
    $str = preg_replace("/\r?\n/", "\\n", $str);

    // convert 't' and 'f' to boolean values
    if($str == 't') $str = 'TRUE';
    if($str == 'f') $str = 'FALSE';

    // force certain number/date formats to be imported as strings
    if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
      $str = "'$str";
    }

    // escape fields that include double quotes
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

 

?>