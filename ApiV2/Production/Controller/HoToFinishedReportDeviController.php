<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class HoToFinishedReportDeviController
{
    private $conn;
    private $auditLogger;
    private $utilController;
    private $Error = null;
    private $Msg = 'Success';
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Production');
    }

    public function getHoToFinishedReportDetails($RequestBody)
    {
        $DataType=$RequestBody['DataType'];
        $ArrivalID = $RequestBody['ArrivalID'];

        // $PlantID = $RequestBody['PlantID'];


        $Data['Error'] = $this->Error;
        $Data['Msg'] = "Success";

        if($DataType=="getArrivals"){
           $Data['ArrivalsList'] =$this->getArrivals($RequestBody);
        }

        else if ($DataType=="getReportData"){
            if($ArrivalID!=''){
                $Data['HoToFinishedReportData'] =$this->getHoToFinishedReportData($RequestBody);

            }
            else{
                $Data['Msg'] = "ArrivalID not found";
            }

        }


        return $Data;
    }

function getArrivals($RequestBody){
    $ArrivalDate = $RequestBody['ArrivalDate'];
    $DataType=$RequestBody['DataType'];
    $PlantID = $RequestBody['PlantID'];

   $SqlArrivalsList="SELECT RmArrivalID,ArrivalNumber  FROM `RmArrivalDetatils`  WHERE ProcessingStationID='$PlantID' and ArrivalDate='$ArrivalDate'";
   $stmtArrivalsList = $this->conn->prepare($SqlArrivalsList);
   $stmtArrivalsList->execute();

   while ($dataRowArrivalsList = $stmtArrivalsList->fetch(PDO::FETCH_ASSOC)) {
$ArrayArrivalList[]=$dataRowArrivalsList;

}
return $ArrayArrivalList;

}

function getHoToFinishedReportData($RequestBody){
    $ArrivalID = $RequestBody['ArrivalID'];
//    $GradesData= $this->getGrades();


   $sqlSelect = "DROP TABLE IF EXISTS `HeadonToFinishedReportTemp`";
   $stmtSelect = $this->conn->prepare($sqlSelect);
   $stmtSelect->execute();

   $sqlCreate = "CREATE TABLE HeadonToFinishedReportTemp (
    ID INT AUTO_INCREMENT PRIMARY KEY,
     GradingBreakUpID VARCHAR(50),
    Grade VARCHAR(50),
    HlCount VARCHAR(100),
    GradingVariety VARCHAR(100),
    HlQuantity DECIMAL(10,2),
    VaQuantity DECIMAL(10,2),
    SoakingQuantity DECIMAL(10,2),
    HlToVaYieldPercentage DECIMAL(5,2),
    PickupPercentage DECIMAL(5,2),
    FreezingVariety VARCHAR(100),
    Packing VARCHAR(100),
    FinishedQuantity DECIMAL(10,2)
)";
   $stmtCreate= $this->conn->prepare($sqlCreate);
   $stmtCreate->execute();



$SqlInsert="INSERT INTO HeadonToFinishedReportTemp (
    GradingBreakUpID,
    HlCount,
    GradingVariety,
    HlQuantity,
    VaQuantity,
    SoakingQuantity,
    PickupPercentage,
    FinishedQuantity,
    HlToVaYieldPercentage,
    FreezingVariety,
    Packing,
    Grade
)
SELECT
    Sub.DataIDs AS GradingBreakUpID,
    Sub.HLCounts AS HlCount,
    Sub.Variety AS GradingVariety,
    Sub.TotalHLWeight AS HlQuantity,
    Sub.ValueAdditionWeight AS VaQuantity,
    Sub.SoakInQty AS SoakingQuantity,

    -- Pickup Percentage
    ROUND(
        CASE
            WHEN Sub.SoakInQty > 0 THEN ((Sub.ProductionWeight - Sub.SoakInQty) / Sub.ProductionWeight) * 100
            ELSE 0
        END, 2
    ) AS PickupPercentage,

    Sub.ProductionWeight AS FinishedQuantity,

    -- HL to VA Yield Percentage
    ROUND(
        CASE
            WHEN Sub.TotalHLWeight > 0 THEN (Sub.ValueAdditionWeight / Sub.TotalHLWeight) * 100
            ELSE 0
        END, 2
    ) AS HlToVaYieldPercentage,

    Sub.FreezingVariety,
    Sub.Packing,
    Sub.PackingGrade AS Grade

FROM (
    SELECT
        PHGB.PackingGrade,
        GROUP_CONCAT(DISTINCT PHGB.DataID) AS DataIDs,
        GROUP_CONCAT(DISTINCT PHGB.HLCount) AS HLCounts,
        (PHGB.HLWeight) AS TotalHLWeight,
        GROUP_CONCAT(DISTINCT PVA.ShortName) AS Variety,

        -- Value Addition Weight
        COALESCE(SUM(DISTINCT PVAData.WtAftrVA), 0) AS ValueAdditionWeight,

        -- Soak In Quantity
        COALESCE(SUM(DISTINCT PSDData.SoakInQty), 0) AS SoakInQty,

        -- Production Weight
        COALESCE(SUM(DISTINCT PFDData.ProductionWeight + PFDData.RejectionWeight), 0) AS ProductionWeight,

        GROUP_CONCAT(DISTINCT CONCAT(SPVL.`PreparationType`,'-',SPVL.ShortName)) AS FreezingVariety,
        GROUP_CONCAT(DISTINCT APS.PackingStyle) AS Packing

    FROM PpcHLGradingBreakup PHGB
    LEFT JOIN PpcHLGradingDetails PHGD ON PHGB.MixedID = PHGD.MixIDs
    LEFT JOIN PpcHLVAVarietyList PVA ON PVA.VarietyID = PHGB.VarietyID
    LEFT JOIN RmLotDetatils RLD ON RLD.RmLotID = PHGD.LotID

    -- Join instead of subquery lookups
    LEFT JOIN PpcValueAddition PVAData ON FIND_IN_SET(PHGB.DataID, PVAData.GradingBreakupID)
    LEFT JOIN ProdSoakingDetails PSDData ON FIND_IN_SET(PHGB.DataID, PSDData.VABreakupID)
    LEFT JOIN ProdFreezingData PFDData ON FIND_IN_SET(PHGB.DataID, PFDData.GradingBreakupID)
    LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID = PFDData.VarietyID
    LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFDData.PackingStyleID

    WHERE RLD.RmReferenceID IN ($ArrivalID)
      AND PHGB.HLWeight != 0

    GROUP BY PVA.ShortName,PHGB.HLCount,ABS(PHGB.PackingGrade)
    ORDER BY ABS(PHGB.PackingGrade) ASC
) AS Sub;
";

// echo $SqlInsert; exit;
    $stmtInsert= $this->conn->prepare($SqlInsert);
    $stmtInsert->execute();

    /*$this->auditLogger->logInsert('HeadonToFinishedReportTemp', null, null, $RequestBody, [
        'ReferenceNo' => $ArrivalID,
        'Remarks' => 'Head-on to finished report generated',
    ]);*/


    $SqlInsertTotals="INSERT INTO HeadonToFinishedReportTemp (
        GradingBreakUpID,
        HlCount,
        GradingVariety,
        HlQuantity,
        VaQuantity,
        SoakingQuantity,
        PickupPercentage,
        FinishedQuantity,
        HlToVaYieldPercentage,
        FreezingVariety,
        Packing,
        Grade
    )
    SELECT
        'TOTAL' AS GradingBreakUpID,
        NULL AS HlCount,
        NULL AS GradingVariety,
        SUM(HlQuantity),
        SUM(VaQuantity),
        SUM(SoakingQuantity),
        NULL AS PickupPercentage,
        SUM(FinishedQuantity),
        NULL AS HlToVaYieldPercentage,
        NULL AS FreezingVariety,
        NULL AS Packing,
        'TOTALS' AS Grade
    FROM HeadonToFinishedReportTemp
    WHERE GradingBreakUpID != 'TOTAL'";


$stmtInsertTotals= $this->conn->prepare($SqlInsertTotals);
$stmtInsertTotals->execute();

$SqlHeadonToFinishedReportData="SELECT * FROM `HeadonToFinishedReportTemp`";
$stmtHeadonToFinishedReportData= $this->conn->prepare($SqlHeadonToFinishedReportData);
$stmtHeadonToFinishedReportData->execute();
// $ArrayHeadonToFinishedReportData = [];
while ($dataRowHeadonToFinishedReportData = $stmtHeadonToFinishedReportData->fetch(PDO::FETCH_ASSOC)) {
    if($dataRowHeadonToFinishedReportData['Packing'] != '' && count(explode(',', $dataRowHeadonToFinishedReportData['Packing'])) > 1){
        $dataIds = $dataRowHeadonToFinishedReportData['GradingBreakUpID'];
        $FreezingQty = '';
        $PackingStyle = '';
        $sqlFreezing = " SELECT SUM(ProductionWeight+RejectionWeight) AS PWeight,APS.PackingStyle
            FROM ProdFreezingData AS PFD
            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = PFD.PackingStyleID
            WHERE PFD.GradingBreakupID IN ($dataIds) GROUP BY PFD.PackingStyleID";
        $stmtFreezing= $this->conn->prepare($sqlFreezing);
        $stmtFreezing->execute();
        // $ArrayHeadonToFinishedReportData = [];
        while ($dataFreezing = $stmtFreezing->fetch(PDO::FETCH_ASSOC)) {
            if($PackingStyle != ''){
                $PackingStyle .=  ',' . $dataFreezing['PackingStyle'];
            }else{
                 $PackingStyle .=   $dataFreezing['PackingStyle'];
            }
            if($FreezingQty != ''){
                $FreezingQty .= ',' . $dataFreezing['PWeight'];
            }else{
                 $FreezingQty .= $dataFreezing['PWeight'];
            }
        }
        $dataRowHeadonToFinishedReportData['Packing'] = $PackingStyle;
        $dataRowHeadonToFinishedReportData['FinishedQuantity'] = $FreezingQty;

    }
    $ArrayHeadonToFinishedReportData[]=$dataRowHeadonToFinishedReportData;

    }
    $SqlMixlotIDS="SELECT GROUP_CONCAT(distinct(MixIDs) ) as LotIDS FROM PpcHLGradingDetails PHGD  left join  `RmLotDetatils` RLD on RLD.RmLotID =PHGD.LotID
 WHERE RmReferenceID='$ArrivalID' ORDER BY `RmLotID` DESC";
    $stmtMixlotIDS= $this->conn->prepare($SqlMixlotIDS);
    $stmtMixlotIDS->execute();
    $dataRowMixlotIDS= $stmtMixlotIDS->fetch(PDO::FETCH_ASSOC);
    $MixedLotIDS=$dataRowMixlotIDS['LotIDS'];

    $Data['PurchaseData']= $this->getPurchaseandReceivingData($ArrivalID);
    // $Data['Yields']= $this->getYields($ArrivalID);
    $Data['Yields']= $this->getYields($MixedLotIDS);
    $Data['AbStatus']= $this->getAbStatus($ArrivalID);
    $Data['HeadonToFinishedReportData']=$ArrayHeadonToFinishedReportData;


return $Data;
}

function getAbStatus($MixedLotIDS){
    //$MixedLotIDS = 2043;
    $SqlAbstatus = "SELECT RmLotID,AbStatus,Name,Colour from RmLotDetatils as RLD left join QcRmAbReport as QCAB on RLD.RmLotID=QCAB.LotID LEFT join QcABOptions as ABO on ABO.OptID=QCAB.AbStatus where RmReferenceID='".$MixedLotIDS."'";
    $AbStatusName='TEST PENDING';
    $AbStatusColour='#666';
    $stmtAbstatus = $this->conn->prepare($SqlAbstatus);
    $stmtAbstatus->execute();
    while ($dataAbStatusData = $stmtAbstatus->fetch(PDO::FETCH_ASSOC)) {
        if( isset($dataAbStatusData['Name']) && $dataAbStatusData['Name']){
            if( $dataAbStatusData['Name'] == "POSITIVE" ){
                $AbStatusName=$dataAbStatusData['Name'];
                $AbStatusColour=$dataAbStatusData['Colour'];
                break;
            }else{
                $AbStatusName=$dataAbStatusData['Name'];
                $AbStatusColour=$dataAbStatusData['Colour'];
            }
        }
    }
    return [ "Name"=>$AbStatusName, "Colour" => $AbStatusColour ];
}

function getPurchaseandReceivingData($ArrivalID){
    $SqlFileds="DATE_FORMAT(RAD.ArrivalDate, '%d-%m-%Y') AS Date, ArrivalNumber as BatchNumber,GROUP_CONCAT(distinct Count)as PurchaseCount ,sum(Weight) as PurchaseHoQty ,
    GROUP_CONCAT(distinct ReceivingCount) as PlantCount,StationName as Place";

   $SqlPurchaseData=" SELECT  $SqlFileds FROM `RmArrivalDetatils` RAD
left join RmArrivalBreakUpDetails RAB on RAB.ArrivalID=RAD.RmArrivalID
left join RmReceivingBreakup RRB on RRB.ArrivalID=RAD.RmArrivalID
left join RmReceivingDetails RRD on RRD.ReceivingID=RRB.ReceivingID
left join RmProcessingStations RPS on RPS.ProcessingStationID=RRD.ReceivingPlantID
WHERE  RmArrivalID='$ArrivalID'";

$stmtPurchaseData = $this->conn->prepare($SqlPurchaseData);
$stmtPurchaseData->execute();


// $SqlBatchNumber=" SELECT * FROM PpcHLGradingDetails PHGD  left join  `RmLotDetatils` RLD on RLD.RmLotID =PHGD.LotID
//  WHERE RmReferenceID='$ArrivalID' ORDER BY `RmLotID` DESC";

//  $stmtBatchNumber = $this->conn->prepare($SqlBatchNumber);
//  $stmtBatchNumber->execute();

while ($dataRowPurchaseData = $stmtPurchaseData->fetch(PDO::FETCH_ASSOC)) {
$ArrayPurchaseData[]=$dataRowPurchaseData;

}
return $ArrayPurchaseData;

}

// function getGrades()
//  {
//      $sqlQueryGrade = "SELECT value AS Grade FROM ProductionGrades ORDER BY ABS(CAST(value AS SIGNED)) ASC";

//      $stmtGrade = $this->conn->prepare($sqlQueryGrade);
//      $stmtGrade->execute();
//      while ($dataRowGrade = $stmtGrade->fetch(PDO::FETCH_ASSOC)) {
//          $Grade['Grade'] = $dataRowGrade['Grade'];
//          $ArrayGrade[] = $Grade;
//      }
//      return $ArrayGrade;
//  }

    // function getYields($ArrivalID){
        function getYields($MixedLotIDS){

            // echo $MixedLotIDS; exit;
            $length = count(explode(',', $MixedLotIDS));
//             // echo $length;
//             $DisplayText="";
//             $DisplayText2="";
// $Colour="";
// $BatchNumber="";
// $MixedHeadonWeight="";
// $PurchaseCount="";



    //    $sqlHeadOnweight=  " SELECT sum(Weight) as HeadOnWeight FROM `RmArrivalBreakUpDetails` WHERE ArrivalID='$ArrivalID'";
    if($length>0){
       $sqlHeadOnweight=  "  SELECT GROUP_CONCAT(distinct RmReferenceID) ArrivalID, GROUP_CONCAT(distinct LotNumber) BatchNumber,sum(PurchaseWeight) as HeadOnWeight,
       GROUP_CONCAT(distinct PurchaseCount) as PurchaseCount ,
       COALESCE((     SELECT GROUP_CONCAT(distinct ReceivingCount) FROM RmReceivingBreakup RABD
    WHERE FIND_IN_SET(RABD.ArrivalID, GROUP_CONCAT(RLD.RmReferenceID))    ), '') AS ReceivingCount  FROM `RmLotDetatils`  RLD WHERE RmLotID in ($MixedLotIDS)";

// $sqlHeadOnweight=  "  SELECT GROUP_CONCAT(distinct RmReferenceID) ArrivalID, GROUP_CONCAT(distinct LotNumber) BatchNumber,COALESCE((
//     SELECT sum(Weight) FROM RmArrivalBreakUpDetails RABD
//     WHERE FIND_IN_SET(RABD.ArrivalID, GROUP_CONCAT(RLD.RmReferenceID))    ), '') AS HeadOnWeight FROM `RmLotDetatils`  RLD
// WHERE RmLotID in ($MixedLotIDS)";


       $stmtHeadOnweight= $this->conn->prepare($sqlHeadOnweight);
         $stmtHeadOnweight->execute();
         $dataRowHeadOnweight = $stmtHeadOnweight->fetch(PDO::FETCH_ASSOC) ;

             $HeadOnWeight = $dataRowHeadOnweight['HeadOnWeight'];
             $BatchNumber=$dataRowHeadOnweight['BatchNumber'];
             $PurchaseCount = $dataRowHeadOnweight['PurchaseCount'];
             $ReceivingCount = $dataRowHeadOnweight['ReceivingCount'];
        }

            //  if($length>1){
            //     $DisplayText="Mixed Batch Numbers : ";
            //     $Colour="red";
            //     $BatchNumber=$dataRowHeadOnweight['BatchNumber'];
            //     $MixedHeadonWeight=$HeadOnWeight;
            //     $DisplayText2="Total mixed batches purchase weight : ";
            //     $PurchaseCount = $dataRowHeadOnweight['PurchaseCount'];


            //                 }
            //  $ArrayGrade[] = $Grade;


      $sqlYieldsQuery = "  SELECT  HlQuantity, FinishedQuantity FROM `HeadonToFinishedReportTemp` WHERE GradingBreakUpID='TOTAL'";
      $stmtYields = $this->conn->prepare($sqlYieldsQuery);
        $stmtYields->execute();
        $dataRowYields = $stmtYields->fetch(PDO::FETCH_ASSOC) ;

            $HlQuantity = $dataRowYields['HlQuantity'];
            $FinishedQuantity = $dataRowYields['FinishedQuantity'];

            $ArrayYields['HoToHlYield']= number_format((($HlQuantity/$HeadOnWeight)*100),2);
            $ArrayYields['HoToFinishedYield']=number_format((($FinishedQuantity/$HeadOnWeight)*100),2);
            $ArrayYields['HlToFinishedYield']=number_format((($FinishedQuantity/$HlQuantity)*100),2);
            $ArrayYields['DisplayText']=$DisplayText;
            $ArrayYields['Colour']=$Colour;
            $ArrayYields['BatchNumber']=$BatchNumber;
            $ArrayYields['MixedHeadonWeight']=$HeadOnWeight;
            $ArrayYields['PurchaseCount']=$PurchaseCount;
            $ArrayYields['ReceivingCount']=$ReceivingCount;


            $AryYields[] = $ArrayYields;

        return $AryYields;

    }

}
