<?php
//require('../fpdf/fpdf.php');
session_start();
include "../includes/config.php";
include "Constants.php";
include "PrintHeaderInc.php";
$ID=$_GET['id'];

$sql="SELECT * FROM `IH_MICROBIOLOGY_TEST_REPORT`  WHERE `ID`='$ID'";
$stmt_REP=$iconn->query($sql);
$row=$stmt_REP->fetch_array();

$sql="SELECT * FROM `TEST_RESULT_PARMS` where TEST_NAME='Buyer Mb'";

$stmt_HEADER=$iconn->query($sql);

$post_details=$stmt_HEADER->fetch_array();

$sql="SELECT * FROM `IH_MICROBIOLOGY_TEST_RESULT` WHERE `M_T_REPORT_ID`='$ID'";

$stmt=$iconn->query($sql);


$INV_ID=$row['INVOICE_ID'];

$SQL_INVOICE_DATA="SELECT * FROM `INVOICE_DATA`  WHERE `ID`='$INV_ID'";

$STMT_INVOICE_DATA=$iconn->query($SQL_INVOICE_DATA);
$ROW_INVOICE_DATA=$STMT_INVOICE_DATA->fetch_array();



		 $CODE_LIST_ID=$row['FARM_REG_NO'];

		  $SQL_CODE_LIST="SELECT * FROM `CODE_LIST_CREATE` where CODE_LIST_ID='$CODE_LIST_ID'";
		 $STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
		 $CODE_LIST_ROW=$STMT_CODE_LIST->fetch_array();


		 $NAME_ADD_OF_PROCESSOR=$ROW_INVOICE_DATA['NAME_ADD_OF_PROCESSOR'];		
		 $SCIENTIFIC_NAME=$ROW_INVOICE_DATA['SCIENTIFIC_NAME'];		
		 $PO_NUM=$ROW_INVOICE_DATA['PO_NUM'];		
		 $FARM_REG=$CODE_LIST_ROW['FARM_REG_ID'];		
		 $PACKING=$CODE_LIST_ROW['PACKING'];
		 $T_O_MCS=$CODE_LIST_ROW['T_O_MCS'].' MCS';
		 $DAY_CODE=$CODE_LIST_ROW['DAY_CODE'];
		 $FULL_NAME_ID=$CODE_LIST_ROW['FULL_NAME'];
		 $NAME_OF_FARMER=$CODE_LIST_ROW['NAME_OF_FARMER'];
		 $NAME_ADD_OF_FARMER=$CODE_LIST_ROW['NAME_OF_FARMER'].$CODE_LIST_ROW['ADD_OF_FARMER'];
		
		 
		  $SQL_PO_VARITY_LIST="SELECT * FROM `PO_VARIETY_LIST` where ID='$FULL_NAME_ID'";
		  $STMT_PO_VARITY_LIST=$iconn->query($SQL_PO_VARITY_LIST);
		  $PO_VARITY_LIST_ROW=$STMT_PO_VARITY_LIST->fetch_array();
		  $FULL_NAME=$PO_VARITY_LIST_ROW['FULL_NAME'];
		  $SHORT_NAME=$PO_VARITY_LIST_ROW['SHORT_NAME'];





$pdf->Cell(0 ,5,'IN-HOUSE TEST REPORT FOR MICROBIOLOGY',0,1,'C');
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
$W2=30;
$W3=120;
$H1=5;

$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Count:',1,'C',1);
$pdf->Cell($W2 ,$H1,'Count:',1,'C',1);
$pdf->Cell($W2 ,$H1,'Count:',1,'C',1);
$pdf->Cell($W2 ,$H1,'Count:',1,'C',1);
$pdf->Cell($W2 ,$H1,'Count:',1,'C',1);
$pdf->Cell($W2 ,$H1,'Count:',1,'C',1);
$OV_COUNT++;

for($i=1;$i++;$i=10){
	
	$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,,1,'C',1);
$pdf->Cell($W2 ,$H1,,1,'C',1);
$pdf->Cell($W2 ,$H1,,1,'C',1);
$pdf->Cell($W2 ,$H1,,1,'C',1);
$pdf->Cell($W2 ,$H1,,1,'C',1);
$pdf->Cell($W2 ,$H1,,1,'C',1);
	
$OV_COUNT++;	
	
}

// MULTILINE END




// MULTILINE START
$HEADER='Product/Sample Name';
$FARMER_NAME=$FULL_NAME;
$NAME_LENGTH=strlen($FARMER_NAME);
$X=false;
$COUNT=1;
$LIMIT=60;

while($X==false){

if(strlen($FARMER_NAME)>$LIMIT){
	
$FIRST=substr($FARMER_NAME,0,$LIMIT);
$REST=substr($FARMER_NAME,$LIMIT);

if($COUNT==1){
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,$HEADER,'LR',0,'L');
$pdf->Cell($W3 ,$H1,$FIRST,'LR',1,'L');	
}else{
$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'','LR',0,'L');
$pdf->Cell($W3 ,$H1,$FIRST,'LR',1,'L');	
}
$FARMER_NAME=$REST;		
}else{
if($COUNT==1){
$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,$HEADER,'LR',0,'L');
$pdf->Cell($W3 ,$H1,$FARMER_NAME,'LR',1,'L');	
}else{
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

$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Purchase Order No',1,0,'L');
$pdf->Cell($W3 ,$H1,$ROW_INVOICE_DATA['PO_NUM'],1,1,'L');
$OV_COUNT++;

$D_SAMPLING_TMSP=strtotime($row['D_O_SAMPLING']);
$D_SAMPLING=date("d-m-Y", $D_SAMPLING_TMSP);

$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'P.O date',1,0,'L');
$pdf->Cell($W3 ,$H1,$D_SAMPLING,1,1,'L');
$OV_COUNT++;

$pdf->Cell($W1 ,$H1,$OV_COUNT,'LR',0,'L');
$pdf->Cell($W2 ,$H1,'Name of the representation/Technologist ','LR',0,'L');
$pdf->Cell($W3 ,$H1,$row['NAME_OF_REP'],'LR',1,'L');

$pdf->Cell($W1 ,$H1,'','LR',0,'L');
$pdf->Cell($W2 ,$H1,'who has drawn the sample','LR',0,'L');
$pdf->Cell($W3 ,$H1,'','LR',1,'L');


$OV_COUNT++;

$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Lab Sample ID',1,0,'L');
$pdf->Cell($W3 ,$H1,$row['LAB_SAMPLE_ID'],1,1,'L');
$OV_COUNT++;

$D_ANALYSIS_TMSP=strtotime($row['D_O_ANALYSIS']);
$D_ANALYSIS=date("d-m-Y", $D_ANALYSIS_TMSP); 


$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Date of Analysis',1,0,'L');
$pdf->Cell($W3 ,$H1,$D_ANALYSIS,1,1,'L');

$DOA=$row['D_O_ANALYSIS'];
$OV_COUNT++;

$D_ISSUANC_TMSP=strtotime($row['D_O_COMPLEATION']);
$D_ISSUANC=date("d-m-Y", $D_ISSUANC_TMSP);


$pdf->Cell($W1 ,$H1,$OV_COUNT,1,0,'L');
$pdf->Cell($W2 ,$H1,'Date of Issuance',1,0,'L');
$pdf->Cell($W3 ,$H1,$D_ISSUANC,1,1,'L');
$DOC=$row['D_O_COMPLEATION'];
$OV_COUNT++;


$pdf->SetFont($FONT_ARI,'BU',$header);

//Cell(width , height , text , border , end line , [align] )

$pdf->Cell(180 ,$HEIGHT,'TEST RESULT :',0,1,'L');
$pdf->SetFont($FONT_ARI,'',$sub_header);

$W1=35;								
$W2=30;									
$W3=19;									
$W4=18;									
$W5=20;	
$W6=18;	
$W7=16;	
$W8=16;	
$W9=16;	
$W10=16;
$W11=20;	
$H=5;								

$pdf->Ln();				

			

$pdf->Cell($W2,$H,substr($post_details['PROD_REG'],0,10),'LRT',0,'C',0);
$pdf->Cell($W3,$H,substr($post_details['DOA_DOC'],0,8),'LRT',0,'C',0); 
$pdf->Cell($W4,$H,$post_details['VARITY'],'LRT',0,'C',0);  
$pdf->Cell($W5,$H,substr($post_details['TPC'],0,6),'LRT',0,'C',0);						
$pdf->Cell($W6,$H,substr($post_details['ECOLI'],0,7),'LRT',0,'C',0);						
$pdf->Cell($W7,$H,substr($post_details['STAP'],0,8),'LRT',0,'C',0);						
$pdf->Cell($W8,$H,substr($post_details['V_CHLORAE'],0,11),'LRT',0,'C',0);						
$pdf->Cell($W9,$H,substr($post_details['V_PARAHA'],0,7),'LRT',0,'C',0);						
$pdf->Cell($W10,$H,substr($post_details['SALMONELLA'],0,11),'LRT',0,'C',0);						
$pdf->Cell($W11,$H,substr($post_details['ANALYTICAL_REPORT'],0,10),'LRT',1,'C',0);	


$pdf->Cell($W2,$H,substr($post_details['PROD_REG'],10,10),'LR',0,'C',0);
$pdf->Cell($W3,$H,substr($post_details['DOA_DOC'],8,9),'LR',0,'C',0); 
$pdf->Cell($W4,$H,'','LR',0,'C',0);  
$pdf->Cell($W5,$H,substr($post_details['TPC'],6,6),'LR',0,'C',0);						
$pdf->Cell($W6,$H,substr($post_details['ECOLI'],7,12),'LR',0,'C',0);						
$pdf->Cell($W7,$H,substr($post_details['STAP'],8,16),'LR',0,'C',0);						
$pdf->Cell($W8,$H,substr($post_details['V_CHLORAE'],11,10),'LR',0,'C',0);						
$pdf->Cell($W9,$H,substr($post_details['V_PARAHA'],7,10),'LR',0,'C',0);						
$pdf->Cell($W10,$H,substr($post_details['SALMONELLA'],11,10),'LR',0,'C',0);						
$pdf->Cell($W11,$H,substr($post_details['ANALYTICAL_REPORT'],10),'LR',1,'C',0);	

$pdf->Cell($W2,$H,substr($post_details['PROD_REG'],20),'LR',0,'C',0);
$pdf->Cell($W3,$H,substr($post_details['DOA_DOC'],17,11),'LR',0,'C',0); 
$pdf->Cell($W4,$H,'','LR',0,'C',0);  
$pdf->Cell($W5,$H,substr($post_details['TPC'],12),'LR',0,'C',0);						
$pdf->Cell($W6,$H,substr($post_details['ECOLI'],20),'LR',0,'C',0);						
$pdf->Cell($W7,$H,substr($post_details['STAP'],23,8),'LR',0,'C',0);						
$pdf->Cell($W8,$H,substr($post_details['V_CHLORAE'],22),'LR',0,'C',0);						
$pdf->Cell($W9,$H,substr($post_details['V_PARAHA'],19),'LR',0,'C',0);						
$pdf->Cell($W10,$H,substr($post_details['SALMONELLA'],22),'LR',0,'C',0);						
$pdf->Cell($W11,$H,'','LR',1,'C',0);

$pdf->Cell($W2,$H,'','LRB',0,'C',0);
$pdf->Cell($W3,$H,substr($post_details['DOA_DOC'],28),'LRB',0,'C',0); 
$pdf->Cell($W4,$H,'','LR',0,'C',0);  
$pdf->Cell($W5,$H,'','LRB',0,'C',0);												
$pdf->Cell($W6,$H,'','LRB',0,'C',0);
$pdf->Cell($W7,$H,substr($post_details['STAP'],32),'LRB',0,'C',0);						
$pdf->Cell($W8,$H,'','LRB',0,'C',0);						
$pdf->Cell($W9,$H,'','LRB',0,'C',0);						
$pdf->Cell($W10,$H,'','LRB',0,'C',0);						
$pdf->Cell($W11,$H,'','LRB',1,'C',0);						



$COUNT=1;

$pdf->SetFont($FONT_ARI,'',$sub_header);

while($roww=$stmt->fetch_array()){
	
	$prod=explode('&',$roww['P_CODES_REG_NUM']	);
	$DOA_DOC=explode('&',$roww['DOA_DOC']	);

$pdf->Cell($W2,$H,$DAY_CODE,'LRT',0,'C',0);
$pdf->Cell($W3,$H,$DOA,'LRT',0,'C',0); 
$pdf->Cell($W4,$H,substr($SHORT_NAME,0,6),'LRT',0,'C',0);  
$pdf->Cell($W5,$H,$roww['T_P_C'],'LRT',0,'C',0);						
$pdf->Cell($W6,$H,$roww['E_COLI'],'LRT',0,'C',0);						
$pdf->Cell($W7,$H,$roww['STAPHYLO_COCCUS'],'LRT',0,'C',0);						
$pdf->Cell($W8,$H,$roww['V_CHOLERAE'],'LRT',0,'C',0);						
$pdf->Cell($W9,$H,$roww['V_PARAHA_EMOLYTICU'],'LRT',0,'C',0);						
$pdf->Cell($W10,$H,$roww['SALMOELLA'],'LRT',0,'C',0);						
$pdf->Cell($W11,$H,$roww['A_REPORT'],'LRT',1,'C',0);	

$pdf->Cell($W2,$H,$FARM_REG,'LRB',0,'C',0);
$pdf->Cell($W3,$H,$DOC,'LRB',0,'C',0); 
$pdf->Cell($W4,$H,substr($SHORT_NAME,6),'LRB',0,'C',0);  
$pdf->Cell($W5,$H,'','LRB',0,'C',0);						
$pdf->Cell($W6,$H,'','LRB',0,'C',0);						
$pdf->Cell($W7,$H,'','LRB',0,'C',0);						
$pdf->Cell($W8,$H,'','LRB',0,'C',0);						
$pdf->Cell($W9,$H,'','LRB',0,'C',0);						
$pdf->Cell($W10,$H,'','LRB',0,'C',0);						
$pdf->Cell($W11,$H,'','LRB',1,'C',0);	


$COUNT++;
   }
$pdf->Ln();

$HEIGHT=5;
$pdf->Cell(130 ,$HEIGHT,'THE  ABOVE CONSIGNMENT HAS BEEN TESTED FOR  MICROBIOLOGICALLY AS PER USFDA BAM 8TH ED METHOD AND  FOUND ',0,1);
$pdf->Cell(130 ,$HEIGHT,' SATISFACTORY & I ALSO CERTIFY THAT THE ABOVE CONSIGNMENT ORGANOLEPTICALLY PASSED AND EXPORT WORTHY ',0,1);
$pdf->Ln();

$pdf->Cell(95,5,'Tested By :'.$row['TESTED_BY'],1,0,'L',0);
$pdf->Cell(95,5,'Verified By :'.$row['VARIFIED_BY'],1,1,'L',0);  // cell with left and right borders


$pdf->Cell(95,10,'Signature',1,0,'L',0);
$pdf->Cell(95,10,'Signature',1,1,'L',0);  // cell with left and right borders

//$pdf->Cell( 189, 40, $pdf->Image($footer, null, 0, 180), 0, 0, 'L' );

$pdf->Ln();
// $pdf->Cell(12,5, $pdf->Image($footer, 10,265, 190,0),0,0,'C',0);
               
$pdf->Output();
?>