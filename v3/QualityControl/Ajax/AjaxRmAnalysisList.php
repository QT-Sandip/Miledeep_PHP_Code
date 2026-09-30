<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


 $PsID=$_POST['PsID'];
 $Year=$_POST['Year'];
 $Month=$_POST['Month'];
 

  $DateStart=$Year.'-'.$Month.'-01';
  $DateEnd=$Year.'-'.$Month.'-31  23:59:59';
  $DateFilter="BETWEEN '$DateStart' and '$DateEnd'";


if($PsID!=''){
	$Query="and PurchaseStationID=$PsID";
}else{
	$Query="";
}
 


 if($_SESSION['DeptID']== 2 || $_SESSION['DeptID']== 10){
	 $showOptions=true;
 }else{
	 $showOptions=false;
 }

 $accessType=$_SESSION['ACCESS_TYPE'];


 $SqlRmList="select RmLotID,LotNumber,PurchaseDate,CreatedDateTime,PurchaseCount,PurchaseWeight,ROR.PostedUserID,RPC.PurchaseType,
 AbStatus,Name,Colour,
(SELECT COUNT(*) from QcRmOrgnDetails as ORGD WHERE  ORGD.LotID=RLD.RmLotID ) as OrgnDetailsPreview
,(SELECT Remarks from QcRmOrgnDetails as ORGD WHERE ORGD.LotID=RLD.RmLotID ORDER BY LotID DESC LIMIT 1 ) as Remarks
from RmLotDetatils as RLD 
left join RmPurchaseCategory as RPC on RPC.CatID=RLD.PurchaseCategoryID 
left join QcRmAbReport as QRAB on QRAB.LotID=RLD.RmLotID 
left join QcABOptions as QAO on QAO.OptID=QRAB.AbStatus 
left join QcRmOrgnReport as ROR on ROR.LotID=RLD.RmLotID

WHERE PurchaseDate $DateFilter $Query";


$StmtRmList=$iconn->query($SqlRmList);


?>


 <table  id="key-datatable" data-paging="false" class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">

      <tr>

        <th>ID</th>
        <th>Lot Number</th>
	    <th>Purchase Date</th>
	    <th>Posted Date</th>
	    <th>Purchase Type</th>
		<th>Count</th>
		<th>Weight(KGS)</th>
        <th>AB Status</th>
        <th>Status</th>
		<th>Reason</th>
	    <th>Actions</th>

      </tr>

    </thead>


    <tbody>



<?php

	//echo $sql;



   while($RowRmList=$StmtRmList->fetch_array()){
	   
	$RmLotID = $RowRmList['RmLotID'];
	$AbStatus = $RowRmList['RmLotID'];

      $OrgnSubDt=date('d-m-Y',strtotime($RowOrgnDetails['PostedDate']));
	if($OrgnSubDt=="01-01-1970"){
		$OrgnSubDt="00-00-0000";
	}else{
		$OrgnSubDt;
	}
		if($RowRmList['OrgnDetailsPreview'] == 0){
			$Status='Report Pending';
			$StatusColour='#f00';
		}else if($RowRmList['OrgnDetailsPreview'] >= 1 ){

			
		


			 if( $RowOrgnReportCount['RM_ANA_STATUS'] == 2){
			$Status='Qc Not Aval';
			$StatusColour='#f00';
			}else if( $RowRmList['PostedUserID']==0 ){
			$Status='Verification Pending';
			$StatusColour='#f00';
			}else {
			$Status='Verified';
			$StatusColour='green';
			}

		}


if($RowRmList['AbStatus']!=''){	
$AbStatus=$RowRmList['Name'];
$Colour=$RowRmList['Colour'];
}else{
$AbStatus='Test Pending';
$Colour='gray';
}



     ?>
	 
	    <td id=<?php echo  $RowRmList['RmLotID']?> ><?php echo  $RowRmList['RmLotID']?> </td>
        <td> <a  href="LotDetails.php?id=<?php echo $RowRmList['LotNumber']?>"></a><?php echo $RowRmList['LotNumber']?></td>

		<td id=<?php echo  $RowRmList['RmLotID']?> ><?php echo date('d-m-Y',strtotime($RowRmList['PurchaseDate']))?></td>

		<td id=<?php echo  $RowRmList['RmLotID']?> ><?php echo$RowRmList['CreatedDateTime']?></td>
		<td id=<?php echo  $RowRmList['RmLotID']?> ><?php echo$RowRmList['PurchaseType']?></td>
		<td id=<?php echo  $RowRmList['RmLotID']?> ><?php echo$RowRmList['PurchaseCount']?></td>

		<td id=<?php echo  $RowRmList['RmLotID']?> ><?php echo $RowRmList['PurchaseWeight']?></td>

        <td id=<?php echo  $RowRmList['RmLotID']?> align="CENTER" width="10%">

			<div class="btn  badge badge-success font-size-12 " align="center"
				style="background: <?php echo $Colour; ?>;"			>
			<span class="label" align="center" ><?php echo $AbStatus; ?></span>
			</div>
			
			</td>
			<td id=<?php echo  $RowRmList['RmLotID']?> align="CENTER" width="10%">

			<div class="btn  badge badge-success font-size-12 " align="center"
				style="background: <?php echo $StatusColour; ?>;"			>
			<span class="label" align="center" ><?php echo $Status; ?></span>
			</div>
			
			</td>

		
		
		<td><?php echo  $RowRmList['Remarks']?></td>
		
        <td style="width:10%;"> 
		<?php 

		if($RowRmList['OrgnDetailsPreview']<3 ){
			if($showOptions){ ?>
					<a href="RmReportCreate.php?LotId=<?php echo  $RowRmList['RmLotID']?>"class="btn btn-primary">Add</a>
			<?php }else{ ?>
				<a href="#"class="btn btn-danger" style="color:white" target="_blank">Report Pending</a>

			<?php  }
		} else  {

			if( $RowRmList['OrgnDetailsPreview']>=3 & $RowRmList['PostedUserID']==0){
			if($showOptions){ ?>
			<a href="RmAnalysisPreview.php?LotId=<?php echo $RowRmList['RmLotID']?>"
			class="btn btn-primary" target="_blank">Preview
			<?php //echo $RowRmList['VERIFIED_BY'] ?> </a>
			<?php }else{ ?>
				<a href="#"class="btn btn-danger" target="_blank">Approval Pending</a>
			<?php	}
			

		} 
		
		else if( $RowRmList['PostedUserID'] !=0){ 
			 if($showOptions){ ?>
			<a href="RmaPrint.php?LotID=<?php echo $RowRmList['RmLotID']?>"class="btn btn-warning" target="_blank">Print</a>

		
		<?php	}
		else{}		} }?>

		
		</td>


        </tr>


   <?php  } ?>




	 </tbody></table>


