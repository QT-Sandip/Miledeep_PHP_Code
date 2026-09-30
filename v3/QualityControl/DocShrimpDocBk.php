<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
$ID=$_GET['id'];
require_once('../tcpdf/tcpdf.php'); 
	
	
class MYPDF extends TCPDF {
	
    //Page header  
    public function Header() {
        // Logo
		
       
       		include "Constants.php";
        $image_file = $COMPANY_LOGO;
        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		
		
		
		
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
    $pdf->SetFooterMargin(45); 
	$pdf->SetHeaderMargin(155);	
    $pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT,45);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
//	$fontname = TCPDF_FONTS::addTTFfont('fonts/cambria.ttf', 'UTF-8', '', 32);

	
    $pdf->SetAutoPageBreak(TRUE, 40);  
    
    $pdf->AddPage();  
	
	//$fontname = $pdf->addTTFfont('/fonts/cambria.ttf', 'TrueTypeUnicode','', 32);
	
		$pdf->SetFont('ZapfDingbats','', 10);
		//$pdf->SetFont($fontname,'', 10);
	 //$str = iconv("UTF-8", "ISO-8859-1", "?");
 $str ="4";
	 $pdf->SetFont('helvetica', '', 11); 
   $pdf->SetFont($fontname,'', 10);	 
	$table_data=getPageOne($iconn,$ID,$str);
	//$table_data = utf8_encode($table_data);
	$pdf->writeHTML($table_data);
	
	$pdf->AddPage();  
	$table_data=getDS_TABLE($iconn,$ID,$str);
	$pdf->writeHTML($table_data);
	
 
  
$file_name='DS'.$row['PO_NUM'].'.pdf';
$pdf->Output($file_name, 'I');



function getPageOne($iconn,$PH_ID,$str){
	
			
			
			
			
			$sql_elisa_ph="select * from IH_ELSIA_PH_TEST_REPORT WHERE ID='$PH_ID'";
			$stmt_ph_rep=$iconn->query($sql_elisa_ph);
			$r_ph_row = $stmt_ph_rep->fetch_array();
	
	
		  
	
			$CODE_LIST=$r_ph_row['FARM_ID_POND_ID']; 
			$sql_code_list="select * from CODE_LIST_CREATE WHERE CODE_LIST_ID='$CODE_LIST'";
			$stmt_code_list=$iconn->query($sql_code_list);
			$r_code_list = $stmt_code_list->fetch_array();
			
			$INV_ID=$r_code_list['INV_ID'];
		 	$ID=$r_code_list['INV_ID'];
			
			include "Constants.php";
			
			//ECHO $PROCESSOR;
			
			 $sql_inv_data="select * from INVOICE_DATA WHERE ID='$INV_ID'";
			$stmt_inv=$iconn->query($sql_inv_data);
			$inv_row = $stmt_inv->fetch_array();
			
		/*	if($inv_row['NAME_ADD_OF_EXPORTER'] == $UNIT_1){
				$EXPORTER= $UNIT_1 .',' .$UNIT_1_ADD;
			}else{
				$EXPORTER= $UNIT_2 .',' .$UNIT_2_ADD;
			}
			
			if($inv_row['NAME_ADD_OF_PROCESSOR'] == $UNIT_1){
				$PROCESSOR= $UNIT_1 .',' .$UNIT_1_ADD;
			}else{
				$PROCESSOR= $UNIT_2 .',' .$UNIT_2_ADD;
			}
			
			*/
	
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
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">	
	 

				<tr style="background-color:#D3D3D3">  
           		<th  align="center"  width="100%"><font size="'.$header.'"><b  >  Catch Certificate for Traceability- Harvest and Landing Receipt </b> </font>
				
				</th>
               </tr>	 

	            <tr>  
           		<th  align="LEFT"  width="45%"><font size="'.$text.'"><b>(1) Production Document Identifier*:</b></font>
				<br><font align="center" size="14"> <b>'.$tracibility.' </b> </font></th>
                <th  align="LEFT" width="55%"><font face="Arial" size="'.$text.'"> <b> (x)Wild Capture Fishery (Complete Section 2) <br><br><br> 
				(v)Farm Raised(Complete Section 3) </b> </font></th>
                

				</tr>
				
				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"> <b>(2) Complete this Section for Wild
				Capture Fish Products ... NOT APPLICABLE </b> </font></th>
               </tr>	
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"><b>Flag state of Vessel </b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"><b>Name of Harvesting Vessel:**</b></font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"><b>Vessel Registration or Documentation Number:**</b></font></th>


				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> <b> Fishing Permit Number** </b> </font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Fishing / catch Area: </b> </font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b> Fishing Gear: </b> </font></th>
                

				</tr>
				
				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"> <b> (3) Complete this Section for Farm
				raised Fish Products </b> </font></th>
               </tr>	
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"><b> State of Jurisdriction: </b></font>
				<br><font align="center" size="14"><b>INDIA</b></font>
				</th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Facility License or Authorization**: </b> </font>
				<br><font align="center" size="14"><b>'.$tracibility.' </b></font>
				</th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b>Name and Location of Aquaculture Facility**: </b><br><b>'.$farm_location.'</b></font></th>
                

				</tr>

				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"> <b> (4)Complete this section for receipt of Fishery
				Products: </b></font></th>
               </tr>
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> <b>Name of Processor: <br>'.$PROCESSOR.' <br> '.$PROCESSOR_ADD.'</b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Telephone: '.$PROCESSOR_PHONE.'<br><br> 
				Email:'.$PROCESSOR_EMAIL.' </b> </font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b> Business Address: <br>'.$PROCESSOR.'<br>'.$PROCESSOR_ADD.'</b> </font></th>
                

				</tr>    
				
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> <b>Receiving Facility :<br>'.$PROCESSOR.'<br> '.$PROCESSOR_ADD.'</b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b>Date of Landing: </b></font> 
				<br><font align="center" size="14"><b>'.$date_of_landing.' </b></font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b> Delivery Location: <br>'.$PROCESSOR.'<br> '.$PROCESSOR_ADD.'</b></font></th>
                

				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> <b> Species of fish (ASFIS 3 alpha code): </b></font>

				
				<br><font align="center" size="14"><b>2280100111</b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Delivered Weight: </b></font>
				<br><font align="center" size="14"><b>'.$weight.' </b></font></th>
                <th  align="LEFT" width="40%"><font size="'.$text.'"> <b> Product Form: </b></font>
				<br><font align="center" size="14"><b>'.$product_form.' </b></font></th>
                

				</tr>';
				

				
			$content .= '</table>'; 
	
	return $content;	
	
}

function getDS_TABLE($iconn,$ID){
	
	
			$sql_elisa_ph="select * from IH_ELSIA_PH_TEST_REPORT WHERE ID='$ID'";
			$stmt_ph_rep=$iconn->query($sql_elisa_ph);
			$r_ph_row = $stmt_ph_rep->fetch_array();
			
			
	
	
			$CODE_LIST=$r_ph_row['FARM_ID_POND_ID']; 
			$sql_code_list="select * from CODE_LIST_CREATE WHERE CODE_LIST_ID='$CODE_LIST'";
			//$sql_code_list="select * from CODE_LIST_CREATE WHERE FARM_REG_ID='AP028222/001' and INV_ID=74";
			$stmt_code_list=$iconn->query($sql_code_list);
			$r_code_list = $stmt_code_list->fetch_array();
			
			$ID=$r_code_list['INV_ID'];
			
			include "Constants.php";
			
			$PO_ID=$r_code_list['FULL_NAME'];
			$sql_PO="select * from PO_VARIETY_LIST WHERE ID='$PO_ID'";
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
			
			
			$sql_code_list="select * from CODE_LIST_CREATE WHERE FARM_REG_ID='$tracibility' and INV_ID='$INV_ID'";
			$stmt_code_list=$iconn->query($sql_code_list);
			while($r_code_list = $stmt_code_list->fetch_array()){
				$T_O_MCS=$T_O_MCS + $r_code_list['T_O_MCS'];
				
				$PO_ID=$r_code_list['FULL_NAME'];
			 $sql_PO="select * from PO_VARIETY_LIST WHERE ID='$PO_ID'";
			$stmt_PO=$iconn->query($sql_PO);
			$r_PO = $stmt_PO->fetch_array();
				
				if($product_op_form==''){
					$product_op_form=$r_PO['FULL_NAME'];
				}else{
					$product_op_form=$product_op_form . "<br>" . $r_PO['FULL_NAME'];
				}
				
			}
			
			
			$INV_ID=$r_code_list['INV_ID'];
			
			 $sql_inv_data="select * from INVOICE_DATA WHERE ID='$INV_ID'";
			$stmt_inv=$iconn->query($sql_inv_data);
			$inv_row = $stmt_inv->fetch_array();
			
			/*if($inv_row['NAME_ADD_OF_EXPORTER'] == $UNIT_1){
				$EXPORTER= $UNIT_1 .',' .$UNIT_1_ADD;
			}else{
				$EXPORTER= $UNIT_2 .',' .$UNIT_2_ADD;
			}
			
			if($inv_row['NAME_ADD_OF_PROCESSOR'] == $UNIT_1){
				$PROCESSOR= $UNIT_1_ADD;
				$APPROVAL_NO=$UNIT1_APPROVAL_NO;
				$CONTACT=$UNIT1_CONTACT;
			}else{
				$PROCESSOR= $UNIT_2_ADD;
				$APPROVAL_NO=$UNIT2_APPROVAL_NO;
				$CONTACT=$UNIT1_CONTACT;
			}
			
			*/
 
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">	
	 

				<tr  style="background-color:#D3D3D3" >  
           		<th  align="center"  width="100%"><font size="'.$header.'"> <b> Reprocessing Certificate for Traceability- 
				Primary or Secondary Processing</b> </font></th>
               </tr>

<tr>  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"><b>(x)Wild Harvest or (v)Farm Raised </b></font></th>
               </tr>			   

	            <tr>  
           		<th  align="LEFT"  width="50%"><font size="'.$text.'"> <b>Unique Catch or Production Document Identifier: </b></font>
				<br><font align="center" size="14"><b>'.$tracibility.' </b></font>
				</th>
                <th  align="LEFT" width="50%"><font size="'.$text.'"> <b> Company Product Received From: <br>'. $farm_location.' </b></font></th>
                
				</tr>
				
				  <tr>  
           		<th  align="LEFT"  width="25%"><font size="'.$text.'"> <b> Processor Name: <br><br> '.$PROCESSOR.' </b> </font></th>
                <th  align="LEFT" width="25%"><font size="'.$text.'"> <b> Processor Address: <br>'.$PROCESSOR_ADD.'</b> </font></th>
				<th  align="LEFT" width="23%"><font size="'.$text.'"><b> Processor Approval Number: </b></font>
<br><font align="center" size="14"><b>'.$PROCESSOR_APP_NUM.'</b></font></th>
				<th  align="LEFT" width="27%"><font size="'.$text.'"> <b> Telephone :'.$PROCESSOR_PHONE.' <br> 
				Email:'.$PROCESSOR_EMAIL.'  </b></font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="25%"><font size="'.$text.'"> <b>Commodity Scientific Name: </b></font>
				<br><font align="center" size="'.$header.'"><b>PENAEUS VANNAMEI  </b></font></th>
				
                <th  align="left" width="48%"><font size="'.$text.'"> <b>Commodity Market Name: </b></font>
				<br><font align="center" size="'.$header.'"><b>SHRIMPS </b></font></th>
                
                <th  align="LEFT" width="27%"><font size="'.$text.'"><b> Product ASFS#:</b></font>
				<br><font align="center" size="'.$header.'"><b>2280100111 </b></font></th>
                

				</tr>
			
				
				<tr>  
           		<th  align="LEFT"  width="25%"><font size="'.$text.'"> <b> Received Weight KGS: </b> </font>
				<br><font align="center" size="14"><b>'.$weight.' </b></font></th>
                <th  align="LEFT" width="25%"><font size="'.$text.'"> <b> Received LOT Identifier(s)A: </b> </font>
				<br><font align="center" size="14"><b>'.$tracibility.'  </b></font></th>
                <th  align="LEFT" width="50%"><font size="'.$text.'"> <b> Received Product Form: </b></font>
				<br><font align="center" size="14"><b>'.$product_form.'  </b></font></th>


				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="25%"><font size="'.$text.'"> <b> Processed Weight KGS: </b></font>
				<br><font align="center" size="14"><b>'.$weight.' </b></font></th>
                <th  align="center" width="25%"><font size="'.$text.'"> <b> Finished Product Lot Identifier(s)a: </b></font>
				<br><font align="center" size="14"><b>'.$tracibility.'  </b></font></th>
                <th  align="LEFT" width="50%"><font size="'.$text.'"> <b>Finished Product Form: <br><br> </b> </font>
				<br><font align="center" size="'.$text.'"><b>'.$product_op_form.'  </b></font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> <b> Finished Product Weight KGS: </b> </font>
				<br><font align="center" size="14"><b>'.$finalweight.' </b></font></th>
               </tr>	
				
				<tr>  
           		<th  align="LEFT"  width="38%"><font size="'.$text.'"> <b> Production Date(s): </b></font>
				<br><font align="center" size="14"><b>'.$date_of_landing.' </b></font></th>
                <th  align="LEFT" width="32%"><font size="'.$text.'"><b> Total No Of Packages: </b></font>
				<br><font align="center" size="14"><b>'.$T_O_MCS.' </b></font></th>
                <th  align="LEFT" width="30%"><font size="'.$text.'"> <b> Type of Packaging: </b></font>
				<br><font align="center" size="14"><b>'.$PACKING.' </b></font></th>
                

				</tr>';


				
			$content .= '</table>'; 
	
	return $content;	
	
}


?>