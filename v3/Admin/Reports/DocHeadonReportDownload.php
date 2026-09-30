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
			  <h3 align="center"> Headon Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="4">
			   <tr>  

           		

                <th  align="center"  width="3%">S No.</th>              
				 
				<th  align="center"  width="'.$width_s.'%"><B>PO NUMBER</B></th>					   
				   <th  align="center"  width="35%"><B>BUYER</B></th>
                   <th  align="center"  width="'.$width.'%" <B>CODE</B></th>				   
				   
				<th  align="center" width="'.$width_s.'%" ><B>HO COUNT</B></th>
				<th  align="center"  width="'.$width.'%"><B>VARIETY</B></th>	
				   <th  align="center"  width="'.$width_s.'%"><B>GRADE</B></th>					   
				   <th  align="center"  width="'.$width.'%"><B>BRAND</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>PACKING STYLE</B></th>			
				   <th  align="center"  width="'.$width.'%"><B>SHIPMENT DATE</B></th>			
                <th  align="center"  width="'.$width.'%" <B>NO.OF CASE</B></th>
               
                <th  align="center"  width="'.$width.'%" <B>PACKING MATERIAL STATUS</B></th>
                
                
                
    
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

				
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['PoNo'].'</td>
				 <td  align="center" width="'.$width_s.'%">'.$RowPo['ConsigneeName'].'</td>
				 				 <td  align="center" width="'.$width_s.'%">'.$RowApproval['ExpApprovalNo'].'</td>

				 <td  align="center" width="'.$width_s.'%"></td>
                 <td  align="left" width="'.$width_s.'%">'.$RowPo['Brand'].'</td>	
				 <td  align="center" width="'.$width_s.'%"></td>
				 
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" ></td>
				 <td  align="center" width="'.$width_s.'%" >'.date('d-m-Y',strtotime($RowPo['ShipByDate'])).'</td>
				 <td  align="center" width="'.$width_s.'%" >	</td>
				 <td  align="center" width="'.$width_s.'%" >	</td>
				 
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
				 <td  align="center" width="'.$width_s.'%"></td>
				  <td  align="center" width="'.$width_s.'%">'.$RowVariety['ShortName'].'</td>
				  <td  align="center" width="'.$width_s.'%" >'.$RowVariety['Grade'].'</td>
				 <td  align="left" width="'.$width_s.'%">'.$RowVariety['Brand'].'</td>
				  <td  align="center" width="'.$width_s.'%" >'.$RowVariety['PackingStyle'].'</td>
				 <td  align="center" width="'.$width_s.'%"></td>

				 <td  align="center" width="'.$width_s.'%" >'.$RowVariety['Mcs'].'</td>
				 <td  align="center" width="'.$width_s.'%"></td>
				 
				 
				  
				 </tr>';
			}

			 
}


		$content .= '</table>';

		$filename='DocHeadonReport -'.$from_date.'-'.$to_date.'.xls';

		header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=".$filename."");
	echo $content;	


?>