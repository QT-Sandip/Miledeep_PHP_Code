<?php


$ID=$_GET['id'];
session_start();


include "../includes/config.php";

include "Constants.php";
include "PrintHeaderInc.php";



$sql_exporter="SELECT * FROM EXPORTER_DETAILS ORDER BY ID DESC LIMIT 1";
$stmt_exporter=$iconn->query($sql_exporter);
$row_processor=$stmt_exporter->fetch_array();



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
	   //$MCS=$code_list_row['T_O_MCS'];
	   
	   $T_MCS=$T_MCS+$MCS;
       
	   
	  
	  
	  if(!in_array($DAY_CODE,$ary_code,true)){ 
		$ary_code[]=$DAY_CODE;
	  
	  IF($T_DAY_CODE==''){
		  $T_DAY_CODE=$DAY_CODE;
	  }
	  else{
		  $T_DAY_CODE=$T_DAY_CODE.','.$DAY_CODE;
	  }  
	  }else{
	  //echo $DAY_CODE;
	  }
	  
	  
	  //print_r($ary_code);
	  
	  if(in_array($PACKING,$ary_PACKING)){                               		 
	  }
	  else{
		  $ary_PACKING[]=$PACKING;
	  
	  IF($T_PACKING==''){
		  if($GLAZE_PERC==0){
			  $T_PACKING=$PACKING; 
		  }else{
			  $T_PACKING=$PACKING; 
		  }
		 
	  }
	  else{
		  if($GLAZE_PERC==0){
			 $T_PACKING=$T_PACKING.','.$PACKING;
		  }else{
			  $T_PACKING=$T_PACKING.','.$PACKING;
		  }
		  
	  }
	  
	  }
	
}

//print_r($ary_code);

 $T_MCS= $T_MCS.'MC';

$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

function getHeader($ID,$iconn){

$contents = '';
	 	
$sql="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();
$CONSIGNMENT_EXPORTED=$row['CONSIGNMENT_EXPORTED'];

 $PO_TME_STAMP=strtotime($row['PO_DATE']);
 $PO_DATE=date("d-m-Y", $PO_TME_STAMP);

$INV_TME_STAMP=strtotime($row['INVOICE_DATE']);
$INV_DATE=date("d-m-Y", $INV_TME_STAMP);

}



		
	

	$from_title = date('d M, Y');
	$to_title = date('d M, Y');

	


	
    
	//require_once('Constants.php');
	
    



 $pdf->SetTitle('Covering Note: '.$row['PO_NUM'].' - '.$row['PO_DATE']);  
	
    $content = '';  
   
    $content .= getHeader($ID,$iconn);  
    
   
   

	
$logo = $COMPANY_LOGO;
$toolcopy = '<img src="http://live-jme.mydeal.live/images/jme_logo.jpg"  width="80" height="80">';
//$pdf->writeHTML($toolcopy, true, 0, true, 0);


//$pdf->Cell(180 ,35,'',0,1,'L');
$header=11;
$sub_header=10;


$PO_TIME_STAMP = strtotime($row['DO_SAMPLING']);
$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);

$pdf->SetFont($FONT_ARI,'',$header);

$pdf->Cell(0 ,5,'DT:'.$DO_SAMPLING,0,1,'R');


$pdf->Cell(180 ,5,'TO',0,1,'L');
//$pdf->Ln();

$pdf->Cell(150 ,5,'THE DEPUTY DIRECTOR IN-CHARGE,',0,1,'L');
$pdf->Cell(49 ,5,'EXPORT INSPECTION AGENCY-CHENNAI,',0,1,'L');
$pdf->Cell(150 ,5,'SUB OFFICE BHIMAVARAM-534 202',0,1,'L');
$pdf->Cell(0 ,5,'Respected Sir/Madam',0,1,'L');

$pdf->Ln();


$pdf->SetFont($FONT_ARI,'B',20);
$pdf->Cell(0 ,5,'Sub: Submission of Q certificate',0,1,'L');

//$pdf->Ln();

$pdf->SetFont($FONT_ARI,'BU',20);
//$pdf->Cell(0 ,5,'NOn -EU(HEALTH)',0,1,'C');
$pdf->SetFont($FONT_ARI,'',$header);


$pdf->writeHTML($content);   
/*$pdf->Ln();	
$pdf->Cell($W1 ,$H1,'',0,1,'L');
*/
$W1=100;
$W2=90;
$H1=5;
$pdf->SetFont($FONT_ARI,'',$header);

$pdf->Cell($W1 ,$H1,'I.Details identifying the fishery products:',0,1,'L');

$pdf->Ln();
//$pdf->SetFont($FONT_ARI,'B',$sub_header);
while($varity_list_row=$stmt_varity_list->fetch_array()){
	$ary_varity_list[]=$varity_list_row['FULL_NAME'];
$pdf->Cell(15 ,$H1,'',0,0,'L');
$pdf->Cell($W2 ,$H1,$varity_list_row['FULL_NAME'],0,1,'L');
}

$pdf->SetFont($FONT_ARI,'',$header);
$pdf->Ln();

$COUNT=1;





$count_v=count($ary_varity_list);

if($count_v>5){
	$c_pd=3;
}else{
	$c_pd=6;
}





$FARMER_NAME .= '<table border="0" cellspacing="0" padding-left: 10px; cellpadding="2">

           		<tr><th  width="55%" align="left">‘’Q’’ CERTIFICATE NUMBER  </th>
                <th width="45%" align="left">:'.$row['CERTIFICATE_NO'].' </th>				
           </tr>
		   
		   <tr><th  width="55%" align="left">INVOICE NO&DT	  </th>
                <th width="45%" align="left">:'.$row['INVOICE_NUMBER'].' DT:'.$row['INVOICE_DATE'].'</th>				
           </tr>
		   
		   <tr><th  width="55%" align="left">PO NO&DT </th>
                <th width="45%" align="left">: '.$row['PO_NUM'].' DT:'.$row['PO_DATE'].'</th>				
           </tr>

<tr><th  width="55%" align="left">Description of fishery/ aquaculture products</th>
                <th width="45%" align="left">:AQUACULTURE ORIGIN </th>				
           </tr>

			
		   <tr>  
           		<th width="55%" align="left">Species (Scientifc Name)</th>
                <th width="45%" align="left">: '.$row['SCIENTIFIC_NAME'].'</th>				
           </tr>
		   
			
			<tr>  
           		<th width="55%" align="left">Presentation of product and type of treatment(2)</th>
                <th width="45%" align="left">: '.$row['TYPE_PROCESSING'].'</th>				
           </tr>
		   
		   
			
		   
		   <tr> 
           		<th width="55%" align="left">Code Number(where available)</th>
                <th width="45%" align="left">: '.$T_DAY_CODE.'</th>				
           </tr>
			
			<tr> 
           		<th width="55%" align="left">Type of Packaging</th>
                <th width="45%" align="left">:CORRUGATED FIBRE BOARD MASTER </th>				
           </tr>
		   
		   <tr>  
           		<th width="55%" align="left">Number of Packages</th>
                <th width="45%" align="left">: '.$T_MCS.'('.$T_PACKING.')</th>				
           </tr>
		   
		   <tr> 
           		<th width="55%" align="left"> Net Weight</th>
                <th width="45%" align="left">: '.$row['QUANTITY_IN_KGS'].'</th>				
           </tr>
		   
		   <tr> 
           		<th width="55%" align="left">GROSS WEIGHT</th>
                <th width="45%" align="left">: ****</th>				
           </tr>
		  
		  <tr> 
           		<th width="55%" align="left">COUNTRY </th>
                <th width="45%" align="left">: '.$row['DESTINATION_COUNTRY'].'</th>				
           </tr>
		   
		   <tr> 
           		<th width="55%" align="left">Requisite  storage and transport temperature</th>
                <th width="45%" align="left">: -18 .0°C OR BELOW
</th>				
           </tr>
		   
		   <tr>  
				
           		<th width="55%" align="left">II Origin of Products</th>
                <th width="45%" align="left">: INDIA  </th>				
           </tr>
		   
		   <tr>  
				
           		<th width="55%" align="left">Approval Number of the Establishment                        </th>
                <th width="45%" align="left">: '.$PROCESSOR_APP_NUM.'  </th>				
           </tr>
		   
		   </table>  
		';


$pdf->writeHTML($FARMER_NAME);  

//$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);
		
 /*
 $toolcopy = '<img src="http://live-jme.mydeal.live/images/jme_footer.jpg"  width="600px" height="80">';
$footer = "images/jme_footer.jpg";
//$pdf->Cell(12,5, $pdf->Image($footer, 10,265, 190,0),0,0,'C',0);

//$pdf->writeHTML($toolcopy, true, 0, true, 0);
*/


/* next page*/

//$pdf->AddPage();



$W2=85;
$W3=100;
$H1=5;


$pdf->Cell(180 ,35,'',0,1,'L');

$consign_exp = explode(',', $row['CONSIGNMENT_EXPORTED_TO']);

 $CONSIGNMENT_EXPORTED_TO=$consign_exp[0].', CONTAINER NO :'.$consign_exp[1].', '.'VESSEL NAME :'.$consign_exp[2].', SEAL NO :'.$consign_exp[3];







//$pdf->writeHTML($contents);

$pdf->Cell(0 ,5,'',0,1,'L');

//$pdf->writeHTML($contents);
$PO_TIME_STAMP = strtotime($row['DO_SAMPLING']);
$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);


$pdf->Ln();
//$pdf->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);

 
 
 
 


$pdf->Output('payroll.pdf', 'I');

?>