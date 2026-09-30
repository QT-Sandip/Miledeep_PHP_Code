<style>


th {
  height: 1px;
}
</style>

<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

   $QueryParms=$_GET['Q']; 
   $Type=$_GET['Type'];
   $Date=$_GET['Dt'];
 
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
 $AccessType=$_SESSION["AccessType"];
 $AccessType=1;

   $Month=$_GET['Month']; 
 $Year=$_GET['Year'];
 
 $PsID=$_GET['PsID'];
  $ContractorID=$_GET['ContractorID'];
 //$ContactorID=44;

  

		 class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
				
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 15);
		$this->Cell(190, 0, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}




    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	

    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('PD Billing Report : ');  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(10);	
    $pdf->SetMargins(10, 10, 10,10);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage(L);  
	
	
	
	$table_data=getDS_TABLE($iconn,$Year,$Month,$PsID,$ContractorID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);

$file_name='PDBillingReport-'.$PoID.'.pdf';

ob_clean();
$pdf->Output($file_name);

		 
function getDS_TABLE($iconn,$Year,$Month,$PsID,$ContractorID){

global $ContractorID,$Type,$AccessType;
if($Type==1){
	$Query= "Date between '$Month'  and '$Year'  "; 
	
 }else {
$Query= "month(Date)='$Month'  and year(Date)='$Year'  ";

 }
		
		
$SqlBatchIDs="SELECT GROUP_CONCAT(SubContactorID) AS SUBID FROM
 `PpcSubContractorMaster` WHERE ContactorID='$ContractorID'";
 $StmtBatchIDs=$iconn->query($SqlBatchIDs);
$RowBatchIDs= $StmtBatchIDs->fetch_array();
//echo $SqlBatchIDs;exit;
			 
$SqlContractor="SELECT ContrctrName FROM `PpcContractMaster`  WHERE ContractorID='$ContractorID'";
$StmtContractor=$iconn->query($SqlContractor);
//echo $SqlContractor; exit;
$RowContractor= $StmtContractor->fetch_array();

$ContractorName=$RowContractor['ContrctrName'];

$SubContactorID=$RowBatchIDs['SUBID'];
	 
$SqlDailyWages="SELECT * FROM `PpcDailyBatchDetails` PDBD
LEFT JOIN PpcSubContractorMaster PSM ON PSM.SubContactorID=PDBD.BatchID
LEFT JOIN PpcOptions HSD ON HSD.OptID=PDBD.ShiftID
WHERE BatchID IN ($SubContactorID) AND  $Query ";			  
//echo $SqlDailyWages;exit;
			 
$StmtDailyWages=$iconn->query($SqlDailyWages);
$i=0;				  
$Whitespacee='<b style="color:white">  jhyg</b>';


	$text=4;
	$text1=2;
	
	$SNO=0;
	$PREV_DATE='';
    $ExporterName=$_SESSION["ClientName"];




$width=100;

	
$width_s=7.7;

$widthh=25;
$size=9;
$size1=10;
$height=1;
$BgClr='LIGHT GREY';		



  $content .= '
			  <table border="1" cellspacing="0" cellpadding="5" align="center" >
			   
				
				<tr><th  align="center"  width="'.$width.'%"><B><font size="10">CONTRACTOR: '.$ContractorName.'</font>
				</b></th>  
                </tr> 
            

	           <tr> 	<th  align="CENTER" width="'.$width.'%" ><font size="10"> <B>DATE - '.getFormatedDate($Month).''.$Whitespacee.' TO'.$Whitespacee.' DATE - '.getFormatedDate($Year).'</B></font></th> </tr>   
				 

				<tr  >  

				<th bgcolor="'.$BgClr.'" align="left"  width="'.$width_s.'%"> <font size="'.$size.'">S.No  </FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left"  width="'.$width_s.'%"> <font size="'.$size.'">Mestri Details</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Date</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">No.Of Workers</FONT> </th>         
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Count</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Variety</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Quantity</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Rate</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Amount</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Sunday Extra</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Transport Charges</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Mestri Charges</FONT> </th>    
				<th bgcolor="'.$BgClr.'" align="left" width="'.$width_s.'%"> <font size="'.$size.'">Mestri Day Total</FONT> </th>    
				
				</tr> 
				
				  ';

	
				  $Recordcount=mysqli_num_rows($StmtDailyWages);
$S_NO=1;



$TotalHLWeight='';
$TotalWageAmount='';
 
 
while($RowDailyWages = $StmtDailyWages->fetch_array()){

$BatchName=$RowDailyWages['SubContractorName']; 		
$BatchID=$RowDailyWages['BatchID']; 				
$ShiftName=$RowDailyWages['Name']; 		
$ShiftID=$RowDailyWages['OptID']; 		
$VarietyType=$RowDailyWages['Type']; 		
$NoOfPersons=$RowDailyWages['Persons']; 
$ProcessingDate=date("d-m-Y", strtotime($RowDailyWages['Date']));
$BreakupID=$RowDailyWages['ID']; 
$Adjustments=$RowDailyWages['Adjustments']; 
$Extras=$RowDailyWages['Extras']; 
$AutoCharge=$RowDailyWages['AutoCharge']; 
$MestriComissions=$RowDailyWages['Comissions']; 

$AryRawData=getBreakUpDetails($RowDailyWages);





foreach($AryRawData as $Item){
	//print_r($Item); exit;
	$RmLotNumber=$Item['LotNumber'];
	$HLQty=$Item['Qty'];
	$PricePerKg=$Item['PricePerKg'];
	$LotCount=$Item['PurchaseCount'];
	$TransPortTotal=$Item['TransPortTotal'];
	$MestriCharges=$Item['MestriCharges'];
	$Bonus=$Item['Bonus'];
	$AvgDayPrice=$Item['AvgDayPrice'];
	$AvgDayHlKgs=$Item['AvgDayHlKgs'];
	$VarietyName=$Item['Variety'];
	$AvgDayCostToCmny=$Item['AvgDayCostToCmny'];
	$TotalAmountPerDay=$Item['Price']+$Item['OtherCharges'];

	 
		$content .='<tr nobr="true">
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$S_NO.' '.$SNO=''.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$BatchName.'</font></th> 
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$ShiftName.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$NoOfPersons.'</font></th>
				 <th  align="center" width="'.$width_s.'%" ><font size="'.$size.'">'.$ProcessingDate.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$RmLotNumber.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'. $LotCount .'<br>('.$VarietyName.') </font></th>
				 <th style="color:'.$COLOR .'" align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$HLQty.'  Kgs </font></th>
                 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$PricePerKg.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$TransPortTotal.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$MestriCharges.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$Bonus.'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="'.$size.'">'.$TotalAmountPerDay.'</font></th>';
	$content .= '
                 </tr>';
$TransPortTotal='';
$MestriCharges='';
$Bonus='';
$AvgAmount='';
$AvgQuantity='';
$AvgAmountPerPerson='';
 

$S_NO++;
//$i++;

}
	 

		}
	

$content .=

    '<tr>

   <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"><B>Total </B></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"></font></th>
   <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"><B>'.round(($TotalHLWeight),2).'Kgs</B></font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>
     <th  align="center" width="'.$width_s.'%"><font size="2"></font></th>

  <th  align="center" width="'.$width_s.'%"><font size="'.$size.'"><B>'.round(($TotalWageAmount),2).'</B></font></th>
   
   
   
    </tr>';
     
	// echo $S_NO; exit;
	 $content .= '<br></table>
					  <table border="0" cellspacing="0" cellpadding="5" align="center" >
					  <BR><BR>
   
   <BR><BR>
   
   <tr>
<th  align="left" width="100%"><font size="'.$size1.'">Average Rate Per Kg:<B>'.round(($uperterm/$TotalHLWeight),2).'</B></font></th>

   </tr>
			
   <BR><BR>
   <tr>
<th  align="center" width="'.$widthh.'%"><font size="'.$size1.'"><B>Prepared By</B></font></th>
 <th  align="center" width="'.$widthh.'%"><font size="'.$size1.'"><B>Checked By</B></font></th>
 <th  align="center" width="'.$widthh.'%"><font size="'.$size1.'"><B>Verified By</B></font></th>
 <th  align="center" width="'.$widthh.'%"><font size="'.$size1.'"><B>Approved By</B></font></th>
   </tr>
					  
					  </table>

		';
		
	return $content;

}


function getBreakUpDetails($RowDailyWages){
global $iconn;	

$BatchName=$RowDailyWages['SubContractorName']; 		
$BatchID=$RowDailyWages['BatchID']; 				
$ShiftName=$RowDailyWages['Name']; 		
$ShiftID=$RowDailyWages['OptID']; 		
$VarietyType=$RowDailyWages['Type']; 		
$NoOfPersons=$RowDailyWages['Persons']; 
$ProcessingDate=date("d-m-Y", strtotime($RowDailyWages['Date']));
$BreakupID=$RowDailyWages['ID']; 
$Adjustments=$RowDailyWages['Adjustments']; 
$Extras=$RowDailyWages['Extras']; 
$AutoCharge=$RowDailyWages['AutoCharge']; 
$MestriComissions=$RowDailyWages['Comissions']; 


$TransPortTotal=($AutoCharge*$NoOfPersons)+$Adjustments;
$MestriCharges=	($MestriComissions*$NoOfPersons)+$Extras;


if($VarietyType==1){
$LotCount=$LotDetails['RmCount'];			
$VarietyID=1;
}else{
$LotCount=$RowBreakUpDtls['Count'];
}



if($ShiftID ==2 || $ShiftID ==4 || $ShiftID ==5){
$ShiftID =1;	
$ShiftNm =1;	
}
if ($ShiftID ==3){	
$ShiftNm =2;	
}else{
$ShiftNm =1;	
}
//$VarietyID=1;
$AryRawData=getHLWageDetails($VarietyID,$ShiftNm,$BreakupID );

$TotalBouns=0;
$Price=0;
$TotalHlKgs=0;
foreach($AryRawData as $Item){
$Today = date('l', $ProcessingDate);
$PricePerKg=$Item['PricePerKg'];
$HlQty=$Item['Qty'];
$Price+=$Item['Price'];
$TotalHlKgs+=$Item['Qty'];
if($Today=='Sunday' && $ShiftID==3 && $VarietyType==1){
$Bonus=$HlQty*$PricePerKg;	
}else if($Today=='Sunday' && $ShiftID==3){	
$Bonus=$HlQty*($PricePerKg/4);
//25% so 1/4	
}else if($ShiftID==3 && $VarietyType !=1){
$Bonus=	$NoOfPersons*50;
}
$TotalBouns+=$Bonus;
}
$OtherCharges=$TransPortTotal+$MestriCharges+$Bonus;
$AvgDayPrice=$Price/$NoOfPersons;
$AvgDayHlKgs=$TotalHlKgs/$NoOfPersons;
$AvgDayCostToCmny=($Price+$OtherCharges)/$NoOfPersons;

$index=count($AryRawData)-1;
$index=0;

$AryRawData[0]['Bonus']=$TotalBouns;
$AryRawData[0]['TransPortTotal']=$TransPortTotal;
$AryRawData[0]['MestriCharges']=$MestriCharges;
$AryRawData[0]['OtherCharges']=$OtherCharges;
$AryRawData[0]['AvgDayPrice']=round($AvgDayPrice,2);
$AryRawData[0]['AvgDayHlKgs']=round($AvgDayHlKgs,2);
$AryRawData[$index]['AvgDayCostToCmny']=round($AvgDayCostToCmny,2);

return $AryRawData;	
}


function getHLWageDetails($VarietyID,$ShiftID,$BreakupID ){
	global $iconn;
echo $VarietyID; 
	
	if($VarietyID==1){
	$Qry="(SELECT PricePerKg FROM `PpcPeelingPrices` WHERE VarietyID=$VarietyID 
			and  LowerLimit <=RMD.PurchaseCount 
			and UpperLimit >= RMD.PurchaseCount and ShiftID=$ShiftID)";
			
	 $SqlBrkDetailsQry="SELECT RMD.RmLotID,'HL' as ShortName, RMD.LotNumber,RMD.PurchaseCount,sum(BBD.HLQty) as Qty,
			$Qry as PricePerKg,
			sum(BBD.HLQty * $Qry) as Price FROM `PpcDailyBatchBreakUpDeatils`  as BBD 
			left JOIN RmLotDetatils as RMD on BBD.LotID=RMD.RmLotID 
				WHERE DailyBatchID=$BreakupID group by RMD.RmLotID 
				ORDER by RMD.LotNumber asc";
	}else{
	$Qry="(SELECT PricePerKg FROM `PpcPeelingPrices` WHERE VarietyID=BBD.VarietyID and 
	LowerLimit <=BBD.Count and UpperLimit >= BBD.Count and ShiftID=$ShiftID) ";
		
		 $SqlBrkDetailsQry="SELECT RMD.RmLotID,RMD.LotNumber,
		RMD.PurchaseCount,sum(BBD.HLQty) as Qty, ShortName,$Qry as PricePerKg,
 sum(BBD.HLQty * $Qry) as Price FROM `PpcDailyBatchPeelingBreakUpDeatils` as BBD 
left JOIN RmLotDetatils as RMD on BBD.LotID=RMD.RmLotID
left join ScrmPIVarietyList as SPL on SPL.VarietyID=BBD.VarietyID
WHERE DailyBatchID=103 group by RMD.RmLotID ORDER by RMD.LotNumber asc;";	
	}
	$StmtBrkDetailsQry=$iconn->query($SqlBrkDetailsQry);
	while($RowLotDtls=$StmtBrkDetailsQry->fetch_array()){
	$RowLotDtls['Bonus']='';
	$RowLotDtls['TransPortTotal']='';
	$RowLotDtls['MestriCharges']='';
	$RowLotDtls['Variety']=$RowLotDtls['ShortName'];
	$Data[]=$RowLotDtls;
	}		
	
	return $Data;
}
	


?>
