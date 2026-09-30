<style>
table tr td{
	
	    font-size: 15px;
    text-align: left;
}

table tr th{
	
	    font-size: 12px;
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
	
	
$SqlPo="SELECT *,
(SELECT SUM(Mcs) from $TblScrmPoGradeData WHERE InvId=PoID) as TotalMcs
FROM $TblScrmPIData as SPD LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN $TblScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge
LEFT JOIN ScrmShipmentDispatchDetails as SDD on SDD.PIID=SPD.PoID
LEFT JOIN ScrmPackingData as SP on SP.PIID=SPD.PoID
    
WHERE  SPD.Status>='5'  and SDD.SealTime='' "; 

//'and PoDate BETWEEN '$from_date' and '$date_end' $Query  order by PoNo Asc,PoDate Asc ";
			  $StmtPo=$iconn->query($SqlPo);
	

	
			 
			  

	
 
	$content='';


	$size=40;	 
 
	$size1=60;	



		//( date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )	  	
			  
			  $S_NO=0;
			  $width=11;
			 // $width_s=10;
			  //$width_w=$width+5;
              
			    $content .= '
				<h3 align="center">'.$ExporterName.'</h3>
			  <h3 align="center"> Headon Report </h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="4">
			   <tr>  

           		

                <th  align="center"  >S No.</th>              
				 
				<th  align="center"  ><B>PO NUMBER</B></th>					   
				   <th  align="center"  ><B>BUYER</B></th>
                   <th  align="center"   ><B>CODE</B></th>				   
				   
				<th  align="center"  ><B>HO COUNT</B></th>
				<th  align="center" ><B>VARIETY</B></th>	
				   <th  align="center"><B>GRADE</B></th>					   
				   <th  align="center"><B>BRAND</B></th>			
				   <th  align="center"><B>PACKING STYLE</B></th>			
				   <th  align="center"><B>SHIPMENT DATE</B></th>			
                <th  align="center" > <B>NO.OF CASE</B></th>
               
                <th  align="center" > <B>PACKING MATERIAL STATUS</B></th>
                <th  align="center" ><B>LAB SAMPLE SENT WITH DATE</B></th>
                <th  align="center" > <B>PO RECEIVED  DATE </B></th>
                <th  align="center"  ><B>DATE ON  WHICH  MAIL HAS BEEN SENT FOR ARTWORK APPROVAL</B></th>
                <th  align="center"  > <B>PACKAGING ORDER DATE</B></th>
                <th  align="center" > <B>PACKAGING RECEIVED DATE</B></th>
                
                
                
    
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
				 <td  align="center" >'.++$S_NO.'</td>

				
				 <td  align="center" >'.$RowPo['PoNo'].'</td>
				 <td  align="center" >'.$RowPo['ConsigneeName'].'</td>
				 <td  align="center" >'.$RowApproval['ExpApprovalNo'].'</td>

				 <td  align="center"></td>
                 <td  align="left" >'.$RowPo['Brand'].'</td>	
				 <td  align="center"></td>
				 
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  >'.date('d-m-Y',strtotime($RowPo['ShipByDate'])).'</td>
				 <td  align="center"  >	</td>
				 <td  align="center"  >'.$RowPo['MaterialStatus'].'	</td>
				 <td  align="center"  >	</td>

                 <td  align="center" >'.date('d-m-Y',strtotime($RowPo['PoDate'])).'</td>
				 <td  align="center" >'.$RowPo['MailSentDate'].'	</td>
				 <td  align="center"  >'.$RowPo['OrderedDate'].'	</td>
				 <td  align="center" >'.$RowPo['ReceivedDate'].'	</td>

				 
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
				 <td  align="center" ></td>

				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				  <td  align="center"  >'.$RowVariety['ShortName'].'</td>
				  <td  align="center"   >'.$RowVariety['Grade'].'</td>
				 <td  align="left"  >'.$RowVariety['Brand'].'</td>
				  <td  align="center"   >'.$RowVariety['PackingStyle'].'</td>
				 <td  align="center"  ></td>

				 <td  align="center"   >'.$RowVariety['Mcs'].'</td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 <td  align="center"  ></td>
				 
				 
				  
				 </tr>';
			}

			 
}


		$content .= '</table>';


	echo $content;	


?>