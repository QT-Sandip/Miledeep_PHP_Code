<?php

$style = '<style>
th {
    font-size: 9px;
	padding : 10px;
}
td {
    font-size: 6px;
	padding : 10px;
}
p {
	//line-height: 0.01;
}
</style>
';
//ini_set( 'display_errors', 1 );

session_start();

//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $PoID=$_GET['id'];
 $Type=$_GET['Type'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");
include ($cp."Includes/IncludePage.php");

$SqlScrmPoData="SELECT * FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortofLoading=SPC.PortId
 left JOIN ScrmCurrencyMaster as SCM on SCM.CurrencyCode=SPD.UnitCurrency
 WHERE PoID='$PoID'"; 
 

$StmtPoData=$iconn->query($SqlScrmPoData); 
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID=$PoDataRow['ExporterID'];
$PortOfDischarge=$PoDataRow['PortOfDischarge'];
$NotifyPartyID=$PoDataRow['NotifyPartyID'];

$SqlCustom="SELECT CustomsInvNo,CInvDate FROM `ScrmCustomInvoices`  as SCV WHERE PiID='$PoID'";
$StmtCustom=$iconn->query($SqlCustom);
$RowCustom=$StmtCustom->fetch_assoc();

$CustomsInvNo=$RowCustom['CustomsInvNo'];
$CustInvDate=date("d-m-Y", strtotime($RowCustom['CInvDate']));

class MYPDF extends TCPDF { 
 
    public function Header() {
		
	global $Status;
	
				
        $image_file = 'images/ds_logo.jpg';
        $this->SetFont('helvetica', 'B', 15); 
		//$this->Cell(100, 0, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		//$this->Cell(0, 10, '', 0, 1, 'C', 0, '', 1, false, 'T', 'M');
		
    }
	
    public function Footer() {
		global $CustomsInvNo,$CustInvDate;
        $this->SetFont('helvetica', 'I', 8);
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'('.$CustomsInvNo.' / Dt.'.$CustInvDate.')', 0, false, 'C', 0, '', 0, false, 'T', 'M');

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
	$pdf->SetFont('dejavusans', '', 12); 
    $pdf->SetFooterMargin(10); 
	$pdf->SetHeaderMargin(2);	
    $pdf->SetMargins(10, 2, 10,2);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
	
    $pdf->SetAutoPageBreak(TRUE, 5);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
	
	
	
	$table_data=getShipmentDetails($iconn,$PoID);
	$pdf->writeHTML($table_data);
/*
//ECHO $NotifyPartyID; EXIT;	
$pdf->AddPage();  
IF($NotifyPartyID==2){
	$table_data=getCha1($iconn,$PoID);
}ELSE IF($NotifyPartyID==3){
	$table_data=getCha2($iconn,$PoID);
	$pdf->writeHTML($table_data);
	$pdf->AddPage();  
	$table_data=getCha21($iconn,$PoID);		
}ELSE{
$table_data=getCha3($iconn,$PoID);	
}
$pdf->writeHTML($table_data);

IF($NotifyPartyID==1){	
	$pdf->AddPage();
	$table_data=getCha31($iconn,$PoID);
	$pdf->writeHTML($table_data);
	$pdf->AddPage();
	$table_data=getCha32($iconn,$PoID);
	
}
	$pdf->writeHTML($table_data);
*/

	$pdf->SetFont('helvetica', '', 6);

$ArrayFilename=getfilename($iconn,$ExpID,$PoID);

/*$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];*/
$CustomsInvNo=$ArrayFilename['CustomsInvNo'];
$file_name = str_replace('/','_', $CustomsInvNo );
$Fname='-SHIPMENT';
$file_name=$file_name.$Fname.'.pdf'; 
//$file_name=$InvShortCode.'-SD-'.$CInvNo.'-'.$FinancialYear.'.pdf';

ob_end_clean();
if($Type==''){
$Type='I';
}else{
		$Fname=$MailTempFolder; //From Config 
	$file_name=$_GET['FileName'];
	$file_name = str_replace('/','_', $file_name );;
	$file_name=$Fname.$file_name;
}$pdf->Output($file_name, $Type);
//$pdf->Ln();


function getShipmentDetails($iconn,$PoID){
	
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/func.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include "../SalesInculdes/SalesConstants.php"; 


global $PoDataRow,$style,$DocType;
$DocType='TPT';

	
   $CustomInvoiceSql="SELECT *,SCI.ByRoad FROM ScrmCustomInvoices as SCI
 
  left join`ScrmPoData` as  SPID on SPID.PoID=SCI.PiID
  left join ScrmCountriesList as SCL on SCL.id=SPID.DestinationCountry
  left join AdminExporterDetails as AED on AED.ExpID=SPID.ProcessorID
  left join ScrmISFDetails as SISD on SCI.PiID=SISD.PIID
  left join ScrmLinerMaster as SLM on SLM.LinerID=SISD.ShippingLineID
  left JOIN ScrmPortCities as SPC on SPID.FinalDestination=SPC.PortId

  WHERE SCI.PiID='$PoID'"; 
//ECHO  $CustomInvoiceSql; EXIT;
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
$ExpID=$PoDataRow['ExporterID'];
$PoNo=$PoDataRow['PoNo'];
$ReferenceNo=$PoDataRow['ReferenceNo'];
$PortOfDischarge=$PoDataRow['PortOfDischarge'];
$NotifyPartyID=$PoDataRow['NotifyPartyID'];
$UnitCurrency=$PoDataRow['UnitCurrency'];
$CurrenySymbol= $PoDataRow['CurrenySymbol']; 

$SqlPoGradeData="SELECT SUM(Mcs) as TMCS,Brand,Group_Concat(VarietyID) as VarietyID FROM `ScrmPoGradeData` WHERE InvId='$PoID'";
$StmtPoGradeData=$iconn->query($SqlPoGradeData); 
$RowPoGradeData = $StmtPoGradeData->fetch_assoc();

$SqlSauceVariety="SELECT COUNT(*) AS HasSauce FROM `ScrmPIVarietyList` WHERE VarietyID IN (" . $RowPoGradeData['VarietyID'] . ") AND HasSauce = 'Yes'";
$StmtSauceVariety=$iconn->query($SqlSauceVariety);
$RowSauceVariety = $StmtSauceVariety->fetch_assoc();

if($RowSauceVariety['HasSauce'] >= 1){
	$PaymentData=getShrimpPaymentValues($iconn,$PoID);
} else {
	$PaymentData=getPaymentValues($iconn,$PoID);
}

//$ExporterData=getExporterDetails($ExpID);

$SqlDischargePrt="SELECT * FROM `ScrmPortCities` WHERE 
PortId='$PortOfDischarge'";
$StmtDischargePrt=$iconn->query($SqlDischargePrt); 
$RowDischargePrt = $StmtDischargePrt->fetch_assoc();

$SqlPrtList="SELECT * FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' and Status=1  ";
$StmtPrtList=$iconn->query($SqlPrtList); 
$PortCount=mysqli_num_rows($StmtPrtList);	




 $SqlCIGradeData="SELECT sum(MCS) as Mcs,Variety,CIDescription,PackingStyle,Brand,
GROUP_CONCAT(Grade,'- ',MCS,' M/CTNS') as GradeMcs FROM 
`ScrmCIGradeData` SCGD
LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SCGD.PackingStyleID
 WHERE InvId='$PoID' GROUP BY Variety ORDER BY SCGD.ID"; 
 
 $SqlCIGradeData="SELECT sum(MCS) as Mcs,Variety,PackingStyle,Brand,PrintStatus,
GROUP_CONCAT(Grade,'- ',MCS,' M/CTNS' ORDER BY SCGD.ID ASC) as GradeMcs FROM `ScrmPoGradeData` SCGD
LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SCGD.PackingStyleID
left join `ScrmBrandsMaster` SBM on  SBM.BrandName=SCGD.Brand
 WHERE InvId='$PoID'  GROUP BY Variety,PackingStyle,Brand ORDER BY  SCGD.ID asc";  //SCGD.ID, 

$StmtCIGradeData=$iconn->query($SqlCIGradeData); 

//ECHO  $SqlCIGradeData; EXIT;
$Sno=1;
WHILE($RowCIGradeData = $StmtCIGradeData->fetch_assoc()){

	$BrandPrintStatus=$RowCIGradeData['PrintStatus'];
	
	if($BrandPrintStatus){
		$DisplayBrand="BRAND: ".$RowCIGradeData['Brand'];
		//$Brand=$RowCIGradeData['Brand'];
		$AryBrandsList[]=$RowCIGradeData['Brand'];
	}
	else{
		$AryBrandsList[]='-------';
	}
	
	$AryBreakUp[]='('.$Sno.')'.$RowCIGradeData['Variety']
	.'.PACKING:'.$RowCIGradeData['PackingStyle'].'   '
	.$RowCIGradeData['GradeMcs'].'    '.$DisplayBrand;
	
	//$AryBrandsList[]=$RowCIGradeData['Brand'];
	
	$TtlMCS+= $RowCIGradeData['Mcs'] ;

$Sno++;	
	
}	


$Brandslist=implode(",",array_unique($AryBrandsList));

$TtlMCS=$TtlMCS.'M/CTNS';

$InvNO=$ExporterData['InvShortCode'].$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

//PRINT DATA
$Buyer=$PoDataRow['ConsigneeName'];
//$CInvNo=getInvoiceNumber($iconn,$ExpID,$PoID) ;
$CInvNo=$CustomInvoiceRow['CustomsInvNo'] ;
//$CInvNo=$CustomInvoiceRow['CInvNo'];
$CInvDate=getFormatedDateNA($CustomInvoiceRow['CInvDate']);
$MCS=$RowPoGradeData['TMCS'];
$Brand=$RowPoGradeData['Brand'];
$BLNo=$CustomInvoiceRow['BLNo'];
$ContainerNumber=$CustomInvoiceRow['ContainerNo'];
$SealNo=$CustomInvoiceRow['SealNo'];
$BLDate=getFormatedDateNA($CustomInvoiceRow['BLDate']);
$LodingPort=$PoDataRow['Name'];
$FinalDestination=strtoupper($CustomInvoiceRow['Name']);
$PoID=$CustomInvoiceRow['PiID'];
$SqlOrgnVessel="SELECT FromTOPID,VesselName,VOYNO FROM ScrmTranshipmentPortsList as TSP 
left join ScrmPoData as POD on TSP.PIID=POD.PoID 
left join ScrmPortCities  as SPC on SPC.PortId=POD.PortofLoading
WHERE TSP.FromTOPID=SPC.Name and  PoID=$PoID";
$StmtOrgnVessel=$iconn->query($SqlOrgnVessel); 
$RowOrgnVessel = $StmtOrgnVessel->fetch_assoc();
 
 $VesselOrg=$RowOrgnVessel['VesselName'];
 $VoyOrg=$RowOrgnVessel['VOYNO'];

 
 
$MotherVessel=$CustomInvoiceRow['ImportingVessel'];

$CountryName=$CustomInvoiceRow['name'];
$ShippingLineID=$CustomInvoiceRow['ShippingLineID'];
$TranshipmentPort=$CustomInvoiceRow['TranshipmentPort'];
$ProcessorName=$CustomInvoiceRow['ExpName'];
$ETA_POD=getFormatedDateNA($CustomInvoiceRow['ETA_POD']);
$ETA_FD=getFormatedDateNA($CustomInvoiceRow['ETA_FD']);
$LoadingDate=getFormatedDateNA($CustomInvoiceRow['LoadingDate']);
$ShippingOnboardDt=getFormatedDateNA($CustomInvoiceRow['ShippingOnboardDt']);
$TranshipmentDate=getFormatedDateNA($CustomInvoiceRow['TranshipmentDate']);
$DischargePort=$RowDischargePrt['Name'];

$ProcessorID=$CustomInvoiceRow['ProcessorID'];
$ByRoad=$CustomInvoiceRow['ByRoad'];
$ByRoadDate=getFormatedDateNA($CustomInvoiceRow['ByRoadDate']);
 $CountryID=$PoDataRow['DestinationCountry'];
//ECHO $DocType; EXIT;
$ExporterData=getExporterDetails($ExpID);
$ProcessorData=getExporterDetails($ProcessorID,$CountryID,$DocType);
 $SupplierNm=$ProcessorData['ExpName']; 
$SupplierAddres=$ProcessorData['PackerAddress'];

$BreakUp=implode("<br>",array_unique($AryBreakUp));


$SqlLiner="SELECT * FROM `ScrmLinerMaster` WHERE LinerID='$ShippingLineID'";
$StmtLiner=$iconn->query($SqlLiner); 
$RowLiner= $StmtLiner->fetch_assoc();

$LinerName=$RowLiner['LinerName'];

		
	$content='';

	$size=40;	 
 
	$size1=60;	
if($ReferenceNo !=''){
$b1='(';
$b2=')';

}	

$Via=', Via ';
if($FinalDestination==$DischargePort){
	
$DischargePort='';
$Via='';	
}	

if($ByRoad!=''){
	
$ByRoadd=$ByRoad.'  Via';
//$FinalDestination=$ByRoad.'  Via';

}else{
	
$ByRoadd='';
//$FinalDestination=$FinalDestination.$Via.$DischargePort;	
}	 	
			  
			  $S_NO=1;
			  $width=40;
			  $width2=60;
			  $width_s=10;
              
			  
			  $content .=$style;
			  $content .= '
			  
			  	<h3 align="CENTER"><br>SHIPMENT DETAILS <br></h3>
			  	
			  <table border="1" cellspacing="0" cellpadding="5" align="center" width="100%">

			  <tr> 

				   
				<th  align="LEFT" width="'.$width.'%" ><B>PO NO</B> '.$PoNo.$b1.$ReferenceNo.$b2.'</th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$Buyer.'</th>
				</tr>  
				 <tr> 

				   
				<th  align="LEFT" width="'.$width.'%" ><B>1.Our Invoice no. and Dt.</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$CInvNo.',Dt.'.$CInvDate.'</th>
				</tr>  
				<!-- <tr> 

				   
				<th  align="LEFT" width="'.$width.'%" ><B>2.Manufacturer :</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$SupplierNm.'</th>
				</tr>  -->
				
			   <tr> 
				
			   <th  align="LEFT"  width="'.$width.'%"><B>2.Break-up:</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$BreakUp.'   ='.$TtlMCS.'</th>
                </tr> 
				
				<tr> 
				
			   <th  align="LEFT"  width="'.$width.'%"><B>3.BRAND</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$Brandslist.'</th>
                </tr> 
				
				
				<tr> 				
			     <th  align="LEFT"  width="'.$width.'%"><B>4.Total Master Cartons</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$MCS.' M/CTNS.</th>
                </tr>'; 
				
				// echo '<span style="font-family:dejavusans;">&#8377;</span>';
				// exit;
				
				// <span style="font-family:dejavusans;">&#8377;</span> 

				// <th  align="LEFT" width="'.$width2.'%">'.$UnitCurrency;

				// if($CurrenySymbol == ''){
				//  $content .= '<span style="font-family:dejavusans;">'.htmlspecialchars($CurrenySymbol);
				// } else {
				//  $content .= htmlspecialchars($CurrenySymbol);
				// }
				// $content .= number_format($PaymentData['InvValueUSD'],2).'</th>
				// <th  align="LEFT" width="'.$width2.'%">'.$UnitCurrency.' <span style="font-family:dejavusans;">&#8377;</span> '.$CurrenySymbol.''.number_format($PaymentData['InvValueUSD'],2).'</th>

			$content .=	'<tr> 				
			   <th  align="LEFT"  width="'.$width.'%"><B>5.Total Invoice Value</B></th>  
			   <th  align="LEFT" width="'.$width2.'%">'.$UnitCurrency . ' ';

			   if($CurrenySymbol == "₹"){
				$content .= '<span style="font-family:dejavusans;">&#8377;</span>';
			   } else {
				$content .= htmlspecialchars($CurrenySymbol);
			   }
				$content .= ' ' .number_format($PaymentData['InvValueUSD'],2).'</th>

                </tr>


				<tr> 
				
			   <th  align="LEFT"  width="'.$width.'%"><B>6.B/L No. and Dt.</B></th>  
				<th  align="LEFT" width="'.$width2.'%">'.$BLNo.',DT.'.$BLDate.'</th>
                </tr> 
            
			<tr> 

			   <th  align="LEFT"  width="'.$width.'%"><B>7.Container No.</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$ContainerNumber.'</th>
                </tr> 
           
				 <tr> 
				
			   <th  align="LEFT"  width="'.$width.'%"><B>8.Seal No.</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$SealNo.'</th>
                </tr> 
            <tr> 
				
			   <th  align="LEFT"  width="'.$width.'%"><B>9.Port of Loading</B></th>  
				 <th  align="LEFT" width="'.$width2.'%">'.$LodingPort.',INDIA</th>
                </tr> 
           
				                

				<tr> 
				
				<th  align="LEFT" width="'.$width.'%" ><B>10.Destination </B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$ByRoadd.'  '.$FinalDestination.$Via.$DischargePort.','.$CountryName.'</th>
				</tr>
				<!--<tr> 
				
				<th  align="LEFT" width="'.$width.'%" ><B>11.Destination </B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$FinalDestination.','.$CountryName.'</th>
				</tr> -->  

				<tr> 
				
				<th  align="LEFT" width="'.$width.'%" ><B>11.Vessel Name</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$VesselOrg.'   '.$VoyOrg.'</th>
				</tr>   

				<tr> 
				
				<th  align="LEFT" width="'.$width.'%" ><B>12.Liners</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$LinerName.'</th>
				</tr>   

				<tr> 
				  
				<th  align="LEFT" width="'.$width.'%" ><B>ETD and ETA Details:</B></th>    
				 <th  align="LEFT" width="'.$width2.'%"></th>
				</tr>   
               
			   <tr> 
				  
				<th  align="LEFT" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width2.'%"></th>
				</tr>
				<!--<tr> 
				<th  align="LEFT" width="'.$width.'%" ><B>ETD '.$LodingPort.',INDIA</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$ShippingOnboardDt.'</th>
				</tr>-->
				'; 
				  
				
				$Sno=0;
				
				while($RowPrtList= $StmtPrtList->fetch_assoc()){ 
						$ArrivalPort=$RowPrtList['FromTOPID'];
						$DeparturePort=$RowPrtList['ToTOPID'];
						$ArrivalDate=getFormatedDateNA($RowPrtList['FromDate']);
						$DepartureDate=getFormatedDateNA($RowPrtList['TranshipDt']);
						
				$SqlLoadingCountry="SELECT PortofLoading,PoID,STPL.FromTOPID,SCL.name AS CountryName,SPC.Name as LoadingPort FROM `ScrmPIData` SPD
                  LEFT JOIN ScrmTranshipmentPortsList STPL ON STPL.PIID=SPD.PoID
                  LEFT JOIN ScrmPortCities SPC ON SPC.PortId=SPD.PortofLoading
                  LEFT JOIN ScrmCountriesList SCL ON SCL.id=SPC.Country
                  WHERE STPL.PIID=$PoID";
				$StmtLoadingCountry=$iconn->query($SqlLoadingCountry);  
				$RowLoadingCountry= $StmtLoadingCountry->fetch_assoc();
				$LoadingPort=$RowLoadingCountry['LoadingPort'];
				
				if($ArrivalPort==$LoadingPort){
					
					$Country=' , '.$RowLoadingCountry['CountryName'];
					
				}else{
					
				$Country='';	
				}

				
						
						$MotherVessel1=$RowPrtList['VesselName'].' '.$RowPrtList['VOYNO'];
				
				
				if(++$Sno!=1){
					
			
			
				$content.='<tr>   
				<th  align="LEFT" width="'.$width.'%" ><B>MOTHER VESSEL </B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$MotherVessel1.'</th>
				</tr>';
				}
				
				$content.='<tr>	
				 <th  align="LEFT" width="'.$width.'%" ><B>ETD '.$ArrivalPort.$Country.'</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$ArrivalDate.'</th>
				</tr>
			
				<tr>					  
				<th  align="LEFT" width="'.$width.'%" ><B>ETA  '.$DeparturePort.'</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$DepartureDate.'</th>
				</tr>
				
					
				
				
				
				
				
				
				
				  <!--<tr>
				<th  align="LEFT" width="'.$width.'%" ><B>ETA  '.$ToPort.'</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$TranshipDt.'</th>
				</tr>-->
					
				';
				}
				if($ByRoad!=''){
					
					$content.='<tr>
				<th  align="LEFT" width="'.$width.'%" ><B>ETA  '.$ByRoad.','.$CountryName.'</B></th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$ByRoadDate.'</th>
				</tr>';
					
					
				}
				
				/*$content.='<tr>
				<th  align="LEFT" width="'.$width.'%" >ETA  '.$ToPort.'</th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$TranshipDt.'</th>
				</tr>';*/
				
				/*$content.='<tr>
				<th  align="LEFT" width="'.$width.'%" >ETA  '.$FinalDestination.$Via.$DischargePort.'</th>    
				 <th  align="LEFT" width="'.$width2.'%">'.$ETA_FD.'</th>
				</tr>';*/
				
				
				
			
				
			$content.='

                  				
				
				
				<tr> 
				  
				<th  align="LEFT" width="'.$width.'%" ></th>    
				 <th  align="LEFT" width="'.$width2.'%"></th>
				</tr>
				<tr> 
				  
				<th  align="LEFT" width="'.$width.'%" >BEST BEFORE DATES</th>    
				 <th  align="LEFT" width="'.$width2.'%">-------<BR><BR></th>
				</tr>
				
			  '; 
			  
	
			$content .= '</table>';


	return $content;	

}






?>