<?php
//ini_set( 'display_errors', 1 );

session_start();

//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $PoID=$_GET['id'];
 $Type=$_GET['Type'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
require_once __DIR__ . '/Includes/SaimeSinglePagePdf.php';
include ($cp."Includes/IncludePage.php");
require_once __DIR__ . '/Includes/SaimeReportData.php';

$SqlScrmPoData="SELECT * FROM ScrmPIData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortofLoading=SPC.PortId
 WHERE PoID='$PoID'"; 
 

$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$PortOfDischarge=$PoDataRow['PortOfDischarge'];


class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
				
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 15); 
		//$this->Cell(100, 0, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		//$this->Cell(0, 10, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		
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
	$pdf->SetHeaderMargin(2);	
    $pdf->SetMargins(10, 2, 10,2);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getDS_TABLE($iconn,$PoID);
	writeSaimeSinglePage($pdf, $table_data);
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
$linerName=SaimeReportData::linerName($iconn, $PoID);
	
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include "../../SalesInculdes/SalesConstants.php"; 

	global $PoDataRow;
	
$CustomInvoiceRow = SaimeReportData::proformaProcessor($iconn, $PoID);

$SlHSCode="SELECT SPGD.VarietyID,SPL.VarietyID,SPL.PreparationType, SPM.HSCode FROM `ScrmPoGradeData` SPGD LEFT JOIN ScrmPIVarietyList SPL ON SPL.VarietyID=SPGD.VarietyID LEFT JOIN ScrmPreparationMaster SPM ON SPL.PreparationType=SPM.ShortCode WHERE SPGD.InvId='$PoID'";
$ExecHSCode=$iconn->query($SlHSCode); 
WHILE($RowHSCode = $ExecHSCode->fetch_assoc()){
	
	$ArrayHSCode[]=$RowHSCode['HSCode'];
	
}  

$HSCodes=implode(",",array_unique($ArrayHSCode));	
$ExpID=$PoDataRow['ExporterID'];
$PoNo=$PoDataRow['PoNo'];
$PortOfDischarge=$PoDataRow['PortOfDischarge'];
$SupplierNm=$CustomInvoiceRow['ExpName'];
$SupplierAddres=$CustomInvoiceRow['ExpAddress'];
$ExporterData=getExporterDetails($ExpID);


//PRINT DATA
$Buyer=$PoDataRow['ConsigneeName'];
$BuyerAddress=$PoDataRow['ConsigneeAddress'];
$ConsigneeIRSNum=$PoDataRow['ConsigneeIRSNumber'];


		
	$content='';

	$size=40;	 
 
	$size1=60;	
  
			  $S_NO=1;
			  $width=40;
			  $width2=55;
			  $width_s=5;
              
			  
			  $content .= '
			  
				<h3 align="CENTER"><br><br><br>' . saimeReportHtml($iconn, $PoID, 'BrokerName', 'JOHN S JAMES CO...') . '</h3>
				<h5 align="CENTER">' . nl2br(saimeReportHtml($iconn, $PoID, 'BrokerAddress', "4500 LEEDS AVE, # 300 NORTH CHARLESTON, SC 29405\nUNITED STATES OF AMERICA\nTEL: 843-554-6400 FAX:")) . '</h5>
			  	
				<h3 align="CENTER">10 + 2 Importer&#8217;s Security Filing Data Elements</h3>
				' . ($linerName !== '' ? '<h5 align="CENTER">STEAMSHIP COMPANY: ' . htmlspecialchars(strtoupper($linerName), ENT_QUOTES, 'UTF-8') . '</h5>' : '') . '
				
				
			  <table border="1" cellspacing="0" cellpadding="5" align="center" width="100%">

			  <tr> 
               <th  align="LEFT" width="'.$width_s.'%" ><B>1</B></th>    
				   
				<th  align="LEFT" width="'.$width.'%" ><B>Manufacturer (or supplier) Name:</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$SupplierNm.'</th>
				</tr> 
				
				 <tr> 

				<th  align="LEFT" width="'.$width_s.'%" > <B></B></th>    				   
				<th  align="LEFT" width="'.$width.'%" ><B>Manufacturer (or supplier) Address:</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$SupplierAddres.'</th>
				</tr>  
				 <tr> 

				<th  align="LEFT" width="'.$width_s.'%" ><B>2</B></th>    
				    
				<th  align="LEFT" width="'.$width.'%" ><B>Seller / Shipper Name:</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$ExporterData['ExpName'].'</th>
				</tr>  
				
			   <tr> 
				<th  align="LEFT" width="'.$width_s.'%" ><B></B></th>    
				
			   <th  align="LEFT"  width="'.$width.'%"><B>Seller / Shipper Address:</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$ExporterData['ExpAddress'].'</th>
                </tr> 
				
				<tr> 
				<th  align="LEFT" width="'.$width_s.'%" ><B>3</B></th>    
				
			   <th  align="LEFT"  width="'.$width.'%"><B>Buyer Name</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$Buyer.'</th>
                </tr> 
				
				
				<tr> 

                <th  align="LEFT" width="'.$width_s.'%" ><B></B></th>    
								
			     <th  align="LEFT"  width="'.$width.'%"><B>Buyer Address:</B></th>  
			<th  align="LEFT" width="'.$width2.'%">'.$BuyerAddress.'</th>
                </tr> 
				
				<tr>
           <th  align="LEFT" width="'.$width_s.'%" ><B>4</B></th>    
				 				
			   <th  align="LEFT"  width="'.$width.'%"><B>Ship To /Consignee Name:</B></th>  
			   <th  align="LEFT" width="'.$width2.'%">'.$Buyer.'</th>
                </tr>


				<tr> 
				<th  align="LEFT" width="'.$width_s.'%" ><B></B></th>    
				
			   <th  align="LEFT"  width="'.$width.'%"><B>Ship To / Consignee Address:</B></th>  
			<th  align="LEFT" width="'.$width2.'%">'.$BuyerAddress.'</th>
                </tr> 
            
			<tr> 
               <th  align="LEFT" width="'.$width_s.'%" ><B>5</B></th>    
				
			   <th  align="LEFT"  width="'.$width.'%"><B>Container stuffing location:</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$SupplierNm.' <br> '.$SupplierAddres.'</th>
                </tr> 
           
				 <tr> 
				<th  align="LEFT" width="'.$width_s.'%" ><B>6</B></th>    
				
			   <th  align="LEFT"  width="'.$width.'%"><B>Consolidator (stuffer) Name:</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$ExporterData['ExpName'].'</th>
                </tr> 
            <tr> 
				<th  align="LEFT" width="'.$width_s.'%" ><B></B></th>    
				
			   <th  align="LEFT"  width="'.$width.'%"><B>Consolidator (stuffer) Address:</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$ExporterData['ExpAddress'].'</th>
                </tr> 
           
				                

				<tr> 
				<th  align="LEFT" width="'.$width_s.'%" ><B>7</B></th>    
				
				<th  align="LEFT" width="'.$width.'%" ><B>Importer of Record Number:</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">' . saimeReportHtml($iconn, $PoID, 'ImporterCode', '044601-01124') . '</th>
				</tr>   

				<tr> 
 <th  align="LEFT" width="'.$width_s.'%" ><B>8</B></th>    				
   <th  align="LEFT" width="'.$width.'%" ><B>Consignee Number:</B></th>    
		<th  align="LEFT" width="'.$width2.'%">'.$ConsigneeIRSNum.'</th>
				</tr>   

				<tr> 
<th  align="LEFT" width="'.$width_s.'%" ><B>9</B></th>    				
				<th  align="LEFT" width="'.$width.'%" ><B>Country of Origin:</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">' . saimeReportHtml($iconn, $PoID, 'CountryOfOrigin', 'INDIA') . '</th>
				</tr>   

				<tr> 
				<th  align="LEFT" width="'.$width_s.'%" ><B>10</B></th>    
				  
				<th  align="LEFT" width="'.$width.'%" ><B>Commodity HTSUS Number:</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$HSCodes.'</th>
				</tr>   
               
			   
				
				
			  '; 
			  
	
			$content .= '</table>';


	return $content;	

}

?>