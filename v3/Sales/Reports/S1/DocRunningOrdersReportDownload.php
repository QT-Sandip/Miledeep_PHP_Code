<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

$Year=$_GET['Year'];
 $Exporter=$_GET['Exporter'];	

if($Year!=''){
 $from_date=$Year.'-04-01'; 
$YearEnd=$Year+1;
$to_date=$YearEnd.'-03-31';
}
else{
$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
}
   
if($Exporter!=''){
	//$Query='';
	$Query.='and SPD.ExporterID in ('.$Exporter.')';
}


$file_name='Shipment Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
	
	
			
			
			 $SqlPo="SELECT *,SPC.Name AS Port,SPD.FinalDestination as FinalDestination from ScrmPoData as SPD 
			left JOIN ScrmShipmentDetails as SSD on SSD.PiID=SPD.PoID 
			 
			LEFT JOIN AdminExporterDetails as AED on AED.ExpID=SPD.ExporterID  
			LEFT JOIN ScrmProductionData as SP on SP.PIID=SPD.PoID 
			LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
			LEFT JOIN ScrmLogisticsDetails as SLD on SLD.PiID=SPD.PoID 
			LEFT JOIN ScrmLinerMaster as SLM on SP.LinerId=SLM.LinerID 
			left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID 			
			where   ProductionStartedDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) $Query  order by SPD.PoID desc"; 
		$StmtPo=$iconn->query($SqlPo);
	
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=11;
			  $width_s=8;
			  $width_w=$width+5;
              
			    $content .= '
				<h3 align="center">'.$ExporterName.'</h3>
			  <h3 align="center"> Running Orders Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="5">
			   <tr>  

           		

				<th  align="center" ><B>S No.</B></th>           
				<th  align="center" ><B>Report ID</B></th>           
				<th  align="center"	><B>PLANT</B></th>
				<th  align="center" ><B>UNIT</B></th>	
				<th  align="center" ><B>PO#</B></th>					   
				<th  align="center" ><B>PRODUCT</B></th>					   
				<th  align="center"  ><B>BRAND</B></th>			
				<th  align="center"  ><B>STOCK STATUS</B></th>					
				<th  align="center"  ><B>POD</B></th>			
                <th  align="center"   <B>BUYER INSPECTION STATUS</B></th>
                <th  align="center"   <B>LAB RESULTS STATUS</B></th>
                <th  align="center"   <B>LOADING INDENT STATUS</B></th>
               <!-- <th  align="center"   <B>BOOKED CONTAINERS </B></th>-->
                <th  align="center"   <B>LINER</B></th>
                <th  align="center"   <B>VEHICLE STATUS</B></th>
                <th  align="center"   <B>CONTAINER BOOKED</B></th>
                <th  align="center"   <B>PO SHIP DATE</B></th>
                <th  align="center"   <B>REMARKS</B></th>
                
                
    
               </tr> 
			   
			  '; 
			  
	
		//	
$TotalFOBUS=0;
$TotalFOB=0;
$GrandTotalDiscount=0;
$TotalNetWgtKgs=0;
$TotalNetWgtLbs=0;

		while($RowPo = $StmtPo->fetch_array()){
			$Plant=explode('/',$RowPo['InvShortCode']);
			$PoID=$RowPo['PoID'];
			$NameofRepresentative =$RowPo['NameofRepresentative '];
			$ProcessorID=$RowPo['ProcessorID'];
			if($ProcessorID==1){
			$Unit='1';	
			}
			else{
				$Unit='2';
			}
			
			/*$SqlMcs="select sum(AvalMcs) as AvalMcs from ScrmProductionDataAvalMcs where PoID=$PoID";
	   $StmtMcs=$iconn->query($SqlMcs);
	   $RowMcs=$StmtMcs->fetch_array();
	 
	  $AvalMcs = $RowMcs['AvalMcs']; */

	
			
			$AryShortName=array();
			$AryGrades=array();
			$RequiredMcs=0;
			 $SqlVariety="SELECT * FROM `$TblScrmPoGradeData` SPDT LEFT join 
			$ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID LEFT join 
			$TblAdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID WHERE InvId='$PoID'"; 
			$Stmtvariety=$iconn->query($SqlVariety);
			
						while($RowVariety=$Stmtvariety->fetch_array()){

	$AryShortName[]=$RowVariety['ShortName'];
	$AryGrades[]=$RowVariety['Grade'];
	$Brand=$RowVariety['Brand'];
	$Mcs=$RowVariety['Mcs'];
	$AvalMcs = $RowVariety['CurrentMcs'];
	
	 $RequiredMcs+=$Mcs; 
						}
						$Grades=implode(",",array_unique($AryGrades));
						//$Grades=implode(",",array_unique(explode(",",$Grades)));
                        $ShortName=implode(",",array_unique($AryShortName)); 
						$Product=$ShortName.' '.$Grades;
                        $StockPercentage=($AvalMcs/$RequiredMcs)*100;
		    $content .= 
					
					'<tr>
				<td  align="center" >'.$S_NO.'</td>
				<td  align="center" >'.$RowPo['PoID'].'</td>
				<td  align="center" >'.$Plant[0].'</td>
				<td  align="center" >'.$Unit.'</td>';
				 if($NameofRepresentative==''){ 
				$content .=  '<td  align="center" ><font color="red">'.$RowPo['PoNo'].'</font></td>';
				 }
				 
				 else{ 
				$content .=  '<td  align="center" >'.$RowPo['PoNo'].'</td>';
				 }

				$content .= ' 
				 <td  align="center"  >'.$Product.'</td>
				 <td  align="center"  >'.$Brand.'</td>
				 <td  align="center"  >'.number_format($StockPercentage,2).'%</td>	
				 <td  align="center"  >'.$RowPo['Port'].'</td>

				 <td  align="center"  >'.$RowPo['BuyerInseptionStatus'].'</td>
				 <td  align="center"  >'.$RowPo['LabResultStatus'].'</td>
				 <td  align="center"  >'.$RowPo['LoadingIndentStatus'].'</td>
				 <td  align="center"  >'.$RowPo['LinerName'].'</td>

				 <td  align="center"  >'.$RowPo['VehicleStatus'].'</td>
				 <td  align="center"  >'.$RowPo['FinalDestination'].'</td>
				 <td  align="center"  >'.getFormatedDate($RowPo['ShipByDate']).'</td>
				 <td  align="center"  >'.$RowPo['Remarks'].'</td>
				 
				  
				 </tr>';

 $S_NO++;			 
}



		$content .= '</table>';

$filename='DocRunningOrdersReport -'.$from_date.'-'.$to_date.'.xls';

		header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=".$filename."");
	echo $content;	


?>