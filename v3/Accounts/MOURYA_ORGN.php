<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
//include "Util.php";
$ID=$_GET['id'];
require_once('../tcpdf/tcpdf.php'); 

$id=$_GET['LOT_ID'];


	
	$SULPHITE=$row_org_insert['SULPHITE'];


$SQL_CODE_LIST="SELECT * FROM `DAILY_LAB_REPORT_LIST` where  `LOT_NUMBER`='$LOT_NUMBER'";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
$ROW_CODE_LIST=$STMT_CODE_LIST->fetch_array();
	
	$sql_list="SELECT * FROM `inputmaterial_details` WHERE id=$id";
    $stmt_ID=$iconn->query($sql_list);  
	$row_list=$stmt_ID->fetch_array();
	
	$LOT_NO=$row_list['LOT_NO'];
	$PURCHASE_COUNT=$row_list['COUNT'];
	$AGENT_NAME=$row_list['AGENT_NAME'];
	$ANTI_BIOTIC_STATUS=$row_list['ANTI_BIOTIC_STATUS'];
	
	 $sql_list="SELECT * FROM `DAILY_LAB_REPORT_LIST` WHERE LOT_NUMBER='$LOT_NO'";
     $stmt_ID=$iconn->query($sql_list);  
	 $row_d_lab=$stmt_ID->fetch_array();
	
	 $RESULT=$row_d_lab['PARAMETER'];	  
	 $SULPHITE=$row_d_lab['SULPHITE'];

	$sql_INSP="SELECT * FROM `ORGN_INSP_REPORT` WHERE LOT_ID=$id";
    $stmt_INSP=$iconn->query($sql_INSP); 

	$INSPECTED_BY="";	
	$VERIFIED_BY="";	
	$AUTH_REMARKS="";	 
	$TESTEDBY = $row_d_lab['TESTED_BY'];


	
	while($row_INSP=$stmt_INSP->fetch_array()){
		
		if($INSPECTED_BY==''){
			 $INSPECTED_BY=$row_INSP['INSPECTED_BY'];
		}
		
		if($VERIFIED_BY==''){
		 	$VERIFIED_BY=$row_INSP['VERIFIED_BY'];
		}
		
		if($AUTH_REMARKS==''){
		 	$AUTH_REMARKS=$row_INSP['AUTH_REMARKS'];
		}if($DEDUCTIONS==''){
		 	$DEDUCTIONS=$row_INSP['DEDUCTIONS'];
		}
		
	$arry_INSP[]=$row_INSP;
	}
	
	$CHECK_COUNT=$row_INSP['CHECK_COUNT'];
	
	$sql_user="SELECT * FROM `login` WHERE USER_ID=$INSPECTED_BY";
    $stmt_user=$iconn->query($sql_user);
	$row_user=$stmt_user->fetch_array();
	
	$USER_NAME=$row_user['USER_NAME'];
	
	$UNIT_ID=$row_user['UNIT_ID'];
	
	$sql_units="SELECT * FROM `PROCESSING_UNITS` WHERE UNIT_ID=$UNIT_ID";
    $stmt_units=$iconn->query($sql_units);
	$row_units=$stmt_units->fetch_array();
	$INSPECTED_UNIT=$row_units['UNIT_NAME'];
	
	
	
	$sql_user="SELECT * FROM `login` WHERE USER_ID=$VERIFIED_BY";
    $stmt_user=$iconn->query($sql_user);
	$row_user=$stmt_user->fetch_array();
	
	$VERIFIED_NAME=$row_user['USER_NAME'];
	
	
	/*if($LOT_NO[0]=='M'){
          $image_file = K_PATH_IMAGES.'ma_logo_edit.jpg';
		
	}else{
		$image_file = K_PATH_IMAGES. 'jme_logo.jpg';
	} */
	
	
		if($LOT_NO[0]=='M'){
class MYPDF extends TCPDF {


    public function Header() {	
	
        $image_file = K_PATH_IMAGES.'ma_logo_edit.jpg';	
        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'M/s. MOURYA AQUEX PVT LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'INTERNAL QUALITY CHECK', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
   }
}
		}else{
			class MYPDF extends TCPDF {


    public function Header() {	
	
        $image_file = K_PATH_IMAGES.'jme_logo.jpg';	
        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'M/s. JAGADEESH MARINE EXPORTS', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'INTERNAL QUALITY CHECK', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
   }
}
			
		}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('INTERNAL QUALITY CHECK: '.$row['PO_NUM'].' - '.$row['PO_DATE']);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(45); 
	$pdf->SetHeaderMargin(155);	
    $pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT,45);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 15);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage('P');  
	$H1='5';
		$pdf->Cell(40,$H1,'Ref No.'.$row_list['LOT_NO'] ,0,0,'L');
	
	$pdf->Cell(30,$H1,'Qty.'.$row_list['WEIGHT_IN_KGS'].'.KGS',0,0,'L');
	$pdf->Cell(50,$H1,'Purchase Date:' .getFormatedDate($row_list['DATE_TIME']),0,0,'L');
	//$pdf->Cell(40,$H1,'Production Date:' .getFormatedDate($row_list['DATE_TIME']),0,1,'L');
	$pdf->Cell(40,$H1,'Receiving Station :' .$INSPECTED_UNIT,0,1,'L');
	
	
	
	
	
	
	$table_data=getDS_TABLE3($iconn,$ID,$arry_INSP,$PURCHASE_COUNT, $AGENT_NAME);
	$pdf->writeHTML($table_data);
	
	//$pdf->Cell(0,$H1,'Remarks : '. $arry_INSP[1]['IC_REMARKS'] ,0,1,'L');
				$pdf->Cell(100,$H1,'INSPECTED by: '.$USER_NAME,0,0,'L');
				//$pdf->Cell(100,$H1,'Monitered by:',0,1,'L');
				
				$pdf->Cell(0,$H1,'',0,1,'L');
				
				$pdf->Cell(0,$H1,'ANTIBIOTIC ANALYSIS REPORT (PRE HARVEST/HARVEST) :',0,1,'L');
	      
		  
		  
	
	$table_data=getDS_TABLE4($iconn,$ID,$RESULT,$ANTI_BIOTIC_STATUS);
	$pdf->writeHTML($table_data);
	
	$pdf->Cell(100,$H1,'Tested by: '.$TESTEDBY,0,1,'L');
	$pdf->Cell(100,$H1,'',0,1,'L');
	$pdf->Cell(0,$H1,'Remarks: ',0,1,'L');
	$pdf->Cell(180,10,$AUTH_REMARKS.',  Deductions: Rs. '.$DEDUCTIONS.' /Kg',1,1,'L');
	$pdf->Cell(100,$H1,'',0,1,'L');                 
				
				$pdf->Cell(100,$H1,'Verified and Certified by: ' .$VERIFIED_NAME,0,1,'L');
  
  	
$logo = "images/jme_logo.jpg";

$file_name='DS'.$row['PO_NUM'];
$pdf->Output($file_name, 'I');

			
       






	function getDS_TABLE3($iconn,$ID,$ary,$PURCHASE_COUNT, $AGENT_NAME){
	


  $CHECKED_COUNT=$ary[0]['CHECK_COUNT']+$ary[1]['CHECK_COUNT']+$ary[2]['CHECK_COUNT'];
$CHECKED_COUNT=round($CHECKED_COUNT/3);


 $Avg_weight= '1000'/$CHECKED_COUNT;


 $RED_BLACK_GILLS=$ary[0]['RED_BLACK_GILLS']* $Avg_weight +$ary[1]['RED_BLACK_GILLS']* $Avg_weight+$ary[2]['RED_BLACK_GILLS']* $Avg_weight;

 $RED_BLACK_GILLS=ROUND($RED_BLACK_GILLS/30,1);
	
	$SEMI_SOFT_PAPER_SOFT=$ary[0]['SEMI_SOFT_PAPER_SOFT']* $Avg_weight+$ary[1]['SEMI_SOFT_PAPER_SOFT']* $Avg_weight+
	$ary[2]['SEMI_SOFT_PAPER_SOFT']* $Avg_weight;
$SEMI_SOFT_PAPER_SOFT=round($SEMI_SOFT_PAPER_SOFT/30,1);
	
	$DROOPING_HEAD=$ary[0]['DROOPING_HEAD']* $Avg_weight +$ary[1]['DROOPING_HEAD'] * $Avg_weight+ $ary[2]['DROOPING_HEAD']*
	$Avg_weight;
$DROOPING_HEAD=round($DROOPING_HEAD/30,1);
	
	$BLACK_SPOT=$ary[0]['BLACK_SPOT']* $Avg_weight+$ary[1]['BLACK_SPOT'] * $Avg_weight +$ary[2]['BLACK_SPOT']* $Avg_weight;
$BLACK_SPOT=round($BLACK_SPOT/30,1);
	
	$BROKEN=$ary[0]['BROKEN']* $Avg_weight +$ary[1]['BROKEN']* $Avg_weight +$ary[2]['BROKEN']* $Avg_weight;
$BROKEN=round($BROKEN/30,1);
	
	$IMPROPER_LEGS=$ary[0]['IMPROPER_LEGS']* $Avg_weight+$ary[1]['IMPROPER_LEGS']* $Avg_weight+$ary[2]['IMPROPER_LEGS']* $Avg_weight;
$IMPROPER_LEGS=round($IMPROPER_LEGS/30,1);


	$BENDS=$ary[0]['BENDS']* $Avg_weight+$ary[1]['BENDS']* $Avg_weight+$ary[2]['BENDS']* $Avg_weight;
$BENDS=round($BENDS/30,1);

$FIT_FOR_HEAD_ON=$stmt_INSP ['FIT_FOR_HEAD_ON'];
	


	$content='';
	
	$size=40;	
	$size1=60;	
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="4">  
	
	<tr> 

                <th  align="center" width="100%"><font size="13"> ORGANELLOPATIC INSPECTION REPORT</font></th>
               
                
				</tr>
	
	          <tr> 
           		
                <th  align="center" width="6%"><font size="9"> S.No </font></th>
                <th  align="center" width="34%"><font size="9"> Description </font></th>
                <th  align="center" width="12%"><font size="9"> Standard </font></th>
                <th  align="center" width="12%"><font size="9"> 1 </font></th>
                <th  align="center" width="12%"><font size="9"> 2  </font></th>
                <th  align="center" width="12%"><font size="9"> 3 </font></th>
                <th  align="center" width="12%"><font size="9"> Average% </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		 <th  align="center" width="6%"><font size="9"> 1. </font></th>
                <th  align="center" width="34%"><font size="9"> Farmer Agent</font></th>
                <th  align="center" width="12%"><font size="9">-</font></th>
                <th  align="center" width="48%"><font size="9">'. $AGENT_NAME.' </font></th>

               
                
               
                
				</tr>
				
				 <tr> 
           		 <th  align="center" width="6%"><font size="9"> 2. </font></th>
                <th  align="center" width="34%"><font size="9"> Purchase count/Kg </font></th>
                <th  align="center" width="12%"><font size="9"> - </font></th>
                <th  align="center" width="12%"><font size="9"> '.$PURCHASE_COUNT.'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$PURCHASE_COUNT.'   </font></th>
                <th  align="center" width="12%"><font size="9"> '.$PURCHASE_COUNT.'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$PURCHASE_COUNT.'  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		<th  align="center" width="6%"><font size="9"> 3. </font></th>
                <th  align="center" width="34%"><font size="9"> Checked count/Kg </font> </th>
                <th  align="center" width="12%"><font size="9"> -  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$ary[0]['CHECK_COUNT'].'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$ary[1]['CHECK_COUNT'].'   </font></th>
                <th  align="center" width="12%"><font size="9"> '.$ary[2]['CHECK_COUNT'].'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$CHECKED_COUNT.'  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		<th  align="center" width="6%"><font size="9"> 4. </font></th>
                <th  align="center" width="34%"><font size="9"> Red/Black gill in% </font></th>
                <th  align="center" width="12%"><font size="9"> 5% </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[0]['RED_BLACK_GILLS']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[1]['RED_BLACK_GILLS']*$Avg_weight/10,1).'   </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[2]['RED_BLACK_GILLS']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$RED_BLACK_GILLS.'  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		<th  align="center" width="6%"><font size="9"> 5. </font></th>
                <th  align="center" width="34%"><font size="9"> Soft/Semi Soft/Paper Soft </font></th>
                <th  align="center" width="12%"><font size="9"> 10% </font></th>
                <th  align="center" width="12%"><font size="9">  '.ROUND($ary[0]['SEMI_SOFT_PAPER_SOFT']*$Avg_weight/10,1).' </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[1]['SEMI_SOFT_PAPER_SOFT']*$Avg_weight/10,1).'   </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[2]['SEMI_SOFT_PAPER_SOFT']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$SEMI_SOFT_PAPER_SOFT.'  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		<th  align="center" width="6%"><font size="9"> 6. </font></th>
                <th  align="center" width="34%"><font size="9"> Dropping Head in %</font></th>
                <th  align="center" width="12%"><font size="9"> 5% </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[0]['DROOPING_HEAD']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[1]['DROOPING_HEAD']*$Avg_weight/10,1).'   </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[2]['DROOPING_HEAD']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$DROOPING_HEAD.'  </font></th>
                
               
                
				</tr>
				
				
				 <tr> 
           		<th  align="center" width="6%"><font size="9"> 7. </font></th>
                <th  align="center" width="34%"><font size="9"> Black spot/Black Dot in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9">  '.ROUND($ary[0]['BLACK_SPOT']*$Avg_weight/10,1).' </font></th>
                <th  align="center" width="12%"><font size="9">  '.ROUND($ary[1]['BLACK_SPOT']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9">  '.ROUND($ary[2]['BLACK_SPOT']*$Avg_weight/10,1).' </font></th>
                <th  align="center" width="12%"><font size="9"> '.$BLACK_SPOT.'  </font></th>
          
				</tr>
				
				 <tr> 
           		<th  align="center" width="6%"><font size="9"> 8. </font></th>
                <th  align="center" width="34%"><font size="9"> Broken/Damages in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[0]['BROKEN']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[1]['BROKEN']*$Avg_weight/10,1).'   </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[2]['BROKEN']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$BROKEN.'  </font></th>
          
				</tr>
				
				 <tr> 
           		<th  align="center" width="6%"><font size="9"> 9. </font></th>
                <th  align="center" width="34%"><font size="9"> Improper Legs/Decomposition/Discoloration in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[0]['IMPROPER_LEGS']*$Avg_weight/10,1).'   </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[1]['IMPROPER_LEGS']*$Avg_weight/10,1).'   </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[2]['IMPROPER_LEGS']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.$IMPROPER_LEGS.'  </font></th>
          
				</tr>
				
				 <tr> 
            	<th  align="center" width="6%"><font size="9"> 10. </font></th>
                <th  align="center" width="34%"><font size="9"> Bends in %</font></th>
                <th  align="center" width="12%"><font size="9"> 10% </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[0]['BENDS']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[1]['BENDS']*$Avg_weight/10,1).'   </font></th>
                <th  align="center" width="12%"><font size="9">  '.ROUND($ary[2]['BENDS']*$Avg_weight/10,1).' </font></th>
                <th  align="center" width="12%"><font size="9">  '.$BENDS.' </font></th>
          
				</tr> 
				
				
				<tr> 
            	<th  align="center" width="6%"><font size="9"> 11. </font></th>
                <th  align="center" width="34%"><font size="9">Presence Of Black Tails</font></th>
                <th  align="center" width="12%"><font size="9"> 2% </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[0]['BENDS']*$Avg_weight/10,1).'  </font></th>
                <th  align="center" width="12%"><font size="9"> '.ROUND($ary[1]['BENDS']*$Avg_weight/10,1).'   </font></th>
                <th  align="center" width="12%"><font size="9">  '.ROUND($ary[2]['BENDS']*$Avg_weight/10,1).' </font></th>
                <th  align="center" width="12%"><font size="9">  '.$BENDS.' </font></th>
          
				</tr>
				
				
				
				
				 <tr> 
           		           		<th  align="center" width="6%"><font size="9"> 12. </font></th>
                <th  align="center" width="34%"><font size="9"> Material is fit for Head On Packing or not</font></th>
                <th  align="center" width="12%"><font size="9"> -- </font></th>
                
                <th  align="center" width="48%"><font size="9"> '.$FIT_FOR_HEAD_ON.'  </font></th>
              
          
				</tr>
				
				
				</table>'; 
				
				
		return $content;		
	}
				
				
			
	function getDS_TABLE4($iconn,$ID,$RESULT,$ANTI_BIOTIC_STATUS){
	
	$content='';
	
	$PARMS=ARRAY('CAP','AOZ','AMOZ','AHD','SEM');
	
	$size=40;	
	$size1=60;	
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="4">  	
		
		 <tr> 
           		
               
                <th  align="center" width="28%"><font size="9"> DESCRIPTION  </font></th>
                <th  align="center" width="18%"><font size="9"> Standard </font></th>
                <th  align="center" width="18%"><font size="9"> Analysis  </font></th>
                <th  align="center" width="18%"><font size="9"> Re-Analysis </font></th>
                <th  align="center" width="18%"><font size="9"> Third Party </font></th>
          
				</tr>
		
		
		<tr> ';
		
		FOREACH($PARMS AS $value){
			
			
			if($ANTI_BIOTIC_STATUS == "0"){
				$parM_val='TEST PENDING';
			}else
			if($value == $RESULT)
			{
				$parM_val='POsitive';
			}else{
				$parM_val='NEGATIVE';
			}
               
            $content .= '    <th  align="center" width="28%"><font size="9"> '.$value.'  </font></th>
                <th  align="center" width="18%"><font size="9"> NEGATIVE </font></th>
                <th  align="center" width="18%"><font size="9"> '. $parM_val.'  </font></th>
                <th  align="center" width="18%"><font size="9"> </font></th>
                <th  align="center" width="18%"><font size="9">   </font></th>
          
				</tr>
		<tr>'; 
		}
           		
               
             
           		
               
          $content .= '      <th  align="center" width="28%"><font size="9"> SULPHITE  </font></th>
                <th  align="center" width="18%"><font size="9"> Less Than 50ppm </font></th>
                <th  align="center" width="18%"><font size="9">'.$SULPHITE.' </font></th>
                <th  align="center" width="18%"><font size="9">  </font></th>
                <th  align="center" width="18%"><font size="9">   </font></th>
          
				</tr>
		
						</table>'; 
	
	return $content;


}



?>