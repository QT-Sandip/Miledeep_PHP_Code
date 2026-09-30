<?php
session_start();
include "config.php";
include "Globals.php";
$ID=$_GET['INV_ID'];
$CID=$_GET['CID'];
require_once('tcpdf/tcpdf.php'); 
	
	
class MYPDF extends TCPDF {

    public function Header() {	
       // $image_file = K_PATH_IMAGES.'jme_logo.jpg';
        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'M/s. JAGADEESH MARINE EXPORTS , BHIMAVARM', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'REGISTER FOR ANALYTICAL RESULTS', 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
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
    $pdf->SetFooterMargin(15); 
	$pdf->SetHeaderMargin(0);	
    $pdf->SetMargins(5, 15, 5,10);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 15);  
    $pdf->SetFont('TIMES', '', 11);  
    //$pdf->AddPage();  
	
	$pdf->Cell(0, 3, 'M/s. JAGADEESH MARINE EXPORTS , BHIMAVARM', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->Cell(0, 3, 'REGISTER FOR ANALYTICAL RESULTS', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		
	 $limit = 10; 
	 $sql = "SELECT COUNT(*) FROM REG_FR_NYLTIC_RESULT where CODE_LIST_ID='$CID'"; 
	$stmt_sql=$iconn->query($sql);	
     $rs_result = $stmt_sql->fetch_array();   
     
        $total_records = $rs_result[0];   

		$total_pages = ceil($total_records / $limit);   
        
		$start_from=0;

   $pdf->AddPage('P'); 
   $table_data=getDS_TABLE($iconn,$CID);
	$pdf->writeHTML($table_data);
		
			   
        for ($i=1; $i<=$total_pages; $i++) { 
     
	 $start_from;
     //$pdf->AddPage('L'); 
     $table_datad=getDS_TABLE3($iconn,$CID,$start_from,$limit);
	 $pdf->writeHTML($table_datad);
	 $start_from=($start_from+$limit);
	 
	
		}
		
  
  $pdf->writeHTML($toolcopy);
$file_name='DS'.$row['PO_NUM'];
$pdf->Output($file_name, 'I');

 
			
					
function getDS_TABLE($iconn,$CID){
	
			$sql_sam="SELECT COUNT(*) as SAMPLES from REG_FR_NYLTIC_RESULT where CODE_LIST_ID='$CID'";
			$stmt_sam=$iconn->query($sql_sam);
			$r_sam = $stmt_sam->fetch_assoc();
	
			$sql_varity_list="SELECT * FROM `CODE_LIST_CREATE` WHERE CODE_LIST_ID='$CID'";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$r_varity_list = $stmt_varity_list->fetch_assoc();
			
			 $INV_ID=$r_varity_list['INV_ID'];
			
			 $sql_BUYER_MB_RESULT="SELECT * FROM `BUYER_MB_RESULT` WHERE CODE_LIST_ID='$CID'";
			$stmt_BUYER_MB_RESULT=$iconn->query($sql_BUYER_MB_RESULT);
			$r_BUYER_MB_RESULT = $stmt_BUYER_MB_RESULT->fetch_assoc();
			
			$P_CODES_REG_NUM = $r_BUYER_MB_RESULT['DOA_DOC'];
			$P_CODES_REG_NUM_ARY=explode('/',$P_CODES_REG_NUM);
			$ExaminationDATE = $P_CODES_REG_NUM_ARY[1];
			
			$ExaminationDATE = date("d-m-Y", strtotime($ExaminationDATE));
			
			
			 $sql_INV_ID="SELECT * FROM `INVOICE_DATA` where ID='$INV_ID'";
			 $stmt_INV_ID=$iconn->query($sql_INV_ID);
			 $r_INV_ID = $stmt_INV_ID->fetch_assoc();
			
			
			
			
	
	
		//$content='';	
	$text=10;
 
 
      $TOTAL_MCS=$r_varity_list['MCS'];
	 
	  $TOTAL_MCS_ARY=explode(',',$TOTAL_MCS);
	  $TOTAL_MCS = 0;
	  
	  foreach($TOTAL_MCS_ARY as $value){
		$TOTAL_MCS	= $TOTAL_MCS + $value;	
	  }
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="1">  
	
	<tr>  
           		<th  align="LEFT"  width="15%"><font size="'.$text.'">Name of processor</font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'"> <B>JME</B></font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'">Approval number</font></th>
                <th  align="LEFT" width="5%"><font size="'.$text.'"><B>999</B></font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'">Type of product</font></th>
                <th  align="LEFT" width="10%"><font size="'.$text.'"><B>'.$r_varity_list['SHORT_NAME'].'</B></font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'">Production code</font></th>
                <th  align="LEFT" width="10%"><font size="'.$text.'"><B>'.$r_varity_list['DAY_CODE'].'</B></font></th>
				
                </tr>
				
				<tr> 
				<th  align="LEFT" width="15%"><font size="'.$text.'">Tracebility Details:</font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'"><B>'.$r_varity_list['FARM_REG_ID'].'</B></font></th>				
           		<th  align="LEFT"  width="15%"><font size="'.$text.'">No of Cartons</font></th>
                <th  align="LEFT" width="5%"><font size="'.$text.'"><B>'.$TOTAL_MCS .'</B></font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'">No of samples</font></th>
                <th  align="LEFT" width="10%"><font size="'.$text.'"><B>'.$r_sam['SAMPLES'].'</B></font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'"><B>Date of Examination</B></font></th>
                <th  align="LEFT" width="10%"><font size="'.$text.'"><B>'.$ExaminationDATE.'</B></font></th>
                
                </tr>
				
				
				<tr>  
				<th  align="LEFT" width="15%"><font size="'.$text.'">Brand</font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'"><B>'.$r_INV_ID['BRAND'].'</B></font></th>
           		<th  align="LEFT"  width="15%"><font size="'.$text.'">Product core temperature:</font></th>
                <th  align="LEFT" width="15%"><font size="'.$text.'"><B>-18.1 Degree Centigrade </B></font></th>
                

                </tr>
				
				</table>'; 
	
	return $content;
	
}
	


	function getDS_TABLE3($iconn,$ID,$start_from,$limit){
		
		  $sql_varity_list="SELECT * FROM `REG_FR_NYLTIC_RESULT` WHERE CODE_LIST_ID='$ID'   LIMIT $start_from, $limit";
			$stmt_varity_list=$iconn->query($sql_varity_list);
			$data=array();
			while($r_varity_list = $stmt_varity_list->fetch_assoc()){
				
				
				//print_r($r_varity_list);
				$data[] = $r_varity_list;
			}
			
			//print_r($data);
		
	
	
	//$content='';
	
	$size=40;	
	$size1=60;	
	$textw='30%';
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">  
	
	
	<tr> 
           		<th  align="left"  width="'.$textw.'"><font size="9">  Sample No   </font></th>';
				
				$c=1+$start_from;
				$cell_size=100-30;
				$cell_size=$cell_size/count($data);
				foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$c.'</font></th>';
					$c++;
				}
				
				$CODE_LIST_ID =$value['CODE_LIST_ID'];
				$sql_s_name="SELECT * FROM `CODE_LIST_CREATE` WHERE CODE_LIST_ID='$CODE_LIST_ID'";
				$stmt_s_name=$iconn->query($sql_s_name);
				$rr_row=$stmt_s_name->fetch_assoc();
				
				$content .= '</tr>
				
				<tr> 
           		<th  align="left"  width="'.$textw.'"><font size="9"> Type of packing  </font></th>
                <th  align="center" width="70%"><font size="9">'.$rr_row['PACKING'].'</font></th>             
				</tr>
				
	            <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Species   </font></th>';
				
				foreach($data as $value){	
				
				$VARITY_ID=$value['SPECIES'];
				 $sql_s_name="SELECT * FROM `PO_VARIETY_LIST` WHERE ID='$VARITY_ID'";
				$stmt_s_name=$iconn->query($sql_s_name);
				$rr_row=$stmt_s_name->fetch_assoc();
				$VARITY_ID=$rr_row['SHORT_NAME'];
				
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$VARITY_ID.'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
                
				
				
	            <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Declared size grade   </font></th>';
               foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['GRADE'].'</font></th>';
					$c++;
				}
				$content .= '</tr>
					
				<tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Frozen Wt. in Kg.  </font></th>';
               
				
               foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['FROZEN_WEIGHT'].'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
				
				<tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Drained Wt/Deglazed Wt.in Kg.  </font></th>';
				
               foreach($data as $value){	
			 //  $date= getDayCodeDate($value['DAY_CODE']);
			 
			 $FROZEN_WEIGHT  = $value['FROZEN_WEIGHT'];
			 $GLAZE  = $value['GLAZE'];
			
			$GLAZE=(($value['FROZEN_WEIGHT']*$value['GLAZE'])/100);

						 $GLAZE =round($value['FROZEN_WEIGHT'] - $GLAZE,1);
			   
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$GLAZE .'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Glaze%   </font></th>';
				foreach($data as $value){	
			 //  $date= getDayCodeDate($value['DAY_CODE']);
			   
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['GLAZE'].'</font></th>';
					$c++;
				}
				
				
				
				
				
				$content .= '</tr>
				
				<tr> 
				<th  align="LEFT"  width="'.$textw.'"><font size="9"> Frozen/Thawed count per lb/wt/p.c/Deglazed count  </font></th>';
				foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['FROZEN_COUNT'].'</font></th>';
					$c++;
				}
				$content .= '</tr>
				
				<tr> 
				<th  align="LEFT"  width="'.$textw.'"><font size="9">  General Appearance  </font></th>';
				foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">GOOD</font></th>';
					$c++;
				}
				$content .= '</tr><tr> 
				
				
				<th  align="LEFT"  width="'.$textw.'"><font size="9">Feneral Order </font></th>';
				foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">Charateristic</font></th>';
					$c++;
				}
				$content .= '</tr>
               
				

				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Dehydration   </font></th>';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				$content .= '</tr>
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Discoloration   </font></th>';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				$content .= '</tr>				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Detirioration</font></th>';
                 foreach($data as $value){		
				
				 
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				
				
				$content .= '</tr>				
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Blackspot on shell or meat   </font></th>
                
				';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				$content .= '</tr>				 
				
				
				<tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Broken,damaged bruiced pieces,  </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				$content .= '</tr>	
				<tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Legs,bits of veins,antennae,loose-shells,soft shells,hanging meat etc  </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				
				
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Non uniformity of size  </font></th>
';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['NON_UNIFORMITY'].'</font></th>';
					$c++;
				}
				$content .= '</tr>	
				 <tr>
				 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Foreign vegetable matter & No of Pieces </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				$content .= '</tr>	<tr>
				 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Objectionable foreign matter  </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				$content .= '</tr>	
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Texture  </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">Normal</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Total Deduction </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NA</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Satisfactory / unsatisfactory </font></th>
               ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">Satisfactory</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  TCP at 37C c.f.u/gm  </font></th>';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">'.$value['TPC'].'</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				<tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  E.Coil at 37C/gm </font></th>
               ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Staph. cocci at 37C/gm  </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}
				
				
				$content .= '</tr>	
				
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Salmonella/Arizona  </font></th>';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">Absent</font></th>';
					$c++;
				}
				$content .= '</tr>	
				

				<tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  V.C./V.P </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">Absent</font></th>';
					$c++;
				}
				$content .= '</tr>	
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9">  Satisfactory/ Unsatisfactory </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">Satisfactory</font></th>';
					$c++;
				}
				$content .= '</tr>	
				 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Decision:Exportworthy/Nor-exportworthy/to be re-examined  </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">Export Worthy</font></th>';
					$c++;
				}
				$content .= '</tr>	
				
								 <tr> 
           		<th  align="LEFT"  width="'.$textw.'"><font size="9"> Remarks </font></th>
                ';
                foreach($data as $value){					
					$content .= ' <th  align="center" width="'.$cell_size.'%"><font size="9">NILL</font></th>';
					$c++;
				}  
				$content .= '</tr>	
								 
				
				
		</table>'; 
	
	return $content;
}


?>