<?php
session_start();
$ID=$_GET['id'];
include "../includes/config.php";
include "Constants.php";
include "PrintHeaderInc.php";


$INV_ID=$ID;




$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `DAY_CODE` ASC";
$stmt_code_list=$iconn->query($sql_code_list);

$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$ID'";
$stmt_varity_list=$iconn->query($sql_varity_list);

$T_DAY_CODE='';
$T_PACKING='';
$T_MCS='';
while($code_list_row=$stmt_code_list->fetch_array()){
       
	   $DAY_CODE=$code_list_row['DAY_CODE'];
	   $PACKING=$code_list_row['PACKING'];
	   $GLAZE_PERC=$code_list_row['GLAZE_PERC'];
	   $MCS=$code_list_row['T_O_MCS'];
	   
	   $T_MCS=$T_MCS+$MCS;
       
	   
	  if(in_array($DAY_CODE,$ary_code)){                               		 
	  }
	  else{
		  $ary_code[]=$DAY_CODE;
	  
	  IF($T_DAY_CODE==''){
		  $T_DAY_CODE=$DAY_CODE;
	  }
	  else{
		  $T_DAY_CODE=$T_DAY_CODE.','.$DAY_CODE;
	  }
	  }	  
	  if(in_array($PACKING,$ary_PACKING)){                               		 
	  }
	  else{
		  $ary_PACKING[]=$PACKING;
	  
	  IF($T_PACKING==''){
		  $T_PACKING=$PACKING.'('.$GLAZE_PERC.' % GLAZE)';
	  }
	  else{
		  $T_PACKING=$T_PACKING.','.$PACKING.'('.$GLAZE_PERC.' % GLAZE)';
	  }
	  
	  }
	
}

 $T_MCS= $T_MCS.' MCS';

$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();


$sql_eia="SELECT * FROM `SAMPLE_REG_FORM_DATA` WHERE INVOICE_ID='$ID'";
$stmt_eia=$iconn->query($sql_eia);
$row_eia=$stmt_eia->fetch_array();
$ref_no=$row_eia['REFERENCE_NUMBER'];
$VERIFIED_BY=explode('-',$row_eia['VERIFIED_BY']);
$VERIFIED_BY=$VERIFIED_BY[0];
$analysis=$row_eia['ANALYSIS_REQ'];
$date=date('d.m.y',strtotime($row_eia['DATE']));



//echo $row['PHOSPHATE'];

if($row['PHOSPHATE']=='TREATED'){
	$PHSPT='Water, salt ,shrimp,Sodium Tripoly phosphate';
}else{
	$PHSPT='Citric Acid,Sodium Metabisulphate Paprica & Allura Red';
}



function getHeader($ID,$iconn){

$contents = '';
	 	
$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$PO_TME_STAMP=strtotime($row['PO_DATE']);
$PO_DATE=date("d-m-Y", $PO_TME_STAMP);

	

$INV_TME_STAMP=strtotime($row['INVOICE_DATE']);
$INV_DATE=date("d-m-Y", $INV_TME_STAMP);

$contents .='<p>Sir,<br/>Sub :- Request for Health Certificate for Fishery products to be exported to <B>'.$row['DESTINATION_COUNTRY'].'</B> <BR/>
Please issue us a Health certificate for the consignment bearing Purchase Order No: <B>' .$row['PO_NUM'] .' </B>
Dt. <B>' .$PO_DATE .' </B>  
against Invoice No. <B>' .$row['INVOICE_NUMBER'] .' </B> 
 Dt. <B>' .$INV_DATE .' </B> 
The Particulars of Purchase Order are given Below are  to be exported to . <B>' .$row['TYPE_OF_COUNTRY'] .' </B> 
Destination .The Certificate fee is paid through Online System and details are attached .
The Certificate for Export relating to this consignment (Pink copy of Certificate NO: <B>' .$row['CERTIFICATE_NO'] .' </B> 
or export enclosed)</p>';
return $contents;
}



		
	

	$from_title = date('d M, Y');
	$to_title = date('d M, Y');

	
//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;



 



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
	$w1='50';
	$w2='150';
	$w3='70';

$content .= '
      	
      	<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="center"  width="'.$w1.'"><font size="10"><b> Sl.no</b></font></th>
                <th  align="center" width="'.$w2.'"><font size="10"><b>Consignment number</b></font></th>
				<th  align="center" width="'.$w2.'"><font size="10"><b>List of parameters to be tested</b></font></th>
		
				
				
				<th  align="center" width="'.$w1.'"><font size="10"><b>Testing fee</b></font></th>
				<th  align="center" width="'.$w1.'"><font size="10"><b>Service tax
(18%)
</b></font></th>
				<th  align="center" width="'.$w1.'"><font size="10"><b>Total amount</b></font></th>
				
				</tr>';
	$content .= '			<tr>  
           		<th  align="center"  width="'.$w1.'"><font size="10"> '.	$s_no.'.</font></th>
           		<th  align="center"  width="'.$w2.'"><font size="10"> '.$ref_no.'<br>'.$row['PO_NUM'].'</font></th>
           		<th> 
				<table border="0" cellspacing="0" cellpadding="3" >
				<tr>
				<th  width="'.$w2.'" style="border-bottom: 1px solid black"><b>AOZ</b></th></TR>
				</table>
				</th>
           		
				
				
				<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
           		<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
           		<th  align="center"  width="'.$w1.'"><font size="10"> </font></th>
                
				
				</tr>';
				$content .= '			<tr>  
           		<th  align="center" style="border-top: none" width="'.$w1.'"><font size="9"></font></th>
           		<th  align="center"  width="'.$w2.'"><font size="9"></font></th>
           		<th  align="center"  width="'.$w2.'"><font size="10">TOTAL</font></th>
           		<th  align="center"  width="'.$w1.'"><b><font size="10"> </b></font></th>
           		<th  align="center"  width="'.$w1.'"><b><font size="10"> </b></font></th>
           		<th  align="center"  width="'.$w1.'"><b><font size="10"> </b></font></th>
				
								</tr>
</table>'; 
$s_no++;				
	$pdf->writeHTML($content);

$pdf->SetFont($FONT_ARI,'',$header);
//$pdf->Cell(0 ,15,'',0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,''.$VERIFIED_BY,0,1,'L');
$pdf->Ln();

$pdf->Cell(0 ,5,'Q.C, TECHNOLOGIST',0,1,'L');



$pdf->Output('payroll.pdf', 'I');

?>