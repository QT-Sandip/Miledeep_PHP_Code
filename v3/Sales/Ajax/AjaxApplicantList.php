<?php 
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

	$BUYER=$_POST['BUYER'];
	
	if($BUYER==''){
		 $sql1="SELECT * FROM `ScrmApplicants` as SAP left join ScrmCountriesList as SCL 
on SCL.id= SAP.ApplicantCountry  order by SAP.ID DESC";	
	
	$stmt_pid=$iconn->query($sql1);
			}
			
		
	ELSE{		
	 $sql1="SELECT * FROM `ScrmApplicants` as SAP left join ScrmCountriesList as SCL 
on SCL.id= SAP.ApplicantCountry WHERE ConID='$BUYER'  order by SAP.ID DESC
	 ";	
	
	$stmt_pid=$iconn->query($sql1);
	
	}
		
	//echo $sql1; exit;
	
	
	 $data .='<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">
		
		
		<tr>
		
		<th>ID</th>
		<th>Buyer Name</th>
		<th>Applicant Country</th>
		<th>Applicant Name</th>
		<th>Applicant Address</th>
		
		
		</tr>
		</thead>
		
		<tbody>';
		
	
		
	$S_No=1;	
	 while($row_csrd=$stmt_pid->fetch_array()){
	$con_id=$row_csrd['ConID'];
		
$sql_buyer="SELECT * FROM `ScrmConsigneeDetails` WHERE ConID='$con_id'";
$stmt_buyer=$iconn->query($sql_buyer);
$row_buyer=$stmt_buyer->fetch_array();
			$ConsigneeName=mb_strimwidth($row_buyer['ConsigneeName'], 0, 20, "...");

			
				$data .='
				<tr>
		
		
		
		
		<td >'.$S_No.'</td>
	<td >'.$ConsigneeName.'</td>
	<td >'.$row_csrd['name'].'</td>
		<td >'.$row_csrd['ApplicantName'].'</td>
		<td >'.$row_csrd['ApplicantAddress'].'</td>
		
		
		
		
		
			</tr>';
					  
$S_No++;}
					
		echo $data .='</tbody></table>';
		
		
		
		
		
	//	echo json_encode($OUT);
	
	
?>