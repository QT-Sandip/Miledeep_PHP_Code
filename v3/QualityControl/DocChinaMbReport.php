<?php
session_start();
error_reporting(E_ALL);
include "../includes/config.php";
include "../includes/Globals.php";
include "Constants.php";
include "PrintHeaderInc.php";

$ID=$_GET['id'];


$sql="SELECT * FROM `BUYER_MB_REPORT`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$BUYER_MB_REPORT_ROW=$stmt_REP->fetch_array();

$sql="SELECT * FROM `TEST_RESULT_PARMS` where TEST_NAME='Buyer Mb' AND COUNTRY='$C_NAME'";
$stmt_HEADER=$iconn->query($sql);
$post_details=$stmt_HEADER->fetch_array();

$sql="SELECT * FROM `BUYER_MB_RESULT` WHERE `Mb_REPORT_ID`='$ID'";
$stmt=$iconn->query($sql);



$C_NAME=$BUYER_MB_REPORT_ROW['COUNTRY'];
$FILE_NAME=$BUYER_MB_REPORT_ROW['COUNTRY'];
$INVOICE_ID=$BUYER_MB_REPORT_ROW['INVOICE_ID'];
$TYPE=$BUYER_MB_REPORT_ROW['VARITY'];

$DATA=getPoDataByType($iconn,$INVOICE_ID,$TYPE);
$DATA=json_decode($DATA,true);

//print_r($DATA);



$PO_VARIETY_LIST_SQL="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$INVOICE_ID' and TYPE='$TYPE'";
$STMT_PO_VARIETY_LIST=$iconn->query($PO_VARIETY_LIST_SQL);

$SQL_CODE_LIST="SELECT * FROM `CODE_LIST_CREATE` where INV_ID='$INVOICE_ID' and SHORT_NAME='$TYPE' ORDER BY `DAY_CODE` ASC";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);



$SQL_INVOICE_DATA="SELECT * FROM `INVOICE_DATA` where ID='$INVOICE_ID'";
$STMT_INVOICE_DATA=$iconn->query($SQL_INVOICE_DATA);

$INVOICE_DATA=$STMT_INVOICE_DATA->fetch_array();

 $PO_NUM=$INVOICE_DATA['PO_NUM'];
 $PO_DATA=$INVOICE_DATA['PO_DATE'];

$TYPE_DEST=$INVOICE_DATA['TYPE_OF_COUNTRY'];

 $sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$INVOICE_ID' ORDER BY `DAY_CODE` ASC";  
$stmt_code_list=$iconn->query($sql_code_list);

$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$INVOICE_ID'";
$stmt_varity_list=$iconn->query($sql_varity_list);


 $T_MCS= $T_MCS.' MCS';

$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$sql_mc="SELECT T_O_MCS FROM `CODE_LIST_CREATE` WHERE INV_ID='$INVOICE_ID'  ORDER BY `DAY_CODE` ASC";
	 $stmt_mc=$iconn->query($sql_mc);
	 while($mcs_row=$stmt_mc->fetch_array()){
	 $T_O_MCS=$T_O_MCS+$mcs_row['T_O_MCS'];
	 }

function generateTableHead($C_NAME,$iconn){

$ary_fields=array();

  $sql="SELECT `PROD_REG`, `DOA_DOC`,  `PARAMETER`, `VARITY`,`DETCTION_LIMIT`, `TEST_RESULT`, `ANALYTIC_METHOD`, `TPC`, `ECOLI`, `STAP`, `V_CHLORAE`, `V_PARAHA`, `SALMONELLA`, 
`LISTERIA`, `ANALYTICAL_REPORT`  FROM `TEST_RESULT_PARMS` where TEST_NAME='Buyer Mb' AND COUNTRY='$C_NAME'";
$stmt_HEADER=$iconn->query($sql);
$post_details=$stmt_HEADER->fetch_array();


 $countt=0;	
	for($y=0; $y<count($post_details); $y++){
		 $value=$post_details[$y];
		if($value!='NA' & $value!=''){
			$countt++;
	}
	}
	
	//echo count($post_details);
for($x=0; $x<count($post_details); $x++){
	 $value=$post_details[$x];
	 
	 if($x==0){
	 $size=12;
	 }else{
	 $size=100/$countt;
	 }
	 


	if($value!='NA' & $value!=''){
					 $text = iconv("ISO-8859-1","UTF-8//TRANSLIT",$value);
					 $content .= '<th align="center" width="'.$size.'%" ><font size="7">'.$text.'</font></th>';
		 }
	
		 
		
}


return $content;

}


function generateTableRow($ID,$iconn,$TYPE_DEST,$INVOICE_ID){
	
	//include "Constants.php";

	 
	
   $sql="SELECT `P_CODES_REG_NUM`, `DOA_DOC`, `VARITY`, `TPC`, `ECOLI`, `STAPHYLO_COCCUS`, `V_CHOLERAE`, 
 `V_PRAHAE`, `SALMONELLA`, `LISTERIA`, `A_REPORT` FROM `BUYER_MB_RESULT` WHERE `Mb_REPORT_ID`='$ID'  ORDER by ID ASC";
 $stmt=$iconn->query($sql);
 
  $TOTAL_SAMPLES =(sqrt($T_O_MCS)+1)/2;
	 $TOTAL_SAMPLES =round($TOTAL_SAMPLES);
 
 $cou=0;

 while($post_details=$stmt->fetch_array()){
	$ary_fields=array();
	 $content .='<tr >';
	 
	 
	 
	 $parts =explode("&",$post_details['P_CODES_REG_NUM']);
	// print_r($parts);
	 $fri =$parts[0];
	 $dc =$parts[1];
	
	
	  $sql_mc="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$INVOICE_ID' AND CODE_LIST_ID='$dc' AND DAY_CODE='$fri' ORDER BY `DAY_CODE` ASC";
	 $stmt_mc=$iconn->query($sql_mc);
	 $post_detailss=$stmt_mc->fetch_array();
	 $I_MCS= $post_detailss['T_O_MCS'];
	 $TRACEBILITY= $post_detailss['FARM_REG_ID'];
	 
	 
	 if($post_details['DOA_DOC']!='NA'){
			$DOA_DOC= explode("/", $post_details['DOA_DOC']);
			 
			 $DOA=$DOA_DOC[0];
			 $DOC=$DOA_DOC[1];
			 
			 $DOA = strtotime($DOA);
			 $po_DOA = date("d-m-Y", $DOA);
			 
			 $DOC = strtotime($DOC);
			 $po_DOC = date("d-m-Y", $DOC);
			 
			 $text = $po_DOA .'<br>'.$po_DOC;
			 
			  $content .= '<td   align="center"><font size="8">'.$po_DOA.'</font></td>';
			  $content .= '<td   align="center"><font size="8">'.$po_DOC.'</font></td>';
		 
		 }
	 
	 
		if($post_details['P_CODES_REG_NUM']!='NA'){
			 $text= $fri;
			 $content .= '<td align="center"><font size="7">'.$text.'<br><br></font></td>';		 
		 }
		
		 
		
		
		  $samples= getSamples($iconn,$INVOICE_ID,$I_MCS);
		  $ary_fields[]=$samples;
		  //$ary_fields[]='9';
		  
		  
	 
	
		  
		 if($post_details['VARITY']!='NA'){
			 $ary_fields[]=$post_details['VARITY'];
		 }
		 
		 if($post_details['TPC']!='NA'){
			 $post_details['TPC'];
			 $ary_fields[]=$post_details['TPC'];
		 }
		
		
		 
		  if($post_details['ECOLI']!='NA'){
			 if($TYPE_DEST=='EU'){
				 $ary_fields[]='less than 3 MPN/gm';
			 }else{
				$ary_fields[]=$post_details['ECOLI'];
			}			
		 }
		 
		  if($post_details['STAPHYLO_COCCUS']!='NA'){			 
			 if($TYPE_DEST=='EU'){			 
				 $ary_fields[]='less than 3 MPN/gm';
			}
			ELSE{
				$ary_fields[]=$post_details['STAPHYLO_COCCUS'];				
			}			 
		 }
		 
		
		 if($post_details['V_CHOLERAE']!='NA'){
			 $ary_fields[]=$post_details['V_CHOLERAE'];
		 }
		 
		 if($post_details['V_PRAHAE']!='NA'){
			 $ary_fields[]=$post_details['V_PRAHAE'];
		 }
		 
		  if($post_details['SALMONELLA']!='NA'){
			 $ary_fields[]=$post_details['SALMONELLA'];
		 }
		 
		  if($post_details['LISTERIA']!='NA'){
			 $ary_fields[]=$post_details['LISTERIA'];
		 }

		 if($post_details['A_REPORT']!='NA'){
			 $ary_fields[]=$post_details['A_REPORT'];
		 }
		
//print_r($ary_fields);
 foreach($ary_fields as $value){
	 
	
	  if($value==''){
		  $value='Absent';
	  }else{
	  
		$text = iconv("ISO-8859-1","UTF-8//TRANSLIT",$value);
	      $content .= '<td   align="center"><font size="7">'.$text.'</font></td>';
	  }

 
  	
}

   $content .='</tr>';
 

$cou++;
 }
		 

return $content;
}


	$from_title = date('d M, Y');
	$to_title = date('d M, Y');

	require_once('../tcpdf/tcpdf.php'); 
	

$HEIGHT=8;
$header=10;
$sub_header=8;
$text=6;

$pdf->SetFont($FONT_ARI,'BU',$header);
$pdf->SetFont('','B',12);
$pdf->Cell(0 ,5,'IN HOUSE BACTERIOLOGICAL ANALYSIS  REPORT',0,1,'C');
$pdf->Cell(70 ,5,'PO NO :'.$PO_NUM ,0,0,'C');
//$pdf->Cell(40 ,5,''  ,0,0,'C');
$pdf->Cell(50 ,5,'DT:'.$PO_DATA,0,1,'C');
//$pdf->Cell(180 ,$HEIGHT,'VARIETY',0,1,'L');
$pdf->SetFont($FONT_ARI,'B',$header);

while($V_ROW=$STMT_PO_VARIETY_LIST->fetch_array()){
	//$pdf->Cell(180 ,5,$V_ROW['FULL_NAME'],0,1,'L');
}

$pdf->SetFont($FONT_ARI,'BU',$header);
//$pdf->Cell(180 ,$HEIGHT,'SAMPLE PARTICULARS :',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$sub_header);



$OV_COUNT=1;


$W1=10;
$W2=60;
$W3=120;
$H1=5;

$w1='25%';
$w2='12';
$pdf->Ln();


$FARMER_NAME = '<table border="1" cellspacing="0" cellpadding="3">

			<tr>  
           		<th width="'.$w1.'" align="left"><FONT SIZE="'.$w2.'"><B>CODE</B></FONT></th>
                <th width="'.$w1.'" align="left"><FONT SIZE="'.$w2.'"><B>VARIETY/TYPE OF PACKING</B></FONT></th>				
                <th width="'.$w1.'" align="left"><FONT SIZE="'.$w2.'"><B>NO OF CASES</B></FONT></th>				
                <th width="'.$w1.'" align="left"><FONT SIZE="'.$w2.'"><B>REMARKS</B></FONT></th>				
           </tr>
		  <tr>  
           		<th width="'.$w1.'" align="left"><FONT SIZE="'.$w2.'"><B>'.$DATA['PRODUCTION_CODES'].'</B></FONT></th>
                <th width="'.$w1.'" align="left"><FONT SIZE="'.$w2.'"><B>VARIETY/TYPE OF PACKING</B></FONT></th>				
                <th width="'.$w1.'" align="left"><FONT SIZE="'.$w2.'"><B>NO OF CASES</B></FONT></th>				
                <th width="'.$w1.'" align="left"><FONT SIZE="'.$w2.'"><B>REMARKS</B></FONT></th>				
           </tr>
		  
</table> ';


$pdf->writeHTML($FARMER_NAME);





$pdf->Cell(180 ,$HEIGHT,'TEST RESULT :',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$sub_header);

$pdf->SetFont($FONT_ARI,'B',$sub_header);

$size=10;

		 $content = '';  
		 $content .= '<table border="1" cellspacing="0"  cellpadding="2"><tr>';  	  
		 //echo  generateTableHead($C_NAME,$iconn);
		 $content .= generateTableHead($C_NAME,$iconn);
		 $content .= '</tr>';
		 
		// echo $BUYER_MB_REPORT_ROW['COUNTRY'];
		
		if($BUYER_MB_REPORT_ROW['COUNTRY']!='CHINA'	&& $BUYER_MB_REPORT_ROW['COUNTRY']!='NEWZEALAND'	 ){	 
		 $content .= generateTableRow($ID,$iconn,$TYPE_DEST,$INVOICE_ID);
		}		
		 $content .= '</table>'; 
		 $pdf->SetFont($FONT_ARI,'',$header);
		$pdf->SetMargins(15, 29, 16,true);
		$pdf->writeHTML($content, false); 


/*------------------------------------------------CHINA------------------------------------------------------*/

$W1=10;								
$W2=21.6;									
$W3=16;									
$W4=16.4;									
$W5=16.40;	
$W6=16.1;	
$W7=16.5;	
$W8=16.2;	
$W9=16.2;	
$W10=16.2;
$W11=16.2;

if($BUYER_MB_REPORT_ROW['COUNTRY']=='CHINA' || $BUYER_MB_REPORT_ROW['COUNTRY']=='NEWZEALAND'){

$INTERNAL_COUNT=$post_details['COUNT'];
$H=4;
$COUNT=1;

$pdf->SetFont($FONT_ARI,'',$sub_header);
	$sql_mc="SELECT T_O_MCS FROM `CODE_LIST_CREATE` WHERE INV_ID='$INVOICE_ID'  ORDER BY `DAY_CODE` ASC";
	 $stmt_mc=$iconn->query($sql_mc);
	 while($mcs_row=$stmt_mc->fetch_array()){
	 $T_O_MCS=$T_O_MCS+$mcs_row['T_O_MCS'];
	 }
		 

while($roww=$stmt->fetch_array()){
	
	$prod=explode('&',$roww['P_CODES_REG_NUM']	);
	$DOA_DOC=explode('/',$roww['DOA_DOC']	);
	
	$parts =explode("&",$roww['P_CODES_REG_NUM']);
	 
	 $fri =$parts[0];
	 $dc =$parts[1];
	 $sql_mc="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$INVOICE_ID' AND CODE_LIST_ID='$dc' AND DAY_CODE='$fri'  ORDER BY `DAY_CODE` ASC";
	 $stmt_mc=$iconn->query($sql_mc);
	 $post_detailss=$stmt_mc->fetch_array();
	 $I_MCS= $post_detailss['T_O_MCS'];
	 $TRACEBILITY= $post_detailss['FARM_REG_ID'];
	 
	 $TOTAL_SAMPLES =(sqrt($T_O_MCS)+1)/2;
	 $TOTAL_SAMPLES =round($TOTAL_SAMPLES);
	 
	 $each_sample=getSamples($iconn,$INVOICE_ID,$I_MCS);
		//  $ary_fields[]=$samples;
	
	
	//$DOA_DOC= explode("/", $post_details['DOA_DOC']);

			 
			 $DOA=$DOA_DOC[0];
			 $DOC=$DOA_DOC[1];
			 
			 $DOA = strtotime($DOA);
			 $po_DOA = date("d-m-Y", $DOA);
			 
			 $DOC = strtotime($DOC);
			 $po_DOC = date("d-m-Y", $DOC);
			 
			 
//$y = $pdf->getY();
$pdf->Cell($W2,$H,$prod[0],'LRT',0,'C',0);
$pdf->Cell($W3,$H,$po_DOA,'LRT',0,'C',0); 
$pdf->Cell($W4,$H, $each_sample,'LRT',0,'C',0); 
$pdf->writeHTMLCell($W4,$H, '', '', $roww['VARITY'], 0, 0, 0, false, 'C', false);
//$pdf->writeHTMLCell($W4,$H, '', '', $each_sample, 0, 0, 0, false, 'C', false);

//$pdf->writeHTMLCell($W3,$H, '', '', $right_column, 1, 1, 1, true, 'J', true);

//$pdf->Cell($W4,$H,substr($roww['VARITY'],0,5),'LRT',0,'C',0);  
//$pdf->Cell($W4,$H,substr($roww['VARITY'],0,5),'LRT',0,'C',0);  
$pdf->Cell($W5,$H,$roww['TPC'],'LRT',0,'C',0);						
$pdf->Cell($W6,$H,$roww['ECOLI'],'LRT',0,'C',0);						
$pdf->Cell($W7,$H,$roww['STAPHYLO_COCCUS'],'LRT',0,'C',0);						
$pdf->Cell($W8,$H,$roww['V_CHOLERAE'],'LRT',0,'C',0);
$pdf->Cell($W9,$H,$roww['V_PRAHAE'],'LRT',0,'C',0);						
$pdf->Cell($W10,$H,$roww['SALMONELLA'],'LRT',0,'C',0);						
$pdf->Cell($W11,$H,$roww['LISTERIA'],'LRT',1,'C',0);	

//$H1=1;
for ($x = 0; $x <= 2; $x++) {
	
if($x==1){
	$pdf->Cell($W2,$H,$TRACEBILITY,'LR',0,'C',0);
$pdf->Cell($W3,$H,$po_DOC,'LR',0,'C',0); 
$pdf->Cell($W4,$H,'','LR',0,'C',0); 	
$pdf->Cell($W4,$H,'','LR',0,'C',0); 	
	}else{
	$pdf->Cell($W2,$H,'','LR',0,'C',0);
$pdf->Cell($W3,$H,'','LR',0,'C',0); 
$pdf->Cell($W4,$H,'','LR',0,'C',0); 	
$pdf->Cell($W4,$H,'','LR',0,'C',0); 	
	}
	
 
$pdf->Cell($W5,$H,'','LR',0,'C',0);						
$pdf->Cell($W6,$H,'','LR',0,'C',0);						
$pdf->Cell($W7,$H,'','LR',0,'C',0);						
//$pdf->Cell($W8,$H,'','LR',0,'C',0);	
$pdf->Cell($W8,$H,$roww['V_CHOLERAE'],'LRT',0,'C',0);	
$pdf->Cell($W9,$H,$roww['V_PRAHAE'],'LRT',0,'C',0);						
$pdf->Cell($W10,$H,$roww['SALMONELLA'],'LRT',0,'C',0);						
$pdf->Cell($W11,$H,$roww['LISTERIA'],'LRT',1,'C',0);	
} 
					


$pdf->Cell($W2,$H,$I_MCS,'LRB',0,'C',0);
$pdf->Cell($W3,$H,'','LRB',0,'C',0); 
$pdf->Cell($W4,$H,'','LRB',0,'C',0);  
$pdf->Cell($W4,$H,'','LRB',0,'C',0);  
$pdf->Cell($W5,$H,'','LRB',0,'C',0);						
$pdf->Cell($W6,$H,'','LRB',0,'C',0);						
$pdf->Cell($W7,$H,'','LRB',0,'C',0);						
//$pdf->Cell($W8,$H,'','LRB',0,'C',0);						
$pdf->Cell($W8,$H,$roww['V_CHOLERAE'],1,0,'C',0);						
$pdf->Cell($W9,$H,$roww['V_PRAHAE'],1,0,'C',0);						
$pdf->Cell($W10,$H,$roww['SALMONELLA'],1,0,'C',0);						
$pdf->Cell($W11,$H,$roww['LISTERIA'],1,1,'C',0);	


$COUNT++;
   }

/*------------------------------------------------CHINA------------------------------------------------------*/   
}

//$TOTAL_SAMPLES
$pdf->Ln();
$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(49 ,5,'Method:****',0,1,'L');
$pdf->SetFont($FONT_ARI,'',9);

$contents='<p size="10">The bacteriological results are found well with in the limits. This is also to certify that the
 above product is free from filth & decomposition.Hence the above product is fit for export and human consumption</p>';
$pdf->writeHTML($contents);
$pdf->Ln();


$pdf->Ln();
$pdf->Cell(49 ,5,'',0,1,'L');
//$pdf->Cell(95,5,'Tested By :'.$BUYER_MB_REPORT_ROW['TESTED_BY'],0,1,'L');



$pdf->SetFont($FONT_ARI,'B',$header);

$pdf->Cell(95,5,'FOR'.$PROCESSOR,0,1,'L');  // cell with left and right borders
$pdf->Cell(95,5,''.$BUYER_MB_REPORT_ROW['VERIFIED_BY'],0,1,'L');  // cell with left and right borders
$pdf->Cell(95,5,'QC TECHNOLOGIST',0,1,'L');  // cell with left and right borders




$pdf->Ln();
$pdf->Output('microbiology.pdf', 'I');

?>   