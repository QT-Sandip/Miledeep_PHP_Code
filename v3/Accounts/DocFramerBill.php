<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
$LOT_ID=$_GET['LOT_ID'];
require_once('../tcpdf/tcpdf.php'); 

 $RmBillSql="SELECT * FROM `RM_BILL_DETAILS`  WHERE `LOT_ID`='$LOT_ID'";	 
$RmBillExec=$iconn->query($RmBillSql);
$RmBillRow = $RmBillExec->fetch_assoc();

if($RmBillRow['BILL_COPY']==0){
$BillCopy='[ ORIGINAL COPY ]';

$BillCount=$RmBillRow['BILL_COPY']+1;

 $RmBillSql="UPDATE `RM_BILL_DETAILS` SET BILL_COPY='$BillCount' WHERE `LOT_ID`='$LOT_ID'";	 
$RmBillExec=$iconn->query($RmBillSql);

}else{
$BillCopy='[ DUPLICATE COPY ]';	
$BillCopy='';	
}



	 $sql_input="SELECT * FROM `inputmaterial_details`  WHERE `id`='$LOT_ID'";	 
     $stmt_input=$iconn->query($sql_input);
     $row_input=$stmt_input->fetch_array();
	 $PURCHASE_TYPE=$row_input['PURCHASE_TYPE'];
 $UNIT=$row_input['UNIT'];
IF($UNIT==1){
	
	$GST='37AABFJ7166R1ZL';
	
}ELSE{
	$GST='37AADCJ1985B1ZN';
}


	

  
class MYPDF extends TCPDF {

    public function Header() {	
	
	include  "../qc/Constants.php";
	 global  $UNIT; 
	 global  $BillCopy; 
IF($UNIT==1){
	
	$GST='37AABFJ7166R1ZL';
	$COMPANY_NAMEE='JAGADEESH MARINE EXPORTS';
	        $image_file = $COMPANY_LOGO;

}ELSE{
	$GST='37AADCJ1985B1ZN';
	$COMPANY_NAMEE='Mourya Aquex Pvt Ltd';
	$image_file=$COMPANY_LOGO_ma; 
}
 


        $this->Image($image_file, 15, 5, 25, 25, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', '', 6);
		 $this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 5, '' , 0,1, 'L', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 6, $BillCopy , 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 6, $COMPANY_NAMEE , 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', '', 10);
		$this->Cell(0, 6, 'GSTIN No : '.$GST , 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 6, 'RAW MATERIAL PURCHASE REPORT CUM RECEIVED NOTE', 0, 1, 'C', 0, '', 0, false, 'T', 'M');	
		}
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		//$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Purchase Bill: ');  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(0);	
    $pdf->SetMargins(10, 25, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$LOT_ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);
	
 
$file_name='pdf'.$row['PO_NUM'].'.pdf';
$pdf->Output($file_name, 'I');


			


function getDS_TABLE($iconn,$LOT_ID){
	

			
			
 $HLGQuery = "SELECT 
im.PURCHASE_DATE,
 im.LOT_NO,
 im.UNIT_ID,
 im.AGENT_NAME,
 
 im.DRIVER_NAME,
 im.VEHICAL_NO,
 im.ADDRESS,
 im.WEIGHT_IN_KGS AS F_WEIGHT,
 im.COUNT AS F_COUNT,
 im.VEHICAL_NO ,
 im.DRIVER_NAME ,
 im.BILL_NAME ,
 im.ANTI_BIOTIC_STATUS ,
 im.PURCHASE_TYPE,
 im.SUPERVISOR,
 im.BillPanNumber,
 RBD.COUNT AS C_COUNT,
 RBD.WEIGHT ,
 RBD.DEDUCTIONS ,
 RBD.REMARKS ,
 RBD.Tds ,
 RBD.RATE FROM 
`inputmaterial_details` im LEFT JOIN 
RM_BILL_DETAILS RBD on im.id = RBD.LOT_ID WHERE im.id = '$LOT_ID'";

$HLGexec=$iconn->query($HLGQuery);

$BillPanNumber= $center = $purchaseDate = $headlessCount = $headlessQty = $LOTNO = "";
if(!empty($HLGexec)){
	
	$HLGRep = $HLGexec->fetch_assoc();
	
	$AB=$HLGRep['ANTI_BIOTIC_STATUS'];
		
		if($AB==0){
			$A_B_STATUS='TEST PENDING';
			$CLR='#666';
		}else if($AB==1){
			$A_B_STATUS='POSITIVE';
			$CLR='#f00';
		}else if($AB==2){
			$A_B_STATUS='NEGATIVE';
			$CLR='#00a65a';
		}else if($AB==3){
			$A_B_STATUS='ON HOLD';
			$CLR='#04c';
		}


	if($HLGRep['UNIT_ID'] == 1){
		$center = 'Bhimavaram';
	}else if($HLGRep['UNIT_ID'] == 2){
		$center = 'Narsapuram';
	}
	$purchaseDate = $HLGRep['PURCHASE_DATE'];
	$BillPanNumber = $HLGRep['BillPanNumber'];
	$Tdsperc = $HLGRep['Tds'];
	$ADDRESS = $HLGRep['ADDRESS'];
	$C_COUNT = $HLGRep['C_COUNT'];
	$F_COUNT = $HLGRep['F_COUNT'];
	$F_QTY = $HLGRep['F_WEIGHT'];
	$VEHICAL_NO = $HLGRep['VEHICAL_NO'];
	$DRIVER_NAME = $HLGRep['DRIVER_NAME'];
	$C_QTY = $HLGRep['WEIGHT'];
	$RATE =$HLGRep['RATE'];
	$LOTNO = $HLGRep['LOT_NO'];
	$TUBS = $HLGRep['TUBS'];
	$BILL_NAME = $HLGRep['BILL_NAME'];
	$AGENT_NAME = $HLGRep['AGENT_NAME'];
	$VARITY = $HLGRep['VARITY'];
	$DRIVER_NAME = $HLGRep['DRIVER_NAME'];
	$VEHICAL_NO = $HLGRep['VEHICAL_NO'];
	$AccDeductionsKg= $HLGRep['DEDUCTIONS'];
	$PURCHASE_TYPE= $HLGRep['PURCHASE_TYPE'];
	$GRADER_NAME= $HLGRep['SUPERVISOR'];
	$ACCOUNT_REMARKS= $HLGRep['REMARKS'];
	
	
/*	if($C_COUNT > 100){
		$RATE = round($RATE);	
		$C_COUNT = round($C_COUNT);	
	} */
	

$TOTAL_HON=$HON_USED+$HON_GRADED;
$TOTAL_HON_PRICE=$TOTAL_HON*$RATE;
$DEFECT_HON_PRICE=$DEFECT_WGT*$DEFECT_RATE;
$PURCHASE_AMOUNT=($C_QTY * $RATE);



}

			$SqlDefectsDetails="SELECT * FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
			$StmtDefectsDetails=$iconn->query($SqlDefectsDetails);


	        $SqlQcRmDetails="SELECT * from ORGN_INSP_REPORT where LOT_ID='$LOT_ID'";
			$StmtQcRmDetails=$iconn->query($SqlQcRmDetails);
			$RowQcRmDetails = $StmtQcRmDetails->fetch_assoc();
			
			$SqlHonGrading="SELECT * FROM `PPC_GRADING_HON` WHERE LOT_ID='$LOT_ID'";
			$StmtHonGrading=$iconn->query($SqlHonGrading);
			$RowHonGrading=$StmtHonGrading->fetch_array();
			
			if($RowHonGrading!=0){
			$HonUsed=$RowHonGrading['HON_WEIGHT'];
			$HonUsedRate=$RowHonGrading['HON_PACKED_RATE'];
			$HonUsedCount=$RowHonGrading['HON_PACKED_COUNT'];
			
			$HonUsedAmount=$HonUsedRate*$HonUsed;
}else{
			$HonUsed=0;
			$HonUsedAmount=0;
}
			
			
			
			$TotalWeight=$C_QTY+$HonUsed;
			$LabDeductionKg= $RowQcRmDetails['DEDUCTIONS'];
			$ExactLabDeductionKg= $RowQcRmDetails['labdeductions'];
			$Remarks= $RowQcRmDetails['AUTH_REMARKS'];
			$LabDeduction= $RowQcRmDetails['DEDUCTIONS']*$TotalWeight;
			$AccDeductions= $AccDeductionsKg*$TotalWeight;
			$DefectsAmount=0;




	
	
	
	
	
	
	$content='';
	
		$content.='<h5  style="text-align:right"> AB REPORT: '.$A_B_STATUS.'</h5>';
		$content.='<h5  style="text-align:center"></h5>';

	
	$size1=100/6;
	$size2=100/6;
	$size=100-$size1-$size2;	
	$size3=60;
	
	
	$EXP=explode(',',$QUANTITY_IN_KGS);
	$LBS=$EXP[0];
	$KGS=$EXP[1];
				
				
				
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="LEFT"  width="'.($size).'%"><font size="10">Bill Name: <b>'.$BILL_NAME.'</font></b></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Date : </font></th>
                <th  align="LEFT" width="'.($size1).'%"><font size="10"><b>'.getFormatedDate($purchaseDate).'</b></font></th>
                

				</tr>
				 <tr>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Farmer Name:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"></font></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Cell Number:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"></font></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Village: </font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"><B>'.$ADDRESS.'</B></font></th>
				  
           		

				</tr>
				
				<tr>

                <th  align="LEFT"  width="'.($size2).'%"><font size="10">Agent Name:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"><b>'.$AGENT_NAME.'</font></b></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Cell Number:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"></font></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Lot No:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"><b>'.$LOTNO.'</font></b></th>				
           		
               </tr>
				
				<tr>  
           		<th  align="LEFT"  width="50%"><font size="10">Company Count/Quality : <b>'.$C_COUNT.'c / '.$C_QTY.'</b> Variety :<b>'.$VARITY.' </b></font></th>
           		<th  align="LEFT"  width="50%"><font size="10"> Farmer Count/Quality : <b>'.$F_COUNT.'c / '.$F_QTY.'</b> Variety :<b>'.$VARITY.' </b></font></th>
               </tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Details </font></th>
                <th  align="LEFT" width="15%"><font size="9">Total Pies 5Kg </font></th>
                <th  align="LEFT" width="15%"><font size="9"> 1Kg C </font></th>
                <th  align="LEFT" width="15%"><font size="9"> Total Pies 5Kg</font></th>
                <th  align="LEFT" width="15%"><font size="9"> 1Kg C </font></th>
                <th  align="LEFT" width="20%"><font size="9"> Average </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> 5 Kg Count </font></th>
                <th  align="LEFT" width="15%"><font size="9"> '.(5 * $headlessCount).'</font></th>
                <th  align="LEFT" width="15%"><font size="9"><b>'.$headlessCount.'</b> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Peeling </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Discoloured </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Black spot </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="10"><b>Quantity Details</b></font></th>
           		<th  align="center"  width="50%"><font size="10"><b> Transport Details </b></font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="10">Tubs Details</font></th>
           		<th  align="left"  width="50%"><font size="10"> Truck No : <b>'.$VEHICAL_NO.'</B> </font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="14%"><font size="10"> Material Tubs </font></th>
           		<th  align="center"  width="10%"><font size="10"> Empty </font></th>
           		<th  align="center"  width="12%"><font size="10"> Total Tubs </font></th>
           		<th  align="center"  width="14%"><font size="10">Quantity </font></th>
           		<th  align="left"  width="50%"><font size="10"> Driver :<b>'.$DRIVER_NAME.'</B> </font></th>
               </tr>
				
					<tr>  
           		<th  align="center"  width="14%"><font size="10"> '.$TUBS.' </font></th>
           		<th  align="center"  width="10%"><font size="10">Hon Qty  </font></th>
           		<th  align="center"  width="12%"><font size="10"> '.$C_COUNT .' C</font></th>
           		<th  align="center"  width="14%"><font size="10"> <B>'.$C_QTY .' Kgs</B></font></th>
           		<th  align="left"  width="50%"><font size="10"> Dispatched : <b>'.$center.'</B> </font></th>
               </tr>';
			   
			   
			   while($RowDefectsDetails=$StmtDefectsDetails->fetch_array()){
			   $AryRowDefects[]=$RowDefectsDetails;
			   
			   $content .= '<tr>  
           		<th  align="center"  width="14%"><font size="10">  </font></th>
           		<th  align="center"  width="10%"><font size="10">'.$RowDefectsDetails['NAME'].'  </font></th>
           		<th  align="center"  width="12%"><font size="10"> -- </font></th>
           		<th  align="center"  width="14%"><font size="10"><B>'.$RowDefectsDetails['WEIGHT'].' Kgs</B></font></th>
           		<th  align="left"  width="50%"><font size="10">  </font></th>
               </tr>';
			  
			  }
			   
			   
			  
			   
			   
			   
				
				 $content .= '<tr>  
           		<th  align="center"  width="24%"><font size="10"> <br> <br>  Driver Sign <br> </font></th>
           		<th  align="center"  width="26%"><font size="10"> <br> GRADER:<B>'.$GRADER_NAME.'<BR><br>'. $PURCHASE_TYPE.' </B><br> </font></th>
           		<th  align="center"  width="25%"><font size="10"> <br> <br>  Supervisor Sign <br> </font></th>
           		<th  align="center"  width="25%"><font size="10">  <br> <br>  Agent/Farm Sign <br> </font></th>
           		
               </tr>
				
				
				
				<tr>  
           		<th  align="center"  width="100%"><font size="9"><b>For Office use only   
				BillNo.   Certificate       NoCertificate </b></font></th>    
				</tr>
				
				 <tr>  
           		<th  align="center"  width="20%"><font size="10"> Variety/Quality </font></th>
           		<th  align="center"  width="15%"><font size="10"> Count  </font></th>
           		<th  align="center"  width="15%"><font size="10"> Quantity </font></th>
           		<th  align="center"  width="15%"><font size="10"> Rate/Kg </font></th>
           		<th  align="center"  width="15%"><font size="10"> Deduction/Kg </font></th>
           		<th  align="center"  width="20%"><font size="10"> Amount :  </font></th>
           		
               </tr>
			   
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><B>Head On Qty </B></font></th>
           		<th  align="center"  width="15%"><font size="10"> '.$C_COUNT .'  C </font></th>
           		<th  align="center"  width="15%"><font size="10"> '.$C_QTY .'  Kgs  </font></th>
           		<th  align="center"  width="15%"><font size="10"> '.$RATE .' / Kgs  </font></th>
           		<th  align="center"  width="15%"><font size="10"> <B></b></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>'.round((($C_QTY * $RATE))) .' /-  </b></font></th>
           		
               </tr>';
			   
			   if($HonUsed!='0'){
			   
			    $content .= '<tr>  
           		<th  align="left"  width="20%"><font size="10"><B>Head On (Packed) </B></font></th>
           		<th  align="center"  width="15%"><font size="10"> '.$HonUsedCount .'  C </font></th>
           		<th  align="center"  width="15%"><font size="10"> '.$HonUsed .'  Kgs  </font></th>
           		<th  align="center"  width="15%"><font size="10"> '.$HonUsedRate .' / Kgs  </font></th>
           		<th  align="center"  width="15%"><font size="10"> <B></b></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>'.round((($HonUsed * $HonUsedRate))) .' /-  </b></font></th>
           		
               </tr>' ;
			   }
			   
			   
			   foreach($AryRowDefects as $value){
				   $DefectsWeight=$DefectsWeight+$value['WEIGHT'];
				   $DefectsAmount=$DefectsAmount+($value['WEIGHT'] * $value['RATE']);
			   $content .= '
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><B>'.$value['NAME'].'</B></font></th>
           		<th  align="center"  width="15%"><font size="10">  -- </font></th>
           		<th  align="center"  width="15%"><font size="10"> '.$value['WEIGHT'] .'  Kgs  </font></th>
           		<th  align="center"  width="15%"><font size="10"> '.$value['RATE'] .' / Kgs  </font></th>
           		<th  align="center"  width="15%"><font size="10"> Rs .<B> -- </b></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>'.round($value['WEIGHT'] * $value['RATE']) .' /- </b></font></th>
           		
               </tr>';
			   
			   }
			   
			   if($ExactLabDeductionKg!=0){
				   $LabRemarks='A DEDUCTION OF RS.'.round($ExactLabDeductionKg).'/- IS RECOMMENDED FOR QUALITY LOSS';
			   }else{
				  $LabRemarks='NILL'; 
			   }
			   
			 if($AccDeductions > 0){
				 $Sing='+';
				 
			 }else{
				 $Sing='';
			 }
			   
			    $content .= '
				
				 <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Sub Total</b></font></th>
           		<th  align="center"  width="15%"><font size="10"> </font></th>
           		<th  align="center"  width="15%"><font size="10"><b>'.($C_QTY+$DefectsWeight+$HonUsed).' Kgs</b></font></th>
           		<th  align="center"  width="15%"><font size="10"></font></th>
           		<th  align="center"  width="15%"><font size="10"></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>'.round(($PURCHASE_AMOUNT + $DefectsAmount+$HonUsedAmount)) .' /- </b> </font></th>
           		
               </tr> 
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Lab </b></font></th>
           		
				
           		<th  align="center"  width="45%"><font size="10">'.$LabRemarks.'</font></th>
           		
           		<th  align="center"  width="15%"><font size="10">'.round($LabDeductionKg).'</font></th>
           		<th  align="right"  width="20%"><font size="10"><B> - '.round($LabDeduction) .' /- </b> </font></th>
           		
               </tr>
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Accounts </b></font></th>	
           		<th  align="center"  width="45%"><font size="10">'.$ACCOUNT_REMARKS.'</font></th>
           		<th  align="center"  width="15%"><font size="10">'.round($AccDeductionsKg,1).'</font></th>
           		<th  align="right"  width="20%"><font size="10"><B>'.round($AccDeductions) .' /- </b> </font></th>
           		
               </tr>'; 
			   
			   if($purchaseDate > '2021-06-30'){
				   
				   $BillTotal=$PURCHASE_AMOUNT + $DefectsAmount +$HonUsedAmount + $AccDeductions -$LabDeduction;
			       
				   $TdsAmount=($BillTotal*$Tdsperc)/100;
				 
				// comment after bill creation updated
				   if($BillPanNumber !='' && 
				   strlen($BillPanNumber) ==10){
					  $TDSperc='0.1%'; 
					 $TdsAmount=($BillTotal*0.1)/100;  
				   }else{
					   $TDSperc='5%';
					   $BillPanNumber='NA';
					  $TdsAmount=($BillTotal*5)/100;
				   }
				   
				  //echo $TdsAmount; exit; 
			   $content.='<tr>  
           		<th  align="left"  width="20%"><font size="10"><b>TDS </b></font></th>	
           		<th  align="center"  width="45%"><font size="10">PAN Number : '.$BillPanNumber.'</font></th>
           		<th  align="center"  width="15%"><font size="10">'.$TDSperc.'</font></th>
           		
           		<th  align="right"  width="20%"><font size="10"><B>'.round($TdsAmount) .' /- </b> </font></th>
           		
               </tr>';
			   $BillTotal=$BillTotal-$TdsAmount;
			    
			   }else{
			   $BillTotal=$PURCHASE_AMOUNT + $DefectsAmount +$HonUsedAmount + $AccDeductions -$LabDeduction;
			   }
			   
			    $content.=' <tr>  
           		<th  align="center"  width="80%"><font size="10"><B> Total</B></font></th>
           		
           		<th  align="right"  width="20%"><font size="10">Rs. <B>'.round($BillTotal) .' /- </b> </font></th>
           		
               </tr>
				
				
			   
                <tr>  
           		<th  align="center"  width="20%"><font size="10"> <br> <br>  Pur.Mgr   <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br> <br>  Pur.Authorised <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br> <br>  Plant Mgr  <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br> <br>  Account Mgr <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br> <br>  Authorised  <br></font></th>
                
				</tr>
				
				
				
				</table>'; 
	
	return $content;	
	
}

?>