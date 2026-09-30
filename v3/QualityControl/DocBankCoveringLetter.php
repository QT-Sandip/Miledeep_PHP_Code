<?php
$PoID=$_GET['PoID'];
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
include "Constants.php";
include "../SalesInculdes/SalesConstants.php"; 

$IsHeader=0;
$IsFooter=0;
include "PrintHeaderInc.php";

/*$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);*/

 
 $Brand=implode(",",array_unique($AryBrand)); 

 $T_MCS= $T_MCS.' MCS';

  $SqlPoData="SELECT *,PD.ExporterID from  ScrmPoData as PD  
left join ScrmConsigneeDetails as CD on CD.ConID=PD.ConsigneeID
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID
WHERE PoID ='$PoID'";

$StmtPoData=$iconn->query($SqlPoData);
$RowPoData=$StmtPoData->fetch_array();

$SqlCoveringLetter="SELECT * from $TblQcCoveringLetter WHERE InvID ='$PoID'";

$StmtCoveringLetter=$iconn->query($SqlCoveringLetter);
$RowCoveringLetter=$StmtCoveringLetter->fetch_array();

function getHeader($ID,$iconn){
global $TblScrmPoData,$QcNonHealthDocsList;
global $RowPoData,$PoID;


/********UMA*****/
/*************/
/*************/
 
			$ExpID=$RowPoData['ExporterID'];
			$CInvNo=$RowPoData['CInvNo'];
			$CInvDate=$RowPoData['CInvDate'];
			$ConsigneeName=$RowPoData['ConsigneeName'];
			$ConsigneeCountry=$RowPoData['ConsigneeCountry'];
			$PoNo=$RowPoData['PoNo'];
			$PoDate=$RowPoData['PoDate'];
			
			
			
			$ExporterData=getExporterDetails($ExpID);
			$InvShortCode=$ExporterData['InvShortCode']; 



/*
	$CurrentMonth = strftime("%m");  
if($RowPoData['ExporterID']==5){		
if($CurrentMonth<=3){
	
 $NxtYear = strftime("%y");  	
 $CurrentYear = strftime("%y")-1;  	
	
} 
else{ 
 $CurrentYear = strftime("%Y");  	
 $NxtYear = strftime("%y")+1;  	
}
		}
		else{		
if($CurrentMonth<=3){
	
 $NxtYear = strftime("%y");  	
 $CurrentYear = strftime("%Y")-1;  	
	
} 
else{ 
 $CurrentYear = strftime("%Y");  	
 $NxtYear = strftime("%y")+1;  	
}
		}*/
	
	include "Constants.php";
	//if($ExpID==5){
	if($RowPoData['ExporterID']==5)		
{
							$InvNO=$InvShortCode.$RowPoData['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;
			}
			else{
							$InvNO=$InvShortCode.$RowPoData['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

			}

$SqlDocsList="SELECT * FROM `$QcNonHealthDocsList` WHERE Status=1";
$StmtDocsList=$iconn->query($SqlDocsList);
/********UMA*****/


$contents = '';

 $CONSIGNMENT_EXPORTED=$RowPoData['SciName'];
$PO_DATE=date("d-m-Y", strtotime($RowPoData['PoDate']));
$INV_DATE=date("d.m.Y", strtotime($RowPoData['CInvDate']));

if($RowPoData['TypeCountry']==1){
$COUNTRY_TYPE='NON-EU';	
}else{
$COUNTRY_TYPE='EU';	
}
$SNo=1;

$contents .='<p><br/>Respected Madam / Sir,<B>' .$RowPoData['QCertNo'] .' </B> <BR/><BR/>
          
		  We are herewith submitting the following documents of 1500'.$TOTAL_MCS.' Master CASES <br>
Inv.no : <B>' .$InvNO.', </B>Dt. <B>' .$INV_DATE .' </B>
 
 of the Fresh Frozen Indian Shrimps for Negotiation<br>
against PO NO : AZ13792 , DT.: 25.02.2022, issued of AZ GEMS INC., USA
</p>';

$contents .='<B><p>List of documents presented: </p></B>';
$contents .='<p>Bill of exchange (Sight Draft)		: 		2 ORIGINALS </p>';
$contents .='<p>Commercial Invoice : 2 ORIGINALS </p>';
$contents .='<p>Packing List : 2 ORIGINALS </p>';
$contents .='Certificate of Origin : 1 ORIGINAL</p>';
$contents .='<p>Bill of Lading : 2/3 ORIGINALS</p>';
$contents .='<p>Survey Report : 1 ORIGINAL</p>';
$contents .='<p>Dsp : 1 ORIGINAL</p>';

	$contents .='		<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="CENTER"  ><font size="10" ><b> SB NUMBER_DATE.</b>
				</font></th>
				<th  align="CENTER" ><font size="10"><b> SB VALUE IN US$</b>
				</font></th>
				<th  align="CENTER" ><font size="10"><b>SB VALUE FOB IN USD</b>
				</font></th>
				<th  align="CENTER" ><font size="10"><b>PORT OF LOADING</b>
				</font></th>
				
				<th  align="CENTER" ><font size="10"><b>AD CODE</b>
				</font></th>
				</tr>
				
				<tr> 
				
				<th  align="CENTER"  ><font size="'.$FontSize.'" >'.$ContainerNumber.'
				</font></th>
				<th  align="CENTER" ><font size="'.$FontSize.'">40 FCL</font></th>
				<th  align="CENTER" ><font size="'.$FontSize.'">'.$SealNo.'</font></th>
				<th  align="CENTER" ><font size="'.$FontSize.'">'.$SealNo.'</font></th>
				
				<th  align="CENTER" ><font size="'.$FontSize.'">'.$TOTAL_MCS.'  CARTONS OF FROZEN SHRIMPS</font></th>
				
				</tr>';
			
			
				
			$contents .= '	
				
				
				</table>';
	
$contents .='<p>HS CODE: 16052900<BR/>
			SHIPPING LINER : <B> MSC </B><BR/>
			Further we solemnly affirm that:<BR/>
			1. We have not availed of any Advance / Packing Credit facility from any bank against the order / Letter of Credit.
			<BR/>
			2. We request you to negotiate the documents. Please transfer to the credit of our
			<B> EPC/PCFC </B>  A/c with your branch.<BR/>
			3. All bank charges with respect of the negotiation may please be debited directly to our Current 
			Account Number : <B> 36718635634 </B>		<BR/>
			4. Please hold the original documents until further our confirmation<BR/>
			5. Please Discount the bill with due date 60 days sight<BR/>
		</p>';
	

return $contents;
}






	$from_title = date('d M, Y');
	$to_title = date('d M, Y');






	//require_once('Constants.php');





 $pdf->SetTitle('Non Health Report '.$row['PO_NUM'].' - '.$row['PO_DATE']);

    $content = '';

   // $pdf->Cell(180 ,35,'',0,1,'L');

    $content .= getHeader($ID,$iconn);




//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;


$DO_SAMPLING = date("d-m-Y", strtotime($RowCoveringLetter['Date']));


$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(150 ,5,'To,',0,0,'L');		
$pdf->Cell(80 ,5,'Date:',0,1,'L');
$pdf->Cell(150 ,5,'The Asst. general manager,',0,1,'L');
$pdf->Cell(150 ,5,'State Bank of Manager',0,1,'L');
$pdf->Cell(150 ,5,'SME BRANCH',0,1,'L');
$pdf->Cell(150 ,5,'BHIMAVARAM',0,1,'L');




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
$contents='<p><br>Thanking you sir,<br><br><br>		</p>';
$pdf->writeHTML($contents);


$pdf->SetFont($FONT_ARI,'B',$sub_header);

 

 
$pdf->Ln();

ob_end_clean();

$pdf->Output('nonhealth.pdf', 'I');

?>
