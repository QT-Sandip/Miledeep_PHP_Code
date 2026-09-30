<?php
session_start();
$IsHeader=0;
$IsFooter=0;
$QueryParms=$_GET['Q'];  
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include "PrintHeaderInc.php";


  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET); 
  }    

 $PoID=$_GET['PoID']; 
 $ReportName=$_GET['ReportName']; 
 $Brand=implode(",",array_unique($AryBrand)); 

 $T_MCS= $T_MCS.' MCS';

  $SqlPoData="SELECT *,PD.ExporterID from  $TblScrmPoData as PD  
left join $TblScrmConsigneeDetails as CD on CD.ConID=PD.ConsigneeID
left join $ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID
WHERE PoID ='$PoID'";

$StmtPoData=$iconn->query($SqlPoData);
$RowPoData=$StmtPoData->fetch_array();

 $SqlCoveringLetter="SELECT * from $TblQcCoveringLetter WHERE InvID ='$PoID'"; 

$StmtCoveringLetter=$iconn->query($SqlCoveringLetter);
$RowCoveringLetter=$StmtCoveringLetter->fetch_array();

$SqlVarifiedBy = "SELECT VerifiedBy, LbuDesignation FROM `QcCoveringLetter` as CL 
LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy
 WHERE InvID='$PoID' ";  
 $StmtVarifiedBy = $iconn->query($SqlVarifiedBy);
 $RowVarifiedBy = $StmtVarifiedBy->fetch_array();
 $VerifiedBy = $RowVarifiedBy['VerifiedBy'];
 $Designation = $RowVarifiedBy['LbuDesignation'];

function getHeader($ID,$iconn){
global $RowCoveringLetter,$QcNonHealthDocsList;
global $RowPoData,$PoID;
 
 $ExpID=$RowPoData['ExporterID']; 
 $ExporterData=getExporterDetails($ExpID);
 $InvShortCode=$ExporterData['InvShortCode']; 
 
  $InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);



$SqlDocsList="SELECT * FROM `$QcNonHealthDocsList` WHERE Status=1";
$StmtDocsList=$iconn->query($SqlDocsList);


$contents = '';

$CONSIGNMENT_EXPORTED=$RowPoData['SciName'];	
$PO_DATE=getFormatedDateNA($RowPoData['PoDate']);
$INV_DATE=getFormatedDateNA($RowPoData['InvDate']);

if($RowPoData['TypeCountry']==1){
$COUNTRY_TYPE='NON-EU';	
}else{
$COUNTRY_TYPE='EU';	
}
$SNo=1;

//echo $RowCoveringLette['QCertNo'] ; exit;

$contents .='<p><br/>Sub :- Submission of Q-Certificate no. <B>' . $RowCoveringLetter['QCertNo'] .' </B>reg <BR/><BR/>
Ref: Our Export Invoice no <B>' .$InvNO.', </B>Dt. <B>' .$INV_DATE .' </B><BR>
with reference to the above subject, we have loaded one container to U.S.A from Visakhapatnam port.Herewith submittig the following<br>
Documents for your kind perusal</p>';
while($RowDocsList=$StmtDocsList->fetch_array()){
	$contents .='<p>'.$SNo.'.'.$RowDocsList['DocName'].'</p>';
$SNo++ ;}

return $contents;
}


 $pdf->SetTitle('Non Health Report '.$row['PO_NUM'].' - '.$PO_DATE);

    $content = '';



    $content .= getHeader($ID,$iconn);




//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;


//$pdf->Cell(180 ,40,'',0,1,'L');

$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(180 ,5,'To,',0,1,'L');
$pdf->Cell(150 ,5,'THE DEPUTY DIRECTOR,',0,1,'L');
$pdf->Cell(150 ,5,'Export Inspection Agency-Chennai',0,1,'L');
$pdf->Cell(150 ,5,'SUB OFFICE:BHIMAVARAM',0,1,'L');
$pdf->Cell(150 ,5,'Dear Sir,',0,0,'L');

$DO_SAMPLING = getFormatedDate($RowCoveringLetter['Date']);


$pdf->Cell(49 ,5,'Date:'.$DO_SAMPLING,0,1,'L');
//$pdf->Cell(49 ,5,'Date:',0,1,'L');


$pdf->SetFont($FONT_ARI,'BU',$header);
//$pdf->Cell(0 ,5,'BHIMAVARAM',0,1,'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI,'',$header);


$pdf->writeHTML($content);
$pdf->Ln();
//$pdf->Cell($W1 ,$H1,'',0,1,'L');

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
$contents='<p><br>Thanking you sir,<br><br><br>
 <b>For '.$EXPORTOR.'</b><br></p>';
$pdf->writeHTML($contents);


$pdf->SetFont($FONT_ARI,'B',$sub_header);

//$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Cell(0 ,5,$VerifiedBy,0,1,'L'); 
$pdf->Cell(0, 5, $Designation, 0, 1, 'L');

 
$pdf->Ln();

ob_end_clean();

if($FileType==''){
$FileType='I';
$FileName=$ReportName.'_'.$RowPoData['PoNo'];
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
