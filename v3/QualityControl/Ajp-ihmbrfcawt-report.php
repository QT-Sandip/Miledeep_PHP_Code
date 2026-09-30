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

function fetchLookupMap($iconn, $query, $keyField, $valueFields)
{
    $result = $iconn->query($query);
    $map    = [];
    while ($row = $result->fetch_assoc()) {
        $key   = $row[$keyField];
        $value = [];
        foreach ($valueFields as $field) {
            $value[$field] = $row[$field];
        }
        $map[$key] = $value;
    }
    return $map;
}

function setColor($res, $val){
    $chkval = 20;
    if( $res == "result1" ){
        $chkval = 35;    
    }
    $color="";
    if( is_numeric($val) && $val>$chkval ){
        $color = "Red";
    }    
    return $color;
}

function setDate($date){
    if( isset($date) && $date ){
        return date("d-m-Y", strtotime($date));
    }else{
        return $date;
    }
}

$allPlants      = fetchLookupMap($iconn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations", 'ProcessingStationID', ['StationName']);
$allMedia       = fetchLookupMap($iconn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', ['MediaName']);
$allInstruments = fetchLookupMap($iconn, "SELECT InstrumentId, InstrumentName FROM instrumentmaster", 'InstrumentId', ['InstrumentName']);

//  Userlist names
$sqlQuery      = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ";
$stmtUsers     = $iconn->query($sqlQuery);
$RowUsersLists = [];
$i = 0;
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
}
//end here

$FromDate = $_GET['FromDate'];
$ToDate   = $_GET['ToDate'];

$sql           = "SELECT * from micro_biology_ice_chutes_water_tanks where 1=1 AND AnalysisDate BETWEEN '$FromDate' and '$ToDate' order by mbwic_id desc";
//echo $sql; exit;
$result = $iconn->query($sql);
while ($RowLeaveList = $result->fetch_assoc()) { 
    $i++;
    $singlerow = array();
    
    $singlerow['Sno'] = $i;       

    $singlerow['sample_drawn_by']     = $RowUsersLists[$RowLeaveList['sample_drawn_by']] ? $RowUsersLists[$RowLeaveList['sample_drawn_by']] : '';
    $singlerow['instant_swab'] = isset($RowLeaveList['instant_swab']) ? $RowLeaveList['instant_swab'] : '';
    $singlerow['sample_collection_date']  = setDate($RowLeaveList['sample_collection_date']);
    $singlerow['plant_name'] = $allPlants[$RowLeaveList['Plant_id']]['StationName'] ? $allPlants[$RowLeaveList['Plant_id']]['StationName'] : '';                    
    $singlerow['sample_desc'] = $RowLeaveList['sample_desc'];
    $singlerow['sample_collection_area'] = $RowLeaveList['sample_collection_area'];
    $singlerow['AnalysisDate'] = setDate($RowLeaveList['AnalysisDate']);
    $singlerow['Dateofcompletion'] = setDate($RowLeaveList['Dateofcompletion']);
            
    $tpc_mediaIDs   = explode(",", $RowLeaveList['tpc_media']);
    $tpc_mediaNames = array_map(function ($id) use ($allMedia) {
        return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
    }, $tpc_mediaIDs);

    $singlerow['tpc_media'] = implode(",", array_filter($tpc_mediaNames));

    //equipmentused
    $tpc_equipementIDs   = explode(",", $RowLeaveList['tpc_equipement']);
    $tpc_equipementNames = array_map(function ($id) use ($allInstruments) {
        return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
    }, $tpc_equipementIDs);

    $singlerow['tpc_equipement'] = implode(",", array_filter($tpc_equipementNames));
            
    $tpc_bgcolor = setColor('result1',$RowLeaveList['tpc_result1']);
    $tpc_class ="";    
    if($tpc_bgcolor){
        $tpc_class="redbg";
    }
    $singlerow['tpc_result']     = $RowLeaveList['tpc_result'];            
    $singlerow['tpc_completion_date']     = setDate($RowLeaveList['tpc_completion_date']);

    //equipmentused

    $vcholerae_mediaIDs   = explode(",", $RowLeaveList['vcholerae_media']);
    $vcholerae_mediaNames = array_map(function ($id) use ($allMedia) {
        return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
    }, $vcholerae_mediaIDs);

    $singlerow['vcholerae_media'] = implode(",", array_filter($vcholerae_mediaNames));
    
     $vcholerae_equipementIDs   = explode(",", $RowLeaveList['vcholerae_equipement']);
    $vcholerae_equipementNames = array_map(function ($id) use ($allInstruments) {
        return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
    }, $vcholerae_equipementIDs);

    $singlerow['vcholerae_equipement'] = implode(",", array_filter($vcholerae_equipementNames));

    $tpc_bgcolor = setColor('result1',$RowLeaveList['vcholerae_result1']);
    $tpc_class ="";    
    if($tpc_bgcolor){
        $tpc_class="redbg";
    }
    $singlerow['vcholerae_result']     = $RowLeaveList['vcholerae_result'];
    $singlerow['vcholerae_completion_date']     = setDate($RowLeaveList['vcholerae_completion_date']);
    
    $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
    $singlerow['checked_by']   = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
    $singlerow['verified_by']      = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';
    $singlerow['remarks']     = $RowLeaveList['remarks'];
        
    $rows[] = $singlerow;   
}
//echo "<pre>"; print_r($rows); exit;



if(1){
    /////// media wise

    // Create PHPExcel object
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle(str_replace($report_type," ","")." Report");

    $row = 1;

    
$sheet->mergeCells("A$row:J$row")->setCellValue("A$row", "MEDIA & EQUIPMENT");
$sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($row)->setRowHeight(30);

$row++;

    // Column Headers
    if( $newcol == 0 ){
        $headers = [
            "Sno","Analysis Date", "Sample Description", "Location", "Parameter", "Name Of The Media Used", "Equipment Used","Parameter", "Name Of The Media Used", "Equipment Used" ];
    }
    // else{
    //       $headers = [
    //         "Sno", "Analysis Date", "Sample Description", "Location","Parameter","Name Of The Media Used", "Equipment Used","Parameter","Name Of The Media Used", "Equipment Used"
    //     ];  
    // }

    $columnWidths = [5, 18, 25, 25, 20, 30, 25, 20, 30, 25];
   

    
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

    // if($newcol == 1){
    // Data Rows
        foreach ($rows as $data) {
            $values = [
                $data['Sno'], 
                 $data['AnalysisDate'],
                $data['sample_desc'], 
                $data['sample_collection_area'], 
                 "TPC",
                $data['tpc_media'], 
                $data['tpc_equipement'], 
                "V. Cholerae",
                $data['vcholerae_media'], 
                $data['vcholerae_equipement'],
            ];
            $col = 'A';
        foreach ($values as $value) {
            $sheet->setCellValue($col . $row, $value);
            if($col == 'L' || $col == 'M' || $col == 'R' || $col == 'S'){
                $color="";

                if($col == 'L' || $col == 'R'){            
                    if( is_numeric($value) && $value>35 ){
                        $color = "FF0000";
                    }
                }

                if($col == 'M' || $col == 'S'){            
                    if( is_numeric($value) && $value>20 ){
                        $color = "FF0000";
                    }
                } 

                if($color !=""){
                    $sheet->getStyle($col . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
          ->getStartColor()->setRGB($color);
                }
            }
            $col++;
        }

        $sheet->getStyle("A$row:AP$row")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(-1);
        
        $row++;
        }
    }

    
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="MediaAndEquipement.xls"');
header('Cache-Control: max-age=0');
ob_end_clean();

// Write file to output
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

?>


