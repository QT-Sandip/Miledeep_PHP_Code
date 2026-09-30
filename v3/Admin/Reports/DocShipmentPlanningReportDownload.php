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
$table_data=getP_TABLE($iconn);
	echo $table_data;
	function getP_TABLE($iconn){

 global $from_date,$to_date,$Exporter,$ScrmCIGradeData,	$ScrmCustomInvoices,$TblScrmPIData,
 $TblScrmConsigneeDetails,$TblAdminExporterDetails,$TblScrmPortCities,$TblScrmPoGradeData,$ScrmPIVarietyList,$TblAdminPackingStyles,$TblScrmPoGradeData
;


   
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

$filename='Shipping Planning Report -'.$from_date.'-'.$to_date.'.xls';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	 $SqlPo="SELECT *,
(SELECT SUM(Mcs) from $TblScrmPoGradeData WHERE InvId=PoID) as TotalMcs
FROM $TblScrmPIData as SPD LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN $TblScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge WHERE   
NOT EXISTS (SELECT PiID  FROM $ScrmCustomInvoices as SCI  WHERE SCI.PiID = SPD.PoID )  and 
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
			  <h3 align="center"> SHIPMENT PLANNING REPORT ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="4">
			   <tr>  

           		

                <th  align="center"  width="3%">S No.</th>              
				 
				<th  align="center" width="'.$width_s.'%" >REPORT ID</th>
				<th  align="center" width="'.$width_s.'%" >ORDER NO</th>
				   <th  align="center"  width="'.$width_s.'%"><B> BUYER NAME</B></th>					   
				   <th  align="center"  width="'.$width_s.'%"><B>BRAND</B></th>					   
				   <th  align="center"  width="'.$width_s.'%"><B>PACKAGING READY</B></th>					   
				   <th  align="center"  width="'.$width_s.'%"><B>CODE ISSUE</B></th>			
				   <th  align="center"  width="'.$width_s.'%"><B>LAB SAMPLE</B></th>			
				   		
                   <th  align="center"  width="'.$width_s.'%" <B>MASTER CARTONS</B></th>
				   <th  align="center"  width="'.$width_s.'%"><B>VARIETY</B></th>	
				   <th  align="center"  width="'.$width_s.'%"><B>PACK</B></th>			
				   <th  align="center"  width="'.$width_s.'%"><B>GRADE</B></th>			
				   <th  align="center"  width="'.$width_s.'%"><B>QTY</B></th>			
				   <th  align="center"  width="'.$width_s.'%"><B>MAJOR GRADE</B></th>			
				   <th  align="center"  width="'.$width_s.'%"><B>SHIPMENT DATE AS PER PO</B></th>			
				   <th  align="center"  width="'.$width_s.'%"><B>DESTINATION</B></th>			
				   <th  align="center"  width="'.$width_s.'%"><B>DATA ENTERED BY</B></th>			

                
    
               </tr> 
			   
			  '; 
			  


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
				 <td  align="center" width="3%">'.++$S_NO.'</td>

				 <td  align="center" width="'.$width_s.'%">'.$RowPo['PoID'].'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['PoNo'].'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['ConsigneeName'].'</td>
				 <td  align="left" width="'.$width_s.'%">'.$RowPo['Brand'].'</td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%">'.$RowApproval['ExpApprovalNo'].'</td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%">'.$TotalMcs.'</td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" >	</td>
				 <td  align="center" width="'.$width_s.'%" >'.date('d-m-Y',strtotime($RowPo['ShipByDate'])).'</td>
				 <td  align="center" width="'.$width_s.'%" >'.strtoupper($Destination).'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowPo['PreparedBy'].'</td>
				  
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
				 <td  align="center" width="3%"></td>

				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="left" width="'.$width_s.'%">'.$RowVariety['Brand'].'</td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%"></ttdh>
				 
				 <td  align="center" width="'.$width_s.'%"></td>
				 <td  align="center" width="'.$width_s.'%">'.$RowVariety['ShortName'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['PackingStyle'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['Grade'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['Mcs'].'</td>
				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['FreezingType'].'</td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				  
				 </tr>';
			}

			 
}


		$content .= '</table>';
header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=".$filename."");


	echo $content;	
	}

?>