<?php

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $PoID=$_GET['id'];
  $Type=$_GET['Type'];
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."SalesInculdes/SalesConstants.php");
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Includes/Globals.php");

include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");



	
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
$file_name='Cargo Dispatch Details-'.$PoID.'.pdf';

ob_end_clean();
if($Type==''){
$Type='I';
}else{
	$Fname=__DIR__ ."/TempFolder/";
	$Fname=$_SERVER['DOCUMENT_ROOT']."SalesNew/Reports/TempFolder/";
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
PortOfLoadingCountry,SPC.Name as PortOfLoading 
FROM $ScrmCustomInvoices as SCV 
left join $ScrmShipmentDispatchDetails  as SD on SD.PIID=SCV.PiID

  left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortOfLoading
 left JOIN $TblScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN $TblScrmCountriesList as SL on SL.id=SPC.Country

 WHERE SCV.PiID='$PoID'"; 

 /*echo  $CustomInvoiceSql;
exit;*/
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 

	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
	$SqlScrmPoData="SELECT * FROM $TblScrmPIData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();

/*$CurrentMonth = strftime("%m");  
if($PoDataRow['ExporterID']==5){		
if($CurrentMonth<=3){
	
 $NxtYear = strftime("%y");  	
 $CurrentYear = strftime("%y")-1;  	
	
} 
else{ 
 $CurrentYear = strftime("%Y");  	
 $NxtYear = strftime("%y")+1;  	
}
		}
		else{		
if($CurrentMonth<=3){
	
 $NxtYear = strftime("%y");  	
 $CurrentYear = strftime("%Y")-1;  	
	
} 
else{ 
 $CurrentYear = strftime("%Y");  	
 $NxtYear = strftime("%y")+1;  	
}
		}*/
		
$ExpID=$PoDataRow['ExporterID'];
$PoNo=$PoDataRow['PoNo'];
$Buyer=$PoDataRow['ConsigneeName'];

$ExporterData=getExporterDetails($ExpID);
$PackSealNo=$CustomInvoiceRow['PackSealNo'];
$SealNo=$CustomInvoiceRow['SealNo'];
$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
$CInvCode=$CustomInvoiceRow['CInvCode'];
$SBNo=$CustomInvoiceRow['SBNo'];
$SBDate=$CustomInvoiceRow['SBDate'];
$VehicleNumber=$CustomInvoiceRow['VehicleNumber'];
 $EWayBillNo=$CustomInvoiceRow['EWayBillNo'];
 $PortOfLoading=$CustomInvoiceRow['PortOfLoading'];
 $SealTime=$CustomInvoiceRow['SealTime'];
 $SealTime=date('d-m-Y H:i:s',strtotime($SealTime));
 IF($SealTime=='01-01-1970 00:00:00'){
	$SealTime=''; 
 }
 ELSE{
	$SealTime; 
 }
 

$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

$SqlPOgradeData="SELECT * FROM `$ScrmCIGradeData` where InvId='$PoID'  ";
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
				 <th  align="center" width="'.$width_s.'%">'.$SBNo.'/'.$SBDate.'</th>
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

				
			  '; 
			  
	
		$content .= '</table>';


	return $content;

}

?>