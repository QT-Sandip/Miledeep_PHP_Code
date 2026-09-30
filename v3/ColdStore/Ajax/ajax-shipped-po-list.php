<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_SHIPPED_PO_LIST;

$Exporter = trim($_POST["Exporter"]);
$Year = trim($_POST["Year"]);
$Country = trim($_POST["Country"]);
$ColdStore = trim($_POST["coldStore"]);
$PageNo = trim($_POST["pageNo"]);
$SearchKey = trim($_POST["searchKey"]);
$LoadAll = trim($_POST["loadAll"]);

$bodyContent = array(
    'Exporter' => $Exporter,
    'Country' => $Country,
    'Year' => $Year,
    'ColdStore' => $ColdStore,
    'PageNo' => $PageNo,
    'SearchKey' => $SearchKey,
    'LoadAll' => $LoadAll
);

// Request:{"PageNo":"1","Exporter":"","Country":"","SearchKey":"","LoadAll":"","Year":"2024"}

$resList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->ShippedPoListData;
        //  // Sort the list by ShipmentDate in ascending order
        // usort($List, function($a, $b) {
        //     return strtotime($a->ShipmentDate) - strtotime($b->ShipmentDate);
        // });
        // Sort the list by ShipmentDate in descending order
        // usort($List, function ($a, $b) {
        //     return strtotime($b->ShipmentDate) - strtotime($a->ShipmentDate);
        // });


?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>S.No</th>
                        <th>C.Inv No</th>
                        <th>C.Inv Date</th>
                        <th>Po.No</th>
                        <th>Po.Date</th>
                        <th>Shipment Date</th>
                        <th>ETA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNo ?></td>
                            <td><?php echo $item->CInvNo ?></td>
                            <td><?php echo $item->CInvDate ?></td>
                            <td><?php echo $item->PoNo ?></td>
                            <td><?php echo $item->PoDate ?></td>
                            <td><?php echo $item->ShipmentDate ?></td>
                            <td><?php echo $item->ETA ?></td>
                        </tr>
                    <?php  } ?>
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