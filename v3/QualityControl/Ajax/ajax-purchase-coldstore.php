<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


function fmt3($val)
{
    if ($val === '' || $val === null || $val === '-' || !is_numeric($val)) {
        return $val;
    }
    return number_format((float) $val, 3, '.', '');
}

function formatAbStatus($val)
{
    if ($val == "AB+")
        return "POSITIVE";
    if ($val == "AB-")
        return "NEGATIVE";
    return $val;
}



$api = API_PURCHASE_COLDSTORE_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    include_once 'PurchaseColdStoreReportExcel.php';
    exit;
}


$methodType = $_POST["methodType"];
// $methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';



if ($methodType == "GET") {

    $PageNo = trim($_POST["PageNo"]);
    $bodyContent = array(
        'FromDate' => trim($_POST["fromDate"]),
        'ToDate' => trim($_POST["toDate"]),
        'LotIds' => $_POST["lotIds"],
        'PageNo' => $PageNo,
        'hdnTotalRecords' => trim($_POST["hdnTotalRecords"])
    );

    // echo print_r($bodyContent);exit;


    $resList = CallAPI($api, "GET", "", $bodyContent);

    // echo '<pre>'; print_r($resList);exit;

} else if ($methodType == "GET_BATCH") {

    $bodyContent = array(
        'FromDate' => trim($_POST["fromDate"]),
        'ToDate' => trim($_POST["toDate"]),
        'Type' => $methodType,
    );

    $resList = CallAPI($api, "POST", "", $bodyContent);
    
    // print_r($resList);die;
    header('Content-Type: application/json');
    echo json_encode($resList);
    exit;
}

// elseif (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
// include_once  'PurchaseColdStoreReportExcel.php';
// }


if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->PurchaseColdStoreReportData;
        // echo json_encode($List);
        // exit;
        // $totalRows = $resList->totalRows;
        // $PageNo = !empty($_POST['pageNo'])?$_POST['pageNo']:1;

        $PageNo = !empty($_POST['PageNo']) ? $_POST['PageNo'] : 1;
        $RecordsPerPage = 50;
        $totalRows = $resList->totalRows;

        // $pageStart = ($PageNo - 1) * $RecordsPerPage + 1;
        // $pageEnd = ($pageStart - 1) + count($List);

        $RecordsPerPage = 50;
        $PageCount = (int) ceil($totalRows / $RecordsPerPage);
        if ($PageNo < 1)
            $PageNo = 1;
        if ($PageNo > $PageCount)
            $PageNo = $PageCount;

        $pageStart = ($PageNo - 1) * $RecordsPerPage + 1;
        $pageEnd = min($pageStart + count($List) - 1, $totalRows);

        $serial = $pageStart;

        ?>
        <?php if (!isset($error) || empty($error)) { 
            $BatchCode = $HoToHlYield = $HoToFinishedYield = '';            
            foreach ($List as $item) {
                $BatchCode = $item->BatchCode;
                $HoToHlYield = $item->HoToHlYield;
                $HoToFinishedYield = $item->HoToFinishedYield;
            }?>
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <td class="freezeHLCnt" colspan="5">HO TO HL YIELD % : <?php echo $HoToHlYield;?></td>
                    </tr>
                    <tr>
                        <td class="freezeHLCnt"  colspan="5">HO TO FINISHED YIELD % : <?php echo $HoToFinishedYield;?></td>
                    </tr>
                    <tr>
                        <th rowspan="2">S.No</th>
                        <th rowspan="2">Batch Code</th>

                        <th colspan="3">Purchase</th>
                        <th colspan="3">Harvest</th>
                        <th colspan="6">Receiving</th>
                        <th colspan="4">Deheading</th>
                        <th colspan="4">Grading</th>
                        <th colspan="6">Value Addition</th>
                        <th colspan="6">Soaking</th>
                        <th colspan="6">Freezing</th>
                        <th colspan="3">Cold Store</th>
                        <th colspan="3">Shipment</th>
                    </tr>


                    <tr>
                        <th>Booking Date</th>
                        <th>Quantity</th>
                        <th>Count</th>

                        <th>Harvest Date</th>
                        <th>Quantity</th>
                        <th>Count</th>

                        <th class="frezeReceivingDate">Receiving Date</th>
                        <th>Quantity</th>
                        <th>Count</th>
                        <th>Quantity Difference</th>
                        <th>Count Difference</th>
                        <th style="min-width: 100px;">Antibiotic Test</th>

                        <th>Head On Qty</th>
                        <th>Head Less Qty</th>
                        <th>HL Count</th>
                        <th>Count LBS</th>
                        <th>Deheading Yield %</th>

                        <th>No of Grades</th>
                        <th>Grade Count</th>
                        <th>Variety</th>
                        <th>Grade Quantity</th>

                        <th style="min-width: 120px;">Value Addition Date</th>
                        <th>VA Variety</th>
                        <th>Head less Weight</th>
                        <th>After VA Weight</th>
                        <th>VA Yield %</th>
                        <th>Floor Balance</th>

                        <th style="min-width: 120px;">Soaking Date</th>
                        <th>Soak In Qty</th>
                        <!-- <th>Soak Out Qty</th> -->
                        <th>Source Count</th>
                        <th>Soak Out Count</th>
                        <th>Soaking Yield %</th>

                        
                        <th>Freezing In Qty</th>
                        <th style="min-width: 120px;">Freezing Date</th>
                        <th style="min-width: 250px;">Packing Style</th>

                        <th>No of Cases</th>
                        <th>Packing Type</th>
                        <th>Production Qty</th>
                        <th>Freezing Yield %</th>

                        <th style="min-width: 80px;">Out Date</th>
                        <th>Inward Mcs</th>
                        <th> Outward Mcs</th>
                        <th>Pallet No</th>

                        <th style="min-width: 00px;">Shipment Date</th>
                        <th>Shipped Out Kgs</th>
                        <th>Remaining Stock Kgs</th>
                    </tr>

                </thead>

                <tbody>
                    <?php
                    foreach ($List as $item) {

                        $recvQty = explode(',', $item->ReceivingQty);
                        $recvCnt = explode(',', $item->ReceivingCount);
                        $qtyDiff = explode(',', $item->QuantityDifference);
                        $cntDiff = explode(',', $item->CountDifference);

                        $honWt = explode(',', $item->HonWeight);
                        $hlWt = explode(',', $item->WtAfterDH);
                        $dhCnt = explode(',', $item->CountAfterDH);
                        $dYields = explode(',', $item->Yield);
                        $ctHLCount = explode(',', $item->ctHLCount);

                        $grade = explode(',', $item->PackingGrade);
                        $gCnt = explode(',', $item->HLCount);
                        $gWt = explode(',', $item->HLWeight);
                        

                        $var = explode(',', $item->Variety);//echo '<pre>';print_r($var);die;
                        $vaWt = explode(',', $item->WtAftrVA);
                        $vaShortName = explode(',', $item->VaShortName);
                        $sentWt = explode(',', $item->SentWeight);
                        $gYields = explode(',', $item->Yields);
                        $FloorBalances = explode(',', $item->FloorBalance);

                        $sinQ = explode(',', $item->SoakInQty);
                        $soutQ = explode(',', $item->SoakOutQty);
                        $sinC = explode(',', $item->SoakInCount);
                        $soutC = explode(',', $item->SoakOutCount);

                        $prodWt = explode(',', $item->ProductionWeight);
                        $stkIn = explode(',', $item->StockInKgs);
                        $stkOut = explode(',', $item->StockOutKgs);
                        $ship = explode(',', $item->ShipmentOutKgs);
                        $bal = explode(',', $item->BalanceKgs);

                        // $AbStatuss = explode(',', $item->AbStatus);
                        $ValueAdditionDates = explode(',', $item->ValueAdditionDate);
                        $SoakingDates = explode(',', $item->SoakingDate);
                        $FreezingDates = explode(',', $item->FreezingDate);
                        $PackingStyles = explode(',', $item->PackingStyle);
                        
                        $PackingTypeIDs = explode(',', $item->PackingTypeID);
                        $RackNames = explode(',', $item->RackName);
                        $OutDates = explode('|', $item->OutDate);
                        $ShipmentDates = explode('|', $item->ShipmentDate);
                        $NoofCaseses = explode(',', $item->NoofCases);
                        $FreezingYields = explode(',', $item->FreezingYield);
                        $SoakingYields = explode(',', $item->SoakingYield);

                        $FreezingInArr = explode('|', $item->FreezingIn);


                        $rows = count($gCnt);

                        for ($i = 0; $i < $rows; $i++) {

                            echo "<tr>";

                            if ($i == 0) {
                                echo "<td>" . $serial++ . "</td>";
                                echo "<td>" . $item->BatchCode . "</td>";

                                echo "<td>" . (!empty($item->BookingDate) ? date('d-m-y', strtotime($item->BookingDate)) : '') . "</td>";
                                echo "<td>" . fmt3($item->BookingWeight) . "</td>";
                                echo "<td>" . $item->BookingCount . "</td>";

                                echo "<td>" . (!empty($item->HarvestDate) ? date('d-m-y', strtotime($item->HarvestDate)) : '') . "</td>";
                                echo "<td>";
                                $hw = explode(',', $item->HarvestWeight);
                                foreach ($hw as $v) {
                                    echo fmt3(trim($v)) . "<br>";
                                }
                                echo "</td>";

                                /* Harvest Count – multi row */
                                echo "<td>";
                                $hc = explode(',', $item->HarvestCount);
                                foreach ($hc as $v) {
                                    echo fmt3(trim($v)) . "<br>";
                                }
                                echo "</td>";

                                echo "<td>" . (!empty($item->ArrivalDate) ? date('d-m-y', strtotime($item->ArrivalDate)) : '') . "</td>";
                            } else {
                                echo str_repeat("<td></td>", 9);
                            }

                            echo "<td>" . (isset($recvQty[$i]) ? fmt3($recvQty[$i]) : '') . "</td>";
                            echo "<td>" . (isset($recvCnt[$i]) ? fmt3($recvCnt[$i]) : '') . "</td>";
                            echo "<td>" . (isset($qtyDiff[$i]) ? $qtyDiff[$i] : '') . "</td>";
                            echo "<td>" . (isset($cntDiff[$i]) ? $cntDiff[$i] : '') . "</td>";
                            // echo "<td>" . (isset($AbStatuss[$i]) ? $AbStatuss[$i] : '')  . "</td>";
                            // echo "<td>" . (isset($AbStatuss[$i]) ? formatAbStatus($AbStatuss[$i]) : '')  . "</td>";
                           // echo "<td>" . (!empty($item->AbStatus) ? formatAbStatus($item->AbStatus) : '') . "</td>";
                            echo "<td>";
                            if ($i == 0) {
                                echo (!empty($item->AbStatus) ? formatAbStatus($item->AbStatus) : '-');
                            }
                            echo "</td>";

                            echo "<td>" . (isset($honWt[$i]) ? fmt3($honWt[$i]) : '') . "</td>";
                            echo "<td>" . (isset($hlWt[$i]) ? fmt3($hlWt[$i]) : '') . "</td>";
                            echo "<td>" . (isset($dhCnt[$i]) ? fmt3($dhCnt[$i]) : '') . "</td>";
                            echo "<td> " . (isset($ctHLCount[$i]) ? ($ctHLCount[$i]) : '') . "</td>";
                            echo "<td> " . (isset($dYields[$i]) ? ($dYields[$i]) : '') . "</td>";

                            echo "<td>" . (isset($grade[$i]) ? $grade[$i] : '') . "</td>";
                            echo "<td>" . (isset($gCnt[$i]) ? fmt3($gCnt[$i]) : '') . "</td>";
                            echo "<td>";
                            if (!empty($var[$i])) {
                                $cases = explode('|', $var[$i]);
                                foreach ($cases as $v) {
                                    echo trim($v) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>" . (isset($gWt[$i]) ? fmt3($gWt[$i]) : '') . "</td>";

                            echo "<td>";
                            if (!empty($ValueAdditionDates[$i])) {
                                $cases = explode('|', $ValueAdditionDates[$i]);
                                foreach ($cases as $v) {
                                    $v = trim($v);
                                    if ($v != '' && $v != '-' && $v != '0000-00-00') {
                                        echo date('d-m-y', strtotime($v)) . "<br>";
                                    } else {
                                        echo "-";
                                    }
                                }
                            }
                            echo "</td>";
                            echo "<td>";
                            if (!empty($vaShortName[$i])) {
                                $cases = explode('|', $vaShortName[$i]);
                                foreach ($cases as $v) {
                                    echo trim($v) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>";
                            if (!empty($sentWt[$i])) {
                                $cases = explode('|', $sentWt[$i]);
                                foreach ($cases as $v) {
                                    echo fmt3(trim($v)) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>";
                            if (!empty($vaWt[$i])) {
                                $cases = explode('|', $vaWt[$i]);
                                foreach ($cases as $v) {
                                    echo fmt3(trim($v)) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>" . (isset($gYields[$i]) ? (($gYields[$i])) : '') . "</td>";
                            echo "<td>" . (isset($FloorBalances[$i]) ? (fmt3($FloorBalances[$i])) : '') . "</td>";

                            echo "<td>";
                            if (!empty($SoakingDates[$i])) {
                                $cases = explode('|', $SoakingDates[$i]);
                                foreach ($cases as $v) {
                                    $v = trim($v);
                                    if ($v != '' && $v != '-' && $v != '0000-00-00') {
                                        echo date('d-m-y', strtotime($v)) . "<br>";
                                    } else {
                                        echo "-";
                                    }
                                }
                            }
                            echo "</td>";
                            echo "<td>";
                            if (!empty($sinQ[$i])) {
                                $cases = explode('|', $sinQ[$i]);
                                foreach ($cases as $v) {
                                    echo fmt3(trim($v)) . "<br>";
                                }
                            }
                            echo "</td>";
                            /* echo "<td>";
                            if (!empty($soutQ[$i])) {
                                $cases = explode('|', $soutQ[$i]);
                                foreach ($cases as $v) {
                                    echo fmt3(trim($v)) . "<br>";
                                }
                            }
                            echo "</td>"; */
                            echo "<td>";
                            if (!empty($sinC[$i])) {
                                $cases = explode('|', $sinC[$i]);
                                foreach ($cases as $v) {
                                    echo trim($v) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>";
                            if (!empty($soutC[$i])) {
                                $cases = explode('|', $soutC[$i]);
                                foreach ($cases as $v) {
                                    echo trim($v) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>" . (isset($SoakingYields[$i]) ? ($SoakingYields[$i]) : '') . "</td>";

                            echo "<td>";
                            if (!empty($FreezingInArr[$i])) {
                                $FreezingInQty = explode(',', $FreezingInArr[$i]);
                                foreach ($FreezingInQty as $FInQty) {
                                    echo trim($FInQty) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>";
                            if (!empty($FreezingDates[$i])) {
                                $cases = explode('|', $FreezingDates[$i]);
                                foreach ($cases as $v) {
                                    $v = trim($v);
                                    if ($v != '' && $v != '-' && $v != '0000-00-00') {
                                        echo date('d-m-y', strtotime($v)) . "<br>";
                                    } else {
                                        echo "-";
                                    }
                                }
                            }
                            echo "</td>";

                            echo "<td>";
                            if (!empty($PackingStyles[$i])) {
                                $cases = explode('|', $PackingStyles[$i]);
                                foreach ($cases as $v) {
                                    echo trim($v) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>";
                            if (!empty($NoofCaseses[$i])) {
                                $cases = explode('|', $NoofCaseses[$i]);
                                foreach ($cases as $v) {
                                    echo fmt3(trim($v)) . "<br>";
                                }
                            }
                            echo "</td>";

                            echo "<td>";
                            if (!empty($PackingTypeIDs[$i])) {
                                $cases = explode('|', $PackingTypeIDs[$i]);
                                foreach ($cases as $v) {
                                    echo trim($v) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>";
                            if (!empty($prodWt[$i])) {
                                $cases = explode('|', $prodWt[$i]);
                                foreach ($cases as $v) {
                                    echo fmt3(trim($v)) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "<td>" . (isset($FreezingYields[$i]) ? ($FreezingYields[$i]) : '') . "</td>";

                            echo "<td>";
                            if (!empty($OutDates[$i])) {
                                $cases = explode(',', $OutDates[$i]);
                                foreach ($cases as $v) {
                                    $v = trim($v);
                                    if ($v != '' && $v != '-' && $v != '0000-00-00') {
                                       // echo date('d-m-y', strtotime($v)) . "<br>";
                                       echo $v . "<br>";
                                    } else {
                                        echo "-";
                                    }
                                }
                            }
                            echo "</td>";
                           // echo "<td>" . (isset($stkIn[$i]) ? ($stkIn[$i]) : '') . "</td>";
                            echo "<td>";
                            if (!empty($stkIn[$i])) {
                                $InMcs = explode('|', $stkIn[$i]);
                                foreach ($InMcs as $In) {
                                    echo trim($In) . "<br>";
                                }
                            }
                            echo "</td>";

                           // echo "<td>" . (isset($stkOut[$i]) ? ($stkOut[$i]) : '') . "</td>";
                           echo "<td>";
                            if (!empty($stkOut[$i])) {
                                $OutMcs = explode('|', $stkOut[$i]);
                                foreach ($OutMcs as $Out) {
                                    echo trim($Out) . "<br>";
                                }
                            }
                            echo "</td>";
                            /* echo "<td>";
                            if (!empty($RackNames[$i])) {
                                $cases = explode('|', $RackNames[$i]);
                                foreach ($cases as $v) {
                                    echo trim($v) . "<br>";
                                }
                            }
                            echo "</td>"; */
                            echo "<td>";
                            if (!empty($RackNames[$i])) {
                                $RackName = explode('|', $RackNames[$i]);
                                foreach ($RackName as $Rack) {
                                    echo trim($Rack) . "<br>";
                                }
                            }
                            echo "</td>";

                            echo "<td>";
                            if (!empty($ShipmentDates[$i])) {
                                $cases = explode(',', $ShipmentDates[$i]);
                                foreach ($cases as $v) {
                                    $v = trim($v);
                                    if ($v != '' && $v != '-' && $v != '0000-00-00') {
                                        echo date('d-m-y', strtotime($v)) . "<br>";
                                    } else {
                                        echo "-";
                                    }
                                }
                            }
                            echo "</td>";
                            echo "<td>" . (isset($ship[$i]) ? fmt3($ship[$i]) : '') . "</td>";
                            echo "<td>" . (isset($bal[$i]) ? fmt3($bal[$i]) : '') . "</td>";

                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>

            <!-- <div class="mt-3 mb-3">Showing <?php echo $pageStart; ?>  to <?php echo $item->SNO ?> out of <?php echo empty($_POST['loadAll']) ? $totalRows : $item->SNO ?> entries</div> -->
            <div class="mt-3 mb-3">
                Showing <?php echo $pageStart; ?> to <?php echo $pageEnd; ?> out of <?php echo $totalRows; ?> entries
            </div>

            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
        <div>
            <!-- <input type='hidden' id='hdnTotalRecords' value='<?php echo count($List) ?>' /> -->
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $totalRows ?>' />

            <ul class="pagination pagination-rounded <?php echo $PageCount < 30 ? 'justify-content-center' : ''; ?>"
                style="overflow-x: auto;margin: 0px auto;">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i) ? "active" : "";
                    $disable = ($PageNo == $i) ? "disabled" : "";
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" ' . $disable . ' data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
}
?>