<style>
table tr td{
	
	    font-size: 17px;
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
   
if($Exporter==''){
	$Query='';
}
else{
if($Exporter==1){
	//$Query='and SPD.ExporterID!=5';
	$Query='and SPD.ExporterID!=5';
}
	else{
	
	//$Query=' and SPD.ExporterID='.$Exporter;
	$Query=' and SPD.ExporterID='.$Exporter;
	}
	 $SqlExporter="SELECT * FROM $TblAdminExporterDetails where ExpID=$Exporter"; 
				  $StmtExporter=$iconn->query($SqlExporter);

$RowExporter= $StmtExporter->fetch_array();	
			 $ExporterName=$RowExporter['ExpName'];
	  
}

$file_name='Shipment Report -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
	
	
		
	  $SqlPo="SELECT * FROM `$ScrmCustomInvoices` as SCI 
	 LEFT JOIN $TblScrmPIData as SPD on SPD.PoID=SCI.PiID
	  left JOIN $ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID

LEFT JOIN $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
LEFT JOIN $TblAdminExporterDetails as AED on AED.ExpID=SPD.ExporterID
LEFT JOIN ScrmShipmentDispatchDetails as SD on SD.PIID=SCI.PiID
LEFT JOIN ScrmProductionData as SP on SP.PIID=SCI.PiID
	 
	 where    SPD.Status>='5'  and SD.SealTime='' and PoDate BETWEEN '$from_date' and '$to_date' $Query order by CInvNo Asc,CInvDate Asc"; 
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
			  <h3 align="center"> Shipment Report ( '.date('d-M-Y',strtotime($from_date)).' to  '.date('d-M-Y',strtotime($to_date)).' )</h3>
			  
			  
			  <table border="1" cellspacing="0" cellpadding="5">
			   <tr>  

           		

				<th  align="center" ><B>S No.</B></th>           
				<th  align="center"	><B>PLANT</B></th>
				<th  align="center" ><B>UNIT</B></th>	
				<th  align="center" ><B>PO#</B></th>					   
				<th  align="center" ><B>PRODUCT</B></th>					   
				<th  align="center"  ><B>BRAND</B></th>			
				<th  align="center"  ><B>STOCK STATUS</B></th>					
				<th  align="center"  ><B>DESTINATION</B></th>			
                <th  align="center"   <B>BUYER INSPECTION STATUS</B></th>
                <th  align="center"   <B>LAB RESULTS STATUS</B></th>
                <th  align="center"   <B>LOADING INDENT STATUS</B></th>
                <th  align="center"   <B>BOOKED CONTAINERS </B></th>
                <th  align="center"   <B>VEHICLE STATUS</B></th>
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
			
			$AryShortName=array();
			$AryGrades=array();
			 $SqlVariety="SELECT * FROM `$TblScrmPoGradeData` SPDT LEFT join 
			$ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID LEFT join 
			$TblAdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID WHERE InvId='$PoID'"; 
			$Stmtvariety=$iconn->query($SqlVariety);
			
						while($RowVariety=$Stmtvariety->fetch_array()){

	$AryShortName[]=$RowVariety['ShortName'];
	$AryGrades[]=$RowVariety['Grade'];
	$Brand=$RowVariety['Brand'];
						}
						$Grades=implode(",",array_unique($AryGrades));
						//$Grades=implode(",",array_unique(explode(",",$Grades)));
                        $ShortName=implode(",",array_unique($AryShortName)); 
						$Product=$ShortName.' '.$Grades;

		    $content .= 
					
					'<tr>
				<td  align="center" >'.$S_NO.'</td>
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
				 <td  align="center"  >'.$RowPo['AvalMcs'].'</td>
				 <td  align="center"  >'.$RowPo['Destination'].'</td>
				 <td  align="center"  >'.$RowPo['BuyerInseptionStatus'].'</td>
				 <td  align="center"  ></td>
				 <td  align="center"  >'.$RowPo['LoadingIndentStatus'].'</td>
				 <td  align="center"  >'.$RowPo['BookedContainers'].'</td>
				 <td  align="center"  >'.$RowPo['VehicleStatus'].'</td>
				 <td  align="center"  >'.$RowPo['Remarks'].'</td>
				 
				  
				 </tr>';

 $S_NO++;			 
}



		$content .= '</table>';


	echo $content;	


?>