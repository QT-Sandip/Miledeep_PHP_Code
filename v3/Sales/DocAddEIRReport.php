

<?php
//ini_set( 'display_errors', 1 );
$PoID=$_GET['PIID'];
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."SalesInculdes/SalesConstants.php");
include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Includes/Globals.php");



//$PoRefNumber=$_GET['id'];
$PoRefNumber=$_GET['PIID'];

/**uma 22/08/2022**/

$CustomInvoiceSql="SELECT *,SPD.ExporterID as ExpId FROM $ScrmCustomInvoices as SCV 
 left JOIN $TblScrmPIData as SPD on SPD.PoID=SCV.PiID WHERE PiID='$PoRefNumber'";
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
$ExpID=$CustomInvoiceRow['ExpId'];

$ExporterData=getExporterDetails($ExpID);

$CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate']));   
if($CustomInvoiceRow['ExpId']==5){		
if($CurrentMonth<=3){
	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	
	
} 
else{ 

 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
 
}
		}
		else{		
if($CurrentMonth<=3){
	
 	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	

	
} 
else{ 
 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
  
}
		}
		
$file_name=$ExporterData['InvShortCode'].'-CI-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear;

$PageTitleFolder="Payments";
$PageTitle="Export Information Report (".$file_name.")";
/**uma 22/08/2022**/





$SqlLogistics="SELECT * FROM `$ScrmLogisticsMaster`";
$StmtLogistics=$iconn->query($SqlLogistics);

$SqlLiner="SELECT * FROM `$ScrmLinerMaster` WHERE Status=1";
$StmtLiner=$iconn->query($SqlLiner);

$SqlExportUsers="SELECT * FROM `AdminUserDetails`  WHERE UserType='ExportUsers'";
$StmtExportUsers=$iconn->query($SqlExportUsers);

$SqlBankMaster="SELECT * FROM `ScrmBankMaster`";
$StmtBankMaster=$iconn->query($SqlBankMaster);


$SqlDocumentsList="SELECT * FROM `ScrmDocumentsList` where DocStatus=1  Order By SortOrder asc";
$StmtDocumentsList=$iconn->query($SqlDocumentsList);

$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='SALES' order by SortOrder asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

	
	$SqlBuyer="SELECT * FROM $TblScrmConsigneeDetails";
	$StmtBuyer=$iconn->query($SqlBuyer);
	$StmtBuyer1=$iconn->query($SqlBuyer);

    $SqlCountryList="SELECT * FROM $TblScrmCountriesList";
	$StmtCountryList=$iconn->query($SqlCountryList); 

$SqlAdminUsers="SELECT * FROM $TblAdminUserDetails WHERE DepartmentID=9 and PoUserStatus='1'";
	$StmtAdminUsers=$iconn->query($SqlAdminUsers);
	

$SqlEirAgent="SELECT * FROM `$ScrmEirAgentNDiscountDetails` WHERE PoRefNumber='$PoRefNumber'";
	$StmtEirAgent=$iconn->query($SqlEirAgent);
	$RowEirAgent=$StmtEirAgent->fetch_array();
	
	
$SqlEIRBank="SELECT * FROM `$ScrmEIRBankNFieghtDetaails` WHERE PoRefNumber='$PoRefNumber'";
	$StmtEIRBank=$iconn->query($SqlEIRBank);
		$RowEIRBank=$StmtEIRBank->fetch_array();

		

$SqlEirFda="SELECT * FROM `$ScrmEirFdaNOtherDetails` WHERE PoRefNumber='$PoRefNumber'";
	$StmtEirFda=$iconn->query($SqlEirFda);
			$RowEirFda=$StmtEirFda->fetch_array();

		$AccAval=true;
			$CYear=date("Y");
			$AYear=$CYear+1;
			while($AccAval){
			$PoRefId=generateAccountId();
			//$PINo="SME/".$CYear.'-'.$AYear.'/'.$PoRefId;
			$sql="SELECT count(*) as Total FROM `$TblScrmPoData` where PoRefId='$PoRefId'";
			$sqlca=$iconn->query($sql);
			$rlt = mysqli_fetch_array($sqlca,MYSQLI_ASSOC);
			if($rlt['Total'] == 0 ){
				$AccAval=false;
			}			
			}
	
	 function generateAccountId(){
				$generator = "1357902468"; 
                $result = ""; 
                for ($i = 1; $i <= 5; $i++) { 
                    $result .= substr($generator, (rand()%(strlen($generator))), 1); 
                }  
				
				return $result;
		}
		
extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){
	
	$SqlCountryType="SELECT CountryType FROM `ScrmCountriesList` where id='$DestinationCountry' ";
	$StmtCountryType=$iconn->query($SqlCountryType);
	$RowCountryType=$StmtCountryType->fetch_array();
	
	$TypeCountry=$RowCountryType['CountryType'];
	
	 $StartYear=date('Y',strtotime($InvDate));
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01'; 
			  $FinEndDate=$EndYear.'-03-31';
	
	$YEAR=date("Y");
	 $AgentCount=mysqli_num_rows($StmtEirAgent);
	 $BankCount=mysqli_num_rows($StmtEIRBank);
	 $FdaCount=mysqli_num_rows($StmtEirFda);
			

if($AgentCount==0){
$SqlAgentDtlsInsert="INSERT INTO `$ScrmEirAgentNDiscountDetails`
(`PoRefNumber`,`LocalAgentName`, `LocalAgentComisn`, `OverseasAgent`, 
`OverseasAgentComisn`, `ExchangeRateperUnit`, `ExchangeRate`, `BillRefNo`,
 `BillDiscountDate`, `BillRealizationDate`, `RealizationEarlyDate`, 
 `RealizationLateDate`,`EnteredBy`) 
 VALUES ('$PoRefNumber','$LocalAgentName','$LocalAgentComisn','$OverseasAgent',
 '$OverseasAgentComisn', '$ExchangeRateperUnit',
'$ExchangeRate','$BillRefNo','$BillDiscountDate','$BillRealizationDate',
'$RealizationEarlyDate','$RealizationLateDate','$EnteredBy')"; }
else{
	$oldAgentData = legacyAuditFetchRowById('ScrmEirAgentNDiscountDetails', 'PoRefNumber', $PoRefNumber, 'Sales');
	 $SqlAgentDtlsInsert="UPDATE `ScrmEirAgentNDiscountDetails` SET 
	`LocalAgentName`='$LocalAgentName',`LocalAgentComisn`='$LocalAgentComisn',`OverseasAgent`='$OverseasAgent',
	`OverseasAgentComisn`='$OverseasAgentComisn',`ExchangeRateperUnit`='$ExchangeRateperUnit',`ExchangeRate`='$ExchangeRate',
	`BillRefNo`='$BillRefNo',`BillDiscountDate`='$BillDiscountDate',`BillRealizationDate`='$BillRealizationDate',
	`RealizationEarlyDate`='$RealizationEarlyDate',`RealizationLateDate`='$RealizationLateDate',`EnteredBy`='$EnteredBy' WHERE PoRefNumber='$PoRefNumber'";
}
if($BankCount==0){
 $SqlBankDtlsInsert="INSERT INTO `$ScrmEIRBankNFieghtDetaails`
(`PoRefNumber`,`EbrcReleasedUSD`, `EbrcNumber`, `EbrcDate`,`EbrcDate2`,`EbrcDate3`,`EbrcDate4`, `AdvanceRefNo`,
 `AdvanceDate`, `LcNumber`, `LcDate`, `LcReceiptFrom`, `AwbNumber`, `AwbDate`, 
 `RoadTransport`,  `OceanFreightINR`, `ThcNOthers`, `DueDate`, `FreightCostPerKg`,
 `Bank`,`SBValue`,`SBValueFOB`,`AddOnPoint`,`DocumentsList`,`BankDate`)
 VALUES ('$PoRefNumber','$EbrcReleasedUSD','$EbrcNumber','$EbrcDate','$EbrcDate2','$EbrcDate3','$EbrcDate4','$AdvanceRefNo', 
 '$AdvanceDate','$LcNumber','$LcDate','$LcReceiptFrom','$AwbNumber','$AwbDate',
'$RoadTransport','$OceanFreightINR','$ThcNOthers','$DueDate','$FreightCostPerKg',
'$Bank','$SBValue','$SBValueFOB','$AddOnPoint','$DocumentsList','$BankDate')";}
else{
	$oldBankData = legacyAuditFetchRowById('ScrmEIRBankNFieghtDetaails', 'PoRefNumber', $PoRefNumber, 'Sales');
	$SqlBankDtlsInsert="UPDATE `ScrmEIRBankNFieghtDetaails` SET `EbrcReleasedUSD`='$EbrcReleasedUSD',
	`EbrcNumber`='$EbrcNumber',`EbrcDate`='$EbrcDate',`EbrcDate2`='$EbrcDate2',`EbrcDate3`='$EbrcDate3',`EbrcDate4`='$EbrcDate4',
	`AdvanceRefNo`='$AdvanceRefNo',`AdvanceDate`='$AdvanceDate',`LcNumber`='$LcNumber',
	`LcDate`='$LcDate',`LcReceiptFrom`='$LcReceiptFrom',`AwbNumber`='$AwbNumber',`AwbDate`='$AwbDate',`RoadTransport`='$RoadTransport',
	`OceanFreightINR`='$OceanFreightINR',`ThcNOthers`='$ThcNOthers',`DueDate`='$DueDate',`FreightCostPerKg`='$FreightCostPerKg',
	`Bank`='$Bank',`SBValue`='$SBValue',`SBValueFOB`='$SBValueFOB',`AddOnPoint`='$AddOnPoint',
	`DocumentsList`='$DocumentsList',`BankDate`='$BankDate' WHERE PoRefNumber='$PoRefNumber'";
}
if($FdaCount==0){
$SqlEirDtlsInsert="INSERT INTO `$ScrmEirFdaNOtherDetails`
(`PoRefNumber`, `ContainerOutDate`, `FdaNEuHoldDate`, 
`FdaNEuReleaseDate`, `FdaEntryNumber`, `FdaEntryDate`, `AgentInv`, 
`ActualDutyPaidUSD`, `CvdPaidUSD`, `OthersHMFUSD`, `OthersMPFUSD`, 
`OtherCharges`, `LetExportDate`, `FobSbUsd`, `FobSbInr`, `DrawBackRecvd`,
 `RodtepRecvd`, `DrawBackRcvdAmount`, `DrawBackRcvdDate`, `MeisRcvdAmount`, 
 `MeisApplDate`, `QDateIssue`, `FobVaueINR`, `HCNumber`, `GPSNumber`, `HCDate`,
 `GPSDate`, `CoNumber`, `CoDate`, `InsuranceDtls`, `DueDate`, `UsBroker`, `BuyerAgent`, 
 `BillReceivedDate`, `BillAmount`, `BillRemarks`, `BillStatus`)
 VALUES('$PoRefNumber','$ContainerOutDate','$FdaNEuHoldDate',
 '$FdaNEuReleaseDate','$FdaEntryNumber','$FdaEntryDate','$AgentInv','$ActualDutyPaidUSD'
 ,'$CvdPaidUSD','$OthersHMFUSD','$OthersMPFUSD','$OtherCharges','$LetExportDate',
 '$FobSbUsd','$FobSbInr','$DrawBackRecvd','$RodtepRecvd','$DrawBackRcvdAmount',
 '$DrawBackRcvdDate','$MeisRcvdAmount','$MeisApplDate','$QDateIssue',
 '$FobVaueINR','$HCNumber','$GPSNumber','$HCDate','$GPSDate','$CoNumber',
'$CoDate','$InsuranceDtls','$DueDate','$UsBroker','$BuyerAgent','$BillReceivedDate',
'$BillAmount','$BillRemarks','$BillStatus')";}
else{
	$oldEirData = legacyAuditFetchRowById('ScrmEirFdaNOtherDetails', 'PoRefNumber', $PoRefNumber, 'Sales');
	$SqlEirDtlsInsert="UPDATE `ScrmEirFdaNOtherDetails` SET 
	`ContainerOutDate`='$ContainerOutDate',`FdaNEuHoldDate`='$FdaNEuHoldDate',`FdaNEuReleaseDate`='$FdaNEuReleaseDate',
	`FdaEntryNumber`='$FdaEntryNumber',`FdaEntryDate`='$FdaEntryDate',`AgentInv`='$AgentInv',`ActualDutyPaidUSD`='$ActualDutyPaidUSD',
	`CvdPaidUSD`='$CvdPaidUSD',`OthersHMFUSD`='$OthersHMFUSD',`OthersMPFUSD`='$OthersMPFUSD',`OtherCharges`='$OtherCharges',
	`LetExportDate`='$LetExportDate',`FobSbUsd`='$FobSbUsd',`FobSbInr`='$FobSbInr',`DrawBackRecvd`='$DrawBackRecvd',
	`RodtepRecvd`='$RodtepRecvd',`DrawBackRcvdAmount`='$DrawBackRcvdAmount',`DrawBackRcvdDate`='$DrawBackRcvdDate',
	`MeisRcvdAmount`='$MeisRcvdAmount',`MeisApplDate`='$MeisApplDate',`QDateIssue`='$QDateIssue',`FobVaueINR`='$FobVaueINR',
	`HCNumber`='$HCNumber',`GPSNumber`='$GPSNumber',`HCDate`='$HCDate',`GPSDate`='$GPSDate',
	`CoNumber`='$CoNumber',`CoDate`='$CoDate',`InsuranceDtls`='$InsuranceDtls',
	`DueDate`='$DueDate',`UsBroker`='$UsBroker',`BuyerAgent`='$BuyerAgent',
	`BillReceivedDate`='$BillReceivedDate',`BillAmount`='$BillAmount',
	`BillRemarks`='$BillRemarks',`BillStatus`='$BillStatus' 
	WHERE `PoRefNumber`='$PoRefNumber'";
	
	
}

	//ECHO $SqlBankDtlsInsert; exit;

	$StmtAgentDtlsInsert=$iconn->query($SqlAgentDtlsInsert);
	if($AgentCount==0){
		if($StmtAgentDtlsInsert) legacyAuditLogInsertById('ScrmEirAgentNDiscountDetails', 'PoRefNumber', $PoRefNumber, $_POST, array(), 'Sales');
	} else {
		if($StmtAgentDtlsInsert) legacyAuditLogUpdateById('ScrmEirAgentNDiscountDetails', 'PoRefNumber', $PoRefNumber, $oldAgentData, $_POST, array(), 'Sales');
	}

	$StmtBankDtlsInsert=$iconn->query($SqlBankDtlsInsert);
	if($BankCount==0){
		if($StmtBankDtlsInsert) legacyAuditLogInsertById('ScrmEIRBankNFieghtDetaails', 'PoRefNumber', $PoRefNumber, $_POST, array(), 'Sales');
	} else {
		if($StmtBankDtlsInsert) legacyAuditLogUpdateById('ScrmEIRBankNFieghtDetaails', 'PoRefNumber', $PoRefNumber, $oldBankData, $_POST, array(), 'Sales');
	}

	$StmtEirDtlsInsert=$iconn->query($SqlEirDtlsInsert);
	if($FdaCount==0){
		if($StmtEirDtlsInsert) legacyAuditLogInsertById('ScrmEirFdaNOtherDetails', 'PoRefNumber', $PoRefNumber, $_POST, array(), 'Sales');
	} else {
		if($StmtEirDtlsInsert) legacyAuditLogUpdateById('ScrmEirFdaNOtherDetails', 'PoRefNumber', $PoRefNumber, $oldEirData, $_POST, array(), 'Sales');
	}

	if($StmtAgentDtlsInsert||$StmtBankDtlsInsert||$StmtEirDtlsInsert){
		
	

		//header('Location: DocAddEIRReport.php?id='.$PoRefNumber);
		header('Location:ShippedPoList.php');

	
	}

}
 ?>


<!DOCTYPE html>
<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

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
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 
<?php } ?>

   <div class="modal fade" id="myModal1" role="dialog"> 
			   
			   <div class="modal-dialog">        <!-- Modal content-->        
			   <div class="modal-content">            
			   <div class="modal-header">               
			   <button type="button" class="close" data-dismiss="modal">&times;</button>   
			   <h4 class="modal-title modheading"></h4>            </div>   
			   <div class="modal-body checkdiv1">		

<?php 

			  $Name='';
			  ?>			 

			  <?php while($RowDocumentsList=$StmtDocumentsList->fetch_array()){
?>
 <table border="1" cellspacing="0" >
 
 <tr> 
  
		<th >		
		
		<div class = "checkbox">
               <label>
			   
			    
				<input 
				type = "checkbox" id="<?php echo $RowDocumentsList['DocName'];?>" 
				value = "<?php echo $RowDocumentsList['DocName'];?>">
				<?php echo $RowDocumentsList['DocName'];?>
			   
			   </label>
			   </div>
			   
		</th >   
		<th >  
		
                  
                  <input   type="number"  name='Original' 
				  id="Original" placeholder="Enter Original" value="<?php echo $RowEirAgent['Original'];?>">
                
		</th >   
		<th> 
                
                  <input   type="number" name='Copy' 
				  id="Copy" placeholder="Enter Copy" value="<?php echo $RowEirAgent['Copy'];?>">
                
		</th >   
			   
				
			   </tr> 
		</table>	   
			  <?php } ?>
			   
			   
			   </div>            
			   <div class="modal-footer">                
			   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>            
			   </div>        
			   </div>    
			   </div>
			   </div>

	

                        <?php include ($IncludesPath."page-title.php");?>
<?php include "PopUpTable.php"; ?>
	
          
	
<!--<div class="row">
                            <div class="col-12">
                                <div class="card">-->
								
				
		 <h3 align="LEFT"><B>   Agent Details</B></h3>
		          <form role="form" method="post" enctype="multipart/form-data">
		
        <div class="card">
		
        <!--  <form role="form" method="post" enctype="multipart/form-data">-->

              <div class="card-body">
			  
			 
<div class="row">
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Local Agent Name</label>
                  <input   type="text" class="form-control" name='LocalAgentName' 
				  id="LocalAgentName" placeholder="Enter Local Agent Name" value="<?php echo $RowEirAgent['LocalAgentName'];?>">
                </div>
				
				
							  
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Local Agent Comission</label>
                  <input   type="text" class="form-control" name='LocalAgentComisn' 
				  id="LocalAgentComisn" placeholder="Enter Local Agent Comission" value="<?php echo $RowEirAgent['LocalAgentComisn'];?>">
                </div>
				
				
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Overseas Agent Name</label>
                  <input   type="text" class="form-control" name='OverseasAgent' 
				  id="OverseasAgent" placeholder="Overseas Agent Name" value="<?php echo $RowEirAgent['OverseasAgent'];?>">
                </div>	
				
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Overseas Agent Comission</label>
                  <input   type="text" class="form-control" name='OverseasAgentComisn' 
				  id="OverseasAgentComisn" placeholder="Overseas Agent Comission" value="<?php echo $RowEirAgent['OverseasAgentComisn'];?>">
                </div> 
				
			
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Entered By</label>
                 
                <select class="form-control select2" name="EnteredBy" id="EnteredBy">
									  <option   value="">Select</option>

					 <?php while ($RowExportUsers=$StmtExportUsers->fetch_array()){
						$Name= $RowExportUsers['UserName'];
						$VALUE= $RowExportUsers['UserID'];
						 ?>
			 
					<option value="<?php echo $VALUE ?>"<?php if( $VALUE==$RowEirAgent['EnteredBy'])
					  echo 'selected="selected"'; ?>><?php echo $Name;?></option>
					<?php  }?>
				<select>
				
				</div>
				
			</div>
             
           
			
			</div>
			</div>
				
				
			<h3 align="LEFT"><B> Bill Release Details</B></h3>
				
        <div class="card">
		
         

              <div class="card-body">
			  	

			  <div class="row">
			  			
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Exchange Rate(Advance)</label>
                  <input   type="text" class="form-control" name='ExchangeRateperUnit' 
				  id="ExchangeRateperUnit" placeholder="Enter Exchange Rate(Advance)" value="<?php echo $RowEirAgent['ExchangeRateperUnit'];?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Exchange Rate(Bank)</label>
                  <input   type="text" class="form-control" name='ExchangeRate' 
				  id="ExchangeRate" placeholder="Enter Exchange Rate(Bank)" value="<?php echo $RowEirAgent['ExchangeRate'];?>">
                </div>
				
				
			  
			  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Bill Reference No</label>
                  <input  type="text" class="form-control" name='BillRefNo' 
				  id="BillRefNo" placeholder="Enter Bill Reference Number" value="<?php echo $RowEirAgent['BillRefNo'];?>">
                </div> 
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Bill Discount Date</label>
                  <input  type="date" onkeydown="return false" class="form-control" name='BillDiscountDate' value="<?php echo $RowEirAgent['BillDiscountDate'];?>" 
				  id="BillDiscountDate" placeholder="Enter Bill Discount Date">
                </div> 
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Bill Date of Realization</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEirAgent['BillRealizationDate'];?>" class="form-control" name='BillRealizationDate' 
				  id="BillRealizationDate" placeholder="Enter Bill Date of Realization">
                </div> 
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Realization Early Days</label>
                  <input  type="text" onkeydown="return false" class="form-control" name='RealizationEarlyDate' 
				  id="RealizationEarlyDate" placeholder="Enter Realization Early Days" value="<?php echo $RowEirAgent['RealizationEarlyDate'];?>">
                </div> 
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Realization Late Days</label>
                  <input  type="text" class="form-control" name='RealizationLateDate' 
				  id="RealizationLateDate" placeholder="Enter Realization Late Days" value="<?php echo $RowEirAgent['RealizationLateDate'];?>">
                </div> 
				
				</div>            
				</div>            
             
           			

            </div>
				
			<h3 align="LEFT"><B> Local Bank Details</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  		<div class="row">	


					
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> E-BRC Realized USD</label>
                  <input  type="text" class="form-control" name='EbrcReleasedUSD' 
				  id="EbrcReleasedUSD" placeholder="Enter E-BRC Realized USD" value="<?php echo $RowEIRBank['EbrcReleasedUSD'];?>">
                </div>

				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> E-BRC Number</label>
                  <input  type="text" class="form-control" name='EbrcNumber' 
				  id="EbrcNumber" placeholder="Enter E-BRC Number" value="<?php echo $RowEIRBank['EbrcNumber'];?>">
                </div>

				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> E-BRC Date1</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEIRBank['EbrcDate'];?>" 
				  class="form-control" name='EbrcDate' id="EbrcDate" 
				  placeholder="Enter E-BRC Date">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> E-BRC Date2</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEIRBank['EbrcDate2'];?>" 
				  class="form-control" name='EbrcDate2' id="EbrcDate2" 
				  placeholder="Enter E-BRC Date2">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> E-BRC Date3</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEIRBank['EbrcDate3'];?>" 
				  class="form-control" name='EbrcDate3' id="EbrcDate3" 
				  placeholder="Enter E-BRC Date3">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> E-BRC Date4</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEIRBank['EbrcDate4'];?>" 
				  class="form-control" name='EbrcDate4' id="EbrcDate4" 
				  placeholder="Enter E-BRC Date4">
                </div>

				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> Advance Ref Number</label>
                  <input  type="text" class="form-control" name='AdvanceRefNo' 
				  id="AdvanceRefNo" placeholder="Enter Advance Ref Number" value="<?php echo $RowEIRBank['AdvanceRefNo'];?>">
                </div>

				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Advance Date</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEIRBank['AdvanceDate'];?>" 
				  class="form-control" name='AdvanceDate' id="AdvanceDate" 
				  placeholder="Enter E-BRC Date">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Due date as per Bank</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEIRBank['DueDate'];?>" 
				  class="form-control" name='DueDate' id="DueDate" 
				  placeholder="Enter Due date as per Bank">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> LC Number</label>
                  <input  type="text" class="form-control" name='LcNumber' 
				  id="LcNumber" placeholder="Enter LC Number" value="<?php echo $RowEIRBank['LcNumber'];?>">
                </div>

				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">LC Date</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEIRBank['LcDate'];?>" 
				  class="form-control" name='LcDate' id="LcDate" 
				  placeholder="Enter E-BRC Date" >
                </div>
							 
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> LC Receipt From</label>
                  <input  type="text" class="form-control" name='LcReceiptFrom' 
				  id="LcReceiptFrom" placeholder="Enter LC Receipt From" value="<?php echo $RowEIRBank['LcReceiptFrom'];?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> AWB Number</label>
                  <input  type="text" class="form-control" name='AwbNumber' 
				  id="AwbNumber" placeholder="Enter AWB Number" value="<?php echo $RowEIRBank['AwbNumber'];?>">
                </div>

				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">AWB Date</label>
                  <input  type="date" onkeydown="return false" value="<?PHP echo $RowEIRBank['AwbDate']; ?>" 
				  class="form-control" name='AwbDate' id="AwbDate" 
				  placeholder="Enter AWB Date">
                </div>
								
				</div>	
				</div>	
             
         
            </div>
			
	

				<h3 align="LEFT"><B> Bank Letters</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  <div class="row">
			  
			  
                 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank(AD Code)</label>
                 
                <select class="form-control select2" name="Bank" id="Bank">
									  <option   value="">Select</option>

					 <?php while ($RowBankMaster=$StmtBankMaster->fetch_array()){
						$BankName= $RowBankMaster['BankName'];
						$BankID= $RowBankMaster['BankID'];
						 ?>
			 
					<option value="<?php echo $BankID ?>"<?php if( $BankID==$RowEIRBank['Bank'])
					  echo 'selected="selected"'; ?>><?php echo $BankName.' - '.$RowBankMaster['ADCode'];?></option>
					<?php  }?>
				<select>
				
				</div>
				
			  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Bank Letter Date</label>
                  <input  type="date" onkeydown="return false" value="<?PHP ECHO $RowEIRBank['BankDate']; ?>" 
				  class="form-control" name='BankDate' 
				  id="BankDate" placeholder="Enter Bank Letter Date">
              </div>
			  
			  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> SB Value(US$)</label>
                  <input  type="text" class="form-control" name='SBValue' 
				  id="SBValue" placeholder="Enter SB Value in US$" value="<?php echo $RowEIRBank['SBValue'];?>">
                </div>
				<!--<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> SB Value FOB</label>
                  <input  type="text" class="form-control" name='SBValueFOB' 
				  id="SBValueFOB" placeholder="Enter SB Value FOB" value="<?php echo $RowEIRBank['SBValueFOB'];?>">
                </div>-->
				
				
				
				<!-- <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Documents to be Submitted</label>
                   <input   autocomplete='off' type="text" class="form-control"  name="DocumentsList" id="DocumentsList" 
				   placeholder="Click To Select" value="<?php echo $RowEIRBank['DocumentsList']?>">
                <input type="hidden" class="form-control"  name="type" id="type"  >
                </div>-->
				
				
			<button  class="btn btn-warning waves-effect waves-light"
					 data-toggle="popover" data-trigger="focus" 
					 
					 data-id="<?php echo $row['PoID'];?> " id="BtnPopUpBulk" 
							 value=" <?php echo $row['PoID']?>"> Documents to be Submitted 
				 </button>
				
				<!--<div class="form-group col-sm-9">
                  <label for="exampleInputPassword1">Add On Point</label>
                  <input   type="text" class="form-control" name='AddOnPoint' 
				  id="AddOnPoint" placeholder="Enter 1st point after insert , Enter 2nd point" value="<?php echo $RowEIRBank['AddOnPoint'];?>">
                </div>-->
				
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Add On Point</label>
				  
				  <textarea name="AddOnPoint" class="form-control" rows="4" cols="50"
                    id="AddOnPoint"   
					placeholder="Enter 1st point after insert , Enter 2nd point" ><?php echo $RowEIRBank['AddOnPoint'] ?></textarea>
				 		
		
                </div>	
				
				
				<!--<div class="form-group col-sm-9">
                  <label for="exampleInputPassword1">Add On Point</label>
				  
				   <textarea name='text' rows=20 cols=100 name='AddOnPoint' 
				  id="AddOnPoint" value='<?php echo $RowEIRBank['AddOnPoint'];?>'></textarea
   <br />
                  <!--<input   type="text" class="form-control" name='AddOnPoint' 
				  id="AddOnPoint" placeholder="Enter 1st point after insert , Enter 2nd point" value="<?php echo $RowEIRBank['AddOnPoint'];?>">-->
                </div>
				
			  
				
            </div>
            </div>
            </div>
			
			
			<h3 align="LEFT"><B> FDA/EU/FOB Details</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  <div class="row">
			  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Container Gate Out Date</label>
                  <input  type="date" onkeydown="return false" value="<?PHP echo $RowEirFda['ContainerOutDate'];?>" class="form-control" name='ContainerOutDate' 
				  id="ContainerOutDate" placeholder="Enter Container Gate Out Date">
                </div>
				
			  
			  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">FDA/EU Hold Date</label>
                  <input  type="date" onkeydown="return false" value="<?PHP ECHO $RowEirFda['FdaNEuHoldDate']; ?>" class="form-control" name='FdaNEuHoldDate' 
				  id="FdaNEuHoldDate" placeholder="Enter FDA/EU Hold Date">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">FDA/EU Release/CFIA Date</label>
                  <input  type="date" onkeydown="return false"  class="form-control" name='FdaNEuReleaseDate' 
				  id="FdaNEuReleaseDate" placeholder="Enter FDA/EU Release/CFIA  Date" value="<?php echo $RowEirFda['FdaNEuReleaseDate'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">FDA Entry Number</label>
                  <input  type="text" class="form-control" name='FdaEntryNumber' 
				  id="FdaEntryNumber" placeholder="Enter FDA Entry Number" value="<?php echo $RowEirFda['FdaEntryNumber'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">FDA Entry Date</label>
                  <input  type="date"  onkeydown="return false" class="form-control" name='FdaEntryDate' 
				  id="FdaEntryDate" placeholder="Enter FDA Entry Date" value="<?php echo $RowEirFda['FdaEntryDate'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">FOB Value SB USD</label>
                  <input  type="text" class="form-control" name='FobSbUsd' 
				  id="FobSbUsd" placeholder="Enter FOB Value SB USD" value="<?php echo $RowEirFda['FobSbUsd'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">FOB Value SB INR</label>
                  <input  type="text" class="form-control" name='FobSbInr' 
				  id="FobSbInr" placeholder="Enter FOB Value SB INR"  value="<?php echo $RowEirFda['FobSbInr'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">FOB Value INR</label>
                  <input  type="text" class="form-control" name='FobVaueINR' 
				  id="FobVaueINR" placeholder="Enter FOB Value INR"  value="<?php echo $RowEirFda['FobVaueINR'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Agent Invoice</label>
                  <input  type="text" class="form-control" name='AgentInv' 
				  id="AgentInv" placeholder="Enter Agent Invoice" value="<?php echo $RowEirFda['AgentInv'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Actual A Paid USD</label>
                  <input  type="text" class="form-control" name='ActualDutyPaidUSD' 
				  id="ActualDutyPaidUSD" placeholder="Enter Actual A Paid USD" value="<?php echo $RowEirFda['ActualDutyPaidUSD'];  ?>">
                </div>
				
				</div>           
           
            </div>
            </div>

				<h3 align="LEFT"><B>FC Payment Details</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  <div class="row">
			  		<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">CVD Paid USD</label>
                  <input  type="text" class="form-control" name='CvdPaidUSD' 
				  id="CvdPaidUSD" placeholder="Enter CVD Paid USD" value="<?php echo $RowEirFda['CvdPaidUSD'];  ?>">
                </div>
								<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Others HMF USD</label>
                  <input  type="text" class="form-control" name='OthersHMFUSD' 
				  id="OthersHMFUSD" placeholder="Enter Others HMF USD" value="<?php echo $RowEirFda['OthersHMFUSD'];  ?>">
                </div>
								<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Others MPF USD</label>
                  <input  type="text" class="form-control" name='OthersMPFUSD' 
				  id="OthersMPFUSD" placeholder="Enter Others MPF USD" value="<?php echo $RowEirFda['OthersMPFUSD'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Others Charges(US$)</label>
                  <input  type="text" class="form-control" name='OtherCharges' 
				  id="OtherCharges" placeholder="Enter Charges (Ex:100,200,300)" value="<?php echo $RowEirFda['OtherCharges'];  ?>">
                </div>
				
			  	<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">LET Export Date</label>
                  <input  type="date"  onkeydown="return false" class="form-control" name='LetExportDate' 
				  id="LetExportDate" placeholder="Enter LET Export Date" value="<?php echo $RowEirFda['LetExportDate'];  ?>">
                </div>
				
				</div>           
				</div>           
            </div>

				<h3 align="LEFT"><B>Receipts Details</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  
			  <div class="row">
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Drawback Received 3%</label>
                  <input  type="text" class="form-control" name='DrawBackRecvd' 
				  id="DrawBackRecvd" placeholder="Enter Drawback Received 3%" value="<?php echo $RowEirFda['DrawBackRecvd'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">RODTEP Receivable 2.50%</label>
                  <input  type="text" class="form-control" name='RodtepRecvd' 
				  id="RodtepRecvd" placeholder="Enter RODTEP Receivable 2.50%" value="<?php echo $RowEirFda['RodtepRecvd'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Drawback/ST Received</label>
                  <input  type="text" class="form-control" name='DrawBackRcvdAmount' 
				  id="DrawBackRcvdAmount" placeholder="Enter Drawback Received 3%" value="<?php echo $RowEirFda['DrawBackRcvdAmount'];  ?>">
                </div>
								<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Drawback/ST Received Date</label>
                  <input  type="date" onkeydown="return false" class="form-control" name='DrawBackRcvdDate' 
				  id="DrawBackRcvdDate" placeholder="Enter Drawback Received 3%" value="<?php echo $RowEirFda['DrawBackRcvdDate'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">MEIS Received</label>
                  <input  type="text" class="form-control" name='MeisRcvdAmount' 
				  id="MeisRcvdAmount" placeholder="Enter Drawback Received 3%" value="<?php echo $RowEirFda['MeisRcvdAmount'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">MEIS APPL.Date</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEirFda['MeisApplDate'];  ?>"class="form-control" name='MeisApplDate' 
				  id="MeisApplDate" placeholder="Enter Drawback Received 3%">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Q Date Issue</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEirFda['QDateIssue'];  ?>"class="form-control" name='QDateIssue' 
				  id="QDateIssue" placeholder="Enter Q Date Issue">
                </div>
				</div>           
            </div>
            </div>
			
			<h3 align="LEFT"><B> Payment Received Details</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  <div class="row">
			  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> Bill Received Date</label>
                  <input  type="date" onkeydown="return false" class="form-control" name='BillReceivedDate' 
				  id="BillReceivedDate" placeholder="" value="<?php echo $RowEirFda['BillReceivedDate'];  ?>">
                </div>
				
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Bill Status</label>
                  <select class="form-control select2" name="BillStatus" id="BillStatus">
					<?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='BILL STATUS'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE'];
				?>
			 <option <?php if($RowEirFda['BillStatus']==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php  }}?>
					 
				<select>
				</div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> Bill Amount</label>
                  <input  type="text" class="form-control" name='BillAmount' 
				  id="BillAmount" placeholder="Enter Bill Amount" value="<?php echo $RowEirFda['BillAmount'];  ?>">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> Bill Remarks</label>
                  <input  type="text" class="form-control" name='BillRemarks' 
				  id="BillRemarks" placeholder="Enter Bill Remarks" value="<?php echo $RowEirFda['BillRemarks'];  ?>">
                </div>
				
			    </div>           
			</div>           
            </div>
			
			<h3 align="LEFT"><B> Others Details</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  <div class="row">
			  
			 
			  
			  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> Road Transport</label>
                  <input  type="text" class="form-control" name='RoadTransport' 
				  id="RoadTransport" placeholder="Enter Road Transport" value="<?php echo $RowEIRBank['RoadTransport'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> Vehicle Number</label>
                  <input  type="text" class="form-control" name='Vehicle' 
				  id="Vehicle" placeholder="Enter Vehicle Number" value="<?php echo $RowEIRBank['Vehicle'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"> Ocean Freight INR</label>
                  <input  type="text" class="form-control" name='OceanFreightINR' 
				  id="OceanFreightINR" placeholder="Enter Ocean Freight INR" value="<?php echo $RowEIRBank['OceanFreightINR'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Freight Cost(per kg)</label>
                  <input  type="text" class="form-control" name='FreightCostPerKg' 
				  id="FreightCostPerKg" placeholder="Enter Freight Cost Per Kg" value="<?php echo $RowEIRBank['FreightCostPerKg'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">THC & Others if extra </label>
                  <input  type="text" class="form-control" name='ThcNOthers' 
				  id="ThcNOthers" placeholder="Enter THC & Others if extra" value="<?php echo $RowEIRBank['ThcNOthers'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">HC Number</label>
                  <input  type="text" class="form-control" name='HCNumber' 
				  id="HCNumber" placeholder="Enter HC Number" value="<?php echo $RowEirFda['HCNumber'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">GPS Number </label>
                  <input  type="text" class="form-control" name='GPSNumber' 
				  id="GPSNumber" placeholder="Enter GPS Number" value="<?php echo $RowEirFda['GPSNumber'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">HC Date</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEirFda['HCDate'];  ?>"class="form-control" name='HCDate' 
				  id="HCDate" placeholder="Enter HC Date">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">GPS Date</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEirFda['GPSDate'];  ?>"class="form-control" name='GPSDate' 
				  id="GPSDate" placeholder="Enter GPS Date">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">CO Number </label>
                  <input  type="text" class="form-control" name='CoNumber' 
				  id="CoNumber" placeholder="Enter CO Number" value="<?php echo $RowEirFda['CoNumber'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">CO Date</label>
                  <input  type="date" onkeydown="return false" value="<?php echo $RowEirFda['CoDate'];  ?>"class="form-control" name='CoDate' 
				  id="CoDate" placeholder="Enter CO Date">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Insurance Details</label>
                  <input  type="text" class="form-control" name='InsuranceDtls' 
				  id="InsuranceDtls" placeholder="Enter Insurance Details" value="<?php echo $RowEirFda['InsuranceDtls'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Due Date</label>
                  <input  type="date" onkeydown="return false" class="form-control" name='DueDate' 
				  id="DueDate" placeholder="Enter Due Date" value="<?php echo $RowEirFda['DueDate'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">US Broker</label>
                  <input  type="text" class="form-control" name='UsBroker' 
				  id="UsBroker" placeholder="Enter US Broker" value="<?php echo $RowEirFda['UsBroker'];  ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Buyer Agent</label>
                  <input  type="text" class="form-control" name='BuyerAgent' 
				  id="BuyerAgent" placeholder="Enter Buyer Agent" value="<?php echo $RowEirFda['BuyerAgent'];  ?>">
                </div>
				
			</div>           
			</div>           
            <div>
			            	

			
			
				<div class="col-xs-12 card-footer"  align="right">
				
				<a target="" href="ShippedPoList.php"
       class="btn btn-danger  "> Close</a>
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div>
				
				</div>
            </form>
       <!-- end col -->
                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>

<Script>


$('document').ready(function(){   
 $('.checkdiv input').change(function() {		
 var arr=[];	  if (this.checked) {    arr.push(this.value);  } 
 else {   arr.splice(arr.indexOf(this.value), 1);  } 
 //$('#PRODUCT_NAME').val(arr + '');  });$('#PRODUCT_NAME').click(function() {	
 $('#myModal').modal('toggle'); });$('#DocumentsList').click(function() {  
 $('#myModal1').modal('toggle'); });    var ar=[];  var type=[];	
 $('.checkdiv1 input').change(function() {  if (this.checked) {
	 ar.push(this.value);	 type.push(this.id);
	 }  else {   ar.splice(ar.indexOf(this.value), 1); 
	 type.splice(type.indexOf(this.value), 1);   } 
	 $('#DocumentsList').val(ar + ''); $('#type').val(type + '');  });});



  
$(document).on('click','#BtnPopUpBulk',function(e){			
			e.preventDefault();
				//var YEAR=$('#PoRefNumber').val();
				var PoId='<?php echo $PoRefNumber; ?>';
//alert(PoId);
				

		$.ajax({
		   url:'Ajax/AjCertificatesList.php',
		  type:'POST',
		  data:{'PoId':PoId},
		  success:function(data){
			 
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});
			//}
    	});	
</script>
<script>

		
		  	
$(document).on('click','.StatusBulkUpdate',function(e){
	e.preventDefault();
	var PoId='<?php echo $PoRefNumber; ?>';
	var Count =$(this).val();
	
		
		
		
		    var OriginalIDs = [];
			var CopiesIDs = [];
			var AryData = [];
			
		for(var i=1; i<= Count; i++){
				var DocDataId='#DocId'+i;
				var OriginalID='#Original'+i;
				var CopyID='#Copies'+i;
				
				var DocId=$(DocDataId).val();
				var Copy=$(CopyID).val();
				var Original=$(OriginalID).val();
				//alert(DocId);
			
			if(Original !='' || Copy !='') {
				var Data= DocId+'&'+ Original+'&'+Copy;
				AryData.push(Data);
				OriginalIDs.push(Original);
				CopiesIDs.push(Copy);
			}
		
		}
		
				var jsonData=	JSON.stringify(AryData);
		/*if(Status==''){
			alert('Please Select Status');
			return;	
		}
		if(SelectedbulkInvId==''){
			alert('Please Select Invoices');
			return;	
		}*/
		
		
	
	$.ajax({
    type: 'POST',
	dataType: "json",
    url: 'Ajax/AjLCDocListSelect.php',
    data: {'Data':jsonData,'PoId' : PoId},
	
    
    success: function(response){
		response=JSON.stringify(response);
		var data=JSON.parse(response);
			if(data['Error']==null){			
			$('#myModal').modal('hide');
			alert(data['Msg']);
			//location.reload();
			}else{
			alert(data['Error']);	
			$('#myModal').modal('hide');
			}
	 }
  });

	
});

</script>
