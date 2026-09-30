<?php



 //ini_set('display_errors', 1);
 //ini_set('display_startup_errors', 1);
 //error_reporting(E_ALL);
session_start();

$LotID=$_GET['LotID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php"); 

$RmBillSql="SELECT * FROM `RmBillDetails`  WHERE `ArrivalID`='$LotID'";	 
$RmBillExec=$iconn->query($RmBillSql);
$RmBillRow = $RmBillExec->fetch_assoc();




	 $SqlLotDetails="SELECT * FROM `RmLotDetatils` AS RLD LEFT
	 JOIN RmPurchaseStations AS RPS ON RLD.PurchaseStationID=RPS.PurchaseStationID
	 WHERE `RmLotID`='$LotID'";	 
     $StmtLotDetails=$iconn->query($SqlLotDetails);
     $RowLotDetails=$StmtLotDetails->fetch_array();
	 $PurchaseType=$RowLotDetails['PurchaseType'];
	 $ProcessingStnID=$RowLotDetails['ProcessingStationID'];
     $UNIT=$RowLotDetails['UNIT'];

     $SqlClientDetails="SELECT * FROM `RmProcessingStations` WHERE `ProcessingStationID`='$ProcessingStnID'"; 
     $StmtClientDetails=$iconn->query($SqlClientDetails);
     $RowClientDetails=$StmtClientDetails->fetch_array();
	
     $CompanyName=$RowClientDetails['FullName'];
     $GST=$RowClientDetails['GSTNumber'];
     $Logo=$RowClientDetails['CompanyLogo'];
  
class MYPDF extends TCPDF {

    public function Header() {	
	
	include  "../qc/Constants.php";
	 global  $UNIT; 
	 global  $CompanyName; 
	 global  $GST; 
	 global  $Logo; 
 

        $this->Image($Logo, 15, 5, 25, 25, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', '', 6);
		 $this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 5, '' , 0,1, 'L', 0, '', 0, false, 'T', 'M');
		//$this->Cell(0, 6, $BillCopy , 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 6, $CompanyName , 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', '', 10);
		$this->Cell(0, 6, 'GSTIN No : '.$GST , 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 6, 'RAW MATERIAL PURCHASE REPORT CUM RECEIVED NOTE', 0, 1, 'C', 0, '', 0, false, 'T', 'M');	
		}
	
    public function Footer() {
		
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$dt=$dateTime->format("d-m-Y  H:i:s");
        $this->SetFont('helvetica', 'I', 8);
		//$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->Cell(0, 10, 'Dated '.$dt .', Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
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
	
	
	
	$table_data=getDS_TABLE($iconn,$LotID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);
	
 ob_end_clean();

$file_name='pdf'.$row['PO_NUM'].'.pdf';
$pdf->Output($file_name, 'I');


			


function getDS_TABLE($iconn,$LotID){
	

			
			
 $BillQuery = "SELECT *,
(SELECT UserName FROM `RmUsersList` WHERE ID=RMD.AgentID ) as AgentName,
(SELECT UserName FROM `RmUsersList` WHERE ID=RMD.DriverID ) as DriverName,
(SELECT UserName FROM `RmUsersList` WHERE ID=RMD.GraderID ) as GraderName,
(SELECT AbStatus FROM `QcRmAbReport` WHERE LotID=RMD.RmLotID ) as ABStatus,
(SELECT Location FROM `RmPurchaseStations` WHERE PurchaseStationID=RMD.PurchaseStationID ) as PurchaseCenter,
(SELECT PurchaseType FROM `RmPurchaseCategory` WHERE CatID=RMD.PurchaseCategoryID ) as PurchaseType,
(SELECT VehicalNumber FROM `RmVehicalDetails` WHERE ID=RMD.VehicalID ) as TruckNumber,
RBD.Remarks as BillRemarks 
FROM `RmLotDetatils` as RMD
LEFT JOIN RmBillDetails RBD on RMD.RmLotID = RBD.ArrivalID
LEFT JOIN QcRmOrgnReport QCOR on RMD.RmLotID = QCOR.LotID 
LEFT JOIN RmFinanciers RFD on RFD.ID = RBD.FinancierID 
WHERE RMD.RmLotID = '$LotID'";

$BillExec=$iconn->query($BillQuery);

$BillPanNumber= $center = $PurchaseDate = $headlessCount = $headlessQty = $LotNumber = "";
if(!empty($BillExec)){
	
	$BillRow = $BillExec->fetch_assoc();
	
	$ABStatusID=$BillRow['ABStatus'];
		
		
    $SqlABReport="SELECT * FROM `QcABOptions` WHERE OptID='$ABStatusID'";
	$StmtABReport=$iconn->query($SqlABReport);
    $RowABReport= $StmtABReport->fetch_assoc();

	
    $ABStatus = $RowABReport['Name'];
    $center = $BillRow['PurchaseCenter'];
	$PurchaseDate = $BillRow['PurchaseDate'];
	$BillPanNumber = $BillRow['PanNumber'];
	$Tdsperc = $BillRow['Tds'];
	$FarmAddress = $BillRow['FarmAddress'];
	$C_COUNT = $BillRow['C_COUNT'];
	$PurchaseCount = $BillRow['PurchaseCount'];
	$PurchaseWeight = $BillRow['PurchaseWeight'];
	$TruckNumber = $BillRow['TruckNumber'];
	$DriverName = $BillRow['DriverName'];
	$C_QTY = $BillRow['WEIGHT'];
	$RATE =$BillRow['RATE'];
	$LotNumber = $BillRow['LotNumber'];
	$TUBS = $BillRow['TUBS'];
	$FinancierName = $BillRow['FinancierName'];
	$AgentName = $BillRow['AgentName'];
	$VARITY = $BillRow['VARITY'];	
	$AccDedKg= $BillRow['Deductions'];
	$LabActDedKg= $BillRow['ActualDeductions'];
	$LabSugDedKg= $BillRow['SuggestedDeductions'];
	$PurchaseType= $BillRow['PurchaseType'];
	$GraderName= $BillRow['GraderName'];
	 $ACCOUNT_REMARKS= $BillRow['BillRemarks']; 
	
	




}
  $SqlBillBrkUpQuery = "SELECT * from RmBillBreakup WHERE  LotID = '$LotID'";
  $SqlBillBrkUpExec=$iconn->query($SqlBillBrkUpQuery);
			   
			   while($SqlBillRow = $SqlBillBrkUpExec->fetch_assoc()){
				   
				   $BillWeight+=$SqlBillRow['Weight'];
				   $BillCount=$SqlBillRow['Count'];
				   
			   }


	$content='';
	
		$content.='<h5  style="text-align:right"> AB REPORT: '.$ABStatus.'</h5>';
		$content.='<h5  style="text-align:center"></h5>';

	
	$size1=100/6;
	$size2=100/6;
	$size=100-$size1-$size2;	
	$size3=60;
	
				
				
				
	 $content .= '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="LEFT"  width="'.($size).'%"><font size="10">Bill Name: <b>'.$FinancierName.'</font></b></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Date : </font></th>
                <th  align="LEFT" width="'.($size1).'%"><font size="10"><b>'.getFormatedDate($PurchaseDate).'</b></font></th>
                

				</tr>
				 <tr>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Farmer Name:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"></font></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Cell Number:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"></font></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Village: </font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"><B>'.$FarmAddress.'</B></font></th>
				  
           		

				</tr>
				
				<tr>

                <th  align="LEFT"  width="'.($size2).'%"><font size="10">Agent Name:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"><b>'.$AgentName.'</font></b></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Cell Number:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"></font></th>
				<th  align="LEFT"  width="'.($size2).'%"><font size="10">Lot No:</font></th>
                <th  align="LEFT" width="'.($size2).'%"><font size="10"><b>'.$LotNumber.'</font></b></th>				
           		
               </tr>
				
				<tr>  
           		<th  align="LEFT"  width="50%"><font size="10">Company Count/Quality : <b>'.$BillCount.'c / '.$BillWeight.'</b> Variety :<b>'.$VARITY.' </b></font></th>
           		<th  align="LEFT"  width="50%"><font size="10"> Farmer Count/Quality : <b>'.$PurchaseCount.'c / '.$PurchaseWeight.'</b> Variety :<b>'.$VARITY.' </b></font></th>
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
           		<th  align="left"  width="50%"><font size="10"> Truck No : <b>'.$TruckNumber.'</B> </font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="14%"><font size="10"> Material Tubs </font></th>
           		<th  align="center"  width="10%"><font size="10"> Empty </font></th>
           		<th  align="center"  width="12%"><font size="10"> Total Tubs </font></th>
           		<th  align="center"  width="14%"><font size="10">Quantity </font></th>
           		<th  align="left"  width="50%"><font size="10"> Driver :<b>'.$DriverName.'</B> </font></th>
               </tr>
				
					<tr>  
           		<th  align="center"  width="14%"><font size="10"> '.$TUBS.' </font></th>
           		<th  align="center"  width="10%"><font size="10">Hon Qty  </font></th>
           		<th  align="center"  width="12%"><font size="10"> '.$BillCount .' C</font></th>
           		<th  align="center"  width="14%"><font size="10"> <B>'.$BillWeight .' Kgs</B></font></th>
           		<th  align="left"  width="50%"><font size="10"> Dispatched : <b>'.$center.'</B> </font></th>
               </tr>';

			   
			   
			  
			   
			   
			   
				
				 $content .= '<tr>  
           		<th  align="center"  width="24%"><font size="10"> <br> <br>  Driver Sign <br> </font></th>
           		<th  align="center"  width="26%"><font size="10"> <br> GRADER:<B>'.$GraderName.'<BR><br>'. $PurchaseType.' </B><br> </font></th>
           		<th  align="center"  width="25%"><font size="10"> <br> <br>  Supervisor Sign <br> </font></th>
           		<th  align="center"  width="25%"><font size="10">  <br> <br>  Agent/Farm Sign <br> </font></th>
           		
               </tr>
				
				
				
				<tr>  
           		<th  align="center"  width="100%"><font size="10"><b>For bill details </b></font></th>    
				</tr>
				
				
			   <tr>  
           		<th  align="center"  width="100%"><font size="17"><b>MATERIAL RETURNED </b></font></th>    
				</tr>';
				
			   
			   
			   $BillBrkUpQuery = "SELECT * FROM `RmBillBreakup`  left join RmDefectsList on RmBillBreakup.Description=RmDefectsList.DefectID 
									WHERE LotID='$LotID'";

			   $BillBrkUpExec=$iconn->query($BillBrkUpQuery);
			   $BillWeight=0;
			   while($BillRow = $BillBrkUpExec->fetch_assoc()){
				   
				   if($BillRow['DefectName']!=''){
					 $Description= $BillRow['DefectName'];
				   }else{
					 $Description= $BillRow['Description'];
				   }
				   
				   $BillWeight+=$BillRow['Weight'];
				   $BillPrice+=round($BillRow['Weight'] * $BillRow['Rate']);
			   
			   $content .= '<tr>  
           		<th  align="left"  width="20%"><font size="10"><B>'.$Description.'</B></font></th>
           		<th  align="right"  width="15%"><font size="10"> '.$BillRow['Count'] .'  C </font></th>
           		<th  align="right"  width="15%"><font size="10"> '.$BillRow['Weight'] .'  Kgs  </font></th>
           		<th  align="right"  width="15%"><font size="10"> '.$BillRow['Rate'] .' / Kgs  </font></th>
           		<th  align="right"  width="15%"><font size="10"> <B></b></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>'.round($BillRow['Weight'] * $BillRow['Rate']) .' /-  </b></font></th>
           		
               </tr>';
			   
			   };
			   
			   
			
			   
			   if($LabSugDedKg!=0){
				   $LabRemarks='A DEDUCTION OF RS.'.round($LabSugDedKg).'/- IS RECOMMENDED FOR QUALITY LOSS';
			   }else{
				  $LabRemarks='NILL'; 
			   }
			   
			 if($AccDedKg > 0){
				 $Sing='+';
				 
			 }else{
				 $Sing='';
			 }
			 
			   
			   $LabActDed= round($LabActDedKg*$BillWeight);
			   $AccDed= round($AccDedKg*$BillWeight);
			   
	
			   
			    $content .= '
				
				 <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Sub Total</b></font></th>
           		<th  align="center"  width="15%"><font size="10"> </font></th>
           		<th  align="center"  width="15%"><font size="10"><b>'.round($BillWeight,2).' Kgs</b></font></th>
           		<th  align="center"  width="15%"><font size="10"></font></th>
           		<th  align="center"  width="15%"><font size="10"></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>'.$BillPrice .' /- </b> </font></th>
           		
               </tr> ';
			   
			   
			  
			   		    $content.=' 
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