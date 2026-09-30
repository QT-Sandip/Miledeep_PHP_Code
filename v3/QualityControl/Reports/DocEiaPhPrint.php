<?php
/*
ini_set('display_errors', 1); 
error_reporting(E_ALL);
session_start();

*/
$IsHeader=1;
$IsFooter=1;
$QueryParms=$_GET['Q'];


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";

$PoID=$_GET['PoID'];
$ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];
$RepID=$_GET['RID'];



   $sql="SELECT *,IHAB.TracebilityID as TraceId,IHAB.VerifiedBy as VerifiedBy FROM QcPhAbReports as IHAB 
left JOIN QcCodeList as cl on cl.CLID=IHAB.TracebilityID

left join QcTracebilityList as TL on TL.TraceID=cl.TracebilityID left join ScrmPoData as PD 
on PD.PoID=IHAB.InvID WHERE IHAB.ID='$RepID'";


$stmt_REP=$iconn->query($sql);
$RowIhRep=$stmt_REP->fetch_array();

 $TraceID=$RowIhRep['TraceId']; 
$TypeCountry=$RowIhRep['TypeCountry'];
$AnalysisDate=$RowIhRep['AnalysisDate'];
  
 $SqlOtherData="SELECT Brand,ShortName,McsTotal,DayCode,VerifiedBy,PL.PackingStyle FROM `QcCodeList` AS CL 
left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left JOIN ScrmPIVarietyList as VL on CL.VarietyID=VL.VarietyID WHERE CL.InvID='$PoID' AND CLID='$TraceID'";
$StmtOtherData=$iconn->query($SqlOtherData);

while($RowOtherData=$StmtOtherData->fetch_array()){
	$AryBrand[]=$RowOtherData['Brand'];
	$AryMcsTotal[]=$RowOtherData['McsTotal'];
	$AryDayCode[]=$RowOtherData['DayCode'];
	$AryVerifiedBy[]=$RowOtherData['VerifiedBy'];
	$AryPackStyle[]=$RowOtherData['PackingStyle'];
	$AryShortName[]=$RowOtherData['ShortName'];
}

		  $SqlCodeList="SELECT * FROM `QcCodeList` where CLID='$TraceID'";  
		 $StmtCodeList=$iconn->query($SqlCodeList);
		 $RowCodeList=$StmtCodeList->fetch_array();

		 $DayCode=$RowCodeList['DayCode'];



$Brand=implode(",",array_unique($AryBrand));
$McsTotal=implode(",",array_unique($AryMcsTotal));
$DayCodes=implode(",",array_unique($AryDayCode));
$PackStyle=implode(",",array_unique($AryPackStyle));
$VerifiedBy=implode(",",array_unique($AryVerifiedBy));
$ShortName=implode(",",array_unique($AryShortName));

$ID=$row['INVOICE_ID'];

//include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Constants.php");
$TypeCountry=1;
  $SqlResult="SELECT * FROM QcInHouseAbResult WHERE TypeCountry='$TypeCountry' and FromDate > $AnalysisDate"; 
//exit;

 // $SqlResult="SELECT * FROM QcInHouseAbResult WHERE TypeCountry='$TypeCountry' and DateId=1"; 
 //$SqlResult="SELECT * FROM QcInHouseAbResult WHERE TypeCountry='1'  "; 
$StmtResult=$iconn->query($SqlResult);
//ECHO $SqlResult; EXIT;

	//include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
	

$HEIGHT=8;
$header=10;
$sub_header=8;
$text=6;
$Text=8;
$SnoSize=5;
$Size=40;
$Size1=55;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->SetFont('','BU',12);
$pdf->Cell(0 ,5,'IN-HOUSE ELISA PRE HARVEST TEST REPORT FOR ANTIBIOTIC'.'('.$DayCode.')',0,1,'C');


$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(180 ,$HEIGHT,'SAMPLE PARTICULARS :',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$sub_header);



$D_TEST_RPRT_TMSP=strtotime($row['TEST_REPORT_DATE']);
$TEST_RPRT=date("d-m-Y", $D_TEST_RPRT_TMSP);

$D_SAMPLNG_TMSP=strtotime($row['DATE_OF_SAMPLING']);
$D_SAMPLING=date("d-m-Y",$D_SAMPLNG_TMSP);


$D_ANALYSIS_TMSP=strtotime($row['DATE_OF_ANALYSIS']);
$D_ANALYSIS=date("d-m-Y", $D_ANALYSIS_TMSP);



$D_ISSUANCE_TMSP=strtotime($row['DATE_OF_ISSUANCE']);
$D_ISSUANCE=date("d-m-Y", $D_ISSUANCE_TMSP);

		$SamplingTime= date("h:i A",strtotime($RowIhRep['SamplingTime'])); 

$OV_COUNT=1;


$W1=10;
$W2=60;
$W3=120;
$H1=5;

$w1='34%';
$w3='6%';
$w2='60%';
$Sno=0;
$PondID=explode("/",$RowIhRep['Tracebility']);
$Tracebility=$PondID[0];
$PondID=$PondID[1];
//$PondID=$RowIhRep['Tracebility'];

$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="3">

			<tr>  
           		<th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
           		<th width="'.$w1.'" align="left"><B>Test Report No.& date </B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['ReportNo'].' & '.getFormatedDate($RowIhRep['ReportDate']).'</B></th>				
           </tr>
		   <tr>  
		   <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
           		<th width="'.$w1.'" align="left"><B>Name and address of the Farmer </B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['FarmerName'].','.$RowIhRep['AddressContinued'].'</B></th>				
           </tr>
		   
		   <tr>  
		   <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Farm Reg. No / Regn. authority </B></th>
                <th width="'.$w2.'" align="left"><B>'.$Tracebility.'/'.$RowIhRep['RegAuthority'].'</B></th>				
                				
           </tr>
		   
		  

		   <tr>  
		        <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Farm ID and Pond ID </B></th>
               <th width="'.$w2.'" align="left"><B>'.$PondID.' </B></th>			
           </tr> 
		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Name of the supplier in case the raw material 
				received from the middleman) </B></th>
                <th width="'.$w2.'" align="left"><B>NA </B></th>				
           </tr> 
		   		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Type/Species Cultured </B></th>
                <th width="'.$w2.'" align="left"><B>LITOPENAEUS VANNAMEI</B></th>				
           </tr> 
		   
		   		   		   <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Name and address of Processor</B></th>
                <th width="'.$w2.'" align="left"><B>'.$EXPORTOR.'&'.$EXPORTOR_ADD.'</B></th>				
           </tr> 
		   
		   
		   	 <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Approx Quantity assessed by the establishment(Kg.)</B></th>
               <!-- <th width="'.$w2.'" align="left"><B>'.number_format((($RowIhRep['ReceivedQty']/$RowIhRep['Yield'])*100),3).' Kgs</B></th>	-->			
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['ReceivedQty'].' Kgs</B></th>				
           </tr> 
		   
		   	  <tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Approval number of Establishment</B></th>
                <th width="'.$w2.'" align="left"><B>'.$PROCESSOR_APP_NUM.'</B></th>				
           </tr> 
		   
		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Name of the representation/Technologist who 
				has drawn the sample</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['LabRep'].'</B></th>				
           </tr> 
		   

		   		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Date and time of sample collection </B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDate($RowIhRep['SamplingDate']).' & '.$SamplingTime.'</B></th>				
           </tr> 
		   
		    	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Sample ID/Code </B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['SampleID'].'</B></th>				
           </tr> 
		   
		   
		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Date of Analysis </B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDate($RowIhRep['AnalysisDate']).'</B></th>				
           </tr> 
		   
		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Date of Issuance </B></th>
                <th width="'.$w2.'" align="left"><B>'.getFormatedDate($RowIhRep['DateIssuence']).'</B></th>				
           </tr> 
		   
		   	<tr>  
		    <th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Date of Harvesting & Time </B></th>
                <th width="'.$w2.'" align="left"><B>'.date('d-m-Y h:i A',strtotime($RowIhRep['HarvestingDate'])).'</B></th>				
           </tr> 
		   
</table> ';


$pdf->writeHTML($FARMER_NAME);




$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(180 ,$HEIGHT,'TEST RESULT :',0,1,'L');
$pdf->SetFont($FONT_ARI,'B',$sub_header);

$Sno=0;
$content='<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="center"  width="'.($SnoSize).'%"><font size="'.$Text.'">S/N</font></th>
           		<th  align="center"  width="30%"><font size="'.$Text.'">PARAMETERS TESTED</font></th>
                <th  align="center" width="20%"><font size="'.$Text.'">Cut Off Value(µg/kg)</font></th>
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

$pdf->Ln();

$HEIGHT=5;
$pdf->Cell(130 ,$HEIGHT,'1.Test results are based on ELISA screening. ',0,1);
$pdf->Cell(130 ,$HEIGHT,'2.Test result should be reported as Screened positive or negative.',0,1);
$pdf->Cell(130 ,$HEIGHT,'3.Values obtained below detection limit can be reported as negative and above detection limit as positive.',0,1);
$pdf->Cell(130 ,$HEIGHT,'4.Actual values obtained above detection limit as positive shall be reported.',0,1);

$pdf->Ln();

$pdf->SetFont($FONT_ARI,'B',$sub_header);

$pdf->Cell(95,5,'Tested By :'.$RowIhRep['TestedBy'],1,0,'L',0);
$pdf->Cell(95,5,'Verified By :'.$RowIhRep['VerifiedBy'],1,1,'L',0);  // cell with left and right borders


$pdf->Cell(95,10,'Signature',1,0,'L',0);
$pdf->Cell(95,10,'Signature',1,1,'L',0);  // cell with left and right borders

//$pdf->Cell( 189, 40, $pdf->Image($footer, null, 0, 180), 0, 0, 'L' );

$pdf->Ln();
 //$pdf->Cell(12,5, $pdf->Image($footer, 10,265, 190,0),0,0,'C',0);
 
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

}?>