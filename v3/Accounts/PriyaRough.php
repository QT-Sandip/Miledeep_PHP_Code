<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
include "AjaxCostingTable.php";
include "../includes/AjaxGradingDetails.php";
$LOT_ID=$_GET['id'];
$UNIT_ID=$_GET['U'];
require_once('../tcpdf/tcpdf.php'); 

getHlGradingReport($iconn,$LOT_ID);
	$NAME=getUnitDetails($iconn,$LOT_ID); 
	
	
	$sql="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt=$iconn->query($sql);
$post_details=$stmt->fetch_array();

$LOT_NUMBER=$post_details['LOT_NO'];
	
class MYPDF extends TCPDF {

    public function Header() {	
       // $image_file = K_PATH_IMAGES.'jme_logo.jpg';
	  // include "../includes/Globals.php";
	   global  $NAME;
	   global  $LOT_NUMBER;
		 
		// $this->Cell(0, 3, $NAME, 0,1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, $NAME, 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, 'HEAD LESS TO GRADING REPORT - ' .$LOT_NUMBER, 0, 1, 'C', 0, '', 0, false, 'T', 'M');		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
   }
}


	
    $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Costing Report');  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(15); 
	$pdf->SetHeaderMargin(10);	
    $pdf->SetMargins(5, 10, 5,10);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(false);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
   // $pdf->SetFont('TIMES', '', 11);  
   $pdf->AddPage('L'); 
   
    $pdf->Cell(180, 20, '', 0, 1, 'C', 0);
		

   
   $table_data=getPRI_PurchaseTable($iconn,$LOT_ID);
   $pdf->writeHTML($table_data);
   
   $table_data=getPRI_GradingTable($iconn,$LOT_ID);
   $pdf->writeHTML($table_data);
   
  $pdf->AddPage('L');
   
   $pdf->Cell(180, 20, '', 0, 1, 'C', 0);
   //$table_data=getCostingTableP($iconn,$LOT_ID);
   $table_data=getCosingDataPrint($iconn,$LOT_ID);
   $pdf->writeHTML($table_data);
   
   
	$file_name='Costing.pdf';
	$pdf->Output($file_name, 'I');
	
	
	function getCosingDataPrint($iconn,$LOT_ID){
	$text=9;
	$width=25;
	
			$SqlCostingDetails="SELECT *  FROM `RM_COSTING_DETAILS` WHERE LOT_ID='$LOT_ID'";
		$StmtCostingDetails=$iconn->query($SqlCostingDetails);
		$RowCostingDetails=$StmtCostingDetails->fetch_array();		
		$EXPANSE=$RowCostingDetails['EXPANSE'];
		$DEPB=$RowCostingDetails['DEPB'];
		$EXCHANGE_RATE=$RowCostingDetails['EXCHANGE_RATE'];
	
			
			$SQL_GRADE_STATUS="SELECT * FROM `RM_COSTING_GRADES` where LOT_ID='$LOT_ID'";
			$STMT_GRADE_STATUS=$iconn->query($SQL_GRADE_STATUS);
			
			
			
			
			
			
			$content = '<table border="1" cellspacing="0" cellpadding="3">
				
			   <tr>  
           		
                <td  align="center" width="'.$width.'%"><font size="'.$text.'"> <B>Exchange Rate</B></font></td>
                
                <td  align="center" width="'.($width*3).'%"><font size="'.$text.'"><B>Rs .'.$EXCHANGE_RATE.' /-</B></font></td>
              
                </tr>
				
				<tr>  
           		
                <td  align="center" width="'.$width.'%"><font size="'.$text.'"> <B>COUNT</B></font></td>
                <td  align="center" width="'.$width.'%"><font size="'.$text.'"> <B>HL QTY</B></font></td>
                <td  align="center" width="'.$width.'%"><font size="'.$text.'"> <B>USD</B></font></td>
                <td  align="center" width="'.$width.'%"><font size="'.$text.'"><B>INR</B></font></td>
              
                </tr>';
				
				
			
			while($GRADES_ROW=$STMT_GRADE_STATUS->fetch_array()){
				$ID=$GRADES_ROW['ID'];
				$GRADE=$GRADES_ROW['GRADE'];
				$WEIGHT=$GRADES_ROW['WEIGHT'];
				$USD=$GRADES_ROW['USD'];
				$SALE_PRICE=$WEIGHT*$USD*$EXCHANGE_RATE;
				
				$TOTAL_HL_WEIGHT+=$WEIGHT;
				$TOTAL_SALE_PRICE+=$SALE_PRICE;
				
	
	
				$content .= '<tr>  
           		
               
				 <td align="right" width="'.$width.'%"><font size="'.$text.'"> '.$GRADE.'</font></td>
				
				 <td  align="right" width="'.$width.'%"><font size="'.$text.'"> '.($WEIGHT).'</font></td>
				 <td  align="right" width="'.$width.'%"><font size="'.$text.'"> '.($USD).'</font></td>
				
                
                <td  align="right" width="'.$width.'%"><font size="'.$text.'"> <B>'.($SALE_PRICE).'</B></font></td>
                </tr>';
				
				
			}
			
			
			
	
	
		$SqlBillDetails="SELECT *  FROM `RM_BILL_DETAILS` WHERE LOT_ID='$LOT_ID'";
		$StmtBillDetails=$iconn->query($SqlBillDetails);
		$RowBillDetails=$StmtBillDetails->fetch_array();		
		$TotalWeight=$RowBillDetails['WEIGHT'];
		$Rate=$RowBillDetails['RATE'];
		$AccDeductions=$RowBillDetails['DEDUCTIONS'];
		
		$SqlDefectsDetails="SELECT SUM(WEIGHT) AS DEF_WEIGHT FROM `RM_DEFECTS_DETAILS` WHERE LOT_ID='$LOT_ID'";
		$StmtDefectsDetails=$iconn->query($SqlDefectsDetails);
		$RowDefectsDetails=$StmtDefectsDetails->fetch_array();
		$DefWeight=$RowDefectsDetails['DEF_WEIGHT'];
   
		$SqlHonGrading="SELECT * FROM `PPC_GRADING_HON` WHERE LOT_ID='$LOT_ID'";
		$StmtHonGrading=$iconn->query($SqlHonGrading);
		$RowHonGrading=$StmtHonGrading->fetch_array();
		

		
		
		
		if($RowHonGrading!=0){
			$HonUsed=$RowHonGrading['HON_WEIGHT'];
		}else{
			$HonUsed=0;
		}
   
   
   $SqlRmQc="SELECT * FROM `ORGN_INSP_REPORT` WHERE LOT_ID='$LOT_ID'";
$StmtRmQc=$iconn->query($SqlRmQc);
$RowRmQC=$StmtRmQc->fetch_array();
$LAB_DEDUCTION=$RowRmQC['DEDUCTIONS'];
   
		$Weight=$TotalWeight + $DefWeight - $HonUsed; 
		$PurchaseCost=$Weight * $Rate; 
		$LAB_DEDUCTION=$Weight * $LAB_DEDUCTION;
		$PurchaseCost=$PurchaseCost-$LAB_DEDUCTION;	
		$PurchaseCost=$PurchaseCost+($Weight*$AccDeductions);
	
		$TOTAL_DEPB=($DEPB * $TOTAL_SALE_PRICE)/100; 
		$TOTAL_Expance=$TOTAL_HL_WEIGHT * $EXPANSE;
		
		$PROFIT_LOSS=($TOTAL_SALE_PRICE+$TOTAL_DEPB)-($TOTAL_Expance+$PurchaseCost);
		$HL_COST=(($TOTAL_SALE_PRICE+$TOTAL_DEPB)-$TOTAL_Expance);
		$AVG_HL_COST=(($TOTAL_SALE_PRICE+$TOTAL_DEPB)-$TOTAL_Expance)/$TOTAL_HL_WEIGHT;
		
		if($PROFIT_LOSS>0){
			$TRANSACTION='GAIN';
			}else{
			$TRANSACTION='LOSS';
			}


				
				$content .= '
				<tr>  
           		
                <td  align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B>Total</B></font></td>
                <td  align="right" width="'.$width.'%"><font size="'.$text.'"> <B>'.($TOTAL_HL_WEIGHT).'</B></font></td>
                <td  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> <B></B></font></td>
                <td  align="right" width="'.$width.'%"><font size="'.$text.'"> <B>'.($TOTAL_SALE_PRICE).'</B></font></td>
                </tr>
				
				<tr>            		
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B>Expanse</B></font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'"> <B>'.($EXPANSE).' / kg</B></font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'"> <B> - '.($TOTAL_Expance).'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B>DEPB</B></font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'"><B> '.$DEPB.' %</B></font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'"> <B> + '.($TOTAL_DEPB).'</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B></B></font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B></B></font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'"> <B> '.($HL_COST).'</B></font></th>
                </tr>
				
				<tr>            		
                <th align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B>'.$TRANSACTION.'</B></font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'">
				<B style="color:red"> '.round($PROFIT_LOSS,2).'</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B>'.$TRANSACTION.'</B></font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'">Per Kg</font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'">
				<B style="color:red"> '.round(($PROFIT_LOSS/$TOTAL_HL_WEIGHT),2).'</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B>Average </B></font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'">INR/HL </font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'">
				<B style="color:red"> '.round($AVG_HL_COST,2).'</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="'.$width.'%"><font size="'.$text.'"><B>Average</B></font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'"> USD </font></th>
                <th  align="LEFT" width="'.$width.'%"><font size="'.$text.'"> </font></th>
                <th  align="right" width="'.$width.'%"><font size="'.$text.'">
				<B style="color:red"> '.round($AVG_HL_COST/$EXCHANGE_RATE,2).'</B>
                
				</font></th>
				
				</tr>
				
				</table>'; 
				

	
	return $content;


				
}




function getPRI_PurchaseTable($iconn,$LOT_ID){
	

$sql="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt=$iconn->query($sql);
$post_details=$stmt->fetch_array();

$SqlBillDetails="SELECT *  FROM `RM_BILL_DETAILS` WHERE LOT_ID='$LOT_ID'";
$StmtBillDetails=$iconn->query($SqlBillDetails);
$RowBillDetails=$StmtBillDetails->fetch_array();

$SqlDefectsDetails="SELECT * FROM `RM_DEFECTS_DETAILS` WHERE LOT_ID='$LOT_ID'";
$StmtDefectsDetails=$iconn->query($SqlDefectsDetails);


$SqlHonGrading="SELECT * FROM `PPC_GRADING_HON` WHERE LOT_ID='$LOT_ID'";
$StmtHonGrading=$iconn->query($SqlHonGrading);
$RowHonGrading=$StmtHonGrading->fetch_array();


$SqlRmQc="SELECT * FROM `ORGN_INSP_REPORT` WHERE LOT_ID='$LOT_ID'";
$StmtRmQc=$iconn->query($SqlRmQc);
$RowRmQC=$StmtRmQc->fetch_array();
$LAB_DEDUCTION=$RowRmQC['DEDUCTIONS'];
		
		
		
if($RowHonGrading!=0){
			$HonUsed=$RowHonGrading['HON_WEIGHT'];
}else{
			$HonUsed=0;
}	


$SQL_GRADE_STATUS="SELECT * FROM `RM_COSTING_GRADES` where LOT_ID='$LOT_ID'";
$STMT_GRADE_STATUS=$iconn->query($SQL_GRADE_STATUS);

while($G_ROW=$STMT_GRADE_STATUS->fetch_array()){
	$HL_WEIGHT+=$G_ROW['WEIGHT'];
}

$RATE=$RowBillDetails['RATE'];
$SHED_YIELD=$RowBillDetails['YIELD'];
$LOT_COUNT=$RowBillDetails['COUNT'];
$DEDUCTIONS=$RowBillDetails['DEDUCTIONS'];
$Rate=$RowBillDetails['RATE'];
$HON_GRADED=$RowBillDetails['WEIGHT'];

$HonUsedPrice=$HonUsed*$Rate;
$HON_GRADED_PRICE=$HON_GRADED*$Rate;
$PURCHASE_AMOUNT=$HON_GRADED_PRICE-($HON_GRADED*$LAB_DEDUCTION);
$PURCHASE_AMOUNT=$HON_GRADED_PRICE+($HON_GRADED*$DEDUCTIONS);

			
		$PurchaseTMST = $post_details['PURCHASE_DATE'];
		$PurchaseDate = date("d-m-Y", strtotime($PurchaseTMST));	
			
			if($post_details['UNIT_ID']==1){
				$Station='BVRM';
				}else{
					$Station='NSP';
				}
				
				$c=100/10;
				$c=$c.'%';
				$text=9;
	 
			$content .= '<table border="1" cellspacing="0" cellpadding="3">  
				
			   <tr>  
           		
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"> <B>Grading Date</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"> <B>CENTER</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"> <B>BILL NO</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>COUNT</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>RATE</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>HON-QNTY</B></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>HL-QNTY</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>SHED  YEILD%</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>Deductions/Kg</B></font></th>
                
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>AMOUNT</B></font></th>
                </tr>
				
				<tr>  
           		
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B style="color:red">'.$PurchaseDate.'</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"> '.$Station.'</font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"> <B>'.$post_details['LOT_NO'].'</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>'.$LOT_COUNT.'</B></font></th>
                <th  align="CENTER" width="'.$c.'" ><font size="'.$text.'"><B style="color:red">'.$Rate.'</B></font></th>
            
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'">'.$HON_GRADED.'</font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'">'.$HL_WEIGHT.'</font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>'.$SHED_YIELD.'</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B></B></font></th>
                <th  align="RIGHT" width="'.$c.'"><font size="'.$text.'"><B>'.round($PURCHASE_AMOUNT).'</B></font></th>
                </tr>';
				
					if($HonUsed!='0'){
						
				$content .= '<tr>  
           		
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'">Hon Packed</font></th>
				
                <th  align="CENTER" width="'.$c.'" ><font size="'.$text.'"><B style="color:red">'.$Rate.'</B></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'">'.$HonUsed.'</font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B>'.round((($HonUsed * $Rate)-($HonUsed*$LAB_DEDUCTION) )).'</B></font></th>
                </tr>';
					}
				
				
			while($RowDefectsDetails=$StmtDefectsDetails->fetch_array()){
						$TOTAL_HON+=$RowDefectsDetails['WEIGHT'];
						$PURCHASE_AMOUNT+=($RowDefectsDetails['WEIGHT']* $RowDefectsDetails['RATE'] );
				$content .= '<tr>  
           		
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'">'.$RowDefectsDetails['NAME'].'</font></th>
				
                <th  align="CENTER" width="'.$c.'" ><font size="'.$text.'"><B style="color:red">'.$RowDefectsDetails['RATE'].'</B></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'">'.$RowDefectsDetails['WEIGHT'].'</font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="RIGHT" width="'.$c.'"><font size="'.$text.'"><B>'.round(($RowDefectsDetails['WEIGHT'] * $RowDefectsDetails['RATE'] )).'</B></font></th>
                </tr>';
					}
				
				
				$PURCHASE_AMOUNT=$PURCHASE_AMOUNT-($HON_GRADED*$LAB_DEDUCTION);
				$content .= '<tr>  
           		
                <th  align="LEFT" width="'.$c.'"><font size="'.$text.'"><B>Lab</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'" ><font size="'.$text.'"></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B></B></font></th>
                <th  align="RIGHT" width="'.$c.'"><font size="'.$text.'"><B>- '.$LAB_DEDUCTION.'</B></font></th>
                <th  align="RIGHT" width="'.$c.'"><font size="'.$text.'"><B>-'.round($HON_GRADED*$LAB_DEDUCTION).'</B></font></th>
                </tr><tr>  
           		
                <th  align="LEFT" width="'.$c.'"><font size="'.$text.'"><B>Accounts</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'" ><font size="'.$text.'"></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B></B></font></th>
                <th  align="RIGHT" width="'.$c.'"><font size="'.$text.'"><B>'.$DEDUCTIONS.'</B></font></th>
                <th  align="RIGHT" width="'.$c.'"><font size="'.$text.'"><B>'.round($HON_GRADED*$DEDUCTIONS).'</B></font></th>
                </tr><tr>  
           		
                <th  align="LEFT" width="'.$c.'"><font size="'.$text.'"><B>Total</B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'" ><font size="'.$text.'">'.$TOTAL_HON.'</font></th>
				<th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="'.$c.'"><font size="'.$text.'"></font></th>
                <th  align="RIGHT" width="'.$c.'"><font size="'.$text.'"><B>Rs. '.round($PURCHASE_AMOUNT).' /-</B></font></th>
                </tr>';
				
				
				$content .= '</table>'; 
	
	return $content;
	
}
	
function getPRI_GradingTable($iconn,$LOT_ID){
	
	
			
			$sql="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
			$stmt=$iconn->query($sql);
			$post_details=$stmt->fetch_array();
	
			$SqlBillDetails="SELECT * FROM `RM_BILL_DETAILS` where LOT_ID='$LOT_ID'";
			$stmtBillDetails=$iconn->query($SqlBillDetails);
			$RowBillDetails=$stmtBillDetails->fetch_array();
			
			$SqlHlWeight="SELECT SUM(WEIGHT) AS WEIGHT FROM `PPC_FINAL_GRADING_HL` WHERE LOT_ID='$LOT_ID'";
			$StmtHlWeight=$iconn->query($SqlHlWeight);
			$RowHlWeight=$StmtHlWeight->fetch_array();
			$HL_WEIGHT=$RowHlWeight['WEIGHT'];
			
			$sql="SELECT * FROM `PPC_FINAL_GRADING_HL` where LOT_ID='$LOT_ID'";
			$STMT_GRADES=$iconn->query($sql);
			
			
			
	
	$size=40;	
	$size1=60;	
	$textw='30%';
	$text=10;
 
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">
				<tr> 
				<th  align="CENTER" width="10%"><font size="'.$text.'"> <B>S.L</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>COUNT</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>QTY</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>ESTD HON</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>PACKED %</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>ESTD HON CT</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>TOTAL PCS</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>AVARAGE HON CT</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>PURCHASE COUNT</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>DIFF</B></font></th>
                </tr>';
				
				$c=1+$start_from;
				$cell_size=100-30;
				$cell_size=$cell_size/count($ary_counts);
				$c=1;
				$TOTAL_HL_WEIGHT=0;
				$TOTAL_ESTD_HON=0;
				$TOTAL_PERC=0;
				$TOTAL_PCS=0;
				$TOTAL_COUNTS=0;
				
				$AVG_COUNT=0;
				$YIELD=$RowBillDetails['YIELD'];
				$LOT_WEIGHT=$RowBillDetails['WEIGHT'];
				$LOT_COUNT=$post_details['COUNT'];
				
			while($row=$STMT_GRADES->fetch_array()){
				

			$count=$row['COUNT'];	
			$weight_sum=$row['WEIGHT'];	
			$weight_sum=$row['WEIGHT'];	
			$pack_Perc=0;			
			
			
			
			
			
			 
			 $ESTD_HON = $weight_sum /($HL_WEIGHT/$LOT_WEIGHT);
			
			$PERC=($weight_sum/$HL_WEIGHT)*100;
			
			$ESTD_HON_COUNT=$count*2.2*$YIELD/100;
			
			
			
			$PCS=$ESTD_HON * $ESTD_HON_COUNT; 
			
			$TOTAL_HL_WEIGHT=$TOTAL_HL_WEIGHT+$weight_sum;
			$TOTAL_ESTD_HON=$TOTAL_ESTD_HON+$ESTD_HON;
			$TOTAL_PERC = $TOTAL_PERC + $PERC;
			$TOTAL_PCS=$TOTAL_PCS+$PCS;
			$TOTAL_COUNTS=$TOTAL_COUNTS+$ESTD_HON_COUNT;
				
					$content .= ' <tr>
					
				<th  align="CENTER" width="10%"><font size="'.$text.'"> '.$c.'</font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> '.$count.'</font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'">'.$weight_sum.'</font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'">'.ROUND($ESTD_HON,3).'</font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'">'.ROUND($PERC,3).'</font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'">'.ROUND($ESTD_HON_COUNT,3).'</font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'">'.ROUND($PCS,3).'</font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B></B></font></th>
                </tr>';
					$c++;
				
				}
				
				$AVG_COUNT=$TOTAL_PCS/$TOTAL_ESTD_HON;
				  $DIFF_COUNT=$LOT_COUNT-$AVG_COUNT;
				
				$content .= '
				
				<tr>  
           		
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>TOTAL</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$TOTAL_HL_WEIGHT.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.round($TOTAL_ESTD_HON,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.round($TOTAL_PERC,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.round($TOTAL_PCS,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B style="color:red" >'.round($AVG_COUNT,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B style="color:red">'.round($LOT_COUNT,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B style="color:red">'.round($DIFF_COUNT,2).'</B></font></th></tr>
				<p>	In order to compensate the loss ,</p>			 
				
				
		</table>'; 
	
	return $content;
}


 
?>