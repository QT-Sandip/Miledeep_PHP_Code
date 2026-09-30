<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);
$api = API_DEHEADING_CHECK_LIST;

if ($methodType == 'GetBatchNumbers') {
    $bodyContent = array(
        "ArrivalDate" => trim($_POST['ArrivalDate']),
        "MethodType" => 'REPORT_BATCH_NUMBERS',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
        $ArrivalDate = trim($_GET["ArrivalDate"]);
        $bodyContent = array(
            "ArrivalDate" => $ArrivalDate,
            "MethodType" => 'GET_DEHEADING_LIST',
        );
        $border = "border='1'";
    } else if ($methodType == 'GET') {
        $bodyContent = array(
            "ArrivalDate" => trim($_POST['ArrivalDate']),
            "MethodType" => 'GET_DEHEADING_LIST',
        );
        $border = "";
    } else {
        $bodyContent = array();
    }

    $result = CallAPI($api, "GET", "", $bodyContent);

    if (isset($result) && !empty($result)) {
        $error = $result->Error;
        if (!isset($error) || empty($error)) {
            $List = $result->DeheadingData;

            $content = '<div class="card"><div class="card-body">';
            if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
                $content .= '
                <style>
                    table, th, td {
                        text-align: center;
                        vertical-align: middle;
                    }
                </style>';
            }

            $content .= '<table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered" style="text-align:center;">';

            if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
                $Totals = $result->Totals;
                $content .= '
                <tr><td colspan="22" align="center"><b>COASTAL AQUA PRIVATE LIMITED</b></td></tr>
                <tr><td colspan="22" align="center"><b>PATHARLAGADDA, KAKINADA</b></td></tr>
                <tr><td colspan="22" align="center"><b>DEHEADING CHECKLIST</b></td></tr>
                <tr><td colspan="22" style="text-align:right">Date: ' . date("d-m-Y", strtotime($_GET["ArrivalDate"])) . '</td></tr>';
            }

            // ---------- Table Header ----------
            $content .= '
            <thead class="thead-light">
                <tr>
                    <th style="text-align:center;">S.No</th>
                    <th style="text-align:center;">Batch Number</th>
                    <th style="text-align:center;">Variety</th>
                    <th style="text-align:center;">Count</th>
                    <th style="text-align:center;">HeadOn Weight</th>
                    <th style="text-align:center;">Receiving Time</th>
                    <th style="text-align:center;">General Appearance</th>
                    <th style="text-align:center;">Colour</th>
                    <th style="text-align:center;">Freshness</th>
                    <th style="text-align:center;">Odour</th>
                    <th style="text-align:center;">Texture</th>
                    <th style="text-align:center;">Temperature</th>
                    <th style="text-align:center;">Deterioration</th>
                    <th style="text-align:center;">Discolouration</th>
                    <th style="text-align:center;">BlackSpots</th>
                    <th style="text-align:center;">SoftShell</th>
                    <th style="text-align:center;">BrokenDamaged</th>
                    <th style="text-align:center;">ForeignMeterial</th>
                    <th style="text-align:center;">HL QTY</th>
                    <th style="text-align:center;">Hanging Meat</th>
                    <th style="text-align:center;">AttachedLegs</th>
                    <th style="text-align:center;">SendingTime</th>';
                    
            // Show "Verified By & Inspected By" only in LIST PAGE
            if (!(isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T")) {
                $content .= '
                 <th style="text-align:center;">Inspected By</th>
                    <th style="text-align:center;">Verified By</th>';
            }

            $content .= '</tr></thead><tbody>';

            // ---------- Table Body ----------
            $i = 1;
            foreach ($List as $item) {
                $content .= '
                <tr>
                    <td style="text-align:center;">' . $i . '</td>
                    <td style="text-align:center;">' . $item->BatchCodeName . '</td>
                    <td style="text-align:center;">' . $item->Variety . '</td>
                    <td style="text-align:center;">' . $item->Count . '</td>
                    <td style="text-align:center;">' . $item->ReceivingQuantity . '</td>
                    <td style="text-align:center;">' . $item->ReceivingTime . '</td>
                    <td style="text-align:center;">' . $item->GeneralAppearance . '</td>
                    <td style="text-align:center;">' . $item->Colour . '</td>
                    <td style="text-align:center;">' . $item->Freshness . '</td>
                    <td style="text-align:center;">' . $item->Odour . '</td>
                    <td style="text-align:center;">' . $item->Texture . '</td>
                    <td style="text-align:center;">' . $item->Temperature . '</td>
                    <td style="text-align:center;">' . $item->Deterioration . '</td>
                    <td style="text-align:center;">' . $item->Discolouration . '</td>
                    <td style="text-align:center;">' . $item->BlackSpots . '</td>
                    <td style="text-align:center;">' . $item->SoftShell . '</td>
                    <td style="text-align:center;">' . $item->BrokenDamaged . '</td>
                    <td style="text-align:center;">' . $item->ForeignMeterial . '</td>
                    <td style="text-align:center;">' . $item->SendingQuantity . '</td>
                    <td style="text-align:center;">' . $item->HangingMeat . '</td>
                    <td style="text-align:center;">' . $item->AttachedLegs . '</td>
                    <td style="text-align:center;">' . $item->SendingTime . '</td>';

                // Show only in LIST PAGE
                if (!(isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T")) {
                    $content .= '
                    <td style="text-align:center;">' . $item->InspectedBy . '</td>
                    <td style="text-align:center;">' . $item->VerifiedBy . '</td>
                    ';
                }

                $content .= '</tr>';
                $i++;
            }

            $content .= '</tbody></table></div></div>';
        }
    }

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $filename = 'Deheading_Check_List_Report.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=" . $filename);

    // Collect multiple names
    $verifiedByArr = [];
    $inspectedByArr = [];
    foreach ($List as $row) {
        if (!empty($row->VerifiedBy)) {
            $verifiedByArr[] = $row->VerifiedBy;
        }
        if (!empty($row->InspectedBy)) {
            $inspectedByArr[] = $row->InspectedBy;
        }
    }

    // Remove duplicates
    $verifiedByArr = array_unique($verifiedByArr);
    $inspectedByArr = array_unique($inspectedByArr);

    // Join with commas
    $verifiedByStr  = implode(", ", $verifiedByArr);
    $inspectedByStr = implode(", ", $inspectedByArr);

    // // Build rows with both label and data bold
    // $verifiedRow  = "<tr><td style='border:none;'><b>Verified By :</b> <b>$verifiedByStr</b></td></tr>";
    // $inspectedRow = "<tr><td style='border:none;'><b>Inspected By :</b> <b>$inspectedByStr</b></td></tr>";


$content .= '
    <br><br>
    <table style="width:100%; font-family:Arial, sans-serif; font-size:12px; border-collapse:collapse;">

       <tr>
            <td colspan="8" style="border:none; text-align:left; width:50%;"><b>Inspected By :</b> <b>' . $inspectedByStr . '</b></td>
        </tr>

        <tr>
            <td colspan="8" style="border:none; text-align:left; width:50%;"><b>Verified By :</b> <b>' . $verifiedByStr . '</b></td>
        </tr>
       
    </table>';
}

echo $content;
}

