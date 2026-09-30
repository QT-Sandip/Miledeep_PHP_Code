<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL);
//session_start();
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



$sqlReportData="SELECT *,IHAB.TracebilityID as TraceId,QLT.LbuDesignation AS TesterDesgn
,QLV.LbuDesignation AS VerifiedDesgn,IHAB.VerifiedBy as VerifiedBy ,TL.Address,TL.AddressContinued,
SCD.ConsigneeName FROM QcPhAbReports as IHAB 
left JOIN QcCodeList as cl on cl.CLID=IHAB.TracebilityID
left join QcTracebilityList as TL on TL.TraceID=cl.TracebilityID
left join QcLabTechnologist as QLT on QLT.LbuName=IHAB.TestedBy
left join QcLabTechnologist as QLV on QLV.LbuName=IHAB.VerifiedBy
left join ScrmPoData as PD on PD.PoID=IHAB.InvID
left join ScrmConsigneeDetails SCD ON SCD.ConID=PD.ConsigneeID
 WHERE IHAB.ID='$RepID'";

$stmtReportData=$iconn->query($sqlReportData);
$RowIhRep=$stmtReportData->fetch_array();

//ECHO $sqlReportData; EXIT;
$TraceID=$RowIhRep['TraceId']; 
$ExporterID=$RowIhRep['ExporterID'];
$ProcessorID=$RowIhRep['ProcessorID'];
$InvoiceNumber=$RowIhRep['InvNumFull'];
$PoNumber=$RowIhRep['PoNo'];
$ConsigneeName=$RowIhRep['ConsigneeName'];
$DestinationCountry=$RowIhRep['DestinationCountry'];
$ReportNo=$RowIhRep['ReportNo'];
$ReportDate = date("d-m-Y", strtotime($RowIhRep['ReportDate']));

$SqlCountryType="SELECT CountryType FROM `ScrmCountriesList`   WHERE id='$DestinationCountry'"; 
$StmtCountryType=$iconn->query($SqlCountryType);
$RowCountryType=$StmtCountryType->fetch_array();

$TypeCountry=$RowCountryType['CountryType'];
  
$SqlOtherData="SELECT SPD.Brand,SPD.Variety,McsTotal,DayCode,VerifiedBy,PL.PackingStyle FROM `QcCodeList` AS CL 
left join AdminPackingStyles as PL on CL.PackingStyleID=PL.PCKID 
left JOIN ScrmPoGradeData as SPD ON SPD.ID=CL.PoGradeID

WHERE CL.InvID='$PoID' AND CLID='$TraceID'";

//echo $SqlOtherData; exit;
$StmtOtherData=$iconn->query($SqlOtherData);

while($RowOtherData=$StmtOtherData->fetch_array()){
	$AryBrand[]=$RowOtherData['Brand'];
	$AryMcsTotal[]=$RowOtherData['McsTotal'];
	$AryDayCode[]=$RowOtherData['DayCode'];
	$AryVerifiedBy[]=$RowOtherData['VerifiedBy'];
	$AryPackStyle[]=$RowOtherData['PackingStyle'];
	$AryShortName[]=$RowOtherData['Variety'];
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
//$ShortName=implode(",",array_unique($AryShortName));
$VarietyFullName= implode(",",array_unique($AryShortName));


$ID=$row['INVOICE_ID'];


//$TypeCountry=1;
$SqlResult="SELECT * FROM QcInHouseAbResult WHERE TypeCountry='$TypeCountry' and DateId=1 ORDER BY SortOrder ASC"; 
$StmtResult=$iconn->query($SqlResult);


$HEIGHT=8;
$header=10;
$sub_header=8;
$text=6;
$Text=8;
$SnoSize=5;
$Size=40;
$Size1=55;

$font_path=$_SERVER['DOCUMENT_ROOT']."/fonts/Roboto-Medium.ttf";
//$fontname = TCPDF_FONTS::addTTFfont( $font_path, 'TrueTypeUnicode', '', 96);
$fontname ='robotomedium';

//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//$pdf->addPage();
$pdf->SetFont($fontname, '',6); //

//$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->SetFont('','BU',12);
$pdf->Cell(0 ,5,$ReportName,0,1,'C');
//$pdf->Cell(0 ,5,'IN-HOUSE ELISA PRE HARVEST TEST REPORT'.'('.$DayCode.')',0,1,'C');


$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Cell(180 ,$HEIGHT,'SAMPLE PARTICULARS :',0,0,'L');
$pdf->SetFont($FONT_ARI,'B',$header);
$pdf->Cell(180 ,$HEIGHT,'Rec No :'.$RowIhRep['ReportNo'],0,1,'R');
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

if($RowIhRep['FarmerCode']!=''){
	$FarmerCode=$RowIhRep['FarmerCode'].' - ';
}
$Tracebility= $FarmerCode.$RowIhRep['Tracebility'] ;

$ExporterData = getExporterDetails($ProcessorID);
$ExporterName=$ExporterData['ExpName'];
$ExpAddress=$ExporterData['ExpAddress'];


$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="2">

			
		   	<tr>  
		   		<th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
           		<th width="'.$w1.'" align="left"><B>Name and address of the Farmer  </B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['FarmerName'].','.$RowIhRep['AddressContinued'].'</B></th>				
            </tr>

		    <tr>  
		    	<th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Production Code </B></th>
            	<th width="'.$w2.'" align="left"><B> - </B></th>			
            </tr> 
		   
		    <tr>  
				<th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Farm ID and Pond ID(or) Farm Reg. No / Regn. authority </B></th>
               <!-- <th width="'.$w2.'" align="left"><B>'.$Tracebility.'/'.$PondID.'</B></th>	-->			    				
                <th width="'.$w2.'" align="left"><B>'.$Tracebility.'</B></th>				    				
           	</tr>
		   
	
		   <tr>  
		    	<th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Name of the supplier in case the raw material is
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
                <th width="'.$w2.'" align="left"><B>'.$ExporterName.' , '.$ExpAddress.'</B></th>				
           </tr> 
		   
		   	<tr>  
		    	<th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
				<th width="'.$w1.'" align="left"><B>Quantity of sample</B></th>
               <!-- <th width="'.$w2.'" align="left"><B>'.number_format((($RowIhRep['ReceivedQty']/$RowIhRep['Yield'])*100),3).' Kgs</B></th>	-->			
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['SamplingQty'].' </B></th>				
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
				<th width="'.$w1.'" align="left"><B>Quantity received at the Establishment in KGs</B></th>
                <th width="'.$w2.'" align="left"><B>'.$RowIhRep['ReceivedQty'].' Kgs</B></th>				
           </tr> 
		   
		   

</table> ';


$EUColumn=' <table border="1" cellspacing="0" cellpadding="2">
     <tr>  
			<th width="'.$w3.'" align="left"><B>'.++$Sno.'</B></th>
			<th width="'.$w1.'" align="left"><B>Test Report No & Date</B></th>
			<th width="'.$w2.'" align="left"><B>'.$ReportNo.' &'.$ReportDate.'</B></th>	

      </tr> </table> ';

	  if($TypeCountry==1){
$pdf->writeHTML($FARMER_NAME);
	  }else{
		$pdf->writeHTML($FARMER_NAME.$EUColumn);
		
	  }
$pdf->SetFont($FONT_ARI,'BU',$header);
// $pdf->Cell(180 ,$HEIGHT,'TEST RESULT :',0,1,'L');
$pdf->Cell(180, $HEIGHT, 'TEST RESULT :', 0, 1, 'C');
$pdf->SetFont($FONT_ARI,'B',$sub_header);

// $pdf->SetFont($FONT_ARI, '', 12);

$Sno=0;
$content='<table border="1" cellspacing="0" cellpadding="1">  
				<tr>  
           		<th  align="center"  width="'.($SnoSize).'%"><font size="'.$Text.'">S/N</font></th>
           		<th  align="center"  width="22%"><font size="'.$Text.'">PARAMETERS TESTED</font></th>
                <th  align="center" width="12%"><font size="'.$Text.'">RPA(µg/kg)</font></th>
                <th  align="center" width="12%"><font size="'.$Text.'">CC  β	(µg/kg)</font></th>
                <th  align="center" width="12%"><font size="'.$Text.'">Cut off value (µg/kg)</font></th>
                <th  align="center" width="12%"><font size="'.$Text.'">Test Result *</font></th>
                <th  align="center" width="23%"><font size="'.$Text.'">Analytical Method/Equipment used</font></th>
				</tr>';
				while($RowResult=$StmtResult->fetch_array()){
				
$content.='<tr>   
           		<th  align="center"  width="'.($SnoSize).'%"><font size="'.$Text.'">'.++$Sno.'</font></th>
           		<th  align="center"  width="22%"><font size="'.$Text.'">'.$RowResult['Parameter'].'</font></th>
                <th  align="center" width="12%"><font size="'.$Text.'">'.$RowResult['RPA'].'</font></th>
                <th  align="center" width="12%"><font size="'.$Text.'">'.$RowResult['CCBLimits'].'</font></th>
                <th  align="center" width="12%"><font size="'.$Text.'">'.$RowResult['Detection'].'</font></th>
                <th  align="center" width="12%"><font size="'.$Text.'">'.$RowResult['Result'].'</font></th>
                <th  align="center" width="23%"><font size="'.$Text.'">'.$RowResult['AnalyticMethod'].'</font></th>
				</tr>';
				}
				$content.='</table>'; 
$pdf->writeHtml($content);	

//$pdf->Ln();

$HEIGHT=0.8;
$pdf->Cell(130 ,$HEIGHT,'1.Test results are based on ELISA screening. ',0,1);
$pdf->Cell(130 ,$HEIGHT,'2.Test result should be reported as Screened positive or negative.',0,1);
$pdf->Cell(130 ,$HEIGHT,'3.Values obtained below detection limit can be reported as negative and above detection limit as positive.',0,1);
$pdf->Cell(130 ,$HEIGHT,'4.Actual values obtained above detection limit as positive shall be reported.',0,1);

//$pdf->Ln();

$pdf->SetFont($FONT_ARI,'B',$sub_header);

// original 
// $pdf->Cell(95,12,$RowIhRep['TestedBy'],1,0,'C',0);
// $pdf->Cell(95,12,$RowIhRep['VerifiedBy'],1,1,'C',0);  // cell with left and right borders

// 12-02-2024 durgaPrasad
$pdf->Cell(95,7,'',1,0,'C',0);
$pdf->Cell(95,7,'',1,1,'C',0);   // cell with left and right borders

$pdf->Cell(95,5,$RowIhRep['TestedBy'],1,0,'C',0);
$pdf->Cell(95,5,$RowIhRep['VerifiedBy'],1,1,'C',0);  // cell with left and right borders
// 12-02-2024

$pdf->Cell(95,4,$RowIhRep['TesterDesgn'],1,0,'C',0);
$pdf->Cell(95,4,$RowIhRep['VerifiedDesgn'],1,1,'C',0);  // cell with left and right borders

$pdf->SetFont($FONT_ARI,'B',7);

$pdf->Cell(95,6,'Authorized by .(Name,designation & signature)',1,0,'L',0);
$pdf->Cell(95,6,'Verified by .(Name/Designation/Signature)',1,1,'L',0);  // cell with left and right borders
//$pdf->Ln();
$pdf->Cell(20 ,1,'The test report refers only to the particular samples submitted for testing.',0,1);
// $pdf->Cell(0, 10, 'The test report refers only to the particular samples submitted for testing.', 0, 1, 'C');
 //$pdf->Cell(12,5, $pdf->Image($footer, 10,265, 190,0),0,0,'C',0);
 
           
               
if($FileType==''){
	
	ob_end_clean(); 
$FileType='I';
$FileName=$ReportName.'_'.$RowIhRep['InvNumFull'];
$FileName=getFormatedFileName($FileName);
$pdf->Output($FileName.'.pdf', 'I');
}else{
	
	ob_end_clean(); 
	$Fname=$MailTempFolder;
	$FileName=$_GET['FileName'];
	$FileName = str_replace('/','', $FileName );
	$FileName=$Fname.$FileName;
	$pdf->Output($FileName.'.pdf', $FileType);

}?>