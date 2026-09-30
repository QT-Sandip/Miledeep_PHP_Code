<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


// echo json_encode($resProcessingStations, JSON_PRETTY_PRINT);

// var_dump($resDropdownsAPI);

$CurrentMonth = date('m');
$CurrentYear = date('Y');

// Determine the past FY and current FY based on the current month
if ($CurrentMonth > 3) {
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
}

?>

<div class="row rowFilter">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <?php
                    $Button = '<input type="button" id="btnSearch" name="btnSearch" class="btn btn-primary" style="max-width:150px;min-width: 150px;max-height: 35px;
                        margin-left: 15px;" value="Search" aria-label="Submit">';
                    ?>
                    <?php if (isset($filterFromDate) && !empty($filterFromDate)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="txtFromDate">From Date</label>
                            <input type="date" class="form-control" id="txtFromDate" name="txtFromDate"
                                value="<?php echo date('Y-m-d') ?>" />
                        </div>
                    <?php } ?>

                    <?php if (isset($filterToDateNew) && !empty($filterToDateNew)) { ?>
                        <div class="col col-md-3 mb-3 ">
                            <label for="txtToDate">To Date</label>
                            <input type="date" class="form-control" id="txtToDate" name="txtToDate"
                                value="<?php echo date('Y-m-d') ?>" />
                        </div>
                    <?php } ?>
                    <?php if (isset($filterStatus) && !empty($filterStatus)) { ?>
                        <div class="col col-md-3 mb-3">
                            <label for="filter_status">Status</label>
                            <select id="filter_status" class="drpSelect form-control">
                                <option value="All">All</option>
                                <option value="Approve">Approve</option>
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    <?php } ?>
                    <?php if (isset($buttonButtom) && !empty($buttonButtom)) { ?>
                        <div class="text-center mt-1">
                            <button id="btnSearch" type="button" class="btn btn-primary waves-effect waves-light">
                                Search <i class="ri-arrow-right-line align-middle ml-2"> </i>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>