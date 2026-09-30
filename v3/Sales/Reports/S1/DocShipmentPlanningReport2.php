<style>
table tr td{
	
	    font-size: 16px;
    text-align: left;
}

table tr th{
	
	    font-size: 13px;
    text-align: left;
	 background: white;
  position: sticky;
  top: 0;
}


</style>
<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];

$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
$Exporter=$_GET['Exporter']; 
//echo $PROCESSOR_APP_NUM; exit;
   
if($Exporter==''){
	$Query='';
}
else{
if($Exporter==1){
	$Query='and ExporterID!=5';
}
	else{
	
	$Query=' and ExporterID='.$Exporter;
	}
	 $SqlExporter="SELECT * FROM $TblAdminExporterDetails where ExpID=$Exporter"; 
				  $StmtExporter=$iconn->query($SqlExporter);

$RowExporter= $StmtExporter->fetch_array();	
			 $ExporterName=$RowExporter['ExpName'];
	  
}

$file_name='FOB Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	
	/* $SqlPo="SELECT *,
(SELECT SUM(Mcs) from $TblScrmPoGradeData WHERE InvId=PoID) as TotalMcs
FROM $TblScrmPIData as SPD LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN $TblScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge WHERE   
NOT EXISTS (SELECT PiID  FROM $ScrmCustomInvoices as SCI  WHERE SCI.PiID = SPD.PoID )  and 
PoDate BETWEEN '$from_date' and '$date_end' $Query  order by PoNo Asc,PoDate Asc ";*/
		$SqlPo="SELECT *,
(SELECT SUM(Mcs) from $TblScrmPoGradeData WHERE InvId=PoID) as TotalMcs
FROM $TblScrmPIData as SPD LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN $TblScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge    
LEFT JOIN ScrmShipmentDispatchDetails as SDD on SDD.PIID=SPD.PoID
 WHERE SPD.Status>='5'  and SDD.SealTime='' and 
PoDate BETWEEN '$from_date' and '$date_end' $Query  order by PoNo Asc,PoDate Asc ";
			  $StmtPo=$iconn->query($SqlPo);
	

	
			 
			  

	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=0;
			  $width=11;
			 // $width_s=10;
			  //$width_w=$width+5;
              
			    $content .= '
				<h3 align="center">'.$ExporterName.'</h3>
			  <h3 align="center">ShipmentPlanningReport ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table width=100 border="1" cellspacing="0" cellpadding="3">
			   <tr>  

           		

                   <th  align="center"   >S No.</th>              
				   <th  align="center"  >REPORT ID</th>
				   <th  align="center"  >ORDER NO</th>
				   <th  align="center"  ><B> PRODUCT</th>
				   <th  align="center"  ><B>BRAND</B></th>					   
				   <th  align="center"  ><B>STOCK STATUS</B></th>					   
				   <th  align="center"  ><B>DESTINATION</B></th>			
				   <th  align="center"  ><B>BUYER INSPECTION STATUS</B></th>			
                   <th  align="center"  ><B>LAB RESULTS STATUS</B></th>
				   <th  align="center"  ><B>LOADING INDENT STATUS</B></th>	
				   <th  align="center"  ><B>VEHICLE STATUS</B></th>			
				   <th  align="center"  ><B>CONTAINR BOOKED</B></th>			
				   <th  align="center"  ><B>REMAKRS</B></th>			
               </tr> '; 
			  
		while($RowPo = $StmtPo->fetch_array()){
			$TotalMcs=$RowPo['TotalMcs'];
			$ProcessorID=$RowPo['ProcessorID'];

$SqlApprovalNo="SELECT * FROM `$TblAdminExporterDetails` where ExpID='$ProcessorID'";
$StmtApprovalNo=$iconn->query($SqlApprovalNo);
$RowApproval=$StmtApprovalNo->fetch_array();
				if($DestinationCountryId==26){
				$Destination=$RowPo['Name'];
				}else{
				$Destination=$RowPo['Name'].' , '.$RowPo['CountryCode'];
				}			
			
			$content .= 
					
					'<tr>
				 
				 <td  align="center" >'.++$S_NO.'</td>
				 <td  align="center" >'.$RowPo['PoID'].'</td>
				 <td  align="center" >'.$RowPo['PoNo'].'</td>
				 <td  align="center" >'.$RowPo[''].'</td>
				 <td  align="left" >'.$RowPo['Brand'].'</td>
				 <td  align="center" ></td>				 
				 <td  align="center"  >'.strtoupper($Destination).'</td>				 
				 <td  align="center" ></td>
				 <td  align="center" >'.$TotalMcs.'</td>
				 <td  align="center" ></td>
				 
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 
				 </tr>';
				 
				 
				 

			$VarietyID=$RowPo['VarietyID'];
			 $DestinationCountryId=$RowPo['DestinationCountry'];
				
				
				$PoID=$RowPo['PoID']; 

  

			
			
			 $SqlVariety="SELECT * FROM `$TblScrmPoGradeData` SPDT LEFT join 
			$ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID LEFT join 
			$TblAdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID WHERE InvId='$PoID'"; 
			$Stmtvariety=$iconn->query($SqlVariety);
			while($RowVariety=$Stmtvariety->fetch_array()){
			
			
		    $content .= 
					
					'<tr>
				 <td  align="center"  ></td>

				 <td  align="center" ></td>
				 <td  align="center" >'.$RowVariety['ShortName'].'</td>
				 <td  align="center" ></td>
				 <td  align="left" >'.$RowVariety['Brand'].'</td>
				 <td  align="center" ></td>
				 <td  align="center" ></td>
				 <td  align="center" ></td>
				 
				 <td  align="center" ></td>
				 <td  align="center" >'.$RowVariety['ShortName'].'</td>
				 <td  align="center" >'.$RowVariety['PackingStyle'].'</td>
				 <td  align="center" >'.$RowVariety['Grade'].'</td>
				 <td  align="center" >'.$RowVariety['Mcs'].'</td>
				  
				 </tr>';
			}

			 
}


		$content .= '</table>';


	echo $content;	


?>