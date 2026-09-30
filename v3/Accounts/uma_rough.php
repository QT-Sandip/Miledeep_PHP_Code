<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";

$ID=$_GET['id'];

 $RATE_ID=base64_url_decode($ID);



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
		$this->Cell(0, 5, ' ', 0,1, 'C', 0, '', 0, false, 'T', 'M');

    }
   

}



    $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  //uma  changing portrait  into landscape
   

	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('RATES LIST : ');  
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

    $pdf->AddPage('L');  //uma changing portrait  into landscape
	

	$pdf->Cell(0, 5, 'RATES LIST '.$month_name, 0, 1, 'C', 0, 'B', 0, false, 'T', 'M');

	$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

	 $pdf->SetFont('helvetica', 'B', '11');  			
	
    $pdf->SetFont('helvetica', '', 11);  



$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);//uma


	
$table_data=getP_TABLE($iconn,$RATE_ID);
// $table_data=getP_TABLE($iconn,$from_date,$to_date);


$pdf->writeHTML($table_data);
  
 
//$file_name='Grading Report -'.$from_date.'-'.$to_date.'.pdf';

$pdf->Output('Rates List', 'I');


function getP_TABLE($iconn,$RATE_ID){

	
 	
	 
	 $sql_count="SELECT count(*) as count FROM `ACC_PURCHASE_RATES` WHERE TARCK_ID =$RATE_ID"; 
	$stmt_count=$iconn->query($sql_count);
	$row_count=$stmt_count->fetch_array();
	$range=$row_count['count'];

$sql_rates="SELECT * FROM `ACC_PURCHASE_RATES`  WHERE TARCK_ID =$RATE_ID"; 
	$stmt_rates=$iconn->query($sql_rates);
			
$SQL_DATE="SELECT * FROM `ACC_PUR_UPDATE_HISTORY` WHERE ID = $RATE_ID "; 
$STMT_DATE=$iconn->query($SQL_DATE);
$ROW_DATE = $STMT_DATE->fetch_array();
			  	
	$UPDATE_DATE = date("d-m-Y", strtotime($ROW_DATE['RM_PRICE_DATE']));		  
			  $S_NO=1; 
			  $width=10;
			  $width_s=$width-5;
			  
			  $content .=' <h5 align="left" width="16" ><font size="9">DATE:'.$UPDATE_DATE.'</font></h5>';
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3"  style="float:right nobr="true"">
			  
			   <tr>  

           		 <th  align="center" width="'.$width.'%" ><font size="9"><b> Count </b></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">Rate</font></th>
               <th  align="center" width="'.$width.'%" ><font size="9"><b> Count </b></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">Rate</font></th>
               <th  align="center" width="'.$width.'%" ><font size="9"><b> Count </b></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">Rate</font></th>
               <th  align="center" width="'.$width.'%" ><font size="9"><b> Count </b></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">Rate</font></th>
				  <th  align="center" width="'.$width.'%" ><font size="9"><b> Count </b></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">Rate</font></th>
               </tr>
			   
			  '; 

			
			 
 			 //uma changing page  format


 while($r_rates[] = $stmt_rates->fetch_array()){
			 }


						 			  							for($i=0;$i<=19;$i++){


				    $content .=
					
                '<tr>

				 <th  align="center" width="'.$width.'%"><font size="10"><b>'.$r_rates[$i]['COUNT'].' C</b></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_rates[$i]['USD'].'</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="10"><b>'.$r_rates[$i+20]['COUNT'].'C</b></font></th>
                  <th  align="center" width="'.$width.'%"><font size="9">'.$r_rates[$i+20]['USD'].'</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="10"><b>'.$r_rates[$i+40]['COUNT'].' C</b></font></th>

				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_rates[$i+40]['USD'].'</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="10"><b>'.$r_rates[$i+60]['COUNT'].' C</b></font></th>

				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_rates[$i+60]['USD'].'</font></th>';
				 if($r_rates[$i+80]['USD'] !=''){
				 $content .=' <th  align="center" width="'.$width.'%"><font size="10"><b>'.$r_rates[$i+80]['COUNT'].' C</b></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_rates[$i+80]['USD'].'</font></th>';	 
				 }else{
					 $content .=' <th  align="center" width="'.$width.'%"><font size="10"><b>'.$r_rates[$i+80]['COUNT'].'</b></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_rates[$i+80]['USD'].'</font></th>';	 
				  
				 }
				 
				 
				 

				$content .=' </tr>';
				 
				 	
													
															

													}

													  
		
		$content .= '</table>';
			  		  

	return $content;	
}
?>