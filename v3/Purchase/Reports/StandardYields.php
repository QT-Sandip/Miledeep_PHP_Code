<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php"); 

/*  $QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  $Type=$_GET['Type'];
  $Month=$_GET['Month']; 
  $Year=$_GET['Year']; 
  $PsID=$_GET['PsID']; 
  
 
  if($PsID!=''){
	  $PsID=$_GET['PsID'];	  
	  $Query="and PurchaseStationID='$PsID'";
  }else{
	   $Query="";
  }
*/
	

class MYPDF extends TCPDF {

	

	


   

    public function Header() {	

	 

        $image_file = K_PATH_IMAGES.'jme_logo.jpg';

        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', 'B', 15);

		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$this->Cell(0, 5, $CompanyName, 0,1, 'C', 0, '', 0, false, 'T', 'M');

		

    }
	
	
	  public function Footer() {
		
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$dt=$dateTime->format("d-m-Y  H:i:s");
        $this->SetFont('helvetica', 'I', 8);
		//$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->Cell(0, 10, 'Dated '.$dt .', Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		//$this->SetY(-5);
   }
   

}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  



	$pdf->SetCreator(PDF_CREATOR);  

    $pdf->SetTitle('Rm Yields ');  

    $pdf->SetHeaderData('', '', '', '');  

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  

    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  

    $pdf->SetDefaultMonospacedFont('helvetica');  

    $pdf->SetFooterMargin(45); 

	$pdf->SetHeaderMargin(15);	

    $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT,45);  

    $pdf->setPrintHeader(false);  

    $pdf->setPrintFooter(true);  

	

    $pdf->SetAutoPageBreak(TRUE, 40);  

    $pdf->SetFont('helvetica', 'B', 13);  

    $pdf->AddPage('P');  
	

	 

		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->Cell(0, 5, 'Standard Yields(Headless)', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		
	

	
	 $table_data=getP_TABLE($iconn);


	$pdf->writeHTML($table_data);
  
 
$file_name='StandardYieldsHl.pdf';
ob_clean();
$pdf->Output($file_name, 'd');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn){
	
	 $date_end=$date.' 23:59:59';
	 global $Query,$Month,$Year,$Type;
		 
		 if($Type==1){
			$Query= "month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
		 }else {
			$Query= "PurchaseDate between '$Month'  and '$Year'  $Query"; 
		 }
	 
	//  $SqlLotsCountWise="SELECT * FROM `RmStandardYields` WHERE (Type='HL' or Type='BT') 
	//  ORDER by Type Asc,LowerCount ASC ;"; 
	 
	 $SqlLotsCountWise="SELECT * FROM `RmStandardYields` WHERE (Type='HL' or Type='BT') and EffectiveDate  =(SELECT MAX(EffectiveDate ) FROM RmStandardYields) 
	 ORDER by Type Asc,LowerCount ASC";
	
      $SqlLotsCountWise;
      $StmtLotsCountWise=$iconn->query($SqlLotsCountWise);

	$content='';

		 
$PO_TMSP = strtotime($date);
$PURCHASE_DATE = date("d-m-Y", $PO_TMSP);

	$size=40;	

	$size1=60;	


	 $content .= '<table border="1" cellspacing="0" cellpadding="3">	

						

				<tr>  

           		

                <th  align="center" width="10%"><font size="11"><B>S No</B></font></th>
                <th  align="center" width="12%"><font size="11"><B>Species</B></font></th>
                <th  align="center" width="12%"><font size="11"><B>Form</B></font></th>
                <th  align="center" width="33%"><font size="11"><B>To</B></font></th>
                <th  align="center" width="33%"><font size="11"><B>Yield %</B></font></th>
				
				</tr>';


$C=0;
$SNo=1;
$WEIGHT_TOTAL=0;
while($RowLotCountWise = $StmtLotsCountWise->fetch_array()){ 

$CountStart=$RowLotCountWise['LowerCount'];
$CountEnd=$RowLotCountWise['UpperCount'];
$Yield=$RowLotCountWise['Yield'];
$Type=$RowLotCountWise['Type'];
if($Type=='HL'){
$Type='VM';	
}

	 
		$content .= '
			
				
				<tr>  

              

				<th  align="center"  width="10%"><font size="9">'.$SNo.'</font></th>
				<th  align="center"  width="12%"><font size="9">'.$Type.'</font></th>
				<th  align="center"  width="12%"><font size="9">'.$CountStart.'</font></th>
				<th  align="center"  width="33%"><font size="9">'.$CountEnd.'</font></th>
				<th  align="center" width="33%"><font size="9">'.$Yield.'</font></th>
				
				


               </tr>';
	
$C++;
$SNo++;
}


				

		$content .= '</table>';


	return $content;	

}

?>