<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

$Month = $_POST['Month'];
$Month = sprintf("%02d", $Month);

$Year = $_POST['Year'];
$Type = $_POST['Type'];
$PsId = $_POST['PsId'];
$AccessType = $_SESSION['AccessType'];
//$btn_id=$_POST['ID'];
$DateStart = $Year . '-' . $Month . '-01';
$DateEnd = $Year . '-' . $Month . '-31 23:59:59';

$Status = $_POST['Status'];

$ClientID = $_SESSION['ClientID'];

$ClientID = $_SESSION['ClientID'];

// var_dump($_SESSION);exit;

if ($PsId != '') {
	$Query = "AND ALD.ClientID='$PsId'  ";
}

if ($Status != '') {
	$Query .= "AND ALD.Status='$Status'";
}

// if($AccessType!=1){
// 	$Query="where ALD.ClientID='$ClientID'  ";
// }

$SqlRmLotDetatils = "SELECT * FROM `AdminLoginDetails` as ALD 
LEFT join AdminDepartments as AD on ALD.DeptID=AD.DeptID
LEFT join AdminRegisteredClients as ARC on ARC.ClientAccountID=ALD.ClientID WHERE 1=1 $Query ";

$StmtRmLotDetatils = $iconn->query($SqlRmLotDetatils);

?>

<!-- <table id="scroll-vertical-datatable" class="table table-striped table-bordered dt-responsive wrap"> -->
<table id="scroll-vertical-datatable" class="table table-hover table-bordered">
	<thead>
		<th align="CENTER">UserId</th>
		<th align="CENTER">ClientName</th>
		<th align="CENTER">Department</th>
		<th align="CENTER">UserName</th>
		<th align="CENTER">Designation</th>
		<th align="CENTER">Access Type</th>
		<th align="CENTER">Last Login</th>
		<th align="CENTER">Login Id</th>
		<th align="CENTER">Status</th>
		<th style="min-width: 80px;" align="CENTER">Actions</th>
	</thead>
	<tbody>
		<?php while ($RowRmLotDetatils = $StmtRmLotDetatils->fetch_array()) { ?>
			<tr>
				<td align="left" width="5%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['UserID']; ?></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><B><?php echo $RowRmLotDetatils['ClientName']; ?></B></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><B><?php echo $RowRmLotDetatils['DepartmentName']; ?></B></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><B><?php echo $RowRmLotDetatils['UserName']; ?></B></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['DesignationID']; ?></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['AccessType']; ?></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'"><?php echo getFormatedDateTime($RowRmLotDetatils['LastLogin']); ?></font>
				</td>
				<td align="left" width="10%" class="tdUserLoginId">
					<font size="'.$text.'"><?php echo $RowRmLotDetatils['UserLoginID']; ?></font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'">
						<?php if ($Status == 0) {
							$color = '#FA0F04';
						} else {
							$color = '#025009';
						} ?>
						<div class="bg-active color-palette text-center">
							<span class="btn btn-link badge badge-warning font-size-10 btnPIStatus" style="width:70px;background:<?php echo $color ?>;color:white" data-toggle="popover" data-trigger="focus" data-poid="<?php echo $item->ID ?>">
								<?php echo ($Status == 0) ? "In-Active" : "Active"; ?>
							</span>
						</div>
					</font>
				</td>
				<td align="left" width="10%">
					<font size="'.$text.'">
						<a target="_blank" href="../Users/edit-user.php?id=<?php echo $RowRmLotDetatils['UserID']; ?>" title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
						<!-- <a class="aLogins aUserLogin" href="javascript:void(0)" class="aUserLogin" title="Login"><i class="mdi mdi-login-variant font-size-18"></i></a> -->
						<a class="aIndex " target="_blank" href="UserLogin.php?UserID=<?php echo $RowRmLotDetatils['UserID']; ?>" title="Login"><i class="mdi mdi-login-variant font-size-18"></i></a>
					<?php if($_SESSION['AccessType']==1){ ?>
						<a class="rIndex " target="_blank" href="resetPassword.php?UserID=<?php echo $RowRmLotDetatils['UserID']; ?>" title="Reset Password" onclick="return confirm('Are you sure you want to reset this user’s password?');"><i class="mdi mdi-lock-reset font-size-18"></i></a>
					<?php } ?>
					</font>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
