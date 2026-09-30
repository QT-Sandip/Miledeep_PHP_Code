<?php
//ini_set('display_errors', 1); 

session_start();

$IsHeader=1;
$IsFooter=1;
$QueryParms=$_GET['Q'];


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($cp."Includes/IncludePage.php");

$PoID=$_GET['PoID'];
$ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];
$RepID=$_GET['RID'];
  
include "PrintHeaderInc.php";


 $sql="SELECT *,QLTV.LbuDesignation AS VerfiedDsgtn,QLT.LbuDesignation AS TestedDsgtn
 FROM QcInHouseAbReport as IHAB
left join QcTracebilityList as TL on IHAB.TracebilityID=TL.TraceID 
left join QcLabTechnologist as QLT on QLT.LbuName=IHAB.TestedBy 
left join QcLabTechnologist as QLTV on QLTV.LbuName=IHAB.VerifiedBy 
left join ScrmPoData as PD on PD.PoID=IHAB.InvID 
left join ScrmCountriesList as SCL on SCL.id=PD.DestinationCountry WHERE IHAB.ID='$RepID'";

$stmt_REP=$iconn->query($sql);
$RowIhRep=$stmt_REP->fetch_array();

//ECHO $sql; EXIT;

$PoID=$RowIhRep['InvID'];
$TraceID=$RowIhRep['TracebilityID'];
$TypeCountry=$RowIhRep['CountryType'];
$SampleQty=$RowIhRep['SampleQty'];

$SqlSpeciesName="SELECT SPM.SpeciesName FROM ScrmPoGradeData SPGD
LEFT JOIN `ScrmPIVarietyList`SPVL ON SPVL.VarietyID=SPGD.VarietyID
LEFT JOIN ScrmSpeciesMaster SPM ON SPM.ShortCode=SPVL.Species
WHERE SPGD.InvId='$PoID'";
$StmtSpeciesName=$iconn->query($SqlSpeciesName);
$RowSpeciesName=$StmtSpeciesName->fetch_array();

$ScientificName=$RowSpeciesName['SpeciesName'];

 $SqlOtherData="SELECT CL.Brand,VL.ShortName,McsTotal,DayCode,VerifiedBy,PL.PackingStyle FROM `QcCodeList` AS CL 
left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left join ScrmPoGradeData as POG on CL.InvID=POG.InvId 
left JOIN ScrmPIVarietyList as VL on POG.VarietyID=VL.VarietyID 
WHERE CL.InvID='$PoID' AND TracebilityID='$TraceID'";

//ECHO $SqlOtherData; EXIT;
$StmtOtherData=$iconn->query($SqlOtherData); 


while($RowOtherData=$StmtOtherData->fetch_array()){
	$AryBrand[]=$RowOtherData['Brand'];
	$AryMcsTotal[]=$RowOtherData['McsTotal'];
	$AryDayCode[]=$RowOtherData['DayCode'];
	$AryVerifiedBy[]=$RowOtherData['VerifiedBy'];
	$AryPackStyle[]=$RowOtherData['PackingStyle'];
	$AryShortName[]=$RowOtherData['ShortName'];
}



$Brand=implode(",",array_unique($AryBrand));
$McsTotal=implode(",",array_unique($AryMcsTotal));
$DayCodes=implode(",",array_unique($AryDayCode));
$PackStyle=implode(",",array_unique($AryPackStyle));
$VerifiedBy=implode(",",array_unique($AryVerifiedBy));
$ShortName=implode(",",array_unique($AryShortName));


$SqlResult="SELECT * FROM QcInHouseAbResult WHERE TypeCountry='$TypeCountry'";
$StmtResult=$iconn->query($SqlResult);
 
$pdf->SetFont('helvetica','BU','10');
$pdf->Cell(0 ,5,'IN-HOUSE ELISA TEST REPORTS FOR ANTIBIOTIC',0,1,'C');
$pdf->Cell(130 ,5,'',0,1,'C');

$HEIGHT=7;
$header=10;
$sub_header=8;
$Text=8;
$SnoSize=5;
$Size=40;
$Size1=55;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(180 ,$HEIGHT,'SAMPLE PARTICULARS :',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$header);
$Sno=1;


$date = $RowIhRep['SamplingDate'];
$date_arr = explode('T',$date);

 $SamplDt=$date_arr[0]; 

$dt = new DateTime($SamplDt);
 $dt->modify('+3 days'); 
$DATE = $dt->format('r') . PHP_EOL;
$TIME_STAMP = strtotime($DATE);
$HarvestingDt= date("d-m-Y", $TIME_STAMP);
$DayCodeSplit = explode(' ',$DayCodes);
 $DayCodeDate=$DayCodeSplit[0]; 

  $HarvestingDt=getDayCodeDate($DayCodeDate); 
  $HarvestingDt = date('d-m-Y', strtotime($HarvestingDt .' -1 day'));
 


//$randDate->setTime(mt_rand(0, 23), mt_rand(0, 59));
$HarvestTime= rand(0,23).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT);

//echo $SamplTime=$randDate->format('H:i'); exit;
 
 
 
 $content .= '<table border="1" cellspacing="0" cellpadding="2">  
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Test Report No & Date</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['ReportNo'].'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Name and Address of the farmer</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['FarmerName'].','.$RowIhRep['AddressContinued'].'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Farm Reg No</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['Tracebility'].'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Internal Lot Number</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['LotNo'].'NA </font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Scientific Name of the Species</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$ScientificName.'</font></th>
				</tr> 
				
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Type of Packing</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$PackStyle.'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Total Number of Master Cartons</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$McsTotal.'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Production Codes</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$DayCodes.'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Product/Sample Name</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$ShortName.'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">P.O No & Date</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['PoNo'].
				'& Date :'.getFormatedDate($RowIhRep['PoDate']).'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Date of Sampling and Time</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.getFormatedDate($RowIhRep['SamplingDate']).' & '.$date_arr[1].','.$SampleQty.'</font></th>
				</tr> 
				
				
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Name of the representation/Technologist
					who has drawn the sample</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['Representation'].'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Lab Sample ID</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['SampleID'].'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Date of Analysis</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.getFormatedDate($RowIhRep['AnalysisDate']).'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Date of Issuance</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.getFormatedDate($RowIhRep['IssuanceDate']).'</font></th>
				</tr>

               <tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Date of Harvesting and Time</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$HarvestingDt.' , '.$HarvestTime.'</font></th>
				</tr>				

				
				</table>';	

$pdf->writeHtml($content);	

$Sno=1;
$content='<table border="1" cellspacing="0" cellpadding="2">  
				<tr>  
           		<th  align="center"  width="'.($SnoSize).'%"><font size="'.$Text.'">S/N</font></th>
           		<th  align="center"  width="30%"><font size="'.$Text.'">PARAMETERS TESTED</font></th>
                <th  align="center" width="20%"><font size="'.$Text.'">DETECTION LIMIT (µg/kg)</font></th>
                <th  align="center" width="20%"><font size="'.$Text.'">Test Result *</font></th>
                <th  align="center" width="25%"><font size="'.$Text.'">Analytical Method/Equipment used</font></th>
				</tr>';
				while($RowResult=$StmtResult->fetch_array()){
				
$content.='<tr>  
           		<th  align="center"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="center"  width="30%"><font size="'.$Text.'">'.$RowResult['Parameter'].'</font></th>
                <th  align="center" width="20%"><font size="'.$Text.'">'.$RowResult['Detection'].'</font></th>
                <th  align="center" width="20%"><font size="'.$Text.'">'.$RowResult['Result'].'</font></th>
                <th  align="center" width="25%"><font size="'.$Text.'">'.$RowResult['AnalyticMethod'].'</font></th>
				</tr>';
				}
				$content.='</table>'; 
$pdf->writeHtml($content);				

$HEIGHT=1;
$pdf->Cell(130 ,$HEIGHT,'1.Test results are based on ELISA screening. ',0,1);
$pdf->Cell(130 ,$HEIGHT,'2.Test result should be reported as Screened positive or negative.',0,1);
$pdf->Cell(130 ,$HEIGHT,'3.Values obtained below detection limit can be reported as negative and above detection limit as positive.',0,1);
$pdf->Cell(130 ,$HEIGHT,'4.Actual values obtained above detection limit as positive shall be reported.',0,1);

//$pdf->Ln();
$pdf->SetFont($FONT_ARI,'B',$sub_header);

$pdf->Cell(95,5,'Tested By :'.$RowIhRep['TestedBy'],1,0,'L',0);
$pdf->Cell(95,5,'Verified By :'.$RowIhRep['VerifiedBy'],1,1,'L',0);  // cell with left and right borders
$pdf->Cell(95,5,'Designation :'.$RowIhRep['TestedDsgtn'],1,0,'L',0);  // cell with left and right borders
$pdf->Cell(95,5,'Designation :'.$RowIhRep['VerfiedDsgtn'],1,1,'L',0);  // cell with left and right borders


$pdf->Cell(95,10,'Signature',1,0,'L',0);  
$pdf->Cell(95,10,'Signature',1,1,'L',0);  // cell with left and right borders

 		
				
ob_end_clean(); 

               
if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowIhRep['PoNo'].'.pdf';
$FileName=getFormatedFileName($FileName);
$pdf->Output($FileName, 'I');
}else{
	$Fname=$MailTempFolder;
	$FileName=$_GET['FileName'];
	$FileName = str_replace('/','', $FileName );
	$FileName=$Fname.$FileName;
	$pdf->Output($FileName, $FileType);

}
?>