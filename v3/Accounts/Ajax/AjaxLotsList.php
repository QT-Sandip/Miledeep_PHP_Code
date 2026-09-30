<?php
session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$Month=$_POST['Month'];
$Month=sprintf("%02d", $Month);
$Year=$_POST['Year'];
$Type=$_POST['Type'];
$PsId=$_POST['PsId'];


$date_start=$Year.'-'.$Month.'-01';
$date_end=$Year.'-'.$Month.'-31  23:59:59';

if($PsId!=0){
 $PsId_SQL = "and RMD.PurchaseStationID='$PsId'";
 }else{
	$PsId_SQL = "";
 }
 

$showAddBill=false;
$showOptions=false;
$showAddCosting=false;
$showViewGrading=false;
$showEditGrading=false;
$BillWipeOff=false;



 if($_SESSION['D_ID']== 3){
	 $showOptions=true;
	$showAddBill=true;
 }

  if($_SESSION['D_ID']== 5){
	 $showViewGrading=true;
  }


IF($_SESSION['ACCESS_Type']==1){
	
	$BillWipeOff=true;
}

 if($_SESSION['D_ID']== 8){

	// echo "hii";
	 $showAddCosting=true;
	 if($_SESSION['ACCESS_Type']==1){
		 $showAddBill=true;
		 $showEditGrading=true;
	 }
 }

 //echo $showAddCosting;



 $accessType=$_SESSION['ACCESS_Type'];

   $SqlBillQuery="SELECT
   RMD.RmLotID,
   RMD.LotNumber,
   RMD.PurchaseDate,
   RMD.PurchaseCount as Count,
   RMD.LotStatus,
   RmPC.PurchaseType,
   RMD.PurchaseWeight,
   GD.GradingDate,
   GD.LotID as GdAval,
   RMBD.ArrivalID as BillAval,
   RMBD.BillStatus,
   QCAbO.Name as AbStatus,
   RmFin.FinancierName as FinancierName,
  RMBD.ArrivalID AS CostingAval,
  (SELECT COUNT(*) from QcRmOrgnReport as ORGD WHERE  ORGD.LotID=RMD.RmLotID ) as OrgnReport
  from RmLotDetatils as RMD
  LEFT JOIN RmArrivalBreakUpDetails AS RLBD ON RMD.BreakUpID=RLBD.ArrivalBrkUpID
  LEFT join PpcHLGradingDetails as GD on RMD.RmLotID=GD.LotID
  LEFT join RmBillDetails as RMBD on RMD.RmLotID=RMBD.ArrivalID
  LEFT JOIN QcRmAbReport as QCAbR on RMD.RmLotID=QCAbR.LotID
  LEFT JOIN QcABOptions as QCAbO on QCAbO.OptID=QCAbR.AbStatus
   LEFT JOIN RmFinanciers as RmFin on RmFin.ID=RMBD.FinancierID
     LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RMD.PurchaseCategoryID
    where  PurchaseDate between '$date_start' and '$date_end' AND RLBD.Variety='Head On' $PsId_SQL";

//echo $SqlBillQuery; exit;

$BillExec=$iconn->query($SqlBillQuery);





$content .= '<table id="scroll-vertical-datatable" 
										class="table table-striped 
										table-bordered dt-responsive wrap" >


			<thead>

                  <th align="CENTER">ID</th>
				  <th align="CENTER">Lot</th>
				  <th align="CENTER">Count</th>
				  <th align="CENTER">Purchase Date</th>
				 
				  <th align="CENTER">Grading Date</th>
				  <th align="CENTER">Bill Name</th>
				  <th align="CENTER">Type</th>
				 <!-- <th align="CENTER">Weight</th>
                  <th align="CENTER">AB Status</th>-->
				  <th align="CENTER">Status</th>
				  <th align="CENTER">Tools</th>
                </thead>
                <tbody>

			   ';


	while($BillRow=$BillExec->fetch_array()){
	
		if($BillRow['AbStatus']!=''){	
		$AbStatus=$BillRow['AbStatus'];
		$Colour=$BillRow['Colour'];
		}else{
		$AbStatus='Test Pending';
		$Colour='gray';
		}

		if($BillRow['LotStatus']== 2){
			$AbStatus='RETURN';
			$Colour='#FF7F50';
		}

		$FinancierName = strlen($BillRow['FinancierName']) > 10 ? substr($BillRow['FinancierName'],0,15)."..." : $BillRow['FinancierName'];
		$PurchaseDate = getFormatedDate($BillRow['PurchaseDate']);

		if($FinancierName==''){
		$FinancierName ='NA';
		}

if($BillRow['GradingDate']!=''){
$GradingDate = getFormatedDate(substr($BillRow['GradingDate'],0,10));
}else{
$GradingDate ='NA';
}

if($BillRow['CostingDate']!=''){
$CostingDate = getFormatedDate($BillRow['CostingDate']);
}else{
$CostingDate ='';
}

		$AddBill=false;
		if($BillRow['LotStatus']== 2){
			$STATUS='Lot Return';
			$STATUS_CLR='#f00';
		}else if($BillRow['GdAval']== ''){
			$STATUS='Grading Not Aval';
			$STATUS_CLR='#f00';
		}else if($BillRow['OrgnReport'] == 0){
			$STATUS='Rm Report Not Aval';
			$STATUS_CLR='#f00';
			
		}else if($BillRow['BillAval']== ''){
			$STATUS='Bill Not Aval';
			$STATUS_CLR='#f00';
			$AddBill=true;
		}else if($BillRow['BillStatus']== '0'){
			$STATUS='Bill Approval Pending';
			$STATUS_CLR='#f00';
			//$AddBill=true;
		}else if($BillRow['BillAval']!= ''){
		 $STATUS='All Clear';
		 $STATUS_CLR='Green';		 
		} 

				$content .= '<tr>

                <td  align="CENTER" ><font size="'.$text.'">'.$BillRow['RmLotID'].'</font></td>
                <td  align="CENTER"><font size="'.$text.'"><B>'.$BillRow['LotNumber'].'</B></font></td>
				<td  align="CENTER"><font size="'.$text.'"><B>'.$BillRow['Count'].'</B></font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.$PurchaseDate.'</font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.$GradingDate.'</font></td>
                <td  align="LEFT" ><font size="'.$text.'">'.$FinancierName.'</font></td>
                <td  align="CENTER" ><font size="'.$text.'">'.$BillRow['PurchaseType'].'</font></td>
				<!--<td  align="CENTER" width="10%"><font size="'.$text.'">'.$BillRow['PurchaseWeight'].'</font></td>
				<td align="CENTER" >
			    
				
				<div class="btn  badge badge-success font-size-12 " align="center"
				style="background: '. $Colour.'"			>
				<span class="label" align="center" >'.$AbStatus.'</span>
				</div>
				</td>-->

				<td align="CENTER" width="7%">
				<div style="background:'.$STATUS_CLR.'" class="btn btn-link badge badge-success font-size-12 " align="center" >
				<span class="label" align="center"  >'. $STATUS.'</span>
				</div>
			   
				</td>

		<td> ';

		$content.='<a target="_blank" href="../Purchase/LotPreview?LotID='. $BillRow['RmLotID'].'" 
		class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
		title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>';	 
		
		if($BillRow['LotStatus']== 2){
		$content.='<a target="_blank" href="../Purchase/ReturnBill?LotID='. $BillRow['RmLotID'].'" 
		class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
		title="" data-original-title="Edit"><i class="dripicons dripicons-printer font-size-18"></i></a>';
		}else if($AddBill){
			$content.='
			
			<a target="_blank" href="../Purchase/BillPreview?LotID='. $BillRow['RmLotID'].'" 
			class="text-success" data-toggle="tooltip" data-placement="top" 
			title="" data-original-title="Delete"><i class="mdi mdi-plus font-size-18"></i></a>
          
			';	

			
		}else if($BillRow['BillStatus']=='1'){
			$content.='
			
			<a target="_blank" href="BillReports?LotID='. $BillRow['RmLotID'].'" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-printer font-size-18"></i></a>
          
		  ';	 
		}

$content.='
			
			<a target="_blank" href="../Purchase/AddHLYeildRpt?LotID='. $BillRow['RmLotID'].'" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-plus font-size-18"></i></a>
          
		  ';

		$content.=' </td></tr>';
		
		

	 }
	
	$content.='</tbody></table>';

echo $content;





	?>
