<?php


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if(isset($_POST["methodType"]) && !empty($_POST["methodType"])){

    $method = $_POST["methodType"];
    $orderId = trim($_POST["orderId"]);

    $api = API_CLEARING_AGENT_ACTIONS;

    $bodyContent = array(
        'OrderID' => $orderId
    );
    
    $resDelete = CallAPI($api, "DELETE", "", $bodyContent);

     // var_dump($bodyContent);

    header('Content-Type:application/json');

    echo json_encode($resDelete);

}
else{
$api = API_CLEARING_AGENT;

$FromDate = trim($_POST["FromDate"]);
$ToDate = trim($_POST["ToDate"]);
$Status = trim($_POST["Status"]);
// $CountryID = trim($_POST["CountryID"]);
$CountryID = isset($_POST["CountryID"]) ? $_POST["CountryID"] : [];
$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'FromDate' => $FromDate,
    'Status' => $Status,
    'CountryID' => $CountryID,
    'ToDate' => $ToDate,
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resClearing = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resClearing);

if (isset($resClearing) && !empty($resClearing)) {

    $error = $resClearing->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resClearing->PageCount;
        $resList = $resClearing->Data;
        ?>

        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>S.No</th>
                    <th>Inv No</th>
                    <th>Buyer</th>
                    <th>BL No</th>
                    <th>Port Of Discharge</th>
                    <th>Notify party Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resList as $item) { ?>
                    <tr>
                        <td>
                            <?php echo $item->SNO ?>
                        </td>
                        <td>
                            <?php echo $item->InvNo ?>
                        </td>
                        <td>
                            <?php echo $item->Buyer ?>
                        </td>
                        <td>
                            <?php echo $item->BLNo ?>
                        </td>
                        <td>
                            <?php echo $item->PortOfDischarge ?>
                        </td>
                        <td>
                            <?php echo $item->NotifypartyName ?>
                        </td>
                        <td>
                            <!-- <?php if ($resClearing->DeleteAccess) { ?>
                                <button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px"
                                    data-id="<?php echo $item->ID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value=""
                                    data-original-title="Delete">
                                    <i class="mdi mdi-trash-can font-size-16"></i></button>
                            <?php } ?> -->
                            <!-- <button id="view" class="mr-3 text-primary btnActions" data-placement="top" title="view"
                            data-original-title="Edit" data-id="1170" data-value=""><i class="dripicons-preview font-size-16" style="color:green" ></i></button>
                         -->
                            <?php if ($Status == "1") { // Completed status ?>
                                <a href="<?= $resClearing->EditLink . $item->ID . '&status=' . $item->ApprovalStatus ?>"
                                class="mr-3 text-primary btnActions"
                                data-placement="top"
                                title="Edit"
                                data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>

                                <?php if ($item->ApprovalStatus !== 1): ?>
                                    <button class="btn btn-link text-danger btnActions btnDelete"
                                            style="position: relative; top: -3px"
                                            data-id="<?= $item->ID ?>"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="Delete"
                                            data-original-title="Delete">
                                        <i class="mdi mdi-trash-can font-size-16"></i>
                                    </button>
                                <?php endif; ?>

                            <?php } else { // Pending status ?>
                                <a href="<?= $resClearing->EditLink . $item->ID ?>"
                                class="mr-3 text-primary btnActions"
                                data-placement="top"
                                title="Add"
                                data-original-title="Add">
                                    <i class="mdi mdi-plus font-size-16"></i>
                                </a>
                            <?php } ?>

                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>



        <div class="mt-3 mb-3">Showing 1 to
            <?php echo count($resList) ?> out of
            <?php echo $resClearing->TotalCount ?> entries
        </div>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo $resClearing->TotalCount ?>' /> -->
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
}

?>