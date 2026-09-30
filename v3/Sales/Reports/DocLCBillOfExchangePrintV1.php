<?php
session_start();
	
 $PoID=$_GET['id'];
 $ClientPath= $_SESSION["ClientPath"];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

/* Folder Includes */
 $Type=$_GET['Type'];

class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status,$ClientPath;
	
	
	 $image_file = $ClientPath.'images/billofexchangewatermark.jpg'; 
		
		$this->Image($image_file, 0, 0, 230, 290, 'JPG', '', 'T', false, 30, '', false, false, 0, false, false, false);

		
    }
	
    public function Footer() {
        $this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
   }
}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
	

    
	$pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Lc Bill of Exchange Report : ');  
    $pdf->SetHeaderData('', '', '', '');  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(5);	
    $pdf->SetMargins(10, 10, 10,5);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(false);  
	$pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  

	$table_data=getFirstpage($iconn,$PoID,$ClientPath);
	$pdf->writeHTML($table_data);
	$pdf->AddPage(); 

	$table_data=getSecondPage($iconn,$PoID,$ClientPath);
	$pdf->writeHTML($table_data);
	$pdf->AddPage();  

	$table_data=getThirdPage($iconn,$PoID,$ClientPath);
	$pdf->writeHTML($table_data);
    $pdf->SetFont('helvetica', '', 6);

  $file_name='LCBillofExchangeReport-'.$PoID.'.pdf';

ob_end_clean();
if($Type==''){
$Type='I';
}else{
		$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}$pdf->Output($file_name, $Type);
$pdf->Ln();

function getFirstPage($iconn,$PoID,$ClientPath){
	$Imageurl=$ClientPath."images/1.png";
	$Description="(Second of the same  tenor and date being unpaid)";
	$content=getBillofexchangePage($iconn,$PoID,$Imageurl,$Description);
    return $content;
		}
		
function getSecondPage($iconn,$PoID,$ClientPath){
	$Imageurl=$ClientPath."images/2.png";
	$Description="(First of the same  tenor and date being unpaid)";
	$content=getBillofexchangePage($iconn,$PoID,$Imageurl,$Description);
	return $content;
		}
		
function getThirdPage($iconn,$PoID,$ClientPath){
	 $Imageurl=$ClientPath."images/Copy_Black.png";
	$Description="(Second of the same  tenor and date being unpaid)";
	$content=getBillofexchangePage($iconn,$PoID,$Imageurl,$Description);
    return $content;
		}
		
		function getBillofexchangePage($iconn,$PoID,$Imageurl,$Description){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	
$CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SealTime,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SD.SBDate,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading
FROM ScrmCustomInvoices as SCV 
 left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
 left JOIN ScrmShipmentDispatchDetails as SD on SD.PIID=SCV.PiID
 left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN ScrmCountriesList as SL on SL.id=SP.Country
 left JOIN ScrmBillOfExchange as SB on SB.PIID=SCV.PiID
 left JOIN ScrmEIRBankNFieghtDetaails as SBF on SBF.PoRefNumber=SCV.PiID
 WHERE SCV.PiID='$PoID'";  
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
	/*$SqlScrmPoData="SELECT * FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 WHERE PoID='$PoID'"; */
 $SqlScrmPoData="SELECT * FROM ScrmPoData as SPD 
  left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
 WHERE PoID='$PoID'"; 
 
 $StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
	
	
$LcStatus=$PoDataRow['LcStatus']; 
$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID']; 
$FinalDestination=$PoDataRow['FinalDestination']; 
$PoNo=$PoDataRow['PoNo'];
$Buyer=$PoDataRow['ConsigneeName'];
$ConsigneeAddress=explode(',',$PoDataRow['ConsigneeAddress']);
$CountryName=$PoDataRow['name']; 
$BankName=$PoDataRow['BankName']; 
$BankAddress=$PoDataRow['BankAddress']; 
$PaymentTerms=$PoDataRow['PaymentName']; 
$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
$ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
$AdvanceAmount=$CustomInvoiceRow['AdvanceAmount'];
$Status=$CustomInvoiceRow['Status'];
$BLDate=getFormatedDateStar($CustomInvoiceRow['BLDate']);
$BLNo=$CustomInvoiceRow['BLNo'];
$PoNumber=$PoDataRow['PoNo'];
$IssuedBy=$CustomInvoiceRow['IssuedBy']; 
$ToAddress=$CustomInvoiceRow['ToAddress'];
$DraftDate=getFormatedDateStar($CustomInvoiceRow['DraftDate']);
$IGST=$CustomInvoiceRow['IGST'];
$Discount=$CustomInvoiceRow['Discount'];
$CreditNo=$CustomInvoiceRow['LcNumber'];
$CreditDate=getFormatedDateStar($CustomInvoiceRow['LcDate']);
$InvNO=getInvoiceNumber($iconn,$ExpID,$PoID); 
$InvDate=getFormatedDateNA($CustomInvoiceRow['CInvDate']); 


/***NON LC Calculation start***/
if($LcStatus==0){
	

$sqlExt="sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";
			
			
			 $SqlTotal="SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `ScrmCIGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'"; 
			$StmtTotal=$iconn->query($SqlTotal);
			$RowTotal=$StmtTotal->fetch_assoc();
			
			if( $PoDataRow['QtyUnit']=='KGS'){				
				$Price = $RowTotal['PriceKgs'];	
			}else{
				
				$Price = $RowTotal['PriceLbs'];
			}
			  
   
   
   IF($IGST=='DISCOUNT' && $AdvanceAmount>0 ){ 
				   $Price =$Price-$AdvanceAmount;

				$DiscountAmount=($Price*$Discount)/100;
				 $TotalDiscount=$Price-$DiscountAmount;
   }
 else IF($IGST=='DISCOUNT'){ 
				
				$DiscountAmount=($Price*$Discount)/100;
				 $TotalDiscount=$Price-$DiscountAmount;
   }
   
  else IF($AdvanceAmount>0){
				
   $TotalDiscount =$Price-$AdvanceAmount;
   }
   else{
	  $TotalDiscount=$Price; 
   }

 		/*$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;*/
			 $ExchangeInvoicevalue=$TotalDiscount;  
			$BillWord=numberTowords($ExchangeInvoicevalue);	
		}		
			
			
/***NON LC Calculation End***/

else{
$ExchangeInvoicevalue=$CustomInvoiceRow['ExchangeInvoicevalue'];
$BillWord=numberTowords($ExchangeInvoicevalue);
$LcAddress='LC REFERENCE NUMBER. '.$CreditNo;
}
if($PoDataRow['ShippingTerms']=='DDP'&$LcStatus==1){	
	$ShippingTerms='('.$PoDataRow['ShippingTerms'].' '.strtoupper($FinalDestination).')';
}

if($CreditNo==''){
	$CreditNo='****';
}
if($PaymentTerms==''){
	$PaymentTerms='****';
}
$SqlVarietyMcs="SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP 
FROM `ScrmCIGradeData` left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand"; 
$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
  
  $content='';
  $Whitspace='<b style="color:white">  yuhjutgfrtgujkiolmnbvgftrkliomkjhykihuyy</b>';
			  				
$FontSize='10px';
			  $content .= '	<br><br><br><br><br><br><br><br>
		 <p style="width: 100px; display: table;text-align:right"> Draft Date  <b>'.$DraftDate.' </b></p>
     
		 <p style="width: 100px; display: table;text-align:left">
				NO. <b>'.$InvNO.' </b> and  Date
				 <b> '.$InvDate.' </b></p>
     
				<p style="width: 200px; display: table; text-align:left">
				<b>'.$PaymentTerms.' </b>  
			'.addSpace(50).'  Of this SECOND of Exchange </p>
				
				<p style="width: 200px; display: table;text-align:left">'.$Description.' Pay to the order of the
				<b > '.$BankName.'  </b> </p>
				
				<p style="width: 200px; display: table;text-align:left">
				 <b>'.$BankAddress.'  </b> </p>
				
				
				<p style="width: 200px; display: table;text-align:left">U.S.$.
				 <b>'.number_format($ExchangeInvoicevalue,2).'<font size="10"> ('.strtoupper($BillWord).')</font></b> </p>
				<br><br><br><br><br><br><br><br><br>

				 '.$Whitspace.'
				<img   width="50" height="50" src="'.$Imageurl.'"  />

				 
				<p style="width: 200px; display: table;text-align:left">For value received towards:';
				
				
							while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){
if($LcStatus==0){	
		$Brand=', BRAND: '.$VarietyMcsRow['Brand'];
}
				
			 $content .= '<font size="10">
			<b> 1 X 40 FCL, PO.NO: '.$PoNumber.''.$ShippingTerms.' '.addSpace(3).''.$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].',PACKING:
			 '.$VarietyMcsRow['PackingStyle'].' '.$Brand.'
			 </b></font>';

				 
				 }
				 $content .= '</p> 
<p style="width: 200px; display: table;text-align:left;">Shipped to '.addSpace(1).'<b>'.$PoDataRow['FinalDestination'].'</b>
				 
  '.addSpace(2).' per '.addSpace(2).'<b>BL#'.$BLNo.'</b>, DATED: <b>'.$BLDate.'</b></p>

<p style="width: 200px; display: table;text-align:left">
and Place the same against letter of Credit No. <b>'.$CreditNo.'</b>'.addSpace(9).' </p>
	
		
<p style="text-align:left;">dated.<b> '.$CreditDate.addSpace(90).'</b>Issued by</p>';
			
			
			if($IssuedBy==''){
			
			
	
	$content .= ' <table   border="0" cellspacing="1"  cellpadding="5" >
<tr>
<th style="width:75%" > </th> 
<th   style="width:25%;"  >******<br>******<br>******<br> </th></tr>

</table>';
			}
	else{
			
$content .= ' <table   border="0" cellspacing="1"  cellpadding="5" >
<tr>
<th style="width:70%" > </th> 
<th   style="width:30%;font-size: 9px;"  ><p><b>'.strtoupper(nl2br($IssuedBy)).'</b></p> </th></tr>

</table>';
			  			
		
	}
		
	$content .= '	

				<p style="width: 200px; display: table;text-align:left;"> To</p>';
			
			if($ToAddress==''){
			
		 
		 $content .= ' <table   border="0" cellspacing="1"  cellpadding="5" >
<tr>
<th style="width:70%" >******<br>******<br>****** </th> 
</tr>

</table>';
		 
			}
	else{
		
		$content .= '<p style="width: 200px;font-size: 9px; display: table;text-align:left;"><b>'.strtoupper(nl2br($ToAddress)).'
		'.$LcAddress.''.addSpace(8).'</b><br>
		';
	
			
		}
		
	
		
	$content .= '	</p>'; 
		return $content;
		} 
		 
		function addSpace($SpaceCount){
	
	for($i=0; $i<$SpaceCount ; $i++){
		$Space.='&nbsp;';
	}
	
	return $Space;
}
?>