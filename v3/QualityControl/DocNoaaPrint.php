<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
$PoID=$_GET['PoID'];
require_once('../tcpdf/tcpdf.php'); 
	
	
class MYPDF extends TCPDF {
	
    //Page header  
    public function Header() {
        // Logo
		
       
       		include "Constants.php";
        //$image_file = images/simp_noaa_logo;
        $this->Image('images/noaa_logo.jpg', 10, 5, 25, 20, 'JPG', 'NOAA', 'T', false, 400, 'R',
		false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
       // $this->Cell(0, 15, 'SEAFOOD IMPORT MONITERING PROGRAMME', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		
		
		
		
    }

   
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
   }
}


	    $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  

	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
   $pdf->SetTitle('SIMP Docs Note: '.$row['PO_NUM'].' - '.$row['PO_DATE']);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(40); 
	$pdf->SetHeaderMargin(10);	
    $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT,45);  
    $pdf->setPrintHeader(TRUE);  
    $pdf->setPrintFooter(false);  
//	$fontname = TCPDF_FONTS::addTTFfont('fonts/cambria.ttf', 'UTF-8', '', 32);

	
    //$pdf->SetAutoPageBreak(TRUE, 40);  
    
    $pdf->AddPage();  
	
	//$fontname = $pdf->addTTFfont('/fonts/cambria.ttf', 'TrueTypeUnicode','', 32);
	
		$pdf->SetFont('ZapfDingbats','', 10);
		//$pdf->SetFont($fontname,'', 10);
	 //$str = iconv("UTF-8", "ISO-8859-1", "?");
 $str ="4";
	 $pdf->SetFont('helvetica', '', 11); 
     $pdf->SetFont($fontname,'B', 15);	 
     $pdf->Cell(0 ,5,'NOAA',0,1,'C');
	      $pdf->SetFont($fontname,'BU', 15);	 

     $pdf->Cell(0 ,15,'MANDATORY SIMP US ENTRY DATA LIST',0,1,'C');
	 $pdf->SetFont($fontname,'', 10);	
	  $pdf->SetFont($FONT_ARI,'',$header);
	   $table_data=getPageOne($iconn,$ID,$str);
	   $table_data1=explode("&",$table_data[1]);
	   
	        $pdf->SetFont($fontname,'B', 10);	 

		/*$pdf->Cell(150 ,5,' PO NUMBER : '.$table_data1[0],0,1,'L');
		$pdf->Cell(150 ,5,' PO DATE : '.$table_data1[1],0,0,'L');
		$pdf->Cell(150 ,5,'CONTAINER NUMBER : '.$table_data[2],0,1,'L');*/
	
	 $pdf->writeHTML($table_data[0]);
	
	//$pdf->AddPage();  
	 $pdf->SetFont($fontname,'B', 15);	 
   //$pdf->Cell(0 ,15,'NOAA',0,1,'C');
	$pdf->SetFont($fontname,'', 10);	
	$table_data=getDS_TABLE($iconn,$ID,$str);
	$pdf->writeHTML($table_data);
	
 
  
$file_name='DS'.$row['PO_NUM'].'.pdf';

$pdf->SetFont($FONT_ARI,'B',15);
$pdf->Cell(0 ,2,'**ALL FIELDS ARE MANDATORY AND MUST BE FILLED OUT FOR A NOAA ENTRY TO BE PROCESSED',0,1,'L');
$pdf->Cell(0 ,3,'**COMPLETE ONE PAGE PER COMMERCIAL INVOICE LINE',0,1,'L');
//$pdf->Ln();

//$pdf->Cell(0 ,0,'',0,0,'C');
$pdf->SetFont($FONT_ARI,'',10);

$pdf->Cell(0 ,8,'****'.$VERIFIED_BY,0,1,'C');

$pdf->Cell(0 ,3,'Q.C, TECHNOLOGIST',0,1,'C');

ob_clean();

$pdf->Output($file_name, 'I');



function getPageOne($iconn,$PH_ID,$str){
	
	global $TblQcCodeList;		
	global $TblQcPhAbReports;		
	global $TblScrmPoData;		
			
			
			
			$sql_elisa_ph="select * from $TblQcPhAbReports WHERE ID='$PH_ID'";
			$stmt_ph_rep=$iconn->query($sql_elisa_ph);
			$r_ph_row = $stmt_ph_rep->fetch_array();
	
	
		  
	
			$CODE_LIST=$r_ph_row['FARM_ID_POND_ID']; 
			$sql_code_list="select * from $TblQcCodeList WHERE CLID='$CODE_LIST'";
			$stmt_code_list=$iconn->query($sql_code_list);
			$r_code_list = $stmt_code_list->fetch_array();
			
			$INV_ID=$r_code_list['INV_ID'];
		 	$ID=$r_code_list['INV_ID'];
			
			include "Constants.php";
			
			//ECHO $PROCESSOR;

			 $sql_inv_data="select * from $TblScrmPoData WHERE PoID='$INV_ID'";
			$stmt_inv=$iconn->query($sql_inv_data);
			$inv_row = $stmt_inv->fetch_array();
			
			$CONSIGNMENT_EXPORTED=$row['CONSIGNMENT_EXPORTED'];
				
$consign_exp = explode(',', $inv_row['CONSIGNMENT_EXPORTED_TO']);

//PRINT_R($consign_exp); 

  $CONSIGNMENT_NUMBER= $consign_exp[1];



	$content='';
	
	$header=12 ;	
	$text=8;	
	
 //$str = iconv("UTF-8", "ISO-8859-1", "?");
 //$str = ?;
 
 $weight=$r_ph_row['QUANTITY_IN_KGS'];
 $date_of_landing=getDayCodeDate($r_code_list['DAY_CODE']);
 //$weight=$r_code_list['HEAD_ON_WEIGHT'];
  $finalweight=$r_ph_row['FINISHED_WEIGHT'];
 $tracibility=$r_code_list['FARM_REG_ID'];
 $farm_location=$r_code_list['NAME_OF_FARMER'].''.$r_code_list['ADD_OF_FARMER'];
 $product_form='HEAD ON SHRIMP';
 

  


 $PO_Date = date("d-m-Y", strtotime($inv_row['PO_DATE']));
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">	
	  

				<tr style="background-color:#D3D3D3">  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b  >Field Description </b> </font></TH>
           		<th  align="center"  width="50%"><font size="'.$header.'"><b  >Entry Data </b> </font>
				
				</th>
               </tr>	 

	            <tr>  
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b><u>IFTP#</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 

				</tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Container #</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 
     </tr>	
				
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Species ALPHA Code</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>PNV</b> </font></TH>
 


				</tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Shrimp Size</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 
 

				</tr>
				<TR>
				
				<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Harvest Code- Farm Raised- show HBA</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 
   </tr>	
				
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Country of Harvest</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 


				</tr>

				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Area of Harvest</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 
    

				</tr>    
				
				
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Date of Harvest(MONTH/DATE/YEAR)</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 


				</tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Form of Product at Time of Lading/Trans-shipment</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>HEAD ON SHRIMPS </b> </font></TH>
 


				</tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Harvest Weight - specify KGS OR Lbs</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 


				</tr>
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><u>Farm Name & City Name only</u></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 


				</tr>
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><U>Point of Contact at Farm</U></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 


				</tr>
				<tr>  
           		<th  align="center"  width="50%"><font size="'.$header.'"><b><U>Point of Contact at Farm - email address or phone #</U></b> </font></TH>
           		 <th  align="center"  width="50%"><font size="'.$header.'"><b>**** </b> </font></TH>
 


				</tr>
				
								<tr style="background-color:#D3D3D3">  
								<th  align="center"  width="100%"><font size="'.$header.'"> </font></TH>
 
</TR>
				
				';

	//$pdf->writeHTML($content);

				
			$content .= '</table>'; 
			
	
	$data= array();
	$data[]=$content;
	
	$data[]=$inv_row['PO_NUM'].'& '.$PO_Date;
	$data[]=$CONSIGNMENT_NUMBER;
					

	
	return $data;
	
	
}

function getDS_TABLE($iconn,$ID){
	
	global $TblQcCodeList;
	global $TblQcPhAbReports;
	global $TblQcPoVarityList;
	
			$sql_elisa_ph="select * from $TblQcPhAbReports WHERE ID='$ID'";
			$stmt_ph_rep=$iconn->query($sql_elisa_ph);
			$r_ph_row = $stmt_ph_rep->fetch_array();
			
			
	
	
			$CODE_LIST=$r_ph_row['FARM_ID_POND_ID']; 
			$sql_code_list="select * from $TblQcCodeList WHERE CLID='$CODE_LIST'";
			//$sql_code_list="select * from CODE_LIST_CREATE WHERE FARM_REG_ID='AP028222/001' and INV_ID=74";
			$stmt_code_list=$iconn->query($sql_code_list);
			$r_code_list = $stmt_code_list->fetch_array();
			
			$ID=$r_code_list['INV_ID'];
			
			include "Constants.php";
			
			$PO_ID=$r_code_list['FULL_NAME'];
			$sql_PO="select * from $TblQcPoVarityList WHERE InvID='$PO_ID'";
			$stmt_PO=$iconn->query($sql_PO);
			$r_PO = $stmt_PO->fetch_array();

	$content='';
	$size=40;	
	$size1=60;	
 	$header=12;	
	$text=8;	  
	
	
	
	
 $date_of_landing=getDayCodeDate($r_code_list['DAY_CODE']);
 $weight=$r_ph_row['QUANTITY_IN_KGS'];
 $tracibility=$r_code_list['FARM_REG_ID'];
  $finalweight=$r_ph_row['FINISHED_WEIGHT'] ." KGS";
 $farm_location=$r_code_list['NAME_OF_FARMER'].''.$r_code_list['ADD_OF_FARMER'];
 $product_form='HEAD ON SHRIMP';
 $product_op_form=$r_PO['FULL_NAME'];
 $PACKING=$r_code_list['PACKING'];
 $T_O_MCS=$r_code_list['T_O_MCS'];
 $INV_ID=$r_code_list['INV_ID'];
 
 $T_O_MCS=0;
 $product_op_form='';
 $PACKING='';
			
			
			 $sql_code_list="select * from $TblQcCodeList WHERE TracebilityID='$tracibility' and InvID='$INV_ID'";
			$stmt_code_list=$iconn->query($sql_code_list);
			while($r_code_list = $stmt_code_list->fetch_array()){
				$T_O_MCS=$T_O_MCS + $r_code_list['T_O_MCS'];
				
				if($PACKING==''){
					$PACKING=$r_code_list['PACKING'];
				}else{
					$PACKING=$PACKING . "," . $r_code_list['PACKING'];
				}
				
				$PO_ID=$r_code_list['FULL_NAME'];
			 $sql_PO="select * from $TblQcPoVarityList WHERE InvID='$PO_ID'";
			$stmt_PO=$iconn->query($sql_PO);
			$r_PO = $stmt_PO->fetch_array();
				
				if($product_op_form==''){
					$product_op_form=$r_PO['FULL_NAME'];
				}else{
					$product_op_form=$product_op_form . "<br>" . $r_PO['FULL_NAME'];
				}
				
				
				
			}
			
 
 
	 
}

	 


?>