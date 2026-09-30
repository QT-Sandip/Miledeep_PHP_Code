<?php



include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';

require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed



$api = API_MONTHLY_SLABS_PRODUCTION_REPORT;

$plant = $_GET["plant"];

$fromDate = $_GET["fromDate"];

$toDate = $_GET["toDate"];

$month = $_GET["month"];

$year = $_GET["year"];



$Type = trim($_GET["dayMonthSelection"]);

$bodyContent = array(

    "FromDate" => trim($_GET["fromDate"]),

    "ToDate" => trim($_GET["toDate"]),

    "Species" => trim($_GET["species"]),

    "ProductionPlant" => trim($_GET["productionPlant"]),

    "TypeProduct" => trim($_GET["TypeProduct"]),

    "FreezingType" => trim($_GET["freezingType"]),

    "Month" => trim($_GET["month"]),

    "Year" => trim($_GET["year"]),

    "Type" => trim($_GET["dayMonthSelection"]),

    "ColdStore" => trim($_GET["coldStore"]),

    "SlabType" => trim($_GET["slabType"]),

);

if ($Type == "DayWise") {

    $typeWise = '_Date(' + $fromDate + ' - ' + $toDate + ')';

} else if ($Type == "MonthWise") {

    $typeWise = '_for (' + $month + '-' + $year + ')';

}

$heading = "Daily and Monthly Report Plant ".$plant;

$FileName = "Daily and Monthly Report";

header("Content-type: application/vnd.ms-excel; charset=UTF-8");

header('Content-Disposition: attachment; filename=' . $FileName . $typeWise . '.xlsx');

$resList = CallAPI($api, "GET", "", $bodyContent);



$DayWiseData = $resList->DayWiseData;

$Dates = $resList->Dates;

$TypeProductDateWiseData = $resList->TypeProductDateWiseData;

$DateWiseGrandTotals = $resList->DateWiseGrandTotals;

// echo $DateWiseGrandTotals->TypeProduct;

// echo json_encode($DateWiseGrandTotals, JSON_PRETTY_PRINT);

// exit;



$MonthWiseData = $resList->MonthWiseData->MonthWiseArrayData;

$MonthWiseTotals = $resList->MonthWiseTotals;

$GrandTotalGrossweight = $resList->MonthWiseData->GrandTotalGrossweight;

$GrandTotalSlabs = $resList->MonthWiseData->GrandTotalSlabs;

$TypeProductData = $resList->TypeProductData;



// Create PHPExcel object

$spreadsheet = new PHPExcel();

$sheet = $spreadsheet->getActiveSheet();



// Set Font Size

$spreadsheet->getDefaultStyle()->getFont()->setSize(10);

$HeaderCount = Count($Dates)+7;

// Set Heading

$HeaderColIndex = "A";

$HeaderEndIndex = PHPExcel_Cell::columnIndexFromString($HeaderColIndex)+($HeaderCount -1) ;

$HeaderEndCol = PHPExcel_Cell::stringFromColumnIndex($HeaderEndIndex);

$sheet->mergeCells("A1:".$HeaderEndCol."1");

$sheet->setCellValue('A1', $heading);

$sheet->getStyle("A1:".$HeaderEndCol."2")->getFont()->setBold(true);

$sheet->getStyle("A1:".$HeaderEndCol."2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



if($Type == "DayWise"){

    $headers = ['SNo', 'Species', 'Variety', 'Type Product', 'Packing Style', 'Treatment'];

    // Add Header Row to Excel

    $colIndex = 'A';

    foreach ($headers as $header) {

        $sheet->setCellValue($colIndex . '2', $header);

        $colIndex++;

    }

    foreach ($Dates as $date) {

        $sheet->setCellValue($colIndex . '2', $date);

        $colIndex++;

    }

    $sheet->setCellValue($colIndex . '2', 'Total Slabs');



    //Fill Data

    $row = 3;

    foreach ($DayWiseData as $item) {

        $col = 'A'; // Start with column A

        $sheet->setCellValue($col++ . $row, $item->SNo);

        $sheet->setCellValue($col++ . $row, $item->Species);

        $sheet->setCellValue($col++ . $row, $item->Species . '-' . $item->ShortName);

        $sheet->setCellValue($col++ . $row, $item->TypeProduct);

        $sheet->setCellValue($col++ . $row, $item->PackingStyle);

        $sheet->setCellValue($col++ . $row, $item->SockingType);

        foreach ($item->SlabsData as $ItemBreakUp){

            $sheet->setCellValue($col . $row, cleanNumber($ItemBreakUp->TotalSlabs));

            $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        }

        $row++;

    }

    $styleStartIndex = $row;

    $col = 'G'; // Start with column G

    foreach ($TypeProductDateWiseData as $ItemData){

        $sheet->setCellValue("C" . $row, "Total ".$ItemData->TypeProduct." Production");

        $sheet->mergeCells("C".$row.":D".$row);

        foreach ($ItemData -> TypeProductData as $ItemDataBreakup){

            $sheet->setCellValue($col . $row, cleanNumber($ItemDataBreakup->TypeWiseTotalSlabs));

            $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        }

        $col = 'G'; // Reset to column G

        $row++;

    }

    $col = 'G'; // Start with column G

    foreach ($DateWiseGrandTotals[0]->TypeProductData as $ItemDataBreakUps){

        $sheet->setCellValue("C" . $row,$DateWiseGrandTotals[0]->TypeProduct);

        $sheet->mergeCells("C".$row.":D".$row);

        $sheet->setCellValue($col. $row, cleanNumber($ItemDataBreakUps->TypeWiseTotalSlabs));

        $sheet->getStyle($col++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

    }

    $sheet->getStyle("C".$styleStartIndex.":".$HeaderEndCol.$row)->getFont()->setBold(true);

}else if($Type == "MonthWise"){

    $headers = ['SNo', 'Species', 'Variety', 'Type Product', 'Packing Style', 'Treatment', 'No Of Slabs', 'Gross Weight'];

    // Add Header Row to Excel

    $colIndex = 'A';

    foreach ($headers as $header) {

        $sheet->setCellValue($colIndex . '2', $header);

        $colIndex++;

    }



    $row = 3;

    foreach ($MonthWiseData as $item){

        $colIndex = 'A';

        $sheet->setCellValue($colIndex++ . $row, $item->SNo);

        $sheet->setCellValue($colIndex++ . $row, $item->Species);

        $sheet->setCellValue($colIndex++ . $row, $item->Species . '-' . $item->ShortName);

        $sheet->setCellValue($colIndex++ . $row, $item->TypeProduct);

        $sheet->setCellValue($colIndex++ . $row, $item->PackingStyle);

        $sheet->setCellValue($colIndex++ . $row, $item->SockingType);

        $sheet->setCellValue($colIndex . $row, cleanNumber($item->TotalSlabs));

        $sheet->getStyle($colIndex++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue($colIndex . $row, cleanNumber($item->GrossWeight));

        $sheet->getStyle($colIndex++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $row++;

    }

    $styleStartIndex = $row;

    foreach ($MonthWiseTotals as $index => $ItemMonthwiseTotals){

        $colIndex = 'G';

        $sheet->setCellValue("B" . $row, "Total ".$index." Production");

        $sheet->mergeCells("B".$row.":D".$row);

        $sheet->setCellValue( $colIndex . $row, cleanNumber($ItemMonthwiseTotals->TotalSlabs));

        $sheet->getStyle($colIndex++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->setCellValue( $colIndex . $row, cleanNumber($ItemMonthwiseTotals->GrossWeight));

        $sheet->getStyle($colIndex++ . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



        $row++;

    }

    $sheet->setCellValue("B" . $row, "Grand Totals");

    $sheet->setCellValue("G" . $row, cleanNumber($GrandTotalSlabs));

    $sheet->getStyle("G" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

    $sheet->setCellValue("H" . $row, cleanNumber($GrandTotalGrossweight));

    $sheet->getStyle("H" . $row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

    $sheet->getStyle("B".$styleStartIndex.":".$HeaderEndCol.$row)->getFont()->setBold(true);

}

// Column Widths

foreach (range(0, $HeaderEndIndex) as $columnID) {

    $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($columnID))->setAutoSize(true);

}

ob_clean();

// Write to Output

$writer = new PHPExcel_Writer_Excel2007($spreadsheet);

$writer->save('php://output');

function cleanNumber($num) {

    return floatval(str_replace([',', "'"], '', $num)); // Remove comma & apostrophe, convert to number

}