<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

 $sqlStockData = "SELECT StockID,AvalMcs,AvalKgs as OldAvalKgs,(AvalMcs*TotalSlabs*SlabWeight*MFactor) as AvalMCSKgs,(LooseBags*SlabWeight*MFactor) as AvalLooseKgs FROM `CsStockData` as CSD  
left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID ORDER BY `StockID` ASC"; 
$stmtStockData = $iconn->query($sqlStockData);

$data = '';
while ($dataRowStockData = $stmtStockData->fetch_array()) {

    $StockID = $dataRowStockData['StockID'];
    $OldAvalKgs = $dataRowStockData['OldAvalKgs'];   
    $AvalKgs = $dataRowStockData['AvalMCSKgs']+$dataRowStockData['AvalLooseKgs'];

   
    // if($AvalKgs<$OldAvalKgs){

    // echo $OldAvalKgs.': '.$StockID.'<br>';
      $sqlUpdateAvalKgs = "UPDATE `CsStockData` SET `AvalKgs`='$AvalKgs' WHERE `StockID`='$StockID'";
    // }
    $stmtUpdateAvalKgs = $iconn->query($sqlUpdateAvalKgs);
    if ($stmtUpdateAvalKgs) {
        ECHO 'HI';
      ECHO  $data = "success Oldkgs :". $OldAvalKgs.'<br> new kgs: '.$AvalKgs;
    } 
}

echo json_encode($data, JSON_PRETTY_PRINT);
