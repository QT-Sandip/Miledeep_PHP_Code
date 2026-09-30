<?php
// ini_set( 'display_errors', 1 );

session_start();
 
$Type=$_GET['Type'];
  $PoID=$_GET['id'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");


include ($cp."Includes/IncludePage.php");



 $SqlScrmPoData="SELECT * FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();


$ExpID=$PoDataRow['ExporterID'];


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
    $pdf->SetTitle('AMC Report : ');  
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
 //$file_name='AMCReport-'.$PoID.'.pdf';
 /***uma 05-05-2023**/
$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];
 
$file_name=$InvShortCode.'-AMCReport-'.$CInvNo.'-'.$FinancialYear.'.pdf';
/***uma 05-05-2023**/

ob_end_clean();

if($Type==''){
$Type='I';
}else{
		$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','', $file_name );;
	$file_name=$Fname.$file_name;
}
$pdf->Output($file_name, $Type);
//$pdf->Ln();


function getDS_TABLE($iconn,$PoID){
	
	global $PoID,$PoDataRow;
 include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
//include ($_SERVER['DOCUMENT_ROOT']."/QualityControl/Constants.php");
include "../SalesInculdes/SalesConstants.php"; 

	
	
			 	  
   $CustomInvoiceSql="SELECT *,SPC.Name as PortofDischarge,SPC.CountryCode as CountryCode,
SC.name as PortofDischargeCountry,SL.name as PortOfLoadingCountry,SP.Name as PortOfLoading FROM ScrmCustomInvoices as SCV 
 left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SCV.PiID
left JOIN ScrmPortCities as SPC on SPC.PortId=SSD.PortofDischarge
 left JOIN ScrmPortCities as SP on SP.PortId=SSD.PortOfLoading
 left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
 left JOIN ScrmCountriesList as SL on SL.id=SP.Country

 WHERE SCV.PiID='$PoID'"; 


$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
	/* $SqlScrmPoData="SELECT * FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
 left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
 WHERE PoID='$PoID'"; 
 
 
$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();*/


$ExpID=$PoDataRow['ExporterID'];
$ShippingTerms=$PoDataRow['ShippingTerms'];
 $ProcessorID=$PoDataRow['ProcessorID']; 
$PoNo=$PoDataRow['PoNo'];
$Buyer=$PoDataRow['ConsigneeName'];
 //$CountryName=$PoDataRow['name'];
 

if($PoDataRow['ShowFrightCharge']==1 && $PoDataRow['ShowFrightCharge']==1){
		$AryData['ShowFrightCharge']=true;	
		}
		
		else{
		$AryData['ShowFrightCharge']=false;
		}

$ShowFrightCharge=$AryData['ShowFrightCharge'];
 
 //$FrieghtCharge=$CustomInvoiceRow['FrieghtCharge'];
 $ExchangeRate=$CustomInvoiceRow['ExchangeRate'];
$IGST=$CustomInvoiceRow['IGST'];
//$Discount=$CustomInvoiceRow['Discount'];
//exit;

//$ExporterData=getExporterDetails($ExpID);
$ExporterData=getExporterDetails($ExpID,$PoID);
$ExporterName=$ExporterData['ExpName'];

/*if($ExpID==1||$ExpID==3){
	$ExporterName='SURYAMITRA EXIM PVT LTD';
	
}
else if($ExpID==5){
	$ExporterName='SUNRISE SEAFOODS INDIA PRIVATE LIMITED';
}*/

// if($ProcessorID==3){
// 	$Unit='I';
	
// }
// else if($ProcessorID==4){
// 	$Unit='II';
// }
$ProcessorData=getImporterDetails($ProcessorID);





/*if($PoDataRow['ExporterID']==5){		

	$CurrentYear=date("y",strtotime($CurrentYear));
	}*/


//$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;
//$InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);
$InvNO=$CustomInvoiceRow['CustomsInvNo'];

$CInvDate=$CustomInvoiceRow['CInvDate'];
/*$StartEffectedDate='2022-04-26';
$EndEffectedDate='2022-05-26';*/

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
			
			/* $NetWgtKgs = $RowTotal['NetWgtKgs']; 
			$NetWgtLbs = $RowTotal['NetWgtLbs'];
			$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
			$GrossWeightLbs = $RowTotal['GrossWeightLbs'];*/
			
			 $PoDataRow['QtyUnit'];
		//	exit;
			if( $PoDataRow['QtyUnit']=='KGS'){				
				//$Weight = $RowTotal['NetWgtKgs'];	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				//$Weight = $RowTotal['NetWgtLbs'] *0.454;
				//$Weight = $RowTotal['NetWgtKgs'];// Suspisios
				$Price = $RowTotal['PriceLbs'];
			
			}
 $SqlDedINR="SELECT * FROM `ScrmDatewiseValues` where FromDate<='$CInvDate' and Type= 'AMCDeductionINR' ORDER BY ID DESC limit 1";
		 
		 $SqlDedUSD="SELECT * FROM `ScrmDatewiseValues` where FromDate<='$CInvDate' and Type= 'AMCDeductionUSD' ORDER BY ID DESC limit 1";
		 
			$StmtDedINR=$iconn->query($SqlDedINR);
			$StmtDedUSD=$iconn->query($SqlDedUSD);
           
           $RowDedINR=$StmtDedINR->FETCH_ARRAY();
           $RowDedUSD=$StmtDedUSD->FETCH_ARRAY();
		   
		   $AMCDeductionINR=$RowDedINR['Value'];
		$AMCDeductionUSD=$RowDedUSD['Value'];
		

		
	$AryPaymentData=getPaymentValues($iconn,$PoID,'');
   $TotalDiscount=$AryPaymentData['PriceAfterDiscount'];
   $FrieghtCharge=$AryPaymentData['FrieghtCharge'];
   $ExchangeRate=$AryPaymentData['ExchangeRate'];//uma
$DiscountAmount=$AryPaymentData['DiscountAmount'];
$InvValueUSDWithOutDuties=$AryPaymentData['InvValueUSDWithOutDuties'];
$InvValueINRWithOutDuties=$InvValueUSDWithOutDuties*$ExchangeRate;
$DutiesPrice=$AryPaymentData['DutiesPrice'];
$FOBValueUSD=$AryPaymentData['FOBValueUSD'];
if($ShowFrightCharge){
					$FobValueRs=(($FOBValueUSD-$AMCDeductionUSD)*$ExchangeRate)-$AMCDeductionINR;

				}


			else{
								$FobValueRs=(($InvValueUSDWithOutDuties-$AMCDeductionUSD)*$ExchangeRate)-$AMCDeductionINR;


		}
			
			 /* $AMCDeductionUS=0;//uma 04/06/2022
			  $AMCDeductionINR=1000000;//uma 02/06/2022

			  //$AMCDeductionUS=4250;
   IF($IGST=='DISCOUNT'){
				
				$DiscountAmount=($Price*$Discount)/100;
				 $TotalDiscount=$Price-$DiscountAmount;
   }
   

 //uma
 		$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
				
				
			 IF($IGST=='DISCOUNT'){
					
					$DutiesPrice=(($TotalDiscount-$FrieghtCharge)/101.35)*1.35; 
					 $DutiesPrice=round(($DutiesPrice+125),2);

				if($ShowFrightCharge==0||$ShippingTerms=='CFR'||$ShippingTerms=='CNF'){
					$FobValueRs=(($TotalDiscount-$FrieghtCharge-$AMCDeductionUS)*$ExchangeRate)-$AMCDeductionINR;
				}


			else{
				
				$FobValueRs=(($TotalDiscount-$FrieghtCharge-$DutiesPrice-$AMCDeductionUS)*$ExchangeRate)-$AMCDeductionINR;
			}
	
			}
			
ELSE{
	 
					$DutiesPrice=(($Price-$FrieghtCharge)/101.35)*1.35;
				$DutiesPrice=$DutiesPrice+125;
			
				if($ShowFrightCharge!=0&&$ShippingTerms!='CNF'&&$ShippingTerms!='CFR'){
					$FobValueRs=(($Price-$FrieghtCharge-$DutiesPrice-$AMCDeductionUS)*$ExchangeRate)-$AMCDeductionINR;
				             //   ($TotalDiscount-$FrieghtCharge-$AMCDeductionUS)*$ExchangeRate)-1000000)
			}
else{
		$FobValueRs=(($Price-$FrieghtCharge-$AMCDeductionUS)*$ExchangeRate)-$AMCDeductionINR;		
			}

 //uma

}	*/

//if($CInvDate<$EffectedDate){
//echo $DutiesPrice;	
//echo $CInvDate;	
//echo $StartEffectedDate;	
//echo $EndEffectedDate;	
 //$FobValueRs;	
//exit;


 $SqlAmcPercentage="SELECT * FROM `ScrmDatewiseValues` where FromDate<='$CInvDate' and Type= 'AMCPercentage' ORDER BY ID DESC limit 1";

$StmtAmcPercentage=$iconn->query($SqlAmcPercentage);
$RowAmcPercentage=$StmtAmcPercentage->FETCH_ARRAY();
$AmcPercentage=$RowAmcPercentage['Value'];

$FobValueRsPercentage=round(($FobValueRs*$AmcPercentage)/100); 


/*if ($CInvDate >= $StartEffectedDate && $CInvDate <= $EndEffectedDate){

//if($StartEffectedDate>=$CInvDate<=$EndEffectedDate){
	 //$FobValueRsPercentage=round(($FobValueRs*0.25)/100); 
	 		 $FobValueRsPercentage=round($FobValueRs/100); 

	
}
else{
		 //$FobValueRsPercentage=round($FobValueRs/100); 
	 $FobValueRsPercentage=round(($FobValueRs*0.25)/100); 

}*/
			
  $BillWord=numberTowordsRupees($FobValueRsPercentage);
	$content='';

	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=100;
			  $width_s=50;
			  $width_w=$width+5;
              
			  				$Whitspace='<b style="color:white">  yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
			  				$Whitspacee='<b style="color:white">  jhygtfrdeswderfgtyhjmnk</b>';
			  				$Whitespacee='<b style="color:white">  jhygtfrd</b>';
			  				$spacee='<b style="color:white">  yuuujhygtf</b>';

			  $content .= '
			  <table border="1" cellspacing="0" cellpadding="5" align="center" >
			   <tr>  

           		

                <th  align="center"  width="'.$width.'%"><B><font size="14">'.$ExporterName.'</font>
				<br><font size="10">BHIMAVARAM, W.G.DIST,AP,INDIA</font>
				<br><font size="8"><u>Cash/Cheque/Online Payment approval slip</u></font></B></th>  
                </tr> 
           
	           <tr> 	<th  align="left" width="'.$width.'%" >'.$Whitspace.''.$Whitespacee.' <u> Date: '.date('d-m-Y',strtotime($CustomInvoiceRow['CInvDate'])).'
			   <br>Party Name/Station  </u>: Agriculture Market Committee</th> </tr>   
				 

				<tr> 

				<th  align="left" width="'.$width.'%" ><u>Payment against  </u> : '.$InvNO.''.$Whitspacee.' No Of Days : </th>    
				</tr>  
				<tr> 

				<th  align="left" width="'.$width.'%" ><u>A/C Balance as On : </u>'.$Whitspacee.'Sanctioned Rs :'.$FobValueRsPercentage.'.00</th>    
				</tr>  
				
				<tr> 


				<th  align="left" width="'.$width.'%" ><u>Amount in words</u> : '.strtoupper($BillWord).' </th>    
				</tr>  
				<tr> 

				<th  align="left" width="'.$width.'%" ><u>Type of Payment :</u>'.$Whitespacee.'Bank : '.$Whitespacee.'Online/Cheque No.'.$Whitespacee.'  Cash :</th>    
				</tr>  
				<tr> 

				<th  align="left" width="'.$width.'%" ><u>Purpose :</u></th>    
				</tr>  
				<tr> 

				<th  align="left" width="'.$width.'%" ><b><u>'.$Whitespacee.'Prepared By'.$Whitspace.'Accounts Dept</u></b><br>
				<br><br> '.$Whitespacee.' Authorized '.$Whitspacee.' Authorized '.$Whitspacee.' Authorized    
				<br><b> '.$spacee.' (Pay) '.$Whitspacee.' (Important) '.$Whitspacee.' (Must do now)</b></th>    
				</tr>  
				
				
				
			  '; 
			  
	
		$content .= '</table>';


	return $content;

}

?>