<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($cp . "Includes/IncludePage.php");
include_once ROOT . 'config/api-call.php';

$api = API_PO_STATUS_REPORT;

$bodyContent = array(
    'Exporter' => trim($_POST["exporter"]),
    'Processor' => trim($_POST["processor"]),
    'Consignee' => trim($_POST["consignee"]),
    'Plant' => trim($_POST["coldStore"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'LoadAll' => trim($_POST["loadAll"]),
    'PackingStyle' => trim($_POST["packingStyle"]),
    'Grade' => trim($_POST["grade"]),
    'Freezing' => trim($_POST["freezing"]),
    'Soaking' => trim($_POST["soaking"]),
    'Variety' => trim($_POST["variety"]),
    'PoNumber' => trim($_POST["poNumber"]),
    'PDCategory' => trim($_POST["pDCategory"]),
    'ABPossitive' => trim($_POST["aBPossitive"]),
);

// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);
// echo print_r($resList, JSON_PRETTY_PRINT);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
        // echo json_encode($List, JSON_PRETTY_PRINT);
        // exit;
?>
        <?php if (!isset($error) || empty($error)) { ?>
            <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width:50px;">S.No</th>
                        <th style="min-width: 250px;width: 250px;">Buyer</th>
                        <th style="min-width: 100px;width: 100px;">Po No</th>
                        <th style="min-width: 100px;width: 100px;">Brand</th>
                        <th style="min-width: 100px;width: 100px;">Shipment Date</th>
                        <!-- breakUp -->
                        <th style="min-width: 80px;max-width: 80px;">SUPC</th>
                        <th style="min-width: 250px;max-width: 250px;">Product</th>
                        <th style="min-width: 100px;max-width: 100px;">Variety</th>
                        <th style="min-width: 100px;max-width: 100px;">Freezing Type</th>
                        <th style="min-width: 100px;max-width: 100px;">Soaking Type</th>
                        <th style="min-width: 100px;max-width: 100px;">Packing</th>
                        <th style="min-width: 100px;max-width: 100px;">Grade</th>
                        <th style="min-width: 100px;max-width: 100px;">Ordered Mcs</th>
                        <th style="min-width: 100px;max-width: 100px;">Final Mcs</th>
                        <th style="min-width: 100px;max-width: 100px;">Bal Mcs</th>
                        <th style="min-width: 100px;max-width: 100px;">Ordered Kgs</th>
                        <th style="min-width: 150px;max-width: 150px;">Final Kgs</th>
                        <th style="min-width: 100px;max-width: 100px;">Bal Kgs</th>
                        <th style="min-width: 120px;max-width: 120px;">Aval In Store Kgs</th>
                        <th style="min-width: 120px;max-width: 120px;">Aval Store Mcs</th>
                        <th style="min-width: 100px;max-width: 100px;">Head on Count</th>
                        <th style="min-width: 100px;max-width: 100px;">Qty in Kgs</th>
                        <th style="min-width: 100px;max-width: 100px;">Qty in Tons</th>
                         <th style="min-width: 100px;max-width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td style="vertical-align: middle"><?php echo $item->SNO ?></td>
                            <td style="vertical-align: middle"><?php echo $item->ConsigneeName ?></td>
                            <td style="vertical-align: middle"><?php echo $item->PoNo ?></td>
                            <td style="vertical-align: middle"><?php echo $item->Brand ?></td>
                            <td style="vertical-align: middle"><?php echo $item->ShipByDate ?></td>

                            <td style="padding: 0px !important;" colspan="19">
                                <table class="subTable table table-bordered mb-0" style="width: 100%">
                                    <?php
                                    foreach ($item->PoBreakUp as $itemBreak) { ?>
                                        <tr>
                                            <td style="min-width: 80px;max-width: 80px;"><?php echo $itemBreak->SUPC ?> </td>
                                            <td style="min-width: 250px;max-width: 250px;">
                                                <div style=""><?php echo $itemBreak->Product ?></div>
                                            </td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->Variety ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->FreezingType ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->SoakingType ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->PackingStyle ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->Grade ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->OrderedMcs ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->FinishedMcs ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->BalanceMcs ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->OrderedKgs ?></td>
                                            <td style="min-width: 150px;max-width: 150px;"><?php echo $itemBreak->FinishedKgs ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->BalanceKgs ?></td>
                                            <td style="min-width: 120px;max-width: 120px;"><?php echo $itemBreak->AvalStoreKgs ?></td>
                                            <td style="min-width: 120px;max-width: 120px;"><?php echo $itemBreak->AvalStoreMcs ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->HeadonCount ?></td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->HeadonQuantityinKgs ?>
                                            </td>
                                            <td style="min-width: 100px;max-width: 100px;"><?php echo $itemBreak->HeadonQuantityinTons ?>
                                            </td>
                                           <td style="min-width: 100px;max-width: 100px;" data-excel="<?php echo $itemBreak->Percent ?>">
                                            <div class="badge <?php echo $itemBreak->Colorclass ?> font-size-10"
                                                >
                                                <?php echo $itemBreak->Percent ?>
                                            </div>
                                        </td>
                           

                                  
                                        </tr>
                                    <?php } ?>
                                </table>
                            </td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                            <td style="display: none"></td>
                             <td style="display: none"></td>

                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
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
