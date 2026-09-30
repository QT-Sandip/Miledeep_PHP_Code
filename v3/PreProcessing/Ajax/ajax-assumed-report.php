<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);





include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_ASSUMED_REPORT;

$bodyContent = array(

    "ReceivingPlantID" => trim($_POST["receivingPlantId"]),
    "ArrivalPlant" => trim($_POST["receivingPlantId"]),
    "ArrivalDate" => trim($_POST["arrivalDate"]),
    "TrackID" => trim($_POST["trackID"]),
    "Species" => trim($_POST["species"]),
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
// var_dump($resList);


if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;
    if (!isset($error) || empty($error)) {
        $ArrivalData = $resList->ArrivalData;
        $Grading = $resList->Grading;
        $TotalPurchaseWeight = $resList->TotalPurchaseWeight;
        $CountGroupWeight = $resList->CountGroupWeight;
        $TotalPurchaseAmount = $resList->TotalPurchaseAmount;
        $TotalGradingWeight = $resList->TotalGradingWeight;
        $TotalYield = $resList->TotalYield;

        if (class_exists('NumberFormatter')) {
            $formatter = new NumberFormatter('en_IN', NumberFormatter::DECIMAL);

            $formatter->setAttribute(
                NumberFormatter::MIN_FRACTION_DIGITS,
                3
            );
            $formatter->setAttribute(
                NumberFormatter::MAX_FRACTION_DIGITS,
                3
            );

            $TotalPurchaseAmount = $formatter->format((float) str_replace(',', '',$TotalPurchaseAmount));
        }
?>
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">

                <tr>
                    <th>SNo</th>
                    <th>Count</th>
                    <th>Weight</th>
                    <th>Purchase count</th>
                    <th>Purchase Weight</th>
                    <th>Purchase Rate</th>
                    <th style="min-width: 150px">Purchase Total Value</th>
                    <th></th>
                    <th>SNo</th>
                    <th>HLCount</th>
                    <th>Packing Grade</th>
                    <th>Variety Name</th>
                    <th>Treatment</th>
                    <th>Weight</th>
                    <th>Rate</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $maxLength = max(count($ArrivalData), count($Grading));

                for ($i = 0; $i < $maxLength; $i++) {
                    $arrivalItem = isset($ArrivalData[$i]) ? $ArrivalData[$i] : null;
                    $gradingItem = isset($Grading[$i]) ? $Grading[$i] : null;
                ?>
                    <tr class="section-data">
                        <td><?php echo $arrivalItem ? $arrivalItem->SNO : ''; ?></td>
                        <td><?php echo $arrivalItem ? $arrivalItem->BaseCount : ''; ?></td>
                        <td class="tdBaseWeight"><?php echo $arrivalItem ? $arrivalItem->BaseCountWeight : ''; ?></td>
                        <td><?php echo $arrivalItem ? $arrivalItem->Count : ''; ?></td>
                        <td class="tdPurchaseWeight"><?php echo $arrivalItem ? $arrivalItem->Weight : ''; ?></td>
                        <td><?php echo $arrivalItem ? $arrivalItem->Rate : ''; ?></td>
                        <td class="tdPurchaseAmount"><?php echo $arrivalItem ? $arrivalItem->Amount : ''; ?></td>
                        <td></td>
                        <td><?php echo $gradingItem ? $gradingItem->SNO : ''; ?></td>
                        <td><?php echo $gradingItem ? $gradingItem->HLCount : ''; ?></td>
                        <td><?php echo $gradingItem ? $gradingItem->PackingGrade : ''; ?></td>
                        <td><?php echo $gradingItem ? $gradingItem->ShortName : ''; ?></td>
                        <td><?php echo $gradingItem ? $gradingItem->Treatment : ''; ?></td>
                        <td class="tdHLWeight"><?php echo $gradingItem ? $gradingItem->Weight : ''; ?></td>
                        <td></td>
                        <td></td>

                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>

                <!-- <tr class="section-data" style="font-weight: bold;">
                    <td></td>
                    <td>Total Hon Qty</td>
                    <td id="totalBaseWeight" style="min-width: 100px;max-width: 100px;"><?php echo $CountGroupWeight ?></td>
                    <td></td>
                    <td id="totalPurchaseWeight" style="min-width: 100px;max-width: 100px;"><?php echo $TotalPurchaseWeight ?></td>
                    <td></td>
                    <td id="totalPurchaseAmount" style="min-width: 100px;max-width: 100px;"><?php echo $TotalPurchaseAmount ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Total HL Weight</td>
                    <td id="totalHLWeight" style="min-width: 100px;max-width: 100px;"><?php echo $TotalGradingWeight ?></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="section-data" style="font-weight: bold;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Yield</td>
                    <td id="totalYield" style="min-width: 100px;max-width: 100px; font-weight:900;"><?php echo $TotalYield . ' %' ?></td>
                    <td></td>
                    <td></td>
                </tr> -->

                <style>
                    .footer-bold {
                        font-family: 'Arial Black', Arial, sans-serif;
                        font-weight: 900;
                        color: #000;
                        min-width: 100px;
                        max-width: 100px;
                        text-align: center;
                        /* optional */
                    }
                </style>

                <tr class="section-data">
                    <td></td>
                    <td class="footer-bold">Total Hon Qty</td>
                    <td id="totalBaseWeight" class="footer-bold"><?php echo $CountGroupWeight ?></td>
                    <td></td>
                    <td id="totalPurchaseWeight" class="footer-bold"><?php echo $TotalPurchaseWeight ?></td>
                    <td></td>
                    <td id="totalPurchaseAmount" class="footer-bold"><?php echo $TotalPurchaseAmount ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="footer-bold">Total HL Weight</td>
                    <td id="totalHLWeight" class="footer-bold"><?php echo $TotalGradingWeight ?></td>
                    <td></td>
                    <td></td>
                </tr>

                <tr class="section-data">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="footer-bold">Yield</td>
                    <td id="totalYield" class="footer-bold"><?php echo $TotalYield . ' %' ?></td>
                    <td></td>
                    <td></td>
                </tr>



            </tfoot>

        </table>

    <?php  } ?>

    <!-- <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo count($List) ?> entries</div>
    <?php $activepagestyle = "border:2px solid #74788d" ?>
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
    </div> -->
<?php
} else {
    echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
}

?>