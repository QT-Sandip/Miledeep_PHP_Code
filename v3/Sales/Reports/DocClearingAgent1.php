<?php
//ini_set( 'display_errors', 1 );

session_start();

//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $PoID=$_GET['id'];
 $Type=$_GET['Type'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");

class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
				
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 15);
		$this->Cell(190, 0, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		$this->Cell(0, 10, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 15, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	

    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Jhon A.Steer : '.$Header);  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 10, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 20);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$PoID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);

$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];
 
$file_name=$InvShortCode.'-SD-'.$CInvNo.'-'.$FinancialYear.'.pdf';

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
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include "../SalesInculdes/SalesConstants.php"; 

	global $PoDataRow;
	
	
$SqlScrmPoData="SELECT * FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN AdminExporterDetails as AED on SPD.ProcessorID=AED.ExpID
 left JOIN ScrmBuyerDetails as SBD on SPD.BuyerID=SBD.BuyerID
 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];

	
  $CustomInvoiceSql="SELECT * FROM $ScrmCustomInvoices as SCI
  left join`$ScrmShipmentDetails` as  SSD on SSD.PiID=SCI.PiID
  left join $ScrmShipmentDispatchDetails as SD on SD.PIID=SSD.PiID
  WHERE SCI.PiID='$PoID'"; 

$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
	
		
$ExpID=$PoDataRow['ExporterID'];
$ImporterID=$PoDataRow['ImporterID'];
$PoNo=$PoDataRow['PoNo'];
$Buyer=$PoDataRow['ConsigneeName'];
$InvDate=$PoDataRow['InvDate'];
$LoadingPort=$PoDataRow['Name'];
$ConsigneeName=$PoDataRow['ConsigneeName'];
$ConsigneeAddress=$PoDataRow['ConsigneeAddress'];
$PoNo=$PoDataRow['PoNo'];
$ReferenceNo=$PoDataRow['ReferenceNo'];
$PoDate=getFormatedDateNA($PoDataRow['PoDate']);
$ProcsrName=$PoDataRow['ExpName'];
$ProcsrAddress=$PoDataRow['ExpAddress'];	
$BuyerName=$PoDataRow['BuyerName'];	
$BuyerAddress=$PoDataRow['BuyerAddress'];	
	

$SqlPoGradeData="SELECT 
SUM(Mcs) as MCS,Brand FROM `ScrmPoGradeData`  WHERE InvId='$PoID'"; 
$StmtPoGradeData=$iconn->query($SqlPoGradeData); 
$RowPoGradeData = $StmtPoGradeData->fetch_assoc();


$SqlImporterData="SELECT 
* FROM `AdminExporterDetails`  WHERE ExpID='$ImporterID'"; 
$StmtImporterData=$iconn->query($SqlImporterData); 
$RowImporterData = $StmtImporterData->fetch_assoc();

$ImporterName=$RowImporterData['ExpName'];
$ImportAddress=$RowImporterData['ExpAddress'];
$ImportPhnNo=$RowImporterData['ExpPhoneNumber'];

$MCS=$RowPoGradeData['MCS'];
$Brand=$RowPoGradeData['Brand'];

$PaymentData=getPaymentValues($iconn,$PoID);

$ExporterData=getExporterDetails($ExpID);
$PackSealNo=$CustomInvoiceRow['PackSealNo'];
$SealNo=$CustomInvoiceRow['SealNo'];
$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
$CInvCode=$CustomInvoiceRow['CInvCode'];
$VehicalNumber=$CustomInvoiceRow['VehicalNumber'];
$SBNo=$CustomInvoiceRow['SBNo'];
$BLNo=$CustomInvoiceRow['BLNo'];
$PlaceOfRecipt=$CustomInvoiceRow['PlaceOfRecipt'];
$FinalDestination=$CustomInvoiceRow['FinalDestination'];
$BLDate=getFormatedDateNA($CustomInvoiceRow['BLDate']);
$Discount=$CustomInvoiceRow['Discount'];
$IGST=$CustomInvoiceRow['IGST'];

$ETD=getFormatedDateNA($CustomInvoiceRow['ETD']);
$ETA=getFormatedDateNA($CustomInvoiceRow['ETA']);
		
$ExpName=$ExporterData['ExpName'];
$ExpAddress=$ExporterData['ExpAddress'];
$ExpPhnNo=$ExporterData['ExpPhoneNumber'];

$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

$SqlPOgradeData="SELECT * FROM `$ScrmCIGradeData` where InvId='$PoID'  ";
$StmtPOgradeData=$iconn->query($SqlPOgradeData);

WHILE($RowPOgradeData = $StmtPOgradeData->fetch_assoc()){

$MCS=	$RowPOgradeData['Mcs']; 
   $TotalMcs+=$MCS; 
}

 $SqlTenPlusTwo="SELECT * FROM `$ScrmLogisticsDetails` as STD LEFT JOIN $ScrmLogisticsMaster 
as SL on SL.ID=STD.ConsolidatorNameAddress WHERE PiID='$PoID'"; 
			$StmtTenPlusTwo=$iconn->query($SqlTenPlusTwo);
		$RowTenPlusTwo=$StmtTenPlusTwo->fetch_assoc();
		
		
		$VesselName=$RowTenPlusTwo['VesselName'];
		$VOY=$RowTenPlusTwo['VOY'];
		
		
		
		
	$content='';

	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=60;
			  $width_s=40;
              
			  
			  $content .= '
			  
			  <table border="1" cellspacing="0" cellpadding="3" align="center" width="100%">


               <tr>  	   
				 <th  align="center" width="100%" ><B>John A. Steer Co.</B></th>    
				</tr>
				
			  <tr> 	   
				 <th  align="LEFT" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%">IMPORTER "ISF" WORKSHEET</th>
			  </tr>
			  
			  <tr> 			   
				<th  align="RIGHT" width="'.$width.'%" >VESSEL :</th>    
				<th  align="LEFT" width="'.$width_s.'%">'.$PoNo.'</th>
			  </tr>  
				
			<tr> 	   
				<th  align="RIGHT" width="'.$width.'%" >CUT-OFF:</th>    
				<th  align="LEFT" width="'.$width_s.'%">--</th>
			</tr> 

            <tr> 	   
				<th  align="RIGHT" width="'.$width.'%" >Estimated Loading Date</th>    
				<th  align="LEFT" width="'.$width_s.'%">'.$BLDate.'</th>
			</tr> 			
				
			   <tr> 
				
			   <th  align="RIGHT"  width="'.$width.'%">Estimated DTA to Discharge</th>  
				 <th  align="LEFT" width="'.$width_s.'%">'.$ETA.'</th>
                </tr> 
				<tr> 
				
			   <th  align="RIGHT"  width="'.$width.'%">P.O. NUMBER</th>  
				 <th  align="LEFT" width="'.$width_s.'%">'.$PoNo.'</th>
                </tr> 
				<tr> 
				
			   <th  align="RIGHT"  width="'.$width.'%">Container Number</th>  
				 <th  align="LEFT" width="'.$width_s.'%">'.$ContainerNumber.'</th>
                </tr> 
				
				<tr> 
				
			   <th  align="LEFT"  width="100%">
			   BILL OF LADING NUMBER REQUIREMENT: PLEASE NOTE, US CUSTOMS REQUIRES THE AMS <BR>
			   HOUSE BILL OF LADING.IF AN AMS HOUSE BILL OF ADING DOES NOT EXIST FOR <BR>
			   THE SHIPMENT,THEN THE CARRIERS MASTER BILL OF LADING IS REQUIRED.</th>  
				
                </tr> 
				
				<tr> 							     
				 <th  align="LEFT" width="100%"></th>
                </tr>
				<tr> 							     
				 <th  align="LEFT" width="100%"></th>
                </tr>
				
				
				
				
				<tr> 				
			     <th  align="CENTER"  width="'.$width.'%">1.AMS BILL OF LADING IS REQUIRED</th>  
				 <th  align="LEFT" width="'.$width_s.'%"></th>
                </tr> 
				
				
				<tr> 				
			     <th  align="CENTER"  width="'.$width.'%">AMS HB/L (House/NVOCC Bill of Lading):</th>  
				 <th  align="LEFT" width="'.$width_s.'%"></th>
                </tr> 
				
				<tr> 				
			     <th  align="CENTER"  width="'.$width.'%">AMS MB/L (Master/Carrier Bill of Lading):</th>  
				 <th  align="LEFT" width="'.$width_s.'%">,'.$BLDate.'</th>
                </tr> 
				
			   <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   
			   <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >2.IMPORTER OF RECORD:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ImporterName.'</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ImportAddress.'</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >CONTACT:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ImportPhnNo.'</th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >TAX/EIN#</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>			   
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   
			    <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >3.SELLER:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ExpName.'</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ExpAddress.'</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >CONTACT:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ExpPhnNo.'</th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   
			       <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >3.BUYER:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$BuyerName.'</th>
			   </tr>
			   
			   
				
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$BuyerAddress.'</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >CONTACT:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
				    <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >5.CONSIGNEE:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ConsigneeName.'</th>
			   </tr>
				
			

				
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ConsigneeAddress.'</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >CONTACT:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   
			    <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >TAX/EIN#</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   
			        <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >6.MANFACTURER/SUPPLIER</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ProcsrName.'</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ProcsrAddress.'</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >CONTACT:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   
			     <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >7.SHIP TO PARTY:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$BuyerName.'</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$BuyerAddress.'</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >CONTACT:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >COUNTRY OF ORIGIN</th>    
				 <th  align="LEFT" width="'.$width_s.'%">INDIA</th>
			   </tr>
			   <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >COMMODITY HTS#:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			    <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			    <tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >10.CONTAINER STUFFING LOCATION</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ProcsrName.'</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ProcsrAddress.'</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   	<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   	<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >11.CONSOLIDATOR</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ExpName.'</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%">'.$ExpAddress.'</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="'.$width.'%" >ADDRESS:</th>    
				 <th  align="LEFT" width="'.$width_s.'%"></th>
			   </tr>
			   
			  '; 
			  
	
			$content .= '</table>';


	return $content;	

}

?>