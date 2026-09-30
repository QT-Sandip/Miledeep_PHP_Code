<?php
//ini_set( 'display_errors', 1 );

session_start();

//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $PoID=$_GET['id'];
 $Type=$_GET['Type'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");

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
include "../SalesInculdes/SalesConstants.php"; 

	global $PoDataRow;
	
	$content='';

	$size=40;	 
	$size1=60;	
	$width=100;
		       
			  
			  $content .= '
			
			  <table border="1" cellspacing="0" cellpadding="5" align="center" width="100%">
               <tr>  
			   
<th  align="LEFT" width="100%" >
 <P align="left" style="font-size:9.75px;">
 1. Importer of record number / Foreign trade zone applicant identification number. Internal Revenue Service (IRS) 
number,<BR>
Employer Identification Number (EIN), Social Security Number (SSN), or CBP assigned number of the entity liable for <BR>
payment of all duties and responsible for meeting all statutory and regulatory requirements incurred as a result of <BR>
importation. For goods intended to be delivered to a foreign trade zone (FTZ), the IRS number, EIN, SSN, or CBP 
assigned <BR>
number of the party filing the FTZ documentation with CBP must be provided<BR>
2. Buyer. Name and address of the last known entity to whom the goods are sold or agreed to be sold. If the goods are to 
be<BR>
imported otherwise than in pursuance of a purchase, the name and address of the owner of the goods must be provided . <BR>
A
widely recognized commercially accepted identification number for this party may be provided in lieu of the name and
address.<BR>
3. Consignee number(s). Internal Revenue Service (IRS) number, Employer Identification Number (EIN), Social Security
Number (SSN), or CBP assigned number of the individual(s) or firm(s) in the United States on whose account the
merchandise is shipped.<BR>
4. Consignee. Complete name and address fo individual(s) or firm(s) in the United State whose account the merchandise 
is
shipped.<BR>
5. Ship to party. Name and address of the first deliver-to party scheduled to physically receive the goods after the goods 
have <BR>
been released from customs custody. A widely recognized commercially accepted identification number for this party may <BR>
be provided in lieu of the name and address. <BR>
6. Manufacturer (or supplier). Name and address of the entity that last manufactures, assembles, produces, or grows the <BR>
commodity or name and address of the party supplying the finished goods in the country from which the goods are <BR>
leaving. <BR>
In the alternative the name and address of the manufacturer (or supplier) that is currently required by the import laws, 
rules <BR>
and regulations of the United States (i.e., entry procedures) may be provided (this is the information that is used to create 
the <BR>
existing manufacturer identification (MID) number for entry purposes). A widely recognized commercially accepted
identification number for this party may be provided in lieu of the name and address. <BR>
7. Seller. Name and address of the last known entity by whom the goods are sold or agreed to be sold. If the goods are to <BR>
be <BR>
imported otherwise than in pursuance of a purchase, the name and address of the owner of the goods must be provided . <BR>
A <BR>
widely recognized commercially accepted identification number for this party may be provided in lieu of the name and <BR>
address.<BR>
8. Container stuffing location. Name and address(es) of the physical location(s) where the goods were stuffed into the 
container.<BR>
For break bulk shipments, as defined in § 149.1 of this part, the name and address(es) of the physical location(s) where 
the <BR>
goods were made “ship ready” must be provided. A widely recognized commercially accepted identification number for 
this <BR>
element may be provided in lieu of the name and address.
9. Consolidator (stuffer). Name and address of the party who stuffed the container or arranged for the stuffing of the 
container. <BR>
For break bulk shipments, as defined in § 149.1 of this part, the name and address of the party who made the goods 
“ship <BR>
ready” or the party who arranged for the goods to be made “ship ready” must be provided. A widely recognized <BR>
commercially accepted identification number for this party may be provided in lieu of the name and address. <BR>
10 Country of origin. Country of manufacture, production, or growth of the article, based upon the import laws, rules and <BR>
regulations of the United States. <BR>
11. Commodity HTSUS number. Duty/statistical reporting number under which the article is classified in the Harmonized  <BR>
Tariff <BR>
Schedule of the United States (HTSUS). The HTSUS number must be provided to the six-digit level. The HTSUS number <BR>
may be provided up to the 10-digit level. This element can only be used for entry purposes if it is provided at the 10-digit <BR>
level or greater by the importer of record or its licensed customs broker. In lieu of the HTSUS number a complete an <BR>
detailed description of each article contained in the shipment.</P></th>    
				
				</tr>
				
		
			  '; 
			  
	
			$content .= '</table>';


	return $content;	

}


?>