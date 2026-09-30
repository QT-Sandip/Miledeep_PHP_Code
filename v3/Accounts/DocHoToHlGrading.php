<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
$LOT_ID=$_GET['LOT_ID'];
require_once('../tcpdf/tcpdf.php'); 

//$COMPANY_NAME='JAGADEESH MARINE EXPORTS';	
$COMPANY_NAME='MOURYA AQUEX PVT LTD';	

class MYPDF extends TCPDF {

    public function Header() {	
        $image_file = 'images/ma_logo.jpg';
        $this->Image($image_file, 15, 5, 25, 25, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', '', 6);
		 $this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 5, $COMPANY_NAME, 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'HEAD ON TO HEAD LESS REPORT', 0, 1, 'C', 0, '', 0, false, 'T', 'M');	
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		//$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('HO to HL Grading: '.$row['PO_NUM'].' - '.$row['PO_DATE']);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(0);	
    $pdf->SetMargins(10, 32, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$LOT_ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);
	
 
$file_name='Ds'.$row['PO_NUM'];
$pdf->Output($file_name, 'I');


			


function getDS_TABLE($iconn,$LOT_ID){
	
	
	
	      $sql_varity_list="SELECT * from PPC_HOSO_W_DATA where LOT_ID='$LOT_ID' and DATA_TYPE='Headless Grading'";
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
$HLGQuery = "SELECT 
im.PURCHASE_DATE,
 im.LOT_NO,
 im.UNIT_ID,
 im.AGENT_NAME,
 im.VARITY,
 im.DRIVER_NAME,
 im.COUNT,
 im.VEHICAL_NO,
 ppc.GRADING_COUNT,
 ppc.RATE,
 ppc.HON_QTY FROM 
`inputmaterial_details` im LEFT JOIN PPC_PRICING_AND_WEIGHT ppc on im.id = ppc.LOT_ID WHERE im.id = '$LOT_ID'";

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
	$headlessCount = $HLGRep['GRADING_COUNT'];
	$headlessQty = $HLGRep['HON_QTY'];
	$headlessRATE = $HLGRep['RATE'];
	$LOTNO = $HLGRep['LOT_NO'];
	$AGENT_NAME = $HLGRep['AGENT_NAME'];
	$VARITY = $HLGRep['VARITY'];
	$DRIVER_NAME = $HLGRep['DRIVER_NAME'];
	$VEHICAL_NO = $HLGRep['VEHICAL_NO'];
	$purchaseCount = $HLGRep['COUNT'];
}
$weight_sum = 0;
$headlessGradingDetails = "SELECT ppc.HOSO_W_DATA, ppc.SUBMISSION_DATE FROM `PPC_HOSO_W_DATA` ppc where LOT_ID = '$LOT_ID'";
$headlessExec = $iconn->query($headlessGradingDetails);
$headlessRes = $headlessExec->fetch_assoc();
$submission_date = date('d-m-Y', strtotime($headlessRes['SUBMISSION_DATE']));

$TMSPDate = $purchaseDate;
$PURDate = date("d-m-Y", strtotime($TMSPDate));


if(!empty($headlessRes['HOSO_W_DATA'])){
    $json_decode_data = json_decode($headlessRes['HOSO_W_DATA'], true);
    foreach($json_decode_data  as $value){
			 $weight_sum=$weight_sum + $value['NetWeight'];
					
	}	
}
	
		
	$content='';
	
	$size=10;	
	$size1=60;
	$size2=15;
	$size3=60;
	
	
	$EXP=explode(',',$QUANTITY_IN_KGS);
	$LBS=$EXP[0];
	$KGS=$EXP[1];
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="LEFT"   width="10%"><font size="10">SI.No:</font></th>
                <th  align="LEFT"  width="30%"><font size="10">Headon Material Received</font></th>
                <th  align="LEFT"  width="20%"><font size="10">Purchase Count</font></th>
                <th  align="LEFT"  width="20%"><font size="10">Quantity</font></th>
                <th  align="LEFT"  width="20%"><font size="10">HO-HL Doing</font></th>
                

				</tr>
				 <tr> 
				<th  align="LEFT"  width="10%"><font size="10"></font></th>
                <th  align="LEFT"  width="30%"><font size="10">Date: '.$PURDate.'     Time:</font></th>
				<th  align="LEFT"  width="20%"><font size="10">'.$headlessCount.'/'.$purchaseCount .'</font></th>
                <th  align="LEFT"  width="20%"><font size="10">'.$headlessQty.'</font></th>
				<th  align="LEFT"  width="20%"><font size="10"></font></th>
               
				  
           		

				</tr>
				
				<tr>

                <th  align="center"  width="100%"><font size="10">QUANTITY & COUNT INSPECTION </font></th>
                		
               </tr>
				
				<tr> '; 

				$count=2;
				$size=100/($count+1);           		
				$content .= '<th  align="left"  width="'.($size).'%"><font size="10">Count For</font></th>';				
				
				
				
				for( $i=0 ; $i < $count ; $i++){
				 $content .= '<th  align="center"  width="'.($size).'%"><font size="10">Kgs</font></th>';
				}
				
				$content .= '</tr>
				<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">Quality</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10">Time</font></th>';
				}
				
				$content .= '</tr>';
				
				$content .= '<tr>  
           		
				<th  align="left"  width="'.($size).'%"><font size="10">HO/HL</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10">HO/HL</font></th>';
				}
				$content .= '</tr>';
				
				$content .= '<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">Total Pieces</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10"></font></th>';
				}
				$content .= '</tr>';
           		
				
				
				
				$content .= '
				
				<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">Kg count</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10"></font></th>';
				}
				$content .= '</tr>';
				
				
				
				$content .= '
				
				<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">Peeling</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10"></font></th>';
				}
				$content .= '</tr>';
				
				$content .= '
				
				<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">DC</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10"></font></th>';
				}
				$content .= '</tr>';
				
				$content .= '
				
				<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">BS</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10"></font></th>';
				}
				$content .= '</tr>';
				
				
				$content .= '
				
				<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">HL% to HO</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10"></font></th>';
				}
				$content .= '</tr>';
				
				
				$content .= '
				
				<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">Checked by</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10"></font></th>';
				}
				$content .= '</tr>';
				
				
				$content .= '
				
				<tr>             		
				<th  align="left"  width="'.($size).'%"><font size="10">Dept.</font></th>';				
				for( $i=0 ; $i < $count ; $i++){
						 $content .= '<th  align="center"  width="'.($size).'%"><font size="10"></font></th>';
				}
				$content .= '</tr>';
				
				
				$content .= '
				
				<tr>             		
				<th  align="center"  width="100%"><font size="10">HO to HL Details</font></th>
				</tr>';
				
				
				
				
				
				
				
			   
			   $content .= '<tr><th  align="center" width="'.($size/2).'%"><font size="10">SL</font></th>'; 
			   
			    $content .= '<th  align="center"  width="'.($size/2).'%"><font size="10">HO Qty</font></th>';
			    $content .= '<th  align="center"  width="'.($size).'%"><font size="10">HL Qty</font></th>';
			    $content .= '<th  align="center"  width="'.($size).'%"><font size="10">Yield</font></th>';
				
                
           		
               $content .= '</tr>';
			   
	
			   
			   
			   $content .= '<tr><th  align="center" width="'.($size/2).'%"><font size="10">1</font></th>';

				$content .= '<th  align="center"  width="'.($size/2).'%"><font size="10">'.$headlessQty.'</font></th>';
			    $content .= '<th  align="center"  width="'.($size).'%"><font size="10">'.$weight_sum.'</font></th>';
			    $content .= '<th  align="center"  width="'.($size).'%"><font size="10">'.round(($weight_sum/$headlessQty)*100,2).'</font></th>';			   
				
               
           		
               $content .= '</tr>
				
			   
			    <tr>  
           		<th  align="LEFT"  width="50%"><font size="10"> For Processing Department             HeadLess Dept Sign:</font></th>
           		
               </tr>
			   
			   
                <tr>  
           		<th  align="LEFT"  width="15%"><font size="7"> Grade Count  </font></th>
                <th  align="LEFT" width="25%"><font size="7"> Quantity</font></th>
                <th  align="LEFT" width="60%"><font size="7">  Remarks: </font></th>
                
                
				</tr>
				
				 <tr>  
           		<th  align="LEFT"  width="15%"><font size="7">  </font></th>
                <th  align="LEFT" width="25%"><font size="7"> </font></th>
                <th  align="LEFT" width="60%"><font size="7">   </font></th>
                
                
				</tr>
				
				 <tr>  
           		<th  align="LEFT"  width="15%"><font size="7">  </font></th>
                <th  align="LEFT" width="25%"><font size="7"> </font></th>
                <th  align="LEFT" width="60%"><font size="7">  </font></th>
                
                
				</tr>
				
				 <tr>  
           		<th  align="LEFT"  width="15%"><font size="7"> Total </font></th>
                <th  align="LEFT" width="25%"><font size="7"> </font></th>
                <th  align="LEFT" width="60%"><font size="7"> Plant manager:          Grading Incharge: </font></th>
                
                
				</tr>
				
				</table>'; 
	
	return $content;	
	
}

?>