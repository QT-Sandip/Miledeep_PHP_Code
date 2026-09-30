<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


include ($cp."Includes/IncludePage.php");

include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php"); 


$ID=$_GET['id'];

 $RateID=urlDecode64($ID);

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


	
$table_data=getP_TABLE($iconn,$RateID);


$pdf->writeHTML($table_data);
  
 
ob_end_clean();
$pdf->Output('Rates List', 'I','.pdf');


function getP_TABLE($iconn,$RateID){
global $RmPurchaseRates,$RmPurchaseRatesHistory;
	
	
 $SqlRates="SELECT * FROM `RmPurchaseRates`  WHERE DateID =$RateID Order by ABS(RmCount) Asc" ;  
	$StmtRates=$iconn->query($SqlRates);
			
$SqlDate="SELECT * FROM `RmPurchaseRatesHistory` WHERE ID = $RateID "; 
$StmtDate=$iconn->query($SqlDate);
$RowDate = $StmtDate->fetch_array();
			  	
	$UpdatedDate = date("d-m-Y", strtotime($RowDate['RmPriceDate']));		  
			  $S_NO=1; 
			  $width=10;
			  $width_s=$width-5;
			  
			  $content .=' <h5 align="left" width="16" ><font size="9">DATE:'.$UpdatedDate.'</font></h5>';
			  
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


 while($AryRates[] = $StmtRates->fetch_array()){
			 }


						 			  							for($i=0;$i<=19;$i++){


				    $content .=
					
                '<tr>

				 <th  align="center" width="'.$width.'%"><font size="10"><b>'.$AryRates[$i]['RmCount'].' C</b></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$AryRates[$i]['RmPrice'].'</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="10"><b>'.$AryRates[$i+20]['RmCount'].'C</b></font></th>
                  <th  align="center" width="'.$width.'%"><font size="9">'.$AryRates[$i+20]['RmPrice'].'</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="10"><b>'.$AryRates[$i+40]['RmCount'].' C</b></font></th>

				 <th  align="center" width="'.$width.'%"><font size="9">'.$AryRates[$i+40]['RmPrice'].'</font></th>
				 
				 <th  align="center" width="'.$width.'%"><font size="10"><b>'.$AryRates[$i+60]['RmCount'].' C</b></font></th>

				 <th  align="center" width="'.$width.'%"><font size="9">'.$AryRates[$i+60]['RmPrice'].'</font></th>';
				 if($AryRates[$i+80]['RmPrice'] !=''){
				 $content .=' <th  align="center" width="'.$width.'%"><font size="10"><b>'.$AryRates[$i+80]['RmCount'].' C</b></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$AryRates[$i+80]['RmPrice'].'</font></th>';	 
				 }else{
					 $content .=' <th  align="center" width="'.$width.'%"><font size="10"><b>'.$AryRates[$i+80]['RmCount'].'</b></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$AryRates[$i+80]['RmPrice'].'</font></th>';	 
				  
				 }
				 
				 		$content .=' </tr>';				}

													  
		
		$content .= '</table>';
			  		  

	return $content;	
}
?>