<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Libraries/PHPExcel/Classes/PHPExcel.php';
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

if (isset($_GET['FromDate']) && $_GET['FromDate'] && isset($_GET['ToDate']) && $_GET['ToDate'] ) {
    $StartDate = $_GET['FromDate'];
    $EndDate = $_GET['ToDate'];
    
} else {
    echo "Bad request";
    exit;
}
$displayBrandColumn = ($_SESSION['ClientID'] == 'DFLV3') ? 1 : 0;
$api = API_STORE_ON_FLOOR_STOCK;

    $ColdstoreID = trim($_GET["coldStore"]);
    $StockBlgsTo = trim($_GET["TenantID"]);
    $VarietyID = trim($_GET["variety"]);
    $Grade = trim($_GET["grade"]);
    $PackingStyle = trim($_GET["pckgStyle"]);
    $PackingType = trim($_GET["pckgType"]);
    $StockType = trim($_GET["stockType"]);
    $ShortName = trim($_GET["fShortName"]);
    $PoNumber = trim($_GET["PoNumber"]);
    $Species = trim($_GET["species"]);
    $TypeProduct = trim($_GET["typeProduct"]);
    $freezingType = trim($_GET["freezingType"]);
    $blocks = trim($_GET["blocks"]);
    $Brand = trim($_GET["drpBrand"]);

    $PageNo = trim($_GET["pageNo"]);
    $SearchKey = trim($_GET["searchKey"]);
    $LoadAll = trim($_GET["loadAll"]);


    $bodyContent = array(
        'ColdstoreID' => $ColdstoreID,
        'TenantID' => $StockBlgsTo,
        'VarietyID' => $VarietyID,
        'PoGradeId' => $Grade,
        'PackingStyleID' => $PackingStyle,
        'PackingType' => $PackingType,
        'StockType' => $StockType,
        'Species' => $Species,
        'ShortName' => $ShortName,
        'TypeProduct' => $TypeProduct,
        'PoNumber' => $PoNumber,
        'FreezingType' => $freezingType,
        'Blocks' => $blocks,
        'PageNo' => 1,
        'SearchKey' => $SearchKey,
        'LoadAll' => 0,
        "ProductBatchCode" => $ProductBatchCode,
        "StartDate" => $StartDate,
        "EndDate" => $EndDate,
         'Brand' => $Brand,
    );


    $resList = CallAPI($api, "GET", "", $bodyContent);
    $List = $resList->Data;
    //echo "<pre>"; print_r($resList); exit;


// Create PHPExcel object
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("Store on Floor Stock");

    $row = 1;

    // Header Information
    $sheet->mergeCells("A$row:P$row")->setCellValue("A$row", "Store on Floor Stock from ".$StartDate." to ".$EndDate);
    $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(20);
    $sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($row)->setRowHeight(30); // Adjust as needed

    $row++;

     $headers = [
        "S.No", "BID","Date","Track Code","Group","Variety", "Grade",
        "Soaking", "Species", "Packing Type","Brand Name", "Packing style", "Category","AB Status",
        "Po Number","MCS","Loose", "Store"
    ];

    $columnWidths = [
        5,
        9,
        10,
        23,
        10,
        10,
        10,
        10,
        10,
        10,
        10,
        10,
        15,
        15,
        25,
        10,
        10,
        10
    ];

    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->getFont()->setBold(true)->setSize(10);
        $col++;
    }
    $sheet->getRowDimension($row)->setRowHeight(20);
    $colIndex = 'A';
    foreach ($columnWidths as $width) {
        $sheet->getColumnDimension($colIndex)->setWidth($width);
        $colIndex++;
    }

    $row++;

    foreach ($List as $data) {
        $values = [
            $data->SNO,
            $data->BarCodeID,
            date('d-m-Y', strtotime($data->ProductionDate)),
            $data->TrackCode,
            $data->ProductionTypeID,
            ($data->Species . '-' . $data->VarietyName) ,
            $data->Grade,
            $data->SockingType,
            $data->Species,
            $data->PackingTypeID,
            $data->BrandName,
            $data->PackingStyle,
            $data->ProductCategoryID,
            $data->AbStatus,
            $data->PoNo . $data->LotNo,
            $data->AvalMcs,
            $data->LooseBags,
            $data->StoreName

        ];

    $col = 'A';

    foreach ($values as $index => $value) {
        $sheet->setCellValue($col . $row, $value);

        $val = trim(str_replace(',', '', $value));



        if ($col == 'L') {
            if (strtolower($val) === "positive") {
                $color = "FF0000";
            }
        }

        if ($color != "") {
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB($color);
        }

        $col++;
    }

    $sheet->getStyle("A$row:AQ$row")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(-1);
        
    $row++;
}

header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment;filename=Store-on-Floor-Stock_".$StartDate."_".$EndDate.".xls");
header('Cache-Control: max-age=0');
ob_end_clean();

// Write file to output
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>
