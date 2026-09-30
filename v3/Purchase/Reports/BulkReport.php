<?php
$fileParamsString = $_GET['fileParams'];
$LotID = $_GET['lotId'];

$fileParams = explode(',', $fileParamsString);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");

$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Bulk Report -' . $LotNumber);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(45);
$pdf->SetHeaderMargin(155);
$pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT, 45);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 15);

foreach ($fileParams as $file) {
    if ($file === 'RmaPrint') {
        $SULPHITE = $row_org_insert['SULPHITE'];
        $SqlLotDetails = "SELECT * FROM `RmLotDetatils`AS RLD 
        LEFT JOIN RmPurchaseStations AS RPS ON RPS.PurchaseStationID=RLD.PurchaseStationID
        LEFT JOIN RmUsersList AS RUL ON RUL.ID=RLD.AgentID
        LEFT JOIN RmProcessingStations AS RPRS ON RPRS.ProcessingStationID=RPS.ProcessingStationID	
        WHERE RmLotID=$LotID";
        $StmtLotDetails = $iconn->query($SqlLotDetails);
        $RowLotDetails = $StmtLotDetails->fetch_array();

        $LotNumber = $RowLotDetails['LotNumber'];
        $PurchaseCount = $RowLotDetails['PurchaseCount'];
        $CompanyLogo = $RowLotDetails['CompanyLogo'];
        $CompanyName = $RowLotDetails['FullName'];
        $CompanyFooter = $RowLotDetails['Name'];
        $AgentName = $RowLotDetails['UserName'];


        $SqlABReport = "SELECT * FROM `QcRmAbReport` as QRAR 
        LEFT JOIN QcABOptions AS QABO ON QRAR.AbStatus=QABO.OptID 
        WHERE LotID='$LotID'";

        $StmtABReport = $iconn->query($SqlABReport);
        $RowABReport = $StmtABReport->fetch_array();

        $RESULT = $RowABReport['Parameter'];
        $SULPHITE = $RowABReport['Sulphite'];
        $AntiBioticStatus = $RowABReport['Name'];

        $SqlOrgnDtls = "SELECT * FROM `QcRmOrgnDetails` WHERE LotID=$LotID";
        $StmtOrgnDtls = $iconn->query($SqlOrgnDtls);
        $RowOrgnoDtls = $StmtOrgnDtls->fetch_array();
        $CheckedCount = $RowOrgnoDtls['CheckedCount'];

        $InspectedBy = $RowOrgnoDtls['PostedUserID'];
        $InspectedRemarks = $RowOrgnoDtls['Remarks'];
        $Inspected = getUserDetails($InspectedBy);



        while ($RowOrgnoDtls = $StmtOrgnDtls->fetch_array()) {

            $arryOrgnoDtls[] = $RowOrgnoDtls;
        }


        // $SqlInspectedBy="SELECT * FROM `AdminLoginDetails` WHERE UserID='$InspectedBy'";
        // $StmtInspectedBy=$iconn->query($SqlInspectedBy); 
        // $RowInspectedBy=$StmtInspectedBy->fetch_array();
        // $Inspected=$RowInspectedBy['UserName'];


        $SqlVerifiedOrgnDtls = "SELECT * FROM `QcRmOrgnReport` WHERE LotID=$LotID";
        $StmtVerifiedOrgnDtls = $iconn->query($SqlVerifiedOrgnDtls);
        $RowVerifiedOrgnoDtls = $StmtVerifiedOrgnDtls->fetch_array();

        $VerifiedBy = $RowVerifiedOrgnoDtls['PostedUserID'];
        $VerifiedRemarks = $RowVerifiedOrgnoDtls['Remarks'];
        $SuggestedDeductions = $RowVerifiedOrgnoDtls['SuggestedDeductions'];
        $ActualDeductions = $RowVerifiedOrgnoDtls['ActualDeductions'];

        $Verified = getUserDetails($VerifiedBy);
        // $SqlVerifiedBy="SELECT * FROM `AdminLoginDetails` WHERE UserID='$VerifiedBy'";
        // $StmtVerifiedBy=$iconn->query($SqlVerifiedBy); 
        // $RowVerifiedBy=$StmtVerifiedBy->fetch_array();
        // $Verified=$RowVerifiedBy['UserName'];

        $pdf->SetFont('helvetica', '', 11);
        $pdf->AddPage('P');
        $pdf->Image($CompanyLogo, 10, 10, 35, 30, 'JPG', '', 'T', True, 400, '', false, false, 0, false, false, false);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->Cell(0, 5, $CompanyName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->Cell(0, 5, 'INTERNAL QUALITY CHECK', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

        $pdf->Ln(15);
        $pdf->SetFont('helvetica', '', 11);
        $H1 = '5';
        $pdf->Cell(40, $H1, 'Ref No.' . $RowLotDetails['LotNumber'], 0, 0, 'L');
        $pdf->Cell(30, $H1, 'Qty.' . $RowLotDetails['PurchaseWeight'], 0, 0, 'L');
        $pdf->Cell(60, $H1, 'Purchase Date:' . getFormatedDate($RowLotDetails['PurchaseDate']), 0, 0, 'L');
        $pdf->Cell(40, $H1, 'Production Date:' . getFormatedDate($RowLotDetails['CreatedDateTime']), 0, 1, 'L');



        $table_data = getDS_TABLE3($iconn, $ID, $arryOrgnoDtls, $PurchaseCount, $AgentName);
        $pdf->writeHTML($table_data);

        $pdf->Cell(0, $H1, 'Remarks : ' . $InspectedRemarks, 0, 1, 'L');
        $pdf->Cell(0, $H1, '', 0, 1, 'L');
        $pdf->Cell(90, $H1, 'INSPECTED BY: ' . $Inspected, 1, 0, 'L');
        $pdf->Cell(90, $H1, 'Monitered BY:', 1, 1, 'L');
        $pdf->Cell(0, $H1, '', 0, 1, 'L');
        $pdf->Cell(0, $H1, 'ANTIBIOTIC ANALYSIS REPORT (PRE HARVEST/HARVEST) :', 0, 1, 'L');




        $table_data = getDS_TABLE4($iconn, $LotID, $RESULT, $ANTI_BIOTIC_STATUS);
        $pdf->writeHTML($table_data);

        $pdf->Cell(0, $H1, 'Remarks: ' . $VerifiedRemarks, 0, 1, 'L');
        $pdf->Cell(0, $H1, '', 0, 1, 'L');
        //$pdf->Cell(180,10,$VerifiedRemarks,1,1,'L');
        $pdf->Cell(90, $H1, 'Verified and Certified by:', 1, 0, 'L');
        $pdf->Cell(90, $H1, $Verified, 1, 1, 'L');
        $pdf->Cell(90, $H1, 'Suggested Deductions:' . $SuggestedDeductions, 1, 0, 'L');
        $pdf->Cell(90, $H1, 'Actual Deductions:' . $ActualDeductions, 1, 0, 'L');
    }
    if ($file === 'RmBill') {
        $RmLotSql = "SELECT * FROM `RmLotDetatils`  WHERE `RmLotID`='$LotID'";
        $RmLotStmt = $iconn->query($RmLotSql);
        $RmLotRow = $RmLotStmt->fetch_assoc();

        $ArrivalID = $RmLotRow['RmReferenceID'];
        $LotNumber = $RmLotRow['LotNumber'];


        $RmBillSql = "SELECT * FROM `RmBillDetails`  WHERE `ArrivalID`='$ArrivalID'";
        $RmBillExec = $iconn->query($RmBillSql);
        $RmBillRow = $RmBillExec->fetch_assoc();


        // echo json_encode($RmBillRow, JSON_PRETTY_PRINT);
        // exit;




        $SqlLotDetails = "SELECT * FROM `RmArrivalDetatils` AS RLD LEFT
            JOIN RmPurchaseStations AS RPS ON RLD.ArrivalStationID=RPS.PurchaseStationID
            WHERE `RmArrivalID`='$ArrivalID'";
        $StmtLotDetails = $iconn->query($SqlLotDetails);
        $RowLotDetails = $StmtLotDetails->fetch_array();
        $PurchaseType = $RowLotDetails['PurchaseType'];
        $ProcessingStnID = $RowLotDetails['ProcessingStationID'];
        $UNIT = $RowLotDetails['UNIT'];

        $SqlClientDetails = "SELECT * FROM `RmProcessingStations` WHERE `ProcessingStationID`='$ProcessingStnID'";
        $StmtClientDetails = $iconn->query($SqlClientDetails);
        $RowClientDetails = $StmtClientDetails->fetch_array();

        $CompanyName = $RowClientDetails['FullName'];
        $GST = $RowClientDetails['GSTNumber'];
        $Logo = $RowClientDetails['CompanyLogo'];

        if ($RowClientDetails['StationName'] == 'Mourya') {
            $Logo = "http://3.7.44.132/Images/mourya_Logo.png";
        }

        $pdf->AddPage('P');
        $pdf->Image($Logo, 15, 5, 25, 25, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 5, '', 0, 1, 'L', 0, '', 0, false, 'T', 'M');
        //$pdf->Cell(0, 6, $BillCopy , 0,1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->Cell(0, 6, $CompanyName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'GSTIN No : ' . $GST, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'RAW MATERIAL PURCHASE REPORT CUM RECEIVED NOTE', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $table_data = getDS_TABLE_RmBill($iconn, $LotID);
        $pdf->writeHTML($table_data, true, false, true, false, '');
        $pdf->SetFont('helvetica', '', 10);
    }
    if ($file === 'CostingReportPrint') {
        $pdf->setPrintHeader(false);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetMargins(5, 10, 5, 10);
        $pdf->AddPage('L');
        $pdf->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->Cell(0, 5, $NAME, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->Cell(0, 0, 'Costing Report  ', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $pdf->Cell(180, 20, '', 0, 1, 'C', 0);



        $PurchaseData = getPurchaseTable($iconn, $LotID);
        $BillData = getBillTable($iconn, $LotID);
        $GradingData = getPackingBreakUp($iconn, $LotID);
        $CostingData = getCosingData($iconn, $LotID);

        $content = '<table border="0" cellspacing="0" cellpadding="3">

                    <tr>
                    <td width="70%">
                    <table border="0" cellspacing="0" cellpadding="3">
                    <tr><td>' . $PurchaseData . '</td></tr>
                    </table>
                    <table border="0" cellspacing="0" cellpadding="3">
                    <tr><td>' . $BillData . '</td></tr></table>
                    <table border="0" cellspacing="0" cellpadding="3">
                    <tr><td>' . $GradingData . '</td></tr></table>
                    </td>
                        <td width="30%"> <table border="0" cellspacing="0" cellpadding="3">
                    <tr><td>' .

            $CostingData
            . '</td></tr></table></td>

                    </tr>

                    </table>';
        $pdf->writeHTML($content);
    }
    if ($file === 'DocWeightSlipPrint') {
        $SqlGradinglist = "SELECT * FROM `RmLotDetatils` as RLD 
                    LEFT JOIN PpcHLGradingDetails as PHGD on PHGD.LotID=RLD.RmLotID
                    LEFT JOIN RmPurchaseStations as RPS on RPS.PurchaseStationID= RLD.PurchaseStationID
                    where RmLotID='$LotID'";
        $StmtGradinglist = $iconn->query($SqlGradinglist);
        $RowGradinglist = $StmtGradinglist->fetch_assoc();

        $data = $RowGradinglist['GradingData'];


        $json_decode_data = json_decode($data, true);
        $json_decode_data = array();
        $HL_WEIGHT = 0;


        $SqlGradingBrkUp = "SELECT * FROM `PpcHLGradingBreakup` WHERE LotID=$LotID";
        $StmtGradingBrkUp = $iconn->query($SqlGradingBrkUp);
        while ($RowGradingBrkUp = $StmtGradingBrkUp->fetch_assoc()) {

            $json_decode_data[] = $RowGradingBrkUp;
        }

        //24854;


        foreach ($json_decode_data as $value) {

            //var_dump($value);

            if (!in_array($value['HLCount'], $ary_counts)) {
                $ary_counts[] = $value['HLCount'];
                ${"ary_cc" . $value['HLCount']} = $value['HLCount'];
            }
        }

        //exit;

        sort($ary_counts);

        $ary_test;
        foreach ($ary_counts as $count) {
            $ary_start = array();
            foreach ($json_decode_data as $value) {

                if ($value['HLCount'] == $count) {

                    $ary_start[] = $value['HLWeight'];
                }
            }

            $ary_test[] = $ary_start;
        }


        //print_r($ary_test);

        $r = 0;
        foreach ($ary_test as $val) {

            if ($r < count($val)) {
                $r = count($val);
            }
        }



        //================= fetching HLG details ===================
        $SqlHlGradingDtls = "SELECT RLD.PurchaseDate, RLD.LotNumber, RLD.PurchaseStationID, RPS.Location,RLD.PurchaseCount as Count, RLD.PurchaseWeight as Weight FROM `RmLotDetatils` RLD 
        LEFT JOIN RmArrivalDetatils RAD on RAD.RmArrivalID = RLD.RmReferenceID 
        LEFT JOIN RmBillDetails RBD on RAD.RmArrivalID = RBD.ArrivalID 
        LEFT JOIN RmBillBreakup as RBB on RBB.BreakUpID=RLD.BreakUpID 
        LEFT JOIN RmPurchaseStations as RPS on RPS.PurchaseStationID= RLD.PurchaseStationID WHERE RLD.RmLotID = '$LotID'";

        //echo $HLGQuery; exit;
        $StmtHlGradingDtls = $iconn->query($SqlHlGradingDtls);

        $center = $PurchaseDate = $headlessCount = $headlessQty = $LOTNO = "";
        if (!empty($StmtHlGradingDtls)) {
            $HLGRep = $StmtHlGradingDtls->fetch_assoc();

            $PurchaseDate = $HLGRep['PurchaseDate'];
            $center = $HLGRep['Location'];
            $headlessCount = (!empty($HLGRep['Count']) ? $HLGRep['Count'] . ' c' : '');
            $headlessQty = (!empty($HLGRep['Weight']) ? $HLGRep['Weight'] . ' Kg(s)' : '');
            $LOTNO = $HLGRep['LotNumber'];
        }
        $weight_sum = 0;
        $SqlHlDtls = "SELECT PPC.GradingData, PPC.GradingDate FROM `PpcHLGradingDetails` PPC where LotID ='$LotID'";
        $StmtHlDtls = $iconn->query($SqlHlDtls);
        $headlessRes = $StmtHlDtls->fetch_assoc();
        $dd = strtotime($headlessRes['GradingDate']);
        $dateF = getDate($dd);
        $submission_date = date('d-m-Y h:i:s', strtotime($headlessRes['GradingDate']));


        if (!empty($headlessRes['GradingData'])) {
            $json_decode_data = json_decode($headlessRes['GradingData'], true);
            foreach ($json_decode_data as $value) {
                $weight_sum = $weight_sum + $value['HLWeight'];
            }
        }
        $pdf->setPrintHeader(false);
        $pdf->AddPage('P');



        $pdf->SetFont('helvetica', 'BU', 12);

        $FONT_ARI = 'helvetica';
        $HEIGHT = 8;
        $WIDHT_1 = 'helvetica';

        $header = 10;
        $sub_header = 8;
        $text = 6;

        $pdf->SetFont($FONT_ARI, 'BU', $header);


        $pdf->Cell(0, 5, 'HEADLESS TO GRADING REPORT', 0, 1, 'C');
        $pdf->Cell(130, 5, '', 0, 1, 'C');


        $OV_COUNT = 1;


        $W1 = 30;
        $W5 = 35;
        $W2 = 30;
        $W3 = 60;
        $W4 = 35;
        $H1 = 5;


        $PurchaseDate = date("d-m-Y", strtotime($PurchaseDate));


        $pdf->SetFont($FONT_ARI, 'B', $sub_header);
        $pdf->Cell($W1, $H1, 'Grading Date:', 1, 0, 'L');
        $pdf->Cell($W2, $H1, $submission_date, 1, 0, 'L');
        $pdf->Cell($W3, $H1, '', 1, 0, 'L');
        $pdf->Cell($W4, $H1, 'Purchase Date:', 1, 0, 'L');
        $pdf->Cell($W5, $H1, $PurchaseDate, 1, 1, 'L');


        $pdf->SetFont($FONT_ARI, 'B', $sub_header);
        $pdf->Cell($W1, $H1, 'HL-COUNT:', 1, 0, 'L');
        $pdf->Cell($W2, $H1, $headlessCount, 1, 0, 'L');
        $pdf->Cell($W3, $H1, '', 1, 0, 'L');
        $pdf->Cell($W4, $H1, 'LOT NO:', 1, 0, 'L');
        $pdf->Cell($W5, $H1, $LOTNO, 1, 1, 'L');


        $pdf->SetFont($FONT_ARI, 'B', $sub_header);
        $pdf->Cell($W1, $H1, 'HL-QUANTITY:', 1, 0, 'L');
        $pdf->Cell($W2, $H1, '', 1, 0, 'L');
        $pdf->Cell($W3, $H1, 'HEADLESS TO GRADING REPORT', 3, 0, 'C');
        $pdf->Cell($W4, $H1, 'CENTER:', 1, 0, 'L');
        $pdf->Cell($W5, $H1, $center, 1, 1, 'L');


        $pdf->SetFont($FONT_ARI, 'B', $sub_header);
        $pdf->Cell($W1, $H1, 'HON-PACKING:', 1, 0, 'L');
        $pdf->Cell($W2, $H1, '', 1, 0, 'L');
        $pdf->Cell($W3, $H1, '', 1, 0, 'L');
        $pdf->Cell($W4, $H1, 'HON-COUNT:', 1, 0, 'L');
        $pdf->Cell($W5, $H1, $headlessCount, 1, 1, 'L');


        $pdf->SetFont($FONT_ARI, 'B', $sub_header);
        $pdf->Cell($W1, $H1, 'REJECTION QTY:', 1, 0, 'L');
        $pdf->Cell($W2, $H1, '', 1, 0, 'L');
        $pdf->Cell($W3, $H1, '', 1, 0, 'L');
        $pdf->Cell($W4, $H1, 'HON.QTY:', 1, 0, 'L');
        $pdf->Cell($W5, $H1, $headlessQty, 1, 1, 'L');

        $W5 = 10;
        $pdf->Cell($W5, $H1, '', 0, 1, 'L');


        $pdf->SetFont($FONT_ARI, 'B', $sub_header);
        $pdf->Cell($W5, $H1, 'SI.NO', 1, 0, 'L');


        $W4 = 180 / COUNT($ary_counts);
        sort($ary_counts);
        $c = 1;
        foreach ($ary_counts as $EACH_count) {
            if ($c == count($ary_counts)) {
                $pdf->Cell($W4, $H1, 'COUNT: ' . $EACH_count, 1, 1, 'L');
            } else {
                $pdf->Cell($W4, $H1, 'COUNT: ' . $EACH_count, 1, 0, 'L');
            }
            $c++;
        }

        for ($i = 0; $i < $r; $i++) {
            $pdf->Cell($W5, $H1, $i + 1, 1, 0, 'L');

            for ($j = 0; $j < count($ary_test); $j++) {
                $val = $ary_test[$j][$i];

                if ($j < count($ary_test) - 1) {
                    $pdf->Cell($W4, $H1, $val, 1, 0, 'L');
                } else {
                    $pdf->Cell($W4, $H1, $val, 1, 1, 'L');
                }
            }
        }

        $pdf->Cell($W5, $H1, 'Total', 1, 0, 'L');

        $c = 1;

        //$weight_sum=0;
        foreach ($ary_counts as $count) {

            foreach ($json_decode_data as $value) {
                if ($value['HLCount'] == $count) {
                    $weight_count = $weight_count + $value['HLWeight'];
                }
            }


            if ($c == count($ary_counts)) {
                $pdf->Cell($W4, $H1, $weight_count . ' Kgs', 1, 1, 'L');
            } else {
                $pdf->Cell($W4, $H1, $weight_count . ' Kgs', 1, 0, 'L');
            }
            $c++;

            $weight_count = 0;
        }





        $weight_sum = 0;
        foreach ($json_decode_data as $value) {
            $weight_sum = $weight_sum + $value['HLWeight'];
        }


        $pdf->Cell(190, 10, 'Total : ' . $weight_sum . ' Kgs', 1, 1, 'L', 0);
        $pdf->Cell(95, 10, 'Signature', 1, 0, 'L', 0);
        $pdf->Cell(95, 10, 'Signature', 1, 1, 'L', 0);


        $pdf->Ln();
    }
}


ob_end_clean();
$file_name = 'Bulk Report' . $LotNumber . '.pdf';
$pdf->IncludeJs("print();");
$pdf->Output($file_name, 'I');


function getDS_TABLE3($iconn, $ID, $ary, $PurchaseCount, $AgentName)
{


    //CheckedCount
    $TotalCheckedCount = $ary[0]['CheckedCount'] + $ary[1]['CheckedCount'] + $ary[2]['CheckedCount'];
    $TotalCheckedCount = round($TotalCheckedCount / 3);
    $AverageWeight = '1000' / $TotalCheckedCount;

    //BlackGills
    $BlackGills = $ary[0]['BlackGills'] * $AverageWeight + $ary[1]['BlackGills'] * $AverageWeight + $ary[2]['BlackGills'] * $AverageWeight;
    $BlackGills = ROUND($BlackGills / 30, 1);

    //SoftShell

    $Soft = $ary[0]['Soft'] * $AverageWeight + $ary[1]['Soft'] * $AverageWeight +
        $ary[2]['Soft'] * $AverageWeight;
    $Soft = round($Soft / 30, 1);

    //DroopingHead

    $DroopingHead = $ary[0]['DroopingHead'] * $AverageWeight + $ary[1]['DroopingHead'] * $AverageWeight + $ary[2]['DroopingHead'] *
        $AverageWeight;
    $DroopingHead = round($DroopingHead / 30, 1);

    //BlackSpot	

    $BlackSpot = $ary[0]['BlackSpot'] * $AverageWeight + $ary[1]['BlackSpot'] * $AverageWeight + $ary[2]['BlackSpot'] * $AverageWeight;
    $BlackSpot = round($BlackSpot / 30, 1);

    //Broken

    $Broken = $ary[0]['Broken'] * $AverageWeight + $ary[1]['Broken'] * $AverageWeight + $ary[2]['Broken'] * $AverageWeight;
    $Broken = round($Broken / 30, 1);

    //ImproperLegs	

    $ImproperLegs = $ary[0]['ImproperLegs'] * $AverageWeight + $ary[1]['ImproperLegs'] * $AverageWeight + $ary[2]['ImproperLegs'] * $AverageWeight;
    $ImproperLegs = round($ImproperLegs / 30, 1);

    //Bends

    $Bends = $ary[0]['Bends'] * $AverageWeight + $ary[1]['Bends'] * $AverageWeight + $ary[2]['Bends'] * $AverageWeight;
    $Bends = round($Bends / 30, 1);

    $FitForHonPack = $StmtOrgnDtls['FitForHonPack'];



    $content = '';

    $size = 40;
    $size1 = 60;

    $content .= '<table border="1" cellspacing="0" cellpadding="4">  
	
	<tr> 

                <th  align="center" width="100%"><font size="13"> ORGANELLOPATIC INSPECTION REPORT</font></th>
               
                
				</tr>
	
	          <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Description </font></th>
                <th  align="center" width="12%"><font size="9"> Standard </font></th>
                <th  align="center" width="12%"><font size="9"> 1 </font></th>
                <th  align="center" width="12%"><font size="9"> 2  </font></th>
                <th  align="center" width="12%"><font size="9"> 3 </font></th>
                <th  align="center" width="12%"><font size="9"> Average% </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Farmer Agent</font></th>
                <th  align="center" width="12%"><font size="9">-</font></th>
                <th  align="center" width="48%"><font size="9">' . $AgentName . ' </font></th>

               
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Purchase count/Kg </font></th>
                <th  align="center" width="12%"><font size="9"> - </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $PurchaseCount . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $PurchaseCount . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $PurchaseCount . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $PurchaseCount . '  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Checked count/Kg </font> </th>
                <th  align="center" width="12%"><font size="9"> -  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $ary[0]['CheckedCount'] . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $ary[1]['CheckedCount'] . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $ary[2]['CheckedCount'] . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $TotalCheckedCount . '  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Red/Black gill in% </font></th>
                <th  align="center" width="12%"><font size="9"> 5% </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['BlackGills'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['BlackGills'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['BlackGills'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $BlackGills . '  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Soft/Semi Soft/Paper Soft </font></th>
                <th  align="center" width="12%"><font size="9"> 10% </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[0]['Soft'] * $AverageWeight / 10, 1) . ' </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['Soft'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['Soft'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $Soft . '  </font></th>
                
               
                
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Dropping Head in %</font></th>
                <th  align="center" width="12%"><font size="9"> 5% </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['DroopingHead'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['DroopingHead'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['DroopingHead'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $DroopingHead . '  </font></th>
                
               
                
				</tr>
				
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Black spot/Black Dot in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[0]['BlackSpot'] * $AverageWeight / 10, 1) . ' </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[1]['BlackSpot'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[2]['BlackSpot'] * $AverageWeight / 10, 1) . ' </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $BlackSpot . '  </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Broken/Damages in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['Broken'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['Broken'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['Broken'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $Broken . '  </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Improper Legs/Decomposition/Discoloration in %</font></th>
                <th  align="center" width="12%"><font size="9"> Nil </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['ImproperLegs'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['ImproperLegs'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[2]['ImproperLegs'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . $ImproperLegs . '  </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Bends in %</font></th>
                <th  align="center" width="12%"><font size="9"> 10% </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[0]['Bends'] * $AverageWeight / 10, 1) . '  </font></th>
                <th  align="center" width="12%"><font size="9"> ' . ROUND($ary[1]['Bends'] * $AverageWeight / 10, 1) . '   </font></th>
                <th  align="center" width="12%"><font size="9">  ' . ROUND($ary[2]['Bends'] * $AverageWeight / 10, 1) . ' </font></th>
                <th  align="center" width="12%"><font size="9">  ' . $Bends . ' </font></th>
          
				</tr>
				
				 <tr> 
           		
                <th  align="center" width="40%"><font size="9"> Material is fit for Head On Packing or not</font></th>
                <th  align="center" width="12%"><font size="9"> -- </font></th>
                
                <th  align="center" width="48%"><font size="9"> ' . $FitForHonPack . '  </font></th>
              
          
				</tr>
				
				
				</table>';


    return $content;
}



function getDS_TABLE4($iconn, $LotID, $RESULT, $ANTI_BIOTIC_STATUS)
{


    $content = '';

    $PARMS = array('CAP', 'AOZ', 'AMOZ', 'AHD', 'SEM');

    $size = 40;
    $size1 = 60;

    $content .= '<table border="1" cellspacing="0" cellpadding="4">  	
		
		 <tr> 
           		
               
                <th  align="center" width="28%"><font size="9"> DESCRIPTION  </font></th>
                <th  align="center" width="18%"><font size="9"> Standard </font></th>
                <th  align="center" width="18%"><font size="9"> Analysis  </font></th>
                <th  align="center" width="18%"><font size="9"> Re-Analysis </font></th>
                <th  align="center" width="18%"><font size="9"> Third Party </font></th>
          
				</tr>
		
		
		<tr> ';

    foreach ($PARMS as $value) {


        if ($ANTI_BIOTIC_STATUS == "0") {
            $parM_val = 'TEST PENDING';
        } else
            if ($value == $RESULT) {
                $CLR = 'red';
                $parM_val = 'POSITIVE';
            } else {
                $CLR = 'black';
                $parM_val = 'NEGATIVE';
            }

        $content .= '    <th  align="center" width="28%"><font size="9"> ' . $value . '  </font></th>
                <th  align="center" width="18%"><font size="9"> NEGATIVE </font></th>
                <th  align="center" width="18%" color="' . $CLR . '"><font size="9"> ' . $parM_val . '  </font></th>
                <th  align="center" width="18%"><font size="9"> </font></th>
                <th  align="center" width="18%"><font size="9">   </font></th>
          
				</tr>
		<tr>';
    }





    $content .= '      
		        <th  align="center" width="28%"><font size="9"> SULPHITE  </font></th>
                <th  align="center" width="18%"><font size="9"> Less Than 50ppm </font></th>
                <th  align="center" width="18%"><font size="9">' . $SULPHITE . ' </font></th>
                <th  align="center" width="18%"><font size="9">  </font></th>
                <th  align="center" width="18%"><font size="9">   </font></th>
          
				</tr>
		
						</table>';

    return $content;
}


function getDS_TABLE_RmBill($iconn, $LotID)
{

    global $ArrivalID;



    $BillQuery = "SELECT
    RMD.*,
	RLD.*,
	RFD.*,
	RUL1.*,
	QCR.*,
	QCOR.*,
    RUL1.UserName AS AgentName,
    RUL2.UserName AS DriverName,
    RUL3.UserName AS GraderName,
    QCR.AbStatus AS ABStatus,
    RPS.Location AS PurchaseCenter,
    RPC.PurchaseType AS PurchaseType,
    RVD.VehicalNumber AS TruckNumber,
    RBD.Remarks AS BillRemarks,
    RFD.BankName AS SupBankName,
    RFD.BankAccNo AS SupBankAccNo,
    RFD.IFSC AS SupIFSC,
    RFD.PanNumber AS SupPANNumber
FROM
    `RmArrivalDetatils` AS RMD
LEFT JOIN RmLotDetatils RLD ON
    RLD.RmReferenceID = RMD.RmArrivalID
LEFT JOIN RmBillDetails RBD ON
    RMD.RmArrivalID = RBD.ArrivalID
LEFT JOIN QcRmOrgnReport QCOR ON
    RLD.RmLotID = QCOR.LotID
LEFT JOIN RmSuppliers RFD ON
    RFD.ID = RBD.FinancierID
LEFT JOIN RmUsersList RUL1 ON
    RUL1.ID = RMD.AgentID
LEFT JOIN RmUsersList RUL2 ON
    RUL2.ID = RMD.DriverID
LEFT JOIN RmUsersList RUL3 ON
    RUL3.ID = RMD.GraderID
LEFT JOIN QcRmAbReport QCR ON
    QCR.LotID = RLD.RmLotID
LEFT JOIN RmPurchaseStations RPS ON
    RPS.PurchaseStationID = RMD.ArrivalStationID
LEFT JOIN RmPurchaseCategory RPC ON
    RPC.CatID = RMD.PurchaseCategoryID
LEFT JOIN RmVehicalDetails RVD ON
    RVD.ID = RMD.VehicalID
WHERE
    RLD.RmLotID = '$LotID'";


    // 	$BillQuery = "SELECT *,
    // (SELECT UserName FROM `RmUsersList` WHERE ID=RMD.AgentID ) as AgentName,
    // (SELECT UserName FROM `RmUsersList` WHERE ID=RMD.DriverID ) as DriverName,
    // (SELECT UserName FROM `RmUsersList` WHERE ID=RMD.GraderID ) as GraderName,
    // (SELECT AbStatus FROM `QcRmAbReport` WHERE LotID=RLD.RmLotID ) as ABStatus,
    // (SELECT Location FROM `RmPurchaseStations` WHERE PurchaseStationID=RMD.ArrivalStationID ) as PurchaseCenter,
    // (SELECT PurchaseType FROM `RmPurchaseCategory` WHERE CatID=RMD.PurchaseCategoryID ) as PurchaseType,
    // (SELECT VehicalNumber FROM `RmVehicalDetails` WHERE ID=RMD.VehicalID ) as TruckNumber,
    // RBD.Remarks as BillRemarks, RFD.BankName as SupBankName, RFD.BankAccNo as SupBankAccNo, RFD.IFSC as SupIFSC,
    // RFD.PanNumber AS SupPANNumber
    // FROM `RmArrivalDetatils` as RMD
    // LEFT JOIN RmLotDetatils RLD on RLD.RmReferenceID=RMD.RmArrivalID
    // LEFT JOIN RmBillDetails RBD on RMD.RmArrivalID = RBD.ArrivalID
    // LEFT JOIN QcRmOrgnReport QCOR on RLD.RmLotID = QCOR.LotID 
    // LEFT JOIN RmSuppliers RFD on RFD.ID = RBD.FinancierID 
    // LEFT JOIN RmUsersList RUL on RUL.ID = RMD.AgentID 
    // WHERE RLD.RmLotID = '$LotID'";



    // (SELECT AbStatus FROM `QcRmAbReport` WHERE LotID=RMD.RmArrivalID ) as ABStatus,

    // ECHO $BillQuery ; exit;
    $BillExec = $iconn->query($BillQuery);

    $BillPanNumber = $center = $PurchaseDate = $headlessCount = $headlessQty = "";
    if (!empty($BillExec)) {

        $BillRow = $BillExec->fetch_assoc();

        //var_dump($BillRow); exit;


        echo json_encode($BillRow, JSON_PRETTY_PRINT);
        // exit;

        $ABStatusID = $BillRow['ABStatus'];
        $RmLotID = $BillRow['RmLotID'];

        $SqlABReport = "SELECT * FROM `QcABOptions` WHERE OptID='$ABStatusID'";
        $StmtABReport = $iconn->query($SqlABReport);
        $RowABReport = $StmtABReport->fetch_assoc();


        $SqlArrivalBreakUp = "SELECT * FROM `RmArrivalBreakUpDetails` WHERE ArrivalID='$ArrivalID' and Variety='Head On'";
        //exit;
        $StmtArrivalBreakUp = $iconn->query($SqlArrivalBreakUp);
        $RowArrivalBreakUp = $StmtArrivalBreakUp->fetch_assoc();

        $PurchaseCount = $RowArrivalBreakUp['Count'];
        $PurchaseWeight = $RowArrivalBreakUp['Weight'];

        $ABStatus = $RowABReport['Name'];
        $center = $BillRow['PurchaseCenter'];
        $PurchaseDate = $BillRow['BillDate'];
        $BillDate = date("d-m-Y", strtotime($PurchaseDate));
        $BillPanNumber = $BillRow['SupPANNumber']; //=1234567890;
        $Tdsperc = $BillRow['Tds'];
        $FarmAddress = $BillRow['FarmAddress'];
        $C_COUNT = $BillRow['C_COUNT'];
        $PurchaseCount = $BillRow['PurchaseCount'];
        $PurchaseWeight = $BillRow['PurchaseWeight'];
        $TruckNumber = $BillRow['TruckNumber'];
        $DriverName = $BillRow['DriverName'];
        $C_QTY = $BillRow['WEIGHT'];
        $RATE = $BillRow['RATE'];
        $LotNumber = $BillRow['ArrivalNumber'];
        $TUBS = $BillRow['TUBS'];
        $FinancierName = $BillRow['FinancierName'];
        $AgentName = $BillRow['UserName'];
        $VARITY = $BillRow['VARITY'];
        //	$AccDedKg= $BillRow['Deductions'];
        $LabActDedKg = $BillRow['ActualDeductions'];
        $LabSugDedKg = $BillRow['SuggestedDeductions'];
        $PurchaseType = $BillRow['PurchaseType'];
        $GraderName = $BillRow['GraderName'];
        $HarvestCommission = $BillRow['HarvestCommission'];
        $ACCOUNT_REMARKS = $BillRow['BillRemarks'];
        $FinancierBankName = $BillRow['SupBankName'];
        $FinancierBankAccNo = $BillRow['SupBankAccNo'];
        $FinancierIFSC = $BillRow['SupIFSC'];
    }



    $SqlArrivalDtls = "SELECT Remarks,Deductions,FinancialDate,CreditDays,CreditRemarks,InterestRate FROM `RmBillDetails` WHERE ArrivalID='$ArrivalID'";
    $StmtArrivalDtls = $iconn->query($SqlArrivalDtls);
    $RowArrivalDtls = $StmtArrivalDtls->fetch_assoc();



    $LotRemarks = $RowArrivalDtls['Remarks'];
    $AccDedKg = $RowArrivalDtls['Deductions'];
    $CreditDays = $RowArrivalDtls['CreditDays'];
    $CreditRemarks = $RowArrivalDtls['CreditRemarks'];
    $InterestRate = round($RowArrivalDtls['InterestRate']);
    if ($RowArrivalDtls['FinancialDate']) {
        $DueDate = date('d-m-Y', strtotime($RowArrivalDtls['FinancialDate']));
    } else {
        $DueDate = '';
    }


    $SqlBillBrkUpQuery = "SELECT * from RmBillBreakup WHERE  ArrivalID = '$ArrivalID'";
    $SqlBillBrkUpExec = $iconn->query($SqlBillBrkUpQuery);

    while ($SqlBillRow = $SqlBillBrkUpExec->fetch_assoc()) {

        $BillWeight += $SqlBillRow['Weight'];
        $BillCount = $SqlBillRow['Count'];
    }


    $content = '';

    $content .= '<h5  style="text-align:right"> AB REPORT: ' . $ABStatus . '</h5>';
    $content .= '<h5  style="text-align:center"></h5>';


    $size1 = 100 / 6;
    $size2 = 100 / 6;
    $size = 100 - $size1 - $size2;
    $size3 = 60;




    $content .= '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="LEFT"  width="' . ($size) . '%"><font size="10">Bill Name: <b>' . $FinancierName . '</font></b></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Date :  <br>Due Date : </font></th>
                <th  align="LEFT" width="' . ($size1) . '%"><font size="10"><b>' . $BillDate . '<br>' . $DueDate . '</b></font></th>
                </tr>
				
				<tr>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Farmer Name:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"></font></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Cell Number:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"></font></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Village: </font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"><B>' . $FarmAddress . '</B></font></th>
				  
           		

				</tr>
				
				<tr>

                <th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Agent Name:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"><b>' . $AgentName . '</font></b></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Cell Number:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"></font></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Lot No:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"><b>' . $LotNumber . '</font></b></th>				
           		
               </tr>
				
				<tr>  
           		<th  align="LEFT"  width="50%"><font size="10">Company Count/Quantity : <b>' . $BillCount . 'c / ' . $BillWeight . '</b> Variety :<b>' . $VARITY . ' </b></font></th>
           		<th  align="LEFT"  width="50%"><font size="10"> Farmer Count/Quantity : <b>' . $PurchaseCount . 'c / ' . $PurchaseWeight . '</b> Variety :<b>' . $VARITY . ' </b></font></th>
               </tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Details </font></th>
                <th  align="LEFT" width="15%"><font size="9">Total Pieces 5Kg </font></th>
                <th  align="LEFT" width="15%"><font size="9"> 1Kg C </font></th>
                <th  align="LEFT" width="15%"><font size="9"> Total Pies 5Kg</font></th>
                <th  align="LEFT" width="15%"><font size="9"> 1Kg C </font></th>
                <th  align="LEFT" width="20%"><font size="9"> Average </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> 5 Kg Count </font></th>
                <th  align="LEFT" width="15%"><font size="9"> ' . (5 * $headlessCount) . '</font></th>
                <th  align="LEFT" width="15%"><font size="9"><b>' . $headlessCount . '</b> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Peeling </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Discoloured </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Black spot </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="10"><b>Quantity Details</b></font></th>
           		<th  align="center"  width="50%"><font size="10"><b> Transport Details </b></font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="10">Tubs Details</font></th>
           		<th  align="left"  width="50%"><font size="10"> Truck No : <b>' . $TruckNumber . '</B> </font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="14%"><font size="10"> Material Tubs </font></th>
           		<th  align="center"  width="10%"><font size="10"> Empty </font></th>
           		<th  align="center"  width="12%"><font size="10"> Total Tubs </font></th>
           		<th  align="center"  width="14%"><font size="10">Quantity </font></th>
           		<th  align="left"  width="50%"><font size="10"> Driver :<b>' . $DriverName . '</B> </font></th>
               </tr>
				
					<tr>  
           		<th  align="center"  width="14%"><font size="10"> ' . $TUBS . ' </font></th>
           		<th  align="center"  width="10%"><font size="10">Hon Qty  </font></th>
           		<th  align="center"  width="12%"><font size="10"> ' . $BillCount . ' C</font></th>
           		<th  align="center"  width="14%"><font size="10"> <B>' . $BillWeight . ' Kgs</B></font></th>
           		<th  align="left"  width="50%"><font size="10"> Dispatched : <b>' . $center . '</B> </font></th>
               </tr>';








    $content .= '<tr>  
           		<th  align="center"  width="24%"><font size="10"> <br>   Driver Sign <br> </font></th>
           		<th  align="center"  width="26%"><font size="10">  GRADER:<B>' . $GraderName . '<BR><br>' . $PurchaseType . ' </B><br> </font></th>
           		<th  align="center"  width="25%"><font size="10"> <br>  Supervisor Sign <br> </font></th>
           		<th  align="center"  width="25%"><font size="10"> <br>  Agent/Farm Sign <br> </font></th>
           		
               </tr>
				
				
				
				<tr>  
           		<th  align="center"  width="100%"><font size="9"><b>For Office use only   
				BillNo.   Certificate /      NoCertificate </b></font></th>    
				</tr>
				
				 <tr>  
           		<th  align="center"  width="20%"><font size="10"> Variety/Quality </font></th>
           		<th  align="center"  width="15%"><font size="10"> Count  </font></th>
           		<th  align="center"  width="15%"><font size="10"> Quantity </font></th>
           		<th  align="center"  width="15%"><font size="10"> Rate/Kg </font></th>
           		<th  align="center"  width="15%"><font size="10"> Deduction/Kg </font></th>
           		<th  align="center"  width="20%"><font size="10"> Amount :  </font></th>
           		
               </tr>';


    $SqlSpecies = "SELECT * FROM `RmArrivalDetatils` as RAD left JOIN RmLotDetatils as RLD on RAD.RmArrivalID=RLD.RmReferenceID 
			   WHERE RLD.RmLotID ='$LotID'";
    $StmtSpecies = $iconn->query($SqlSpecies);
    $RowSpecies = $StmtSpecies->fetch_array();
    $Species = $RowSpecies['Species'];

    $SqlHonGrading = "SELECT * FROM RmLotDetatils as RMD 
						left JOIN PpcHonGradingBreakup as HOG on RMD.RmLotID=HOG.LotID 
						left JOIN RmPurchaseCategory as RPC  on RPC.CatID=RMD.PurchaseCategoryID
                        left JOIN RmPurchaseStations as RPS  on RPS.PurchaseStationID=RMD.PurchaseStationID						
						WHERE LotID='$LotID'";
    $StmtHonGrading = $iconn->query($SqlHonGrading);

    if ($StmtHonGrading->num_rows > 0) {

        $RowHonGrading = $StmtHonGrading->fetch_array();
        $HonWeight = $RowHonGrading['HonWeight'];
        $HonCount = $RowHonGrading['HonCount'];

        $content .= '<tr>  
		<th  align="left"  width="20%"><font size="10"><B>' . $Species . '-Hon Packed</B></font></th>
		<th  align="right"  width="15%"><font size="10"> ' . $HonCount . '  C </font></th>
		<th  align="right"  width="15%"><font size="10"> ' . $HonWeight . '  Kgs  </font></th>
		<th  align="right"  width="15%"><font size="10"> </font></th>
		<th  align="right"  width="15%"><font size="10"> <B></b></font></th>
		<th  align="right"  width="20%"><font size="10"><B> </b></font></th>
		
	</tr>';
    }

    $BillBrkUpQuery = "SELECT * FROM `RmBillBreakup`  WHERE ArrivalID='$ArrivalID'";

    $BillBrkUpExec = $iconn->query($BillBrkUpQuery);
    $BillWeight = 0;
    while ($BillRow = $BillBrkUpExec->fetch_assoc()) {

        if ($BillRow['DefectName'] != '') {
            $Description = $BillRow['DefectName'];
        } else {
            $Description = $BillRow['Description'];
        }

        $Rate = $BillRow['Rate']; //+$HarvestCommission-$AccDedKg;
        $BillWeight += $BillRow['Weight'];
        $BillPrice += round($BillRow['Weight'] * $Rate);

        $content .= '<tr>  
           		<th  align="left"  width="20%"><font size="10"><B>' . $Species . '-' . $Description . '</B></font></th>
           		<th  align="right"  width="15%"><font size="10"> ' . $BillRow['Count'] . '  C </font></th>
           		<th  align="right"  width="15%"><font size="10"> ' . $BillRow['Weight'] . '  Kgs  </font></th>
           		<th  align="right"  width="15%"><font size="10"> ' . $Rate . ' / Kgs  </font></th>
           		<th  align="right"  width="15%"><font size="10"> <B></b></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>' . round($BillRow['Weight'] * $Rate) . ' /-  </b></font></th>
           		
               </tr>';
    }
    ;




    if ($LabSugDedKg != 0) {
        $LabRemarks = 'A DEDUCTION OF RS.' . round($LabSugDedKg) . '/- IS RECOMMENDED FOR QUALITY LOSS';
    } else {
        $LabRemarks = 'NILL';
    }

    if ($AccDedKg > 0) {
        $Sing = '+';
    } else {
        $Sing = '';
    }


    $LabActDed = round($LabActDedKg * $BillWeight);
    $AccDed = round($AccDedKg * $BillWeight);
    // $TotalInterest = round($InterestRate * $BillWeight);

    $BillTotal = $BillPrice + $AccDed - $LabActDed; //+ $TotalInterest

    // echo $BillTotal;
    // exit;

    /*
                                                          1. Credit Days Text 
                                                          
                                                           2. Bill Amount 18% / 360 x No of Credit Days 
                                                          
                                                           3. Bill Amount + Total Credit Days Bill - Tds
                                                          
                                                           */
    $interestPerLakh = 100000 * ($InterestRate / 100);
    $interestPerLakh = round($interestPerLakh / 360);
    $TotalInterest = ($BillTotal / 100000) * $interestPerLakh;
    $TotalInterest = $TotalInterest * $CreditDays;
    $TotalInterest = round($TotalInterest);

    $BillTotal = $BillTotal + $TotalInterest;




    $content .= '
				
				 <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Sub Total</b></font></th>
           		<th  align="center"  width="15%"><font size="10"> </font></th>
           		<th  align="center"  width="15%"><font size="10"><b>' . round($BillWeight, 2) . ' Kgs</b></font></th>
           		<th  align="center"  width="15%"><font size="10"></font></th>
           		<th  align="center"  width="15%"><font size="10"></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>' . $BillPrice . ' /- </b> </font></th>
           		
               </tr> 
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Lab </b></font></th>
           		
				
           		<th  align="center"  width="45%"><font size="10">' . $LabRemarks . '</font></th>
           		
           		<th  align="center"  width="15%"><font size="10">' . $LabActDedKg . '</font></th>
           		<th  align="right"  width="20%"><font size="10"><B> - ' . $LabActDed . ' /- </b> </font></th>
           		
               </tr>

			  
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Accounts </b></font></th>	
           		<th  align="center"  width="45%"><font size="10">' . $LotRemarks . '</font></th>
           		<th  align="center"  width="15%"><font size="10">' . $AccDedKg . '</font></th>
           		<th  align="right"  width="20%"><font size="10"><B>' . $AccDed . ' /- </b> </font></th>
           		
               </tr>
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Credit </b></font></th>	
           		<th  align="center"  width="45%"><font size="10">' . $CreditRemarks . '</font></th>
           		<th  align="center"  width="15%"><font size="10">' . $InterestRate . ' % </font></th>
           		<th  align="right"  width="20%"><font size="10"><B>' . $TotalInterest . ' /- </b> </font></th>
           		
               </tr>';

    if ($PurchaseDate > '2021-06-30') {

        /*
                                                                                                            1. Credit Days Text 

                                                                                                             2. Bill Amount 18% / 360 x No of Credit Days 

                                                                                                             3. Bill Amount + Total Credit Days Bill - Tds

                                                                                                             */

        /*	$BillTotal = $BillPrice + $AccDed  - $LabActDed; //+ $TotalInterest




                                                                                                              $TotalInterest=$BillTotal*($InterestRate/100);
                                                                                                              $TotalInterest=($TotalInterest/360)*$CreditDays;
                                                                                                              $TotalInterest=round($TotalInterest,2);

                                                                                                              $BillTotal =$BillTotal+$TotalInterest;
                                                                                                              */


        // $BillTotal = $BillPrice + $AccDed - $LabActDed;

        $TdsAmount = ($BillTotal * $Tdsperc) / 100;

        // comment after bill creation updated
        if (
            $BillPanNumber != '' &&
            strlen($BillPanNumber) == 10
        ) {
            $TDSperc = '0.1%';
            $TdsAmount = ($BillTotal * 0.1) / 100;
        } else {
            $TDSperc = '5%';
            $BillPanNumber = 'NA';
            $TdsAmount = ($BillTotal * 5) / 100;
        }

        $totalBill = $BillTotal + $TotalInterest;

        $content .= '<tr>  
				<th  align="center"  width="80%"><font size="10"><B> Bill Total</B></font></th>
				<th  align="right"  width="20%"><font size="10">Rs. <B>' . round($BillTotal) . ' /- </b> </font></th>
				</tr>';

        //echo $TdsAmount; exit; 
        $content .= '<tr>  
           		<th  align="left"  width="20%"><font size="10"><b>TDS </b></font></th>	
           		<th  align="center"  width="45%"><font size="10">PAN Number : ' . $BillPanNumber . '</font></th>
           		<th  align="center"  width="15%"><font size="10">' . $TDSperc . '</font></th>
           		
           		<th  align="right"  width="20%"><font size="10"><B>' . round($TdsAmount) . ' /- </b> </font></th>
           		
               </tr>';
        // $BillTotal = $BillTotal + $TotalInterest - $TdsAmount; // 26-04-2024 durgaPrasad $TotalInterest
        $BillTotal = $BillTotal - $TdsAmount;
    } else {
        //$BillTotal=$PURCHASE_AMOUNT + $DefectsAmount +$HonUsedAmount + $AccDeductions -$LabDeduction;
        $BillTotal = $BillPrice + $AccDed + $TotalInterest - $LabActDed; // 26-04-2024 durgaPrasad $TotalInterest
        // $BillTotal = $BillPrice + $AccDed  - $LabActDed;
    }

    $content .= ' <tr>  
           		<th  align="center"  width="80%"><font size="10"><B> Net Payable</B></font></th>
           		
           		<th  align="right"  width="20%"><font size="10">Rs. <B>' . round($BillTotal) . ' /- </b> </font></th>
           		
               </tr>
				
				
			   
                <tr>  
           		<th  align="center"  width="20%"><font size="10"> <br>   Pur.Mgr   <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br>   Pur.Authorised <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br>   Plant Mgr  <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br>   Account Mgr <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br>   Authorised  <br></font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="10"><U><B>Bank Details :</B></U><br><B> Bank Name:</B>' . $FinancierBankName . '
				<br><B> Bank Account No:</B>' . $FinancierBankAccNo . '<br><B> IFSC:</B>' . $FinancierIFSC . '</font></th>
			
                
				</tr>
				
				</table>';

    return $content;
}

function getPurchaseTable($iconn, $LotID)
{
    $SqlQuery = "SELECT *,PurchaseType,
(SELECT sum(HLWeight) from PpcHLGradingBreakup WHERE LotID=RMD.RmLotID )as HlWeight
FROM RmLotDetatils as RMD
left join RmPurchaseStations As RPS On RPS.PurchaseStationID=RMD.PurchaseStationID
left join PpcHLGradingDetails as HLG on HLG.LotID=RMD.RmLotID
left join RmPurchaseCategory as RPC on RPC.CatID=RMD.PurchaseCategoryID
where RmLotID='$LotID'";
    $StmtQuery = $iconn->query($SqlQuery);
    $RowLotDetails = $StmtQuery->fetch_array();

    $PurchaseDate = date("d-m-Y", strtotime($RowLotDetails['PurchaseDate']));
    $GradingDate = date("d-m-Y", strtotime($RowLotDetails['GradingDate']));
    $StationName = $RowLotDetails['StationName'];
    ;
    $LotNumber = $RowLotDetails['LotNumber'];
    $PurchaseType = $RowLotDetails['PurchaseType'];
    $BreakTime = $RowLotDetails['BreakTime'];
    $HlWeight = $RowLotDetails['HlWeight'];
    $RmReferenceID = $RowLotDetails['RmReferenceID'];
    $GradingTime = $RowLotDetails['StartTime'] . ' - ' . $RowLotDetails['EndTime'];
    $TimeGap = strtotime($RowLotDetails['EndTime']) - strtotime("9:30");

    $TimeMin = floor($TimeGap / 60);
    $TimeGap = $TimeMin - $BreakTime;

    $HlWeightPerMin = round($HlWeight / $TimeGap);
    $HlWeightPerHour = $HlWeightPerMin * 60;

    if (($TimeGap / 60) > 1) {
        $TimeHours = $TimeGap / 60;
    } else {
        $TimeHours = 0;
    }

    $TimeMin = $TimeGap % 60;

    $TimeGap = round($TimeHours) . ' Hour : ' . $TimeMin . ' Min';


    $c = 100 / 7;
    $c = $c . '%';
    $text = 9;

    $content .= '<table border="1" cellspacing="0" cellpadding="3">  
				
			    <tr>            		
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Purchase Date</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Center</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Bill No</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Grading Date</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Grading Time</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Time Taken</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Avg Weight <br>(Hl Kg/Hour)</B></font></th>
                </tr>
				
				
				
				<tr>  
           		
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B style="color:red">' . $PurchaseDate . '</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"> ' . $StationName . '</font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"> <B>' . $LotNumber . '<br>' . $PurchaseType . '</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>' . $GradingDate . '</B></font></th>
                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '"><B >' . $GradingTime . '</B></font></th>
                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '"><B >' . $TimeGap . '</B></font></th>
                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '"><B >' . $HlWeightPerHour . '</B></font></th>
            
				</tr>';

    $content .= '</table>';






    return $content;
}

function getBillTable($iconn, $LotID)
{



    $c = 100 / 8;
    $c = $c . '%';
    $text = 9;

    $SqlBillDetails = "SELECT *,RMFL.PanNumber,PurchaseDate,RBD.ArrivalID,
			(SELECT Sum(Weight*Rate) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  ) as BillPrice,
			(SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
			and (Description='HEAD ON' or  Description='Head On Packed') ) as HonTotal,
			(SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
			and (Description!='HEAD ON' and  Description!='Head On Packed') ) as DefTotal
			
			FROM RmBillDetails as RBD 
			left join RmLotDetatils as RMD on RMD.RmReferenceID=RBD.ArrivalID
			left join QcRmOrgnReport as RQR on RMD.RmLotID=RQR.LotID
			left join RmSuppliers as RMFL on RMFL.ID=RBD.FinancierID
			
			WHERE RMD.RmLotID='$LotID'";
    $BillDetailsExec = $iconn->query($SqlBillDetails);
    $RowBillDetails = $BillDetailsExec->fetch_array();

    // echo json_encode($RowBillDetails, JSON_PRETTY_PRINT);
    // exit;
    $Yield = $RowBillDetails['Yield'];
    $ArrivalID = $RowBillDetails['ArrivalID'];




    $SqlBillBrk = "SELECT *,DefectName FROM RmBillBreakup  
							LEFT join RmDefectsList on RmBillBreakup.Description=RmDefectsList.DefectID 
			where ArrivalID='$ArrivalID'";
    $BillBrkExec = $iconn->query($SqlBillBrk);




    $content .= '<table border="1" cellspacing="0" cellpadding="3">
				<tr>            		
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Description</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Machine</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Weight</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Count</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Rate</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Yield</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Add/Deductions</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Amount</B></font></th>
                </tr>';

    while ($RowBillBrkDetails = $BillBrkExec->fetch_array()) {
        if ($RowBillBrkDetails['DefectName'] == '') {
            $Description = $RowBillBrkDetails['Description'];
        } else {
            $Description = $RowBillBrkDetails['DefectName'];
        }
        $Weight = $RowBillBrkDetails['Weight'];
        $Count = $RowBillBrkDetails['Count'];
        $Rate = $RowBillBrkDetails['Rate'];
        $Amount = ($Weight * $Rate);


        $content .= '<tr>  
			
				
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Description . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Weight . '</font></th>            
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Count . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Rate . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Yield . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
				
				 <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>Rs. ' . round($Amount) . ' </B></font></th>
               
               </tr>';
    }




    $LabDeductionsKg = $RowBillDetails['ActualDeductions'];
    $AccDeductionsKg = $RowBillDetails['Deductions'];
    $HonTotal = $RowBillDetails['HonTotal']; //Excluding Defects
    $DefTotal = $RowBillDetails['DefTotal']; //Excluding Hon And HlHon
    $TotalWeight = $HonTotal + $DefTotal;
    $BillPrice = $RowBillDetails['BillPrice'];
    $BillPanNumber = $RowBillDetails['PanNumber'];
    $PurchaseDate = $RowBillDetails['PurchaseDate'];
    $CreditRemarks = $RowBillDetails['CreditRemarks'];
    $CreditRemarks = $RowBillDetails['CreditRemarks'];
    $InterestRate = $RowBillDetails['InterestRate'];
    $CreditDays = $RowBillDetails['CreditDays'];
    if ($RowBillDetails['FinancialDate']) {
        $DueDate = date('d-m-Y', strtotime($RowBillDetails['FinancialDate']));
    } else {
        $DueDate = '';
    }

    $LabDeductions = round($HonTotal * $LabDeductionsKg);
    $AccDeductions = round($HonTotal * $AccDeductionsKg);

    //$BillPrice=$BillPrice+$AccDeductions-$LabDeductions; 

    if ($PurchaseDate > '2021-06-30') {
        $BillTotal = $BillPrice + $AccDeductions - $LabDeductions;
        $TdsAmount = ($BillTotal * $Tdsperc) / 100;

        $aryInterestRequest['InterestRate'] = $InterestRate;
        $aryInterestRequest['CreditDays'] = $CreditDays;
        $aryInterestRequest['BillTotal'] = $BillTotal;

        $aryInterestReponse = getInterestRate($aryInterestRequest);
        $TotalInterest = $aryInterestReponse['TotalInterest'];

        $BillTotal = $BillTotal + $TotalInterest;

        if (
            $BillPanNumber != '' &&
            strlen($BillPanNumber) == 10
        ) {
            $TDSperc = '0.1%';
            $TdsAmount = ($BillTotal * 0.1) / 100;
        } else {
            $TDSperc = '5%';
            $BillPanNumber = 'NA';
            $TdsAmount = ($BillTotal * 5) / 100;
        }
        $content .= '';
        // 	$BillTotal = $BillTotal - $TdsAmount;
        // } else {
        // 	$BillTotal = $BillPrice + $AccDeductions - $LabDeductions;
        // }






        $content .= '<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>Lab</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>

                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B></B></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>- ' . $LabDeductionsKg . '</B></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>-' . $LabDeductions . '</B></font></th>
                </tr>
				<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>Accounts</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>

                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '"></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B></B></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $AccDeductionsKg . '</B></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $AccDeductions . '</B></font></th>
                </tr>
				
				<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>Credit</B></font></th>
                <th  align="CENTER" colspan="3"  ><font size="' . $text . '">' . $CreditRemarks . '</font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"> <b>Due Date</b> : </font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $DueDate . '</font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $InterestRate . ' % </B></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $TotalInterest . '</B></font></th>
                </tr>
				<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" colspan="5"><font size="' . $text . '"><b>Bill Total</b> </font></th>

				<th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . round($BillTotal) . '</B></font></th>
                </tr>
				<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>TDS</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '">PAN Number  </font></th>

                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '">' . $BillPanNumber . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B></B></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $TDSperc . '</B></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>-' . round($TdsAmount) . '</B></font></th>
                </tr>';

        $BillTotal = $BillTotal - $TdsAmount;
    } else {
        $BillTotal = $BillPrice + $AccDeductions - $LabDeductions;
    }
    $content .= '<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>Total</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '">' . round($TotalWeight, 2) . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B></B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>Rs. ' . round($BillTotal) . '</B></font></th>
                </tr>';


    $content .= '</table>';

    return $content;
}

function getPackingBreakUp($iconn, $LotID)
{

    global $RmReferenceID;

    $SqlHlBrk = "select RmReferenceID from RmLotDetatils where RmLotID='$LotID'";
    $HlExec = $iconn->query($SqlHlBrk);
    $RowHlData = $HlExec->fetch_array();
    $ArrivalID = $RowHlData['RmReferenceID'];

    $SqlHlBrk = "SELECT *,
			(select ROUND(AVG(Yield),2) from RmBillBreakup where ArrivalID='$ArrivalID') as Yield,
			(select sum(HLWeight) from PpcHLGradingBreakup where LotID='$LotID') as HLWeightTotal,
			(select PurchaseCount from RmLotDetatils where RmLotID='$LotID') as PurchaseCount,
			(select PurchaseDate from RmLotDetatils where RmLotID='$LotID') as PurchaseDate
			FROM `PpcHLGradingBreakup` where LotID='$LotID'";
    //ECHO  $SqlHlBrk ; EXIT;
    $HlExec = $iconn->query($SqlHlBrk);

    $size = 40;
    $size1 = 60;
    $textw = '30%';
    $text = 9;
    $Sno = 0;

    $content .= '<table border="1" cellspacing="0" cellpadding="3">
				<tr> 
				<th  align="CENTER" width="5%"><font size="' . $text . '"> <B>S.L</B></font></th>
                <th  align="CENTER" width="7%"><font size="' . $text . '"> <B>Count</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"> <B>Qty</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Estd Hon</B></font></th>
                <th  align="CENTER" width="8%"><font size="' . $text . '"><B>Packed %</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Estd Hon Ct</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Estd Hon Rate</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Total Pcs</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Avg Hon Ct</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Pur Ct</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Diff</B></font></th>
                </tr>';


    while ($RowHlData = $HlExec->fetch_array()) {


        $HLCount = $RowHlData['HLCount'];
        $HLWeight = $RowHlData['HLWeight'];
        $Yield = $RowHlData['Yield'];
        $HLWeightTotal = $RowHlData['HLWeightTotal'];
        $PurchaseCount = $RowHlData['PurchaseCount'];
        $PurchaseDate = $RowHlData['PurchaseDate'];

        $Pcs = $HLWeight * $HLCount * 2.2;
        $EstdHon = ($HLWeight / $Yield) * 100;
        $Packed = ($HLWeight / $HLWeightTotal) * 100;
        $EstdHonCt = ($Pcs / $EstdHon);
        $TotalEstdHon += $EstdHon;
        $TotalPacked += $Packed;
        $TotalPcs += $Pcs;

        $EstdHonRate = getPrice($iconn, $PurchaseDate, $EstdHonCt);
        $EstdHonRate = $EstdHon * $EstdHonRate;
        $TotalExpectedPrice += $EstdHonRate;

        $content .= '<tr>
					
				<th  align="CENTER" width="5%"><font size="' . $text . '"> ' . ++$Sno . '</font></th>
				<th style="color:red;" align="CENTER" width="7%"><font size="' . $text . '"> <b>' . $HLCount . ' </b></font></th>
				<th  align="CENTER" width="10%"><font size="' . $text . '">' . $HLWeight . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '">' . ROUND($EstdHon, 2) . '</font></th>
                <th  align="CENTER" width="8%"><font size="' . $text . '">' . ROUND($Packed, 2) . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '">' . ROUND($EstdHonCt, 2) . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '">' . ROUND($EstdHonRate, 2) . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '">' . ROUND($Pcs, 2) . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B></B></font></th>
                </tr>';
        $c++;
    }

    $AvgCount = $TotalPcs / $TotalEstdHon;
    $DiffCount = $PurchaseCount - $AvgCount;

    $content .= '
				
				<tr>  
           		
                <th  align="CENTER" width="5%"><font size="' . $text . '"> <B></B></font></th>
                <th  align="CENTER" width="7%"><font size="' . $text . '"> <B>TOTAL</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"> <B>' . $HLWeightTotal . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>' . round($TotalEstdHon, 2) . '</B></font></th>
                <th  align="CENTER" width="8%"><font size="' . $text . '"><B>' . round($TotalPacked, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>' . round($TotalExpectedPrice, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>' . round($TotalPcs, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B style="color:red" >' . round($AvgCount, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B style="color:red">' . round($PurchaseCount, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B style="color:red">' . round($DiffCount, 2) . '</B></font></th></tr>
								 
				
				
		</table>';

    return $content;
}

function getCosingData($iconn, $LotID)
{
    $text = 9;
    $width = 25;

    $SqlHlBrk = "select RmReferenceID from RmLotDetatils where RmLotID='$LotID'";
    $HlExec = $iconn->query($SqlHlBrk);
    $RowHlData = $HlExec->fetch_array();
    $ArrivalID = $RowHlData['RmReferenceID'];


    $SqlHlExp = "SELECT * FROM `RmCostingExpanses` where UpdatedDate <= (select PurchaseDate from RmLotDetatils where RmLotID='$LotID')  
			order by UpdatedDate desc  LIMIT 1";
    $HlExpExec = $iconn->query($SqlHlExp);
    $ExpRow = $HlExpExec->fetch_array();

    $ExchangeRate = $ExpRow['ExchangeRate'];
    $ExpanseKg = $ExpRow['Expanse'];
    $Depb = $ExpRow['DEPB'];

    $SqlHlBrk = "SELECT *,
			(select ROUND(AVG(Yield),2) from RmBillBreakup where ArrivalID='$ArrivalID') as Yield,
			(select sum(HLWeight) from PpcHLGradingBreakup where LotID='$LotID') as HLWeightTotal,
			(select PurchaseCount from RmLotDetatils where RmLotID='$LotID') as PurchaseCount			
			FROM `PpcHLGradingBreakup` where LotID='$LotID'";

    $HlExec = $iconn->query($SqlHlBrk);


    $content = '<table border="1" cellspacing="0" cellpadding="3">
				
			   <tr>  
           		
                <td  align="center" width="' . ($width * 2) . '%"><font size="' . $text . '"> <B>Exchange Rate</B></font></td>
				<td  align="center" width="' . ($width * 2) . '%"><font size="' . $text . '"><B>Rs .' . $ExchangeRate . ' /-</B></font></td>             
                </tr>
				
				<tr>            		
                <td  align="center" width="' . $width . '%"><font size="' . $text . '"> <B>Count</B></font></td>
                <td  align="center" width="' . $width . '%"><font size="' . $text . '"> <B>HL QTY</B></font></td>
                <td  align="center" width="' . $width . '%"><font size="' . $text . '"> <B>USD</B></font></td>
                <td  align="center" width="' . $width . '%"><font size="' . $text . '"><B>INR</B></font></td>            
                </tr>';



    while ($GradeRow = $HlExec->fetch_array()) {

        $PackingGrade = $GradeRow['PackingGrade'];
        $HLWeight = $GradeRow['HLWeight'];
        $HLWeightTotal = $GradeRow['HLWeightTotal'];
        $Usd = $GradeRow['Usd'];
        $SalePrice = $HLWeight * $Usd * $ExchangeRate;
        $TotalSalePrice += $SalePrice;

        $content .= '
				<tr>
				 <td align="right" width="' . $width . '%"><font size="' . $text . '"> ' . $PackingGrade . '</font></td>
				 <td  align="right" width="' . $width . '%"><font size="' . $text . '"> ' . $HLWeight . '</font></td>
				 <td  align="right" width="' . $width . '%"><font size="' . $text . '"> ' . $Usd . '</font></td>
				 <td  align="right" width="' . $width . '%"><font size="' . $text . '"> <B>' . $SalePrice . '</B></font></td>
                </tr>';
    }



    $TotalExpanse = $HLWeightTotal * $ExpanseKg;
    $TotalDepb = ($Depb * $TotalSalePrice) / 100;

    $SaleTotal = $TotalSalePrice + $TotalDepb;
    $SubTotal = $TotalSalePrice + $TotalDepb - $TotalExpanse;



    /* $SqlBillDetails = "SELECT *,
            (SELECT Sum(Weight*Rate) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID 
            and Description!='Head On Packed') as BillPrice,
            (SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
            and Description!='Head On Packed' ) as HonTotal
            
            FROM RmBillDetails as RBD 
            left join QcRmOrgnReport as RQR on RBD.ArrivalID=RQR.LotID
            WHERE RBD.ArrivalID='$ArrivalID'"; 
    $BillDetailsExec = $iconn->query($SqlBillDetails);
    $RowBillDetails = $BillDetailsExec->fetch_array();*/

    $SqlBillDetails = "SELECT *,RMFL.PanNumber,PurchaseDate,RBD.ArrivalID,
	(SELECT Sum(Weight*Rate) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  ) as BillPrice,
	(SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
	and (Description='HEAD ON' or  Description='Head On Packed') ) as HonTotal,
	(SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
	and (Description!='HEAD ON' and  Description!='Head On Packed') ) as DefTotal
	
	FROM RmBillDetails as RBD 
	left join RmLotDetatils as RMD on RMD.RmReferenceID=RBD.ArrivalID
	left join QcRmOrgnReport as RQR on RMD.RmLotID=RQR.LotID
	left join RmSuppliers as RMFL on RMFL.ID=RBD.FinancierID
	
	WHERE RMD.RmLotID='$LotID'";
    $BillDetailsExec = $iconn->query($SqlBillDetails);
    $RowBillDetails = $BillDetailsExec->fetch_array();




    $LabDeductionsKg = $RowBillDetails['ActualDeductions'];
    $AccDeductionsKg = $RowBillDetails['Deductions'];
    $HonTotal = $RowBillDetails['HonTotal']; //Excluding Defects
    $BillPrice = $RowBillDetails['BillAmount'];
    $InterestRate = $RowBillDetails['InterestRate'];
    $CreditDays = $RowBillDetails['CreditDays'];

    $LabDeductions = round($HonTotal * $LabDeductionsKg);
    $AccDeductions = round($HonTotal * $AccDeductionsKg);

    $BillPrice = $BillPrice + $AccDeductions - $LabDeductions;

    $aryInterestRequest['InterestRate'] = $InterestRate;
    $aryInterestRequest['CreditDays'] = $CreditDays;
    $aryInterestRequest['BillTotal'] = $BillPrice;

    $aryInterestReponse = getInterestRate($aryInterestRequest);
    $TotalInterest = $aryInterestReponse['TotalInterest'];

    // $BillPrice=$BillPrice+$TotalInterest;

    $SubTotal = $SubTotal - $TotalInterest;

    /*	
            if($PurchaseDate > '2021-06-30'){
                   $BillTotal=$BillPrice+$AccDeductions-$LabDeductions; 			       
                   $TdsAmount=($BillTotal*$Tdsperc)/100;
                   if($BillPanNumber !='' && 
                   strlen($BillPanNumber) ==10){
                      $TDSperc='0.1%'; 
                     $TdsAmount=($BillTotal*0.1)/100;  
                   }else{
                       $TDSperc='5%';
                       $BillPanNumber='NA';
                      $TdsAmount=($BillTotal*5)/100;
                   }
               $content.='';
               $BillTotal=$BillTotal-$TdsAmount;
                
               }else{
                  $BillTotal=$BillPrice+$AccDeductions-$LabDeductions; 
               }
               
               */
    //echo $SubTotal; 
    $SaleStatus = round($SubTotal - $BillPrice);

    $TransactionKg = $SaleStatus / $HLWeightTotal;

    $AvgHlInr = $SubTotal / $HLWeightTotal;
    $AvgHlUsd = $AvgHlInr / $ExchangeRate;


    if ($SaleStatus > 0) {
        $Transaction = 'Gain';
    } else {
        $Transaction = 'Loss';
    }



    $content .= '
				<tr>  
           		
                <td  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Sale Value</B></font></td>
                <td  align="right" width="' . $width . '%"><font size="' . $text . '"> <B>' . $HLWeightTotal . '</B></font></td>
                <td  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> <B></B></font></td>
                <td  align="right" width="' . $width . '%"><font size="' . $text . '"> <B>' . ($TotalSalePrice) . '</B></font></td>
                </tr>

				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>DEPB</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"><B> ' . $Depb . ' %</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> + ' . round($TotalDepb, 2) . '</B></font></th>
                </tr>

				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Sub Total</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B></B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> ' . round($SaleTotal, 2) . '</B></font></th>
                </tr>

				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Rm Value</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"><B>' . $HonTotal . '</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> -' . round($BillPrice) . '</B></font></th>
                </tr>

				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Interest</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"><B>' . $InterestRate . ' %</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> -' . round($TotalInterest) . '</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Expanse</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B>' . ($ExpanseKg) . ' / kg</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> <B></B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> - ' . ($TotalExpanse) . '</B></font></th>
                </tr>
				
				
				
				

				
				
				<tr>            		
                <th align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Total ' . $Transaction . '</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">
				<B style="color:red"> ' . round($SaleStatus, 2) . '</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>' . $Transaction . ' /Kg</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">Per Kg</font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">
				<B style="color:red"> ' . round($TransactionKg, 2) . '</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Average </B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">INR/HL </font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">
				<B style="color:red"> ' . round($AvgHlInr, 2) . '</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Average</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> USD </font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">
				<B style="color:red"> ' . round($AvgHlUsd, 2) . '</B>
                
				</font></th>
				
				</tr>
				
				</table>';



    return $content;
}


function getPrice($iconn, $PurchaseDate, $FINAL_COUNT)
{



    $REExpCount = 'NA';
    $RmPrice = 'NA';




    $SqlTrackId = "SELECT * FROM `RmPurchaseRatesHistory` where RmPriceDate <= '$PurchaseDate 23:59:59' 
		order by RmPriceDate DESC, ID DESC  LIMIT 1";
    $StmtTrackId = $iconn->query($SqlTrackId);
    $RowTrackId = $StmtTrackId->fetch_array();
    $TARCK_ID = $RowTrackId['ID'];
    $RmPriceDate = $RowTrackId['RmPriceDate'];

    $FINAL_COUNT;

    if ($FINAL_COUNT > 99) {
        $temp_c = 100;
        $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";
        $FINAL_COUNT = round($FINAL_COUNT);
    } else if ($FINAL_COUNT > 52.25 && $FINAL_COUNT < 60.01) {
        $FINAL_COUNT = ceil($FINAL_COUNT);
        $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
    } else if ($FINAL_COUNT > 62.25 && $FINAL_COUNT < 70.01) {
        $FINAL_COUNT = ceil($FINAL_COUNT);
        $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
    } else if ($FINAL_COUNT > 72.25 && $FINAL_COUNT < 80.01) {
        $FINAL_COUNT = ceil($FINAL_COUNT);
        $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
    } else if ($FINAL_COUNT > 82.25 && $FINAL_COUNT < 90.01) {
        $FINAL_COUNT = ceil($FINAL_COUNT);
        $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
    } else if ($FINAL_COUNT > 92.25 && $FINAL_COUNT < 100) {
        $FINAL_COUNT = ceil($FINAL_COUNT);
        $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
    } else {

        $TEMP_DIFF = $FINAL_COUNT - round($FINAL_COUNT);

        if (abs($TEMP_DIFF) > 0.25 && abs($TEMP_DIFF) < 0.76) {

            $TEMP_DIFF;
            if ($TEMP_DIFF > 0) {
                $LC = round($FINAL_COUNT);
                $UC = ceil($FINAL_COUNT);
            } else {
                $LC = round($FINAL_COUNT) - 1;
                $UC = round($FINAL_COUNT);
            }

            $sql_acr = "SELECT SUM(RmPrice/2) as RmPrice,SUM(RmCount/2) AS COUNT FROM RmPurchaseRates
				WHERE DateID='$TARCK_ID' and RmCount >='$LC' and RmCount <='$UC'";
        } else {

            $temp_c = round($FINAL_COUNT);
            $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";
        }
    }

    //echo $sql_acr;


    $stmt_acr = $iconn->query($sql_acr);
    $row = $stmt_acr->fetch_array();
    $RmPrice = round($row['RmPrice'], 2);
    /*$data['RmPriceDate']=$RmPriceDate;
         $data['RmPrice']=$RmPrice;
         $data['BillHonCount']=$FINAL_COUNT;*/
    //return	 $data;

    if ($FINAL_COUNT > 99) {
        $DIFF = $FINAL_COUNT - 100;
        $RmPrice = $RmPrice - $DIFF;
    }
    return $RmPrice;
}

function getInterestRate($aryInterestRequest)
{

    $InterestRate = $aryInterestRequest['InterestRate'];
    $CreditDays = $aryInterestRequest['CreditDays'];
    $BillTotal = $aryInterestRequest['BillTotal'];

    $interestPerLakh = 100000 * ($InterestRate / 100);
    $interestPerLakh = round($interestPerLakh / 360);
    $TotalInterest = ($BillTotal / 100000) * $interestPerLakh;
    $TotalInterest = $TotalInterest * $CreditDays;
    $TotalInterest = round($TotalInterest);

    $aryInterestReponse['TotalInterest'] = $TotalInterest;
    return $aryInterestReponse;
}
