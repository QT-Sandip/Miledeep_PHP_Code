<?php
//$PoID=$_GET['PoID'];
session_start();
$IsHeader=1;
$IsFooter=1;

 $QueryParms=$_GET['Q'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  
 $PoID=$_GET['PoID'];
 include "PrintHeaderInc.php";
//include "Constants.php";


// $PROCESSOR_APP_NUM; 


 
$SqlPoData="SELECT * from $TblQcCoveringLetter as CL 
left join $TblScrmPoData as PD on PD.PoID=CL.InvID 
left join $TblScrmConsigneeDetails as CD on CD.ConID=PD.ConsigneeID
left join $TblAdminExporterDetails as AED on AED.ExpID=PD.ProcessorID
left join $TblQcLabTechnologist QLV on QLV.LbuName=CL.VerifiedBy
LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=PD.PoID
WHERE InvID ='$PoID'";  

// $SqlPoData="SELECT * from $TblQcCoveringLetter as CL 
// left join $TblScrmPoData as PD on PD.PoID=CL.InvID 
// left join $TblScrmConsigneeDetails as CD on CD.ConID=PD.ConsigneeID
// left join $TblAdminExporterDetails as AED on AED.ExpID=PD.ProcessorID
// left join $TblQcLabTechnologist QLV on QLV.LbuName=CL.VerifiedBy
// WHERE InvID ='$PoID'";

$StmtPoData=$iconn->query($SqlPoData);
$RowPoData=$StmtPoData->fetch_array();

function getHeader($ID,$iconn){
global $TblScrmPoData;
global $RowPoData;

$contents = '';

 $CONSIGNMENT_EXPORTED=$RowPoData['SciName'];
$PO_DATE=date("d-m-Y", strtotime($RowPoData['PoDate']));
$INV_DATE=date("d-m-Y", strtotime($RowPoData['InvDate']));

if($RowPoData['TypeCountry']==1){
$COUNTRY_TYPE='NON-EU';	
}else{
$COUNTRY_TYPE='EU';	
}

$contents .='<p><br><br>Dear Sir,<br/><br>     
 We are submitting the Q-Certificates(Pink Copy) for your kind Perusal against
the Invoice No - <B>' .$RowPoData['CustomsInvNo'] .', </B>
  Dt. <B>' .$INV_DATE .' </B>
So please deduct the Monitoring fee from our account maintained by with you.</p>';
return $contents;  // .$RowPoData['InvNo'] .',
}






	$from_title = date('d M, Y');
	$to_title = date('d M, Y');

 $pdf->SetTitle('Covering Note Format2: '.$RowPoData['PoNo'].' - '.date('d-m-Y',strtotime($RowPoData['PoDate'])));

    $content = '';

   // $pdf->Cell(180 ,35,'',0,1,'L');

    $content .= getHeader($ID,$iconn);




//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;




$pdf->SetFont($FONT_ARI,'',$header);
$DO_SAMPLING = date("d-m-Y", strtotime($RowPoData['Date']));

$pdf->Cell(177 ,5,' Date:'.$DO_SAMPLING,0,1,'R');
$pdf->Cell(171 ,5,'Bhimavaram.',0,1,'R');

$pdf->Cell(180 ,5,'To,',0,1,'L');
$pdf->Cell(150 ,5,'The Deputy Director,',0,1,'L');
$pdf->Cell(150 ,5,'Export Inspection Agency-Chennai,',0,1,'L');
$pdf->Cell(150 ,5,'Sub Office: Bhimavaram.',0,0,'L');

$pdf->Ln();
$pdf->SetFont($FONT_ARI,'',$header);


$pdf->writeHTML($content);
$pdf->Ln();
$pdf->Cell($W1 ,$H1,'',0,1,'L');

$W1=100;
$W2=90;
$H1=5;
$pdf->SetFont($FONT_ARI,'',$sub_header);

$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,'Q-Certificate No - '.$RowPoData['QCertNo'],0,1,'L'); 
$pdf->Cell(0 ,5,'',0,1,'L');

$pdf->Cell(0 ,5,'E.U. APPROVAL NUMBER - '.$RowPoData['ExpApprovalNo'],0,1,'L'); 
$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'B',$sub_header);

//$pdf->Cell(0 ,5,'For '.$EXPORTOR,0,1,'L'); //26/03/2024--ROBO
$pdf->Cell(0 ,5,'For '.$PROCESSOR,0,1,'L'); 
$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$sub_header);

$pdf->Cell(0 ,5,'Autorised Signatory',0,1,'L'); 
$pdf->Cell(0 ,10,'',0,1,'L');

$pdf->Cell(0 ,5,$RowPoData['VerifiedBy'],0,1,'L'); 
$pdf->Cell(0 ,5,$RowPoData['LbuDesignation'],0,1,'L'); 
 

 
$pdf->Ln();


ob_end_clean();
$pdf->Output('CoverNote(Format2)-'.$RowPoData['PoNo'].'.pdf', 'I');

?>
