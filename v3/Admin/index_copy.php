<style>
label {
    font-size: 15px;
    color: black;
}

</style>

<style>
th {
    font-size: 12px;
}


.table tr:hover {
  background-color: #343a40 !important;
  color: #fff !important;
}


.table td, .table th {
	 font-size: 12px;
    padding: 7px !important;
    vertical-align: top;
    border-top: 1px solid #eff2f7;
}

.customWrap {
	white-space : nowrap !important;
}

		.warning-row {
            background-color: #f7ff84 !important;
            color: #111;
            font-weight: 500 !important;
        }
        
        .success-row {
            background-color: #fff !important;
        }
        
        .deadline-row {
            background-color: #df411d !important;
            color: #ffffff !important;
            font-weight: 500 !important;
        }

</style>

<?php
/* 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
//include "../includes/config.php";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$CurrentUrl = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$AccessType=$_SESSION["AccessType"];

function checkEmpty($Value){

	
		if($Value==''){
		$Value='--';	
		}
		
		return $Value;
	}




if($_GET['chbkExp'] == ''){
	$chbkExp='true';
}else{
	$chbkExp=$_GET['chbkExp'];
}


if($_GET['chbkShip'] == ''){
	$chbkShip='true';
}else{
	$chbkShip=$_GET['chbkShip'];
}

//Start Year From Config
//End Year If Current Year+1

$Year=$_GET['Year'];
$CurrentMonth=date('m');


if($Year ==''){
$Currentyear=date('Y');
}else{
$Currentyear=$Year;
}	

if($CurrentMonth > 3 &&  $Year==''){	
$FinClsYear=$Currentyear;	
}else if($Year!=''){
$FinClsYear=$Year;		
}else{
$FinClsYear=$Currentyear-1;
}


if($CurrentMonth > 3){	
$RunningFinStartYear=date('Y');	
}else {
$RunningFinStartYear=(date('Y')-1);		
}

 
$DateStart=$FinClsYear.'-04-01';
$DateEnd=($FinClsYear+1).'-03-31';

$NextDateStart=($FinClsYear+1).'-04-01';
$NextDateEnd=($FinClsYear+2).'-03-31';

$PastYearStart=($FinClsYear-1).'-04-01';
$PastYearEnd=($FinClsYear).'-03-31';
$PastNextYear=$FinClsYear ."-03-31";

$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);

$NextDateFilter="BETWEEN '$NextDateStart' and '$NextDateEnd'";
 $DateFilter="BETWEEN '$DateStart' and '$DateEnd'";
 $PastDateFilter="BETWEEN '$PastYearStart' and '$PastYearEnd'";


$CurrentFincialYear=$DateStart;



    $SqlAttDetails="SELECT COUNT(*) as Pending,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE ClosedDate!='' and FinYear='$Currentyear' ) 
as Closed,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE ShippedOn!='' 
 and ReturnedDate ='' and  (DeliveredDate='' or DeliveredDate is Null)  and FinYear='$Currentyear'
 ) as OnSea,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE 
TransportedDate!='' and (ShippedOn='' or ShippedOn is null) 
and (case when  $RunningFinStartYear=$FinClsYear then 
   (TransportedDate!='' and  ShippedOn='') 
 else FinYear='$Currentyear' end )) as InTransport, 
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE  CInvNo='' ) as OrderOnHand,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE ProductionStartedDate!='' and (TransportedDate='' or TransportedDate is null) ) as OpeningBalance,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE DeliveredDate!='' and  (ClosedDate='' or ClosedDate is null )  ) as Delivered
from ScrmShipmentDetailsBreif;"; 
$StmtAttDetails=$iconn->query($SqlAttDetails);
//ECHO $SqlAttDetails;
  $SqlOrderOnHand="SELECT count(*) as OpeningBalance   FROM `ScrmPoData` as SPD LEFT JOIN
ScrmShipmentDetailsBreif as SSB On SPD.PoID=SSB.PiID 
WHERE   SPD.PoDate $PastDateFilter and (SSB.TransportedDate > '$PastNextYear' 
or SSB.TransportedDate=''  or SSB.TransportedDate is null )";



 $SqlOrderOnHand="SELECT count(*) as OpeningBalance   FROM `ScrmPoData` as SPD LEFT JOIN
ScrmShipmentDetailsBreif as SSB On SPD.PoID=SSB.PiID 
WHERE SPD.PoDate < '$DateStart' and (SSB.FinYear=$FinClsYear or SSB.FinYear='')";
$StmtOrderOnHand=$iconn->query($SqlOrderOnHand);

//echo $SqlOrderOnHand;

$AttDataRow=$StmtAttDetails->fetch_array();
$OrdersOnHandRow=$StmtOrderOnHand->fetch_array();


$Closed=$AttDataRow['Closed'];
//$OnSea=$AttDataRow['OnSea'];
$InTransport=$AttDataRow['InTransport'];
 $OrderOnHand=$AttDataRow['OrderOnHand'];
 $OpeningBalance=$OrdersOnHandRow['OpeningBalance'];
$Delivered=$AttDataRow['Delivered']; 


   $SqlShiftDetails="SELECT COUNT(*) Total,COALESCE(Name,'Other') as Name ,
  Status,COALESCE(Color,'#ff3d60')as Color from ScrmPIData as SPD 
 LEFT join ScrmShipmentStatusCodes as SSC On SPD.Status=SSC.Code 
 WHERE SPD.PoDate $DateFilter
 GROUP by Status";
$StmtShiftDetails=$iconn->query($SqlShiftDetails);
while($AttShiftRow=$StmtShiftDetails->fetch_array()){
$AttShiftRow['Color']= $AttShiftRow['Color'];
$Series[]=$AttShiftRow['Total']+0;
$Lables[]=$AttShiftRow['Name'];
$Colors[]=$AttShiftRow['Color'];
$TotalPos=array_sum($Series);

$AryShiftDtls[]=$AttShiftRow;
	
}
 



$SqlBuyerData="SELECT ConID,PoID, MAX(PoDate)as LastPoDate, ConsigneeName, count(*) as TotalPo,
(SELECT sum(Mcs*TotalSlabs*SlabWeight*Mfactor) from ScrmPoGradeData as PGD 
 LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID WHERE PGD.InvId=SPD.PoID ) as Kgs,
(SELECT sum(Mcs*TotalSlabs*SlabWeight*Price) from ScrmPoGradeData as PGD 
 LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID WHERE PGD.InvId=SPD.PoID ) as Price 
FROM `ScrmPoData` as SPD
LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID   
 WHERE SPD.PoDate  $DateFilter GROUP by ConID order by TotalPo Desc";
$BuyerDataExec=$iconn->query($SqlBuyerData);



$DelayedShimpentsSql="SELECT PoNo,PoDate,ShipByDate,ShippedOn,
DATEDIFF(SCI.TransportedDate,SPD.ShipByDate) as DelayedDays from ScrmShipmentDetailsBreif as SCI
LEFT JOIN ScrmPoData as SPD on SCI.PiID=SPD.PoID
WHERE  DATE_SUB(SCI.ShippedOn, INTERVAL 10 DAY) > SPD.ShipByDate  and ShippedOn!='' ORDER by ShipByDate ASC";
$DelayedShimpentsExec=$iconn->query($DelayedShimpentsSql);
$TotalDelayedShipments=mysqli_num_rows($DelayedShimpentsExec);  

//$DateFilter;
$AryPos=array();

   $SqlLate="SELECT SPD.*,ConsigneeName,SCL.name as CountryName,
  SPC.Name as PortOfDischarge,SDB.CInvNo,
`ShippedOn`, `BLNumber`, `ETA`, `ProductionStartedDate`, `TransportedDate`,SDB.TransferDate,
`ReturnedDate`, `ContainerNo`, `AvailableDate`,
 `ClosedDate`, `DeliveredDate`,COALESCE(DATEDIFF(SPD.ShipByDate,now())-1,0) as DaysRemaining,
 COALESCE(DATEDIFF(SPD.ShipByDate,SDB.TransportedDate),0) as DelayedDays
 from ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID= SCD.ConID
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
left join ScrmShipmentDetailsBreif as SDB on SPD.PoID=SDB.PiID
WHERE SDB.CInvNo =''  and (SDB.TransportedDate='' or SDB.TransportedDate is null)
order by SPD.ShipByDate DESC";
$StmtLate=$iconn->query($SqlLate);

while($LateRow=$StmtLate->fetch_array()){
	
	if($RunningFinStartYear==$FinClsYear){
		$AryPos[]=$LateRow;
	}
}


   $SqlLate="SELECT SPD.*,ConsigneeName,SCL.name as CountryName,
  SPC.Name as PortOfDischarge,SDB.CInvNo,
`ShippedOn`, `BLNumber`, `ETA`, `ProductionStartedDate`, `TransportedDate`,SDB.TransferDate,
`ReturnedDate`, `ContainerNo`, `AvailableDate`,
 `ClosedDate`, `DeliveredDate`,COALESCE(DATEDIFF(SPD.ShipByDate,now())-1,0) as DaysRemaining,
 COALESCE(DATEDIFF(SPD.ShipByDate,SDB.TransportedDate),0) as DelayedDays
 from ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID= SCD.ConID
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
left join ScrmShipmentDetailsBreif as SDB on SPD.PoID=SDB.PiID
WHERE SDB.CInvNo ='' and SDB.TransportedDate!='' 
order by SPD.ShipByDate DESC";
$StmtLate=$iconn->query($SqlLate);

while($LateRow=$StmtLate->fetch_array()){
	
	if($RunningFinStartYear==$FinClsYear){
		$AryPos[]=$LateRow;
	}
}

//$OrderOnHand= count($AryPos);
                                                                                                                          

   $SqlLate="SELECT SPD.*,ConsigneeName,SCL.name as CountryName,SPC.Name as PortOfDischarge,SDB.CInvNo,
`ShippedOn`, `BLNumber`, `ETA`, `ProductionStartedDate`, `TransportedDate`,`ReturnedDate`, 
`ContainerNo`, `AvailableDate`,SDB.TransferDate,
 `ClosedDate`, `DeliveredDate`,COALESCE(DATEDIFF(SPD.ShipByDate,now())-1,0) as DaysRemaining,
 COALESCE(DATEDIFF(SPD.ShipByDate,SDB.TransportedDate),0) as DelayedDays
 from ScrmPoData as SPD 
left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID= SCD.ConID
left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
left join ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
left join ScrmShipmentDetailsBreif as SDB on SPD.PoID=SDB.PiID
WHERE SDB.CInvNo !='' and  SDB.FinYear='$FinClsYear' 
order by CInvNo DESC,DelayedDays DESC";

//echo $SqlLate;
$StmtLate=$iconn->query($SqlLate);

while($LateRow=$StmtLate->fetch_array()){
	$AryPos[]=$LateRow;
	
}


$Closed=0;
$OnSea=0;
$InTransport=0;
//$OrderOnHand=0;
//$OpeningBalance=0;
//$Delivered=0;

foreach($AryPos as $PoItem ){

if($PoItem['ReturnedDate']!=''){
//$CurrentStatus='Returned';
}else if($PoItem['ClosedDate']!=''){ 
$Closed++;
} else if($PoItem['DeliveredDate']!=''){
//$Delivered++;
}else if($PoItem['ShippedOn']!=''){
   
$OnSea++;
}else if($PoItem['TransportedDate']!=''){
$InTransport++;
}else if($PoItem['ProductionStartedDate']!=''){
//$CurrentStatus='Production';
}else{
//$CurrentStatus='Issued';
}

//$OpeningBalance='';
	

}


  $SqlSales="SELECT MonthName(PID.InvDate) as MonthName,
GROUP_CONCAT(PoID) as InvIds from ScrmPIData as PID WHERE PID.InvDate 
between '$DateStart' and '$DateEnd'
 and PID.Status NOT IN(0,2,4) GROUP by PID.InvDate"; 
 
 
 $SqlSales="SELECT count(*) as Orders ,MonthName(PID.PoDate) as MonthName,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE Month(TransportedDate)=Month(PID.PoDate)) 
as Shipments,
GROUP_CONCAT(PoID) as InvIds from ScrmPoData  as PID

WHERE  PID.PoDate $DateFilter
GROUP by  Month(PID.PoDate)order by Month(PID.PoDate) asc"; 

/*
   $SqlSales="SELECT MN.MonthName as MonthName,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE Month(TransportedDate)=MN.Month 
and TransportedDate $DateFilter  and  FinYear='$FinClsYear') as Shipments,
(SELECT COUNT(*) from ScrmPoData WHERE Month(PoDate)=MN.Month 
and PoDate $DateFilter ) as Orders

from ScrmMonthNames as MN
left join  ScrmPoData as PID on Month(PID.PoDate)=MN.Month
and  PID.PoDate $DateFilter
GROUP by  Month(PID.PoDate)order by MN.SortOrder asc"; 
 

$SalesExec=$iconn->query($SqlSales);


while($SalesRow=$SalesExec->fetch_array()){
	if($SalesRow['MonthName'] !=''){
if(	$SalesRow['Shipments']!=0 || $SalesRow['Orders']!=0	){
	$InvIds=$SalesRow['InvIds'];	
	$MonthName[]=$SalesRow['MonthName'];
	$Orders[]=$SalesRow['Orders'];
	$Shipments[]=$SalesRow['Shipments'];
}
	}
}

*/


 $SqlSales="SELECT MN.MonthName as MonthName,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif as SDB left join ScrmPoData as SPD on SPD.PoID=SDB.PiID 
 WHERE Month(TransportedDate)=MN.Month and TransportedDate $DateFilter  $Qry) as Shipments,
(SELECT COUNT(*) from ScrmPoData WHERE Month(PoDate)=MN.Month and PoDate $DateFilter  $Qry) as Orders
from ScrmMonthNames as MN  order by SortOrder asc"; 
 
 
$SalesExec=$iconn->query($SqlSales);


while($SalesRow=$SalesExec->fetch_array()){
	

	$InvIds=$SalesRow['InvIds'];	
	$MonthName[]=$SalesRow['MonthName'];
	$Orders[]=$SalesRow['Orders'];
	$Shipments[]=$SalesRow['Shipments'];


}

$AryData['MonthName']=$MonthName;
$AryData['Orders']=$Orders;
$AryData['Shipments']=$Shipments;


/*
$SqlPorts="SELECT COALESCE(SC.name,'Na') as CountryName,
Max(SCI.PoDate) as LastShipment,
COALESCE(SPC.Name,'Na') as PortName,COUNT(*) as Total FROM `ScrmShipmentDetailsBreif` as  SSD LEFT join
ScrmPoData  as SCI on SCI.PoID=SSD.PiID
left join ScrmPortCities  as SPC on SCI.PortOfDischarge=SPC.PortId
left join ScrmCountriesList  as SC on SC.id=SPC.Country
WHERE SCI.PoDate $DateFilter  and  FinYear='$FinClsYear'
 GROUP by PortofDischarge ORDER by Total DESC;";
*/
// ROBO 
  $SqlPorts="SELECT COALESCE(SC.name,'Na') as CountryName,
Max(SCI.PoDate) as LastShipment,
COALESCE(SPC.Name,'Na') as PortName,COUNT(*) as Total FROM `ScrmShipmentDetailsBreif` as  SSD LEFT join
ScrmPoData  as SCI on SCI.PoID=SSD.PiID
left join ScrmPortCities  as SPC on SCI.PortOfDischarge=SPC.PortId
left join ScrmCountriesList  as SC on SC.id=SPC.Country
WHERE SSD.CInvNo='' 
 GROUP by PortofDischarge ORDER by Total DESC;";
$StmtPorts=$iconn->query($SqlPorts);



//and SC.id=157
//echo $SqlPorts;

$PortsExec=$iconn->query($SqlPorts);
while($RowPorts=$PortsExec->fetch_array()){
$RowPorts['Color']= sprintf('#%06X', mt_rand(0, 0xFFFFFF));;
$PortSeries[]=$RowPorts['Total']+0;
$PortLables[]=$RowPorts['PortName'];
$PortColors[]=$RowPorts['Color'];

$AryPortDtls[]=$RowPorts;
	
}





$TotalOrders=array_sum($Orders);
$TotalShipments=array_sum($Shipments);


 $SqlOverAllShipments="SELECT COUNT(*) as OverAllShipments FROM ScrmShipmentDetailsBreif WHERE FinYear=Year('$PastNextYear') and TransportedDate $NextDateFilter";
$SqlOverAllShipmentsExec=$iconn->query($SqlOverAllShipments);
$SqlOverAllShipmentsRow=$SqlOverAllShipmentsExec->fetch_array(); 
$OverAllShipments=$SqlOverAllShipmentsRow['OverAllShipments'];
 


$SqlShipments="SELECT Grade,SUM(Mcs) as Mcs, 
	 sum(Mcs*TotalSlabs*SlabWeight*Price ) as Price,
	 sum(Mcs*TotalSlabs*SlabWeight*Mfactor ) as Kgs
					from ScrmPoGradeData as PGD
					left join AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID group by Grade
					ORDER by Abs(Grade);";

  $SqlShipments="SELECT count(*) as TotalOrders ,MonthName(PID.PoDate) as MonthName,
(SELECT COUNT(*) from ScrmShipmentDetailsBreif WHERE Month(ShippedOn)=Month(PID.PoDate)) as Shipments,
(SELECT COALESCE(GROUP_CONCAT(PiID),0) from ScrmShipmentDetailsBreif WHERE Month(ShippedOn)=Month(PID.PoDate)) as ShipPoIds,
COALESCE(GROUP_CONCAT(PoID),0) as ExportPoIds from ScrmPoData  as PID
WHERE  PID.PoDate $DateFilter order by Month(PID.PoDate) asc";   
 

  $SqlShipments="SELECT COALESCE(GROUP_CONCAT(PoID),0)  as ExportPoIds,
(SELECT COALESCE(GROUP_CONCAT(PiID),0) from ScrmShipmentDetailsBreif 
WHERE  (TransportedDate=''or TransportedDate is Null) ) as ShipPoIds
from ScrmMonthNames as MN
left join  ScrmPoData as PID on Month(PID.PoDate)=MN.Month
and  PID.PoDate $DateFilter  order by MN.SortOrder asc"; 


$ShipmentsExec=$iconn->query($SqlShipments);
$ShipmentsRow=$ShipmentsExec->fetch_array();
		$ExportPoIds=$ShipmentsRow['ExportPoIds'];
		$ShipPoIds=$ShipmentsRow['ShipPoIds']; 
		
		


  $SqlShipments="SELECT Grade, 
	 (SELECT COALESCE(sum(Mcs*TotalSlabs*SlabWeight*Mfactor),0) from ScrmPoGradeData as PGD1
					left join AdminPackingStyles as APS on APS.PCKID=PGD1.PackingStyleID 
                    WHERE InvId in ($ShipPoIds) and PGD1.Grade=PGD.Grade ) as Shipped,
      (SELECT COALESCE(sum(Mcs*TotalSlabs*SlabWeight*Mfactor),0) from ScrmPoGradeData as PGD2
					left join AdminPackingStyles as APS on APS.PCKID=PGD2.PackingStyleID 
                    WHERE InvId in ($ExportPoIds) and PGD2.Grade=PGD.Grade ) as Export
                   from ScrmPoGradeData as PGD
                    group by Grade
					ORDER by Abs(Grade)";


$ShipmentsExec=$iconn->query($SqlShipments);


while($ShipmentsRow=$ShipmentsExec->fetch_array()){

if($chbkExp=='false' && $chbkShip=='false' ){
}else if($chbkExp =='false'){
$ShipmentsRow['Export']=0;
}else if($chbkShip =='false'){
$ShipmentsRow['Shipped']=0;	
}
		

	$ExportKgs[]=round($ShipmentsRow['Export']/1,3);	
	$ShippedKgs[]=round($ShipmentsRow['Shipped']/1,3);	 
	$Grade[]=$ShipmentsRow['Grade'];
	
		}
		
		$TotalExportKgs=round(array_sum($ExportKgs));
		$TotalShippedKgs=round(array_sum($ShippedKgs));
	




/*
$TotalInvoiceValue=array_sum($ShipmentPrice);
$TotalMcsValue=array_sum($ShipmentMcs);
$TotalKgsValue=array_sum($ShipmentKgs);
$PerKgCost=$TotalInvoiceValue/$TotalKgsValue;
$TotalKgsValue=$TotalInvoiceValue/$TotalKgsValue;
*/

?>

<!doctype html>
<html lang="en">

    <head>
       <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- jquery.vectormap css -->
        <link href="<?php echo $BaseDist ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />  
		<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
      
<?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
<?php include ($IncludesPath."PreLoader.php"); ?>
        <!-- Begin page -->
        <div id="layout-wrapper">
			<?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php //include ($IncludesPath."page-title.php");?>
						<?php include "PopUpTable.php"; 
						
						?>

						 
						

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										
	  
				  	
      <div class="row">
    			


					
					
					
				  <div class="form-group col-lg-3">
                	  <label>Select Year: </label>
                   <select class="form-control select2" name="Year" id="Year">
                         
                           <?php
							 						
                            for($Startyear; $Startyear < $endYear; $Startyear++) { 
						
									$year=($Startyear).'-'.($Startyear+1); ?>
                                                            
                        <option  
						
						<?php echo $Startyear; if($Startyear == $FinClsYear){ ?> selected="selected" 
						<?php   }   	?>			
						
						
						
						
				   
				   value="<?php echo $Startyear ?>"><?php echo $year ?></option>
							
                                                         
							<?php }   ?>
                   <select>
				  </div>
				  
				 
				  
				  
				 <!-- <div class="col-lg-3 ">
                    <label>Select Buyer: </label>
				
				<select class="form-control select2 " name="Buyer" id="Buyer">
			 <option value=" " >All</option>

				   <?php while($RowConsignee=$StmtConsignee->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowConsignee['ConID'];?>"><?php echo $RowConsignee['ConsigneeName'].' - '.$RowConsignee['ConsigneeCountry'];?></OPTION>
				
				<?php } ?>
				<select> 
				</div>
					
			<div class="col-lg-3">
                    <label>Select Country: </label>
				
				<select class=" select2" name="Country" id="Country">
			 <option value=" " >All</option>

				   <?php while($RowCountries=$StmtCountries->fetch_array()) { 
				
				?>  
				
				<option Value="<?php echo $RowCountries['id'];?>"><?php echo $RowCountries['name'];?></OPTION>
				
				<?php } ?>
				<select> 

				
					</div> -->
				  </div> 
				  
				  
                <div align="CENTER" class="col-lg-12">
                                                     
                <button id="list" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                    </button> 
					<?php if($AccessType<3){ ?>
				<!-- <a target="_blank" href="PIGeneration.php" class="btn btn-primary waves-effect waves-light">
				<i class="fa fa-plus"></i> New PI</a> -->
					<?php } ?>
				
				</div> 
          

				 
											    <!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>--> 
	
									 
										</div>
                        <!-- end row -->
									</div>
									</div>
									
						
						
                            <div class="col-xl-8">
                                <div class="row">
								
								<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Opening Balance</p>
                                                        <h4 class="mb-0"><?php echo $OpeningBalance ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
									

                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Closed Shipments</p>
                                                        <h4 class="mb-0"><?php echo $Closed ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-stack-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>                                           
                                        </div>
                                    </div>
									
									<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Order On Hand</p>
                                                        <h4 class="mb-0"><?php echo $OrderOnHand ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-stack-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>

                                           
                                        </div>
                                    </div>
									
									
                                    
                                    
                                </div>
								<div class="row">
								
								<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">TransPort</p>
                                                        <h4 class="mb-0"><?php echo $InTransport ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
									
									<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Freight</p>
                                                        <h4 class="mb-0"><?php echo $OnSea ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
									
								
								
                                    
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Delivered</p>
                                                        <h4 class="mb-0"><?php echo $Delivered ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-store-2-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    
                                </div>
                                <!-- end row -->

                                <div class="card">
								
								
								
									<div class="card-body">
									<h4 class="card-title mb-4">Sales vs Shipments</h4>
                                    <div>
                                    <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                    </div>
                                    </div>

                                    <div class="card-body border-top text-center">
                                        <div class="row">
                                           
										   
										   <div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<i class="mdi mdi-circle text-primary font-size-10 mr-1">
													</i>Sales  -<?php
													echo $TotalOrders ?> </p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"></h5>
                                                        <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>Shipments-<?php
													echo $TotalShipments . ' (F.I.-'. ( $OverAllShipments ) . ')'?></p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"></h5>
                                                        <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div>
											
											
											
											
											
											
                                           

                                        </div>
                                    </div>
                                    </div>
								
								<!--/****************************/  -->
								 <div class="card">
                                    <div class="card-body">
									<h4 class="card-title mb-4">Export Qty vs To Ship Qty</h4>
                                    <div>
                                    <div id="shipment-chart" class="apex-charts" dir="ltr"></div>
                                    </div>
                                    </div> 
									
									

                                    <div class="card-body border-top text-center">
                                        <div class="row">
                                           

                                            <div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
													<input  type = "checkbox" id="chbkExp" <?php if($chbkExp !='false'){ ?>
																
																<?php } ?>>
													Export Qty- <?php echo $TotalExportKgs ?> Kgs</p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"></h5>
                                                        <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div>
											
											<div class="col-sm-4">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-2 text-muted text-truncate">
													<!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
													<input  type = "checkbox" id="chbkShip" 
													<?php if($chbkShip !='false'){ ?>
																checked
																<?php } ?>
														>

													To Ship Qty 
													- <?php echo
													$TotalShippedKgs ?> Kgs</p>
                                                    <div class="d-inline-flex">
                                                        <h5 class="mb-0 mr-2"></h5>
                                                        <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div>
											
													
                                           

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                       
                                        <h4 class="card-title mb-4">Proforma Details
										<?php echo $Fi='('. $FinClsYear . '-' .($FinClsYear+1) .')'; ?></h4>

                                        <div id="donut-chart" class="apex-charts"></div>

                                        <div class="row">
										<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													
													>
													</i>Total</p>
                                                    <h5><?php echo $TotalPos?></h5>
                                                </div>
                                            </div>
										 
                                            <?php foreach($AryShiftDtls as $ShiftRow){ ?>
												<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													class="mdi mdi-circle font-size-10 mr-1" 
													style="color: <?php echo $ShiftRow['Color'] ?>;">
													</i> <?php echo $ShiftRow['Name'] ?></p>
                                                    <h5><?php echo $ShiftRow['Total'] ?></h5>
                                                </div>
                                            </div>
										<?php	} ?>
											
											
                                           
                                        </div>
                                    </div>
                                </div>

                              <!--  <div class="card">
                                    <div class="card-body">
                                        

                                        <h4 class="card-title mb-4">On Time Vs Late Shipments </h4>
                                        <div class="text-center">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div>
                                                        <div class="mb-3">
                                                            <div id="radialchart-1" class="apex-charts"></div>
                                                        </div>

                                                        <p class="text-muted text-truncate mb-2">Late Shipments</p>
                                                        <h5 class="mb-0"><?php echo $OnTime ?></h5>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="mt-5 mt-sm-0">
                                                        <div class="mb-3">
                                                            <div id="radialchart-2" class="apex-charts"></div>
                                                        </div>

                                                        <p class="text-muted text-truncate mb-2">On Time Shipments</p>
                                                        <h5 class="mb-0"><?php echo $EmpCount ?></h5>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div> -->
								
								                                <div class="card">
                                    <div class="card-body">
                                       
                                        <h4 class="card-title mb-4">Port - Containers</h4>

                                        <div id="donut-chart-port" class="apex-charts"></div>

                                        <div class="row">
                                            <?php $Count=0; foreach($AryPortDtls as $ShiftRow){  $Count++;
											?>
												<div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
													class="mdi mdi-circle font-size-10 mr-1" 
													style="color: <?php echo $ShiftRow['Color'] ?>;">
													</i> <?php echo $ShiftRow['PortName'] ?></p>
                                                    <h5><?php echo $ShiftRow['Total'] ?></h5>
                                                </div>
                                            </div>
										<?php if( $Count==8){
											break;
											} }?>
											
											
                                           
                                        </div>
                                    </div>
                                </div>
								
								
                            </div>
                        </div>
                        <!-- end row -->

                     <!--    <div class="row">
                            
							<div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="dropdown float-right">
                                            <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </a>

                                        </div>

                                       <h4 class="card-title mb-4">Port Wise Orders</h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($PortRow=$StmtPorts->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                            <i class="ri-edit-2-fill"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13"><?php echo $PortRow['Total'] ." Container To ".$PortRow['PortName'] ; ?>
															<small class="text-muted">08:00 am</small></h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo $PortRow['CountryName'] .
															'-'.$PortRow['PortName'] ?> Last Shipment on  
															<?php echo date("d-M", strtotime($PortRow['LastShipment'])); ?>
															</p>
                                                        </div>
                                                    </div>
                                                </li>
											  <?php } ?>
                                              
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

							
							
							
							
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                      

                                        <h4 class="card-title mb-4">Buyer Orders</h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($BuyerDataRow=$BuyerDataExec->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
													<input id="ConID" hidden value="<?php echo $BuyerDataRow['ConID'] ?>" >
                                                        <span id="BuyerDetails"  class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                            <i class="ri-edit-2-fill"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13"><?php echo $BuyerDataRow['TotalPo']." Pos -". $BuyerDataRow['ConsigneeName'] ; ?>
															<small class="text-muted">08:00 am</small></h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo "Total Ordered - " . round($BuyerDataRow['Kgs']) ." Kgs" ?> 
															Last Purchase Order Date on  
															<?php echo date("d-M", strtotime($BuyerDataRow['LastPoDate'])); ?></p>
                                                        </div>
                                                    </div>
                                                </li>
											  <?php } ?>
                                              
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          

							<div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        

                                        <h4 class="card-title mb-4">Delayed Shipments - <span 
										style="color: red;"><?php echo 
										$TotalDelayedShipments ?></span></h4>

                                        <div data-simplebar style="max-height: 330px;">
                                            <ul class="list-unstyled activity-wid">
                                               
											  <?php while($DSRow=$DelayedShimpentsExec->fetch_array()){ ?>
											   <li class="activity-list">
                                                    <div class="activity-icon avatar-xs">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                            <i class="ri-edit-2-fill"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div>
                                                            <h5 class="font-size-13"><?php echo $DSRow['PoNo'] ."-Ship By Date ". date("d-M-Y", strtotime($DSRow['ShipByDate'])); ?>
															</h5>
                                                        </div>
                                                        
                                                        <div>
                                                            <p class="text-muted mb-0"><?php echo "Shipment Date ". date("d-M-Y", strtotime($DSRow['ShippedOn'])).' Delayed By '
															.$DSRow['DelayedDays'] ?> 
															Days</p>
                                                        </div>
                                                    </div>
                                                </li>
											  <?php } ?>
                                              
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>-->
                        <!-- end row -->


                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                <?php
				include ($IncludesPath."footer.php");
				
				// echo file_get_contents($BaseDist."partials/footer.php"); ?>
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->
<?php
        
	
		include ($IncludesPath."vendor-scripts.php");
		include ($IncludesPath."NewScripts.php");
		
		?>

	<script>
$(document).ready( function () {
    // $('#example1').DataTable();
    var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
  sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : false 
        /*"columnDefs": [
            { "width": "20%", "targets": 0 }
          ]*/
    } );
} );
</script>	
<script>


 $(document).on('click','#PoDetails',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	  var PoID =$(this).data('id');
	  
	 $('#Update').val(PoID);
	 
	 showPoPopUp(PoNo,PoID);
	  
	

			
    	});
		
function showPoPopUp(PoNo,PoID){
	
			 $.ajax({
		   url:'Ajax/AjaxPopUpPoDetails.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			//  $("#Update").hide();

			 $('.modal-title').text('PO Status Details - '+PoNo);
			$("#PopUpTable").html(data);

			$('#myModal').modal('show');
					}
			});

}	



$(document).on('click','#list',function(e){
	
	
	
     var SelectedDate =$('#Year').val(); 
	// alert(SelectedDate);
	 
	 var Url='<?php echo $CurrentUrl ?>?Year='+SelectedDate;
	// alert(Url);
	 
		window.open(Url,"_self");
	
		// alert(SelectedDate);

 });
 
 $(document).on('click','#chbkExp',function(e){
	var chbkExp = $("#chbkExp").is(':checked');
	var chbkShip = $("#chbkShip").is(':checked');
	var SelectedDate='<?php echo $PresentYear ?>';
/*
	 var Url='<?php echo $CurrentUrl ?>?Year='+SelectedDate+'&chbkExp='+chbkExp+'&chbkShip='+chbkShip;
		window.open(Url,"_self");
		*/
		
		updateGraph(chbkExp,chbkShip);
 }); 
 
 $(document).on('click','#chbkShip',function(e){
	var chbkExp = $("#chbkExp").is(':checked');
	var chbkShip = $("#chbkShip").is(':checked');
	var SelectedDate='<?php echo $PresentYear ?>';
/*
	 var Url='<?php echo $CurrentUrl ?>?Year='+SelectedDate+'&chbkExp='+chbkExp+'&chbkShip='+chbkShip;
		window.open(Url,"_self");
		*/
		
		updateGraph(chbkExp,chbkShip);
 });

 $(document).on('click','#Update',function(e){
	 
	 var PoID =$('#Update').data('id');
	 var PoID =$('#Update').val();
	
	var Url='CreateShipmentStatus.php?PoID='+ PoID;
		window.open(Url,"_self");
 });
 
 $(document).on('click','#BuyerDetails',function(e){
	 
	 var ConID=$('#ConID').val();
	
	
	
		 $.ajax({
		   url:'Ajax/AjaxBuyerDetails.php',
		  type:'POST',
		  data:{'ConID':ConID },
		  success:function(data){
			 
			 //$('.modal-title').text('Do You Want To Shipped This Po '+INT_LOT_NUM+' ?');
			$("#PopUpTable").html(data);
	/*	$('#PopUpTable').DataTable({
		"bPaginate": false,
		autoWidth: true,
        scrollY: 600,
        scrollX: true,
        scroller: true
    });*/
			$('#myModal').modal('show');
			
		
	
			//$('#myModal').modal('show');
					}
			});
			
    	});	



</script>
<script>

var ArySeries=<?php echo json_encode($Series); ?>;
var AryLables=<?php echo json_encode($Lables,true); ?>;
var AryColors=<?php echo json_encode($Colors,true); ?>;

var AryPortSeries=<?php echo json_encode($PortSeries); ?>;
var AryPortLables=<?php echo json_encode($PortLables,true); ?>;
var AryPortColors=<?php echo json_encode($PortColors,true); ?>;





options={series : ArySeries,chart:{height:230,type:"donut"},
labels : AryLables,
plotOptions:{pie:{donut:{size:"75%"}}},
dataLabels:{enabled:!1},legend:{show:!1},
colors : AryColors};

Portoptions={series : AryPortSeries,chart:{height:230,type:"donut"},
labels : AryPortLables,
plotOptions:{pie:{donut:{size:"75%"}}},
dataLabels:{enabled:!1},legend:{show:!1},
colors : AryPortColors};

(chart=new ApexCharts(document.querySelector("#donut-chart"),options)).render();
(chart=new ApexCharts(document.querySelector("#donut-chart-port"),Portoptions)).render();

/*Donut Chart End */




var AryShipMents=<?php echo json_encode($Shipments,true); ?>;
var AryOrders=<?php echo json_encode($Orders,true); ?>;
var AryXlables=<?php echo json_encode($MonthName,true); ?>;





var AryExportKgs=<?php echo json_encode($ExportKgs,true); ?>;
var AryShippedKgs=<?php echo json_encode($ShippedKgs,true); ?>;
var AryShipmentXlables=<?php echo json_encode($Grade,true); ?>;

var AryPresent=<?php echo json_encode($Present,true); ?>;
var AryAbsent=<?php echo json_encode($Absent,true); ?>;
var AryOnTime=<?php echo json_encode($OnTimeData,true); ?>;
var AryTotal=<?php echo json_encode($Total,true); ?>;
var AryDate=<?php echo json_encode($Date,true); ?>;


var SalesOptions={series:
[

//{name:"Total Kgs X 1000",type:"line",data:AryKgs},
{name:"Orders",type:"column",data:AryOrders},
{name:"Shipments",type:"column",data:AryShipMents}

], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1}},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
//colors:["#eeb902","#666","#ff0000"],
colors:["#666","#ff0000"],
labels: AryXlables};

var shipmentOptions={
	series:
[
	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
], 
stroke:{width:[2,2,2,2],curve:"smooth"},
chart:{height:280,type:"line",toolbar:{show:!1}},

plotOptions:{bar:{horizontal:!1,columnWidth:"20%"}},
dataLabels:{enabled:!1},legend:{show:!1},
//colors:["#5664d2","#eeb902","#ff0000"],
colors:["#ff0000"],
labels: AryShipmentXlables

};




chart=new ApexCharts(document.querySelector("#line-column-chart"),SalesOptions);
chart.render();

chartShipment=new ApexCharts(document.querySelector("#shipment-chart"),shipmentOptions);
chartShipment.render();

//updateGraph();

function updateGraph(chbkExp,chbkShip){
	
	
var AryShipmentXlables=<?php echo json_encode($Grade,true); ?>;

if(chbkExp==true && chbkShip ==true){	
var Series=[
{name:"Export in Kgs",type:"column",data:AryExportKgs} ,	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
];	
var Colors={colors:["#5664d2","#ff0000"]};
}else if(chbkExp){	
var Series=[{name:"Export in Kgs",type:"column",data:AryExportKgs}];
var Colors={colors:["#5664d2"]};
}else if(chbkShip){
var Series=[{name:"Shipment Kgs",type:"column",data:AryShippedKgs}];
var Colors={colors:["#ff0000"]};
}else{	
var Series=[
{name:"Export in Kgs",type:"column",data:AryExportKgs} ,	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
];	
var Colors={colors:["#5664d2","#ff0000"]};	
}	
	
chartShipment.updateSeries(Series);	
chartShipment.updateOptions(Colors);	

/*chartShipment.updateSeries(
[	
{name:"Shipment Kgs",type:"column",data:AryShippedKgs}
]
)	
*/

}



</script>

    </body>
</html>
