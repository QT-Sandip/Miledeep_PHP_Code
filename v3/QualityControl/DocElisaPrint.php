<?php
//ini_set('display_errors', 1); 

session_start();

$IsHeader=1;
$IsFooter=1;
$QueryParms=$_GET['Q'];
$RepID=$_GET['id'];
  

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include "PrintHeaderInc.php";
include ($cp."Includes/IncludePage.php");

$PoID=$_GET['PoID'];
$ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];




 $sql="SELECT * FROM $TblQcInHouseAbReport as IHAB
left join $TblQcTracebilityList as TL on IHAB.TracebilityID=TL.TraceID 
left join $TblScrmPoData as PD on PD.PoID=IHAB.InvID WHERE IHAB.ID='$RepID'"; 
$stmt_REP=$iconn->query($sql);
$RowIhRep=$stmt_REP->fetch_array();
echo $RowIhRep['SamplingDate']; exit;
$PoID=$RowIhRep['InvID'];
$TraceID=$RowIhRep['TracebilityID'];
$TypeCountry=$RowIhRep['TypeCountry'];

 $SqlOtherData="SELECT Brand,ShortName,McsTotal,DayCode,VerifiedBy,PackingStyle FROM `QcCodeList` AS CL 
left join $TblAdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left JOIN $TblQcPoVarityList as VL on CL.VarietyID=VL.VarietyID WHERE CL.InvID='$PoID' AND TracebilityID='$TraceID'";
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


$SqlResult="SELECT * FROM $TblQcInHouseAbResult WHERE TypeCountry='$TypeCountry'";
$StmtResult=$iconn->query($SqlResult);
 
$pdf->SetFont('helvetica','BU','10');
$pdf->Cell(0 ,5,'IN-HOUSE ELISA TEST REPORTS FOR ANTIBIOTIC',0,1,'C');
$pdf->Cell(130 ,5,'',0,1,'C');

$HEIGHT=8;
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
 $content .= '<table border="1" cellspacing="0" cellpadding="3">  
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
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['LotNo'].'</font></th>
				</tr> 
				
				<tr>  
           		<th  align="LEFT"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Scientific Name of the Species</font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.$RowIhRep['SciName'].'</font></th>
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
           		<th  align="LEFT"  width="'.($Size).'%"><font size="'.$Text.'">Date of Sampling </font></th>
                <th  align="LEFT" width="'.($Size1).'%"><font size="'.$Text.'">'.date('d-m-Y h:i:A',strtotime($RowIhRep['SamplingDate'])).'</font></th>
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

				
				</table>';	

$pdf->writeHtml($content);	

$Sno=1;
$content='<table border="1" cellspacing="0" cellpadding="3">  
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

 		
				
ob_end_clean(); 

               
if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowIhRep['PoNo'];
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