<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$YEAR=$_POST['YEAR'];
$EXPORTER=$_POST['EXPORTER'];

/*$DateStart=$YEAR.'-04-01';
$YearEnd=$YEAR+1;
$DateEnd=$YearEnd.'-03-31';*/
$FinClsYear=getFinancialYear($YEAR);
$DateStart=$FinClsYear.'-04-01';
$DateEnd=($FinClsYear+1).'-03-31';

$DateFilter="BETWEEN '$DateStart' and '$DateEnd'";

if($EXPORTER!=" "){
	$Query='and POD.ExporterID='.$EXPORTER;
}
else{
	$Query='';
}

 
//
if($YEAR!=''){
	
	
//ROBO CHANGED $DateFilter TO $FinClsYear

  $sql="SELECT * FROM ScrmPoData as POD 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID 
left join ScrmShipmentDetailsBreif as SDB on POD.PoID=SDB.PiID 
WHERE POD.PoDate $FinClsYear and   TransportedDate!=''
$Query "; 
$stmt=$iconn->query($sql);
//ECHO $sql;

$data=' <table id="key-datatable" data-paging="false"
										class="table dt-responsive wrap w-100">

		
<thead class="thead-light">   
 <tr>
	  <th>PO ID</th>
	 <th>C.Invoice No</th>	
	 <th>PO No</th>
	  <th>PO Date</th>	
	  <th>Buyer Name</th>
	  <th>CodeList Brand</th>
	  <th>Country</th>	  
	 <!-- <th>Status</th> -->
	  <th>Actions</th>
	</tr>
    </thead>
	<tbody>';



  while($row= $stmt->fetch_array()){
	  $InvID=$row['PoID'];

$SqlCodeList="SELECT * FROM `$TblQcCodeList` WHERE InvID='$InvID'";
$StmtCodeList=$iconn->query($SqlCodeList);
$AryBrand=array();
$Brand='';


while($RowCodeList=$StmtCodeList->fetch_array()){
	
	 	$AryBrand[]=$RowCodeList['Brand'];

}
$Brand=implode(",",array_unique($AryBrand));

$PoDate = strtotime($row['PoDate']);
$PoDate = date("d-m-Y", $PoDate);

$PO_TIME_STAMP = strtotime($row['InvDate']);
$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);

if($row['PO_STATUS'] ==0){
	$CLR='#04c';
	$A_B_STATUS="Code List Not Issued";
}else if($row['PO_STATUS'] ==1){
	$A_B_STATUS="Code List Issued But Not Submitted";
}else if($row['PO_STATUS'] ==2){
	$A_B_STATUS="Sample Submitted";
}else if($row['PO_STATUS'] ==3){
	$A_B_STATUS="Report Awaited";
}else if($row['PO_STATUS'] ==4){
	$A_B_STATUS="Draft Report Received";
}else if($row['PO_STATUS'] ==5){
	$A_B_STATUS="Final Report Received";
}	else if($row['PO_STATUS'] ==6){
	$A_B_STATUS="Antibiotic Detected";
	$CLR='red';
}
if($row['TYPE_OF_COUNTRY']=='1'){

$country_type='NON-EU';	
	
}else{
	
$country_type='EU';		
}


$data.='<td id="'.$row['ID'].'" > '.$row['PoID'].'</td>
		<td id="'.$row['ID'].'" > '.$row['InvNo'].'</td>
		<td id="'.$row['ID'].'" > '.$row['PoNo'].'</td>
		<td id="'.$row['ID'].'" > '.$PoDate.'</td>
		<td id="'.$row['ID'].'" > '.$row['ConsigneeName'] .'</td>
		<td id="'.$row['ID'].'" > '.$Brand.'</td>
		<td id="'.$row['ID'].'" > '.$row['ConsigneeCountry'].'</td>
		

		
	   <td style="width:160px;">
	
	<a target="_blank" href="DocAvalReports?PoID='.$row['PoID'].'"
	class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
	title="" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>               
	
   
				
				
				</tr>';
	  }

	 echo $data .='</tbody></table>';






}



?>
