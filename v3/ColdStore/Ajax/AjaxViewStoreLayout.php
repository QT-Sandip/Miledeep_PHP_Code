<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


function convertWeight($kgs, $toUnit)
{
    $toUnit = strtolower($toUnit);

    switch ($toUnit) {
        case 'kg':
            return $kgs;

        case 'g': // grams
            return $kgs * 1000;

        case 't': // metric tonnes
            return $kgs / 1000;

        case 'lb': // pounds
            return $kgs * 2.2046;

        case 'oz': // ounces
            return $kgs * 35.27396195;

        case 'st': // stones
            return $kgs * 0.157473044;

        case 'us_ton': // short ton
            return $kgs * 0.00110231131;

        case 'uk_ton': // long ton
            return $kgs * 0.00098420653;

        case 'container': // 1 container = 20,000 kg
            return $kgs / 20000;

        default:
            trigger_error('Unsupported unit: ' . $toUnit, E_USER_WARNING);
            return false;
    }
}

function indian_number_format($num) {
    $num = (string)$num;
    $last3 = substr($num, -3);
    $rest = substr($num, 0, -3);

    if ($rest != '') {
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        return $rest . ',' . $last3;
    }
    return $last3;
}
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
    // $rows = trim($_POST["rows"]);
    // $cols = trim($_POST["cols"]);

    $api = API_CREATE_STORE_LAYOUT;

    $bodyContent = array(
        "StoreID" => $storeID,
        "BlockID" => $blockID,
        "FloorID" => $floorID,
        "Rows" => "1",
        "Column" => "1"
    );


    $resList = CallAPI($api, "GET", "", $bodyContent);

    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;
    $error = $resList->Error;
    // $resList->Data;
    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;

    if (!isset($error) || empty($error)) {

        $List = $resList->Data->AryData;
        $ListAnalytics = $resList->Data->totalAnalytics;
        //echo "<pre>"; print_r($ListAnalytics); exit;
        $TopVariety1 = $ListAnalytics->VarietyWiseStorage[0]->Variety;
        $TopVariety2 = $ListAnalytics->VarietyWiseStorage[1]->Variety;
        $TopVariety3 = $ListAnalytics->VarietyWiseStorage[2]->Variety;
        $capacity_overview = [$ListAnalytics->TotalCapacity, $ListAnalytics->UsedCapacity, $ListAnalytics->FreeCapacity, ];
        $Usedcapacity_overview = [$ListAnalytics->UtilizationPercentage, 100 - $ListAnalytics->UtilizationPercentage, ];
        $usedPercent = ($ListAnalytics->UsedCapacity/$ListAnalytics->TotalCapacity)*100;
        $freePercent = 100 -$usedPercent;
        $storageWeights_overview = [$ListAnalytics
            ->TotalStoredWeight->TotalStoredWeight, $ListAnalytics
            ->TotalStoredWeight->TotalLooseKgs, ];
        foreach ($ListAnalytics->VarietyWiseStorage as $items)
        {
            $varities[] = $items->Variety;
            $kgs[] = round($items->Kgs + $items->Lkgs);
        }

        $PackingTypeData =[];
        //echo "<pre>"; print_r($ListAnalytics); exit;
        foreach( $ListAnalytics->PackingType as $listofpacktypes ){
            $PackingTypelabels[] = $listofpacktypes->PackingTypeID;
            $PackingTypekgs[] = $listofpacktypes->TotalKgs;
            $PackingTypeData[$listofpacktypes->PackingTypeID] = $listofpacktypes->TotalKgs;
        }
        $dummyKgs = $PackingTypeData['DUMMY'];
        $masterKgs = $PackingTypeData['MASTER'];
        $totalStockKgs = $dummyKgs + $masterKgs;

        $dummyPercent  = ($dummyKgs / $totalStockKgs) * 100;
        $masterPercent = ($masterKgs / $totalStockKgs) * 100;
        $packingTypes = ['DUMMY','MASTER'];
$packingKgs   = [$dummyKgs, $masterKgs];        // e.g., 99164, 58493
$totalKgs     = array_sum($packingKgs);

$packingPercent = array_map(function($v) use($totalKgs) {
    return round(($v/$totalKgs)*100,1);
}, $packingKgs);
$secondTab_totalkgs = round($ListAnalytics->TotalStoredWeight->TotalStoredWeight);
?>

                <div class="row" style="display:none;">
                    <div class="col-12">
                        <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlockData), ENT_QUOTES, 'UTF-8'); ?>" />
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>

                        </div>

                    </div>
                </div>
       <div id="insightsPanel" style="margin-top:5px;">

            <div class="summary-panel-modern">





                <div class="summary-card-modern bg-blue-50 border-1 border-blue-200">


    <div class="summary-info">
        <div class="text-xs text-blue-700 uppercase tracking-wide">Total Stored Weight</div>

        <div class="text-xs mt-2 text-blue-900 flex justify-between items-center text-[25px]" style="font-size:25px;">
                            <div><?= indian_number_format(
                                round(
                                    $ListAnalytics->TotalStoredWeight
                                        ->TotalStoredWeight
                                )
                            ) ?> kg
                                    </div>
                                    <div><?= indian_number_format(
                                        round(
                                            $ListAnalytics->TotalStoredWeight
                                                ->TotalMcs,2
                                        )
                                    ) ?> McS</div>
                        </div>



        <div class="capacity-legend">
            <span class="text-xs  mt-1 mr-0  uppercase">Tonnes <?= number_format(convertWeight($secondTab_totalkgs, 't'), 3) ?> </span>
            <span class="text-xs mt-1 mr-0  uppercase">Pounds <?= number_format(convertWeight($secondTab_totalkgs, 'lb'), 2) ?> </span>
        </div>
    </div>
    <div class="summary-icon">
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 3a4 4 0 00-4 4v2h8V7a4 4 0 00-4-4z"></path>
            <path d="M6 9h12l1 11H5L6 9z"></path>
        </svg>
    </div>
    <div class="summary-sub" style="display:none;">


            <?= number_format(convertWeight($secondTab_totalkgs, 't'), 3) ?> t<br>
            <?= number_format(convertWeight($secondTab_totalkgs, 'lb'), 2) ?> pound<br>
            <?= number_format(convertWeight($secondTab_totalkgs, 'container'), 2) ?> container
        </div>
</div>

<div class="summary-card-modern capacity-card bg-green-50 border-1 border-green-200" onclick='graphCapacity(<?php echo json_encode($capacity_overview); ?>)' style="cursor: pointer;">
              <div class="capacity-header">
                <span class="text-xs text-green-700 uppercase tracking-wide">Racks Overview</span>
                <!-- <span class="capacity-total text-xs uppercase">Total Racks: <?=$ListAnalytics->TotalCapacity?></span> -->
              </div>

              <div class="capacity-bar mt-2">
                <div class="used" style="width:<?=$usedPercent?>%"></div>
                <div class="free" style="width:<?=$freePercent?>%"></div>
              </div>

              <div class="capacity-legend">
                  <span class="text-xs mt-1 legend-item used-dot uppercase">
                    Used Racks <?=round($usedPercent)?>% (<?=$ListAnalytics->UsedCapacity?>)
                  </span>
                  <span class="text-xs mt-1 legend-item free-dot uppercase">
                    Free Racks <?=round($freePercent)?>% (<?=$ListAnalytics->FreeCapacity?>)
                  </span>
                </div>

            </div>






                <div class="summary-card-modern bg-rose-50 border-1 border-rose-200" onclick='graphVarietyKgs(<?php echo json_encode($varities); ?>,<?php echo json_encode($kgs); ?>)'  style="cursor: pointer;">

                    <div class="summary-info">
                        <div class="text-xs text-orange-700 uppercase tracking-wide">Top Varieties</div>
                        <div class="text-xs mt-2 text-orange-900" style="font-size:25px">
                            <?php
                            $varieties = implode(', ', array_filter([$TopVariety1, $TopVariety2, $TopVariety3]));
                            echo mb_strimwidth($varieties, 0, 20, ' ...');
                            ?>
                        </div>
                        <div class="text-xs  mt-2 uppercase">Based on Total Kgs</div>
                    </div>
                    <div class="summary-icon" style="background-color:oklch(0.9 0.08 70.42)">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                             stroke="#ca3500" stroke-width="2">
                            <path d="M3 3v18h18"></path>
                            <rect x="7" y="12" width="3" height="6"></rect>
                            <rect x="12" y="9" width="3" height="9"></rect>
                            <rect x="17" y="6" width="3" height="12"></rect>
                        </svg>
                    </div>
                </div>

                <div class="summary-card-modern capacity-card bg-amber-50 border-1 border-amber-200" >
              <div class="capacity-header">
                <span class="text-xs text-purple-700 uppercase tracking-wide">Packing Type</span>
                <!-- <span class="capacity-total text-xs uppercase">Total KGS: <?= number_format($totalStockKgs) ?></span> -->
              </div>

              <div class="capacity-bar mt-2">
                <div class="used" style="background: oklch(0.5 0.26 301.69);border: 2px solid oklch(0.5 0.26 301.69);width:<?=$dummyPercent?>%"></div>
                <div class="free" style="width:<?=$masterPercent?>%"></div>
              </div>

              <div class="capacity-legend">
                  <span class="text-xs  mt-1 legend-item used2-dot" >
                    DUMMY <?=round($dummyPercent)?>% (<?= number_format($dummyKgs) ?>)
                  </span>
                  <span class="text-xs mt-1 legend-item free-dot">
                    MASTER <?=round($masterPercent)?>% (<?= number_format($masterKgs) ?>)
                  </span>
                </div>

            </div>

            </div>


<?php if(0){ ?>
<!-- /*remove2 */ -->

<div class="summary-panel-modern">

            <div
  class="p-5 rounded-xl border border-green-200 bg-green-50 cursor-pointer
         hover:bg-green-100 hover:shadow-md transition"
  onclick='graphCapacity(<?= json_encode($capacity_overview); ?>)'
>

  <!-- Header -->
  <div class="flex items-center justify-between mb-4">

    <div class="flex items-center gap-2">
      <!-- Icon -->
      <div class="flex items-center justify-center w-8 h-8 rounded-md bg-white shadow">
        <!-- Warehouse / Racks Icon -->
        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
             stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 21h18"></path>
          <path d="M5 21V7l7-4 7 4v14"></path>
          <path d="M9 21v-6h6v6"></path>
        </svg>
      </div>

      <span class="text-sm font-medium text-gray-600">
        Racks Overview
      </span>
    </div>

    <span class="text-sm font-semibold text-gray-700">
      Total Racks: <?= $ListAnalytics->TotalCapacity ?>
    </span>
  </div>

  <!-- Capacity Bar -->
  <div class="relative w-full h-4 rounded-full bg-green-100 overflow-hidden">

    <!-- Used -->
    <div
      class="absolute left-0 top-0 h-full bg-green-500 rounded-full transition-all duration-700"
      style="width: <?= $usedPercent ?>%;"
    ></div>

    <!-- Free -->
    <div
      class="absolute right-0 top-0 h-full bg-green-300 rounded-full transition-all duration-700"
      style="width: <?= $freePercent ?>%;"
    ></div>
  </div>

  <!-- Legend -->
  <div class="flex items-center justify-between mt-4 text-xs text-gray-600">

    <div class="flex items-center gap-2">
      <span class="w-3 h-3 rounded-full bg-green-500"></span>
      <span>
        Used Racks <?= round($usedPercent) ?>%
        (<?= $ListAnalytics->UsedCapacity ?>)
      </span>
    </div>

    <div class="flex items-center gap-2">
      <span class="w-3 h-3 rounded-full bg-green-300"></span>
      <span>
        Free Racks <?= round($freePercent) ?>%
        (<?= $ListAnalytics->FreeCapacity ?>)
      </span>
    </div>

  </div>
</div>




                <div
  class="flex items-center justify-between gap-4 p-5 rounded-xl border border-blue-200 bg-blue-50
         hover:bg-blue-100 transition cursor-default"
>
  <!-- Icon -->
  <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-white shadow">
    <!-- Scale / Weight Icon -->
    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
         viewBox="0 0 24 24">
      <path d="M12 3a4 4 0 00-4 4v2h8V7a4 4 0 00-4-4z"></path>
      <path d="M6 9h12l1 11H5L6 9z"></path>
    </svg>
  </div>

  <!-- Info -->
  <div class="flex-1">
    <div class="text-sm text-gray-500 font-medium">
      Total Stored Weight
    </div>

    <div class="text-2xl font-bold text-gray-800 mt-1">
      <?= indian_number_format(round(
          $ListAnalytics->TotalStoredWeight->TotalStoredWeight
      )) ?> kg
    </div>

    <div class="text-xs text-gray-500 mt-2">
      <?= number_format(convertWeight($secondTab_totalkgs, 't'), 3) ?> t |
      <?= number_format(convertWeight($secondTab_totalkgs, 'lb'), 2) ?> lb |
      <?= number_format(convertWeight($secondTab_totalkgs, 'container'), 2) ?> c
    </div>
  </div>
</div>



<div class="p-5 rounded-xl border border-amber-200 bg-amber-50">

  <!-- Header -->
  <div class="flex items-center justify-between mb-4">

    <div class="flex items-center gap-2">
      <!-- Icon -->
      <div class="flex items-center justify-center w-8 h-8 rounded-md bg-white shadow">
        <!-- Box / Packing Icon -->
        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor"
             stroke-width="2" viewBox="0 0 24 24">
          <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 002 2h14a2 2 0 002-2z"/>
          <path d="M3.3 7L12 12l8.7-5"/>
          <path d="M12 22V12"/>
        </svg>
      </div>

      <span class="text-sm font-medium text-gray-600">
        Packing Type
      </span>
    </div>

    <span class="text-sm font-semibold text-gray-700">
      Total KGS: <?= number_format($totalStockKgs) ?>
    </span>
  </div>

  <!-- Capacity Bar -->
  <div class="relative w-full h-4 rounded-full bg-amber-100 overflow-hidden">

    <!-- Dummy -->
    <div
      class="absolute left-0 top-0 h-full bg-amber-200 border-2 border-amber-300 rounded-full transition-all duration-700"
      style="width: <?= $dummyPercent ?>%;"
    ></div>

    <!-- Master -->
    <div
      class="absolute right-0 top-0 h-full bg-amber-400 rounded-full transition-all duration-700"
      style="width: <?= $masterPercent ?>%;"
    ></div>
  </div>

  <!-- Legend -->
  <div class="flex items-center justify-between mt-4 text-xs text-gray-600">

    <div class="flex items-center gap-2">
      <span class="w-3 h-3 rounded-full bg-amber-200 border border-amber-300"></span>
      <span>
        DUMMY <?= round($dummyPercent) ?>%
        (<?= number_format($dummyKgs) ?>)
      </span>
    </div>

    <div class="flex items-center gap-2">
      <span class="w-3 h-3 rounded-full bg-amber-400"></span>
      <span>
        MASTER <?= round($masterPercent) ?>%
        (<?= number_format($masterKgs) ?>)
      </span>
    </div>

  </div>
</div>


                <div
  class="flex items-center gap-4 p-4 rounded-xl border border-rose-200 bg-rose-50 cursor-pointer hover:bg-rose-100 transition"
  onclick='graphVarietyKgs(<?= json_encode($varities); ?>, <?= json_encode($kgs); ?>)'
>
  <div class="p-2 rounded-lg bg-white shadow">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
         stroke="#3b82f6" stroke-width="2">
      <path d="M3 3v18h18"></path>
      <rect x="7" y="12" width="3" height="6"></rect>
      <rect x="12" y="9" width="3" height="9"></rect>
      <rect x="17" y="6" width="3" height="12"></rect>
    </svg>
  </div>

  <div>
    <div class="text-sm text-gray-500 font-medium">Top Varieties</div>
    <div class="text-lg font-semibold text-gray-800">
      <?= $TopVariety1 ?>, <?= $TopVariety2 ?>, <?= $TopVariety3 ?>
    </div>
    <div class="text-xs text-gray-400 mt-1">Based on Total Kgs</div>
  </div>
</div>


            </div>

<!-- /* remove2 end */            -->
<?php } ?>


            <div class="row">
                <?php if(0){ ?>
                <div class="gate gate-entry gate-left">
                    <img class="gate-icon" src="/Images/greenvector.svg" />
                </div>
                <?php } ?>
                <div class="col-12 summary-panel mt-2">





        <div class="horizontal-scroll">
    <div id="divStoreLayout">
        <div class="table-wrapper-modern">
            <div class="table-row-wrapper">
                <div class="rack-layout-header">

                        <div class="filter-label">Rack Layout</div>

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
                                <span class="legend-box rack-walkpath-modern" style="width: 35px;height: 8px !important;border-radius: none !important;border-radius: unset;">
                                </span><span class="legend-box rack-walkpath-modern" style="width: 35px;height: 8px !important;border-radius: none !important;border-radius: unset;background: #ffa50026;">
                                </span>
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

    // -----------------------------------------------------
    // DYNAMIC DETECTION:
    // A ROW IS WALK-PATH IF *ALL* CELLS ARE NOT RACKS
    // -----------------------------------------------------
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
            echo "
                <td >
                    <div class='walk-path-horizontal {$oddEvenC}'></div>
                </td>";
            continue;
        }

        // -----------------------------------------------------
        // CASE 2: RACK
        // -----------------------------------------------------
        if ($cell->RackType == "1") {
            $count = (int)$cell->boxcount;
            $isFilled     = ($cell->RackStatus == "1" && $count>0);
            $rackClass    = $isFilled ? "filled" : "";
            $fillClass    = $isFilled ? "divFilled" : "divUnFilled";
            $tooltipPos   = ($r == 0) ? "tooltip-down" : "tooltip-up";
            $tooltipText  = $isFilled ? "Filled" : "Empty";
            $icon         = "box.svg";

            // Box icon logic

            if ($count == 1) $icon = "singlebox.svg";
            else if ($count == 2) $icon = "doblebox.svg";
            else if ($count == 3) $icon = "box.svg";
            else if ($count > 3) $icon = "threesome.svg";

            // Rack Label + Suffix
            $label = $cell->Name;
            $suffix = "";
            if (preg_match('/[A-Z]$/', $cell->Name, $m)) {
                $suffix = "<span>{$m[0]}</span>";
                $label = preg_replace('/[A-Z]$/', "", $cell->Name);
            }

            echo "
                <td class='rack-cell {$fillClass}'>
                    <div class='form-check-input rackno rack-box {$rackClass} {$tooltipPos}'
                         data-rackid='{$cell->RackID}'>

                        <div class='rack-label'>{$label} {$suffix}</div>

                        <div class='rack-icon'>
                            <img src='/Images/{$icon}' " . ($isFilled ? "" : "style='display:none'") . ">
                        </div>

                        <div class='rack-tooltip'>
                            <div class='rack-tooltip-content'>Name: {$cell->Name}</div>
                            <div class='rack-tooltip-content'>Status: {$tooltipText}</div>
                            <div class='rack-tooltip-content'>Boxes: {$cell->boxcount}</div>
                        </div>

                    </div>
                </td>";
        }


        // -----------------------------------------------------
        // CASE 3: Empty non-rack (rare)
        // -----------------------------------------------------
        else {

    // ------------------------------------------
    // MERGE VERTICAL WALK-PATH CELLS (left → right)
    // ------------------------------------------
    $colspan = 1;
    $next = $c + 1;

    while ($next < $cols) {

        $nextCell = $rowData[$next];

        // Stop merging if next is a rack
        if ($nextCell->RackType == "1") {
            break;
        }

        // Stop merging if next row is a horizontal walk-path row
        if ($isWalkPathRow) {
            break;
        }

        // Otherwise, merge it
        $colspan++;
        $next++;
    }

    // Output merged vertical walk-path
    echo "<td colspan='{$colspan}'><div class='walk-path-vertical {$oddEvenR}'></div></td>";

    // Skip merged columns
    $c += ($colspan - 1);
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
            <?php if(0){ ?>
            <div class="gate gate-exit gate-right">
                <img class="gate-icon" src="/Images/redvector.svg" />
            </div>
            <?php } ?>
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

    function customeformat($text){
        //$text = ucfirst(substr($text, 0, 1));
        return $text;
    }

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
<style>/* RACK STRUCTURE */
/* ===== RACK ===== */
.rack {
 display: grid;
    grid-template-columns: 30px 1fr 30px;
    width: 100%;
    margin-top: 15px;
    /*height: 600px;
    overflow: scroll;*/
}

.post {
  background: #aba9a9;
  border: 2px solid #4444443b;
  border-radius: 4px;
  position: relative;
}

.post::before {
  content: "";
    position: absolute;
    top: 10px;
    bottom: 10px;
    left: 10px;
    width: 6px;
    background-image: radial-gradient(circle, #fff 2px, transparent 2px);
    background-size: 6px 16px;
    background-repeat: repeat-y;
    opacity: .75;
}

/* ===== SHELVES ===== */
.shelf-area {
  display: flex;
  flex-direction: column;
  gap: 60px;
  padding: 20px 0;
  margin-top: 60px;

}

.shelf {
  position: relative;
  height: 320px;
}

/* ===== BEAMS ===== */
.beam-wrapper {
  position: absolute;
  bottom: 0;
  left: -20px;
  width: calc(100% + 40px);
  height: 45px;
  display: flex;
  align-items: center;
  z-index: 3;
}

.beam-main {
  flex: 1;
  height: 22px;
  background: #b40000;
  box-shadow: inset 0 -2px 0 rgba(0,0,0,.25);
}

.beam-end {
  width: 18px;
  height: 40px;
  background: #b40000;
}

/* ===== BOX AREA ===== */
.box-area {
  position: absolute;
  bottom: 32px;
  left: 15px;
  display: flex;
  gap: 35px;
  z-index: 2;
}

/* ===== PALLET ===== */
/* ===== PALLET (MATCH 2nd IMAGE) ===== */
.pallet {
     width: 238px;
    height: 54px;
    position: relative;
    margin: 6px auto 0;
    left: -4px;
}

/* TOP PLANKS */
.pallet-top {
  display: flex;
  gap: 6px;
}

.pallet-plank {
  flex: 1;
    height: 12px;
    background: #ffc270c4;
    border: 1px solid #9c6b30;
}

/* BOTTOM BLOCKS */
.pallet-bottom {
  position: absolute;
    bottom: 0;
    width: 100%;
    display: flex;
    justify-content: space-between;
    /* padding: 0 10px; */
    border-top: 10px solid #efbe33ed;
    border-bottom: 10px solid #efbe33ed;
    background: #6A4E29;
}

.pallet-block {
      width: 25px;
    height: 16px;
    background: #efbe33ed;
    border-top: 1px solid #b18855;
    border-bottom: 1px solid #b18855;
}

/* ===== 3D BOX ===== */
.box-3d-wrapper {
  width: 229px;
  height: 240px;
    position: relative;
    perspective: 1100px;
}

.box-3d {
  position: relative;
  transform-style: preserve-3d;
  transform: rotateX(2deg);
}

.box-top {
  position: absolute;
  width: 250px;
  height: 90px;
  background: linear-gradient(135deg,#e2c29a,#d1a974);
  border: 2px solid #b18855;
  transform: rotateX(72deg) translateY(-90px);
}

.box-front {
  width: 250px;
  height: 280px;
  background: linear-gradient(135deg,#e2c29a,#d1a974);
  border: 2px solid #b18855;
  padding: 10px;
  box-shadow: 0 5px 7px rgba(0,0,0,.35);
  font-family: Inter, Arial, sans-serif;
}

/* ===== LABEL ===== */
.header {
  background: #cfefff;
  padding: 6px;
  border-radius: 6px;
  position: relative;
}

.shift-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  background: #2563eb;
  color: #fff;
  font-size: 10px;
  padding: 4px 8px;
  border-radius: 12px;
  font-weight: 600;
}

.grade-value {
  font-size: 26px;
  font-weight: 800;
  border: 2px solid #000;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px;
}

.barcode {
  width: 60px;
  height: 28px;
  background: repeating-linear-gradient(
    to right,#000 0 2px,transparent 2px 4px
  );
}

/* ===== INFO ===== */
.info-grid {
  margin-top: 6px;
}

.info-item {
  font-size: 12px;
  display: flex;
  justify-content: space-between;
  border-bottom: 1px dashed #ccc;
}

</style>
<style>
.totals-bar {
    display: flex;
    gap: 1rem;

    justify-content: start;
    flex-wrap: wrap;
}

.total-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    border: 1px solid #e6e6e6;
}

.total-box i {
    font-size: 18px;
    color: #4a6cf7; /* blue icon look from screenshot */
}

.total-box .label {
    font-weight: 500;
    color: #555;
}

.total-box .value {
    font-weight: 700;
    color: #000;
}

.box-3d-wrapper {
        /* width: 250px; */
    height: 240px;
    position: relative;
    perspective: 1100px;
}

.box-3d {
        width: 229px;
    height: 290px;
    position: relative;
    transform-style: preserve-3d;
    transform: rotateX(2deg) rotateY(0deg);
    background: linear-gradient(135deg, #e2c29a, #d1a974);
}

/* FRONT FACE */
.box-front {
    width: 210px;
    height: 294px;
    /*background: #f7f7f7;
    border: 2px solid #c4c4c4;*/
    MARGIN: 10PX;
    PADDING: 10PX;
    transform: translateZ(1px);
    font-family: "Inter", "Segoe UI", sans-serif;
    /* display: flex; */
    flex-direction: column;
    /* gap: 6px;
    box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.08);*/
}
/* TOP FACE */
.box-top {
     position: absolute;
    width: 230px;
    height: 110px;
    /* background: #e0e0e0; */
    /* border: 2px solid #c4c4c4; */
    transform-origin: top center;
    transform: rotateX(72deg) translateY(-111px);
    background: linear-gradient(135deg, #e2c29a, #d1a974);
    border: 2px solid #b18855;
}

/* REMOVE SIDE */
.box-side {
    display: none !important;
}

/* SHADOW */
.box-3d-shadow {
    position: absolute;
    bottom: -25px;
    left: 30px;
    width: 85%;
    height: 28px;
    background: rgba(0,0,0,0.25);
    filter: blur(20px);
}

/* LABEL TYPOGRAPHY */
.species-text {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin-top: 6px;
    text-transform: uppercase;
}

.variety-text {
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    text-transform: uppercase;
}

.grade-value {
    font-size: 26px;
    font-weight: 800;
    margin: 0;
    color: #0f172a;
    letter-spacing: 1px;
    padding: 0px 3px 3px 3px;
    border: 2px solid black;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.barcode {
    width: 60px;
    height: 28px;
    background: repeating-linear-gradient(
        to right,
        #000 0 2px,
        transparent 2px 4px
    );
    margin-left: 6px;
}


/* INFO GRID */
.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 4px;
    margin-top: 4px;
}

.info-item {
    font-size: 12px;
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dashed #d2d2d2;
    padding-bottom: 0px;
}

.info-item span {
    font-weight: 600;
    color: #334155;
}

/* SHIFT BADGE (modern pill style) */
.shift-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #2563eb;
    color: #fff;
    padding: 4px 8px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 12px;
    text-transform: uppercase;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.AShift{
    background: #fff;
}

.BShift{
    background: rgb(167 214 215);
}

.CShift{
    background: #F9ECB9;
}

.DShift{
    background: #zzz;
}

</style>
<div class="total-box-container">
   <div class="totals-bar">
       <div class="total-box">
           <i class="fa-solid fa-boxes-stacked"></i>
           <span class="label">TOTAL MCS:</span>
           <span class="value"><?= $Totals->TotalMcs ?></span>
       </div>

       <div class="total-box">
           <i class="fa-solid fa-bag-shopping"></i>
           <span class="label">TOTAL LOOSE:</span>
           <span class="value"><?= $Totals->TotalLooseBags ?></span>
       </div>

       <div class="total-box">
           <i class="fa-solid fa-weight-hanging"></i>
           <span class="label">TOTAL KGS:</span>
           <span class="value"><?= $Totals->TotalKgs ?></span>
       </div>
   </div>
</div>
<div class="pallet-area">
    <!-- TOTALS -->




    <div class="rack">

  <div class="post"></div>

  <div class="shelf-area">

    <?php
      $perShelf = 3;
      $shelves = array_chunk($RackList, $perShelf);
            if (count($shelves) < 2) {
                $shelves[] = [];
            }
            $i=0;
            $checkShelvesCount = count($shelves) -1;
    foreach ($shelves as $index => $shelfGroup) { ?>

      <div class="shelf">

        <?php if($i == $checkShelvesCount ){ ?>
        <!-- BEAM -->
        <div class="beam-wrapper" style="width: calc(100% + 86px);left: -42px;height: 30p;">
          <div class="beam-end" style="
    width: 50px;
    height: 86px;border-radius: 8px;
"></div>
          <div class="beam-main"></div>
          <div class="beam-end" style="
    width: 50px;
    height: 86px;border-radius: 8px;
"></div>
        </div>

        <?php }else{ ?>
<div class="beam-wrapper">
          <div class="beam-end"></div>
          <div class="beam-main"></div>
          <div class="beam-end"></div>
        </div>
        <?php } ?>

        <!-- BOXES -->
        <div class="box-area">
          <?php foreach ($shelfGroup as $item) { ?>

            <div>
              <div class="box-3d-wrapper" <?php if($i == $checkShelvesCount ){ echo 'style="height:250px"'; }else{ echo 'style="height:278px"'; } ?>>
                <div class="box-3d">

                  <div class="box-top"></div>

                  <div class="box-front">
                    <div class="header <?= str_replace(" ", "", $item->Shift) ?>">
                      <div class="shift-badge"><?= $item->Shift ?></div>
                      <div class="species-text"><?= $item->Species ?> <?= customeformat($item->TypeProduct) ?></div>
                      <div class="variety-text"><?= $item->Variety ?></div>
                      <div class="grade-value">
                        <?= $item->Grade ?>
                        <div class="barcode"></div>
                      </div>
                    </div>

                    <div class="info-grid">
                      <div class="info-item"><span>PO</span><?= $item->PoNo ?></div>
                      <div class="info-item"><span>Freezing</span><?= $item->FreezingType ?></div>
                      <div class="info-item"><span>MCS</span><?= $item->Mcs ?></div>
                      <div class="info-item"><span>Loose</span><?= $item->Loose ?></div>
                      <div class="info-item"><span>Packing</span><?= $item->PackingType ?></div>
                      <div class="info-item"><span>Style</span><?= $item->PackingStyle ?></div>
                      <div class="info-item"><span>Kgs</span><?= $item->Kgs ?></div>
                    </div>
                  </div>

                </div>
              </div>

              <!-- PALLET -->
              <?php if($i == $checkShelvesCount ){ ?>
              <div class="pallet">


                    <div class="pallet-bottom">
                      <div class="pallet-block"></div>
                      <div class="pallet-block"></div>
                      <div class="pallet-block"></div>
                    </div>
                  </div>
                <?php } ?>
            </div>

          <?php } ?>
        </div>

      </div>

    <?php $i++; } ?>

  </div>

  <div class="post"></div>

</div>
<!--div style="

    position: relative;
    width: calc(100% + 27px);
    height: 27px;
    background: #ccc;
    top: -4px;
    left: -13px;
    z-index: 9;
"></div-->


</div>        <?php
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
