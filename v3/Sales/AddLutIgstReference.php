<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$PageTitleFolder="Master";
$PageTitle="LUT / IGST Reference Number";

$LutIgstID=isset($_GET['id']) ? (int) $_GET['id'] : 0;
$PostedUserId=isset($_SESSION['UserID']) ? (int) $_SESSION['UserID'] : 0;
$PageUrl=ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));
$ValidationError='';
$FlashStatus=isset($_SESSION['Status']) ? $_SESSION['Status'] : '';
$_SESSION['Status']='';

function lutIgstSecureEquals($knownValue, $userValue){
    $knownValue=(string) $knownValue;
    $userValue=(string) $userValue;
    if(strlen($knownValue)!==strlen($userValue)){
        return false;
    }
    $result=0;
    $length=strlen($knownValue);
    for($index=0; $index<$length; $index++){
        $result|=ord($knownValue[$index]) ^ ord($userValue[$index]);
    }
    return $result===0;
}

if(empty($_SESSION['lut_igst_csrf'])){
    if(function_exists('openssl_random_pseudo_bytes')){
        $_SESSION['lut_igst_csrf']=bin2hex(openssl_random_pseudo_bytes(32));
    }
    else{
        $_SESSION['lut_igst_csrf']=sha1(uniqid((string) mt_rand(), true));
    }
}

$SqlExporter="SELECT * FROM `AdminExporterDetails` WHERE ExpType=1 ORDER BY ExpName";
$StmtExporter=$iconn->query($SqlExporter);
if(!$StmtExporter){
    $ValidationError='Unable to load exporter details.';
    error_log('AddLutIgstReference exporter query failed: '.$iconn->error);
}

$RowLutIgst=array();
$Count=0;
if($LutIgstID>0){
    $SqlLutIgst="SELECT * FROM `ScrmExporterLutIgstDetails` WHERE ID=".$LutIgstID;
    $StmtLutIgst=$iconn->query($SqlLutIgst);
    if($StmtLutIgst && $StmtLutIgst->num_rows>0){
        $RowLutIgst=$StmtLutIgst->fetch_assoc();
        $Count=1;
    }
    elseif(!$StmtLutIgst){
        $ValidationError='Unable to load the LUT / IGST reference.';
        error_log('AddLutIgstReference record query failed: '.$iconn->error);
    }
    else{
        $ValidationError='The requested LUT / IGST reference was not found.';
    }
}

$ExpID=isset($RowLutIgst['ExporterID']) ? (int) $RowLutIgst['ExporterID'] : 0;
$Type=isset($RowLutIgst['Type']) ? $RowLutIgst['Type'] : '';
$ReferenceNumber=isset($RowLutIgst['ReferenceNumber']) ? $RowLutIgst['ReferenceNumber'] : '';
$FinancialYear=isset($RowLutIgst['FinancialYear']) ? $RowLutIgst['FinancialYear'] : '';
$FromDate=isset($RowLutIgst['FromDate']) ? $RowLutIgst['FromDate'] : '';
$Remarks=isset($RowLutIgst['Remarks']) ? $RowLutIgst['Remarks'] : '';
$RecordStatus=isset($RowLutIgst['Status']) ? (int) $RowLutIgst['Status'] : 1;

if(isset($_POST['submit'])){
    $ExpID=isset($_POST['ExpID']) ? (int) $_POST['ExpID'] : 0;
    $Type=isset($_POST['Type']) ? trim($_POST['Type']) : '';
    $ReferenceNumber=trim(isset($_POST['ReferenceNumber']) ? $_POST['ReferenceNumber'] : '');
    $FinancialYear=trim(isset($_POST['FinancialYear']) ? $_POST['FinancialYear'] : '');
    $FromDate=trim(isset($_POST['FromDate']) ? $_POST['FromDate'] : '');
    $Remarks=trim(isset($_POST['Remarks']) ? $_POST['Remarks'] : '');
    $RecordStatus=isset($_POST['Status']) && $_POST['Status']==='0' ? 0 : 1;
    $PostedCsrf=isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if(!lutIgstSecureEquals($_SESSION['lut_igst_csrf'], $PostedCsrf)){
        $ValidationError='Your form session expired. Refresh the page and try again.';
    }
    elseif($ExpID<=0){
        $ValidationError='Select an exporter.';
    }
    elseif(!in_array($Type, array('LUT','IGST'), true)){
        $ValidationError='Select a valid type (LUT or IGST).';
    }
    elseif($ReferenceNumber===''){
        $ValidationError='Enter the reference number.';
    }
    elseif(!preg_match('/^([0-9]{4})-([0-9]{2})$/', $FinancialYear, $FinancialYearParts)){
        $ValidationError='Financial year must look like 2026-27.';
    }
    elseif(((int) $FinancialYearParts[2])!==(((int) $FinancialYearParts[1]+1)%100)){
        $ValidationError='Enter consecutive years, for example 2026-27.';
    }
    else{
        $DateParts=explode('-', $FromDate);
        if(count($DateParts)!==3 || !checkdate((int) $DateParts[1], (int) $DateParts[2], (int) $DateParts[0])){
            $ValidationError='Select a valid effective (from) date.';
        }
    }

    if($ValidationError===''){
        $SqlDuplicate="SELECT `ID` FROM `ScrmExporterLutIgstDetails`
            WHERE `ExporterID`=? AND `Type`=? AND `ReferenceNumber`=?
              AND `FinancialYear`=? AND `FromDate`=? AND `ID`<>?
            LIMIT 1";
        $StmtDuplicate=$iconn->prepare($SqlDuplicate);
        if($StmtDuplicate){
            $StmtDuplicate->bind_param('issssi', $ExpID, $Type, $ReferenceNumber, $FinancialYear, $FromDate, $LutIgstID);
            if($StmtDuplicate->execute()){
                $StmtDuplicate->store_result();
                if($StmtDuplicate->num_rows>0){
                    $ValidationError='This LUT / IGST reference already exists for the selected exporter, financial year and effective date.';
                }
            }
            else{
                $ValidationError='Unable to verify duplicate references. Please try again.';
                error_log('AddLutIgstReference duplicate check failed: '.$StmtDuplicate->error);
            }
            $StmtDuplicate->close();
        }
        else{
            $ValidationError='Unable to verify duplicate references. Please try again.';
            error_log('AddLutIgstReference duplicate prepare failed: '.$iconn->error);
        }
    }

    if($ValidationError===''){
        $Saved=false;
        if($Count===0){
            $SqlSave="INSERT INTO `ScrmExporterLutIgstDetails`
                (`ExporterID`, `Type`, `ReferenceNumber`, `FinancialYear`, `FromDate`, `Remarks`, `Status`, `PostedBy`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $StmtSave=$iconn->prepare($SqlSave);
            if($StmtSave){
                $StmtSave->bind_param('isssssii', $ExpID, $Type, $ReferenceNumber, $FinancialYear, $FromDate, $Remarks, $RecordStatus, $PostedUserId);
                $Saved=$StmtSave->execute();
            }

            if($Saved){
                header('Location: LutIgstReferenceList.php');
                exit;
            }
        }
        else{
            $SqlSave="UPDATE `ScrmExporterLutIgstDetails` SET
                `ExporterID`=?, `Type`=?, `ReferenceNumber`=?, `FinancialYear`=?, `FromDate`=?, `Remarks`=?, `Status`=?
                WHERE `ID`=?";
            $StmtSave=$iconn->prepare($SqlSave);
            if($StmtSave){
                $StmtSave->bind_param('isssssii', $ExpID, $Type, $ReferenceNumber, $FinancialYear, $FromDate, $Remarks, $RecordStatus, $LutIgstID);
                $Saved=$StmtSave->execute();
            }

            if($Saved){
                $ArgumentsArray=array();
                $ArgumentsArray['PostedUserId']=$PostedUserId;
                $ArgumentsArray['PageUrl']=$PageUrl;
                $ArgumentsArray['Remarks']=$Remarks;
                $ArgumentsArray['ReferenceID']=$LutIgstID;
                postUserEditLog($ArgumentsArray);
                header('Location: LutIgstReferenceList.php');
                exit;
            }
        }

        if(!$Saved){
            $ValidationError='Unable to save the LUT / IGST reference. Please try again.';
            error_log('AddLutIgstReference save failed: '.$iconn->error);
        }
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
	if($FlashStatus !=''){
	//Show Alert
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="mdi mdi-check-all mr-2"></i>
                                                  <?php echo htmlspecialchars($FlashStatus, ENT_QUOTES, 'UTF-8');	  ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

<?php } ?>

<?php if($ValidationError !=''){ ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($ValidationError, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>



<div class="row">
                            <div class="col-12">
                                <div class="card">

								<form role="form" method="post">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['lut_igst_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="card-body">

										<div class="row">

					<div class="form-group col-lg-3">
              <label for="ExpID">Select Exporter<span style="color:red">*</span></label>
			  <select  class="form-control select2" name="ExpID" id="ExpID">
			  <option value=""> Select </option>

			<?php while($StmtExporter && ($RowExporter=$StmtExporter->fetch_array())) { ?>

			<option <?php if((int) $RowExporter['ExpID']===$ExpID){ ?> selected="selected" <?php } ?>
			value="<?php echo $RowExporter['ExpID'];?>">
			<?php echo htmlspecialchars($RowExporter['ExpName'], ENT_QUOTES, 'UTF-8');?></option>

			<?php } ?>

			</select>
            </div>

			<div class="form-group col-sm-3" >
              <label for="Type">Type<span style="color:red">*</span></label>
			  <select required class="form-control" name="Type" id="Type">
			  <option value=""> Select </option>
			  <option value="LUT" <?php if($Type==='LUT'){ ?> selected="selected" <?php } ?>>LUT</option>
			  <option value="IGST" <?php if($Type==='IGST'){ ?> selected="selected" <?php } ?>>IGST</option>
			  </select>
            </div>

			<div class="form-group col-sm-3" >
              <label for="ReferenceNumber">Reference Number<span style="color:red">*</span></label>
			<input required type="text" class="form-control" name='ReferenceNumber' id="ReferenceNumber"
			  placeholder="e.g. AD3703260112431" value="<?php echo htmlspecialchars($ReferenceNumber, ENT_QUOTES, 'UTF-8') ?>">

            </div>

			<div class="form-group col-sm-3" >
              <label for="FinancialYear">Financial Year<span style="color:red">*</span></label>
			<input required type="text" class="form-control" name='FinancialYear' id="FinancialYear"
			  maxlength="7" pattern="[0-9]{4}-[0-9]{2}" title="Enter a financial year like 2026-27"
			  placeholder="e.g. 2026-27" value="<?php echo htmlspecialchars($FinancialYear, ENT_QUOTES, 'UTF-8') ?>">
            </div>

			<div class="form-group col-sm-3" >
              <label for="FromDate">Effective From<span style="color:red">*</span></label>
			<input required type="date" class="form-control" name='FromDate' id="FromDate"
			  value="<?php echo htmlspecialchars($FromDate, ENT_QUOTES, 'UTF-8') ?>">
            </div>

			<?php if($Count!=0){ ?>
			<div class="form-group col-sm-3" >
              <label for="StatusField">Status</label>
			  <select class="form-control" name="Status" id="StatusField">
			  <option value="1" <?php if($RecordStatus!==0){ ?> selected="selected" <?php } ?>>Active</option>
			  <option value="0" <?php if($RecordStatus===0){ ?> selected="selected" <?php } ?>>Inactive</option>
			  </select>
            </div>
			<?php } ?>

			<div class="form-group col-lg-6" >
              <label for="Remarks">Remarks</label>

			  <textarea name="Remarks" class="form-control" rows="2" cols="50"
                id="Remarks"
				placeholder="Enter Remarks" ><?php echo htmlspecialchars($Remarks, ENT_QUOTES, 'UTF-8') ?></textarea>


            </div>
            </div>



		  <div class="col-xs-12 box-footer"  align="right">
		     <?php    if($Count==0){ ?>
		  <a target="" href="LutIgstReferenceList.php"
       class="btn btn-danger  "> Close</a>

                <button type="submit"  name='submit' class="btn btn-success">Add</button>
			 <?php    } else { ?>
		  <a target="" href="LutIgstReferenceList.php"   class="btn btn-danger  "> Close</a>

                <button type="submit"  name='submit' class="btn btn-success">Update</button>
			 <?php    } ?>

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
</html>
