<?php
require('../fpdf/fpdf.php');
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
$LOT_ID=$_GET['LOT_ID'];

			$sql_inp="SELECT * from inputmaterial_details where id='$LOT_ID'";
			$stmt_inp=$iconn->query($sql_inp);
			$inp_row = $stmt_inp->fetch_assoc();
			
			$sql_varity_list="SELECT * from PPC_GRADING_HL where LOT_ID='$LOT_ID' and DATA_TYPE='Headless Grading'";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$r_varity_list = $stmt_varity_list->fetch_assoc();
			
			
			$data = $r_varity_list['HOSO_W_DATA'];
			
		
			$json_decode_data=json_decode($data,true);
			$HL_WEIGHT=0;
		
		
		
		foreach($json_decode_data  as $value){
			
			if(!in_array($value['Count'],$ary_counts)){
				$ary_counts[]=$value['Count'];
				${"ary_cc" . $value['Count']} =$value['Count'];
				
			}
			
			
			
		}
		
		sort($ary_counts);
		
		$ary_test;
		foreach($ary_counts  as $count){
		$ary_start=array();
		foreach($json_decode_data  as $value){
			
			if($value['Count']==$count){
		
				$ary_start[] = $value['NetWeight'];
				
			}	
		}
		
		$ary_test[]=$ary_start;
		}
		
		
		//print_r($ary_test);
		
		$r=0;
		foreach($ary_test as $val){
			
			if($r < count($val)){
			$r=count($val);
		
		}
		}
		
		
//echo $r;

//================= fetching HLG details ===================
$HLGQuery = "SELECT im.PURCHASE_DATE, im.LOT_NO, im.UNIT_ID, ppc.COUNT, ppc.WEIGHT FROM 
`inputmaterial_details` im LEFT JOIN RM_BILL_DETAILS ppc on im.id = ppc.LOT_ID WHERE im.id = '$LOT_ID'";

$HLGexec=$iconn->query($HLGQuery);
$center = $purchaseDate = $headlessCount = $headlessQty = $LOTNO = "";
if(!empty($HLGexec)){
	$HLGRep = $HLGexec->fetch_assoc();
	if($HLGRep['UNIT_ID'] == 1){
		$center = 'Bhimavaram';
	}else if($HLGRep['UNIT_ID'] == 2){
		$center = 'Narsapuram';
	}
	$purchaseDate = $HLGRep['PURCHASE_DATE'];
	$headlessCount = (!empty($HLGRep['COUNT'])?$HLGRep['COUNT'].' c': '');
	$headlessQty = (!empty($HLGRep['WEIGHT'])?$HLGRep['WEIGHT'].' Kg(s)': '');
	$LOTNO = $HLGRep['LOT_NO'];
}
$weight_sum = 0;
$headlessGradingDetails = "SELECT ppc.HOSO_W_DATA, ppc.SUBMISSION_DATE FROM `PPC_GRADING_HL` ppc where LOT_ID = '$LOT_ID'";
$headlessExec = $iconn->query($headlessGradingDetails);
$headlessRes = $headlessExec->fetch_assoc();
$dd = strtotime($headlessRes['SUBMISSION_DATE']);
$dateF = getDate($dd);
$submission_date = date('d-m-Y h:i:s',strtotime($headlessRes['SUBMISSION_DATE']));
// $submission_date = $dateF['mday'].'-'.$dateF['mon'].'-'.$dateF['year'];


if(!empty($headlessRes['HOSO_W_DATA'])){
    $json_decode_data = json_decode($headlessRes['HOSO_W_DATA'], true);
    foreach($json_decode_data  as $value){
			 $weight_sum=$weight_sum + $value['NetWeight'];
					
	}
}



class PDF extends FPDF
{
protected $col = 0; // Current column
protected $y0;      // Ordinate of column start

function Header()
{
    // Page header
    global $title;

    $this->SetFont('Arial','B',15);
    $w = $this->GetStringWidth($title)+6;
    $this->SetX((210-$w)/2);
    $this->SetDrawColor(0,80,180);
    $this->SetFillColor(230,230,0);
    $this->SetTextColor(220,50,50);
    $this->SetLineWidth(1);
    $this->Cell($w,9,$title,1,1,'C',true);
    $this->Ln(10);
    // Save ordinate
    $this->y0 = $this->GetY();
}
}
$pdf = new PDF();
$title = '20000 Leagues Under the Seas';
$pdf->SetTitle($title);
 //A4 width : 219mm
//default margin : 10mm each side
//writable horizontal : 219-(10*2)=189mm
//create pdf object
$pdf = new FPDF('P','mm','A4');
//add new page
$pdf->AddPage();
//set font to arial, bold, 14pt
$pdf->SetFont('Arial','BU',12);

if ($inp_row['UNIT']==1) {
	$COMPANY='JME';
	$logo = "../images/jme_logo.jpg";
	$footer = "../images/jme_footer.jpg";
}else{
	$COMPANY='MOURYA';
	$logo = "../images/ma_logo_editt.jpg";
$footer = "";
}




$pdf->Cell( 0, 0, $pdf->Image($logo, null, null, 33.78), 0, 1, 'L', false );


$FONT_ARI='Arial';
$HEIGHT=8;
$WIDHT_1='Arial';

$header=10;
$sub_header=8;
$text=6;

$pdf->SetFont($FONT_ARI,'BU',$header);

//Cell(width , height , text , border , end line , [align] )

//$pdf->Cell(180 ,$HEIGHT,'SAMPLE PARTICULARS :',0,1,'L');




 

//invoice contents
IF($COMPANY=='JME'){
$pdf->Cell(0 ,5,'JAGADEESH MARINE EXPORTS',0,1,'C');
$pdf->Cell(130 ,5,'',0,1,'C');
}ELSE{
$pdf->Cell(0 ,5,'MOURYA AQUEX PVT LTD',0,1,'C');
$pdf->Cell(130 ,5,'',0,1,'C');	
	
}


$OV_COUNT=1;


$W1=30;
$W5=35;
$W2=30;
$W3=60;
$W4=35;
$H1=5;

$tmspDate = $purchaseDate;
$PURDATE = date("d-m-Y", strtotime($tmspDate));


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'Grading Date:',1,0,'L');
$pdf->Cell($W2 ,$H1,$submission_date,1,0,'L');
$pdf->Cell($W3 ,$H1,'',1,0,'L');
$pdf->Cell($W4 ,$H1,'Purchase DATE:',1,0,'L');
$pdf->Cell($W5 ,$H1,$PURDATE,1,1,'L');


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'HL-COUNT:',1,0,'L');
$pdf->Cell($W2 ,$H1,$headlessCount,1,0,'L');
$pdf->Cell($W3 ,$H1,'',1,0,'L');
$pdf->Cell($W4 ,$H1,'LOT NO:',1,0,'L');
$pdf->Cell($W5 ,$H1,$LOTNO,1,1,'L');


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'HL-QUANTITY:',1,0,'L');
$pdf->Cell($W2 ,$H1,'',1,0,'L');
$pdf->Cell($W3 ,$H1,'HEADLESS TO GRADING REPORT',3,0,'C');
$pdf->Cell($W4 ,$H1,'CENTER:',1,0,'L');
$pdf->Cell($W5 ,$H1,$center,1,1,'L');


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'HON-PACKING:',1,0,'L');
$pdf->Cell($W2 ,$H1,'',1,0,'L');
$pdf->Cell($W3 ,$H1,'',1,0,'L');
$pdf->Cell($W4 ,$H1,'HON-COUNT:',1,0,'L');
$pdf->Cell($W5 ,$H1,$headlessCount,1,1,'L');


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W1 ,$H1,'REJECTION QTY:',1,0,'L');
$pdf->Cell($W2 ,$H1,'',1,0,'L');
$pdf->Cell($W3 ,$H1,'',1,0,'L');
$pdf->Cell($W4 ,$H1,'HON.QTY:',1,0,'L');
$pdf->Cell($W5 ,$H1,$headlessQty,1,1,'L');

$W5=10;
$pdf->Cell($W5 ,$H1,'',0,1,'L');


$pdf->SetFont($FONT_ARI,'B',$sub_header);
$pdf->Cell($W5 ,$H1,'SI.NO',1,0,'L');


$W4=180/COUNT($ary_counts);
sort($ary_counts);
$c=1;
foreach($ary_counts as $EACH_count){
	if($c==count($ary_counts)){
		$pdf->Cell($W4 ,$H1,'COUNT: '.$EACH_count,1,1,'L');
	}else{
		$pdf->Cell($W4 ,$H1,'COUNT: '.$EACH_count,1,0,'L');
	}
	$c++;
}

for($i=0; $i < $r ; $i++){
	$pdf->Cell($W5 ,$H1,$i+1,1,0,'L');
	
	for($j=0 ; $j < count($ary_test) ; $j++){
		$val=$ary_test[$j][$i];
		
		if($j < count($ary_test) -1){
			$pdf->Cell($W4 ,$H1,$val,1,0,'L');
		}else{
			$pdf->Cell($W4 ,$H1,$val,1,1,'L');
		}
		
	}	
}

$pdf->Cell($W5 ,$H1,'Total',1,0,'L');

$c=1;

//$weight_sum=0;
foreach($ary_counts  as $count){	

foreach($json_decode_data  as $value){			
			if($value['Count'] == $count){
				 $weight_count=$weight_count + $value['NetWeight'];
			}			
			}
			
			
	if($c==count($ary_counts)){
		$pdf->Cell($W4 ,$H1,$weight_count .' Kgs',1,1,'L');	
	}else{
		$pdf->Cell($W4 ,$H1,$weight_count .' Kgs',1,0,'L');	
	}
	$c++;
	
	$weight_count=0;	
}

	
		
		
		
		$weight_sum=0;	
		foreach($json_decode_data  as $value){			
				 $weight_sum=$weight_sum + $value['NetWeight'];			
			}


$pdf->Cell(190,10,'Total : '.$weight_sum .' Kgs',1,1,'L',0);
$pdf->Cell(95,10,'Signature',1,0,'L',0);
$pdf->Cell(95,10,'Signature',1,1,'L',0);  // cell with left and right borders

//$pdf->Cell( 189, 40, $pdf->Image($footer, null, 0, 180), 0, 0, 'L' );

$pdf->Ln();
 
               
$pdf->Output();
?>