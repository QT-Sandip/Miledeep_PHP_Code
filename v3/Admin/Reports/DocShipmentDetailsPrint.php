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
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	

    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Shipment Details : '.$Header);  
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
$file_name='Shipment Details-'.$PoID.'.pdf';

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
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include "../SalesInculdes/SalesConstants.php"; 

		 	  
  $CustomInvoiceSql="SELECT * FROM $ScrmCustomInvoices as SCI
  left join`$ScrmShipmentDetails` as  SSD on SSD.PiID=SCI.PiID
  left join $ScrmShipmentDispatchDetails as SD on SD.PIID=SSD.PiID

  WHERE SCI.PiID='$PoID'"; 

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

		
$ExpID=$PoDataRow['ExporterID'];
$PoNo=$PoDataRow['PoNo'];
$Buyer=$PoDataRow['ConsigneeName'];

$ExporterData=getExporterDetails($ExpID);
$PackSealNo=$CustomInvoiceRow['PackSealNo'];
$SealNo=$CustomInvoiceRow['SealNo'];
$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
$CInvCode=$CustomInvoiceRow['CInvCode'];
$VehicalNumber=$CustomInvoiceRow['VehicalNumber'];
$SBNo=$CustomInvoiceRow['SBNo'];
$BLNo=$CustomInvoiceRow['BLNo'];
$Discount=$CustomInvoiceRow['Discount'];
$IGST=$CustomInvoiceRow['IGST'];


/*$ETD=date('d-m-Y',strtotime($CustomInvoiceRow['ETD']));
$ETA=date('d-m-Y',strtotime($CustomInvoiceRow['ETA']));*/
 $ETD=getFormatedDateNA($CustomInvoiceRow['ETD']);
$ETA=getFormatedDateNA($CustomInvoiceRow['ETA']);
		

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
		
		$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
			 $SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `$ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();
			
			$NetWgtKgs = $RowTotal['NetWgtKgs'];
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
			if( $PoDataRow['QtyUnit']=='KGS'){				
				$Weight = $RowTotal['NetWgtKgs'];	
				//$Weight = $RowTotal['NetWgtLbs']*0.454;	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'];
				//$Weight = $RowTotal['NetWgtKgs'];
				$Price = $RowTotal['PriceLbs'];
			}
			
		 IF($IGST=='DISCOUNT'){
				
				$DiscountAmount=($Price*$Discount)/100;
				$TotalDiscount=$Price-$DiscountAmount;
		 }
		 ELSE{
			$TotalDiscount=$Price; 
		 }
				

		
		
	$content='';

	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=40;
			  $width_s=10;
              
			  
			  $content .= '
			  
			  	<h3 align="CENTER"><br><br><br><br><br><br>SHIPMENT DETAILS <br></h3>
			  	<h4 align="CENTER">SHIPMENT DETAILS FOR PO NO  #         <B><U> '.$PoNo.'</U></B></h4>

			  
			  <table border="1" cellspacing="0" cellpadding="5" align="center" width="100%">

			  <tr> 

				<th  align="center" width="'.$width_s.'%" ><B>1</B></th>    
				<th  align="center" width="'.$width.'%" ><B>PO NO</B></th>    
				 <th  align="center" width="'.$width.'%">'.$PoNo.'</th>
				</tr>  
				 <tr> 

				<th  align="center" width="'.$width_s.'%" ><B>2</B></th>    
				<th  align="center" width="'.$width.'%" ><B>ETD</B></th>    
				 <th  align="center" width="'.$width.'%">'.$ETD.'</th>
				</tr>  
				 <tr> 

				<th  align="center" width="'.$width_s.'%" ><B>3</B></th>    
				<th  align="center" width="'.$width.'%" ><B>ETA</B></th>    
				 <th  align="center" width="'.$width.'%">'.$ETA.'</th>
				</tr>  
				
			   <tr> 
				<th  align="center" width="'.$width_s.'%" ><B>4</B></th>    
			   
			   <th  align="center"  width="'.$width.'%"><B>INVOICE NO</B></th>  
				 <th  align="center" width="'.$width.'%">'.$InvNO.'</th>
                </tr> 
            <tr> 
				<th  align="center" width="'.$width_s.'%" ><B>5</B></th>    
			   
			   <th  align="center"  width="'.$width.'%"><B>INVOICE VALUE</B></th>  
				 <th  align="center" width="'.$width.'%">'.number_format($TotalDiscount,2).'</th>
                </tr> 
           
				 <tr> 
				<th  align="center" width="'.$width_s.'%" ><B>6</B></th>    
			   
			   <th  align="center"  width="'.$width.'%"><B>CASES SHIPPED</B></th>  
				 <th  align="center" width="'.$width.'%">'.$TotalMcs.'</th>
                </tr> 
            <tr> 
				<th  align="center" width="'.$width_s.'%" ><B>7</B></th>    
			   
			   <th  align="center"  width="'.$width.'%"><B>LBS</B></th>  
				 <th  align="center" width="'.$width.'%">'.number_format($NetWgtLbs,3).'</th>
                </tr> 
           
				                

				<tr> 
				<th  align="center" width="'.$width_s.'%" ><B>8</B></th>    

				<th  align="center" width="'.$width.'%" ><B>CONTAINER NUMBER</B></th>    
				 <th  align="center" width="'.$width.'%">'.$ContainerNumber.'</th>
				</tr>   

				<tr> 
				<th  align="center" width="'.$width_s.'%" ><B>9</B></th>    

				<th  align="center" width="'.$width.'%" ><B>BL NUMBER</B></th>    
				 <th  align="center" width="'.$width.'%">'.$BLNo.'</th>
				</tr>   

				<tr> 
				<th  align="center" width="'.$width_s.'%" ><B>10</B></th>    

				<th  align="center" width="'.$width.'%" ><B>VESSEL NAME</B></th>    
				 <th  align="center" width="'.$width.'%">'.$VesselName.'</th>
				</tr>   

				<tr> 
				<th  align="center" width="'.$width_s.'%" ><B>11</B></th>    

				<th  align="center" width="'.$width.'%" ><B>VOYAGE NO</B></th>    
				 <th  align="center" width="'.$width.'%">'.$VOY.'</th>
				</tr>   

				
			  '; 
			  
	
			$content .= '</table>';


	return $content;	

}

?>