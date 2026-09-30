<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_DELAYED_SHIPMENTS;

$bodyContent = array(

    'Month' => trim($_POST["Month"]),
    'YEAR' => trim($_POST["Year"]),
    'ExporterID' => trim($_POST["Exporter"]),
    'ConsigneeID' => trim($_POST["Consignee"]),
    'PageNo' => trim($_POST["PageNo"]), 
    'SearchKey' => trim($_POST["SearchKey"]),
    'LoadAll' => trim($_POST["LoadAll"]),
);

// var_dump($bodyContent);
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;


$resList = CallAPI($api, "GET", "", $bodyContent);


if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
?>

        <?php if (!isset($error) || empty($error)) { ?>
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>SNO</th>
                        <th>ID</th>
                        <th>Po No</th>
                        <th style="width: 90px;">Po Date</th>
                        <th>Production Start Date</th>
                        <th>Country</th>
                        <th>Buyer</th>
                        <th style="width: 90px;">Actual Ship By Date</th>
                        <th style="width: 90px;">Stock Out Date </th>
                        <th>Status</th>
                        <th>Days Rem/Delayed</th>
                        <!-- <th>Actions</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->ID ?></td>
                            <td><?php echo $item->PoNo ?></td>
                            <td><?php echo $item->PoDate ?></td>
                            <td><?php echo $item->ProductionStartDate ?></td>
                            <td><?php echo $item->Country ?></td>
                            <td><?php echo $item->Buyer ?></td>
                            <td><?php echo $item->ActualShipByDate ?></td>
                            <td><?php echo $item->StockOutDate ?></td>
                            <td><?php echo $item->Status ?></td>
                            <td><?php echo $item->DaysRemDelayed ?></td>
                            <!-- <td class="text-center">
                            <?php //if ($resList->EditAccess) { ?>
                        <a href="CreateShipmentStatus?PoID=<?php echo $item->ID ?>" class="text-primary btnActions btnViewDocRpt" data-placement="top" title="View">
                            <button id="View" class="btn btn-link">
                                <i class="dripicons-preview font-size-18"></i>
                            </button>
                        </a> 
                            <?php // } ?>
                            <?php //if ($resList->EditAccess) { ?>
                                    <a href="CreateShipmentStatus"<?php echo  $item->ViewLink ?>' class="mr-3 text-primary btnActions" data-placement="top" title="View" data-original-title="View">
                                        <i class="mdi mdi-pencil font-size-16"></i>
                                    </a>
                            <?php // } ?>
                       </td> -->
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php  } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
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
?>