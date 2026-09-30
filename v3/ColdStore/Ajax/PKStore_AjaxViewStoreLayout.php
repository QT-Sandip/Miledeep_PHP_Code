<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $apiType = $_POST["apiType"];

// if($apiType == "palletDetails_pop1") {  
//     echo "popup1";
// }else if($apiType == "palletDetails_pop2") {
//     echo "popup2";
// } 

if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}


if ($methodType == "search") {


    $storeID = trim($_POST["storeID"]);
    $blockID = trim($_POST["blockID"]);
    $floorID = trim($_POST["floorsID"]);
    $rackName = trim($_POST["rackName"]);
    $qParam = trim($_POST["qparam"]);

    $api = API_PK_CREATE_STORE_LAYOUT;

    $bodyContent = array(
        "StoreID" => $storeID,
        "BlockID" => $blockID,
        "FloorID" => $floorID,
        "Rows" => "1",
        "Column" => "1",
        "packingstore"=>1,
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        // Business Logic: Extract dataset array from API response
        $List = $resList->Data;
        ?>

        <div class="row" style="display:none;">
            <div class="col-12">
                <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlockData ?$resBlockData: ''), ENT_QUOTES, 'UTF-8'); ?>" />
                <div class="card">
                    <div class="card-body">
                        <div id="divDataList"></div>
                        <input type="hidden" id="hdnActionType" />
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 summary-panel mt-2">
                <div class="horizontal-scroll">
                    <div id="divStoreLayout">
                        <div class="table-wrapper-modern">

                            <div class="rack-layout-header">
                                <div class="filter-label">Packing Store Layout</div>
                                <div class="rack-layout-legend filter-label">
                                    <div class="legend-item">
                                        <span class="legend-box rack-empty-modern"></span>
                                        Empty Rack
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-box rack-filled-modern"></span>
                                        Filled Rack
                                    </div>
                                    <div class="legend-item legend-walk">
                                        <span class="legend-box rack-walkpath-modern" style="width: 35px; height: 8px !important; border-radius: unset;"></span>
                                        <span class="legend-box rack-walkpath-modern" style="width: 35px; height: 8px !important; border-radius: unset; background: #ffa50026;"></span>
                                        Walk Path
                                    </div>
                                </div>
                            </div>

                            <div class="table-box">
                                <table class="coldstore-layout">
                                    <?php
                                    $totalRows = count($List);

                                    for ($r = 0; $r < $totalRows; $r++) {
                                        echo "<tr>";
                                        $rowData = $List[$r]->RowData;
                                        $cols    = count($rowData);

                                        // Scan row to identify if it consists entirely of walk paths
                                        $isWalkPathRow = true;
                                        foreach ($rowData as $cellCheck) {
                                            if ($cellCheck->RackType == "1") {
                                                $isWalkPathRow = false;
                                                break;
                                            }
                                        }

                                        for ($c = 0; $c < $cols; $c++) {
                                            $cell = $rowData[$c];
                                            $oddEvenC = ($c % 2 == 0) ? "even" : "odd";
                                            $oddEvenR = ($r % 2 == 0) ? "even" : "odd";

                                            // -----------------------------------------------------
                                            // CASE 1: HORIZONTAL WALK PATH ROW
                                            // -----------------------------------------------------
                                            if ($isWalkPathRow) {
                                                echo "<td><div class='walk-path-horizontal {$oddEvenC}'></div></td>";
                                                continue;
                                            }



                                            // -----------------------------------------------------
                                            // CASE 2: RACK UNIT DETECTED (RackType == "1")
                                            // -----------------------------------------------------
                                            if ($cell->RackType == "1") {
                                                if(!$cell->Name){ continue; }
                                                $isFilled    = ($cell->RackStatus == "1");
                                                $rackClass   = $isFilled ? "filled" : "";
                                                $fillClass   = $isFilled ? "divFilled" : "divUnFilled";
                                                $tooltipPos  = ($r == 0) ? "tooltip-down" : "tooltip-up";
                                                $tooltipText = $isFilled ? "Filled" : "Empty";

                                                // Dynamic Asset Assignment according to box counts
                                                $icon = "box.svg";
                                                $count = isset($cell->boxcount) ? (int)$cell->boxcount : 0;
                                                if ($count == 1) {
                                                    $icon = "singlebox.svg";
                                                } else if ($count == 2) {
                                                    $icon = "doblebox.svg";
                                                } else if ($count == 3) {
                                                    $icon = "box.svg";
                                                } else if ($count > 3) {
                                                    $icon = "threesome.svg";
                                                }

                                                // Clean label parsing and extracting structural suffixes
                                                $label = $cell->Name;
                                                $suffix = "";
                                                if (preg_match('/[A-Z]$/', $cell->Name, $m)) {
                                                    $suffix = "<span>{$m[0]}</span>";
                                                    $label = preg_replace('/[A-Z]$/', "", $cell->Name);
                                                }

                                                echo "
                                                <td class='rack-cell {$fillClass}'>
                                                    <div class='form-check-input rackno rack-box {$rackClass} {$tooltipPos}' data-rackid='{$cell->RackID}'>
                                                        <div class='rack-label'>{$label} {$suffix}</div>
                                                        <div class='rack-icon'>
                                                            <img src='/Images/{$icon}' " . ($isFilled ? "" : "style='display:none'") . ">
                                                        </div>
                                                        <div class='rack-tooltip'>
                                                            <div class='rack-tooltip-content'>Name: {$cell->Name}</div>
                                                            <div class='rack-tooltip-content'>Status: {$tooltipText}</div>
                                                            <div class='rack-tooltip-content'>Boxes: {$count}</div>
                                                        </div>
                                                    </div>
                                                </td>";

                                            } else {
                                                // -----------------------------------------------------
                                                // CASE 3: VERTICAL WALK-PATH CELLS & HORIZONTAL MERGING
                                                // -----------------------------------------------------
                                                $colspan = 1;
                                                $next = $c + 1;
                                                while ($next < $cols) {
                                                    $nextCell = $rowData[$next];
                                                    if ($nextCell->RackType == "1" || $isWalkPathRow) {
                                                        break;
                                                    }
                                                    $colspan++;
                                                    $next++;
                                                }
                                                $baseWidth = 55;
                                                $computedWidth = $colspan * $baseWidth;
                                                echo "<td colspan='{$colspan}' style='width: {$computedWidth}px;' ><div class='walk-path-vertical {$oddEvenR}' ></div></td>";
                                                $c += ($colspan - 1); // Track column steps forward past merged paths
                                            }
                                        }
                                        echo "</tr>";
                                    }
                                    ?>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}




if ($methodType == "placePalette") {

    $apiPlacePallet = API_PLACE_PALLET;

    $bodyContent = array(
        'BarCodeID' => trim($_POST["barCId"]),
        'RackID' => trim($_POST["rackID"]),
        'StockID' => trim($_POST["stockID"])
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;
    $resPlacePallet = CallAPI($apiPlacePallet, "POST", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resPlacePallet);
}


if ($methodType == 'rackDetails') {

    $apiRack = API_STOCK_PALLET_DETAILS;

    $RackNo = trim($_POST['rackNo']);

    $bodyContentRack = array(
        "RackID" => $RackNo
    );

    $resultRack = CallAPI($apiRack, "GET", "", $bodyContentRack);

    // var_dump($resultRack);

    $error = $resultRack->Error;

    if (!isset($error) || empty($error)) {

        $RackList = $resultRack->Data;
        $Totals = $resultRack->Totals;

        // var_dump($RackList);
        ?>
        <!-- <div class="modal-body"> -->

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>SNO</th>
                    <th>Po Number</th>
                    <th>Species</th>
                    <th>Variety</th>
                    <th>Grade</th>
                    <th>Packing Type</th>
                    <th>Freezing Type</th>
                    <th>Category</th>
                    <th>Shift</th>
                    <th>Mcs</th>
                    <th>Loose</th>
                    <th>Kgs</th>
                </tr>

            </thead>
            <tbody>
                <?php foreach ($RackList as $itemList) { ?>
                    <tr>
                        <td><?php echo $itemList->SNO ?></td>
                        <td><?php echo $itemList->PoNo ?></td>
                        <td><?php echo $itemList->Species ?></td>
                        <td><?php echo $itemList->Variety ?></td>
                        <td><?php echo $itemList->Grade ?></td>
                        <td><?php echo $itemList->PackingType ?></td>
                        <td><?php echo $itemList->FreezingType ?></td>
                        <td><?php echo $itemList->Category ?></td>
                        <td><?php echo $itemList->Shift ?></td>
                        <td><?php echo $itemList->Mcs ?></td>
                        <td><?php echo $itemList->Loose ?></td>
                        <td><?php echo $itemList->Kgs ?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td>Totals</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $Totals->TotalMcs ?></td>
                    <td><?php echo $Totals->TotalLooseBags ?></td>
                    <td><?php echo $Totals->TotalKgs ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- </div> -->
        <?php
    }
}
if ($methodType == 'rackDetailsForPakingStore') {


    $apiRack = API_DAILY_PACKING_STORE_INWARD_DETAILS;

    $RackNo = trim($_POST['rackNo']);

    $bodyContentRack = array(
        "RackID" => $RackNo,
        'methodType' => 'GetPackingStoreDataByFilters'
    );

    $resultRack = CallAPI($apiRack, "GET", "", $bodyContentRack);
//echo '<pre>';print_r($resultRack);die;
    // var_dump($resultRack);

    $error = $resultRack->Error;

    if (!isset($error) || empty($error)) {

        $RackList = $resultRack->Data;
        $Totals = $resultRack->Totals;

        // var_dump($RackList);
        ?>
        <!-- <div class="modal-body"> -->
        <table class="table table-hover table-bordered" id="rackTableData">
            <thead>
                <tr>
                    <th>SNO</th>
                    <th>PackingType</th> 
                    <th>Po Number</th> 
                    <th>BrandName</th> 
                    <th>SlabWeight</th>                   
                    <th>Packing Style</th>                    
                    <th>Grade</th>
                    <th>Colors</th>
                    <th>Sizes</th>
                    <th>Group</th> 
                    <th>Mcs</th>                    
                    <th>Utilize Mcs</th>
                    <th>Pouches</th>
                    <th>Utilize Pouches</th>
                    <th>Spacers</th>
                    <th>Utilize Spacers</th>

                    <th>Stickers</th>
                    <th>Utilize Stickers</th>

                    <th>Tape Rolls</th>
                    <th>Utilize Tape Rolls</th>

                    <th>Count Slips</th>
                    <th>Utilize Count Slips</th>
                    
                </tr>

            </thead>
            <tbody>
                <?php
                    $TotalMcs = $TotalUtilizedMcs = $TotalLooseBags = $TotalUtilizedPouches = $TotalSpacers = $UtilizedSpacers = 0;
                    $TotalStickers = $TotalUtilizedStickers = $TotalTapeRolls = $TotalUtilizeTapeRolls = $TotalCountSlips = $TotalUtilizeCountSlips = 0;
                    foreach ($RackList as $itemList) { 
                        $TotalMcs += $itemList->Mcs;
                        $TotalUtilizedMcs += $itemList->UtilizedMcs;
                        $TotalLooseBags += $itemList->LooseBags;
                        $TotalUtilizedPouches += $itemList->UtilizedPouches;
                        $TotalSpacers += $itemList->Spacers;
                        $UtilizedSpacers += $itemList->UtilizedSpacers;
                        if($itemList->Group == 'Stickers'){
                            $TotalStickers += $itemList->Count;
                            $TotalUtilizedStickers += $itemList->UtilizedCountVal;
                        }
                        if($itemList->Group == 'Tape Rolls'){
                            $TotalTapeRolls += $itemList->Count;
                            $TotalUtilizeTapeRolls += $itemList->UtilizedCountVal;
                        }
                        if($itemList->Group == 'Count Slips'){
                            $TotalCountSlips += $itemList->Count;
                            $TotalUtilizeCountSlips += $itemList->UtilizedCountVal;
                        }
                        ?>
                        <tr>
                            <td><?php echo $itemList->SNO ?></td>
                            <td><?php echo $itemList->PackingTypeID ?></td>
                            <td><?php echo $itemList->PoNo ?></td>
                            <td><?php echo $itemList->BrandName ?></td>
                            <td><?php echo $itemList->SlabWeight ?></td>
                            <td><?php echo $itemList->PackingStyle ?></td>
                            <td><?php echo $itemList->Grade ?></td>
                            <td><?php echo $itemList->Colours ?></td>
                            <td><?php echo $itemList->Sizes ?></td>
                            <td><?php echo $itemList->Group ?></td>
                            <td><?php echo (int)$itemList->Mcs ?></td>
                            <td><?php echo (int)$itemList->UtilizedMcs ?></td>
                            <td><?php echo (int)$itemList->LooseBags ?></td>
                            <td><?php echo (int)$itemList->UtilizedPouches ?></td>
                            <td><?php echo (int)$itemList->Spacers ?></td>
                            <td><?php echo (int)$itemList->UtilizedSpacers ?></td>

                            <td><?php echo (int)($itemList->Group == 'Stickers') ? $itemList->Count : 0; ?></td>
                            <td><?php echo (int)($itemList->Group == 'Stickers') ? $itemList->UtilizedCountVal : 0; ?></td>

                            <td><?php echo (int)($itemList->Group == 'Tape Rolls') ? $itemList->Count : 0; ?></td>
                            <td><?php echo (int)($itemList->Group == 'Tape Rolls') ? $itemList->UtilizedCountVal : 0; ?></td>

                            <td><?php echo (int)($itemList->Group == 'Count Slips') ? $itemList->Count : 0; ?></td>
                            <td><?php echo (int)($itemList->Group == 'Count Slips') ? $itemList->UtilizedCountVal : 0; ?></td>
                        </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td>Totals</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                     <td></td>
                    <td><?php echo $TotalMcs ?></td>
                    <td><?php echo $TotalUtilizedMcs ?></td>
                    <td><?php echo $TotalLooseBags ?></td>
                    <td><?php echo $TotalUtilizedPouches ?></td>
                    <td><?php echo $TotalSpacers ?></td>
                    <td><?php echo $UtilizedSpacers ?></td>

                    <td><?php echo $TotalStickers ?></td>
                    <td><?php echo $TotalUtilizedStickers ?></td>

                    <td><?php echo $TotalTapeRolls ?></td>
                    <td><?php echo $TotalUtilizeTapeRolls ?></td>

                    <td><?php echo $TotalCountSlips ?></td>
                    <td><?php echo $TotalUtilizeCountSlips ?></td>
                </tr>
            </tfoot>
        </table>
        <script>
            $(document).ready(function() {
                setTimeout(function () {
                    if ($.fn.DataTable.isDataTable('#rackTableData')) {
                        $('#rackTableData').DataTable().clear().destroy();
                    }
                    $('#rackTableData').DataTable({
                        searching: false,
                        ordering: false,
                        scrollY: "300px",
                        scrollX: true,
                        sScrollXInner: "100%",
                        paging: false,
                        info: false ,   
                          fixedColumns: {
                    leftColumns: 10
                },                     
                    });
                }, 200);
            })
        </script>
        <!-- </div> -->
<?php
    }
}
?>
