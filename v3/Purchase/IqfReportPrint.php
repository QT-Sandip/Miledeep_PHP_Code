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

		$this->Cell(0, 5, 'JAGADEESH MARINE EXPORTS', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		

    }
   

}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  



	$pdf->SetCreator(PDF_CREATOR);  

    $pdf->SetTitle('IQF PACKING Report : ');  

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

		$pdf->Cell(0, 5, 'JAGADEESH MARINE EXPORTS', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'B', '11');  

		$pdf->Cell(0, 5, 'IQF PACKING Report - '.$month_name, 0, 1, 'C', 0, 'B', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');

		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');


	
    $pdf->SetFont('helvetica', '', 11);  

	
	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$B_TYPE,$from_date,$to_date);


	$pdf->writeHTML($table_data);
  
 
$file_name='IQF PACKING -'.$from_date.'-'.$to_date.'.pdf';

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
	CS_PALLET_INPUT_DATA as SCK on imd.id=SCK.PURCH_LOT_NUM where SCK.DATE_TIME 
	BETWEEN '$from_date' AND '$date_end'  order by PURCHASE_DATE,LOT_NO asc"; 
			  
			   $sql_lots;
			  
			  $stmt_lots=$iconn->query($sql_lots);
			 	  
 
	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=120/15;
			  $width_s=$width-5;
			  $width_w=$width+5;
              
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>  

           		

                <th  align="center"  width="'.$width_s.'%"><font size="9">SI No.</font></th>              
				 <th  align="center" width="'.$width.'%" ><font size="9"> TUB NO</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9"> ID NO</font></th>
				   <th  align="center"  width="'.$width.'%"><font size="9">RM DATE</font></th>			
				   <th  align="center"  width="'.$width.'%"><font size="9">IQF DATE</font></th>			
				 <th  align="center" width="'.$width.'%" ><font size="9">ITEM</font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9">COUNT </font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9">GRADE </font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9">BRAND </font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9">Cartons </font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9">Packing </font></th>
               
                <th  align="center"  width="'.$width.'%" ><font size="9">Net Qty </font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9">Remarks </font></th>
				
    
               </tr> 
			   
			  '; 
			  
	
		//	 
		while($r_lots = $stmt_lots->fetch_array()){
			$W=$r_lots['CARTONS_NUMBER']*$r_lots['TOTAL_SLABS'] * $r_lots['SLAB_WEIGHT'];
		
		if($r_lots['UOM']=='Lbs'){
		$N_W_KG=round(( $W * 454.4)/1000,2);
		}else if($r_lots['UOM']=='Grams'){
		$N_W_KG=round(($W )/1000,2);
		}else if($r_lots['UOM']=='Kgs'){
		$N_W_KG=round(($W ),2);
		}
		
		$PURCHZ_DATE=$r_lots['PURCHASE_DATE'];
		$PURCHASE_DT = date("d-m-Y", strtotime($PURCHZ_DATE));
		
				    $content .= 
					
					'<tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$S_NO.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['TUB_NO'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['LOT_NO'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$PURCHASE_DT.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['DATE_TIME'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['FORM'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['COUNT'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['GRADES'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['BRAND'].'</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['CARTONS_NUMBER'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['PACKING_STYLE'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['NET_WT'].' Kg</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['REMARKS'].' </font></th>
				 
				 </tr>';

 $S_NO++;			 
}
		  
    

		$content .= '</table>';


	return $content;	

}

?>