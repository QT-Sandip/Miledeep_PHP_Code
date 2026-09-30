<?php

session_start();
	//ECHO $Fname=$_SESSION['ClientPath']."/Reports/TempFolder/"; EXIT;
	//ECHO $Fname=$_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."/Reports/TempFolder/"; EXIT;


 $PoID=$_GET['id'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."SalesInculdes/SalesConstants.php");
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Includes/Globals.php");

include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");

/** Folder Includes **/
 $Type=$_GET['Type'];




 
  $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SealTime,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SD.SBDate,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading FROM $ScrmCustomInvoices as SCV 
 left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
 left JOIN $ScrmShipmentDispatchDetails as SD on SD.PIID=SCV.PiID

 left JOIN $TblScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN $TblScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN $TblScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN $TblScrmCountriesList as SL on SL.id=SP.Country
 left JOIN $ScrmBillOfExchange as SB on SB.PIID=SCV.PiID

 WHERE SCV.PiID='$PoID'";  
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 

	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
	$SqlScrmPoData="SELECT * FROM $TblScrmPoData as SPD 
 left JOIN $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN $TblScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN $TblScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN $TblAdminBankDetails as ABD on ABD.ID=SPD.BankID
 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
			

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
			

/**data**/

$ExpID=$PoDataRow['ExporterID'];
$ProcessorID=$PoDataRow['ProcessorID']; 
$PoNo=$PoDataRow['PoNo'];
$Buyer=$PoDataRow['ConsigneeName'];
$ConsigneeAddress=explode(',',$PoDataRow['ConsigneeAddress']);
$CountryName=$PoDataRow['name']; 
$BankName=$PoDataRow['BankName']; 
$BankAddress=$PoDataRow['BankAddress']; 
$PaymentTerms=$PoDataRow['PaymentName']; 


if($PaymentTerms==''){
	$PaymentTerms='**********';
}
else{
	$PaymentTerms;
}
$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
$ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
$AdvanceAmount=$CustomInvoiceRow['AdvanceAmount'];
$Status=$CustomInvoiceRow['Status'];
$BLDate=date('d-m-Y',strtotime($CustomInvoiceRow['BLDate']));
if($BLDate=='11-01-1111'){
	$BLDate='**********';
}
else{
	$BLDate;
}

$DraftDate=date('d-m-Y',strtotime($CustomInvoiceRow['DraftDate']));
$BLNo=$CustomInvoiceRow['BLNo'];
if($DraftDate=='01-01-1970'){
	$DraftDate='**********';
}
else{
	$DraftDate;
}
$IGST=$CustomInvoiceRow['IGST'];
$Discount=$CustomInvoiceRow['Discount'];
$CreditNo=$CustomInvoiceRow['CreditNo'];
$CreditDate=date('d-m-Y',strtotime($CustomInvoiceRow['CreditDate']));
if($CreditNo==''){
	$CreditNo='**********';
}
else{
	$CreditNo;
}if($CreditDate=='01-01-1970'){
	$CreditDate='**********';
}
else{
	$CreditDate;
}

$ExporterData=getExporterDetails($ExpID);

if($ExpID==1||$ExpID==3){
	$ExporterName='SURYAMITRA EXIM PVT LTD';
	
}
else if($ExpID==5){
	$ExporterName='SUNRISE SEAFOODS INDIA PRIVATE LIMITED';
}

if($ProcessorID==1){
	$Unit='I';
	
}
else if($ProcessorID==3){
	$Unit='II';
}
$ProcessorData=getImporterDetails($ProcessorID);



$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

 $InvDate=date('d-m-Y',strtotime($CustomInvoiceRow['CInvDate'])); 
if($InvDate=="01-01-1970"){
	$InvDate="NA";
}
 else{
	 $InvDate;
 }
			 $PoDataRow['QtyUnit'];
			
			if( $PoDataRow['QtyUnit']=='KGS'){				
				$Price = $RowTotal['PriceKgs'];	
			}else{
				
				$Price = $RowTotal['PriceLbs'];
			}
			  
   /*IF($IGST=='DISCOUNT'){ uma18-11-2022
				
				$DiscountAmount=($Price*$Discount)/100;
				 $TotalDiscount=$Price-$DiscountAmount;
   }
   else{
	  $TotalDiscount=$Price; 
   }*/
   
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

 //uma
 		$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				
			$BillWord=numberTowords($TotalDiscount);

/**data**/



	
	//require_once('../tcpdf/tcpdf.php'); 
	

class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
	
	 $image_file = 'https://sme.silverlights.in/QualityControl/images/smebillofexchangewatermark.jpg'; 
		
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
	
	
	
	$table_data=getFirstpage($iconn,$PoID);
		$pdf->writeHTML($table_data);
		    $pdf->AddPage();  

		$table_data=getSecondPage($iconn,$PoID);
		$pdf->writeHTML($table_data);
		
$pdf->AddPage();  

		$table_data=getThirdPage($iconn,$PoID);
		$pdf->writeHTML($table_data);

	 $pdf->SetFont('helvetica', '', 6);

  
$file_name='LCBillofExchangeReport-'.$PoID.'.pdf';

ob_end_clean();
if($Type==''){
$Type='I';
}else{
	$Fname=__DIR__ ."/TempFolder/";
	//$Fname=$_SERVER['DOCUMENT_ROOT']."SalesNew/Reports/TempFolder/";
	$Fname=$_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."/Reports/TempFolder/";
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}$pdf->Output($file_name, $Type);
$pdf->Ln();


 





function getFirstpage($iconn,$PoID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 
include "../SalesInculdes/SalesConstants.php"; 

		global $CustomInvoiceRow,$PoDataRow,$RowTotal,$ScrmCIGradeData,$TblAdminPackingStyles;
		global $InvDate,$InvNO,$ExpID,$ProcessorID,$PoNo,$Buyer,$CountryName,$FrieghtCharge,$ExchangeRate,
		$IGST,$Discount,$Status,$ConsigneeAddress,$Buyer,
$BillWord,$BankName,$BankAddress,$PaymentTerms,$BLDate,$DraftDate,$BLNo,$TotalDiscount,$CreditDate,$CreditNo;
		$PoNumber=$PoDataRow['PoNo'];
		 $IssuedBy=$CustomInvoiceRow['IssuedBy']; 
		$ToAddress=$CustomInvoiceRow['ToAddress'];
		
		$SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP FROM `$ScrmCIGradeData` 
			left join `$TblAdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand"; 
			 
			 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
 
 
			
	$content='';

	$size=40;	 
 
	$size1=60;	



			 	
			  
			  $S_NO=1;
			  $width=100;
			  $width_s=50;
			  $width_w=$width+5;
              
			  				$Whitspace='<b style="color:white">  yuhjutgfrtgujkiolmnbvgftrkliomkjhykihuyy</b>';
			  				$Whitspacee='<b style="color:white">  jhygtfrdeswderfgtyhjmnk</b>';
			  				$Whitespacee='<b style="color:white">  jhygtfrd</b>';
			  				$spacee='<b style="color:white">  yuuujhygtf</b>';
			  				$space='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$FontSize='10px';
			  $content .= '
			  <table border="0" cellspacing="1" cellpadding="5" align="left" >
			  
			  

				<tr> 
				
				
<br><br><br><br>
<br><br><br><br>
		
				
				
				
			<TH>	
		 <p style="width: 100px; display: table;text-align:right">
			
				 Draft Date <b>'.$DraftDate.' </b>
				
				
				</p>
     
		 <p style="width: 100px; display: table;text-align:left">
				NO. <b>'.$InvNO.' </b> and  Date
				 <b> '.$InvDate.' </b>
				
				
				</p>
     </TH></tr>  
	          
				
				<tr> 
				<th >
				<p style="width: 200px; display: table; text-align:left">
				<b>'.$PaymentTerms.' </b>  
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Of this FIRST of Exchange
				
				</p>
				
				<p style="width: 200px; display: table;text-align:left">(Second of the same  tenor and date being unpaid) Pay to the order of the
				<b > '.$BankName.'  </b> 
				
				
				</p>
				
				<p style="width: 200px; display: table;text-align:left">
				 <b>'.$BankAddress.'  </b>
				
				
				</p>
				
				
				<p style="width: 200px; display: table;text-align:left">U.S.$.
				 <b>'.number_format($TotalDiscount,2).'<font size="10"> ('.strtoupper($BillWord).')</font></b> </p>
				
				
				</th>
				
				
				</tr>  

			 
				
				
				
				<tr > '.$Whitspace.'


				<!--<img   width="50" height="50" src="https://sme.silverlights.in/QualityControl/images/smebe1.jpg"  />
				-->
				<img   width="50" height="50" src="https://sme.silverlights.in/QualityControl/images/1.png"  />
				
				 
				<p style="width: 200px; display: table;text-align:left">For value received towards:';
				
				
							while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){

				/* $content .= '<font size="10">
			<b> 1 X 40 FCL, PO # '.$PoNumber.' &nbsp;&nbsp;&nbsp;'.$VarietyMcsRow['VarietyMcs'].' CASES OF '.$VarietyMcsRow['Variety'].' THE PRODUCT ARE
			 '.$VarietyMcsRow['PackingStyle'].','.$VarietyMcsRow['Brand'].' BRAND PRINTED PSC BAG WITH RS MASTER CARTON
			 </b></font>';*/
			 
			 $content .= '<font size="10">
			<b> 1 X 40 FCL, PO.NO: '.$PoNumber.' &nbsp;,&nbsp;&nbsp;'.$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].',PACKING:
			 '.$VarietyMcsRow['PackingStyle'].', BRAND: '.$VarietyMcsRow['Brand'].' 
			 </b></font>';

				 
				 }
				 $content .= '</p> </th>    
				</tr>  
				<tr> 
<th>
<p style="width: 200px; display: table;text-align:left;">Shipped to &nbsp;<b>'.$PoDataRow['FinalDestination'].'</b>
				 
  &nbsp;&nbsp; per &nbsp;&nbsp;<b>'.$BLNo.'</b>, DATED: <b>'.$BLDate.'</b></p>

<p style="width: 200px; display: table;text-align:left">and Place the same against letter of Credit No. <b>'.$CreditNo.'</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				
    </p>
	
		
<p style="width: 200px; display: table;text-align:left;">dated.

				<b> '.$CreditDate.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp; Issued by &nbsp;	';
			
			if($IssuedBy==''){
			$content .= '<b > *********</b><br>
			
			'.$space.'
			<b >
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; *********</b><br>
  

			'.$space.'
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; *********</b>
    ';
			}
	else{
		
			 $IssuedBy=explode(',',$IssuedBy);
		$content .= '<b>'.strtoupper($IssuedBy[0]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b> <br>
		'.$space.'
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($IssuedBy[1]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  

			'.$space.'
			<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($IssuedBy[2]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
    
		';
	}
		
	$content .= '	</p>

				<p style="width: 200px; display: table;text-align:left;"> To';
			
			if($ToAddress==''){
			$content .= '<b >&nbsp;&nbsp; *********</b><br>
			
		&nbsp;&nbsp;&nbsp;&nbsp;
<b>&nbsp;&nbsp; *********</b><br>
			  
<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; *********</b>
			  

  
    ';
			}
	else{
		
		
		if($Status==1){
			$ToAddress=explode(',',$ToAddress);
		$content .= '<b>&nbsp;&nbsp;'.$ToAddress[0].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			
		
			<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ToAddress[1].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ToAddress[2].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  

		';
		
		}
		

		else{
			$content .= '<b>&nbsp;&nbsp;'.$Buyer.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			
		
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ConsigneeAddress[0].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  &nbsp;&nbsp;
			<b >&nbsp;&nbsp;'.$ConsigneeAddress[1].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  &nbsp;&nbsp;
			<b >&nbsp;&nbsp;'.$ConsigneeAddress[2].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
   

		';
			
			
		
		}
			
		}
		
	
		
	$content .= '	</p>


    
</th>
		   
				</tr>  
				
				
				
			  '; 
			  
	
		$content .= '</table>';

		return $content;
}
function getSecondPage($iconn,$PoID){
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 
include "../SalesInculdes/SalesConstants.php"; 

	
		global $CustomInvoiceRow,$PoDataRow,$RowTotal,$ScrmCIGradeData,$TblAdminPackingStyles;
		global $InvDate,$InvNO,$ExpID,$ProcessorID,$PoNo,$Buyer,$CountryName,$FrieghtCharge,$ExchangeRate,
		$IGST,$Discount,$Status,$ConsigneeAddress,$Buyer,
$BillWord,$BankName,$BankAddress,$PaymentTerms,$BLDate,$DraftDate,$BLNo,$TotalDiscount,$CreditDate,$CreditNo;
		$PoNumber=$PoDataRow['PoNo'];
		 $IssuedBy=$CustomInvoiceRow['IssuedBy']; 
		$ToAddress=$CustomInvoiceRow['ToAddress'];
		
		$SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP FROM `$ScrmCIGradeData` 
			left join `$TblAdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand"; 
			 
			 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
 
 
			
	$content='';

	$size=40;	 
 
	$size1=60;	



			 	
			  
			  $S_NO=1;
			  $width=100;
			  $width_s=50;
			  $width_w=$width+5;
              
			  				$Whitspace='<b style="color:white">  yuhjutgfrtgujkiolmnbvgftrkliomkjhykihuyy</b>';
			  				$Whitspacee='<b style="color:white">  jhygtfrdeswderfgtyhjmnk</b>';
			  				$Whitespacee='<b style="color:white">  jhygtfrd</b>';
			  				$spacee='<b style="color:white">  yuuujhygtf</b>';
			  				$space='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$FontSize='10px';
			  $content .= '
			  <table border="0" cellspacing="1" cellpadding="5" align="left" >
			  
			  

				<tr> 
				
				
<br><br><br><br>
<br><br><br><br>
		
				
				
				
			<TH>	
		 <p style="width: 100px; display: table;text-align:right">
			
				 Draft Date <b>'.$DraftDate.' </b>
				
				
				</p>
     
		 <p style="width: 100px; display: table;text-align:left">
				NO. <b>'.$InvNO.' </b> and  Date
				 <b> '.$InvDate.' </b>
				
				
				</p>
     </TH></tr>  
	          
				
				<tr> 
				<th >
				<p style="width: 200px; display: table; text-align:left">
				<b>'.$PaymentTerms.' </b>  
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Of this SECOND of Exchange
				
				</p>
				
				<p style="width: 200px; display: table;text-align:left">(First of the same  tenor and date being unpaid) Pay to the order of the
				<b > '.$BankName.'  </b> 
				
				
				</p>
				
				<p style="width: 200px; display: table;text-align:left">
				 <b>'.$BankAddress.'  </b>
				
				
				</p>
				
				
				<p style="width: 200px; display: table;text-align:left">U.S.$.
				 <b>'.number_format($TotalDiscount,2).'<font size="10"> ('.strtoupper($BillWord).')</font></b> </p>
				
				
				</th>
				
				
				</tr>  

			 
				
				
				
				<tr > '.$Whitspace.'

				<img   width="50" height="50" src="https://sme.silverlights.in/QualityControl/images/2.png"  />

				 
				<p style="width: 200px; display: table;text-align:left">For value received towards:';
				
				
							while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){

				/* $content .= '<font size="10">
			<b> 1 X 40 FCL, PO # '.$PoNumber.' &nbsp;&nbsp;&nbsp;'.$VarietyMcsRow['VarietyMcs'].' CASES OF '.$VarietyMcsRow['Variety'].' THE PRODUCT ARE
			 '.$VarietyMcsRow['PackingStyle'].','.$VarietyMcsRow['Brand'].' BRAND PRINTED PSC BAG WITH RS MASTER CARTON
			 </b></font>';*/
			 $content .= '<font size="10">
			<b> 1 X 40 FCL, PO.NO: '.$PoNumber.' &nbsp;,&nbsp;&nbsp;'.$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].',PACKING:
			 '.$VarietyMcsRow['PackingStyle'].', BRAND: '.$VarietyMcsRow['Brand'].' 
			 </b></font>';

				 
				 }
				 $content .= '</p> </th>    
				</tr>  
				<tr> 
<th>
<p style="width: 200px; display: table;text-align:left;">Shipped to &nbsp;<b>'.$PoDataRow['FinalDestination'].'</b>
				 
  &nbsp;&nbsp; per &nbsp;&nbsp;<b>'.$BLNo.'</b>, DATED: <b>'.$BLDate.'</b></p>

<p style="width: 200px; display: table;text-align:left">and Place the same against letter of Credit No. <b>'.$CreditNo.'</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				
    </p>
	
		
<p style="width: 200px; display: table;text-align:left;">dated.

				<b> '.$CreditDate.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp; Issued by &nbsp;	';
			
			if($IssuedBy==''){
			$content .= '<b > *********</b><br>
			
			'.$space.'
			<b >
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; *********</b><br>
  

			'.$space.'
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; *********</b>
    ';
			}
	else{
		
			 $IssuedBy=explode(',',$IssuedBy);
		$content .= '<b>'.strtoupper($IssuedBy[0]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b> <br>
		'.$space.'
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($IssuedBy[1]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  

			'.$space.'
			<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($IssuedBy[2]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
    
		';
	}
		
	$content .= '	</p>

				<p style="width: 200px; display: table;text-align:left;"> To';
			
			if($ToAddress==''){
			$content .= '<b >&nbsp;&nbsp; *********</b><br>
			
		&nbsp;&nbsp;&nbsp;&nbsp;
<b>&nbsp;&nbsp; *********</b><br>
			  
<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; *********</b>
			  

  
    ';
			}
	else{
		
		
		if($Status==1){
			$ToAddress=explode(',',$ToAddress);
		$content .= '<b>&nbsp;&nbsp;'.$ToAddress[0].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			
		
			<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ToAddress[1].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ToAddress[2].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  

		';
		
		}
		

		else{
			$content .= '<b>&nbsp;&nbsp;'.$Buyer.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			
		
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ConsigneeAddress[0].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  &nbsp;&nbsp;
			<b >&nbsp;&nbsp;'.$ConsigneeAddress[1].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  &nbsp;&nbsp;
			<b >&nbsp;&nbsp;'.$ConsigneeAddress[2].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
   

		';
			
			
		
		}
			
		}
		
	
		
	$content .= '	</p>


    
</th>
		   
				</tr>  
				
				
				
			  '; 
			  
	
		$content .= '</table>';

		return $content;}

function getThirdPage($iconn,$PoID){
	
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 
include "../SalesInculdes/SalesConstants.php"; 

		global $CustomInvoiceRow,$PoDataRow,$RowTotal,$ScrmCIGradeData,$TblAdminPackingStyles;
		global $InvDate,$InvNO,$ExpID,$ProcessorID,$PoNo,$Buyer,$CountryName,$FrieghtCharge,$ExchangeRate,
		$IGST,$Discount,$Status,$ConsigneeAddress,$Buyer,
$BillWord,$BankName,$BankAddress,$PaymentTerms,$BLDate,$DraftDate,$BLNo,$TotalDiscount,$CreditDate,$CreditNo;
		$PoNumber=$PoDataRow['PoNo'];
		 $IssuedBy=$CustomInvoiceRow['IssuedBy']; 
		$ToAddress=$CustomInvoiceRow['ToAddress'];
		
		$SqlVarietyMcs="
			SELECT sum(McS) As VarietyMcs,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP FROM `$ScrmCIGradeData` 
			left join `$TblAdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 WHERE InvId='$PoID'  GROUP by Variety,PackingStyleID,Brand"; 
			 
			 
			$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
 
 
			
	$content='';

	$size=40;	 
 
	$size1=60;	



			 	
			  
			  $S_NO=1;
			  $width=100;
			  $width_s=50;
			  $width_w=$width+5;
              
			  				$Whitspace='<b style="color:white">  yuhjutgfrtgujkiolmnbvgftrkliomkjhykihuyy</b>';
			  				$Whitspacee='<b style="color:white">  jhygtfrdeswderfgtyhjmnk</b>';
			  				$Whitespacee='<b style="color:white">  jhygtfrd</b>';
			  				$spacee='<b style="color:white">  yuuujhygtf</b>';
			  				$space='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$FontSize='10px';
			  $content .= '
			  <table border="0" cellspacing="1" cellpadding="5" align="left" >
			  
			  

				<tr> 
				
				
<br><br><br><br>
<br><br><br><br>
		
				
				
				
			<TH>	
		 <p style="width: 100px; display: table;text-align:right">
			
				 Draft Date <b>'.$DraftDate.' </b>
				
				
				</p>
     
		 <p style="width: 100px; display: table;text-align:left">
				NO. <b>'.$InvNO.' </b> and  Date
				 <b> '.$InvDate.' </b>
				
				
				</p>
     </TH></tr>  
	          
				
				<tr> 
				<th >
				<p style="width: 200px; display: table; text-align:left">
				<b>'.$PaymentTerms.' </b>  
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Of this FIRST of Exchange
				
				</p>
				
				<p style="width: 200px; display: table;text-align:left">(Second of the same  tenor and date being unpaid) Pay to the order of the
				<b > '.$BankName.'  </b> 
				
				
				</p>
				
				<p style="width: 200px; display: table;text-align:left">
				 <b>'.$BankAddress.'  </b>
				
				
				</p>
				
				
				<p style="width: 200px; display: table;text-align:left">U.S.$.
				 <b>'.number_format($TotalDiscount,2).'<font size="10"> ('.strtoupper($BillWord).')</font></b> </p>
				
				
				</th>
				
				
				</tr>  

			 
				
				
				
				<tr > '.$Whitspace.'


								<img   width="100" height="50" src="https://sme.silverlights.in/QualityControl/images/Copy_Black.png"  />

				 
				<p style="width: 200px; display: table;text-align:left">For value received towards:';
				
				
							while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){

				/* $content .= '<font size="10">
			<b> 1 X 40 FCL, PO # '.$PoNumber.' &nbsp;&nbsp;&nbsp;'.$VarietyMcsRow['VarietyMcs'].' CASES OF '.$VarietyMcsRow['Variety'].' THE PRODUCT ARE
			 '.$VarietyMcsRow['PackingStyle'].','.$VarietyMcsRow['Brand'].' BRAND PRINTED PSC BAG WITH RS MASTER CARTON
			 </b></font>';*/
			 $content .= '<font size="10">
			<b> 1 X 40 FCL, PO.NO: '.$PoNumber.' &nbsp;,&nbsp;&nbsp;'.$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].',PACKING:
			 '.$VarietyMcsRow['PackingStyle'].', BRAND: '.$VarietyMcsRow['Brand'].' 
			 </b></font>';

				 
				 }
				 $content .= '</p> </th>    
				</tr>  
				<tr> 
<th>
<p style="width: 200px; display: table;text-align:left;">Shipped to &nbsp;<b>'.$PoDataRow['FinalDestination'].'</b>
				 
  &nbsp;&nbsp; per &nbsp;&nbsp;<b>'.$BLNo.'</b>, DATED: <b>'.$BLDate.'</b></p>

<p style="width: 200px; display: table;text-align:left">and Place the same against letter of Credit No. <b>'.$CreditNo.'</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				
    </p>
	
		
<p style="width: 200px; display: table;text-align:left;">dated.

				<b> '.$CreditDate.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp; Issued by &nbsp;	';
			
			if($IssuedBy==''){
			$content .= '<b > *********</b><br>
			
			'.$space.'
			<b >
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; *********</b><br>
  

			'.$space.'
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; *********</b>
    ';
			}
	else{
		
			 $IssuedBy=explode(',',$IssuedBy);
		$content .= '<b>'.strtoupper($IssuedBy[0]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b> <br>
		'.$space.'
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($IssuedBy[1]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  

			'.$space.'
			<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($IssuedBy[2]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
    
		';
	}
		
	$content .= '	</p>

				<p style="width: 200px; display: table;text-align:left;"> To';
			
			if($ToAddress==''){
			$content .= '<b >&nbsp;&nbsp; *********</b><br>
			
		&nbsp;&nbsp;&nbsp;&nbsp;
<b>&nbsp;&nbsp; *********</b><br>
			  
<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; *********</b>
			  

  
    ';
			}
	else{
		
		
		if($Status==1){
			$ToAddress=explode(',',$ToAddress);
		$content .= '<b>&nbsp;&nbsp;'.$ToAddress[0].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			
		
			<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ToAddress[1].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ToAddress[2].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  

		';
		
		}
		

		else{
			$content .= '<b>&nbsp;&nbsp;'.$Buyer.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
			
		
			<b >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$ConsigneeAddress[0].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  &nbsp;&nbsp;
			<b >&nbsp;&nbsp;'.$ConsigneeAddress[1].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
  &nbsp;&nbsp;
			<b >&nbsp;&nbsp;'.$ConsigneeAddress[2].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><br>
   

		';
			
			
		
		}
			
		}
		
	
		
	$content .= '	</p>


    
</th>
		   
				</tr>  
				
				
				
			  '; 
			  
	
		$content .= '</table>';

		return $content;
}

?>