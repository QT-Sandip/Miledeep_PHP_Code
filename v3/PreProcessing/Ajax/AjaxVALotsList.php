<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//$MONTH=$_POST['Month'];
//$YEAR=$_POST['Year'];
$TYPE=$_POST['Type'];
$PsId=$_POST['PsId'];
$ToDate=$_POST['ToDate'];
$FromDate=$_POST['FromDate'];


$TYPE=$_POST['TO_DATE'];


$COUNT=$_POST['COUNT'] ;

 //$_SESSION['DeptID']=4;

 $showQuantity=false;

 if($_SESSION['DeptID'] == 3  && $_SESSION['AccessType'] <= 3){
		 $showQuantity=true;
	 }


 if($_SESSION['DeptID']== 1){
	 $showOptions=true;
 }else{
	 $showOptions=false;
 }

 $accessType=$_SESSION['AccessType'];
$query=""; 

   if($PsId!=''){
	   $query= " and PurchaseStationID=".$PsId;
	   
   }
  /* if($COUNT !=''){
	   $COUNT=explode('&',$COUNT);
	   $LowerCount=$COUNT[0];
	   $UpperCount=$COUNT[1];
	   
	   $query="and PurchaseCount > $LowerCount and PurchaseCount < $UpperCount";
   }else{
	$query="";   
   }*/

  /*$SqlLotDetails="SELECT * FROM `RmLotDetatils` AS RLD
  LEFT JOIN RmPurchaseCategory AS RPC ON RLD.PurchaseCategoryID=RPC.CatID
  WHERE Month(PurchaseDate)='$MONTH' and year(PurchaseDate)='$YEAR' $query and LotStatus!=2";*/
  
  $SqlLotDetails="SELECT * FROM `RmLotDetatils` AS RLD
  LEFT JOIN RmArrivalBreakUpDetails AS RLBD ON RLD.BreakUpID=RLBD.ArrivalBrkUpID
  LEFT JOIN RmPurchaseCategory AS RPC ON RLD.PurchaseCategoryID=RPC.CatID
  WHERE PurchaseDate between '$FromDate' and '$ToDate' AND RLBD.Variety='Head On' $query and LotStatus!=2";
  $StmtLotDetails=$iconn->query($SqlLotDetails);

// echo $SqlLotDetails; exit;
$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

                  <th>ID</th>
                  <th>LOT</th>
				  <th>Purchase Date</th>
				  <th>Count</th>
				   <th>Type</th>';
				  if($showQuantity){
				  $content .= '<th >Grader</th>
				  <th>Agent</th>
				  <th>Count</th>
				  <th>Weight</th>';
				  }
				  $content .= '
                 <!-- <th >AB Status</th>-->
				  <th>Actions</th>
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;

	while($RowLotDetails=$StmtLotDetails->fetch_array()){


		
		$PurchaseDate=date("d-m-Y",strtotime($RowLotDetails['PurchaseDate']));
		$PurchaseCount=$RowLotDetails['PurchaseCount'];
		$LotID=$RowLotDetails['RmLotID'];
		$PurchaseWeight=$RowLotDetails['PurchaseWeight'];
		$PurchaseType=$RowLotDetails['PurchaseType'];
		
		$SqlABDetails="SELECT * FROM `QcRmAbReport` AS QRAR
		LEFT JOIN QcABOptions AS QAO ON QAO.OptID=QRAR.AbStatus WHERE LotID ='$LotID'";
        $StmtABDetails=$iconn->query($SqlABDetails);
        $RowABDetails=$StmtABDetails->fetch_array();
		$ABStatus=$RowABDetails['Name'];
		$BgColour=$RowABDetails['Colour'];
			
				$content .= '<tr>

                <td>'.$RowLotDetails['RmLotID'].'</td>

                <td><B>'.$RowLotDetails['LotNumber'].'</B></td>
                <td>'.$PurchaseDate.'</td>
				<td><B>'.$RowLotDetails['Count'].'</B></td>
                <td>'.$PurchaseType.'</td>';
				 if($showQuantity){
				  $content .= '
                <td>'.$RowLotDetails['SUPERVISOR'].'</td>
                <td>'.$RowLotDetails['AGENT_NAME'].'</td>
                <td>'.$PurchaseCount.'</td>
                <td>'.$PurchaseWeight.'</td>';
				 }



			   $content .= '<!--<td>
			   

		<div class="bg-active color-palette"  >
			<span class="label"  style="background:'.$BgColour.'">'. $ABStatus.'</span>
		</div>
		</td>-->

		 <td>
			   <a target="_blank" href="AddVAReport.php?LotID='.$RowLotDetails['RmLotID'].'"
						 class="mr-3 text-primary Add" data-toggle="tooltip" data-placement="top"
						 title="Add" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>
		</td>

		</tr>';


	}
	$content.='</tbody></table>'	;

/*	<a   href="#"class="btn btn-info">Lot Details</a>
		<a   href="AddRMDefects.php?ID='.$RowLotDetails['id'].'"class="btn btn-info">RM DEFECTS</a>
	*/

echo $content;

	?>
	