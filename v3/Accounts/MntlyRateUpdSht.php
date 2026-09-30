<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";
$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];
$TYPE=$_POST['TYPE'];
$UNIT=$_POST['UNIT'];
$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31  23:59:59';




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



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  

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

    $pdf->AddPage('P');  
	

	$pdf->Cell(0, 5, 'RATES LIST '.$month_name, 0, 1, 'C', 0, 'B', 0, false, 'T', 'M');

	$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

	 $pdf->SetFont('helvetica', 'B', '11');  			
	
    $pdf->SetFont('helvetica', '', 11);  

	
$table_data=getP_TABLE($iconn);
// $table_data=getP_TABLE($iconn,$from_date,$to_date);


$pdf->writeHTML($table_data);
  
 
//$file_name='Grading Report -'.$from_date.'-'.$to_date.'.pdf';

$pdf->Output('Rates List', 'I');


function getP_TABLE($iconn){

	
 	
	 
			 	  
$SQL_DATE="SELECT DISTINCT(RM_PRICE_DATE),ID FROM `ACC_PUR_UPDATE_HISTORY`"; 
$STMT_DATE=$iconn->query($SQL_DATE);
$i=0;
while($ROW_DATE = $STMT_DATE-> fetch_array()){

		  	
	 $UPDATE_DATE = date("d-m-Y", strtotime($ROW_DATE['RM_PRICE_DATE']));	

 $R_ID=$ROW_DATE['ID'];

 $sql_rates="SELECT * FROM `ACC_PURCHASE_RATES`  WHERE TARCK_ID =$R_ID"; 
	
	 $stmt_rates=$iconn->query($sql_rates);	 
			  
			  $width=30;
			  $width_s=$width/2;
			  
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			  <tr>
			  <td  align="center" width="'.$width.'%" ><font size="9">DATE:'.$UPDATE_DATE.'</font></td>

			  </tr>
			   <tr>  
                
				 <th  align="center" width="'.$width_s.'%" ><font size="9"> Count</font></th>
				 <th  align="center" width="'.$width_s.'%" ><font size="9">Rate</font></th>
   
               </tr>
			   
			  '; 
			  
	
		
		while($r_rates = $stmt_rates->fetch_array()){


				    $content .= 
					
                '<tr>
				 
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$r_rates['COUNT'].'</font></th>
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$r_rates['USD'].'</font></th>
				 

				 </tr>';

 
}
$i++;
if($i==3) break;	
}	
    

		$content .= '</table>';


	return $content;	
}
?>