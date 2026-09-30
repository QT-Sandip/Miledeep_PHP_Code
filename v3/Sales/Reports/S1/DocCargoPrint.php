<?php
// ini_set( 'display_errors', 1 );

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $PoID=$_GET['id'];
  $Type=$_GET['Type'];
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."SalesInculdes/SalesConstants.php");
//include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Includes/Globals.php");

include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

include ($cp."Includes/IncludePage.php");

$SqlScrmPoData="SELECT * FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
 left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID

 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
	
	//require_once('../tcpdf/tcpdf.php'); 
	

class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
				
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 15);
		$this->Cell(190, 0, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	

    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Cargo Dispatch Details : '.$Header);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 10, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$PoID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);

  
//$file_name=$ExporterData['InvShortCode'].'_'.$PoDataRow['InvNo'].'.pdf';
//$file_name='Cargo Dispatch Details-'.$PoID.'.pdf';

$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];
 
$file_name=$InvShortCode.'-Cargo-'.$CInvNo.'-'.$FinancialYear.'.pdf';
ob_end_clean();

if($Type==''){
$Type='I';
}else{
	$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}$pdf->Output($file_name, $Type);
//$pdf->Ln();


function getDS_TABLE($iconn,$PoID){
	
	
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
include "../SalesInculdes/SalesConstants.php"; 
	
	
			 	  
  /*$CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SealTime,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SCV.SBDate,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading FROM $ScrmCustomInvoice as SCV 
 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SCV.PortofDischarge
 left JOIN $TblScrmPortCities as SP on SP.PortId=SCV.PortOfLoading
 left JOIN $TblScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN $TblScrmCountriesList as SL on SL.id=SP.Country

 WHERE PiID='$PoID'"; */ 
  //echo 'hiii';
 $CustomInvoiceSql="SELECT *,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SL.name as 
PortOfLoadingCountry,SPC.Name as PortOfLoading ,SCV.ContainerNo
FROM ScrmCustomInvoices as SCV
left JOIN ScrmPoData as SSD on SSD.PoID=SCV.PiID
 left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortOfLoading
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN ScrmCountriesList as SL on SL.id=SPC.Country
 left JOIN ScrmISFDetails as ISF on ISF.PIID=SCV.PiID
 left JOIN ScrmShippingBillDetails as SSBD on SSBD.InvID=SCV.PiID

 WHERE SCV.PiID='$PoID'"; 

 /*echo  $CustomInvoiceSql;
exit;*/
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 

	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
global $PoDataRow;
		
$ExpID=$PoDataRow['ExporterID'];
$PoNo=$PoDataRow['PoNo'];
$Buyer=$PoDataRow['ConsigneeName'];
$TransportedDate=$PoDataRow['TransportedDate'];

$ExporterData=getExporterDetails($ExpID);
$PackSealNo=$CustomInvoiceRow['CustomSealNo'];
$SealNo=$CustomInvoiceRow['LinerSealNo'];
$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
//$CInvCode=$CustomInvoiceRow['CInvCode'];
$SBNo=$CustomInvoiceRow['ShippingBillNo'];
$SBDate=$CustomInvoiceRow['ShippingBillDate'];
$DriverName=$CustomInvoiceRow['DriverName'];
$VehicleNumber=$CustomInvoiceRow['VehicelNumber'];
 $EWayBillNo=$CustomInvoiceRow['IRNNumber'];
 $PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
 //$SealTime=getFormatedDateTimeEmpty($TransportedDate.' '.$CustomInvoiceRow['SealTime']); 
 $SealTime=getFormatedDate($TransportedDate).' '. $CustomInvoiceRow['SealTimeNDt']; 
 //
 /*$SealTime=date('H:i:s',strtotime($CustomInvoiceRow['SealTime']));
 IF($SealTime=='01-01-1970 00:00:00'){
	$SealTime=''; 
 }
 ELSE{
	$SealTime; 
 }*/
 $BuyerID = $PoDataRow['BuyerID'];
	$ConsigneeID = $PoDataRow['ConsigneeID'];


//  10-02-2024 durgaPrasad
 if ($BuyerID == '' || $BuyerID == 0) {
	$Buyer = $PoDataRow['ConsigneeName'];
	//$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];
} else if ($ConsigneeID != $BuyerID) {
	$Buyer = $PoDataRow['BuyerName'];
	//$ConsigneeAddress = $PoDataRow['BuyerAddress'];
} else {
	$Buyer = $PoDataRow['ConsigneeName'];
	//$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];

}
// 10-02-204
$InvNO=$CustomInvoiceRow['CustomsInvNo'];

$SqlHsCode="SELECT DISTINCT HSCode FROM `ScrmPoGradeData` as SPGD 
 left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
 left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType 
 and SPM.SpeciesShortCode=SPVL.Species WHERE InvId='$PoID'";
 $StmtHsCode=$iconn->query($SqlHsCode);
 while($RowHsCode=$StmtHsCode->fetch_assoc()){
	$ArrayHsCode[]=$RowHsCode['HSCode'];
	$CInvCode = implode(',', $ArrayHsCode);
  
 }

$SqlPOgradeData="SELECT * FROM `ScrmCIGradeData` where InvId='$PoID'  ";
$StmtPOgradeData=$iconn->query($SqlPOgradeData);

WHILE($RowPOgradeData = $StmtPOgradeData->fetch_assoc()){

$MCS=	$RowPOgradeData['Mcs']; 
   $TotalMcs+=$MCS; 
}

 
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=50;
			  $width_s=50;
			  $width_w=$width+5;
              
			  
			  $content .= '
			  <h3 align="center"><br><br><br><br><br><br>CARGO DISPATCH DETAILS</h3>
			  <table border="1" cellspacing="0" cellpadding="5" align="center" >
			   <tr>  

           		

                <th  align="center"  width="'.$width_s.'%"><B>INVOICE NUMBER</B></th>  
				 <th  align="center" width="'.$width_s.'%">'.$InvNO.'</th>
                </tr> 
           
				                <tr> 

				<th  align="center" width="'.$width.'%" ><B>RFID
				SEAL NUMBER</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$PackSealNo.'</th>
				</tr> 

				<tr> 

				<th  align="center" width="'.$width.'%" ><B>CONTAINER NUMBER</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$ContainerNumber.'</th>
				</tr>  <tr> 

				<th  align="center" width="'.$width.'%" ><B>VEHICLE NUMBER</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$VehicleNumber.'</th>
				</tr>  
				
				<tr> 

				<th  align="center" width="'.$width.'%" ><B>PORT OF LOADING</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$PortOfLoading.'</th>
				</tr>  
				
				<tr> 

				<th  align="center" width="'.$width.'%" ><B>SHIPPING BILL NUMBER/DATE</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$SBNo.'/'.getFormatedDateNA($SBDate).'</th>
				</tr>  
				<tr> 

				<th  align="center" width="'.$width.'%" ><B>BUYER</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$Buyer.'</th>
				</tr> 
<tr> 

				<th  align="center" width="'.$width.'%" ><B>PO NUMBER</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$PoNo.'</th>
				</tr> 
<tr> 

				<th  align="center" width="'.$width.'%" ><B>LINER SEAL NUMBER</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$SealNo.'</th>
				</tr> 
<tr> 

				<th  align="center" width="'.$width.'%" ><B>CARTONS</B></th>    
				 <th  align="center" width="'.$width_s.'%">CS ('.$TotalMcs.')HS - ('.$CInvCode.')</th>
				</tr> 
<tr> 

				<th  align="center" width="'.$width.'%" ><B>E-WAY BILL NUMBER</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$EWayBillNo.'</th>
				</tr> 
<tr> 

				<th  align="center" width="'.$width.'%" ><B>SEAL TIME</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$SealTime.'</th>
				</tr> 
<tr> 

				<th  align="center" width="'.$width.'%" ><B>DRIVER NAME</B></th>    
				 <th  align="center" width="'.$width_s.'%">'.$DriverName.'</th>
				</tr> 

				
			  '; 
			  
	
		$content .= '</table>';


	return $content;

}

?>