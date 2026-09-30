<?php
session_start();

$IsHeader=0;
$IsFooter=0;
$QueryParms=$_GET['Q']; 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";

$PoID=$_GET['PoID']; 
 $ReportName=$_GET['ReportName']; 
$TYPE=$_GET['TypeProduct']; 
$FileType=$_GET['Type'];


  $SqlPoData="SELECT *,PD.ExporterID from  ScrmPoData as PD  
left join ScrmConsigneeDetails as CD on CD.ConID=PD.ConsigneeID
left join ScrmShipmentDispatchDetails as SSD on SSD.PIID=PD.PoID
left join QcHCCoveringLetter as HCL on HCL.InvID=PD.PoID
WHERE PoID ='$PoID'";

$StmtPoData=$iconn->query($SqlPoData);
$RowPoData=$StmtPoData->fetch_array();

function getHeader($ID,$iconn){
global $TblScrmPoData,$QcNonHealthDocsList;
global $RowPoData,$PoID;


$ExpID=$RowPoData['ExporterID'];
$ConsigneeName=$RowPoData['ConsigneeName'];
//$ConsigneeCountry=$RowPoData['ConsigneeCountry'];
$ConsigneeCountry =  getCountryDetails($RowPoData['ConsigneeCountry'],$iconn)['CountryName'];
			


/********UMA*****/


$contents = '';

 $CONSIGNMENT_EXPORTED=$RowPoData['SciName'];
$BLDate=getFormatedDateNA($RowPoData['BLDate']);
$HealthApplicationDate=getFormatedDateNA($RowPoData['HealthApplicationDate']);
$BLSubmissionDate=getFormatedDateNA($RowPoData['BLSubmissionDate']);



$contents .='<p><br/>Respected Madam / Sir,<B>' .$RowPoData['QCertNo'] .' </B> <BR/><BR/>Sub :	Issue of Health Certificate – Reg.  <br>
<br>Ref :	Health Application No. <b>'.$RowPoData['HealthApplicationNo'].'</b><br><br>We have applied the Health Application bearing No.<B>'.$RowPoData['HealthApplicationNo'].'</B>, Dated: <B>'.$HealthApplicationDate.'</B>      for
the Consignment destined to <B> '.$ConsigneeCountry .'</B> Now we are applying for Final Health Certificate from your good 
office.
<br><br>
We are hereby enclosing the BL copy for your kind reference.

</p>';

$contents .='<B><p>BL Details as follows: </p></B>';
$contents .='<p>BL Number		: <b>'.$RowPoData['BLNo'].'</b></p>';
$contents .='<p>BL Date		: <b>'.$BLDate.' </b></p>';
$contents .='<p>BL copy submitted Date	: <b>'.$BLSubmissionDate.'</b></p>';
$contents .='Health Application No.	: <b>'.$RowPoData['HealthApplicationNo'].'</b></p>';

	

return $contents;
}


$BLSubmissionDate=getFormatedDateNA($RowPoData['BLSubmissionDate']);

 $pdf->SetTitle('Non Health Report '.$row['PO_NUM'].' - '.$row['PO_DATE']);

    $content = '';

  
    $content .= getHeader($ID,$iconn);


$header=11;
$sub_header=10;


$DO_SAMPLING = date("d-m-Y", strtotime($RowCoveringLetter['Date']));


$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(150 ,5,'To,',0,0,'L');		
$pdf->Cell(80 ,5,'Date: '.$BLSubmissionDate,0,1,'L');
$pdf->Cell(150 ,5,'The Deputy Director,',0,1,'L');
$pdf->Cell(150 ,5,'Export Inspection Agency - Chennai,',0,1,'L');
$pdf->Cell(150 ,5,'Sub- Office : BHIMAVARAM.',0,1,'L');




$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->Ln();
$pdf->SetFont($FONT_ARI,'',$header);


$pdf->writeHTML($content);
$pdf->Ln();

$W1=100;
$W2=90;
$H1=5;
$pdf->SetFont($FONT_ARI,'',$sub_header);


$pdf->SetFont($FONT_ARI,'',$sub_header);
$pdf->Ln();

$COUNT=1;





$count_v=count($ary_varity_list);

if($count_v>5){
	$c_pd=3;
}else{
	$c_pd=6;
}







$W2=85;
$W3=100;
$H1=5;


//$pdf->Cell(180 ,35,'',0,1,'L');


$pdf->Cell(0 ,5,'',0,1,'L');
$contents='<p><br>Thanking you sir,<br><br><br>		</p>';
$pdf->writeHTML($contents);


$pdf->SetFont($FONT_ARI,'B',$sub_header);

 

 
$pdf->Ln();

ob_end_clean();

if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowPoData['InvNumFull'];
$FileName=getFormatedFileName($FileName);
$pdf->Output($FileName.'.pdf', 'I');
}else{
	$Fname=$MailTempFolder;
	$FileName=$_GET['FileName'];
	$FileName = str_replace('/','', $FileName );
	$FileName=$Fname.$FileName.'.pdf';
	$pdf->Output($FileName, $FileType);

}
?>
