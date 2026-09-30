<?php
  $PoID=$_GET['id'];
//ini_set( 'display_errors', 1 );
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");

 $ID=$_GET['id'];
 $Type=$_GET['Type'];
 
 
 $StyleTbl='Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 10px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';
  
  $StyleTd='Style=" margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;"';

$SqlStatus="SELECT * FROM ScrmPIData  WHERE PoID='$PoID'";
$StmtStatus=$iconn->query($SqlStatus); 
$PoStatus = $StmtStatus->fetch_assoc();
$Status=$PoStatus['Status']; 


class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
if($Status==0){
	$StatusTerm="(Approval Pending)";
}else if($Status==2){
	$StatusTerm="(cancelled)";
}else if($Status==4){
	$StatusTerm="(Rejected)";
}

	global $Header;		
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 8);
		$this->Cell(110, 0, 'PROFORMA INVOICE '.$Header, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
		 $this->SetTextColor(255,0,0);
		$this->SetFont('helvetica', '', 8);
		$this->Cell(10, 0, ''.$StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');	
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}


	
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	
	
    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Proforma Invoice: '.$Header);  
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
	
	
	
	$table_data=getProformaDetails($iconn,$ID);
	$pdf->writeHTML($table_data);
	 $pdf->SetFont('helvetica', '', 6);


$file_name=getPIInvoiceNumber($iconn,$ExpID,$PoID); 
$file_name = str_replace('/','', $file_name );;
$file_name=$Fname.$file_name;
 


ob_end_clean();
if($Type==''){
$Type='I';
}else{
	$Fname=__DIR__ ."/TempFolder/";
	$Fname=$_SERVER['DOCUMENT_ROOT']."SalesNew/Reports/TempFolder/";
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );
	$file_name=$Fname.$file_name;
}


$pdf->Output($file_name, $Type);


function getProformaDetails($iconn,$ID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
					
	$content='';
	                                                                  

	$size=45;	
	$size1=55;	


		$AryData=getReportData($iconn,$ID);
		
				
		
		$ExporterID=$AryData['ExporterID'];
		$ExporterName=$AryData['ExporterName'];
		$ExporterAddress=$AryData['ExporterAddress'];
		$ExporterUsfdaNo=$AryData['ExporterUsfdaNo'];
		$DestinationCountryId=$AryData['DestinationCountryId'];
		$ExporterIeCode=$AryData['ExporterIeCode'];
		$ExporterGstNo=$AryData['ExporterGstNo'];
		
		$ConsigneeID=$AryData['ConsigneeID'];
		$BuyerID=$AryData['BuyerID'];
		$ConsigneeName=$AryData['ConsigneeName'];
		$ConsigneeAddress=$AryData['ConsigneeAddress'];
		$BuyerName=$AryData['BuyerName'];
		$BuyerAddress=$AryData['BuyerAddress'];
		
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$FinalDestination=$AryData['FinalDestination'];
		
		
		$InvNo=$AryData['InvNo'];
		$InvDate=$AryData['InvDate'];
		$PoNo=$AryData['PoNo'];
		$PoDate=$AryData['PoDate'];
		
		$ProcessorName=$AryData['ProcessorName'];
		$ProcessorAddress=$AryData['ProcessorAddress'];
		$ProcessorUsfdaNo=$AryData['ProcessorUsfdaNo'];
		$ProcessorFEI=$AryData['ProcessorFEI'];
		$ProcessorApprovalNo=$AryData['ProcessorApprovalNo'];
		
		$ShowShippingTerms=$AryData['ShowShippingTerms'];
		$ShippingTerms=$AryData['ShippingTerms'];
		$ProcessorIeCustomsNo=$AryData['ProcessorIeCustomsNo'];
		$ExporterRecAdd=$AryData['ExporterRecAdd'];
		$PaymentTerms=$AryData['PaymentTerms'];
		$PortOfDischarge=$AryData['PortOfDischarge'];
		$DestinationCountry=$AryData['DestinationCountry'];
		$DeliveryTerms=$AryData['DeliveryTerms'];
		$ShowDeliveryTerms=$AryData['ShowDeliveryTerms'];
		$QtyUnit=$AryData['QtyUnit'];
		$TotalMcs=$AryData['TotalMcs'];
		$ShipByDate=$AryData['ShipByDate'];
		$SignURL=$AryData['SignURL'];
		$AuthSign=$AryData['AuthSign'];
		$PreparedBy=$AryData['PreparedBy'];
		$BankID=$AryData['BankID'];
		$showExpUsFda=$AryData['showExpUsFda'];
		$showExpIECode=$AryData['showExpIECode'];
		$showExpGSTNo=$AryData['showExpGSTNo'];
		
		$showProUsFda=$AryData['showProUsFda'];
		$showProFEI=$AryData['showProFEI'];
		


 
	 $content .= '<table border="1" cellspacing="0" cellpadding="0">  
				
				 <tr>  
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';
				
				$content .= '
				<tr>
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>'.$ExporterName.' 
				<BR> '.$ExporterAddress.'<BR>';
				
				if($showExpUsFda){
				$content .= '	USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>';
				}
				
				if($showExpIECode){
				$content .= ' IE CODE  : '.$ExporterIeCode;
								
				}
				
				if($showExpGSTNo){
				$content .= '<BR> GST NO  : '.$ExporterGstNo;
				}
				
				
				$content .= '</font></th></tr>';
				
		
		
		
		if($ConsigneeID!=$BuyerID){
		$content .= '
				<tr> 
				<th  align="LEFT" ><font size="7"><b><u>Applicant</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				<br><b><u>Consignee</u></b><br>
				'.$BuyerName.',<br>'.$BuyerAddress.'
				</font></th>
				</tr>';
			}else{
				$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>Applicant/Ultimate Consignee</u></b>
				<br>'.$ConsigneeName.',<br>'.$ConsigneeAddress.'
				
				
				</font></th>
				</tr>';	
			}
				
			$content .= '	<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">ANY PORT, INDIA</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR>  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">ANY PORT, INDIA</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">'.$PortOfDischarge.'</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">'.$FinalDestination.'</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				<br> '.$InvNo.',Dated '.$InvDate.' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Buyer`s Order No. & Date. </b>
				<br> '.$PoNo.$PoDate.' </font></th>
				</tr>';
				
				
				
				
				$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# '.$ProcessorApprovalNo.'</b>
				<br>
				<u><b> Packer</u> :</b><br> '.$ProcessorName.'<br> '.$ProcessorAddress.' ';
				
				if($showProUsFda){			
				$content .= '<br> USFDA REGISTRATION NO  : '.$ProcessorUsfdaNo;
				}
				
				if($showProFEI){			
				$content .= '<BR> FEI # :  '.$ProcessorFEI;
				}
		
				$content .= '</font></th></tr>';
	
	
	
			
			
			
			
			
				
				
	
			 if($ShowShippingTerms){
				
				$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br>
				<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN # '.$ProcessorIeCustomsNo.'
				<br> '.$ExporterRecAdd.' </font></th>
				</tr>';
				}else{
				
			 $content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br></font></th>
				</tr>';
				}
				
			$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  '.$DestinationCountry.'</b></font></th>
				</tr>
				<tr> 
				<th  align="LEFT"  width="100%" ><font size="7"><b><u> Terms of Delivery and Payment:</u>
				</b><br><BR> ' .$PaymentTerms .' <br>
				 SHIPMENT TERM : '.$ShippingTerms .' '.strtoupper($PortOfDischarge).', '.
				strtoupper($DestinationCountry).' ';
				
				if($ShowDeliveryTerms){				
				$content .= '('.$DeliveryTerms.')<br>';
				}
				
				
				$content .= '</font> </th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> 
				
				
				
				
				
				
				<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> '.$TotalMcs.'</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br><b align="center">1 X 40 FCL</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br><b align="center">'.$SealNo.'</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in '.$QtyUnit.' </font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>USD/'.$QtyUnit .' '.$ShippingTerms .' <br><b>'.strtoupper($PortOfDischarge).'</b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in USD '.$ShippingTerms .'<br><b> '.strtoupper($PortOfDischarge).'</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>
				
				';
				
				$description_head='<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
				$W_SPACE='<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
				$Whitspace='<b style="color:white"> yuhju</b>';
				$FontSize="7";
				//$TOTAL_MCS=0;
			$content.='<tr  >
				
			<table border="0" cellspacing="0" cellpadding="1">
			
			'
			;
			

			  $SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmPoGradeData` 
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand"; 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
			while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){
				
				
				$Variety=$VarietyMcsRow['Variety'];
				$Grade=$VarietyMcsRow['Grade'];
				$Brand=$VarietyMcsRow['Brand'];
				 $BAP=	$VarietyMcsRow['BAP']; 
				 $PackingStyleID=	$VarietyMcsRow['PackingStyleID']; 
  if($BAP=='NA'){
	 $BAP='';  
   }
  ELSE{
	$BAP=$BAP;  
  }
  
  $Description=$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].'<br>';
		
		if($DestinationCountryId!=26){
			$Description.='PACKING : '.$VarietyMcsRow['PackingStyle'].' NET WEIGHT PER CARTON.'.$Whitspace.' 

			<B> BRAND </B>: '.$VarietyMcsRow['Brand'].' <B><br>'.$BAP.'</B>';
			}
		else{
		$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].''.$Whitspace.' ';
		}	
			
			
				
				$content.='
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="'.$FontSize.'" >'
			.$Description.'</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>
			

			
			<tr>
			
			<th align="center" width="40%" ><font size="'.$FontSize.'"> <u>SIZE</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'"></font></td>
			</tr>';
			
			
			$SqlVariety="SELECT * FROM `ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";  
		
			$StmtVariety=$iconn->query($SqlVariety);
				
			while($RowVariety=$StmtVariety->fetch_assoc()){ 
			
				$AryPriceBreakUp=getBreakUpData($iconn,$RowVariety['ID']);
				$Qty=$AryPriceBreakUp['Qty'];
				$UsdValue=$AryPriceBreakUp['UsdValue']; 
				$TotalMcs=$AryPriceBreakUp['TotalMcs'];
				$Price=$AryPriceBreakUp['Price'];
				
				
			$content.='
				<tr>
				<td align="center" width="40%"  ><font size="'.$FontSize.'">
				'.$RowVariety['SKUNo'].' <b>'.$RowVariety['Grade'].'</b> '.$RowVariety['Treatment'].'</font></td>
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.$TotalMcs.'</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.number_format($Qty,3).'</font></td>
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.number_format($Price,2).'</font></td>
				<td align="center" width="14.5%" style="border-right: 1px solid black;"><font size="'.$FontSize.'">
				'.number_format($UsdValue,2).'</font></td>
				
				</tr>';
			}


			}
			
				$AryPriceData=getBreakUpData($iconn,$RowVariety['ID'],$ID);
				$Qty=$AryPriceData['Qty'];
				$UsdValue=$AryPriceData['UsdValue']; 
				$TotalMcs=$AryPriceData['TotalMcs'];
				$Price=$AryPriceData['Price'];
				
			
			$content.='
				<tr>
				
				<td align="center" width="40%" style="border-top: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; border-right: 1px solid black;" ><font size="7">
				'.$TotalMcs.'</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.number_format($Qty,3).'</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				'.number_format($UsdValue,2).'</font></td>
				
				
				</tr>';
			
			$content.= '</table>
			  </tr>';

$AryBankData=getBankDetails($iconn,$BankID);

//print_r($AryBankData); exit;


$BenfName=$AryBankData['AccntName'];
$BenfAccNo=$AryBankData['AccountNumber'];
$BenfBankName=$AryBankData['BankName'];
$BenfBankAddress=$AryBankData['BankAddress'];
$BenfBankCode=$AryBankData['SwiftCode'];
	 

				$BILL_WORD=numberTowords($UsdValue);
			$content.='<tr > '.
           		 $TOTAL.'
            
				</tr>

				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> SHIPPING TERMS   : '.$ShippingTerms.'  '.$FinalDestination.'
				<BR> TOLERANCE           : QUANTITY/AMOUNT 10% PLUS OR MINUS
				<BR> SHIPMENT DATE :  '.$ShipByDate.' OR EARLY<BR> <BR><B><U style="background-color:grey">OUR BANKING DETAILS        : </U></B><br>
				
				<br> BENEFICIARY NAME <b style="color:white">-----------------------</b>: <b>'.$BenfName.'</b>
				<BR> BENEFICIARY ACCOUNT NUMBER<b style="color:white">----</b>: <b>'.$BenfAccNo.'</b> 
				<BR> BANK NAME AND ADDRESS<b style="color:white">-------------</b>: <b>'.$BenfBankName.','.$BenfBankAddress.' </b>
				<BR> SWIFT CODE<b style="color:white">-----------------------------------</b>: <b>'.$BenfBankCode.'</b>';
				
			
						$NetWgtKgs=$AryPriceData['NetWgtKgs'];
						$NetWgtLbs=$AryPriceData['NetWgtLbs'];
						$GrossWeightKgs=$AryPriceData['GrossWeightKgs'];
						$GrossWeightLbs=$AryPriceData['GrossWeightLbs'];
				
			
			
			if($QtyUnit=='KGS'){
				$content.='<BR><BR><b> NET WEIGHT <b style="color:white">------</b>: '.number_format($NetWgtKgs, 3, '.', '').' KGS</b>
				<BR><b> GROSS WEIGHT <b style="color:white">-</b>: '.number_format($GrossWeightKgs, 3, '.', '').' KGS</b> 
				</font></th>';	
				}else{
				$content.='<BR><BR> NET WEIGHT<b style="color:white">-----------------------------------</b>: 
				<b>'.number_format($NetWgtLbs, 3, '.', '').'LBS</b>
				<b style="color:white">--------------------------</b><b>'.number_format(($NetWgtLbs*0.454),3,'.','').' KGS</b>
				<BR> GROSS WEIGHT<b style="color:white">------------------------------</b>: 
				<b>'.number_format($GrossWeightLbs,3,'.','').'LBS</b>
				<b style="color:white">--------------------------</b><b>'.number_format(($GrossWeightLbs*0.454),3,'.','').' KGS</b> 
				</font></th>';	
				}
				
				
               $content.='</tr>
				<tr>  
           		<th  align="LEFT"  width="70%"><font size="7"> AMOUNT CHARGABLE IN US.$: <b>'.$BILL_WORD.'</b><BR> <BR> 
				<BR> PREPARED BY:  '.$PreparedBy.'<BR><BR><BR><B><U>DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT</font></th>    
                <th  align="center"  width="30%"><font size="7"> Signature:<br><br>
				<B> '.$AuthSign.',</B>		 
				<img src="'.$SignURL.'" 
				 width="100" height="30"style="width: 100" align="center"     valign="middle"/>				
				<p STYLE="text-align:center" ><b> Authorised Signature </b> </p></font></th>
				</tr>
				
				
				</table>'; 
	
	return $content;
	
	}
	
	
function getReportData($iconn,$PoID){
		
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

  $SqlMcsTotal="SELECT sum(Mcs) as Mcs FROM `ScrmPoGradeData` where InvId='$PoID'  ";
  $StmtMcsTotal=$iconn->query($SqlMcsTotal);
  $RowMcs=$StmtMcsTotal->fetch_assoc();
  $TotalMcs=$RowMcs['Mcs'];		
  



 $SqlScrmPoData="SELECT *,SCDB.ConsigneeName as BuyerName,
 SCDB.ConsigneeAddress as BuyerAddress,SPD.Status as PoStatus,ALD.SignURL,
SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,

ABD.AccntName as AccntName,
ABD.AccountNumber as AccountNumber,
ABD.BankName as BankName,
ABD.BankAddress as BankAddress,
ABD.SwiftCode as SwiftCode,
SC.name as PortofDischargeCountry ,SPC.CountryCode as CountryCode 
	FROM ScrmPIData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmConsigneeDetails as SCDB on SPD.BuyerID=SCDB.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN AdminLoginDetails as ALD on SC.id=SPD.ApprovedBy
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID

 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID'];
$ApprovedBy=$PoDataRow['ApprovedBy'];

 $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
$StmtLogin=$iconn->query($SqlLogin); 
$RowLogin = $StmtLogin->fetch_assoc();
$SignURL=$RowLogin['SignURL'];
		
		$ExporterData=getExporterDetails($ExpID);
		$ProcessorData=getExporterDetails($ProcessorID);
		
		
		if($PoDataRow['ShippingTerms']=='DDP'){
		$AryData['ShowShippingTerms']=true;	
		}else{
		$AryData['ShowShippingTerms']=false;	
		}
		
		if($PoDataRow['DestinationCountry']==26){
		$AryData['ShowDeliveryTerms']=true;	
		}else{
		$AryData['ShowDeliveryTerms']=false;	
		}
		

		if($ExpID==5 &&
		$ExporterData['ExpUsfdaNo']!='' 
		&& $PoDataRow['DestinationCountry']!=26){
		$AryData['showExpUsFda']=true;	
		}else{
		$AryData['showExpUsFda']=false;	
		}
		
		if($ExpID==5){
		$AryData['showExpIECode']=true;	
		}else{
		$AryData['showExpIECode']=false;	
		}
		
		if($ExpID==5){
		$AryData['showExpGSTNo']=true;	
		}else{
		$AryData['showExpGSTNo']=false;	
		}
		
		if($PoDataRow['DestinationCountry']!=26){
		$AryData['showProFEI']=true;	
		}else{
		$AryData['showProFEI']=false;	
		}
		
		if($PoDataRow['DestinationCountry']!=26){
		$AryData['showProUsFda']=true;	
		}else{
		$AryData['showProUsFda']=false;	
		}
		
		
		
		if($PoDataRow['PoNo'] ==''){			
		$AryData['PoDate']='';	
		}else{
		$AryData['PoDate']= "& Dated :".getFormatedDate($PoDataRow['PoDate']);
		}
		
		
		/* Return Data*/
		$AryData['PoStatus']=$PoDataRow['PoStatus']; 
		$AryData['ApprovedBy']=$ApprovedBy; 
		$AryData['PaymentTerms']=$PaymentTerms; 
		$AryData['DeliveryTerms']=$DeliveryTerms; 
		$AryData['TotalMcs']=$TotalMcs; 
		$AryData['ExporterID']=$ExpID; 
		
		$AryData['ExporterName']=$ExporterData['ExpName'];;
		$AryData['ExporterAddress']=$ExporterData['ExpAddress'];
		$AryData['ExporterUsfdaNo']=$ExporterData['ExpUsfdaNo'];
		$AryData['ExporterIeCode']=$ExporterData['ExpIeCode'];
		$AryData['ExporterGstNo']=$ExporterData['ExpGstNo'];
		$AryData['DestinationCountryId']=$ExporterData['DestinationCountry'];
		$AryData['ExporterRecAdd']=$ExporterData['ImporterRecordAddress']; 
		$AryData['AuthSign']=$ExporterData['AuthSign']; 
		
		
		
		$AryData['ConsigneeID']=$PoDataRow['ConsigneeID'];
		$AryData['BuyerID']=$PoDataRow['BuyerID'];
		$AryData['ConsigneeName']=$PoDataRow['ConsigneeName'];
		$AryData['ConsigneeAddress']=$PoDataRow['ConsigneeAddress'];
		$AryData['BuyerName']=$PoDataRow['BuyerName'];
		$AryData['BuyerAddress']=$PoDataRow['BuyerAddress'];
		
		
		$AryData['PortOfDischarge']=strtoupper($PoDataRow['Name']).' '.strtoupper($PoDataRow['CountryCode']).', '.$PoDataRow['PortofDischargeCountry'];
		$AryData['FinalDestination']=$PoDataRow['FinalDestination'];
		
		
		$AryData['InvNo']=getPIInvoiceNumber($iconn,$ExpID,$PoID) ;
		$AryData['InvDate']=getFormatedDate($PoDataRow['InvDate']);			
		$AryData['PoNo']=$PoDataRow['PoNo'];
		
		
		
		$AryData['ProcessorName']=$ProcessorData['ExpName'];
		$AryData['ProcessorAddress']=$ProcessorData['PackerAddress'];
		$AryData['ProcessorUsfdaNo']=$ProcessorData['ExpUsfdaNo'];
		$AryData['ProcessorFEI']=$ProcessorData['ExpFeiNo'];
		$AryData['ProcessorApprovalNo']=$ProcessorData['ExpApprovalNo'];
		$AryData['ProcessorIeCustomsNo']=$ProcessorData['ExpIeCustomsNo'];
		
		
		$AryData['ShippingTerms']=$PoDataRow['ShippingTerms'];
		$AryData['PaymentTerms']=$PoDataRow['PaymentName'];
		$AryData['DestinationCountry']=$PoDataRow['name'];
		$AryData['DeliveryTerms']=$PoDataRow['DeliveryTerms'];
		$AryData['QtyUnit']=$PoDataRow['QtyUnit'];
		$AryData['ShipByDate']=$PoDataRow['ShipByDate'];
		$AryData['SignURL']=$SignURL;
		$AryData['PreparedBy']=$PoDataRow['PreparedBy'];
		$AryData['BankID']=$PoDataRow['BankID'];

		
		return $AryData;
	}
	
	
function getBreakUpData($iconn,$GradeID,$PiID){
		
		if($PiID==''){
			$Qry="WHERE ID='$GradeID'";
		}else{
		$Qry="WHERE InvId='$PiID'";	
		}
		
		$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
							
			$SqlTotal="SELECT sum(Mcs) as MCS,InvId,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";  
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();	
			$InvId=$RowTotal['InvId'];

			$SqlQtyUnit="SELECT QtyUnit from ScrmPIData WHERE PoID='$InvId'"; 
			$StmtQtyUnit=$iconn->query($SqlQtyUnit);
			$RowQtyUnit=$StmtQtyUnit->fetch_assoc();
			$QtyUnit=$RowQtyUnit['QtyUnit'];
			
				
			if($QtyUnit=='KGS'){
			$Qty=$RowTotal['NetWgtKgs'];
			$UsdValue=$RowTotal['PriceKgs'];
			}else{
			$Qty=$RowTotal['NetWgtLbs'];
			$UsdValue=$RowTotal['PriceLbs'];
			}
			
			$AryPriceData['Price']=$RowTotal['Price'];
			$AryPriceData['TotalMcs']=$RowTotal['MCS'];
			$AryPriceData['Qty']=$Qty;
			$AryPriceData['UsdValue']=$UsdValue;
			
			$AryPriceData['NetWgtKgs']=$RowTotal['NetWgtKgs'];
			$AryPriceData['NetWgtLbs']=$RowTotal['NetWgtLbs'];
			$AryPriceData['GrossWeightKgs']=$RowTotal['GrossWeightKgs'];
			$AryPriceData['GrossWeightLbs']=$RowTotal['GrossWeightLbs'];

return 	$AryPriceData;		
		
	}


function getBankDetails($iconn,$BankID){
	
$SqlBank="SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
$StmtBank=$iconn->query($SqlBank); 
$AryBankData = $StmtBank->fetch_assoc();

return $AryBankData;

}	

?>