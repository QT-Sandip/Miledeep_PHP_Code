<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";


     
$ID=$_GET['id'];
include "Constants.php";
require_once('../tcpdf/tcpdf.php'); 

	$Exporter=$PROCESSOR;
	
class MYPDF extends TCPDF {

    public function Header() {	
        $image_file = K_PATH_IMAGES.'jme_logo.jpg';
        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, $Exporter, 0,1, 'C', 0, '', 0, false, 'T', 'M');
		//$this->Cell(0, 5, 'DRAFT', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'Finished Product Inspection', 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
		$this->Cell(0, 5, 'IN/CCN/AFLFOOD/2019/00224', 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Inspection Report');  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(45); 
	$pdf->SetHeaderMargin(15);	
    $pdf->SetMargins(5, 15, 5,15);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 15);  
    $pdf->SetFont('TIMES', '', 11);  
    $pdf->AddPage();  
	
	    $pdf->SetFont('TIMES', 'B', 15);  

	    $pdf->Cell(0, 3, $Exporter, 0,1, 'C', 0, '', 0, false, 'T', 'M');
		//$pdf->Cell(0, 3, 'DRAFT', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		    $pdf->SetFont('TIMES', '', 11);  

		$pdf->Cell(0, 3, 'Finished Product Inspection', 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
		

	$table_data=getDS_TABLE($iconn,$ID);
	$pdf->writeHTML($table_data);
	
   $pdf->AddPage(); 
   
   
   $ID=$_GET['id'];

$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `DAY_CODE` ASC";
$stmt_code=$iconn->query($sql_code_list);
$varity_ids=array();

$S_W_T_MCS=0;




while($row=$stmt_code->fetch_array()){	

$S_W_T_MCS=$S_W_T_MCS+$row['T_O_MCS'];

$FULL_NAME=$row['FULL_NAME'];	
if(in_array($FULL_NAME, $varity_ids)){					
					}else{
						 $FULL_NAME;
						$varity_ids[]=$FULL_NAME;
						}	
}


$TOTAL_SAMPLES =(sqrt($S_W_T_MCS)+1)/2;
$TOTAL_SAMPLES =round($TOTAL_SAMPLES);
   
   $table_data=getDS_TABLE2($iconn,$ID,$TOTAL_SAMPLES,$S_W_T_MCS);
	$pdf->writeHTML($table_data);
	
	
	 $limit = 10; 
	 $sql = "SELECT COUNT(*) FROM INSP_DATA where INV_ID='$ID'  and FROZEN_WEIGHT!=''"; 
	$stmt_sql=$iconn->query($sql);	
     $rs_result = $stmt_sql->fetch_array();   
     
        $total_records = $rs_result[0];   

		$total_pages = ceil($total_records / $limit);   
        
		$start_from=0;		
			   
        for ($i=1; $i<=$total_pages; $i++) { 
     
	 $start_from;
     $pdf->AddPage('L'); 
     $table_data=getDS_TABLE3($iconn,$ID,$start_from,$limit);
	 $pdf->writeHTML($table_data);
	 $start_from=($start_from+$limit);
	 
	
		}
  
 //  $pdf->AddPage('P'); 
  // $table_data=getDS_TABLE4($iconn,$ID);
	//$pdf->writeHTML($table_data);
	
  	
	 //$pdf->AddPage('P'); 
	 // $pdf->AddPage('P'); 
$logo = "images/jme_logo.jpg";


 $sql_img_list="SELECT * FROM `PRODUCT_INSP_IMAGESS` WHERE INV_ID='$ID'";
 $stmt_img_list=$iconn->query($sql_img_list);
 

$toolcopy = '<table border="0" cellspacing="0" cellpadding="0">';

$count=0;
while($r_img_list = $stmt_img_list->fetch_assoc()){
	$base_path='../uploads/INSP_DATA/';
	$base_path=$ID.'/';
	
	//.$ID.'/'
	$toolcopy .= '<tr>';
	if($r_img_list['FROZEN_W']!=''){		
		$toolcopy .= '<td><img src="'.$base_path.$r_img_list['FROZEN_W'].'.jpg"  width="125" height="180"></td> ';
	}if($r_img_list['DEGLAZED_W']!=''){		
		$toolcopy .= '<td><img src="'.$base_path.$r_img_list['DEGLAZED_W'].'.jpg"  width="125" height="180"></td> ';
	}if($r_img_list['TOTAL_PIECES']!=''){		
		$toolcopy .= '<td><img src="'.$base_path.$r_img_list['TOTAL_PIECES'].'.jpg"  width="125" height="180"></td> ';
	}if($r_img_list['SAMPLE_1']!=''){		
		$toolcopy .= '<td><img src="'.$base_path.$r_img_list['SAMPLE_1'].'.jpg"  width="125" height="180"></td> ';
	}if($r_img_list['SAMPLE_2']!=''){		
		$toolcopy .= '<td><img src="'.$base_path.$r_img_list['SAMPLE_2'].'.jpg"  width="125" height="180"></td> ';
	}
	
	
$toolcopy .= '</tr>';
	
	
	
}

	
  $toolcopy .= '</table>';

  $pdf->writeHTML($toolcopy);
$file_name='INSP_'.$ID.'.pdf';
$pdf->Output($file_name, 'I');

 $sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE ID='$id'";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$r_varity_list = $stmt_varity_list->fetch_assoc();
			


function getDS_TABLE($iconn,$ID){
	
include "Constants.php";
	
	$content='';
	
	$text=10;	
	$header=8;	
	


	$sql_INVOICE_DATA="SELECT * FROM `INVOICE_DATA` WHERE ID='$ID'";
	$stmt_INVOICE_DATA=$iconn->query($sql_INVOICE_DATA);
	$INVOICE_DATA= $stmt_INVOICE_DATA->fetch_array();
	if($INVOICE_DATA['NAME_ADD_OF_PROCESSOR']==1){
		$exp="JAGADEESH MARINE EXPORTS";
	}
	else{
		$exp="MOURYA AQUEX PVT LTD";

	}

	//$PROCESSOR_EMAIL '.$exp.'
	$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `DAY_CODE` ASC";
	
	
	$stmt_code_list=$iconn->query($sql_code_list);
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="1">  
				<tr>  
           		<th  align="center"  width="15%"><font size="'.$text.'">To Company</font></th>
                <th  align="LEFT" width="38%"><font size="'.$text.'"><B> '.$INVOICE_DATA['NAME_ADD_OF_CONSIGNEE'].'</B> </font></th>
                <th  align="center" width="16%"><font size="'.$text.'">Fax / E. Mail</font></th>
                <th  align="LEFT" width="31%"><font size="'.$text.'">MAIL@GMAIL.COM</font></th>
                </tr>
				
				
				<tr>  
           		<th  align="center"  width="15%"><font size="'.$text.'">From</font></th>
                <th  align="LEFT" width="38%"><font size="'.$text.'"> <B>COMPANY NAME</B></font></th>
                <th  align="center" width="16%"><font size="'.$text.'">Mobile Number</font></th>
                <th  align="LEFT" width="31%"><font size="'.$text.'">'.$PROCESSOR_PHONE.'</font></th>
                </tr>
				
				<tr>  
           		<th  align="center"  width="15%"><font size="'.$text.'">INSPECTION File No</font></th>
                <th  align="LEFT" width="38%"><font size="'.$text.'">'. $INVOICE_DATA['INTERNAL_LOT_NUM'].'</font></th>
                <th  align="center" width="16%"><font size="'.$text.'">INSPECTION Report No</font></th>
                <th  align="LEFT" width="31%"><font size="'.$text.'"></font></th>
                </tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"></font></th>
               </tr>


                <tr>  
           		<th  align="center"  width="15%"><font size="'.$text.'">Buyer</font></th>
                <th  align="LEFT" width="38%"><font size="'.$text.'"><B>  '.$INVOICE_DATA['NAME_ADD_OF_CONSIGNEE'].'</B></font></th>
                <th  align="center" width="16%"><font size="'.$text.'">Buyer Reference No.</font></th>
                <th  align="LEFT" width="31%"><font size="'.$text.'"></font></th>
                </tr>
				
				<tr>  
           		<th  align="center"  width="15%"><font size="'.$text.'">Agent </font></th>
                <th  align="LEFT" width="38%"><font size="'.$text.'"> NA</font></th>
                <th  align="center" width="16%"><font size="'.$text.'">P.O No.</font></th>
                <th  align="LEFT" width="31%"><font size="'.$text.'">'.$INVOICE_DATA['PO_NUM'].'</font></th>
                </tr>
				
				<tr>  
           		<th  align="center"  width="15%"><font size="'.$text.'">Manufacture</font></th>
                <th  align="LEFT" width="38%"><font size="'.$text.'"><B> COMPANY NAME </B></font></th>
                <th  align="center" width="16%"><font size="'.$text.'">PI No</font></th>
                <th  align="LEFT" width="31%"><font size="'.$text.'"> NA</font></th>
                </tr>
				<tr>  
  
                <th  align="center" width="15%"><font size="'.$text.'">Samples collected from:</font></th>
                <th  align="LEFT" width="38%"><font size="'.$text.'"> Cold Storage</font></th>
				 <th  align="center" width="16%"><font size="'.$text.'">Total Page</font></th>
                <th  align="LEFT" width="31%"><font size="'.$text.'"> 6</font></th>
				
                </tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'">2.Consignement Details </font></th>
               </tr>
			   
                 <tr> 
           		<th  align="center"  width="35%"><font size="'.$text.'">Product Description</font></th>
                <th  align="center" width="19%"><font size="'.$text.'">Packing</font></th>
                <th  align="center" width="8%"><font size="'.$text.'">Code</font></th>
                <th  align="center" width="14%"><font size="'.$text.'">Production Date</font></th>
                <th  align="center" width="8%"><font size="'.$text.'">Grade</font></th>
                <th  align="center" width="8%"><font size="'.$text.'">Ordered Cartons</font></th>
                <th  align="center" width="8%"><font size="'.$text.'">Declared Cartons</font></th>
                </tr>';
				
				while($code_list_row=$stmt_code_list->fetch_array()){
					$FULL_NAME = $code_list_row['FULL_NAME'];
				$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE ID='$FULL_NAME'";
				$stmt_varity_list=$iconn->query($sql_varity_list);
				$varity_list_row=$stmt_varity_list->fetch_array();
				
				$production_date=getDayCodeDate($code_list_row['DAY_CODE']);
				
              $content .= '   <tr> 
           		<th  align="left"  width="35%"><font size="'.$text.'">'.$varity_list_row['FULL_NAME'].'</font></th>
                <th  align="center" width="19%"><font size="'.$text.'">'.$code_list_row['PACKING'].'</font></th>
                <th  align="center" width="8%"><font size="'.$text.'">'.$code_list_row['DAY_CODE'].'</font></th>
                <th  align="center" width="14%"><font size="'.$text.'">'.$production_date.'</font></th>
                <th  align="center" width="8%"><font size="'.$text.'">'.$code_list_row['GRADES'].'</font></th>
                <th  align="center" width="8%"><font size="'.$text.'">'.$code_list_row['T_O_MCS'].'</font></th>
                <th  align="center" width="8%"><font size="'.$text.'">'.$code_list_row['T_O_MCS'].'</font></th>
                </tr>';
				
				};
				
			 	
				$content .= '  
				
				 <tr> 
           		<th  align="center"  width="35%"><font size="'.$text.'">Inspection location</font></th>
                <th  align="center" width="65%"><font size="'.$text.'"> COMPANY NAME, Bhimavaram </font></th>
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%">  Remark: Overall Satisfactory. <font size="'.$text.'"></font></th>    
				</tr>
				
				
				
				 <tr> 
           		<th  align="LEFT"  width="50%"><font size="'.$text.'"> Sample provided by</font></th>
                <th  align="LEFT" width="50%"><font size="'.$text.'"> Cold Store</font></th>
				</tr>
				
				
				
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"></font></th>    
				</tr>
				
				
				
				 <tr> 
           		<th  align="LEFT"  width="30%"><font size="'.$text.'"> Cold Storage</font></th>
                <th  align="CENTER" width="20%"><font size="'.$text.'"> 1 </font></th>
                <th  align="CENTER" width="20%"><font size="'.$text.'">2</font></th>
                <th  align="CENTER" width="30%"><font size="'.$text.'">3</font></th>
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"></font></th>    
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"></font></th>    
				</tr>
				
				
				<tr> 
           		
                <th  align="CENTER" width="50%"><font size="'.$text.'"> Arrival Time 11:00 hrs.</font></th>
                <th  align="CENTER" width="50%"><font size="'.$text.'"> Departure Time 17:00 hrs</font></th>
               
				</tr>
				 <tr>  
           		<th  align="LEFT"  width="100%"><font size="'.$text.'"></font></th>    
				</tr>
				<tr> 

                  <th  align="LEFT" width="50%"><font size="'.$text.'"> Inspector <BR> <BR> </font></th>
                  <th  align="LEFT" width="50%"><font size="'.$text.'"> Signature <BR> <BR> </font></th>
				</tr>
				
				
				

</table>'; 
	
	return $content;
}



  function getDS_TABLE2($iconn,$ID,$TOTAL_SAMPLES,$S_W_T_MCS){
	
		$text=10;	
	$header=8;	
	
	
	$sql_code_list="SELECT * FROM `CODE_LIST_CREATE` WHERE INV_ID='$ID' ORDER BY `DAY_CODE` ASC";
	$stmt_code_list=$iconn->query($sql_code_list);
	
	$content='';
	
	$size=40;	
	$size1=60;	
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">

           
					
				
               <tr>  
           		<th  align="LEFT"  width="100%"><font size="7"></font></th>    
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="12"> <B> 3.Inspection Method Applied: As per Codex Stan 233-1969  </B></font></th>    
				</tr>
				
                <tr> 
           		<th  align="LEFT"  width="55%"><font size="9">  Sample Size Inspected: </font></th>
                <th  align="LEFT" width="45%"><font size="9">  Sample selected from Carton Numbers:</font></th>
                
				</tr>
 <tr> 
           		<th  align="center"  width="35%"><font size="9"> Product Description </font></th>
                <th  align="center" width="20%"><font size="9"> Packing  </font></th>
                <th  align="center" width="18%"><font size="9"> Production Date </font></th>
                <th  align="center" width="12%"><font size="9"> Grade</font></th>
                <th  align="center" width="15%"><font size="9">  Samples </font></th>
                
                
				</tr>';
				
				while($code_list_row=$stmt_code_list-> fetch_array()){

	
				$each_sample=($code_list_row['T_O_MCS']/$S_W_T_MCS)*$TOTAL_SAMPLES;
				$each_sample=round($each_sample);
			
			 	
			
								
					$FULL_NAME = $code_list_row['FULL_NAME'];
				$sql_varity_list="SELECT * FROM `PO_VARIETY_LIST` WHERE ID='$FULL_NAME'";
				$stmt_varity_list=$iconn->query($sql_varity_list);
				$varity_list_row=$stmt_varity_list->fetch_array();   
				
				$production_date=getDayCodeDate($code_list_row['DAY_CODE']);
				
              $content .= '   <tr> 
           		<th  align="left"  width="35%"><font size="'.$text.'">'.$varity_list_row['FULL_NAME'].'</font></th>
                <th  align="center" width="20%"><font size="'.$text.'">'.$code_list_row['PACKING'].'</font></th>
                <th  align="center" width="18%"><font size="'.$text.'">'.$production_date.'</font></th>
                <th  align="center" width="12%"><font size="'.$text.'">'.$code_list_row['GRADES'].'</font></th>
			    <th  align="center" width="15%"><font size="'.$text.'">'.$each_sample.'</font></th>
				
			
                </tr>';
					 
				
				};
								
				
                $content .= '  
           		
				 <tr>  
           		<th  align="LEFT"  width="100%"><font size="7"></font></th>    
				</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9">  Packing list (PL) Provide by </font></th>
                <th  align="CENTER" width="60%"><font size="9"> Yes </font></th>
               
                
				</tr>
				 
              <p> 4.INSPECTION FINDINGS:  </p>
			   
			    <tr>  
           		<th  align="LEFT"  width="100%"><font size="9"> <B>1. Cold Storage Aspect </B></font></th>    
				</tr>
				
				<tr> 
           		<th  align="LEFT"  width="55%"><font size="9">  Surroundings kept clean, tidy and free from waste</font></th>
                <th  align="center" width="45%"><font size="9"> YES </font></th>
                
                
				</tr>
				<tr> 
           		
           		<th  align="LEFT"  width="25%"><font size="9"> Cold Storage No</font></th>
           		<th  align="LEFT"  width="25%"><font size="9"> 1</font></th>
                <th  align="center" width="25%"><font size="9"> Temperature </font></th>
                <th  align="center" width="25%"><font size="9"> -18.4 C </font></th>
                
				</tr>
				
				
				<tr> 
           		<th  align="LEFT"  width="55%"><font size="9">  Products stored without other products (pork…)</font></th>
                <th  align="center" width="15%"><font size="9"> Yes </font></th>
                <th  align="center" width="30%"><font size="9"> No </font></th>
                
				</tr>
				
				<tr> 
           		<th  align="LEFT"  width="55%"><font size="9">  Total quantity of inspected goods stored in one cold storage only</font></th>
                <th  align="center" width="15%"><font size="9"> Yes </font></th>
                <th  align="center" width="30%"><font size="9"> No </font></th>
                
				</tr>
				
				
				<tr> 
           		<th  align="LEFT"  width="55%"><font size="9">  Cold storage located in the process factory</font></th>
                <th  align="center" width="15%"><font size="9"> Yes </font></th>
                <th  align="center" width="30%"><font size="9"> No </font></th>
                
				</tr>
				
				<tr> 
           		<th  align="LEFT"  width="55%"><font size="9">  Presence of frost on master carton </font></th>
                <th  align="center" width="15%"><font size="9"> Yes </font></th>
                <th  align="center" width="30%"><font size="9"> No </font></th>
                
				</tr>
				
				<tr> 
           		<th  align="LEFT"  width="55%"><font size="9">  Temperature of the product º C (record all reading taken)</font></th>
                <th  align="center" width="45%"><font size="9"> >-18ºC </font></th>

                
				</tr>
				
				 <tr>  
           		<th  align="LEFT"  width="100%"><font size="9"> </font></th>    
				</tr>
				

				
				<tr> 
           		
                <th  align="center" width="20%"><font size="9">  Name/Code </font></th>
                <th  align="center" width="80%"><font size="9"> Electronic Weighing Balance – Certificate No: 005733 </font></th>
                
				</tr>
				
								<tr> 
           		<th  align="LEFT"  width="20%"><font size="9">  Supplier Equipment</font></th>
                <th  align="center" width="20%"><font size="9"> Calibrated </font></th>
                <th  align="center" width="10%"><font size="9"> Yes </font></th>
                <th  align="center" width="25%"><font size="9"> Calib Date</font></th>
                <th  align="center" width="25%"><font size="9"> Valid Until:</font></th>
                
				</tr>
				
							
				
				<tr> 
           		
                <th  align="center" width="20%"><font size="9">  Name/Code </font></th>
                <th  align="center" width="80%"><font size="9"> Digital Thermometer – Certificate No.: SGSIPL/CAL-1901/8-006 </font></th>
                
				</tr>
					<tr> 
           		<th  align="LEFT"  width="20%"><font size="9">  SES Equipment</font></th>
                <th  align="center" width="20%"><font size="9"> Calibrated </font></th>
                <th  align="center" width="10%"><font size="9"> Yes </font></th>
                <th  align="center" width="25%"><font size="9"> Calib Date</font></th>
                <th  align="center" width="25%"><font size="9"> Valid Until:</font></th>
                
				</tr>
				
				
				
				</table>'; 
	
	return $content;	
	
}



function getDS_TABLE3($iconn,$ID,$start_from,$limit){
		
		  $sql_varity_list="SELECT * FROM `INSP_DATA` WHERE INV_ID='$ID' and FROZEN_WEIGHT!='' LIMIT $start_from, $limit";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$data=array();
			while($r_varity_list = $stmt_varity_list->fetch_assoc()){
				
				
				//print_r($r_varity_list);
				$data[] = $r_varity_list;
			}
		
		
	
	$content='';
	
	$size=40;	
	$size1=60;	
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">  
	
	<tr> 
           		<th  align="center"  width="100%"><font size="9"> <B> 2. Organoleptic Findings </B> </font></th>
                
               
                
				</tr>
	
	<tr> 
           		<th  align="left"  width="15.5%"><font size="9"><b>Sample No   </b></font></th>';
				
				$c=1+$start_from;
				$cell_size=100-15.5;
				$cell_size=$cell_size/count($data);
				foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$c.'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
				<tr> 
           		<th  align="left"  width="15.5%"><font size="9"><b>Species  </b></font></th>
                <th  align="center" width="84.5%"><font size="9">  Litopenaeus Vanname</font></th>             
				</tr>
				
	            <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b>Product   </b></font></th>';
				
				foreach($data as $value){	
				
				$VARITY_ID=$value['VARITY_ID'];
				$sql_s_name="SELECT * FROM `PO_VARIETY_LIST` WHERE ID='$VARITY_ID'";
				$stmt_s_name=$iconn->query($sql_s_name);
				$rr_row=$stmt_s_name->fetch_assoc();
				$VARITY_ID=$rr_row['SHORT_NAME'];
				
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$VARITY_ID.'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
                
				
				
	            <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b>Code   </b></font></th>';
               foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['DAY_CODE'].'</font></th>';
					$c++;
				}
				$content .= '</tr>
					
				<tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b>Production Date  </b></font></th>';
               
				
               foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.getDayCodeDate($value['DAY_CODE']).'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
				
				<tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b>Use by Date  </b></font></th>';
				
               foreach($data as $value){	
			   $date= getDayCodeDate($value['DAY_CODE']);
			   
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.getUsebyDate($date).'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"> <b>Declared net wt (g)  </b> </font></th>';
				foreach($data as $value){		
				$CODE_LIST_ID =$value['CODE_LIST_ID'];
				$sql_s_name="SELECT * FROM `CODE_LIST_CREATE` WHERE CODE_LIST_ID='$CODE_LIST_ID'";
				$stmt_s_name=$iconn->query($sql_s_name);
				$rr_row=$stmt_s_name->fetch_assoc();
				$mystring=$rr_row['PACKING'];
				$word='LBS';
				$PACKING=explode('X',$mystring);
				$PACKING=$PACKING[1]; 
				$PACKING=explode(' ',$PACKING);
				
				if(strpos($mystring, $word)){
					//print_r($PACKING);
					$D_WEIGHT=$PACKING[1]*453.592;
				}else{
					$D_WEIGHT=$PACKING[1]*1000;
				}
							
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.round($D_WEIGHT).'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
				<tr> 
				<th  align="LEFT"  width="15.5%"><font size="9"> <b> Size Grade   </b> </font></th>';
				foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['GRADE'].'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
				<tr> 
				<th  align="LEFT"  width="15.5%"><font size="9"><b> Frozen Wt (g)  </b> </font></th>';
				foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['FROZEN_WEIGHT'].'</font></th>';
					$c++;
				}
				$content .= '</tr><tr> 
				
				
				<th  align="LEFT"  width="15.5%"><font size="9"><b> Deglazed wt (g) </b></font></th>';
				foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['DEGLAZED_WEIGHT'].'</font></th>';
					$c++;
				}
				$content .= '</tr>
               
				

				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Total Pieces   </b></font></th>';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['TOTAL_PIECES'].'</font></th>';
					$c++;
				}
				$content .= '</tr>
				<tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Count   </b></font></th>';
                foreach($data as $value){
					$Cal_Count= round(( 454/ $value['DEGLAZED_WEIGHT'])*$value['TOTAL_PIECES']);
					//$Cal_Count=round($Cal_Count,2);
				 
					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$Cal_Count.'</font></th>';
					$c++;
				}
				$content .= '</tr>
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"> <b> Uniformity   </b> </font></th>';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['UNIFORMITY'].'</font></th>';
					$c++;
				}
				$content .= '</tr>				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"> <b> Glaze %   </b></font></th>';
                 foreach($data as $value){		
				$GLAZE=(($value['FROZEN_WEIGHT']-$value['DEGLAZED_WEIGHT'])/$value['FROZEN_WEIGHT'])*100;

						$GLAZE =round($GLAZE,1);
				 
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$GLAZE.'</font></th>';
					$c++;
				}
				
				
				$content .= '</tr>				
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"> <b> Appearance   </b></font></th>
                
				';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">GOOD</font></th>';
					$c++;
				}
				$content .= '</tr>				 
				
				
				<tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Texture   </b></font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">GOOD</font></th>';
					$c++;
				}
				$content .= '</tr>	
				<tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Color    </b></font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">GOOD</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				
				
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Foreign Matter   </b></font></th>
';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	
				 <tr>
				 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Filth   </b></font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	<tr>
				 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Dehydration(g)  </b></font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Discoloration(g) </b> </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Decomposition(g)  </b></font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Broken/Damage(g)  </b></font></th>
               ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Hanging Meat(g)  </b></font></th>';
                foreach($data as $value){
                    IF($value['HANGING_MEAT']==''){
						$value['HANGING_MEAT']='nil';
					}
					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['HANGING_MEAT'].'</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				<tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Black Spot Meat(g)  </b></font></th>
               ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Soft Shell(g)  </b></font></th>
                ';
                foreach($data as $value){	
                       IF($value['SOFT_SHELL']==''){
						$value['SOFT_SHELL']='nil';
					}
				
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['SOFT_SHELL'].'</font></th>';
					$c++;
				}
				
				
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"> <b> Improper Peeled  </b></font></th>';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	
				

				<tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Improper Devined  </b></font></th>
                ';
                foreach($data as $value){
					IF($value['IMPROPER_DEVINED']==''){
						$value['IMPROPER_DEVINED']='nil';
					}
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['IMPROPER_DEVINED'].'</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
								 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Black Tail(g)  </b></font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">nil</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
								 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Total Defect (g)   </b></font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['TOTAL_DEFECT_G'].'</font></th>';
					$c++;
				}
				$content .= '</tr>	
								 <tr> 
           		<th  align="LEFT"  width="15.5%"><font size="9"><b> Total Defect %   </b></font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['TOTAL_DEFECT_P'].'</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				
		</table>'; 
	
	return $content;
}

function getDS_TABLE4($iconn,$ID){
	 
	$content='';
	
	$size=40;	
	$size1=60;	
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">  



                   <tr> 
           		<th  align="LEFT"  width="100%"><font size="12"> <B> 6A.Packing Details</B>  </font></th>
              
				</tr>
                <tr> 
           		<th  align="LEFT"  width="58%"><font size="9">  Type of inner-packing in master carton  </font></th>
           		<th  align="LEFT"  width="42%"><font size="9">  Plain poly pouch  </font></th>
              
				</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="58%"><font size="9">  Product code type of marking on inner-packing   </font></th>
           		<th  align="LEFT"  width="42%"><font size="9"> -- </font></th>
              
				</tr>
				 <tr> 
           		<th  align="LEFT"  width="58%"><font size="9">  Inner-packing Printed Deglazed Net Weight (g)  </font></th>
           		<th  align="LEFT"  width="42%"><font size="9"> --  </font></th>
              
				</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="58%"><font size="9">  Quantity of inner-packing per master carton  </font></th>
           		<th  align="LEFT"  width="42%"><font size="9">    </font></th>
              
				</tr>

                <tr> 
           		<th  align="LEFT"  width="40%"><font size="9">  </font></th>
           		<th  align="center"  width="30%"><font size="9">  Conform  </font></th>
           		<th  align="center"  width="30%"><font size="9"> Non Conform  </font></th>

              
				</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> Individual packing conformity: </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>

              
				</tr>
				 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> Inner packing conformity:  </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>

              
				</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> Inner packing conformity:  </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>

				</tr>
              <tr> 
           		<th  align="LEFT"  width="100%"><font size="9">  </font></th>

             </tr>
			 
			 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> <B> 6B. MARKING/LABEL </B></font></th>
           		<th  align="center"  width="30%"><font size="9">  Conform  </font></th>
           		<th  align="center"  width="30%"><font size="9"> Non Conform  </font></th>
           		
              
				</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> Shipping mark conformity (Master cartons)  </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		
              
				</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> Bar code master cartons</font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		
              
				</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> Shipping mark conformity (Inner Packing)  </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		
              
				</tr>
				 <tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> Bar code inner- packing  </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>

              
				</tr>
				
				<tr> 
           		<th  align="LEFT"  width="40%"><font size="9"> Product label conformity  </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		<th  align="LEFT"  width="30%"><font size="9">    </font></th>
           		
				</tr>
				<tr> 
           		<th  align="LEFT"  width="100%"><font size="9">  </font></th>

             </tr>
			 
			
				
				
				<tr> 
           		<th  align="LEFT"  width="100%"><font size="9"> 6. Report Brief </font></th>

             </tr>
			 
			 <tr> 
           		<th  align="LEFT"  width="100%"><font size="9">  </font></th>

             </tr>
			 <tr> 
           		<th  align="LEFT"  width="100%"><font size="9"> Product:  </font></th>

             </tr>
			 <tr> 
           		<th  align="LEFT"  width="100%"><font size="9"> Weight: </font></th>

             </tr>
			 <tr> 
           		<th  align="LEFT"  width="100%"><font size="9"> Size: </font></th>

             </tr>
			  <tr> 
           		<th  align="LEFT"  width="100%"><font size="9"> Defects: </font></th>

             </tr>
				
				
				
	</table>'; 
	
	return $content;
}

?>