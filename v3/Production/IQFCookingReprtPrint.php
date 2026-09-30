<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";
 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

  //$date=$_GET['date']; 
$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
   

 //$month_name= date("F", strtotime($date)); 
 $month_name= date("F", strtotime($from_date)); 

require_once('../tcpdf/tcpdf.php'); 

class MYPDF extends TCPDF {

	public function Footer() {

      

		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);

		$this->SetY(-55);

   }

   

    public function Header() {	

	 

        $image_file = K_PATH_IMAGES.'jme_logo.jpg';

        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', 'B', 15);

		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$this->Cell(0, 5, 'SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		

    }
   

}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  



	$pdf->SetCreator(PDF_CREATOR);  

    $pdf->SetTitle('Cooking Report : ');  

    $pdf->SetHeaderData('', '', '', '');  

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  

    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  

    $pdf->SetDefaultMonospacedFont('helvetica');  

    $pdf->SetFooterMargin(15); 

	$pdf->SetHeaderMargin(15);	

    $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT,15);  

    $pdf->setPrintHeader(false);  

    $pdf->setPrintFooter(false);  

	

    $pdf->SetAutoPageBreak(TRUE,15);  

    $pdf->SetFont('helvetica', '', 11);  

    $pdf->AddPage('l');  
	

	 

		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->Cell(0, 5, 'SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'B', '11');  

		$pdf->Cell(0, 5, 'Cooking Report - '.$month_name, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');

		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');


	
    $pdf->SetFont('helvetica', '', 11);  

	
	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$B_TYPE,$from_date,$to_date);


	$pdf->writeHTML($table_data);
  
 
$file_name='Cooking -'.$from_date.'-'.$to_date.'.pdf';

$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$B_TYPE,$from_date,$to_date){
	global $B_TYPE;
	/* $date_end=$date.'-31 23:59:59';  
	 $date=$date.'-01'; */
	
	//$date_end=$date.' 23:59:59';  
	$date_end=$to_date.' 23:59:59';  
	
	
	if($B_TYPE == 1){ 
	//$B_TYPE == 1;
	$query.="and imd.id='0'";
}
 	
	 $sql_lots="SELECT * from inputmaterial_details as imd left join
	PPC_COOKED_DATA as SCK on imd.id=SCK.LOT_ID where SCK.PURCHASE_DATE 
	BETWEEN '$from_date' AND '$date_end'  order by SCK.PURCHASE_DATE asc"; 
			  
			   $sql_lots;
			  
			  $stmt_lots=$iconn->query($sql_lots);
			 	  
 
	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=120/15;
			  $width_s=$width-3;
			  $width_w=$width+5;
              
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>  
     
           		

                <th  align="center"  width="'.$width_s.'%"><font size="9"><B>S No.</B></font></th>              
				 <th  align="center" width="'.$width.'%" ><font size="9"><B> ID Code</B></font></th>
				 
				   <th  align="center"  width="'.$width.'%"><font size="9"><B>RM Date</B></font></th>			

				 <th  align="center" width="'.$width.'%" ><font size="9"><B>Item</B></font></th>

				<th  align="center"  width="'.$width.'%" ><font size="9"><B>Grade </B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9"><B>Sock in Quantity </B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9"><B>Sock Out Quantity </B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9"><B>Yeild </B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9"><B>Temperature </B></font></th>
               
                <th  align="center"  width="'.$width.'%" ><font size="9"><B>Net Qty </B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9"><B>Cooking Loss </B></font></th>
				
    
               </tr> 
			   
			  '; 
			  
	
		//	 
		while($r_lots = $stmt_lots->fetch_array()){
		
		
		
$sockinQty=$r_lots['SOCK_IN_QTY'];
$sockoutQty=$r_lots['SOCK_OUT_QTY'];
$PurchaseDate=$r_lots['PURCHASE_DATE'];
$CookedQty=$r_lots['NET_QTY'];
$Numerator=$sockoutQty-$sockinQty;
   
   $YEILD=ROUND((($Numerator/$sockinQty)*100),2);
   $CookingLoss=($CookedQty/$sockoutQty);
   $CookingLoss=ROUND((1-$CookingLoss),3);
    $CookingLoss= $CookingLoss*100;
  
	$Purchase_Date = date("d-m-Y", strtotime($PurchaseDate));

		
				    $content .= 
					
					'<tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$S_NO.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['LOT_NUMBER'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$Purchase_Date.'</font></th>

				<th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['VARIETY'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['GRADE'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['SOCK_IN_QTY'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['SOCK_OUT_QTY'].'</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="9">'.$YEILD.'%</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['TEMPERATURE'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['NET_QTY'].' Kg</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="9">'.$CookingLoss.' </font></th>
				 
				 </tr>';

 $S_NO++;			 
}
		  
    

		$content .= '</table>';


	return $content;	

}

?>