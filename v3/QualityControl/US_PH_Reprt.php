<?php
//require('../fpdf/fpdf.php');
session_start();
include "../includes/config.php";

 include "PrintHeaderInc.php";

 

$PH_ID=$_GET['id'];

 $sql="SELECT * FROM `IH_ELSIA_PH_TEST_REPORT`  WHERE `ID`='$PH_ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

 $sql="SELECT * FROM `TEST_RESULT_PARMS` where TEST_NAME='ELISA'";

$stmt_HEADER=$iconn->query($sql);

$post_details=$stmt_HEADER->fetch_array();



$INV_ID=$row['INVOICE_ID'];
$ID=$row['INVOICE_ID'];

 include "Constants.php";

 $SQL_INVOICE_DATA="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$INV_ID'";
$STMT_INVOICE_DATA=$iconn->query($SQL_INVOICE_DATA);
$ROW_INVOICE_DATA=$STMT_INVOICE_DATA->fetch_array();

$TYPE_OF_COUNTRY=$ROW_INVOICE_DATA['TYPE_OF_COUNTRY'];

 $sql="SELECT * FROM `IH_ELISA_PH_TEST_RESULT` WHERE `TEST_ID`='$ID'";
 $sql="SELECT * FROM `TEST_PARMS` where DESTINATION_TYPE='$TYPE_OF_COUNTRY' and COUNTRY='USA'";


$stmt=$iconn->query($sql);



		 $CODE_LIST_ID=$row['FARM_ID_POND_ID'];

		  $SQL_CODE_LIST="SELECT * FROM `CODE_LIST_CREATE` where CODE_LIST_ID='$CODE_LIST_ID'";
		 $STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
		 $CODE_LIST_ROW=$STMT_CODE_LIST->fetch_array();


		 $NAME_ADD_OF_PROCESSOR=$ROW_INVOICE_DATA['NAME_ADD_OF_PROCESSOR'];		
		 $SCIENTIFIC_NAME=$ROW_INVOICE_DATA['SCIENTIFIC_NAME'];		
		 $FARM_REG=$CODE_LIST_ROW['FARM_REG_ID'];		
		 $PACKING=$CODE_LIST_ROW['PACKING'];
		 $T_O_MCS=$CODE_LIST_ROW['T_O_MCS'];
		 $DAY_CODE=$CODE_LIST_ROW['DAY_CODE'];
		 $FULL_NAME_ID=$CODE_LIST_ROW['FULL_NAME'];
		 $NAME_OF_FARMER=$CODE_LIST_ROW['NAME_OF_FARMER'];
		 $NAME_ADD_OF_FARMER=$CODE_LIST_ROW['NAME_OF_FARMER'].$CODE_LIST_ROW['ADD_OF_FARMER'];
		
		 
		  $SQL_PO_VARITY_LIST="SELECT * FROM `PO_VARIETY_LIST` where ID='$FULL_NAME_ID'";
		  $STMT_PO_VARITY_LIST=$iconn->query($SQL_PO_VARITY_LIST);
		  $PO_VARITY_LIST_ROW=$STMT_PO_VARITY_LIST->fetch_array();
		  $FULL_NAME=$PO_VARITY_LIST_ROW['FULL_NAME'];








$pdf->Cell(0 ,5,'IN-HOUSE ELISA PRE HARVEST TEST REPORT FOR ANTIBIOTIC'.'('.$DAY_CODE.')',0,1,'C');
$pdf->Cell(130 ,5,'',0,1,'C');


$FONT_ARI='helvetica';
$HEIGHT=8;
$WIDHT_1='helvetica';

$header=10;
$sub_header=8;
$text=6;

$pdf->SetFont($FONT_ARI,'BU',$header);

//Cell(width , height , text , border , end line , [align] )

$pdf->Cell(180 ,$HEIGHT,'SAMPLE PARTICULARS :',0,1,'L');




//invoice contents
$pdf->SetFont($FONT_ARI,'',$sub_header);



$OV_COUNT=1;


$W1=10;
$W2=62;
$W3=120;
$H1=5;
$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');

$D_TEST_RPRT_TMSP=strtotime($row['TEST_REPORT_DATE']);
$TEST_RPRT=date("d-m-Y", $D_TEST_RPRT_TMSP);

$pdf->Cell($W2 ,$H1,'Test Report No.& date',1,0,'L');
$pdf->Cell($W3 ,$H1,$row['LAB_SAMPLE_ID'].' & '.$TEST_RPRT,1,1,'L');
$OV_COUNT++;


// MULTILINE START

$FARMER_NAME=$row['NAME_ADD_OF_FARMER'];
$FARMER_NAME=$NAME_ADD_OF_FARMER;
$NAME_LENGTH=strlen($FARMER_NAME);
$X=false;
$COUNT=1;
$LIMIT=60;

while($X==false){

if(strlen($FARMER_NAME)>$LIMIT){
	
$FIRST=substr($FARMER_NAME,0,$LIMIT);
$REST=substr($FARMER_NAME,$LIMIT);

if($COUNT==1){
$pdf->SetFont($FONT_ARI,'B',$sub_header);	
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,'Name and address of the Farmer','LR',0,'L');
$pdf->Cell($W3 ,$H1,$FIRST,'LR',1,'L');	
}else{
	$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'','LR',0,'L');
$pdf->Cell($W3 ,$H1,$FIRST,'LR',1,'L');	
}
$FARMER_NAME=$REST;		
}else{
if($COUNT==1){
	$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,'Name and Address of the farmer','LR',0,'L');
$pdf->Cell($W3 ,$H1,$FARMER_NAME,'LR',1,'L');	
}else{
	$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'','LR',0,'L');
$pdf->Cell($W3 ,$H1,$FARMER_NAME,'LR',1,'L');	
}
$X=TRUE;
}
$COUNT++;
	
}


// MULTILINE END
$OV_COUNT++;
$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Farm Reg. No / Regn. authority',1,0,'L');
$pdf->Cell($W3 ,$H1,$FARM_REG.'/'.$row['REG_AUTH'],1,1,'L');
$OV_COUNT++;

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Farm ID and Pond ID',1,0,'L');
$pdf->Cell($W3 ,$H1,$FARM_REG,1,1,'L');
$OV_COUNT++;


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,'Name of the supplier  in case the raw  ','LR',0,'L');
$pdf->Cell($W3 ,$H1,$NAME_OF_FARMER,'LR',1,'L');

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'material received from the middleman)','LR',0,'L');
$pdf->Cell($W3 ,$H1,'','LR',1,'L');
$OV_COUNT++;

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Type/Species Cultured',1,0,'L');
$pdf->Cell($W3 ,$H1,$ROW_INVOICE_DATA['SCIENTIFIC_NAME'],1,1,'L');
$OV_COUNT++;




// MULTILINE START

//$FARMER_NAME=$row['NAME_ADD_OF_PROCESSOR'];
 $FARMER_NAME=$PROCESSOR;
$NAME_LENGTH=strlen($FARMER_NAME);
$HEADER='Name and address of Processor';
$X=false;
$COUNT=1;
$LIMIT=58;

while($X==false){

if(strlen($FARMER_NAME)>$LIMIT){
	
$FIRST=substr($FARMER_NAME,0,$LIMIT);
$REST=substr($FARMER_NAME,$LIMIT);

if($COUNT==1){
	$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,$HEADER,'LR',0,'L');
$pdf->Cell($W3 ,$H1,$FIRST,'LR',1,'L');	
}else{
	$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'','LR',0,'L');
$pdf->Cell($W3 ,$H1,$FIRST,'LR',1,'L');	
}
$FARMER_NAME=$REST;		
}else{
if($COUNT==1){
	$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,$HEADER,'LR',0,'L');
$pdf->Cell($W3 ,$H1,$FARMER_NAME,'LR',1,'L');	
}else{
	$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'','LR',0,'L');
$pdf->Cell($W3 ,$H1,$FARMER_NAME,'LR',1,'L');	
}
$X=TRUE;
}
$COUNT++;
	
}


// MULTILINE END

$OV_COUNT++;
$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LRT',0,'L');
$pdf->Cell($W2 ,$H1,'Approx Quantity assessed by','LRT',0,'L');
$pdf->Cell($W3 ,$H1,$row['QUANTITY_IN_KGS'],'LRT',1,'L');


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'the establishment(Kg.)','LR',0,'L');
$pdf->Cell($W3 ,$H1,'','LR',1,'L');
$OV_COUNT++;


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Approval  number   of Establishment',1,0,'L');
$pdf->Cell($W3 ,$H1,$PROCESSOR_APP_NUM,1,1,'L');
$OV_COUNT++;

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,'Name of the representation/Technologist ','LR',0,'L');
$pdf->Cell($W3 ,$H1,$row['NAME_OF_REP'],'LR',1,'L');

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'who has drawn the sample','LR',0,'L');
$pdf->Cell($W3 ,$H1,'','LR',1,'L');
$OV_COUNT++;

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Date and time of sample collection',1,0,'L');
$D_SAMPLNG_TMSP=strtotime($row['DATE_OF_SAMPLING']);
$D_SAMPLING=date("d-m-Y",$D_SAMPLNG_TMSP);
$pdf->Cell($W3 ,$H1,$D_SAMPLING.' & '.$row['TIME_OF_SAMPLING'],1,1,'L');
$OV_COUNT++;

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Sample ID/Code',1,0,'L');
$pdf->Cell($W3 ,$H1,$row['LAB_SAMPLE_ID'],1,1,'L');
$OV_COUNT++;

$D_ANALYSIS_TMSP=strtotime($row['DATE_OF_ANALYSIS']);
$D_ANALYSIS=date("d-m-Y", $D_ANALYSIS_TMSP);

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Date of Analysis',1,0,'L');
$pdf->Cell($W3 ,$H1,$D_ANALYSIS,1,1,'L');
$OV_COUNT++;

$D_ISSUANCE_TMSP=strtotime($row['DATE_OF_ISSUANCE']);
$D_ISSUANCE=date("d-m-Y", $D_ISSUANCE_TMSP);


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Date of Issuance',1,0,'L');
$pdf->Cell($W3 ,$H1,$D_ISSUANCE,1,1,'L');
$OV_COUNT++;

//$D_HARVEST_TMSP=strtotime($row['D_T_HARVESTING']);
//$D_HARVEST=date("d-m-Y", $D_HARVEST_TMSP);

$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Date of Harvesting & Time',1,0,'L');
$pdf->Cell($W3 ,$H1,$row['D_T_HARVESTING'],1,1,'L');
$OV_COUNT++;

$pdf->SetFont($FONT_ARI,'BU',$header);

//Cell(width , height , text , border , end line , [align] )

$pdf->Cell(180 ,$HEIGHT,'TEST RESULT :',0,1,'L');

$pdf->SetFont($FONT_ARI,'B',$sub_header);

$W1=10;									
$W2=48 ;									
$W3=48;									
$W4=45;									
$W5=38;	
$H=5;								

$header='NITROFURAN METATOLITES';
$pdf->Ln();							
$pdf->Cell($W1,$H,'S/N','LRT',0,'C',0);
$pdf->Cell($W2,$H,substr($post_details['PARAMETER'],0,10),'LRT',0,'C',0);
$pdf->Cell($W3,$H,substr($post_details['DETCTION_LIMIT'],0,10),'LRT',0,'C',0); 
$pdf->Cell($W4,$H,$post_details['TEST_RESULT'],'LRT',0,'C',0);  
$pdf->Cell($W5,$H,substr($post_details['ANALYTIC_METHOD'],0,10),'LRT',1,'C',0);						

$pdf->Cell($W1,$H,'','LRB',0,'C',0);
$pdf->Cell($W2,$H,substr($post_details['PARAMETER'],10),'LRB',0,'C',0);
$pdf->Cell($W3,$H,substr($post_details['DETCTION_LIMIT'],10),'LRB',0,'C',0); 
$pdf->Cell($W4,$H,'','LRB',0,'C',0);  
$pdf->Cell($W5,$H,substr($post_details['ANALYTIC_METHOD'],10),'LRB',1,'C',0);

$COUNT=1;

$pdf->SetFont($FONT_ARI,'',$sub_header);

while($roww=$stmt->fetch_array()){
	if($header!=''){
		$pdf->SetFont($FONT_ARI,'B',$sub_header);
		$pdf->Cell($W1,$H,'A','LT',0,'C',0);
		$pdf->Cell(179,$H,$header,'LR',1,'L',0);
		$header='';
		$pdf->SetFont($FONT_ARI,'',$sub_header);
	}

	
	
	
$FARMER_NAME=$roww['PARAM_NAME'];
$NAME_LENGTH=strlen($FARMER_NAME);
$X=false;

$LIMIT=26;	

if($NAME_LENGTH > $LIMIT){

$pdf->Cell($W1,$H,$COUNT,'LR',0,'C',0);
$pdf->Cell($W2,$H,substr($roww['PARAM_NAME'],0,$LIMIT),'LR',0,'L',0);
$pdf->Cell($W3,$H,$roww['DETECTION_LIMIT'],'LR',0,'C',0);  
$pdf->Cell($W4,$H,'NEGATIVE','LR',0,'C',0);  
$pdf->Cell($W5,$H,'ELISA ','LR',1,'C',0); 	

$pdf->Cell($W1,$H,'','LR',0,'C',0);
$pdf->Cell($W2,$H,substr($roww['PARAM_NAME'],$LIMIT),'LR',0,'C',0);
$pdf->Cell($W3,$H,'','LR',0,'C',0);  
$pdf->Cell($W4,$H,'','LR',0,'C',0);  
$pdf->Cell($W5,$H,'','LR',1,'C',0); 	
}else{
	
		if($COUNT==5){
			$pdf->SetFont($FONT_ARI,'B',$sub_header);
		$pdf->Cell($W1,$H,'B',1,0,'C',0);
		//$pdf->SetFont($FONT_ARI,'',$sub_header);
	}else{
		$pdf->Cell($W1,$H,$COUNT,1,0,'C',0);
	}

$pdf->Cell($W2,$H,$roww['PARAM_NAME'],1,0,'L',0);
$pdf->SetFont($FONT_ARI,'',$sub_header);
$pdf->Cell($W3,$H,$roww['DETECTION_LIMIT'],1,0,'C',0);  
$pdf->Cell($W4,$H,'NEGATIVE',1,0,'C',0);  
$pdf->Cell($W5,$H,'ELISA ',1,1,'C',0); 
}
 

$COUNT++;
   }
$pdf->Ln();

$HEIGHT=5;
$pdf->Cell(130 ,$HEIGHT,'1.Test results are based on ELISA screening. ',0,1);
$pdf->Cell(130 ,$HEIGHT,'2.Test result should be reported as Screened positive or negative.',0,1);
$pdf->Cell(130 ,$HEIGHT,'3.Values obtained below detection limit can be reported as negative and above detection limit as positive.',0,1);
$pdf->Cell(130 ,$HEIGHT,'4.Actual values obtained above detection limit as positive shall be reported.',0,1);

$pdf->Ln();

$pdf->SetFont($FONT_ARI,'B',$sub_header);

$pdf->Cell(95,5,'Tested By :'.$row['TESTED_ID'],1,0,'L',0);
$pdf->Cell(95,5,'Verified By :'.$row['VARIFIED_ID'],1,1,'L',0);  // cell with left and right borders


$pdf->Cell(95,10,'Signature',1,0,'L',0);
$pdf->Cell(95,10,'Signature',1,1,'L',0);  // cell with left and right borders

//$pdf->Cell( 189, 40, $pdf->Image($footer, null, 0, 180), 0, 0, 'L' );

$pdf->Ln();
$pdf->Cell(130,5,'The test report refers only to the particular samples submitted for testing',0,1);   // cell with left and right borders

 //$pdf->Cell(12,5, $pdf->Image($footer, 10,265, 190,0),0,0,'C',0);
               
$pdf->Output();
?>