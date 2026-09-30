<?php
session_start();
$RID=$_GET['RID'];
$PoID=$_GET['PoID'];
include "../includes/config.php";
include "../includes/Globals.php";
include "Constants.php";
include "PrintHeaderInc.php";



$SqlTMcs="select sum(McsTotal) as McsTotal from $TblQcCodeList where InvID=$PoID";
$StmtTMcs=$iconn->query($SqlTMcs);
$RowMcs=$StmtTMcs->fetch_array();
$McsTotal=$RowMcs['McsTotal'];
$McsTotal=ceil($McsTotal/500);


$SqlSampleLtr="SELECT * FROM $TblQcSamplingLettersExt as SL
left join $TblScrmPoData as PD on PD.POID=SL.InvID
 WHERE SL.SMPLID='$RID'";
$StmtSampleLtr=$iconn->query($SqlSampleLtr);
$RowSampleLtr=$StmtSampleLtr->fetch_array();

$SampleNumber=$RowSampleLtr['SampleNumber'];
$AnalysisReq=$RowSampleLtr['AnalysisReq'];
$VerifiedBy=$RowSampleLtr['VerifiedBy'];
$PoNO=$RowSampleLtr['PoNo'];
$MailDate=$RowSampleLtr['Date'];
$PoDate=getFormatedDate($RowSampleLtr['PoDate']);


$header=10;
$sub_header=10;



 



$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(0 ,15,'DATE: '.$MailDate,0,1,'R');

$pdf->Cell(0 ,5,'TO',0,1,'L');
$pdf->Ln();

$pdf->Cell(150 ,5,'THE DEPUTY DIRECTOR',0,1,'L');


$pdf->Cell(150 ,5,'EXPORT INSPECTION AGENCY- CHENNAI',0,1,'L');


$pdf->Cell(49 ,5,'BHIMAVARAM- SUB OFFICE',0,1,'L');
$pdf->Ln();


$pdf->Cell(49 ,5,'Sub: EU Sampling Request – reg',0,1,'L');
$pdf->Ln();

$pdf->Cell(49 ,5,'Sir / Madam,',0,1,'L');




$pdf->Cell(0 ,5,'',0,1,'R');
$contents='<p>			As mentioned at above subjects please find the enclosed  sampling request. 
We have consignment ready to export for EU country. The details are as below
</p>';

  

$pdf->writeHTML($contents);
$pdf->Cell(0 ,5,'',0,1,'L');
    $content = ''; 
	$s_no='1' ;
$w1='200';



$content .= '
      	
      	<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="center"  width="70"><font size="10"><b> Sl.no</b></font></th>
                <th  align="center" width="'.$w1.'"><font size="10"><b>Consignment number</b></font></th>
				<th  align="center" width="'.$w1.'"><font size="10"><b>List of parameters to be tested</b></font></th>
				
				</tr>';
	$content .= '			<tr>  
           		<th  align="center"  width="70"><font size="10"> '.	$s_no.'.</font></th>
           		<th  align="center"  width="'.$w1.'"><font size="8"> '.$SampleNumber.'<br>'.$PoNO.'</font></th>
           		<th  align="center"  width="'.$w1.'"><font size="8">'.$AnalysisReq.'</font></th>
           		
								</tr>
</table>'; 
$s_no++;				
	$pdf->writeHTML($content);




$pdf->Ln();


$contents='<p>        Our  Approval technologist will draw the sample as directed by competent authority vide mail 
dated '.$Date.'</p>';

 

$pdf->writeHTML($contents);

$pdf->Ln();


$pdf->Cell(0 ,5,'',0,1,'L');
$pdf->Cell(0 ,15,'',0,1,'L');

$pdf->Cell(0 ,5,'Thanking you',0,1,'L');

/*$pdf->Cell(0 ,5,'For ' .$PROCESSOR,0,1,'L');
$pdf->Ln();
*/

$pdf->SetFont($FONT_ARI,'',$header);
//$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,''.$VerifiedBy,0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,'Q.C, TECHNOLOGIST',0,1,'L');






$pdf->Ln();
 //$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);


$pdf->AddPage(); 

$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Ln();


$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Cell(0 ,15,'DATE: '.$date,0,1,'R');

$pdf->Cell(0 ,5,'TO',0,1,'L');

$pdf->Cell(150 ,5,'THE DEPUTY DIRECTOR',0,1,'L');

$pdf->Cell(150 ,5,'EXPORT INSPECTION AGENCY- CHENNAI',0,1,'L');

$pdf->Cell(49 ,5,'BHIMAVARAM- SUB OFFICE',0,1,'L');
$pdf->Ln();
//$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Cell(49 ,5,'Sub: Submission of sample testing charges fee – Reg',0,1,'L');
//$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Ln();
$pdf->Cell(49 ,5,'Attached online payment receipt',0,1,'L');
$pdf->Ln();


 $content = ''; 
	$s_no='1' ;
	$w1='5%';
	$w2='25%';
	$w3='15%';

$content .= '
      	
      	<table border="1" cellspacing="0" cellpadding="0">  
				<tr>  
           		<th  align="center"  width="'.$w1.'"><font size="10"><b> Sl.no</b></font></th>
                <th  align="center" width="'.$w2.'"><font size="10"><b>Consignment number</b></font></th>
				<th  align="center" width="'.$w2.'"><font size="10"><b>List of parameters to be tested</b></font></th>
		
				
				
				<th  align="center" width="'.$w3.'"><font size="10"><b>Testing fee</b></font></th>
				<th  align="center" width="'.$w3.'"><font size="10"><b>Service tax(18%)</b></font></th>
				<th  align="center" width="'.$w3.'"><font size="10"><b>Total amount</b></font></th>
				
				</tr>
				';
	

           		
				for($I=0 ; $I< $McsTotal; $I++){
				
				$content.='
				<tr>  
           		<td  align="center"  width="'.$w1.'"><font size="8"><b> '.	$s_no++.'.</b></font></td>
                <td  align="center" width="'.$w2.'"><font size="8"><b>'.$SampleNumber.'<br>'.$PoNO.'</b></font></td>
				<td  align="center" width="'.$w2.'"><font size="8"><b>AOZ</b></font></td>
		
				
				
				<td  align="center" width="'.$w3.'"><font size="8"><b>1200</b></font></td>
				<td  align="center" width="'.$w3.'"><font size="8"><b>216</b></font></td>
				<td  align="center" width="'.$w3.'"><font size="8"><b>1416</b></font></td>
				
				</tr>
				';
				}
           		
				

				$content .= '			<tr>  
           		<th  align="center" style="border-top: none" width="'.$w1.'"><font size="9"></font></th>
           		<th  align="center"  width="'.$w2.'"><font size="9"></font></th>
           		<th  align="center"  width="'.$w2.'"><font size="10">TOTAL</font></th>
           		<th  align="center"  width="'.$w3.'"><b><font size="10">'.($McsTotal*1200 ).' </b></font></th>
           		<th  align="center"  width="'.$w3.'"><b><font size="10">'.($McsTotal*216 ).' </b></font></th>
           		<th  align="center"  width="'.$w3.'"><b><font size="10">'.($McsTotal*1416 ).' </b></font></th>
				
								</tr>
</table>'; 
$s_no++;				
	$pdf->writeHTML($content);

$pdf->SetFont($FONT_ARI,'',$header);
//$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,$VerifiedBy,0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,'Q.C, TECHNOLOGIST',0,1,'L');

ob_clean();

$pdf->Output('payroll.pdf', 'I');

?>