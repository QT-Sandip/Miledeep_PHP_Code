<?php
//session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

?>


<?PHP
$uid=$_SESSION['r_id'];
$D_ID=$_SESSION["D_ID"];

  $AccessType=$_SESSION["ACCESS_TYPE"]; 
$YEAR=$_POST['YEAR'];
   $EXPORTER=$_POST['EXPORTER'];
 $Buyer=$_POST['Buyer']; 
 $Country=$_POST['Country']; 
$TYPE=$_POST['TYPE'];


$_SESSION["SelectedYear"]=$_POST['YEAR'];


if($EXPORTER!=""){
	$Query.='and POD.ExporterID in ('.$EXPORTER.')';
	
}

 
  /*$sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID,
(SELECT sum(AvalMcs) FROM `ScrmProductionDataAvalMcs` PAM  WHERE PAM.PoID= POD.PoID) as AvalMcs,
(SELECT sum(AvalMcs) FROM `ScrmPoGradeData` as SPGD WHERE SPGD.InvId= POD.PoID) as OrderMcs
  FROM ScrmPoData as POD 
left join ScrmProductionData as SPD on SPD.PIID=POD.PoID 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on SCL.id=POD.DestinationCountry 
WHERE POD.PoID not in(SELECT PIID FROM `ScrmShipmentDispatchDetails` WHERE SealTime!='')

$Query order by abs(VehicleStatus) desc";  */


 $sql="SELECT *,name as CountryName,POD.ExporterID as ExporterID,
(SELECT sum(AvalMcs) FROM `ScrmProductionDataAvalMcs` PAM  WHERE PAM.PoID= POD.PoID) as AvalMcs,
(SELECT sum(AvalMcs) FROM `ScrmPoGradeData` as SPGD WHERE SPGD.InvId= POD.PoID) as OrderMcs
  FROM ScrmPoData as POD 
left join ScrmProductionData as SPD on SPD.PIID=POD.PoID 
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
left join ScrmCountriesList as SCL on SCL.id=POD.DestinationCountry
left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=POD.PoID
left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID 
 
WHERE ProductionStartedDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null)

$Query order by abs(VehicleStatus) desc"; 
 
$stmt=$iconn->query($sql);
?>

 <table  id="key-datatable" data-paging="false" class="table dt-responsive wrap w-100">										
		
<thead class="thead-light">
 
	   <th>ID</th>	
	   <th>C.Inv No</th>
	   <th>C.Inv Date</th>
	   <th>Po No</th>
	   <th>Po Date</th>
	   <th>Product</th>
	   <th>Brand</th>
	   <th>Destination</th>		
	   <th>Stock % </th>			   
	   <th>Buyer Insptn Status</th>
	   <th>Lab Result Status</th>		
	   <th>Loading Indent Status</th>		
	   <th>Liner</th>		
	   <th>Vehicle Status	</th>		
	   <th>Remarks</th>		
	   <th>Actions</th>
		

    </thead>
	<tbody>';
				
<?php
				
  while($row= $stmt->fetch_array()){
	  
	  
	  $PoID = $row['PoID'];
	   $SqlMcs="select sum(AvalMcs) as AvalMcs from ScrmProductionDataAvalMcs where PoID=$PoID";
	   $StmtMcs=$iconn->query($SqlMcs);
	   $RowMcs=$StmtMcs->fetch_array();
	 
	  $AvalMcs = $RowMcs['AvalMcs']; 

	
			 $AryShortName=array();
			$AryGrades=array();
			
			 $SqlVariety="SELECT * FROM `$TblScrmPoGradeData` SPDT 
			 LEFT join $ScrmPIVarietyList  PIL on SPDT.VarietyID=PIL.VarietyID 	
			 LEFT join $TblAdminPackingStyles  APS on SPDT.PackingStyleID=APS.PCKID 
             WHERE InvId='$PoID'";
			
			$Stmtvariety=$iconn->query($SqlVariety);
			$RequiredMcs=0;
			
						while($RowVariety=$Stmtvariety->fetch_array()){

	$AryShortName[]=$RowVariety['ShortName'];
	$AryGrades[]=$RowVariety['Grade'];
	$Brand=$RowVariety['Brand'];
	$Mcs=$RowVariety['Mcs'];
	
	 $RequiredMcs+=$Mcs; 
	 
						}
						$Grades=implode(",",array_unique($AryGrades));
						$ShortName=implode(",",array_unique($AryShortName)); 
						$Product=$ShortName.' '.$Grades;
                        $StockPercentage=($AvalMcs/$RequiredMcs)*100;
//echo $AvalMcs; exit;

$LastUpdatedDate = date('d-m-Y',strtotime($row['LastUpdatedDate']));
$PoDate = date('d-m-Y',strtotime($row['PoDate']));
$InvoiceDate = date('d-m-Y',strtotime($row['InvDate']));

if($PoDate=='01-01-1970'){
	$PoDate='*****';
} 

if($InvoiceDate=='01-01-1970'){
	
	$InvoiceDate='****';
}

if($LastUpdatedDate=='01-01-1970'){
	$LastUpdatedDate='*****';
}

if($row['AvalMcs']==''){
	$AvalMcs='0';
} 
else{
	$AvalMcs=$row['AvalMcs'];
}



$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");

		
		

$PoID=$row['PoID'];
		?>
		
	<tr>
	<!--<td> '. $row['PoID'].'</td>-->
			
<td> 
	 <a target="_blank" id="PoDetails" href="#"
	 data-id="<?php echo $row['PoID']; ?>" data-value="<?php echo$row['PoNo']; ?>"class="fill-div"><?php echo $row['PoID']; ?> </a>
	</td>	
	    <td> <?php echo $row['CInvNo'];?></td>
	    <td> <?php echo getFormatedDate($row['CInvDate']);?></td>
	    <td> <?php echo $PoNo;?></td>
	    <td> <?php echo $PoDate;?></td>
	    <td> <?php echo $Product;?></td>
	    <td> <?php echo $Brand;?></td>
	    <td> <?php echo $row['CountryName'];?></td>
	    <td> <?php echo number_format($StockPercentage,2).'%';?></td>
		 
		 
 <td> 
		  <input  type="text" class="form-control"  name="BuyerInseptionStatus" 
		  id="BuyerInseptionStatus<?php echo $row["PoID"]?>"
				   value="<?php echo $row['BuyerInseptionStatus']; ?>"></td>
<td> 
		  <input  type="text" class="form-control"  name="LabResultStatus" id="LabResultStatus<?php echo $row["PoID"]; ?>"
				   value="<?php echo $row['LabResultStatus']; ?>">
				   </td>
				   <td> 
		  <input  type="text" class="form-control"  name="LoadingIndentStatus"
		  id="LoadingIndentStatus<?php echo $row["PoID"];?>"
				   value="<?php echo  $row['LoadingIndentStatus']?>"></td>
				 
				 <td> 
				
				 <select  class="form-control select2" name="LinerId" 
				 id="LinerId<?php echo $row["PoID"];?>">				  
				 
				   <option value=""> Select</option>
			<?php 	
			 $SqlLiner="SELECT * FROM `ScrmLinerMaster`  WHERE Status=1"; 
             $StmtLiner=$iconn->query($SqlLiner);

		
		while($RowLiner=$StmtLiner->fetch_array()){ ?>
					<option 
					<?php if($RowLiner['LinerID']==$row['LinerId']){ ?> selected="selected" <?php }?>
					value="<?php echo $RowLiner['LinerID'];?>"><?php echo $RowLiner['LinerName'];?>
					</option>
				  <?php } ?>
			</select>	
		  </td>
				   				   
				   <td> 
		  <input  type="text" class="form-control"  name="VehicleStatus" id="VehicleStatus<?php echo $row["PoID"]; ?>"
				   value="<?php echo $row['VehicleStatus'];?>"></td>
				   <td> 
		  <input  type="text" class="form-control"  name="Remarks" id="Remarks<?php echo $row["PoID"];?>"
				   value="<?php echo $row['Remarks']; ?>"></td>
						
		</td>
		
	
			<td> 
		<?php 	if($AvalMcs==0){ ?>
			
		<button  id="BtnAddMcs"  value="<?php echo $row["PoID"];?>" align="Center" class="btn btn-success btn-sm btn-flat">
						 Add </button>
			<?php }
			else{ ?>
			
		<button  id="BtnAddMcs"  value="<?php echo $row["PoID"]; ?>" align="Center" class="btn btn-success btn-sm btn-flat">
		 Edit </button> 
					
		<?php 	} ?>
       </td>
			             
						
		</tr>
	  
<?php } ?>
	</tbody></table>
		 <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
<script>
$('document').ready(function(){
	
	
		  $("#LinerId").select2();

    

    }); 
</script>
