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
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
   $Header='ORDER`S LIST REPORT';



 $Year=$_GET['Year']; 
 $Exporter=$_GET['Exporter']; 
 
 /*if($Year==''){
	echo "Unable To Parse Year";
	exit;	
 }*/
 
 $DateFilter=getFinancialYear($Year);
 
$Query='';
if($Exporter!=''){
$Query.="and ExporterID in ($Exporter)";
}

/*
	
	$SqlExporter="SELECT * FROM $TblAdminExporterDetails where ExpID=$Exporter"; 
				  $StmtExporter=$iconn->query($SqlExporter);

			 $RowExporter= $StmtExporter->fetch_array();	
			 $ExporterName=$RowExporter['ExpName'];
			 
			 */
	  


$file_name='Orders List -'.$from_date.'-'.$to_date.'.xlsx';

		$date_end=$to_date.' 23:59:59';  
		
		
		    /*$sql="SELECT GROUP_CONCAT(PoID) as PoID  FROM $TblScrmPoData as SPD 
					left join $TblScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join $TblScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
					WHERE SPD.InvDate  $DateFilter  AND Status not in(0,1,2,4,7) and 
					SPD.PoID not in(SELECT PIID FROM `ScrmShipmentDispatchDetails` WHERE SealTime!='')  
					$Query order by PoID desc"; */
					
					
					$sql="SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
					WHERE AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) 
					$Query order by PoID desc";

			$stmt=$iconn->query($sql);					
			$RowData=$stmt-> fetch_array();
             $PoID=$RowData['PoID'];
			 $AryPoID=explode(',',$PoID);
			 $OrderCount= Count($AryPoID); 
			 //echo  '<h4><b>'.$OrderCount.' -  Order Found </b> </h4>';
		
	
	
		 $SqlGroup="SELECT Category,ShortName,GROUP_CONCAT(VarietyID) as VarietyID 
		FROM `ScrmPIVarietyList` GROUP BY Category order by Category";
		$StmtGroup=$iconn->query($SqlGroup);
		
		
		while($RowData=$StmtGroup-> fetch_array()){
		
		$AryData= getTableDate($RowData['VarietyID'],$PoID);	
		echo $AryData['TableData'];
		if($AryData['RowCount']>0){
			//echo '<h3>'.$RowData['Category']. '-'. $RowData['ShortName']  .'</h3>';
			echo '<h3>'. $RowData['ShortName']  .'</h3>';
			echo $AryData['TableData'];
		}
		}
	

	
	function getTableDate($VarietyID,$PoID){
	global $iconn,$Query;
	 $sql="SELECT *,PoID,PoNo,PODate,PreparedBy,Name,ShipByDate,
sum(SPGD.Mcs) as Mcs,GROUP_CONCAT(SPGD.Grade) as Grade FROM ScrmPoData as SPD
LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
WHERE   SPGD.VarietyID in ($VarietyID)
and SPD.PoID  in($PoID) 

GROUP by PoID order by PoID Asc"; 


$stmt=$iconn->query($sql);


$data='<table border="1" cellspacing="0" cellpadding="3">										
		
<thead class="thead-light">
				   <th  align="center"   >S No.</th>              
				   <th  align="center"  ><B>S.Bill No</B></th>
				   <th  align="center"  ><B>Shipping Date</B></th>
				   <th  align="center"  ><B>Inv No</B></th>					   
				 
				   <th  align="center"  ><B>Item</B></th>	
				  
                   <th  align="center"  ><B>MPEDA Quantity in KGS(DBK)</B></th>
				   <th  align="center"  ><B>FOB Value INR</B></th>			
				   <th  align="center"  ><B>FOB Value in Foreign Currency</B></th>						   
				  			
				   <th  align="center"  ><B>Port of Destination</B></th>			
				 
		
    </thead>
	<tbody>';
				
$Sno=0;
				
  while($row= $stmt->fetch_array()){
	
			 


$LastUpdatedDate = date('d-m-Y',strtotime($row['LastUpdatedDate']));




if($row['AvalMcs']==''){
	$AvalMcs='0';
} 
else{
	$AvalMcs=$row['AvalMcs'];
}


$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");

$ShipByDate=getFormatedDate($row['ShipByDate']);		
$PoDate=getFormatedDate($row['PoDate']);		
		


$PoID=$row['PoID'];
$BuyerAgent=$row['PoID'];
$Plant=$row['PoID'];
$Brand=$row['Brand'];
$Grade=$row['Grade'];
 $PreparedBy=$row['PreparedBy'];
$Destination=$row['Name'];
$ShipByDate=$row['ShipByDate'];
$Rcount=0;		
$AryPoID[]=$PoID;	
	$data.='<tr> 
	<td> '. ++$Sno.'</td>		
	<td> sbno</td> 
	<td> sbdate</td> 
	<td> Inv No</td>
	<td> Short Name</td>
	 <td>MPEDA Quantity in KGS(DBK)</td>
	<td>fob value inr</td>
	<td>fob value usd</td>
	<td >'.$Destination.'</td></tr>
	';
				   
				   

				
				/*$SqlVariety="SELECT sum(Mcs) as Mcs,ShortName,
				 GROUP_CONCAT(APS.PackingStyle) as PackingStyle,
				GROUP_CONCAT(SPDT.Grade,'-',SPDT.Mcs) as Grade	FROM `ScrmPoGradeData` SPDT lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID LEFT join ScrmPackingData SPD on SPDT.ID=SPD.POGradeId left Join ScrmPIVarietyList as SVL on 
				SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
				and SPDT.VarietyID in ($VarietyID) GROUP By SPDT.VarietyID";  
			$Stmtvariety=$iconn->query($SqlVariety);
			$RowCount=mysqli_num_rows($Stmtvariety);
			if($RowCount==0){
				
	$data.='<td ></td>
	
	<td ></td>
	<td ></td>
	<td ></td>
				
	</tr>';
			}
				
			while($RowVariety=$Stmtvariety->fetch_array()){
			
			if($Rcount==0){
				
			}else{
				$data .='
				 
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>'; 
			}
			
			
			
		   

				  $data .='
				 <td>'.$RowVariety['ShortName'].'</td>
				 <td>MPEDA Quantity in KGS(DBK)</td>
				 <td>fob value inr</td>
				 <td>fob value usd</td>
				
				';
				 
				 if($Rcount==0){
				$data .='
				 <td >'.$Destination.'</td>
				'; 
			}else{
				$data .='
				 <td ></td>
				 <td ></td>'; 
			}
			
				$Rcount++;		 
        $data .='  </tr>';
				  
				 
			}	*/
	 
}
  $data .=' </tbody></table>';
$AryData['TableData']=$data;
//$AryData['RowCount']=$Rcount;
	

	 return $AryData;
	
	 
	}



?>