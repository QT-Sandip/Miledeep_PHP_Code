<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

			$LotID=$_GET['LotID'];

	
			//$sql_varity_list="SELECT * from PPC_GRADING_HL where LOT_ID='$LotID' and DATA_TYPE='Headless Grading'";
			 $SqlGradinglist="SELECT * FROM `RmLotDetatils` as RLD 
			LEFT JOIN PpcHLGradingDetails as PHGD on PHGD.LotID=RLD.RmLotID
			LEFT JOIN RmPurchaseStations as RPS on RPS.PurchaseStationID= RLD.PurchaseStationID
			where RmLotID='$LotID'";
			$StmtGradinglist=$iconn->query($SqlGradinglist);
			$RowGradinglist = $StmtGradinglist->fetch_assoc();
			
			$data = $RowGradinglist['GradingData'];
			
		
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
 $HLGQuery = "SELECT RLD.PurchaseDate, RLD.LotNumber, RLD.PurchaseStationID, RPS.Location,RBB.Count,
 RBB.Weight FROM `$RmLotDetatils` RLD LEFT JOIN $RmBillDetails RBD on RLD.RmLotID = RBD.LotID LEFT JOIN $RmBillBreakup as RBB on RBB.LotID=RLD.RmLotID 
 LEFT JOIN $RmPurchaseStations as RPS on RPS.PurchaseStationID= RLD.PurchaseStationID 
WHERE RLD.RmLotID = '$LotID'";

$HLGexec=$iconn->query($HLGQuery);
$center = $purchaseDate = $headlessCount = $headlessQty = $LOTNO = "";
if(!empty($HLGexec)){
	$HLGRep = $HLGexec->fetch_assoc();
	
	$purchaseDate = $HLGRep['PurchaseDate'];
	$center = $HLGRep['Location'];
	$headlessCount = (!empty($HLGRep['Count'])?$HLGRep['Count'].' c': '');
	$headlessQty = (!empty($HLGRep['Weight'])?$HLGRep['Weight'].' Kg(s)': '');
	$LOTNO = $HLGRep['LotNumber'];
}
$weight_sum = 0;
 $headlessGradingDetails = "SELECT PPC.GradingData, PPC.GradingDate FROM `PpcHLGradingDetails` PPC where LotID ='$LotID'";

$headlessExec = $iconn->query($headlessGradingDetails);
$headlessRes = $headlessExec->fetch_assoc();
$dd = strtotime($headlessRes['GradingDate']);
$dateF = getDate($dd);
$submission_date = date('d-m-Y h:i:s',strtotime($headlessRes['GradingDate']));


if(!empty($headlessRes['GradingData'])){
    $json_decode_data = json_decode($headlessRes['GradingData'], true);
    foreach($json_decode_data  as $value){
			 $weight_sum=$weight_sum + $value['NetWeight'];
					
	}
}



class MYPDF extends TCPDF
{


}
$pdf = new MYPDF();
$pdf->SetTitle($title);
$pdf = new TCPDF('P','mm','A4');

$pdf->AddPage();


/*if ($RowGradinglist['ProcessingStationID']==1) {
	//$COMPANY='JME';
	$logo = $_SERVER['DOCUMENT_ROOT']."/images/jme_logo.jpg";
	$footer = $_SERVER['DOCUMENT_ROOT']."/images/jme_footer.jpg";
}else{
	//$COMPANY='MOURYA';
	 $logo = $_SERVER['DOCUMENT_ROOT']."/images/ma_logo_editt.jpg";
	$footer = "";
}*/

//$pdf->Cell(0,20,$pdf->Image($logo, null, null,33.78),0,1,'L',false );

$pdf->SetFont('helvetica','BU',12);

$FONT_ARI='helvetica';
$HEIGHT=8;
$WIDHT_1='helvetica';

$header=10;
$sub_header=8;
$text=6;

$pdf->SetFont($FONT_ARI,'BU',$header);


$pdf->Cell(0 ,5,'HEADLESS TO GRADING REPORT',0,1,'C');
$pdf->Cell(130 ,5,'',0,1,'C');


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
$pdf->Cell(95,10,'Signature',1,1,'L',0);  


$pdf->Ln();
 
    ob_end_clean();
           
$pdf->Output();
?>