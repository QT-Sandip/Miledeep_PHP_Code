<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$PageTitle = "Biometric Device List";
$PageTitleFolder = "HRM";

$SqlConfig = "SELECT * FROM `AdminConfigDetails`";
$StmtConfig = $iconn->query($SqlConfig);
$RowConfig = $StmtConfig->fetch_assoc();

$ApiUrl = $RowConfig['ApiUrl'];
$ApiUrl = $_SESSION["ClientPath"] . $ApiUrl . "postBioMetricData";


$SqlHolidays = "SELECT * FROM `HrmHolidays` ";
$StmtHoliday = $iconn->query($SqlHolidays);

$SqlBioDevices = "SELECT * FROM `HrmBioMetricDevices`";
$StmtBioDevices = $iconn->query($SqlBioDevices);

?>


<!DOCTYPE html>

<html lang="en">


<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include("Includes/DbLinks.php"); ?>


	<?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">

	<!-- Begin page -->
	<div id="layout-wrapper">


		<?php
		include($IncludesPath . "topbar.php");
		include($IncludesPath . "sidebar.php");
		include($IncludesPath . "PreLoader.php");
		?>



		<!-- ============================================================== -->
		<!-- Start right Content here -->
		<!-- ============================================================== -->
		<div class="main-content">

			<div class="page-content">
				<div class="container-fluid">

					<?php include($IncludesPath . "page-title.php"); ?>

					<!--<h2 align="center">Add Holiday</h2>-->


					<div class="row">
					    <div class="col-12">
					        <div class="card">
					            <div class="card-body" id="ATT_DATA">

					                <!-- Action Buttons -->
					                <div class="row mb-3 text-center">
					                    <div class="col-12">
					                        <a href="NewBioMetricMachine.php" class="btn btn-success">
					                            Add
					                        </a>

					                        <button
					                            type="button"
					                            class="btn btn-primary btn-sm btn-flat"
					                            id="getDATA">
					                            <i class="fa fa-search"></i> GET DATA
					                        </button>
					                    </div>
					                </div>

			                <!-- Progress Bar -->
				                <div class="progress mb-2" style="height: 25px;">
				                    <div
				                        id="progressBar"
				                        class="progress-bar bg-success"
				                        role="progressbar"
				                        style="width: 0%;">
				                        0%
				                    </div>
				                </div>
				                <p id="statusText" class="text-muted mb-2" style="min-height:20px;"></p>
				                <iframe id="cronFrame" style="display:none;"></iframe>

					                <!-- Progress Log -->
					                <ul id="progressLog" class="list-unstyled mb-3"></ul>

					                <!-- Device Table -->
					                <div class="table-responsive">
					                    <table
					                        id="key-datatable"
					                        class="table table-striped table-bordered dt-responsive nowrap w-100"
					                        data-paging="false">

					                        <thead class="thead-light">
					                            <tr>
					                                <th>Id</th>
					                                <th>IP Address</th>
					                                <th>Device Location</th>
					                                <th>Device Type</th>
					                            </tr>
					                        </thead>

					                        <tbody>
					                            <?php
					                            $ipList = [];

					                            while ($RowDevices = $StmtBioDevices->fetch_assoc()) {
					                                $ipList[] = $RowDevices['IPAddress'];
					                                ?>
					                                <tr>
					                                    <td><?= $RowDevices['ID']; ?></td>
					                                    <td><?= $RowDevices['IPAddress']; ?></td>
					                                    <td><?= $RowDevices['DeviceLocation']; ?></td>
					                                    <td><?= $RowDevices['DeviceType']; ?></td>
					                                </tr>
					                                <?php
					                            }
					                            ?>
					                        </tbody>

					                    </table>
					                </div>

					            </div>
					        </div>
					    </div>
					</div>
				</div> <!-- end card -->
			</div><!-- end col-->
		</div>
		<!-- end row-->

	</div> <!-- container-fluid -->
	</div>
	<!-- End Page-content -->

	<?php include($IncludesPath . "footer.php"); ?>
	<?php include 'Includes/add_holiday_modal.php'; ?>

	</div>
	<!-- end main content-->

	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include("Includes/NewScripts.php"); ?>


<script>
    var ipList = <?php echo json_encode($ipList); ?>;
    var isSyncRunning = false;

    function updateProgressBar(done, total) {
        var percent = Math.round((done / total) * 100);
        if (percent > 100) percent = 100;
        $("#progressBar")
            .css('width', percent + '%')
            .text(percent + '%');
    }

    function logProgress(ip, message, success) {
        var color = success ? 'green' : 'red';
        $("#progressLog").append(
            '<li style="color:' + color + '">' + ip + ' - ' + message + '</li>'
        );
        var logEl = document.getElementById('progressLog');
        if (logEl) logEl.scrollTop = logEl.scrollHeight;
    }

    function setStatusText(text) {
        $("#statusText").text(text);
    }

    function callLogData(IPAddress, Status, ApiUrl) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: 'http://localhost/DeviceData/LogData.php',
                type: 'POST',
                data: {
                    IP: IPAddress,
                    Status: Status,
                    ApiUrl: ApiUrl
                },
                success: function (data) {
                    try {
                        var res = JSON.parse(data);
                        if (res.Error) reject(res.Error);
                        else resolve(res);
                    } catch (e) {
                        reject('Invalid response from device');
                    }
                },
                error: function () { reject('LogData request failed'); }
            });
        });
    }

    function callUpdate(info, IPAddress) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: 'Ajax/AjaxBioMetricDeviceList.php',
                type: 'POST',
                data: JSON.stringify(info),
                contentType: "application/json",
                processData: false,
                success: function (data) {
                    if (data.Error == null) {
                        resolve(true);
                    } else {
                        reject(data.Error);
                    }
                },
                error: function () { reject('Update request failed'); }
            });
        });
    }

    function fnClearData(ipAddr) {
        return new Promise(function (resolve) {
            $.ajax({
                url: 'http://localhost/DeviceData/LogData.php',
                type: 'POST',
                data: { IP: ipAddr, Type: "C" },
                success: function () { resolve(); },
                error: function () { resolve(); }
            });
        });
    }

    function waitForCronComplete() {
        return new Promise(function (resolve) {
            var cronFrame = document.getElementById('cronFrame');
            if (!cronFrame) { resolve(); return; }
            cronFrame.onload = function () { resolve(); };
        });
    }

    function startCronIframe() {
        $("#cronFrame").attr("src", "ManualScripts/cron_backup_2_attendance.php?regen=true");
    }

    function lockNavigation() {
        isSyncRunning = true;
        window.addEventListener("beforeunload", beforeUnloadHandler);
    }

    function unlockNavigation() {
        isSyncRunning = false;
        window.removeEventListener("beforeunload", beforeUnloadHandler);
    }

    function beforeUnloadHandler(e) {
        if (isSyncRunning) {
            var msg = "Biometric synchronization is currently in progress, including final data processing. Leaving this page will interrupt the process. Are you sure you want to leave?";
            e.returnValue = msg;
            return msg;
        }
    }

    $(document).on('click', '#getDATA', async function (e) {
        e.preventDefault();

        var $btn = $(this);
        if ($btn.hasClass('disabled')) return;

        $btn.addClass('disabled').prop('disabled', true);
        $("#progressLog").empty();
        lockNavigation();

        var Status = 'Both';
        var ApiUrl = '<?php echo $ApiUrl ?>';
        var totalDevices = ipList.length;
        var totalSteps = totalDevices + 1;
        var completed = 0;

        updateProgressBar(0, totalSteps);

        for (var i = 0; i < totalDevices; i++) {
            var ip = ipList[i];
            var deviceNum = i + 1;

            setStatusText('Processing Device ' + deviceNum + ' of ' + totalDevices + '...');
            logProgress(ip, 'Processing device ' + deviceNum + ' of ' + totalDevices + '...', true);

            try {
                var apiData = await callLogData(ip, Status, ApiUrl);
                await callUpdate(apiData, ip);
                await fnClearData(ip);
                logProgress(ip, 'Device ' + deviceNum + ' completed successfully', true);
            } catch (err) {
                console.error('Error for IP:', ip, err);
                logProgress(ip, 'Device ' + deviceNum + ' failed: ' + err, false);
            }

            completed++;
            updateProgressBar(completed, totalSteps);
        }

        setStatusText('Running background cron...');
        logProgress('SYSTEM', 'All devices processed. Running background cron...', true);

        var cronPromise = waitForCronComplete();
        startCronIframe();
        await cronPromise;

        completed++;
        updateProgressBar(completed, totalSteps);

        setStatusText('Synchronization completed successfully.');
        logProgress('SYSTEM', 'Synchronization completed successfully.', true);

        unlockNavigation();
        $btn.removeClass('disabled').prop('disabled', false);
    });

    $(document).on('click', '#DELETE', function(e) {
        e.preventDefault();
        if (!confirm("ARE YOU SURE YOU WANT TO DELETE?")) return;
        var ID = $(this).val();
        $.ajax({
            url: 'Ajax/AjDelBmd.php',
            type: 'POST',
            data: { ID: ID },
            success: function(data) {
                if (data == 'SUCESS') {
                    location.reload(true);
                } else {
                    alert("DELETE operation failed. Please Retry");
                }
            }
        });
    });
</script>

</body>

</html>
