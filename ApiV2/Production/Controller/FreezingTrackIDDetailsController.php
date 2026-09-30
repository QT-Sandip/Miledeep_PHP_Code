<?php

include_once '../Global/Util.php';
include_once '../App/Controller/AppUtilController.php';
include_once '../ColdStore/Controller/DailyProductionDetailsController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class FreezingTrackIDDetailsController
{
    private $conn;
    private $appController;
    private $dailyProductionDeatilsController;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->appController = new AppUtilController($this->conn);
        $this->dailyProductionDeatilsController = new DailyProductionDetailsController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Production');
    }

    public function getFreezingDetails($RequestBody)
    {
        if (!empty($RequestBody['methodType']) && $RequestBody['methodType'] == 'getTotalSlabs') {
            $slabData = $this->getSlabData($RequestBody);
            $Data['slabData'] = $slabData;
        } else { 
            $TubNumbers = [];
            if(!trim($RequestBody['MixedBatchCode'])){
                $TubNumbers = $this->getTubNumbers($RequestBody);
            }


            // foreach ($TubNumbers['TubNumbers'] as $Item) {

            //     //  var_dump($Item);
            //     $SoakinQty = $Item['SoakInQty'];
            //     $allowedSoakoutQty = $SoakinQty + round($SoakinQty * 0.25, 2);
            //     $Item['allowedSoakoutQty'] = $allowedSoakoutQty;
            //     $arrayTubNumbers[] = $Item;



            // }

            //  $ArrayData['TubNumbers'] = $arrayTubNumbers;
            $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
           

            $Data['TubNumbesData'] = $TubNumbers;// By Teja
            if(!trim($RequestBody['MixedBatchCode'])){
                $Data['TimerData'] = $this->getTimerData($RequestBody); //uma
            }

            // $Data['TubNumbesData'] = $arrayTubNumbers;
        }
        return $Data;
    }

    public function getTimerData($RequestBody)  // for getting Tub numbers 12/04/2024
    {
        $Shift = $RequestBody['Shift']; //uma 03-01-2024
        $PlantID = $RequestBody['PlantID'];
        $ProductionDate = $RequestBody['ProductionDate'];

        $sqlFields = "ProductionID , StartTimer, EndTimer ";
        $sqlgetTimerdata = "SELECT $sqlFields FROM `ProdFreezingData` WHERE ProductionDate = '$ProductionDate' AND ShiftID = '$Shift' AND PlantID = '$PlantID' ORDER BY ProductionID desc LIMIT 1;";
        // WHERE RRD.ReceivingID='$TrackID' and IsStoreMaterial=1  and ReceivingPlantID='$PlantID'";

        $stmtgetTimerdata = $this->conn->prepare($sqlgetTimerdata);
        $stmtgetTimerdata->execute();
        $dataRowgetTimerdata = $stmtgetTimerdata->fetch(PDO::FETCH_ASSOC);
        $ArrayTimerdata[] = $dataRowgetTimerdata;
        // $ArrayTimerdata['EndTimer'] = $dataRowgetTimerdata['EndTimer'];



        return $ArrayTimerdata;

    }
    public function getTubNumbers($RequestBody)  // for getting Tub numbers 12/04/2024
    {
        $TrackID = $RequestBody['TrackID']; //uma 03-01-2024
        $PlantID = isset($RequestBody['PlantID']) && !empty($RequestBody['PlantID']) ? $RequestBody['PlantID'] : 0;
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $IsHONPacking = $RequestBody['IsHONPacking'];
        $AddvalueAddition = $RequestBody['AddvalueAddition'];
        $AllowedHONFreezingWeightExcessPercent = $RequestBody['AllowedHONFreezingWeightExcessPercent'];

        $HonFields = '';
        if ($IsHONPacking == 1) {
            $HonFields = ",IF(FIND_IN_SET('1', GROUP_CONCAT(DISTINCT RRB.HONPacking)), '1', '0') AS Has_HONPacking";
        }
        $sqlFields = "sum(ReceivingQty) as ReceivingQty,RRB.CsStockOutID,count(*) as StoreCount,OutStage,IsStoreMaterial,
        SPVL.ShortName, SPVL.Grade, SPVL.Species, SPVL.Treatment, SPVL.TypeProduct,COALESCE(IsStoreMaterial,0) as IsStoreMaterial,SPVL.FreezingType
        ,GROUP_CONCAT(DISTINCT CASE WHEN RRB.CsStockID != 0 THEN RRB.CsStockID END) CsStockIDs";
        $sqlIsStoreMaterial = "SELECT $sqlFields $HonFields FROM RmReceivingDetails as RRD 
        left join RmReceivingBreakup as RRB on RRB.ReceivingID=RRD.ReceivingID 
        LEFT JOIN CsStockOutDetails AS CSOD ON RRB.CsStockOutID = CSOD.StockOutID
        LEFT JOIN CsStockData AS CSD ON RRB.CsStockID = CSD.StockID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON CSD.VarietyID = SPVL.VarietyID
        WHERE RRD.ReceivingID='$TrackID'";

        // echo print_r($sqlIsStoreMaterial);
        // exit;

        // WHERE RRD.ReceivingID='$TrackID' and IsStoreMaterial=1  and ReceivingPlantID='$PlantID'";

        $stmtIsStoreMaterial = $this->conn->prepare($sqlIsStoreMaterial);
        $stmtIsStoreMaterial->execute();
        $dataRowIsStoreMaterial = $stmtIsStoreMaterial->fetch(PDO::FETCH_ASSOC);
        $IsStoreMaterial = $dataRowIsStoreMaterial['IsStoreMaterial'];
        $OutStage = $dataRowIsStoreMaterial['OutStage'];

        if ($IsHONPacking == 1) {
            if ($dataRowIsStoreMaterial['Has_HONPacking'] == '1') {

                $sqlFields = "RRB.ReceivingQty,RRB.ReceivingCount,RAB.Variety,RRB.ReceivalBreakupID,RAD.Species,RAD.ArrivalNumber";
                $sqlHONPackingTubsSql = "SELECT $sqlFields FROM RmReceivingBreakup AS RRB LEFT JOIN RmReceivingDetails AS RRD ON RRB.ReceivingID=RRD.ReceivingID
                    LEFT JOIN RmArrivalBreakUpDetails AS RAB ON RRB.ArrivalBreakupID=RAB.ArrivalBrkUpID  LEFT JOIN RmArrivalDetatils AS RAD ON RAB.ArrivalID=RAD.RmArrivalID
                    WHERE RRD.ReceivingID=$TrackID AND RRB.HONPacking=1";
                $stmtTubNumber = $this->conn->prepare($sqlHONPackingTubsSql);
                $stmtTubNumber->execute();
                while ($dataRow = $stmtTubNumber->fetch(PDO::FETCH_ASSOC)) {
                    $TubNumber = $dataRow['ReceivalBreakupID'];
                    $allowedFreezingWeight = $dataRow['ReceivingQty'];// default
                    if(!empty($AllowedHONFreezingWeightExcessPercent) && $AllowedHONFreezingWeightExcessPercent > 0){
                        $allowedFreezingWeight = $dataRow['ReceivingQty'] + round($dataRow['ReceivingQty'] * $AllowedHONFreezingWeightExcessPercent, 2);
                    }
                    $BalanceQty = $this->getEachBalanceQty("HON", $TubNumber, $TrackID, '', $PlantID);
                    $ArrayTubNumber['TubNumber'] = "HON";
                    $ArrayTubNumber['SoakInQty'] = $dataRow['ReceivingQty'];
                    $ArrayTubNumber['allowedFreezingWeight'] = $allowedFreezingWeight;
                    $ArrayTubNumber['HLCount'] = 0;
                    $ArrayTubNumber['TubNumberValue'] = $TubNumber;
                    $ArrayTubNumber['TubCode'] = "HON-" . $TubNumber;
                    $ArrayTubNumber['GradingBreakupID'] = 0;
                    $ArrayTubNumber['TypeProduct'] = $dataRow['TypeProduct'];
                    $ArrayTubNumber['ShortName'] = $dataRow['Variety'];
                    $ArrayTubNumber['Species'] = $dataRow['Species'];
                    $ArrayTubNumber['GradeNumber'] = $dataRow['ReceivingCount'];
                    $ArrayTubNumber['Variety'] = $dataRow['Variety'];
                    // $ArrayTubNumber['Grade'] = '-' . $dataRow['Grade'] . '(' . $dataRow['ShortName'] . '-' . $dataRow['Treatment'] . ')';
                    $ArrayTubNumber['Grade'] = ' - ' . $dataRow['ReceivingCount'] . '(' . $dataRow['Species'] . ' - ' . $dataRow['Variety'] . ')';
                    $ArrayTubNumber['IsStoreMaterial'] = $IsStoreMaterial; //uma 2025-04-01
                    $ArrayTubNumber['ArrivalNumber'] = $dataRow['ArrivalNumber'];//uma 2025-04-01


                    $ArrayTubNumber['BalanceQty'] = $BalanceQty;
                    $AryTubNumbers[] = $ArrayTubNumber;
                }

            }
        }
        if ($OutStage == 3 && $IsStoreMaterial == 0) {
            $sqlTubNumber = "SELECT sum(RejectionWeight) as RejectionWeight,PFD.Grade,SPVL.Species,SPVL.Treatment,SPVL.FreezingType,ShortName,TypeProduct,PFD.VarietyID,GROUP_CONCAT(ProductionID) AS ProductionList 
            ,GROUP_CONCAT(DISTINCT RAD.ArrivalNumber) ArrivalNumber 
            FROM `ProdFreezingData` as PFD 
            left join ScrmPIVarietyList as SPVL on PFD.VarietyID=SPVL.VarietyID 
            
            -- LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivalBreakupID = PFD.`TrackID`
            -- LEFT JOIN RmArrivalDetatils AS RAD ON RRB.ArrivalID=RAD.RmArrivalID

            LEFT JOIN PpcHLGradingBreakup PHGB ON PHGB.`DataID` =PFD.`GradingBreakupID`  AND PHGB.CreatedFrom=1
             -- LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
            -- LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGB.MixedID=PHGD.MixIDs
            LEFT JOIN PpcHLGradingDetails AS PHGD
                ON PHGD.HLID = (
                    SELECT MAX(HLID)
                    FROM PpcHLGradingDetails
                    WHERE MixIDs = PHGB.MixedID  AND CreatedFrom=1
                )  AND PHGD.CreatedFrom=1
            LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivalBreakupID = PHGD.TrackBreakUpID
            LEFT JOIN RmArrivalDetatils AS RAD ON RRB.ArrivalID=RAD.RmArrivalID

            WHERE PFD.ArrivalDate='$ArrivalDate' and RejectionWeight!=0 
            and PFD.PlantID='$PlantID'  group by PFD.Grade,PFD.VarietyID ORDER BY PFD.ProductionID";

            $stmtTubNumber = $this->conn->prepare($sqlTubNumber);
            $stmtTubNumber->execute();
            while ($dataRow = $stmtTubNumber->fetch(PDO::FETCH_ASSOC)) {
                $TubNumber = $TrackID;

                $BalanceQty = $this->getEachBalanceQty("REJ", $TubNumber, $TrackID, $dataRow['ProductionList'], $PlantID);
                $ArrayTubNumber['TubNumber'] = "REJ";
                $ArrayTubNumber['SoakInQty'] = $dataRow['RejectionWeight'];
                $ArrayTubNumber['allowedFreezingWeight'] = $dataRow['RejectionWeight'];
                $ArrayTubNumber['HLCount'] = 0;
                $ArrayTubNumber['TubNumberValue'] = $TrackID;
                $ArrayTubNumber['TubCode'] = "REJ-" . $TrackID;
                $ArrayTubNumber['GradingBreakupID'] = 0;
                $ArrayTubNumber['TypeProduct'] = $dataRow['TypeProduct'];
                $ArrayTubNumber['ShortName'] = $dataRow['ShortName'];
                $ArrayTubNumber['Species'] = $dataRow['Species'];
                $ArrayTubNumber['Treatment'] = $dataRow['Treatment'];
                $ArrayTubNumber['GradeNumber'] = $dataRow['Grade'];
                $ArrayTubNumber['Variety'] = $dataRow['VarietyID'];
                $ArrayTubNumber['ProductionList'] = $dataRow['ProductionList'];
                $ArrayTubNumber['IsStoreMaterial'] = $IsStoreMaterial; //uma 2025-04-01
                //$ArrayTubNumber['ArrivalNumber'] = '';//uma 2025-04-01
                $ArrayTubNumber['ArrivalNumber'] = $dataRow['ArrivalNumber'];
                ;
                // $ArrayTubNumber['Grade'] = '-' . $dataRow['Grade'] . '(' . $dataRow['ShortName'] . '-' . $dataRow['Treatment'] . ')';
                $ArrayTubNumber['Grade'] = '-' . $dataRow['Grade'] . '(' . $dataRow['ShortName'] . '-' . $dataRow['TypeProduct'] . '-' . $dataRow['Treatment'] . ')';
                $ArrayTubNumber['BalanceQty'] = $BalanceQty;
                $AryTubNumbers[] = $ArrayTubNumber;
            }
        } else if ($OutStage != 3) {

            if ($IsStoreMaterial == 1) {
                $sqlTubNumber = "SELECT IsStoreMaterial,PSD.LotID,SoakOutCount as HLCount,SoakOutQty,TubNumber,
                TubCode,LineNumber FROM ProdSoakingDetails AS PSD                        
                LEFT JOIN RmReceivingDetails AS RRD ON PSD.TrackID =RRD.ReceivingID
                WHERE PSD.TrackID='$TrackID'  and  PSD.PlantID='$PlantID' AND PSD.CreatedFrom=1 ";
            } else {
                if ($IsStoreMaterial == 0 && $OutStage == 1) {
                    $sqlTubNumber = "SELECT IsStoreMaterial,PSD.LotID,SoakOutCount as HLCount,SoakOutQty,TubNumber,
                    TubCode,LineNumber FROM ProdSoakingDetails AS PSD                        
                    LEFT JOIN RmReceivingDetails AS RRD ON PSD.TrackID =RRD.ReceivingID
                    WHERE PSD.TrackID='$TrackID'  and  PSD.PlantID='$PlantID' AND PSD.CreatedFrom=1 ";
                } else {
                    $VAFields = '';
                    if ($AddvalueAddition == 1) {
                        $VAFields = ',PHGB.valueAddition';
                    }
                    $sqlTubNumber = "SELECT IsStoreMaterial,VLID,PHGB.LotID,HLCount,Treatment,IsBlock,HLWeight,WtAftrVA,
                    SoakOutQty,TubNumber,TubCode,DataID,LineNumber,LotNumber as ArrivalNumber$VAFields
                    FROM PpcHLGradingBreakup AS PHGB   LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID =PHGB.LotID  AND PHGD.CreatedFrom=1
                    LEFT JOIN PpcValueAddition AS PVA ON PVA.GradingBreakupID =PHGB.DataID  AND PVA.CreatedFrom=1
                    LEFT JOIN ProdSoakingDetails AS PSD ON PSD.VABreakupID =PHGB.DataID AND PSD.CreatedFrom=1
                    LEFT JOIN RmReceivingDetails AS RRD ON PSD.TrackID =RRD.ReceivingID 
                    left join RmLotDetatils RLD on RLD.RmLotID=PHGD.LotID
                    WHERE PHGD.TrackID='$TrackID'  AND PHGB.CreatedFrom=1 AND ((PHGD.PlantID='$PlantID' AND PVA.SoakingPlantID IS NULL) OR ((PVA.SoakingPlantID ='$PlantID') AND (PSD.PlantID='$PlantID' OR (PSD.PlantID IS NULL AND PHGB.Treatment='UNT')))) AND HLWeight > 0";
                }
            }
            $stmtTubNumber = $this->conn->prepare($sqlTubNumber);
            $stmtTubNumber->execute();

            while ($dataRow = $stmtTubNumber->fetch(PDO::FETCH_ASSOC)) {
                $valueAddition = isset($dataRow['valueAddition']) ? $dataRow['valueAddition'] : 0;
                if ($dataRow['IsStoreMaterial'] == 1 || ($dataRow['Treatment'] != 'UNT' && $dataRow['TubNumber'] != '')) {
                    $TubNumber = $dataRow['TubNumber'];
                    $LineNumber = $dataRow['LineNumber'];
                    $sqlVarietyFields = "GROUP_CONCAT(DISTINCT PHVL.ShortName) AS ShortName,
                    GROUP_CONCAT(DISTINCT PHVL.TypeProduct) AS TypeProduct,
                    GROUP_CONCAT(DISTINCT VAGrade) AS VAGrade,GROUP_CONCAT(DISTINCT SoakingTreatment) AS SoakingTreatment,
                    GROUP_CONCAT(DISTINCT PHVL.Species) AS Species, SUM(SoakInQty) AS SoakInQty";
                    $sqlVariety = "SELECT $sqlVarietyFields FROM ProdSoakingDetails AS PSD
                    LEFT JOIN PpcHLVAVarietyList AS PHVL ON PSD.Variety = PHVL.VarietyID
                    WHERE TubNumber = '$TubNumber' AND LineNumber = '$LineNumber' and PSD.TrackID='$TrackID' AND PSD.PlantID='$PlantID' AND PSD.CreatedFrom=1 ";

                    $stmtVariety = $this->conn->prepare($sqlVariety);
                    $stmtVariety->execute();
                    $dataVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);
                    $BalanceQty = $this->getEachBalanceQty("SKM", $TubNumber, $TrackID, '', $PlantID);

                    $allowedFreezingWeight = $dataVariety['SoakInQty'] + round($dataVariety['SoakInQty'] * 0.20, 2);

                    $ArrayTubNumber['TubNumber'] = "SKM";
                    //  $ArrayTubNumber['SoakInQty'] = $dataRow['SoakOutQty'];
                    $ArrayTubNumber['Count'] = $dataRow['HLCount'];
                    $ArrayTubNumber['TubNumberValue'] = $dataRow['TubNumber'];
                    $ArrayTubNumber['GradingBreakupID'] = $dataRow['DataID'];
                    $ArrayTubNumber['TubCode'] = $dataRow['TubCode'];
                    $ArrayTubNumber['TypeProduct'] = $dataVariety['TypeProduct'];
                    $ArrayTubNumber['ShortName'] = $dataVariety['ShortName'];
                    $ArrayTubNumber['Species'] = $dataVariety['Species'];
                    $ArrayTubNumber['Treatment'] = $dataVariety['SoakingTreatment'];
                    $ArrayTubNumber['SoakInQty'] = $dataVariety['SoakInQty'];
                    $ArrayTubNumber['allowedFreezingWeight'] = $allowedFreezingWeight;
                    $ArrayTubNumber['BalanceQty'] = $BalanceQty;
                    $ArrayTubNumber['IsStoreMaterial'] = $IsStoreMaterial; //uma 2025-04-01
                    $ArrayTubNumber['ArrivalNumber'] = $dataRow['ArrivalNumber'];//uma 2025-04-01
                    $ArrayTubNumber['Grade'] = '-' . $dataVariety['VAGrade'] . '(' . $dataVariety['ShortName'] . '-' . $dataVariety['TypeProduct'] . '-' . $dataVariety['SoakingTreatment'] . ')';
                } else if (((($dataRow['IsBlock'] == 0 && $AddvalueAddition == 0) || ($dataRow['IsBlock'] == 0 && $AddvalueAddition == 1 && $valueAddition == 1) || ($dataRow['IsBlock'] == 1 && $valueAddition == 1))) && $dataRow['Treatment'] == 'UNT' && $dataRow['VLID'] != '') {
                    $TubNumber = $dataRow['VLID'];
                    // $LotId = $dataRow['LotID'];
                    $sqlVarietyFields = "PVA.VarietyID, Species, TypeProduct, ShortName,PHGB.Treatment,PHGB.PackingGrade";
                    $sqlVariety = "SELECT $sqlVarietyFields FROM PpcValueAddition AS PVA
                    LEFT JOIN PpcHLVAVarietyList AS PHVL ON PVA.VarietyID = PHVL.VarietyID
                     left join PpcHLGradingBreakup as PHGB on PHGB.DataID=PVA.GradingBreakupID  AND PHGB.CreatedFrom=1
                    WHERE VLID = '$TubNumber' AND PVA.SoakingPlantID='$PlantID'  AND PVA.CreatedFrom=1";


                    // $sqlVariety = "SELECT $sqlVarietyFields FROM PpcHLGradingBreakup as PHGB
                    // left join PpcValueAddition AS PVA  on PHGB.DataID=PVA.GradingBreakupID
                    // LEFT JOIN PpcHLVAVarietyList AS PHVL ON PVA.VarietyID = PHVL.VarietyID
                    // WHERE LotID = '$LotId'";

                    $stmtVariety = $this->conn->prepare($sqlVariety);
                    $stmtVariety->execute();
                    $dataVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);
                    $BalanceQty = $this->getEachBalanceQty("VM", $TubNumber, $TrackID, '', $PlantID);

                    $ArrayTubNumber['TubNumber'] = "VM";
                    $ArrayTubNumber['SoakInQty'] = $dataRow['WtAftrVA'];
                    $ArrayTubNumber['allowedFreezingWeight'] = $dataRow['WtAftrVA'];
                    $ArrayTubNumber['Count'] = $dataRow['HLCount'];
                    $ArrayTubNumber['TubNumberValue'] = $dataRow['VLID'];
                    $ArrayTubNumber['TubCode'] = "VM-" . $dataRow['VLID'];
                    $ArrayTubNumber['GradingBreakupID'] = $dataRow['DataID'];
                    $ArrayTubNumber['TypeProduct'] = $dataVariety['TypeProduct'];
                    $ArrayTubNumber['ShortName'] = $dataVariety['ShortName'];
                    $ArrayTubNumber['Species'] = $dataVariety['Species'];
                    $ArrayTubNumber['Treatment'] = $dataVariety['Treatment'];
                    $ArrayTubNumber['Grade'] = '- ' . $dataVariety['PackingGrade'] . '(' . $dataVariety['ShortName'] . '-' . $dataVariety['TypeProduct'] . '-' . $dataVariety['Treatment'] . ')';
                    $ArrayTubNumber['BalanceQty'] = $BalanceQty;
                    $ArrayTubNumber['IsStoreMaterial'] = $IsStoreMaterial; //uma 2025-04-01
                    $ArrayTubNumber['ArrivalNumber'] = $dataRow['ArrivalNumber'];//uma 2025-04-01

                } else if ((($dataRow['IsBlock'] == 1 && $valueAddition == 0) || ($dataRow['IsBlock'] == 0 && $AddvalueAddition == 1 && $valueAddition == 0)) && $dataRow['Treatment'] == 'UNT' && $dataRow['DataID'] != '') {
                    $TubNumber = $dataRow['DataID'];
                    $sqlVarietyFields = "PHGB.VarietyID, Species, TypeProduct,ShortName, PackingGrade, PHGB.Treatment";
                    $sqlVariety = "SELECT $sqlVarietyFields FROM PpcHLGradingBreakup AS PHGB
                    LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID = PHVL.VarietyID
                    WHERE DataID = '$TubNumber'  AND PHGB.CreatedFrom=1";
                    $stmtVariety = $this->conn->prepare($sqlVariety);
                    $stmtVariety->execute();
                    $dataVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);
                    $BalanceQty = $this->getEachBalanceQty("GM", $TubNumber, $TrackID, '', $PlantID);

                    $ArrayTubNumber['TubNumber'] = "GM";
                    $ArrayTubNumber['SoakInQty'] = $dataRow['HLWeight'];
                    $ArrayTubNumber['allowedFreezingWeight'] = $dataRow['HLWeight'];
                    $ArrayTubNumber['Count'] = $dataRow['HLCount'];
                    $ArrayTubNumber['TubNumberValue'] = $dataRow['DataID'];
                    $ArrayTubNumber['TubCode'] = "GM-" . $dataRow['DataID'];
                    $ArrayTubNumber['GradingBreakupID'] = $dataRow['DataID'];
                    $ArrayTubNumber['TypeProduct'] = $dataVariety['TypeProduct'];
                    $ArrayTubNumber['ShortName'] = $dataVariety['ShortName'];
                    $ArrayTubNumber['Species'] = $dataVariety['Species'];
                    $ArrayTubNumber['Treatment'] = $dataVariety['Treatment'];
                    $ArrayTubNumber['Grade'] = '- ' . $dataVariety['PackingGrade'] . '(' . $dataVariety['ShortName'] . '-' . $dataVariety['TypeProduct'] . '-' . $dataVariety['Treatment'] . ')';
                    $ArrayTubNumber['BalanceQty'] = $BalanceQty;
                    $ArrayTubNumber['IsStoreMaterial'] = $IsStoreMaterial; //uma 2025-04-01
                    $ArrayTubNumber['ArrivalNumber'] = $dataRow['ArrivalNumber'];//uma 2025-04-01
                }

                $AryTubNumbers[] = $ArrayTubNumber;
            }
        } else {
            $TubNumber = $dataRowIsStoreMaterial['CsStockOutID'];
            $BalanceQty = $this->getEachBalanceQty("SM", $TubNumber, $TrackID, '', $PlantID);

            $ArrayTubNumber['TubNumber'] = "SM";
            $ArrayTubNumber['SoakInQty'] = $dataRowIsStoreMaterial['ReceivingQty'];
            $ArrayTubNumber['allowedFreezingWeight'] = $dataRowIsStoreMaterial['ReceivingQty'];
            $ArrayTubNumber['HLCount'] = 0;
            $ArrayTubNumber['TubNumberValue'] = $dataRowIsStoreMaterial['CsStockOutID'];
            $ArrayTubNumber['TubCode'] = "SM-" . $dataRowIsStoreMaterial['CsStockOutID'];
            $ArrayTubNumber['GradingBreakupID'] = 0;
            $ArrayTubNumber['TypeProduct'] = $dataRowIsStoreMaterial['TypeProduct'];
            $ArrayTubNumber['ShortName'] = $dataRowIsStoreMaterial['ShortName'];
            $ArrayTubNumber['Species'] = $dataRowIsStoreMaterial['Species'];
            $ArrayTubNumber['Treatment'] = $dataRowIsStoreMaterial['Treatment'];
            // $ArrayTubNumber['Grade'] = $dataRowIsStoreMaterial['Grade'];
            $ArrayTubNumber['Grade'] = '-' . $dataRowIsStoreMaterial['Grade'] . '(' . $dataRowIsStoreMaterial['ShortName'] . '-' . $dataRowIsStoreMaterial['TypeProduct'] . '-' . $dataRowIsStoreMaterial['Treatment'] . ')';
            $ArrayTubNumber['BalanceQty'] = $BalanceQty;
            $ArrayTubNumber['IsStoreMaterial'] = $IsStoreMaterial; //uma 2025-04-01
            //$ArrayTubNumber['ArrivalNumber'] = '';//uma 2025-04-01
            $lotNumber = '';
            if (!empty($dataRowIsStoreMaterial['CsStockIDs'])) {
                $lotNumber = $this->getLotNumberByStockId($dataRowIsStoreMaterial['CsStockIDs']);
            }
            $ArrayTubNumber['ArrivalNumber'] = $lotNumber;

            $AryTubNumbers[] = $ArrayTubNumber;
        }



        $AryTubNumbers = array_filter($AryTubNumbers);

        $AryTubNumbers = array_values(array_map("unserialize", array_unique(array_map("serialize", $AryTubNumbers))));

        if (!empty($AryTubNumbers)) {
            $ArrayData['TubNumbers'] = $AryTubNumbers;
        }
        return $ArrayData;
    }
    // for getting Tub numbers 12/04/2024
    public function getDailyFreezingDetails($RequestBody)
    { 
        $ProductionDate = $RequestBody['ProductionDate'];
        $PlantID = $RequestBody['PlantID'];
        $UnitID = $RequestBody['ColdStoreID'];
        $StockHolderID = $RequestBody['StockHolderID'];
        $ProductionTypeID = $RequestBody['Group'];
        $PackingTypeID = $RequestBody['PackingType'];
        $ProductionID = $RequestBody['ProductionID'];
        $TrackID = $RequestBody['TrackID']; // for getting Tub numbers 12/04/2024
        $MixedBatch = $RequestBody['MixedBatchCode'];
        $Query = '';

        if ($ProductionID != '') {
            $PQuery = " where ProductionID='$ProductionID'";
        }
        if ($ProductionID != '') {
            $DailyFreezingData = $this->getFreezingData($PQuery);
            $ProductionDate = $DailyFreezingData['ProductionDate'];
        } else {
            $DailyFreezingData['ProductionCode'] = str_replace("-", "", $ProductionDate);
            $DailyFreezingData['BatchCode'] = $DailyFreezingData['ProductionCode'] . "-" . rand(10, 100);
        }

        if ($TrackID != '') {
            $Query = "  where CSD.TrackID='$TrackID'";
        }
        if ($MixedBatch != '' && $TrackID == '') {
            $Query = "  where CSD.MixedBatch='$MixedBatch'";
        }
        if ($PlantID != '') {
            $Query .= " and CSD.PlantID='$PlantID'";
        }

        if ($UnitID != '') {
            $Query .= " and UnitID='$UnitID'";
        }
        if ($StockHolderID != '') {
            $Query .= " and StockHolderID='$StockHolderID'";
        }
        $DailyFreezingList = $this->getFreezinglist($Query);
        $BalanceQty = $this->getBalanceQty($RequestBody);

        $Data['DailyFreezingList'] = $DailyFreezingList;
        $Data['DailyFreezingData'] = $DailyFreezingData;
        $Data['BalanceQty'] = $BalanceQty;

        return $Data;
    }
    function getFreezinglist($Query)
    {
        // $sqlFields = "SPV.DeviSupc, ProductDescription,ProductionID,CSD.`ProductionWeight`, `AvalProductionWeight`, `PackingTypeID`, CSD.`Grade`,   
        // `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        // `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, RLD.`RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`, 
        // `ShiftID`,  CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,CSD.MixedBatch,CSD.Weight,
        // `StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`,  `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        // APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName, CSD.TrackID,TrackCode,TubCode,TypeofMaterial,CSD.TrackID,ProductionSlabs,RejectionWeight,
        // CSD.StartTimer,CSD.EndTimer,RLD.LotNumber";
        $sqlFields = "SPV.DeviSupc, ProductDescription,ProductionID,CSD.`ProductionWeight`, `AvalProductionWeight`, `PackingTypeID`, CSD.`Grade`,   
        `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, RLD.`RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`, 
        `ShiftID`,  CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`,  `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName, CSD.TrackID,TrackCode,TubCode,TypeofMaterial,CSD.TrackID,ProductionSlabs,RejectionWeight,
        CSD.StartTimer,CSD.EndTimer,RLD.LotNumber,CSD.MixedBatch,CSD.Mcs AS ProductionMcs,CSD.LooseBags AS ProductionLooseBags";

        $sqlQuery = "SELECT $sqlFields  FROM `ProdFreezingData` as CSD 
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.DataID=CSD.GradingBreakupID  AND PHGB.CreatedFrom=1
        LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID=PHGB.LotID AND CSD.TrackID=PHGD.TrackID  AND PHGD.CreatedFrom=1
        left join RmLotDetatils AS RLD on RLD.RmLotID=PHGD.LotID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID        
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmReceivingDetails as RRD on RRD.ReceivingID=CSD.TrackID   
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        //  var_dump($sqlQuery);exit;
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $FreezingProductionID = $dataRow['ProductionID'];
            $TrackID = $dataRow['TrackID'];
            $EditAccess = 1;


            $sqlGetEditAccess = " SELECT CDPD.ProductionID as DailyProductionID,COALESCE(CSD.StockID,'') as StockID
         FROM  `ProdFreezingData` as PFD  left join CsDailyProductionData as CDPD on CDPD.TrackID=PFD.TrackID and 
        CDPD.ProductionDate=PFD.ProductionDate and CDPD.PackingTypeID=PFD.PackingTypeID 
        and CDPD.ProductCategoryID=PFD.ProductCategoryID and CDPD.PackingStyleID=PFD.PackingStyleID and CDPD.AbStatus=PFD.AbStatus
        and CDPD.LineNumber=PFD.LineNumber and CDPD.PoID=PFD.PoID and CDPD.PoGradeId=PFD.PoGradeId and CDPD.Grade=PFD.Grade
        and CDPD.SockingType=PFD.SockingType and CDPD.FreezingType=PFD.FreezingType and CDPD.VarietyID=PFD.VarietyID
        left join CsStockData as CSD on CSD.ProductionID=CDPD.ProductionID
        WHERE  PFD.TrackID='$TrackID' and PFD.ProductionID='$FreezingProductionID'";
            $stmtGetEditAccess = $this->conn->prepare($sqlGetEditAccess);
            $stmtGetEditAccess->execute();
            $dataRowGetEditAccess = $stmtGetEditAccess->fetch(PDO::FETCH_ASSOC);
            if ($dataRowGetEditAccess['StockID'] != '') {
                $EditAccess = 0;
            }
            $AvalMcs = $dataRow['AvalMcs'];
            $LooseBags = $dataRow['LooseBags'];
            $ActualMcs = $dataRow['ActualMcs'];
            $ActualLooseBags = $dataRow['ActualLooseBags'];
            $BalanceMcs = $ActualMcs - $AvalMcs;
            $BalanceBags = $ActualLooseBags - $LooseBags;

            $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
            $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $dataRow);
            $BalanceMcs = $aryBalance['BalanceMcs'];
            $BalanceBags = $aryBalance['BalanceLooseBags'];
            $BalanceKgs = $aryBalance['BalanceWeight'];
            if ($BalanceKgs <= 0) {
                $Status = 1;
            } else {
                $Status = 0;
            }

            $ArrayFreezingData['SNo'] = ++$i;
            $ArrayFreezingData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayFreezingData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayFreezingData['ProductionID'] = $dataRow['ProductionID'];
            $ArrayFreezingData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayFreezingData['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayFreezingData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayFreezingData['StockHolderID'] = $dataRow['StockHolderID'];
            $ArrayFreezingData['StockHolderName'] = $dataRow['TenantName'];
            $ArrayFreezingData['PoNo'] = $dataRow['PoNo'];
            $ArrayFreezingData['PoID'] = $dataRow['PoID'];
            $ArrayFreezingData['StoreID'] = $dataRow['UnitID'];
            $ArrayFreezingData['StoreName'] = $dataRow['StoreName'];
            $ArrayFreezingData['PlantID'] = $dataRow['PlantID'];
            $ArrayFreezingData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayFreezingData['Coldstore'] = $dataRow['UnitID'];
            $ArrayFreezingData['ShiftID'] = $dataRow['ShiftID'];
            $ArrayFreezingData['Shift'] = $dataRow['Shift'];
            $ArrayFreezingData['BalanceMcs'] = $BalanceMcs;
            $ArrayFreezingData['BalanceBags'] = $BalanceBags;
            $ArrayFreezingData['ProductionCode'] = $dataRow['ProdDayCode'];
            $ArrayFreezingData['RmLotID'] = $dataRow['RmLotID'];
            $LotNumber = $dataRow['LotNumber'];
           

            if(!$dataRow['LotNumber'] && $dataRow['TubNo']){ 
                $lotSql = "SELECT  RAD.ArrivalNumber
                            FROM RmReceivingBreakup AS RRB 
                            LEFT JOIN RmArrivalBreakUpDetails AS RAB ON RRB.ArrivalBreakupID=RAB.ArrivalBrkUpID  
                            LEFT JOIN RmArrivalDetatils AS RAD ON RAB.ArrivalID=RAD.RmArrivalID
                            WHERE RRB.ReceivalBreakupID=".$dataRow['TubNo'];
                 $stmtLot = $this->conn->prepare($lotSql);
                $stmtLot->execute();
                $LotNumber = $stmtLot->fetch(PDO::FETCH_ASSOC)['ArrivalNumber'];

            }
            $ArrayFreezingData['LotNumber'] = $LotNumber;
            $ArrayFreezingData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayFreezingData['PoNumber'] = $dataRow['PoNo'];
            $ArrayFreezingData['Variety'] = $dataRow['Variety'];
            $ArrayFreezingData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayFreezingData['PoGradeId'] = $dataRow['PoGradeId'];
            $ArrayFreezingData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayFreezingData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayFreezingData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayFreezingData['Grade'] = $dataRow['Grade'];
            $ArrayFreezingData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayFreezingData['Galze'] = $dataRow['Galze'];
            $ArrayFreezingData['GlazePerc'] = $dataRow['GlazePerc'];
            $ArrayFreezingData['Soaking'] = $dataRow['SockingType'];
            $ArrayFreezingData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayFreezingData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayFreezingData['AbStatus'] = $dataRow['AbStatus'];
            $ArrayFreezingData['TubNo'] = $dataRow['TubNo'];
            $ArrayFreezingData['LineNumber'] = $dataRow['LineNumber'];
            $ArrayFreezingData['Remarks'] = $dataRow['Remarks'];
            $ArrayFreezingData['ProductionWeight'] = $dataRow['ProductionWeight'];
            $ArrayFreezingData['AvalProductionWeight'] = $dataRow['AvalProductionWeight'];
            $ArrayFreezingData['FreezingQuantity'] = $dataRow['FreezingQuantity'];
            $ArrayFreezingData['TrackID'] = $dataRow['TrackID'];
            $ArrayFreezingData['TrackCode'] = $dataRow['TrackCode'];
            $ArrayFreezingData['SoakingPlantID'] = $dataRow['SoakingPlantID'];
            $ArrayFreezingData['TrackBreakUpI D'] = $dataRow['TrackBreakUpID'];
            $ArrayFreezingData['GradingBreakupID'] = $dataRow['GradingBreakupID'];
            $ArrayFreezingData['ProductionCategory'] = $dataRow['ProductionCategory'];
            $ArrayFreezingData['TypeofMaterial'] = $dataRow['TypeofMaterial'];
            $ArrayFreezingData['TubCode'] = $dataRow['TubCode'];
            $ArrayFreezingData['RejectionWeight'] = $dataRow['RejectionWeight'];
            $ArrayFreezingData['ProductionSlabs'] = $dataRow['ProductionSlabs'];
            $ArrayFreezingData['StartTimer'] = $dataRow['StartTimer'];
            $ArrayFreezingData['EndTimer'] = $dataRow['EndTimer'];
            $ArrayFreezingData['MixedBatch'] = trim($dataRow['MixedBatch']);

            $ArrayFreezingData['ProductionMcs'] = trim($dataRow['ProductionMcs']);
            $ArrayFreezingData['ProductionLooseBags'] = trim($dataRow['ProductionLooseBags']);
            // $ArrayFreezingData['Weight'] = $dataRow['Weight'];

            $ArrayFreezingData['BalanceMcs'] = $BalanceMcs;
            $ArrayFreezingData['BalanceBags'] = $BalanceBags;
            $ArrayFreezingData['Kgs'] = sprintf('%0.2f', $TotalWeight);
            $ArrayFreezingData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
            $ArrayFreezingData['Status'] = $Status;
            $ArrayFreezingData['EditAccess'] = $EditAccess;

            $AryData[] = $ArrayFreezingData;
        }
        return $AryData;
    }

    function getFreezingData($Query)
    {
        $dataRow = null;
        // $sqlFields = " ProductionID,`ProductionWeight`, `AvalProductionWeight`, `PackingTypeID`, CSD.`Grade`,   `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        // `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`, 
        // `ShiftID`,  CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        // `StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        // APS.PackingStyle, APS1.PackingStyle as ActualPackingStyle,TenantName,StoreName,PoNo,RPS.StationName,`FreezingQuantity`, `TrackID`, CSD.MixedBatch,CSD.Weight,
        // `SoakingPlantID`, `TrackBreakUpID`, `GradingBreakupID`, `ProductionCategory`,TubNo,TubCode,TypeofMaterial,ProductionSlabs,RejectionWeight,CSD.EndTimer,CSD.StartTimer,SPGD.Mcs";

        $sqlFields = " ProductionID,`ProductionWeight`, `AvalProductionWeight`, `PackingTypeID`, CSD.`Grade`,   `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`, 
        `ShiftID`,  CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
        APS.PackingStyle, APS1.PackingStyle as ActualPackingStyle,TenantName,StoreName,PoNo,RPS.StationName,`FreezingQuantity`, `TrackID`,
        `SoakingPlantID`, `TrackBreakUpID`, `GradingBreakupID`, `ProductionCategory`,TubNo,TubCode,TypeofMaterial,ProductionSlabs,RejectionWeight,CSD.EndTimer,CSD.StartTimer,SPGD.Mcs,CSD.MixedBatch,CSD.Mcs AS ProductionMcs,CSD.LooseBags AS ProductionLooseBags";

        $sqlQuery = "SELECT $sqlFields  FROM `ProdFreezingData` as CSD 
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        LEFT JOIN ScrmPoGradeData SPGD ON SPGD.InvId = SPD.PoID  AND CSD.PoGradeId = SPGD.ID -- NEW
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join AdminPackingStyles as APS1 on APS1.PCKID=SPV.PackingStyle
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID        
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->execute();


        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);


        $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
        $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $dataRow);
        $BalanceMcs = $aryBalance['BalanceMcs'];
        $BalanceBags = $aryBalance['BalanceLooseBags'];
        $BalanceKgs = $aryBalance['BalanceWeight'];

        if ($BalanceKgs == 0) {
            $Status = 1;
        } else if ($BalanceKgs < 0) {
            $Status = 2;
        } else {
            $Status = 0;
        }

        $ArrayFreezingData['ProductionID'] = $dataRow['ProductionID'];
        $ArrayFreezingData['TransactionDate'] = $dataRow['TransactionDate'];
        $ArrayFreezingData['ProductionDate'] = $dataRow['ProductionDate'];
        $ArrayFreezingData['Group'] = $dataRow['ProductionTypeID'];
        $ArrayFreezingData['StockHolderID'] = $dataRow['StockHolderID'];
        $ArrayFreezingData['StockHolderName'] = $dataRow['TenantName'];
        $ArrayFreezingData['PoNo'] = $dataRow['PoNo'];
        $ArrayFreezingData['PoID'] = $dataRow['PoID'];
        $ArrayFreezingData['StoreID'] = $dataRow['UnitID'];
        $ArrayFreezingData['StoreName'] = $dataRow['StoreName'];
        $ArrayFreezingData['PlantID'] = $dataRow['PlantID'];
        $ArrayFreezingData['ProductionPlantName'] = $dataRow['StationName'];
        $ArrayFreezingData['Coldstore'] = $dataRow['UnitID'];
        $ArrayFreezingData['ShiftID'] = $dataRow['ShiftID'];
        $ArrayFreezingData['Shift'] = $dataRow['Shift'];
        $ArrayFreezingData['TubNo'] = $dataRow['TubNo'];
        $ArrayFreezingData['BalanceMcs'] = $BalanceMcs;
        $ArrayFreezingData['BalanceBags'] = $BalanceBags;
        $ArrayFreezingData['ProductionCode'] = $dataRow['ProdDayCode'];
        $ArrayFreezingData['RmLotID'] = $dataRow['RmLotID'];
        $ArrayFreezingData['BatchCode'] = $dataRow['ProdBatchCode'];
        $ArrayFreezingData['PoNumber'] = $dataRow['PoNo'];
        $ArrayFreezingData['Variety'] = $dataRow['Variety'];
        $ArrayFreezingData['VarietyID'] = $dataRow['VarietyID'];
        $ArrayFreezingData['PoGradeId'] = $dataRow['PoGradeId'];
        $ArrayFreezingData['PackingType'] = $dataRow['PackingTypeID'];
        $ArrayFreezingData['PackingStyle'] = $dataRow['PackingStyle'];
        $ArrayFreezingData['ActualPackingStyle'] = $dataRow['ActualPackingStyle'];
        $ArrayFreezingData['PackingStyleID'] = $dataRow['PackingStyleID'];
        $ArrayFreezingData['Grade'] = $dataRow['Grade'];
        $ArrayFreezingData['FreezingType'] = $dataRow['FreezingType'];
        $ArrayFreezingData['Galze'] = $dataRow['Galze'];
        $ArrayFreezingData['GlazePerc'] = $dataRow['Galze'];
        $ArrayFreezingData['Soaking'] = $dataRow['SockingType'];
        $ArrayFreezingData['Salt'] = $dataRow['Salt'];
        $ArrayFreezingData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayFreezingData['ProductCategory'] = $dataRow['ProductCategoryID'];
        $ArrayFreezingData['AbStatus'] = $dataRow['AbStatus'];
        $ArrayFreezingData['TubNo'] = $dataRow['TubNo'];
        $ArrayFreezingData['LineNumber'] = $dataRow['LineNumber'];
        $ArrayFreezingData['Remarks'] = $dataRow['Remarks'];
        $ArrayFreezingData['ProductionWeight'] = $dataRow['ProductionWeight'];
        $ArrayFreezingData['AvalProductionWeight'] = $dataRow['AvalProductionWeight'];
        $ArrayFreezingData['FreezingQuantity'] = $dataRow['FreezingQuantity'];
        $ArrayFreezingData['TrackID'] = $dataRow['TrackID'];
        $ArrayFreezingData['SoakingPlantID'] = $dataRow['SoakingPlantID'];
        $ArrayFreezingData['TrackBreakUpI D'] = $dataRow['TrackBreakUpID'];
        $ArrayFreezingData['GradingBreakupID'] = $dataRow['GradingBreakupID'];
        $ArrayFreezingData['ProductionCategory'] = $dataRow['ProductionCategory'];
        $ArrayFreezingData['TubCode'] = $dataRow['TubCode'];
        $ArrayFreezingData['TypeofMaterial'] = $dataRow['TypeofMaterial'];
        $ArrayFreezingData['RejectionWeight'] = $dataRow['RejectionWeight'];
        $ArrayFreezingData['ProductionSlabs'] = $dataRow['ProductionSlabs'];
        $ArrayFreezingData['StartTimer'] = $dataRow['StartTimer'];
        $ArrayFreezingData['EndTimer'] = $dataRow['EndTimer'];
        $ArrayFreezingData['Mcs'] = $dataRow['Mcs'];
         $ArrayFreezingData['MixedBatch'] = $dataRow['MixedBatch'];

        $ArrayFreezingData['ProductionMcs'] = trim($dataRow['ProductionMcs']);
        $ArrayFreezingData['ProductionLooseBags'] = trim($dataRow['ProductionLooseBags']);
        //  $ArrayFreezingData['Weight'] = $dataRow['Weight'];


        $dataRow = $ArrayFreezingData['Kgs'] = sprintf('%0.2f', $TotalWeight);
        $dataRow = $ArrayFreezingData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
        $dataRow = $ArrayFreezingData['Status'] = $Status;


        return $ArrayFreezingData;
        //   echo print_r($ArrayFreezingData);exit;
    }

    public function postDailyFreezingDetails($RequestBody)
    {
        $TrackID = $RequestBody['TrackID']; //PRODUCTION REQUEST
        $ArrayIsStoreMaterial = $this->checkIsStoreMaterial($TrackID, 0);
        $IsStoreMaterial = $ArrayIsStoreMaterial['IsStoreMaterial'];
        $OutGroup = $ArrayIsStoreMaterial['OutGroup'];
        $StockOutID = $ArrayIsStoreMaterial['StockOutID'];
        if ($IsStoreMaterial == 0) {
            $ProductionTypeID = $RequestBody['Group'] = "Production"; //PRODUCTION REQUEST
        } else {
            $ProductionTypeID = $RequestBody['Group'] = $OutGroup;
        }
        $ClientID = $RequestBody['ClientID'];
        $IsStoreProductionLinked = $this->appController->getClientConfig($ClientID)['IsStoreProductionLinked'];
        $ProductionID = $RequestBody['ProductionID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        // $PlantID = $RequestBody['PlantID'];
        $PlantID = ($RequestBody['PlantID'] != '' ? $RequestBody['PlantID'] : 0); // sriram
        $RequestBody['StockHolderID'] = $StockHolderID = $this->getStockHolderID($PlantID);
        $UnitID = $RequestBody['ColdstoreID'];
        $ShiftID = $RequestBody['Shift'];
        $ProdDayCode = str_replace("-", "", $ProductionDate);
        $ProdBatchCode = $ProdDayCode . "-" . rand(10, 100);
        $RequestBody['ProductionCode'] = $ProdDayCode; //PRODUCTION REQUEST
        $RequestBody['BatchCode'] = $ProdBatchCode; //PRODUCTION REQUEST
        $RmLotID = $RequestBody['RmLotID'];
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['Variety'];
        $PoGradeID = $RequestBody['PoGradeID'];
        $PackingTypeID = $RequestBody['PackingType'];
        $PackingStyleID = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $FreezingType = $RequestBody['FreezingType'];
        $Galze = $RequestBody['Glaze'];
        $SockingType = $RequestBody['Soaking'];
        $ProductCategoryID = $RequestBody['ProductCategory'];
        $AbStatus = $RequestBody['AbStatus'];
        $LineNumber = $RequestBody['LineNumber'];
        $Remarks = $RequestBody['Remarks'];
        $Salt = $RequestBody['Salt'];
        $TypeofMaterial = $RequestBody['TubNumber']; //GM
        $TubNo = $RequestBody['TubNumberValue']; //01
        $TubCode = $RequestBody['TubNumber'] . '-' . $TubNo; //GM-01
        $FreezingQuantity = $RequestBody['FreezingQuantity'];
        $ProductionCategory = $RequestBody['ProductionCategory'];
        $GradingBreakupID = $RequestBody['GradingBreakupID'];
        $TrackBreakUpID = $RequestBody['TrackBreakUpID'];
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $allowedfreezingweight = $RequestBody['allowedfreezingweight'];
        $currentFreezingQty = $RequestBody['currentFreezingQty'];
        // $ProductionWeight = $RequestBody['ProductionWeight'];
        $ProductID = $RequestBody['ProductID'];
        $ProductionSlabs = $RequestBody['ProductionSlabs'];
        $RejectionWeight = $RequestBody['RejectionWeight'];
        $StartTimer = $RequestBody['StartTimer'];
        $EndTimer = $RequestBody['EndTimer'];
        $IsDeductGlaze = $RequestBody['IsDeductGlaze'];
        $IsDeductGlazeFW = $RequestBody['IsDeductGlazeFW'];

        $MixedBatchCodeStatus = $RequestBody['MixedBatchCodeStatus']; 
        $MixedBatchCode = $RequestBody['MixedBatchCode']; 
        $MixedBatchWeight = (float)$RequestBody['Weight']; 

        $Mcs = $RequestBody['txtMcs']; 
        $LooseBags = $RequestBody['txtLooseBags']; 




        if ($TypeofMaterial == "REJ") {
            $ProductionList = $RequestBody['ProductionList'];
        } else {
            $ProductionList = '';
        }
        $RequestBody['StockOutID'] = $StockOutID; //PRODUCTION REQUEST
        $PackingCount = 0;
        $AryCurrentDate = getCurrentDateTime();
        $CreatedDateTime = $AryCurrentDate['TimeStamp'];
        $Year = $AryCurrentDate['Year'];

        $aloowedWeight = $SoakingID = 0;
        if(!$MixedBatchCode){
            $SqlSoakingID = "SELECT SUM(SoakInQty) AS SoakInQty  FROM `ProdSoakingDetails` WHERE TrackID='$TrackID' and TubNumber='$TubNo'and TubCode='$TubCode'  AND CreatedFrom=1 ";
            $StmtSoakingID = $this->conn->prepare($SqlSoakingID);
            $StmtSoakingID->execute();
            $RowSoakingID = $StmtSoakingID->fetch(PDO::FETCH_ASSOC);
            $SoakinQty = $RowSoakingID['SoakInQty'];
            $aloowedWeight = round($SoakinQty * 0.20, 2);


            $SqlSoakingID = "SELECT SckID FROM `ProdSoakingDetails` WHERE TrackID='$TrackID' and TubNumber='$TubNo'and TubCode='$TubCode' 
            and VABreakupID='$GradingBreakupID' AND CreatedFrom=1 ";
            $StmtSoakingID = $this->conn->prepare($SqlSoakingID);
            $StmtSoakingID->execute();
            $RowSoakingID = $StmtSoakingID->fetch(PDO::FETCH_ASSOC);
            $SoakingID = $RowSoakingID['SckID'];
        }

        


        if ($PoGradeID != 0) {
            $SqlVarietyId = "SELECT VarietyID FROM `ScrmPoGradeData` WHERE ID='$PoGradeID'";
            $stmtVarietyId = $this->conn->prepare($SqlVarietyId);
            $stmtVarietyId->execute();
            $RowVarietyId = $stmtVarietyId->fetch(PDO::FETCH_ASSOC);
            $VarietyID = $RowVarietyId['VarietyID'];
        }
        $stmt = false;

        $SqlProductID = "SELECT ProductID,Glaze FROM `ScrmPIVarietyList` as SPVL
        WHERE VarietyID='$VarietyID'";

        $stmtProductID = $this->conn->prepare($SqlProductID);
        $stmtProductID->execute();
        $RowProductID = $stmtProductID->fetch(PDO::FETCH_ASSOC);
        $ProductID = $RowProductID['ProductID'];
        $GlazeType = $RowProductID['Glaze'];

        $SqlPackingStyle = "SELECT Uom,MFactor,LbsFactor,SlabWeight FROM  AdminPackingStyles 
        WHERE PCKID='$PackingStyleID'";

        $stmtPackingStyle = $this->conn->prepare($SqlPackingStyle);
        $stmtPackingStyle->execute();
        $RowPackingStyle = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC);


        $lbsOrOzFactor = $RowPackingStyle['SlabWeight'] * $RowPackingStyle['LbsFactor'] * 0.454;
        // if ($RowPackingStyle['Uom'] == 'OZ') {
        //     $factor = pow(10, 3);
        //     $lbsOrOzFactor = floor(($RowPackingStyle['SlabWeight'] * $RowPackingStyle['LbsFactor'] * 0.454) * $factor) / $factor;
        // }
        if(!trim($MixedBatchCode)){
            if ($IsDeductGlaze == 1 && $Galze > 0) {
                if ($RowPackingStyle['Uom'] == 'KGS' || $RowPackingStyle['Uom'] == 'GRAMS') {
                    $SlabsToWeight = $ProductionSlabs * $RowPackingStyle['SlabWeight'] * $RowPackingStyle['MFactor'];
                    $GlazeWeight = ($SlabsToWeight * $Galze) / 100;
                    $ProductionWeight = $SlabsToWeight - $GlazeWeight;
                } else {
                    // $SlabsToWeight = ($ProductionSlabs * $RowPackingStyle['SlabWeight'] * $RowPackingStyle['LbsFactor']) * $RowPackingStyle['MFactor']; //for oz we need to convert slabs in to lbs weight then into kgs
                    $SlabsToWeight = ($ProductionSlabs * $lbsOrOzFactor); //for oz we need to convert slabs in to lbs weight then into kgs
                    $GlazeWeight = ($SlabsToWeight * $Galze) / 100;
                    $ProductionWeight = $SlabsToWeight - $GlazeWeight;
                }
            } else {
                if ($IsDeductGlazeFW == 1 && strpos($GlazeType, 'FW') === 0 && $Galze > 0) {
                    if ($RowPackingStyle['Uom'] == 'KGS' || $RowPackingStyle['Uom'] == 'GRAMS') {
                        $SlabsToWeight = $ProductionSlabs * $RowPackingStyle['SlabWeight'] * $RowPackingStyle['MFactor'];
                        $GlazeWeight = ($SlabsToWeight * $Galze) / 100;
                        $ProductionWeight = $SlabsToWeight;
                    } else {
                        // $SlabsToWeight = ($ProductionSlabs * $RowPackingStyle['SlabWeight'] * $RowPackingStyle['LbsFactor']) * $RowPackingStyle['MFactor']; //for oz we need to convert slabs in to lbs weight then into kgs
                        $SlabsToWeight = ($ProductionSlabs * $lbsOrOzFactor);
                        $GlazeWeight = ($SlabsToWeight * $Galze) / 100;
                        $ProductionWeight = $SlabsToWeight;
                    }
                } else {
                    if ($RowPackingStyle['Uom'] == 'KGS' || $RowPackingStyle['Uom'] == 'GRAMS') {
                        $ProductionWeight = $ProductionSlabs * $RowPackingStyle['SlabWeight'] * $RowPackingStyle['MFactor'];
                    } else {
                        $ProductionWeight = ($ProductionSlabs * $lbsOrOzFactor);
                    }
                }
            }
        }else{
            if ($RowPackingStyle['Uom'] == 'KGS' || $RowPackingStyle['Uom'] == 'GRAMS') {
                $SlabWeight = $RowPackingStyle['SlabWeight'] * $RowPackingStyle['MFactor'];
            }else{
                $SlabWeight = $lbsOrOzFactor;
            }
            $MixedBatchSlabs = ($MixedBatchWeight / $SlabWeight);
        }
        // $ProductionWeight = round($ProductionWeight);

        if(!trim($MixedBatchCode)){
            $AvalProdQty = $this->getProductionWeight($SoakingID); //500
            $RequestBody['ProductionWeight'] = $ProductionWeight;
                
            if ($ProductionID != '') {
                $sqlFreezingDataQuery = "SELECT `ProductionDate`,`ProductionWeight`,`RejectionWeight`,`ProductionSlabs` FROM `ProdFreezingData` WHERE `ProductionID`='$ProductionID'";
                $freezingDataBeforUpdate = $this->conn->prepare($sqlFreezingDataQuery);
                $freezingDataBeforUpdate->execute();
                $aryFreezingData = $freezingDataBeforUpdate->fetch(PDO::FETCH_ASSOC);

                $OldProductionWeight = $aryFreezingData['ProductionWeight'];
                $OldRejectionWeight = $aryFreezingData['RejectionWeight'];
                $currentQty = round(($currentFreezingQty + $ProductionWeight), 2);
                $currentQty = $currentQty - $OldProductionWeight;
                $currentQty = $currentQty + ($RejectionWeight - $OldRejectionWeight) + $OldRejectionWeight;

                // $allowedfreezingweight = $allowedfreezingweight + $OldProductionWeight;


            } else {
                $currentQty = $currentFreezingQty + $ProductionWeight + $RejectionWeight;
            }

            // echo $currentQty . " -- " . $allowedfreezingweight;exit;
            if ($currentQty > $allowedfreezingweight) {
                $Data['Error'] = "Weight exceed Allowed Limit ";
                $Data['Msg'] = 'Weight exceed Allowed Limit ';
                $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
                return $Data;
            }
        }
        //checking slab data 
        /* if (!empty($RequestBody['MCSRestriction']) && !empty($PoID)) {
            $SlabReqArr['PoNumber'] = $PoID;
            $SlabReqArr['VarietyId'] = $VarietyID;
            $SlabReqArr['PackingType'] = $PackingTypeID;
            $SlabReqArr['txtSlab'] = $ProductionSlabs;
            $SlabReqArr['drpPackingStyle'] = $PackingStyleID;
            $SlabReqArr['prodId'] = $ProductionID;
            $checkSlabRes = $this->getSlabData($SlabReqArr);
            if ($checkSlabRes['RemainSlabs'] < 0) {
                $Data['Error'] = "Weight exceeds the allowed limit,Balance Weight (" . $checkSlabRes['BalQty'] . ')';
                $Data['Msg'] = 'Weight exceeds the allowed limit,Balance Weight (' . $checkSlabRes['BalQty'] . ')';
                $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
                return $Data;
            }
        } */
        //checking slab data end

        if ($ProductionID == '') {
            $ExistingWeight = 0;
            if(empty($MixedBatchCode)){
                $sqlFreezingExist = "SELECT SUM(ProductionWeight + RejectionWeight) AS TotalExistWeight FROM `ProdFreezingData` WHERE 
                    TrackID='$TrackID'and ProductionDate='$ProductionDate'  and PackingTypeID='$PackingTypeID' and 
                    ProductCategoryID='$ProductCategoryID' and PackingStyleID='$PackingStyleID' and AbStatus='$AbStatus'
                    and LineNumber='$LineNumber' and PoID='$PoID' and PoGradeId='$PoGradeID' and Grade='$Grade'and SockingType='$SockingType'
                    and FreezingType='$FreezingType'and VarietyID='$VarietyID' and TubNo='$TubNo' and Mcs='$Mcs' and LooseBags='$LooseBags'";
                $stmtFreezingExist = $this->conn->prepare($sqlFreezingExist);
                $stmtFreezingExist->execute();
                $rowFreezingExist = $stmtFreezingExist->fetch(PDO::FETCH_ASSOC);
                $ExistingWeight = isset($rowFreezingExist['TotalExistWeight']) ? $rowFreezingExist['TotalExistWeight'] : 0;
            }
            
            if (
                empty($MixedBatchCode) &&
                ($TypeofMaterial != 'REJ' && (($ProductionWeight + $ExistingWeight) > $allowedfreezingweight))
                ) {
                $Data['Error'] = "Record already inserted. Weight Exceeds";
                $Data['Msg'] = 'Record already inserted. Weight Exceeds';
                $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
                return $Data;
            } else {
                if(empty($MixedBatchCode)){
                    $sqlFreezingExist = "SELECT COUNT(*) AS Total FROM `ProdFreezingData` WHERE 
                    TrackID='$TrackID'and ProductionDate='$ProductionDate'  and PackingTypeID='$PackingTypeID' and 
                    ProductCategoryID='$ProductCategoryID' and PackingStyleID='$PackingStyleID' and AbStatus='$AbStatus'
                    and LineNumber='$LineNumber' and PoID='$PoID' and PoGradeId='$PoGradeID' and Grade='$Grade'and SockingType='$SockingType'
                    and FreezingType='$FreezingType'and VarietyID='$VarietyID' and TubNo=$TubNo AND ProductionWeight=$ProductionWeight and Mcs='$Mcs' and LooseBags='$LooseBags'";
                    $stmtFreezingExist = $this->conn->prepare($sqlFreezingExist);
                    $stmtFreezingExist->execute();
                    $rowFreezingExist = $stmtFreezingExist->fetch(PDO::FETCH_ASSOC);
                    $Total = $rowFreezingExist['Total'];
                    if ($TypeofMaterial != 'REJ' && $Total > 0 && (($ProductionWeight + $ExistingWeight) > $allowedfreezingweight)) {
                        $Data['Error'] = "Record Already inserted";
                        $Data['Msg'] = 'Record Already inserted';
                        $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
                        return $Data;
                    }
                }else{
                    $ProductionWeight = $MixedBatchWeight;
                    $ProductionSlabs = round($MixedBatchSlabs, 2);
                }


                //     $sqlQuery = "INSERT INTO `ProdFreezingData`(`PackingCount`,`ProductionWeight`, `AvalProductionWeight`, 
                // `PackingTypeID`, `Grade`, `PoGradeId`, `FreezingType`, `VarietyID`, `ProductID`, `Galze`, `SockingType`, `Salt`,
                // `ProductionDate`, `PostedDate`, `PoID`, `RmLotID`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`, `PlantID`,
                // `ShiftID`, `PackingStyleID`, `StockHolderID`, `Remarks`,  `AbStatus`, `ProdDayCode`, `ProdBatchCode`,
                // `TubNo`,  `TubCode`, `LineNumber`,`FreezingQuantity`, `TrackID`, `SoakingPlantID`, `TrackBreakUpID`, `GradingBreakupID`,
                //  `ProductionCategory`, `TypeofMaterial`, `ProductionSlabs`, `RejectionWeight`, `ArrivalDate`, `SoakingID`,`ProductionIDList`,`StartTimer`,`EndTimer`,`MixedBatch`,`Weight`)
                //  values('$PackingCount','$ProductionWeight','$ProductionWeight','$PackingTypeID','$Grade','$PoGradeID',
                //  '$FreezingType','$VarietyID','$ProductID','$Galze','$SockingType','$Salt','$ProductionDate','$CreatedDateTime','$PoID','$RmLotID',
                //  '$ProductionTypeID', '$ProductCategoryID','$UnitID','$PlantID','$ShiftID','$PackingStyleID',
                //  '$StockHolderID','$Remarks','$AbStatus', '$ProdDayCode', '$ProdBatchCode','$TubNo','$TubCode','$LineNumber'
                //  ,'$FreezingQuantity','$TrackID','$PlantID','$TrackBreakUpID','$GradingBreakupID','$ProductionCategory','$TypeofMaterial',
                //  '$ProductionSlabs','$RejectionWeight','$ArrivalDate','$SoakingID','$ProductionList','$StartTimer','$EndTimer','$MixedBatch','$Weight')";

                $sqlQuery = "INSERT INTO `ProdFreezingData`(`PackingCount`,`ProductionWeight`, `AvalProductionWeight`, 
            `PackingTypeID`, `Grade`, `PoGradeId`, `FreezingType`, `VarietyID`, `ProductID`, `Galze`, `SockingType`, `Salt`,
            `ProductionDate`, `PostedDate`, `PoID`, `RmLotID`, `ProductionTypeID`, `ProductCategoryID`, `UnitID`, `PlantID`,
            `ShiftID`, `PackingStyleID`, `StockHolderID`, `Remarks`,  `AbStatus`, `ProdDayCode`, `ProdBatchCode`,
            `TubNo`,  `TubCode`, `LineNumber`,`FreezingQuantity`, `TrackID`, `SoakingPlantID`, `TrackBreakUpID`, `GradingBreakupID`,
             `ProductionCategory`, `TypeofMaterial`, `ProductionSlabs`, `RejectionWeight`, `ArrivalDate`, `SoakingID`,`ProductionIDList`,`StartTimer`,`EndTimer`,MixedBatch
             ,Mcs,LooseBags)
             values('$PackingCount','$ProductionWeight','$ProductionWeight','$PackingTypeID','$Grade','$PoGradeID',
             '$FreezingType','$VarietyID','$ProductID','$Galze','$SockingType','$Salt','$ProductionDate','$CreatedDateTime','$PoID','$RmLotID',
             '$ProductionTypeID', '$ProductCategoryID','$UnitID','$PlantID','$ShiftID','$PackingStyleID',
             '$StockHolderID','$Remarks','$AbStatus', '$ProdDayCode', '$ProdBatchCode','$TubNo','$TubCode','$LineNumber'
             ,'$FreezingQuantity','$TrackID','$PlantID','$TrackBreakUpID','$GradingBreakupID','$ProductionCategory','$TypeofMaterial',
             '$ProductionSlabs','$RejectionWeight','$ArrivalDate','$SoakingID','$ProductionList','$StartTimer','$EndTimer','$MixedBatchCode'
             ,'$Mcs','$LooseBags')";
                ///echo '<pre>';print_r($sqlQuery);die;
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();

                // var_dump( $sqlQuery);exit;
                $lastInsertId = $this->conn->lastInsertId();
                if ($stmt) {
                    $createdFreezing = $this->auditLogger->fetchRowById('ProdFreezingData', 'ProductionID', $lastInsertId);
                    $this->auditLogger->logInsert('ProdFreezingData', $lastInsertId, $createdFreezing, $RequestBody, [
                        'ReferenceNo' => $TrackID,
                        'Remarks' => 'Production freezing row created',
                    ]);
                }

                //  if ($IsStoreProductionLinked == 1) { //uma
                if(!trim($MixedBatchCode)){
                    $TotalProductionWeight = $this->getProductionWeight($SoakingID);

                    $SqlUpdateSoakingOuyQty = "UPDATE `ProdSoakingDetails` SET `SoakOutQty`='$TotalProductionWeight' WHERE `SckID`='$SoakingID'";
                    $stmtUpdateSoakingOuyQty = $this->conn->prepare($SqlUpdateSoakingOuyQty);
                    $stmtUpdateSoakingOuyQty->execute();


                    if ($RejectionWeight != 0) {
                        $this->getAutogeneratedTrackCode($RequestBody, $lastInsertId);
                    }
                }
                
                //    }

                if ($IsStoreProductionLinked == 1) {

                    $sqlFreezingDataQuery = "SELECT * FROM `ProdFreezingData` WHERE 
                TrackID='$TrackID'and ProductionDate='$ProductionDate'  and PackingTypeID='$PackingTypeID' and 
                ProductCategoryID='$ProductCategoryID' and PackingStyleID='$PackingStyleID' and AbStatus='$AbStatus'
                and LineNumber='$LineNumber' and PoID='$PoID' and PoGradeId='$PoGradeID' and Grade='$Grade'and SockingType='$SockingType'
                and FreezingType='$FreezingType'and VarietyID='$VarietyID' and TubNo='$TubNo' AND MixedBatch='$MixedBatchCode' AND Mcs='$Mcs' and LooseBags='$LooseBags'";
                    $freezingDataBeforUpdate = $this->conn->prepare($sqlFreezingDataQuery);
                    $freezingDataBeforUpdate->execute();
                    $freezingDataBeforUpdate = $freezingDataBeforUpdate->fetch(PDO::FETCH_ASSOC);
                    $RequestBody['OldProductionDate'] = $freezingDataBeforUpdate['ProductionDate'];
                    $RequestBody['OldProductionSlabs'] = $freezingDataBeforUpdate['ProductionSlabs'];
                    $RequestBody['OldRejectionWeight'] = $freezingDataBeforUpdate['RejectionWeight'];
                    $RequestBody['OldProductionWeight'] = $freezingDataBeforUpdate['ProductionWeight'];
                    $RequestBody['OldPackingTypeID'] = $freezingDataBeforUpdate['PackingTypeID'];
                    $RequestBody['OldGrade'] = $freezingDataBeforUpdate['Grade'];
                    $RequestBody['OldPoGradeId'] = $freezingDataBeforUpdate['PoGradeId'];
                    $RequestBody['OldVarietyID'] = $freezingDataBeforUpdate['VarietyID'];
                    $RequestBody['OldProductCategoryID'] = $freezingDataBeforUpdate['ProductCategoryID'];
                    $RequestBody['OldPackingStyleID'] = $freezingDataBeforUpdate['PackingStyleID'];
                    $RequestBody['OldAbStatus'] = $freezingDataBeforUpdate['AbStatus'];
                    $RequestBody['OldLineNumber'] = $freezingDataBeforUpdate['LineNumber'];
                    $RequestBody['OldFreezingType'] = $freezingDataBeforUpdate['FreezingType'];
                    $RequestBody['OldPoID'] = $freezingDataBeforUpdate['PoID'];
                    $RequestBody['OldMixedBatch'] = $freezingDataBeforUpdate['MixedBatch'];

                    $RequestBody['OldMcs'] = $freezingDataBeforUpdate['Mcs'];
                    $RequestBody['OldLooseBags'] = $freezingDataBeforUpdate['LooseBags'];

                    $this->postProductionDatatoStore($RequestBody);
                }
                if(!trim($MixedBatchCode)){
                    $this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID, $RequestBody);
                }

                $ProductionID = $this->conn->lastInsertId();
                $RequestBody['ProductionID'] = $ProductionID;
                $Data['Error'] = null;
                $Data['Msg'] = 'Posted Successfully';
                $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
                return $Data;
            }
        } else if ($ProductionID != '') {

            $sqlFreezingDataQuery = "SELECT * FROM `ProdFreezingData` WHERE `ProductionID`='$ProductionID'";
            $freezingDataBeforUpdate = $this->conn->prepare($sqlFreezingDataQuery);
            $freezingDataBeforUpdate->execute();
            $freezingDataBeforUpdate = $freezingDataBeforUpdate->fetch(PDO::FETCH_ASSOC);
            $existingFreezing = $freezingDataBeforUpdate;

            $RequestBody['OldProductionDate'] = $freezingDataBeforUpdate['ProductionDate'];
            $RequestBody['OldProductionSlabs'] = $freezingDataBeforUpdate['ProductionSlabs'];
            $RequestBody['OldRejectionWeight'] = $freezingDataBeforUpdate['RejectionWeight'];
            $RequestBody['OldProductionWeight'] = $freezingDataBeforUpdate['ProductionWeight'];
            $RequestBody['OldPackingTypeID'] = $freezingDataBeforUpdate['PackingTypeID'];
            $RequestBody['OldGrade'] = $freezingDataBeforUpdate['Grade'];
            $RequestBody['OldPoGradeId'] = $freezingDataBeforUpdate['PoGradeId'];
            $RequestBody['OldVarietyID'] = $freezingDataBeforUpdate['VarietyID'];
            $RequestBody['OldProductCategoryID'] = $freezingDataBeforUpdate['ProductCategoryID'];
            $RequestBody['OldPackingStyleID'] = $freezingDataBeforUpdate['PackingStyleID'];
            $RequestBody['OldAbStatus'] = $freezingDataBeforUpdate['AbStatus'];
            $RequestBody['OldLineNumber'] = $freezingDataBeforUpdate['LineNumber'];
            $RequestBody['OldFreezingType'] = $freezingDataBeforUpdate['FreezingType'];
            $RequestBody['OldPoID'] = $freezingDataBeforUpdate['PoID'];
            $RequestBody['OldMixedBatch'] = $freezingDataBeforUpdate['MixedBatch'];

            $RequestBody['OldMcs'] = $freezingDataBeforUpdate['Mcs'];
            $RequestBody['OldLooseBags'] = $freezingDataBeforUpdate['LooseBags'];

            if ($this->CheckProductionDataInserted($RequestBody)) {
                $Data['Error'] = null;
                $Data['Msg'] = "Edit not possible inward already added to this production";
                $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
                return $Data;
            }
            // get proddate from db freedinData
            // $sqlQuery = "UPDATE `ProdFreezingData` SET  `PackingCount`='$PackingCount',`ProductionWeight`='$ProductionWeight',
            // `AvalProductionWeight`='$ProductionWeight',`PackingTypeID`='$PackingTypeID',
            // `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`ProductID`='$ProductID',
            // `Galze`='$Galze',`SockingType`='$SockingType',`Salt`='$Salt',
            // `ProductionDate`='$ProductionDate',`PostedDate`='$CreatedDateTime',`PoID`='$PoID',`RmLotID`='$RmLotID',
            // `ProductionTypeID`='$ProductionTypeID',`ProductCategoryID`='$ProductCategoryID',`UnitID`='$UnitID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',
            // `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='$Remarks',`AbStatus`='$AbStatus',
            // `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode'  ,`TubNo`='$TubNo',`TubCode`='$TubCode',`LineNumber` ='$LineNumber',
            // `FreezingQuantity`='$FreezingQuantity',`TrackID`='$TrackID',
            // `SoakingPlantID`='$PlantID', `TrackBreakUpID`='$TrackBreakUpID',`GradingBreakupID`='$GradingBreakupID'
            // ,`ProductionCategory`='$ProductionCategory',`TypeofMaterial`='$TypeofMaterial',`ProductionSlabs`='$ProductionSlabs',
            // `RejectionWeight`='$RejectionWeight',  `ArrivalDate`='$ArrivalDate',  `SoakingID`='$SoakingID',`ProductionIDList`='$ProductionList' 
            // ,`StartTimer`='$StartTimer' ,`EndTimer`='$EndTimer' ,`MixedBatch`='$MixedBatch',`Weight`='$Weight'  where `ProductionID`='$ProductionID'";
            if(trim($MixedBatchCode)){
                $ProductionWeight = $MixedBatchWeight;
                $ProductionSlabs = round($MixedBatchSlabs, 2);
            }

            $sqlQuery = "UPDATE `ProdFreezingData` SET  `PackingCount`='$PackingCount',`ProductionWeight`='$ProductionWeight',
            `AvalProductionWeight`='$ProductionWeight',`PackingTypeID`='$PackingTypeID',
            `Grade`='$Grade',`PoGradeId`='$PoGradeID',`FreezingType`='$FreezingType',`VarietyID`='$VarietyID',`ProductID`='$ProductID',
            `Galze`='$Galze',`SockingType`='$SockingType',`Salt`='$Salt',
            `ProductionDate`='$ProductionDate',`PostedDate`='$CreatedDateTime',`PoID`='$PoID',`RmLotID`='$RmLotID',
            `ProductionTypeID`='$ProductionTypeID',`ProductCategoryID`='$ProductCategoryID',`UnitID`='$UnitID',`PlantID`='$PlantID',`ShiftID`='$ShiftID',
            `PackingStyleID`='$PackingStyleID',`StockHolderID`='$StockHolderID',`Remarks`='$Remarks',`AbStatus`='$AbStatus',
            `ProdDayCode`='$ProdDayCode',`ProdBatchCode`='$ProdBatchCode'  ,`TubNo`='$TubNo',`TubCode`='$TubCode',`LineNumber` ='$LineNumber',
            `FreezingQuantity`='$FreezingQuantity',`TrackID`='$TrackID',
            `SoakingPlantID`='$PlantID', `TrackBreakUpID`='$TrackBreakUpID',`GradingBreakupID`='$GradingBreakupID'
            ,`ProductionCategory`='$ProductionCategory',`TypeofMaterial`='$TypeofMaterial',`ProductionSlabs`='$ProductionSlabs',
            `RejectionWeight`='$RejectionWeight',  `ArrivalDate`='$ArrivalDate',  `SoakingID`='$SoakingID',`ProductionIDList`='$ProductionList' 
            ,`StartTimer`='$StartTimer' ,`EndTimer`='$EndTimer',MixedBatch='$MixedBatchCode' 
            ,`Mcs`='$Mcs',`LooseBags`='$LooseBags'
             where `ProductionID`='$ProductionID'";
            
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();

            $fetchRequest['TrackID'] = $TrackID;
            if ($stmt) {
                $updatedFreezing = $this->auditLogger->fetchRowById('ProdFreezingData', 'ProductionID', $ProductionID);
                $this->auditLogger->logUpdate('ProdFreezingData', $ProductionID, $existingFreezing, $updatedFreezing, $RequestBody, [
                    'ReferenceNo' => $TrackID,
                    'Remarks' => 'Production freezing row updated',
                ]);
                if(!trim($MixedBatchCode)){
                    $this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody);


                    $TotalProductionWeight = $this->getProductionWeight($SoakingID);
                    $SqlUpdateSoakingOuyQty = "UPDATE `ProdSoakingDetails` SET `SoakOutQty`='$TotalProductionWeight' WHERE `SckID`='$SoakingID'";
                    $stmtUpdateSoakingOuyQty = $this->conn->prepare($SqlUpdateSoakingOuyQty);
                    $stmtUpdateSoakingOuyQty->execute();
                }
                if ($IsStoreProductionLinked == 1) { //uma
                    $this->postProductionDatatoStore($RequestBody);
                }
                // if ($RejectionWeight != 0) {
                if(!trim($MixedBatchCode)){
                    $this->getAutogeneratedTrackCode($RequestBody, '');
                }
                // }
                $Data['Error'] = null;
                $Data['Msg'] = 'Updated Successfully';
                $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
                return $Data;
            }
        }
    }

    public function getBalanceKgs($TotalWeight, $ProductionID, $proddataRow)
    {

        $sqlFields = "ProductionWeight,AvalProductionWeight";

        $sqlQuery = "SELECT $sqlFields FROM `ProdFreezingData` WHERE ProductionID='$ProductionID' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalWeight;
        $TotalWeight = round(($dataRow['ProductionWeight'] - $dataRow['AvalProductionWeight']), 2);
        $ArrayBalance['BalanceWeight'] = $TotalWeight;

        return $ArrayBalance;
    }
    public function getProductionWeight($SoakingID)
    {

        $sqlQuery = "SELECT sum(ProductionWeight+RejectionWeight) AS ProductionWeight FROM `ProdFreezingData` WHERE SoakingID='$SoakingID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ProductionWeight = $dataRow['ProductionWeight'];
        // // $TotalWeight;
        // $TotalWeight = round(($dataRow['ProductionWeight'] - $dataRow['AvalProductionWeight']), 2);
        // $ArrayBalance['BalanceWeight'] = $TotalWeight;

        return $ProductionWeight;
    }

    public function deleteDailyFreezing($RequestBody)
    {
 
        $ProdID = $RequestBody['ProductionID'];
        $TrackID = $RequestBody['TrackID'];
        $IsDeductGlazeFW = $RequestBody['IsDeductGlazeFW'];
        $MixedBatchCode = $RequestBody['MixedBatchCode'];

        
        $sqlTub = "SELECT TubNo FROM ProdFreezingData WHERE ProductionID='$ProdID'";
        $stmtTub = $this->conn->prepare($sqlTub);
        $stmtTub->execute();
        $dataRowTub = $stmtTub->fetch(PDO::FETCH_ASSOC);
        $TubNo = $dataRowTub['TubNo'];
        $where = " AND  PFD.TrackID='$TrackID' AND PFD.TubNo=$TubNo ";
        if(trim($MixedBatchCode)){
            $where = " AND  PFD.MixedBatch = '$MixedBatchCode' ";
        }

        $sqlProductionData = "SELECT CDPD.PackingStyleID,ProductionWeight,CDPD.FreezingProductionWeight,PFD.ProductionSlabs,ArrivalDate,CDPD.VarietyID,
        CDPD.ProductionID as DailyProductionID,CDPD.AvalMcs,CDPD.LooseBags,PFD.TubNo
         FROM  `ProdFreezingData` as PFD  left join CsDailyProductionData as CDPD on CDPD.TrackID=PFD.TrackID and 
        CDPD.ProductionDate=PFD.ProductionDate and CDPD.PackingTypeID=PFD.PackingTypeID 
        and CDPD.ProductCategoryID=PFD.ProductCategoryID and CDPD.PackingStyleID=PFD.PackingStyleID and CDPD.AbStatus=PFD.AbStatus
        and CDPD.LineNumber=PFD.LineNumber and CDPD.PoID=PFD.PoID and CDPD.PoGradeId=PFD.PoGradeId and CDPD.Grade=PFD.Grade
        and CDPD.SockingType=PFD.SockingType and CDPD.FreezingType=PFD.FreezingType and CDPD.VarietyID=PFD.VarietyID AND PFD.TubNo=CDPD.TubNo
        WHERE  PFD.ProductionID='$ProdID' $where ";
       
        $stmtProductionData = $this->conn->prepare($sqlProductionData);
        $stmtProductionData->execute();
        $dataRowProductionData = $stmtProductionData->fetch(PDO::FETCH_ASSOC);
        $DailyProductionID = $dataRowProductionData['DailyProductionID'];
        $ProductionWeight = $dataRowProductionData['ProductionWeight'];
        $AvalFreezingProductionWeight = $dataRowProductionData['FreezingProductionWeight'];
        $PackingStyleID = $dataRowProductionData['PackingStyleID'];
        $ProductionSlabs = $dataRowProductionData['ProductionSlabs'];
        $ArrivalDate = $dataRowProductionData['ArrivalDate'];
        $VarietyID = $dataRowProductionData['VarietyID'];
        $SqlProductID = "SELECT ProductID,Glaze,GlazePerc FROM `ScrmPIVarietyList` as SPVL
        WHERE VarietyID='$VarietyID'";
        $stmtProductID = $this->conn->prepare($SqlProductID);
        $stmtProductID->execute();
        $RowProductID = $stmtProductID->fetch(PDO::FETCH_ASSOC);
        $ProductID = $RowProductID['ProductID'];
        $GlazeType = $RowProductID['Glaze'];
        $Galze = $RowProductID['GlazePerc'];

        if ($IsDeductGlazeFW == 1 && strpos($GlazeType, 'FW') === 0 && $Galze > 0) {

            $GlazeWeight = ($ProductionWeight * $Galze) / 100;
            $ProductionWeight = $ProductionWeight + $GlazeWeight;
        }
        $FreezingProductionWeight = $AvalFreezingProductionWeight - $ProductionWeight;
        $ArraygetMcsandLoosebags = $this->getMcsandLoosebags($PackingStyleID, $FreezingProductionWeight, $ProductionSlabs, 1);
        
        $AvalMcs = $ArraygetMcsandLoosebags['Mcs'];
        $AvalLooseBags = $ArraygetMcsandLoosebags['Loose'];

        $sqlInwardCount = "SELECT count(*) as InwardCount FROM `CsStockData` WHERE ProductionID='$DailyProductionID'";
        $stmtInwardCount = $this->conn->prepare($sqlInwardCount);
        $stmtInwardCount->execute();
        $dataRowInwardCount = $stmtInwardCount->fetch(PDO::FETCH_ASSOC);
        $InwardCount = $dataRowInwardCount['InwardCount'];
        if(!$DailyProductionID){
            $InwardCount = 0;
        }
        if ($InwardCount == 0) {
            $existingFreezing = $this->auditLogger->fetchRowById('ProdFreezingData', 'ProductionID', $ProdID);
            $sqlDelete = "DELETE FROM ProdFreezingData WHERE ProductionID='$ProdID'";
            $stmtDelete = $this->conn->prepare($sqlDelete);
            $stmtDelete->execute();

            if ($stmtDelete) {
                $this->auditLogger->logDelete('ProdFreezingData', $ProdID, $existingFreezing, $RequestBody, [
                    'ReferenceNo' => $TrackID,
                    'Remarks' => 'Production freezing row deleted',
                ]);
                if(!trim($MixedBatchCode)){
                    $this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody);


                    /******delete rejection trackIDs******* */
                    $this->deleteRejectionTrackIds($ArrivalDate,$RequestBody);

                    /******delete rejection trackIDs******* */
                }


                if ($FreezingProductionWeight <= 0) {
                    $sqlDeleteDailyProduction = "DELETE FROM CsDailyProductionData WHERE ProductionID='$DailyProductionID'";
                    $stmtDeleteDailyProduction = $this->conn->prepare($sqlDeleteDailyProduction);
                    $stmtDeleteDailyProduction->execute();
                } else {

                    $ActualKgs = $this->getCsStockAvalWeightinKgs($PackingStyleID, $AvalMcs, $AvalLooseBags);

                    $sqlMcsUpdate = "UPDATE `CsDailyProductionData` SET `ActualMcs`='$AvalMcs',`ActualLooseBags`='$AvalLooseBags',`AvalMcs`='$AvalMcs',
                    `LooseBags`='$AvalLooseBags',`ActualKgs`=' $ActualKgs',`AvalKgs`='$ActualKgs', `FreezingProductionWeight`='$FreezingProductionWeight' WHERE `ProductionID`='$DailyProductionID'";
                    $stmtMcsUpdate = $this->conn->prepare($sqlMcsUpdate);
                    $stmtMcsUpdate->execute();
                }
                $this->Error = null;
                $this->Msg = 'Delete Successfully';
            }
        } else {
            $this->Error = null;
            $this->Msg = 'Delete not possible inward already added to this production';
        }
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $this->getDailyFreezingDetails($RequestBody);
        return $Data;
    }

    function getStockHolderID($PlantID)
    {
        $SqlStockHolderIDQuery = "SELECT TenantID FROM `CsTenant` where ProcessorID='$PlantID'";
        $StmtStockHolderID = $this->conn->prepare($SqlStockHolderIDQuery);
        $StmtStockHolderID->execute();
        $dataRowStockHolderID = $StmtStockHolderID->fetch(PDO::FETCH_ASSOC);
        $StockHolderID = $dataRowStockHolderID['TenantID'];
        return $StockHolderID;
    }

    function getMcsandLoosebags($PackingStyleID, $ProductionWeight, $ProductionSlabs, $useWeight)
    {
        $ArrayMcsandLoosebags = [];
        $SqlgetMcsandLooseQuery = "SELECT TotalSlabs,SlabWeight,MFactor FROM `AdminPackingStyles` where PCKID='$PackingStyleID'";
        $StmtgetMcsandLooseQuery = $this->conn->prepare($SqlgetMcsandLooseQuery);
        $StmtgetMcsandLooseQuery->execute();
        $dataRowgetMcsandLooseQuery = $StmtgetMcsandLooseQuery->fetch(PDO::FETCH_ASSOC);

        $SlabWeight = $dataRowgetMcsandLooseQuery['SlabWeight'];
        $TotalSlabs = $dataRowgetMcsandLooseQuery['TotalSlabs'];
        $MFactor = $dataRowgetMcsandLooseQuery['MFactor'];
        // $Slabs = floor($ProductionWeight / ($SlabWeight * $MFactor));
        if ($useWeight == 1) {
            $Slabs = ($ProductionWeight / ($SlabWeight * $MFactor));
        } else {
            $Slabs = $ProductionSlabs;
        }
        $Mcs = floor($Slabs / $TotalSlabs);
        // $Loose = $Slabs % $TotalSlabs;
        $Loose = $Slabs - ($Mcs * $TotalSlabs);
        $ArrayMcsandLoosebags['Mcs'] = $Mcs;
        $ArrayMcsandLoosebags['Loose'] = $Loose;
        $ArrayMcsandLoosebags['TotalSlabs'] = $dataRowgetMcsandLooseQuery['TotalSlabs'];
        $ArrayMcsandLoosebags['SlabWeight'] = $dataRowgetMcsandLooseQuery['SlabWeight'];
        $ArrayMcsandLoosebags['MFactor'] = $dataRowgetMcsandLooseQuery['MFactor'];
        return $ArrayMcsandLoosebags;
    }

    function deleteRejectionTrackIds($ArrivalDate,array $RequestBody = null)
    {
        $SqlRejectionTrackIDCount = "SELECT count(*) as RejectionCount,sum(RejectionWeight) as RejectionWeight FROM `ProdFreezingData` 
        where ArrivalDate='$ArrivalDate' and RejectionWeight>0";
        $stmtRejectionTrackIDCount = $this->conn->prepare($SqlRejectionTrackIDCount);
        $stmtRejectionTrackIDCount->execute();
        $dataRowRejectionTrackIDCount = $stmtRejectionTrackIDCount->fetch(PDO::FETCH_ASSOC);
        $RejectionCount = $dataRowRejectionTrackIDCount['RejectionCount'];
        $RejectionWeight = $dataRowRejectionTrackIDCount['RejectionWeight'];

        $SqlReceivingIds = "SELECT  ReceivingID FROM `RmReceivingDetails` WHERE ArrivalDate='$ArrivalDate' and OutStage=3 and IsStoreMaterial=0";
        $stmtReceivingIds = $this->conn->prepare($SqlReceivingIds);
        $stmtReceivingIds->execute();
        $dataRowReceivingIds = $stmtReceivingIds->fetch(PDO::FETCH_ASSOC);
        $ReceivingID = $dataRowReceivingIds['ReceivingID'];

        if ($RejectionCount == 0) {
            $existingBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivingID = ?", [$ReceivingID]);


            $SqlDeleteBreakUp = "DELETE FROM `RmReceivingBreakup` WHERE ReceivingID='$ReceivingID'";
            $stmtDeleteBreakUp = $this->conn->prepare($SqlDeleteBreakUp);
            $stmtDeleteBreakUp->execute();

            if ($stmtDeleteBreakUp) {
                $this->auditLogger->logDelete('RmReceivingBreakup', $ReceivingID, $existingBreakup, $RequestBody, [
                    'ReferenceNo' => $ReceivingID,
                    'Remarks' => 'Production rejection breakup deleted',
                ]);
                $existingReceiving = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingDetails WHERE ArrivalDate = ? and OutStage = 3 and IsStoreMaterial = 0", [$ArrivalDate]);
                $SqlDeleteRejectionTrack = "DELETE FROM `RmReceivingDetails` WHERE  ArrivalDate='$ArrivalDate' and OutStage=3 and IsStoreMaterial=0";
                $stmtDeleteRejectionTrack = $this->conn->prepare($SqlDeleteRejectionTrack);
                $stmtDeleteRejectionTrack->execute();
                if ($stmtDeleteRejectionTrack) {
                    $entityId = isset($existingReceiving['ReceivingID']) ? $existingReceiving['ReceivingID'] : $ArrivalDate;
                    $this->auditLogger->logDelete('RmReceivingDetails', $entityId, $existingReceiving, $RequestBody, [
                        'ReferenceNo' => $entityId,
                        'Remarks' => 'Production rejection track deleted',
                    ]);
                }
            }
        } else {
            $existingBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivingID = ?", [$ReceivingID]);
            $SqlUpdateBreakUp = "UPDATE `RmReceivingBreakup` SET `ReceivingQty`='$RejectionWeight' where ReceivingID='$ReceivingID'";
            $stmtUpdateBreakUp = $this->conn->prepare($SqlUpdateBreakUp);
            $stmtUpdateBreakUp->execute();
            if ($stmtUpdateBreakUp) {
                $updatedBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivingID = ?", [$ReceivingID]);
                $this->auditLogger->logUpdate('RmReceivingBreakup', $ReceivingID, $existingBreakup, $updatedBreakup, $RequestBody, [
                    'ReferenceNo' => $ReceivingID,
                    'Remarks' => 'Production rejection breakup updated',
                ]);
            }
        }
        return "success";
    }

    function postProductionDatatoStore($RequestBody)
    {
        $TrackID = $RequestBody['TrackID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $PackingTypeID = $RequestBody['PackingType'];
        $PackingStyleID = $RequestBody['PackingStyle'];
        $ProductCategoryID = $RequestBody['ProductCategory'];
        $AbStatus = $RequestBody['AbStatus'];
        $LineNumber = $RequestBody['LineNumber'];
        $Grade = $RequestBody['Grade'];
        $SockingType = $RequestBody['Soaking'];
        $FreezingType = $RequestBody['FreezingType'];
        $PoID = $RequestBody['PoID'];
        $PoGradeID = $RequestBody['PoGradeID'];
        $VarietyID = $RequestBody['Variety'];
        $TubNo = $RequestBody['TubNumberValue'];
        $MixedBatch = $RequestBody['MixedBatchCode'];

        $Mcs = $RequestBody['txtMcs'];
        $LooseBags = $RequestBody['txtLooseBags'];

        $OldProductionDate = isset($RequestBody['OldProductionDate']) ? $RequestBody['OldProductionDate'] : $RequestBody['ProductionDate'];
        $OldGrade = isset($RequestBody['OldGrade']) ? $RequestBody['OldGrade'] : $RequestBody['Grade'];
        $OldPoGradeId = isset($RequestBody['OldPoGradeId']) ? $RequestBody['OldPoGradeId'] : $RequestBody['PoGradeId'];
        $OldVarietyID = isset($RequestBody['OldVarietyID']) ? $RequestBody['OldVarietyID'] : $RequestBody['Variety'];
        $OldProductCategoryID = isset($RequestBody['OldProductCategoryID']) ? $RequestBody['OldProductCategoryID'] : $RequestBody['ProductCategory'];
        $OldPackingStyleID = isset($RequestBody['OldPackingStyleID']) ? $RequestBody['OldPackingStyleID'] : $RequestBody['PackingStyle'];
        $OldPackingTypeID = isset($RequestBody['OldPackingTypeID']) ? $RequestBody['OldPackingTypeID'] : $RequestBody['PackingType'];
        $OldAbStatus = isset($RequestBody['OldAbStatus']) ? $RequestBody['OldAbStatus'] : $RequestBody['AbStatus'];
        $OldLineNumber = isset($RequestBody['OldLineNumber']) ? $RequestBody['OldLineNumber'] : $RequestBody['LineNumber'];
        $OldFreezingType = isset($RequestBody['OldFreezingType']) ? $RequestBody['OldFreezingType'] : $RequestBody['FreezingType'];
        $OldPoID = isset($RequestBody['OldPoID']) ? $RequestBody['OldPoID'] : $RequestBody['PoID'];
        $OldMixedBatch = isset($RequestBody['OldMixedBatch']) ? $RequestBody['OldMixedBatch'] : $RequestBody['MixedBatchCode'];

        $OldMcs = isset($RequestBody['OldMcs']) ? $RequestBody['OldMcs'] : $RequestBody['txtMcs'];
        $OldLooseBags = isset($RequestBody['OldLooseBags']) ? $RequestBody['OldLooseBags'] : $RequestBody['txtLooseBags'];


        // $sqlgetTotalProductionWeight = "SELECT sum(ProductionWeight) as TotalProductionWeight,sum(ProductionSlabs) as ProductionSlabs   FROM `ProdFreezingData`
        // WHERE TrackID='$TrackID'and ProductionDate='$ProductionDate'  and PackingTypeID='$PackingTypeID' and 
        // ProductCategoryID='$ProductCategoryID' and PackingStyleID='$PackingStyleID' and AbStatus='$AbStatus'
        // and LineNumber='$LineNumber' and PoID='$PoID' and PoGradeId='$PoGradeID' and Grade='$Grade'and SockingType='$SockingType'
        // and FreezingType='$FreezingType'"; 

        $sqlgetTotalProductionWeight = "SELECT sum(ProductionWeight) as TotalProductionWeight,sum(ProductionSlabs) as ProductionSlabs   FROM `ProdFreezingData`
        WHERE TrackID='$TrackID'and ProductionDate='$ProductionDate'  and PackingTypeID='$PackingTypeID' and 
        ProductCategoryID='$ProductCategoryID' and PackingStyleID='$PackingStyleID' and AbStatus='$AbStatus'
        and LineNumber='$LineNumber' and PoID='$PoID' and PoGradeId='$PoGradeID' and Grade='$Grade'and SockingType='$SockingType'
        and FreezingType='$FreezingType'and VarietyID='$VarietyID' and TubNo='$TubNo' and MixedBatch='$MixedBatch'";

        $stmtgetTotalProductionWeight = $this->conn->prepare($sqlgetTotalProductionWeight);
        $stmtgetTotalProductionWeight->execute();
        $dataRowgetTotalProductionWeight = $stmtgetTotalProductionWeight->fetch(PDO::FETCH_ASSOC);
        $TotalProductionWeight = $dataRowgetTotalProductionWeight['TotalProductionWeight'];
        $ProductionSlabs = $dataRowgetTotalProductionWeight['ProductionSlabs'];


        $ArraygetMcsandLoosebags = $this->getMcsandLoosebags($PackingStyleID, $TotalProductionWeight, $ProductionSlabs, 0);
        $RequestBody['Mcs'] = $ArraygetMcsandLoosebags['Mcs']; //PRODUCTION REQUEST

        $RequestBody['LooseBags'] = $ArraygetMcsandLoosebags['Loose']; //PRODUCTION REQUEST
        $RequestBody['FreezingProductionWeight'] = $TotalProductionWeight; //PRODUCTION REQUEST

        $sqlProductionDataCount = "SELECT *  FROM `CsDailyProductionData`
        WHERE TrackID='$TrackID'and ProductionDate='$OldProductionDate' 
        and PackingTypeID='$OldPackingTypeID' and ProductCategoryID='$OldProductCategoryID' and PackingStyleID='$OldPackingStyleID'
        and AbStatus='$OldAbStatus'  and LineNumber='$OldLineNumber' and PoID='$OldPoID' and PoGradeId='$OldPoGradeId' and Grade='$OldGrade' 
        and FreezingType='$OldFreezingType'and VarietyID='$OldVarietyID' and TubNo='$TubNo' AND MixedBatch='$OldMixedBatch'";

       $stmtProductionDataCount = $this->conn->prepare($sqlProductionDataCount);
        $stmtProductionDataCount->execute();
        $dataRowProductionDataCount = $stmtProductionDataCount->fetch(PDO::FETCH_ASSOC);

        $OldProductionID = $dataRowProductionDataCount['ProductionID'];

        // $RequestBody['ProductionID'] = $ProductionID;
        // $InwardCount = 0;
        // if ($ProductionID != '') {
        //     $RequestBody['Type'] = "ProductUpdate";
        // }

        $ProductionID = '';

        if ($OldProductionID != '' && $OldProductionID != '0') {
            // $oldSlabs = $RequestBody['OldProductionSlabs'];
            // $oldProdWt = $RequestBody['OldProductionWeight'];
            // $OldRejWt = $RequestBody['OldRejectionWeight'];

            // $ArraygetMcsandLoosebags = $this->getMcsandLoosebags($OldPackingStyleID, ($oldProdWt + $OldRejWt), $oldSlabs, 0);
            // $deductMcs = $ArraygetMcsandLoosebags['Mcs']; //PRODUCTION REQUEST
            // $deductLooseBags = $ArraygetMcsandLoosebags['Loose']; //PRODUCTION REQUEST

            // if ($deductLooseBags > $dataRowProductionDataCount['ActualLooseBags']) {
            //     $extraMcsDeduction = ceil($deductLooseBags / $ArraygetMcsandLoosebags['TotalSlabs']);
            //     $deductMcs += $extraMcsDeduction;
            //     $deductLooseBags = ($ArraygetMcsandLoosebags['TotalSlabs'] - ($deductLooseBags % $ArraygetMcsandLoosebags['TotalSlabs']));

            // } else {
            //     $deductLooseBags = $dataRowProductionDataCount['ActualLooseBags'] - $deductLooseBags;
            // }


            // $updateDailyProddata = "UPDATE `CsDailyProductionData` SET `ActualMcs`=" . ($dataRowProductionDataCount['ActualMcs'] - $deductMcs) . ",
            //     `ActualLooseBags`=" . $deductLooseBags . ",`AvalMcs`=" . ($dataRowProductionDataCount['ActualMcs'] - $deductMcs) .
            //     ",`LooseBags`=" . $deductLooseBags . ",`ActualKgs`=" . ($dataRowProductionDataCount['ActualKgs'] - ($oldProdWt + $OldRejWt)) .
            //     ",`AvalKgs`=" . ($dataRowProductionDataCount['ActualKgs'] - ($oldProdWt + $OldRejWt)) . ",`FreezingProductionWeight` =" . ($dataRowProductionDataCount['FreezingProductionWeight'] - ($oldProdWt + $OldRejWt)) . "
            //     WHERE ProductionID='$OldProductionID'";
            // $stmtDailyProddata = $this->conn->prepare($updateDailyProddata);
            // $stmtDailyProddata->execute();
            // if ($stmtDailyProddata) {
            //     $RequestBody['ProductionID'] = "";
            //     $sqlProductionDataCount1 = "SELECT *  FROM `CsDailyProductionData`
            //             WHERE ProductionID='$OldProductionID'";
            //     $stmtProductionDataCount1 = $this->conn->prepare($sqlProductionDataCount1);
            //     $stmtProductionDataCount1->execute();
            //     $dataRowProductionDataCount1 = $stmtProductionDataCount1->fetch(PDO::FETCH_ASSOC);
            //     if ($dataRowProductionDataCount1['ActualMcs'] == 0 && $dataRowProductionDataCount1['ActualLooseBags'] == 0) {
            //         $deleteProdData = "DELETE  FROM `CsDailyProductionData`
            //                     WHERE ProductionID='$OldProductionID'";
            //         $stmtdeleteProdData = $this->conn->prepare($deleteProdData);
            //         $stmtdeleteProdData->execute();
            //     }
            // }

            $sqlProductionDataCount = "SELECT *  FROM `CsDailyProductionData`
            WHERE TrackID='$TrackID'and ProductionDate='$ProductionDate'  and PackingTypeID='$PackingTypeID' and 
                ProductCategoryID='$ProductCategoryID' and PackingStyleID='$PackingStyleID' and AbStatus='$AbStatus'
                and LineNumber='$LineNumber' and PoID='$PoID' and PoGradeId='$PoGradeID' and Grade='$Grade'and SockingType='$SockingType'
                and FreezingType='$FreezingType'and VarietyID='$VarietyID' and TubNo='$TubNo'  AND MixedBatch='$MixedBatch'";
            $stmtProductionDataCount = $this->conn->prepare($sqlProductionDataCount);
            $stmtProductionDataCount->execute();
            $dataRowProductionDataCount = $stmtProductionDataCount->fetch(PDO::FETCH_ASSOC);

            $ProductionID = $dataRowProductionDataCount['ProductionID'];

        }

        if ($ProductionID == '' && $OldProductionDate == $ProductionDate && $OldProductionID != '' && $OldProductionID != '0') {
            $ProductionID = $OldProductionID;
        }

        $RequestBody['ProductionID'] = $ProductionID;
        if ($ProductionID != '') {
            $RequestBody['Type'] = "ProductUpdate";
        }

        // $TestSqlLog = "INSERT INTO `TestSqlLog`(`query`) VALUES ('$ProductionID') ";
        // $stmtTestSqlLog = $this->conn->prepare($TestSqlLog);
        // $stmtTestSqlLog->execute();


        // if ($OldProductionDate != $ProductionDate) {
        //     // $this->utilController->UpdateTrackWiseBlockMeatWeight($TrackID);
        //     // $RequestBody['ProductionID'] = "";

        //     $AvalFreezingProductionWeight = $dataRowProductionDataCount['FreezingProductionWeight'];
        //     $PackingStyleID = $dataRowProductionDataCount['PackingStyleID'];
        //     $ProductionSlabs = $dataRowProductionDataCount['ProductionSlabs'];
        //     $FreezingProductionWeight = $AvalFreezingProductionWeight - $TotalProductionWeight;
        //     $ArraygetMcsandLoosebags = $this->getMcsandLoosebags($PackingStyleID, $FreezingProductionWeight, $ProductionSlabs,0);
        //     $AvalMcs = $ArraygetMcsandLoosebags['Mcs'];
        //     $AvalLooseBags = $ArraygetMcsandLoosebags['Loose'];
        //     $ActualKgs = $this->getCsStockAvalWeightinKgs($PackingStyleID, $AvalMcs, $AvalLooseBags);

        //     $TestSqlLog = "INSERT INTO `TestSqlLog`(`query`) VALUES ('$ActualKgs') ";
        //     $stmtTestSqlLog = $this->conn->prepare($TestSqlLog);
        //     $stmtTestSqlLog->execute();


        //     $sqlMcsUpdate = "UPDATE `CsDailyProductionData` SET `ActualMcs`='$AvalMcs',`ActualLooseBags`='$AvalLooseBags',`AvalMcs`='$AvalMcs',
        //     `LooseBags`='$AvalLooseBags',`ActualKgs`=' $ActualKgs',`AvalKgs`='$ActualKgs', `FreezingProductionWeight`='$FreezingProductionWeight' WHERE `ProductionID`='$ProductionID'";
        // $stmtMcsUpdate = $this->conn->prepare($sqlMcsUpdate);
        // $stmtMcsUpdate->execute();


        //     // if ($ActualKgs == 0) {
        //     //     $sqlProductionDataCount = "DELETE  FROM `CsDailyProductionData`
        //     //         WHERE TrackID='$TrackID'and ProductionDate='$ProductionDate' 
        //     //         and PackingTypeID='$PackingTypeID' and ProductCategoryID='$ProductCategoryID' and PackingStyleID='$PackingStyleID'
        //     //          and AbStatus='$AbStatus'  and LineNumber='$LineNumber' and PoID='$PoID' and PoGradeId='$PoGradeID' and Grade='$Grade' 
        //     //          and FreezingType='$FreezingType'and VarietyID='$VarietyID' ";
        //     //     $stmtProductionDataCount = $this->conn->prepare($sqlProductionDataCount);
        //     //     $stmtProductionDataCount->execute();
        //     // } else {
        //     //     $sqlMcsUpdate = "UPDATE `CsDailyProductionData` SET `ActualMcs`='$AvalMcs',`ActualLooseBags`='$AvalLooseBags',`AvalMcs`='$AvalMcs',
        //     //         `LooseBags`='$AvalLooseBags',`ActualKgs`=' $ActualKgs',`AvalKgs`='$ActualKgs', `FreezingProductionWeight`='$FreezingProductionWeight' WHERE `ProductionID`='$ProductionID'";
        //     //     $stmtMcsUpdate = $this->conn->prepare($sqlMcsUpdate);
        //     //     $stmtMcsUpdate->execute();
        //     // }

        // }
        unset($RequestBody['MCSRestriction']);
        $this->dailyProductionDeatilsController->postDailyProductionDetails($RequestBody);



        return 'Success';
    }

    function getTubNumberValue($TypeofMaterial, $ProductionDate)
    {
        $sqlgetTubNoValue = "SELECT Count(*) as TubNo FROM `ProdFreezingData` WHERE TypeofMaterial='$TypeofMaterial' and ProductionDate='$ProductionDate'";
        $stmtgetTubNoValue = $this->conn->prepare($sqlgetTubNoValue);
        $stmtgetTubNoValue->execute();
        $dataRowgetTubNoValue = $stmtgetTubNoValue->fetch(PDO::FETCH_ASSOC);
        $TubNo = $dataRowgetTubNoValue['TubNo'] + 1;
        return $TubNo;
    }

    function checkIsStoreMaterial($TrackID, $IsFreezing)
    {
        $Query = '';
        if ($IsFreezing == 1) {
            $Query = " and OutStage=3";
        }

        $sqlQueryIsStoreMaterial = "SELECT   IsStoreMaterial ,sum(ReceivingQty) as StoreQty,RRB.CsStockOutID,Reason as OutGroup from RmReceivingDetails   as RAD 
        left join RmReceivingBreakup as RRB on RRB.ReceivingID=RAD.ReceivingID 
        left join CsDailyStockOutData as CDSD on CDSD.CsStockOutID=RRB.CsStockOutID 
        WHERE  RAD.ReceivingID='$TrackID' $Query";
        $stmtIsStoreMaterial = $this->conn->prepare($sqlQueryIsStoreMaterial);
        $stmtIsStoreMaterial->execute();
        $dataRowIsStoreMaterial = $stmtIsStoreMaterial->fetch(PDO::FETCH_ASSOC);
        $ArrayStoreMaterial['IsStoreMaterial'] = $dataRowIsStoreMaterial['IsStoreMaterial'];
        $ArrayStoreMaterial['TotalVAQty'] = $dataRowIsStoreMaterial['StoreQty'];
        $ArrayStoreMaterial['StockOutID'] = $dataRowIsStoreMaterial['CsStockOutID'];
        $ArrayStoreMaterial['OutGroup'] = $dataRowIsStoreMaterial['OutGroup'];
        return $ArrayStoreMaterial;
    }
    public function getBalanceQty($RequestBody)  // for getting Balance Qty 24/09/2024
    {

        $TypeofMaterial = $RequestBody['TypeofMaterial'];
        $TubNumber = $RequestBody['TubNumber'];
        $TrackID = $RequestBody['TrackID'];

        $sqlFreezingQty = "SELECT SUM(ProductionWeight) AS ProductionWeight FROM ProdFreezingData 
        WHERE TubNo = '$TubNumber' AND TypeofMaterial = '$TypeofMaterial' and TrackID='$TrackID'";
        $stmtFreezingQty = $this->conn->prepare($sqlFreezingQty);
        $stmtFreezingQty->execute();
        $rowFreezingQty = $stmtFreezingQty->fetch(PDO::FETCH_ASSOC);
        $FreezingQty = $rowFreezingQty['ProductionWeight'];

        if ($TypeofMaterial == "SKM") {
            $sqlQty = "SELECT SUM(SoakOutQty) AS TotalQty FROM ProdSoakingDetails
            WHERE TubNumber = '$TubNumber' and TrackID='$TrackID'  AND CreatedFrom=1 ";
        } else if ($TypeofMaterial == "VM") {
            $sqlQty = "SELECT SUM(WtAftrVA) AS TotalQty FROM PpcValueAddition
            WHERE VLID = '$TubNumber' and TrackID='$TrackID'  AND CreatedFrom=1";
        } else if ($TypeofMaterial == "GM") {
            $sqlQty = "SELECT SUM(HLWeight) AS TotalQty FROM PpcHLGradingBreakup
            WHERE DataID = '$TubNumber'  AND CreatedFrom=1";
        } else if ($TypeofMaterial == "SM") {
            $sqlQty = "SELECT SUM(ReceivingQty) AS TotalQty FROM RmReceivingBreakup
            WHERE CsStockOutID = '$TubNumber' and ReceivingID='$TrackID'";
        }
        $stmtQty = $this->conn->prepare($sqlQty);
        $stmtQty->execute();
        $rowTotalQty = $stmtQty->fetch(PDO::FETCH_ASSOC);
        $TotalQty = $rowTotalQty['TotalQty'];
        $BalanceQty = $TotalQty - $FreezingQty;

        return $BalanceQty;
    }
    public function getEachBalanceQty($TypeofMaterial, $TubNumber, $TrackID, $ProductionIDList, $PlantID)  // for getting Balance Qty 24/09/2024
    {

        $sqlFreezingQty = "SELECT IFNULL(SUM(ProductionWeight), 0) AS ProductionWeight, IFNULL(SUM(RejectionWeight), 0) AS RejectionWeight FROM ProdFreezingData 
        WHERE TubNo = '$TubNumber' AND TypeofMaterial = '$TypeofMaterial' AND PlantID='$PlantID' AND TrackID='$TrackID' AND ProductionIDList REGEXP '$ProductionIDList'";

        $stmtFreezingQty = $this->conn->prepare($sqlFreezingQty);
        $stmtFreezingQty->execute();
        $rowFreezingQty = $stmtFreezingQty->fetch(PDO::FETCH_ASSOC);
        $FreezingQty = ($rowFreezingQty['ProductionWeight'] + $rowFreezingQty['RejectionWeight']);
        if ($TypeofMaterial == "SKM") {
            $sqlQty = "SELECT SUM(SoakInQty) AS TotalQty FROM ProdSoakingDetails
            WHERE TubNumber = '$TubNumber' and TrackID='$TrackID' AND PlantID='$PlantID'  AND CreatedFrom=1 ";
        } else if ($TypeofMaterial == "VM") {
            $sqlQty = "SELECT SUM(WtAftrVA) AS TotalQty FROM PpcValueAddition
            WHERE VLID = '$TubNumber' and TrackID='$TrackID' AND SoakingPlantID='$PlantID'  AND CreatedFrom=1";
        } else if ($TypeofMaterial == "GM") {
            $sqlQty = "SELECT SUM(HLWeight) AS TotalQty FROM PpcHLGradingBreakup
            WHERE DataID = '$TubNumber'  AND CreatedFrom=1";
        } else if ($TypeofMaterial == "SM") {
            $sqlOutStage = "SELECT RRD.OutStage FROM RmReceivingDetails AS RRD LEFT JOIN RmReceivingBreakup AS RRB ON RRD.ReceivingID=RRB.ReceivingID
                WHERE RRB.CsStockOutID = '$TubNumber' and RRB.ReceivingID='$TrackID' LIMIT 1";
            $stmtOutStage = $this->conn->prepare($sqlOutStage);
            $stmtOutStage->execute();
            $rowOutStage = $stmtOutStage->fetch(PDO::FETCH_ASSOC);
            $OutStage = $rowOutStage['OutStage'];
            if ($OutStage == 3) {
                $sqlQty = "SELECT SUM(ReceivingQty) AS TotalQty FROM RmReceivingBreakup
                WHERE CsStockOutID = '$TubNumber' and ReceivingID='$TrackID'";
            } else {
                $sqlQty = "SELECT SUM(SoakInQty) AS TotalQty FROM ProdSoakingDetails 
                WHERE TrackID='$TrackID'  AND CreatedFrom=1 ";
            }
        } else if ($TypeofMaterial == "REJ") { //uma
            $sqlQty = "SELECT SUM(RejectionWeight) AS TotalQty FROM ProdFreezingData
            WHERE PlantID='$PlantID' AND ProductionID IN ($ProductionIDList)";
        } else if ($TypeofMaterial == "HON") { //uma
            $sqlQty = "SELECT SUM(ReceivingQty) AS TotalQty FROM RmReceivingBreakup
            WHERE ReceivalBreakupID ='$TubNumber'";
        }

        $stmtQty = $this->conn->prepare($sqlQty);
        $stmtQty->execute();
        $rowTotalQty = $stmtQty->fetch(PDO::FETCH_ASSOC);
        $TotalQty = $rowTotalQty['TotalQty']; // SoakIn Qty
        $BalanceQty = $TotalQty - $FreezingQty;
        return $BalanceQty;
    }

    public function getCsStockAvalWeightinKgs($PackingStyleID, $AvalMcs, $LooseBags)
    {
        $sqlKgsQuery = "SELECT TotalSlabs,SlabWeight,MFactor,Uom,LbsFactor FROM `AdminPackingStyles` WHERE PCKID='$PackingStyleID'";
        $stmtKgsQuery = $this->conn->prepare($sqlKgsQuery);
        $stmtKgsQuery->execute();
        $RowStockData = $stmtKgsQuery->fetch(PDO::FETCH_ASSOC);
        // $McsKgs = $AvalMcs * $RowStockData['TotalSlabs'] * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        // $LooseKgs = $LooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        // $ActualKgs = $McsKgs + $LooseKgs;
        if ($RowStockData['Uom'] == 'KGS' || $RowStockData['Uom'] == 'GRAMS') {
            $ActualKgs = ($AvalMcs * $RowStockData['TotalSlabs'] + $LooseBags) * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        } else {
            $ActualKgs = ($AvalMcs * $RowStockData['TotalSlabs'] + $LooseBags) * $RowStockData['SlabWeight'] * $RowStockData['LbsFactor'] * 0.454;
        }
        return $ActualKgs;
    }
    function getAutogeneratedTrackCode($RequestBody, $lastInsertId)
    {

        $PlantID = $RequestBody['PlantID'];
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $ReStockinStage = 3;

        $sqlgetRejectionWeight = "SELECT sum(RejectionWeight) as RejectionWeight FROM `ProdFreezingData`WHERE  ArrivalDate='$ArrivalDate' 
         and PlantID='$PlantID'";

        $stmtgetRejectionWeight = $this->conn->prepare($sqlgetRejectionWeight);
        $stmtgetRejectionWeight->execute();
        $dataRowgetRejectionWeight = $stmtgetRejectionWeight->fetch(PDO::FETCH_ASSOC);
        $RejectionWeight = $dataRowgetRejectionWeight['RejectionWeight'];

        if ($RejectionWeight == 0) {
            $this->deleteRejectionTrackIds($ArrivalDate, $RequestBody);
        } else {
            $sqlTrackId = "SELECT max(TrackNumber) as TrackNumber,ReceivingID,
        (select count(*)  FROM RmReceivingDetails WHERE ArrivalDate='$ArrivalDate' and ReceivingPlantID='$PlantID' and IsStoreMaterial=0 and OutStage=3)as TrackCount FROM RmReceivingDetails WHERE month(ArrivalDate)=month('$ArrivalDate') 
        and year(ArrivalDate)=year('$ArrivalDate') and ReceivingPlantID='$PlantID' and IsStoreMaterial=0  and OutStage=3";

            $stmtTrackId = $this->conn->prepare($sqlTrackId);
            $stmtTrackId->execute();
            $dataRowTrackId = $stmtTrackId->fetch(PDO::FETCH_ASSOC);
            $TrackNumber = $dataRowTrackId['TrackNumber'] + 1;
            $TrackCount = $dataRowTrackId['TrackCount'];

            $sqlSelect = "SELECT ProdCode as PlantCode FROM RmProcessingStations  WHERE ProcessingStationID='$PlantID'";
            $stmtSelect = $this->conn->prepare($sqlSelect);
            $stmtSelect->execute();
            $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            $PlantCode = $dataRow['PlantCode'];

            $AutoTrackCode = str_replace("O", "0", $TrackNumber);
            $AutoTrackCode = ltrim($AutoTrackCode, "0");
            $AutoTrackCode = preg_replace("/[^0-9]/", "", $AutoTrackCode);
            $AutoTrackCode = sprintf('%04d', $AutoTrackCode);


            $AutoTrackCode = $PlantCode . '-REJ- ' . sprintf('%03d', $TrackNumber);
            if ($TrackCount == 0 && $RejectionWeight > 0) {
                $sqlInsertQuery = "INSERT INTO RmReceivingDetails(ArrivalDate,ReceivingPlantID,TrackNumber,TrackCode,OutStage,IsStoreMaterial) 
			VALUES ('$ArrivalDate','$PlantID','$TrackNumber','$AutoTrackCode','$ReStockinStage','0')";
                $stmtInsertQuery = $this->conn->prepare($sqlInsertQuery);
                $stmtInsertQuery->execute();
                $ReceivingID = $this->conn->lastInsertId();


                if ($stmtInsertQuery) {
                    $createdReceiving = $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $ReceivingID);
                    $this->auditLogger->logInsert('RmReceivingDetails', $ReceivingID, $createdReceiving, $RequestBody, [
                        'ReferenceNo' => $ReceivingID,
                        'Remarks' => 'Production rejection track created',
                    ]);

                    $sqlInsertBreakup = "INSERT INTO RmReceivingBreakup(ReceivingID,ReceivingQty) 
			VALUES ('$ReceivingID','$RejectionWeight')";
                    $stmtInsertBreakup = $this->conn->prepare($sqlInsertBreakup);
                    $stmtInsertBreakup->execute();
                    if ($stmtInsertBreakup) {
                        $createdBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivingID = ?", [$ReceivingID]);
                        $this->auditLogger->logInsert('RmReceivingBreakup', $ReceivingID, $createdBreakup, $RequestBody, [
                            'ReferenceNo' => $ReceivingID,
                            'Remarks' => 'Production rejection breakup created',
                        ]);
                    }
                }
            } else {
                $ReceivingID = $dataRowTrackId['ReceivingID'];
                $existingBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivingID = ?", [$ReceivingID]);
                $sqlUpdateBreakup = "update RmReceivingBreakup set ReceivingQty='$RejectionWeight' where ReceivingID='$ReceivingID'";
                $stmtUpdateBreakup = $this->conn->prepare($sqlUpdateBreakup);
                $stmtUpdateBreakup->execute();
                if ($stmtUpdateBreakup) {
                    $updatedBreakup = $this->auditLogger->fetchOne("SELECT * FROM RmReceivingBreakup WHERE ReceivingID = ?", [$ReceivingID]);
                    $this->auditLogger->logUpdate('RmReceivingBreakup', $ReceivingID, $existingBreakup, $updatedBreakup, $RequestBody, [
                        'ReferenceNo' => $ReceivingID,
                        'Remarks' => 'Production rejection breakup updated',
                    ]);
                }
            }
        }

        return "Success";
    }
    function getSlabData($RequestBody)
    {
        $PoNumber = $RequestBody['PoNumber'];
        $VarietyId = $RequestBody['VarietyId'];
        $Variety = $RequestBody['Variety'];
        $TrackCode = $RequestBody['TrackCode'];
        $Mcs = 0;
        $Slabs = 0;
        $TotalMcs = 0;
        $TotalMcsStock = 0; //from repacking
        $mcsArr = [];
        if (!empty($PoNumber)) {
            $sqlSlabQuery = "SELECT PGD.Mcs,CPS.`PackingStyle`,CPS.`TotalSlabs` ,CPS.`SlabWeight`,CPS.MFactor,PGD.VarietyID
                                FROM ScrmPoGradeData PGD
                            LEFT JOIN AdminPackingStyles AS CPS ON PGD.PackingStyleID=CPS.PckID            
                            WHERE PGD.InvId='$PoNumber' AND VarietyID=$VarietyId";
            $stmtSlab = $this->conn->prepare($sqlSlabQuery);
            $stmtSlab->execute();
            $RowSlab = $stmtSlab->fetch(PDO::FETCH_ASSOC);
            $Mcs = !empty($RowSlab['Mcs']) ? $RowSlab['Mcs'] : 0;
            $Slabs = !empty($RowSlab['TotalSlabs']) ? $RowSlab['TotalSlabs'] : 0;//20
            $VarietyID = !empty($RowSlab['VarietyID']) ? $RowSlab['VarietyID'] : '';//
            $SlabWeight = !empty($RowSlab['SlabWeight']) ? $RowSlab['SlabWeight'] : '';//
            $MFactor = !empty($RowSlab['MFactor']) ? $RowSlab['MFactor'] : '';//
            $TotalKgs = 0;
           // $TotalKgs = $Mcs * $RowSlab['TotalSlabs'] * $RowSlab['SlabWeight'] * $RowSlab['MFactor'];

            if (!empty($VarietyID)) {
                $where = '';
                if (!empty($RequestBody['prodId'])) {//edit case
                    $where = ' AND ProductionID!=' . $RequestBody['prodId'];
                }
                $sqlTotalSlabQuery = "SELECT SUM(ProductionSlabs) ProductionSlabs FROM ProdFreezingData WHERE `PoID`='$PoNumber' AND VarietyID='$VarietyID'  AND `PackingTypeID`='MASTER' 
                AND ProductionTypeID NOT IN ('Update_Prod')
                $where";
                $stmtTotalSlab = $this->conn->prepare($sqlTotalSlabQuery);
                $stmtTotalSlab->execute();
                $RowTotalSlab = $stmtTotalSlab->fetch(PDO::FETCH_ASSOC);
                $TotalSlab = !empty($RowTotalSlab['ProductionSlabs']) ? $RowTotalSlab['ProductionSlabs'] : 0;//5683
                //if(!empty($PoGradeId)){
                    $csStockSql = "SELECT SUM(`ActualMcs`) AS TotalMcs,SUM(ActualLooseBags) AS TotalBags FROM `CsStockData` WHERE `VarietyID`=$VarietyId AND `PoID`=$PoNumber 
                    AND PackingTypeID = 'MASTER'
                      AND (`ProductionTypeID`='Repacking' OR `ProductionTypeID`='Repacking Out')
                      AND ProductionTypeID NOT IN ('Update_Prod')";
                    $stmtCsStock = $this->conn->prepare($csStockSql);
                    $stmtCsStock->execute();
                    $TotalRes = $stmtCsStock->fetch(PDO::FETCH_ASSOC);
                    $TotalMcsStock = (int)$TotalRes['TotalMcs'];
                    $TotalBags = (int)$TotalRes['TotalBags'];

                    $McsKgs = $TotalMcsStock * $RowSlab['TotalSlabs'] * $RowSlab['SlabWeight'] * $RowSlab['MFactor'];//13.62
                    $LooseKgs = $TotalBags * $RowSlab['SlabWeight'] * $RowSlab['MFactor'];//6.81
                    $TotalKgs = (float)$McsKgs + (float)$LooseKgs;

                    if(!empty($TotalMcsStock) && !empty($Slabs)){
                        $TotalSlab += ((int)$TotalMcsStock * (int)$Slabs) + (int)$TotalBags;//714*6 = 4284, total slab= 9,967
                    }else{
                        $TotalSlab += (int)$TotalBags;//2
                    }

                    $SqlStockOut = "SELECT
                                            SUM(
                                                (CSOD.OutMcs * APS.TotalSlabs) + CSOD.OutLooseBags
                                            ) AS OutStoreSlabs,

                                            SUM(
                                                (CSOD.OutMcs * APS.TotalSlabs + CSOD.OutLooseBags)
                                                * APS.SlabWeight
                                                * APS.MFactor
                                                ) AS OutKgs
                                FROM CsStockOutDetails CSOD
                                LEFT JOIN  `CsStockData` CSD ON CSD.StockID = CSOD.StockID
                                LEFT JOIN AdminPackingStyles AS APS ON CSD.`PackingStyleID`=APS.PckID 
                                WHERE CSD.`VarietyID`=$VarietyId AND CSD.`PoID`=$PoNumber
                                AND CSD.PackingTypeID = 'MASTER' AND ProductionTypeID NOT IN ('Update_Prod')";
                    $stmtStockOut = $this->conn->prepare($SqlStockOut);
                    $stmtStockOut->execute();
                    $OutResults = $stmtStockOut->fetch(PDO::FETCH_ASSOC);

                    $TotalKgs -= (float)!empty($OutResults['OutKgs']) ? $OutResults['OutKgs'] : 0;
                    $TotalSlab -= !empty($OutResults['OutStoreSlabs']) ? $OutResults['OutStoreSlabs'] : 0;


                //}
            }
            $RemainSlabs = ($Mcs * $Slabs) - $TotalSlab;//7200- 9967
            $TotalMcs = ceil($TotalSlab / $Slabs);//4.5//1.75=2
            $remainMcs = $Mcs - $TotalMcs;//1095
            $mcsArr['Mcs'] = (int) $Mcs;
            $mcsArr['TotalSlab'] = (int) $TotalSlab;
            $mcsArr['Slabs'] = (int) $Slabs;
            $mcsArr['RemainMcs'] = (float) $remainMcs;
            $mcsArr['SlabWeight'] = (float) $SlabWeight;
            $mcsArr['MFactor'] = (float) $MFactor;
            //$mcsArr['RemainSlabs'] = $remainMcs*$Slabs;
            $mcsArr['RemainSlabs'] = $RemainSlabs;
            $mcsArr['BalQty'] =  (float) $TotalKgs;
           
            return $mcsArr;
        }
        return false;
    }
    function getSlabDataNew($RequestBody)
    {
        $PoNumber = $RequestBody['PoNumber'];
        $VarietyId = $RequestBody['VarietyId'];
        $Variety = $RequestBody['Variety'];
        $TrackCode = $RequestBody['TrackCode'];
        $PackingType = $RequestBody['PackingType'];
        $txtSlab = (int) $RequestBody['txtSlab'];//entered value in form
        $drpPackingStyle = (int) $RequestBody['drpPackingStyle'];//selected value in form
        $Mcs = 0;
        $POSlabs = 0;
        $TotalMcs = 0;
        $TotalMcsStock = 0; //from repacking
        $ProductionSlabQty = $CurrentProductionSlabQty = $TotalSlab = $CurrentTotalSlab = 0;
        $mcsArr = [];
        if (!empty($PoNumber)) {
            $sqlSlabQuery = "SELECT PGD.Mcs,CPS.`PackingStyle`,CPS.`TotalSlabs` ,CPS.`SlabWeight`,CPS.MFactor,PGD.VarietyID,PGD.PackingStyleID
                                FROM ScrmPoGradeData PGD
                            LEFT JOIN AdminPackingStyles AS CPS ON PGD.PackingStyleID=CPS.PckID            
                            WHERE PGD.InvId='$PoNumber' AND VarietyID=$VarietyId";
            $stmtSlab = $this->conn->prepare($sqlSlabQuery);
            $stmtSlab->execute();
            $RowSlab = $stmtSlab->fetch(PDO::FETCH_ASSOC);
            $Mcs = !empty($RowSlab['Mcs']) ? $RowSlab['Mcs'] : 0;
            $PackingStyleTotalSlabs = $POSlabs = !empty($RowSlab['TotalSlabs']) ? $RowSlab['TotalSlabs'] : 0;//20
            $VarietyID = !empty($RowSlab['VarietyID']) ? $RowSlab['VarietyID'] : '';//
            $PackingStyleSlabWeight = $POSlabWeight = !empty($RowSlab['SlabWeight']) ? $RowSlab['SlabWeight'] : 0;//
            $PackingStyleMFactor = $POMFactor = !empty($RowSlab['MFactor']) ? $RowSlab['MFactor'] : 0;//
            $PackingStyleID = !empty($RowSlab['PackingStyleID']) ? $RowSlab['PackingStyleID'] : '';//
            if (
                $PackingType === 'DUMMY' &&
                !empty($txtSlab) &&
                !empty($drpPackingStyle) &&
                $drpPackingStyle !== $PackingStyleID
            ) {
                $PackingStyleSql = "SELECT TotalSlabs,SlabWeight,MFactor FROM AdminPackingStyles WHERE PckID=$drpPackingStyle";
                $stmtPackingStyle = $this->conn->prepare($PackingStyleSql);
                $stmtPackingStyle->execute();
                $RowPackingStyle = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC);
                $PackingStyleTotalSlabs = $RowPackingStyle['TotalSlabs'];
                $PackingStyleSlabWeight = $RowPackingStyle['SlabWeight'];
                $PackingStyleMFactor = $RowPackingStyle['MFactor'];
            }
            $POQty = $Mcs * $POSlabs * $POSlabWeight * $POMFactor;//16017.12

            $StoreQty = 0;
            // $TotalKgs = $Mcs * $RowSlab['TotalSlabs'] * $RowSlab['SlabWeight'] * $RowSlab['MFactor'];

            if (!empty($VarietyID)) {
                $where = '';
                if (!empty($RequestBody['prodId'])) {//edit case
                    $where = ' AND ProductionID!=' . $RequestBody['prodId'];
                }
                //$sqlTotalSlabQuery = "SELECT SUM(ProductionSlabs) ProductionSlabs FROM ProdFreezingData WHERE `PoID`='$PoNumber' AND VarietyID='$VarietyID'  AND `PackingTypeID`='MASTER' $where";
                $sqlTotalSlabQuery = "SELECT 
                                        ROUND(
                                            SUM(ProductionSlabs * SlabWeight * MFactor),
                                            2
                                        ) AS ProductionSlabQty 
                                        FROM ProdFreezingData PFD
                                        LEFT JOIN AdminPackingStyles AS CPS ON PFD.`PackingStyleID`=CPS.PckID 
                                        WHERE `PoID`='$PoNumber'
                                        -- AND PackingTypeID = 'MASTER' 
                                        AND ProductionTypeID NOT IN ('Update_Prod')
                                         $where";
                $stmtTotalSlab = $this->conn->prepare($sqlTotalSlabQuery);
                $stmtTotalSlab->execute();
                $RowTotalSlab = $stmtTotalSlab->fetch(PDO::FETCH_ASSOC);
                //$TotalSlab = !empty($RowTotalSlab['ProductionSlabs']) ? $RowTotalSlab['ProductionSlabs'] : 0;//5683
                $CurrentProductionSlabQty = $ProductionSlabQty = (float) (!empty($RowTotalSlab['ProductionSlabQty']) ? $RowTotalSlab['ProductionSlabQty'] : 0);
                //$TotalSlab = $ProductionSlabQty / ($POSlabWeight * $POMFactor);//28,118.666

                if (!empty($txtSlab)) {
                    // $TotalSlab += $txtSlab;//28,118.666+25=28,143.666
                    $ProductionSlabQty += (float) ($txtSlab * $PackingStyleSlabWeight * $PackingStyleMFactor);//+22.7=9,597.106
                }
                $TotalSlab = $ProductionSlabQty / ($POSlabWeight * $POMFactor);//include entered value 85.18
                $CurrentTotalSlab = $CurrentProductionSlabQty / ($POSlabWeight * $POMFactor);//not include etered value 85.18
                //$ProductionSlabQty = $TotalSlab * $POSlabWeight * $POMFactor;//17.025 
                //if(!empty($PoGradeId)){
                //$csStockSql = "SELECT SUM(`ActualMcs`) AS TotalMcs,SUM(ActualLooseBags) AS TotalBags FROM `CsStockData` WHERE PackingTypeID='MASTER' AND  `VarietyID`=$VarietyId AND `PoID`=$PoNumber  AND (`ProductionTypeID`='Repacking' OR `ProductionTypeID`='Repacking Out')";
                
                $csStockSql = "SELECT 
                                SUM(
                                    ((CSD.ActualMcs * APS.TotalSlabs) + CSD.ActualLooseBags)
                                    * APS.SlabWeight
                                    * APS.MFactor
                                ) AS StoreQty,

                                SUM(
                                    (CSD.ActualMcs * APS.TotalSlabs) + CSD.ActualLooseBags
                                ) AS StoreSlabs
                                FROM `CsStockData` CSD
                                LEFT JOIN AdminPackingStyles AS APS ON CSD.`PackingStyleID`=APS.PckID 
                                 WHERE  `VarietyID`=$VarietyId
                                --  AND PackingTypeID = 'MASTER' 
                                  AND `PoID`=$PoNumber  
                                  AND CSD.ProductionTypeID NOT IN ('Production','Update_Prod') ";                
                $stmtCsStock = $this->conn->prepare($csStockSql);
                $stmtCsStock->execute();
                $TotalRes = $stmtCsStock->fetch(PDO::FETCH_ASSOC);
                /* $TotalMcsStock = (int) $TotalRes['TotalMcs'];
                $TotalBags = (int) $TotalRes['TotalBags'];

                $McsKgs = $TotalMcsStock * $RowSlab['TotalSlabs'] * $RowSlab['SlabWeight'] * $RowSlab['MFactor'];//13.62
                $LooseKgs = $TotalBags * $RowSlab['SlabWeight'] * $RowSlab['MFactor'];//6.81
                $StoreQty = (float) $McsKgs + (float) $LooseKgs; */
                $StoreQty = (float) $TotalRes['StoreQty'];
                $SlabCs = $TotalRes['StoreSlabs'];

                 $SqlStockOut = "SELECT SUM(`ActualOutKgs`) `ActualOutKgs` ,
                                    SUM(
                                        (CSOD.OutMcs * APS.TotalSlabs) + CSOD.OutLooseBags
                                    ) AS OutStoreSlabs
                                FROM CsStockOutDetails CSOD
                                LEFT JOIN  `CsStockData` CSD ON CSD.StockID = CSOD.StockID
                                LEFT JOIN AdminPackingStyles AS APS ON CSD.`PackingStyleID`=APS.PckID 
                                WHERE CSD.`VarietyID`=$VarietyId AND CSD.`PoID`=$PoNumber";
                $stmtStockOut = $this->conn->prepare($SqlStockOut);
                $stmtStockOut->execute();
                $OutResults = $stmtStockOut->fetch(PDO::FETCH_ASSOC);
                $StockOutQty = (float)$stmtStockOut->fetch(PDO::FETCH_ASSOC)['ActualOutKgs'];
                if(!empty($OutResults)){
                    $StockOutQty = (float)$OutResults['ActualOutKgs'];
                    $OutStoreSlabs = $OutResults['OutStoreSlabs'];
                    $StoreQty -= $StockOutQty;
                    if($StoreQty < 0){
                        $StoreQty = 0;
                    }
                    $SlabCs -= $OutStoreSlabs;
                    if($SlabCs < 0){
                        $SlabCs = 0;
                    }
                }

                $ProductionSlabQty += (float) $StoreQty;
                $CurrentProductionSlabQty += (float) $StoreQty;

                

                /* if (!empty($TotalMcsStock) && !empty($POSlabs)) {
                    $SlabCs = ((int) $TotalMcsStock * (int) $POSlabs) + (int) $TotalBags;//714*6 = 4284, total slab= 9,967
                } else {
                    $SlabCs = (int) $TotalBags;//2
                } */
                $TotalSlab += $SlabCs;//from cold store
                $CurrentTotalSlab += $SlabCs;

                //}
            }
            $RemainSlabs = ($Mcs * $POSlabs) - $TotalSlab;//47,040- 28,108
            $RemainSlabsOld = ($Mcs * $POSlabs) - $CurrentTotalSlab;//not consider entered value
            $TotalMcs = ceil($TotalSlab / $POSlabs);//4.5//1.75=2
            $remainMcs = $Mcs - $TotalMcs;//1095
            $BalQty = (float) $POQty - (float) $CurrentProductionSlabQty;
            $mcsArr['Mcs'] = (int) $Mcs;
            //$mcsArr['TotalSlab'] = (int) $TotalSlab;
            $mcsArr['Slabs'] = (int) $POSlabs;
            $mcsArr['RemainMcs'] = (float) $remainMcs;
            $mcsArr['SlabWeight'] = (float) $POSlabWeight;
            $mcsArr['MFactor'] = (float) $POMFactor;
            //$mcsArr['RemainSlabs'] = $remainMcs*$POSlabs;
            $mcsArr['RemainSlabsOld'] = ceil($RemainSlabsOld);
            $mcsArr['RemainSlabs'] = ceil($RemainSlabs);
            $mcsArr['StoreQty'] = (float) $StoreQty;
            $mcsArr['POQty'] = (float) $POQty;
            $mcsArr['ProductionSlabQty'] = (float) $CurrentProductionSlabQty;
            $mcsArr['BalQty'] = round((float) $BalQty, 2);

            return $mcsArr;
        }
        return false;
    }
    function getLotNumberByStockId($stockIdS)
    {
        if (!empty($stockIdS)) {
            $sql = "SELECT group_concat(DISTINCT RLD.LotNumber ) LotNumber
                    FROM CsStockData CSD
                    LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CSD.ProductionID
                    LEFT JOIN (
                        SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
                        FROM ProdFreezingData
                        GROUP BY TrackID, TubNo, LineNumber
                        ) PFDx
                            ON CDPD.TrackID = PFDx.TrackID 
                            AND CDPD.TubNo = PFDx.TubNo 
                            AND CDPD.LineNumber = PFDx.LineNumber
                    LEFT JOIN ProdFreezingData PFD ON PFD.ProductionID = PFDx.MaxDataID
                    LEFT JOIN PpcHLGradingBreakup AS PGB ON PFD.GradingBreakupID=PGB.DataID   AND PGB.CreatedFrom=1
                    LEFT JOIN RmLotDetatils AS RLD ON PGB.LotID=RLD.RmLotID
                    WHERE `StockID` IN($stockIdS)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rowData = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rowData['LotNumber'];
        }
    }


    function CheckProductionDataInserted($RequestBody)
    {
        $TrackID = $RequestBody['TrackID'];
        $TubNo = $RequestBody['TubNumberValue'];

        $OldProductionDate = isset($RequestBody['OldProductionDate']) ? $RequestBody['OldProductionDate'] : $RequestBody['ProductionDate'];
        $OldGrade = isset($RequestBody['OldGrade']) ? $RequestBody['OldGrade'] : $RequestBody['Grade'];
        $OldPoGradeId = isset($RequestBody['OldPoGradeId']) ? $RequestBody['OldPoGradeId'] : $RequestBody['PoGradeId'];
        $OldVarietyID = isset($RequestBody['OldVarietyID']) ? $RequestBody['OldVarietyID'] : $RequestBody['Variety'];
        $OldProductCategoryID = isset($RequestBody['OldProductCategoryID']) ? $RequestBody['OldProductCategoryID'] : $RequestBody['ProductCategory'];
        $OldPackingStyleID = isset($RequestBody['OldPackingStyleID']) ? $RequestBody['OldPackingStyleID'] : $RequestBody['PackingStyle'];
        $OldPackingTypeID = isset($RequestBody['OldPackingTypeID']) ? $RequestBody['OldPackingTypeID'] : $RequestBody['PackingType'];
        $OldAbStatus = isset($RequestBody['OldAbStatus']) ? $RequestBody['OldAbStatus'] : $RequestBody['AbStatus'];
        $OldLineNumber = isset($RequestBody['OldLineNumber']) ? $RequestBody['OldLineNumber'] : $RequestBody['LineNumber'];
        $OldFreezingType = isset($RequestBody['OldFreezingType']) ? $RequestBody['OldFreezingType'] : $RequestBody['FreezingType'];
        $OldPoID = isset($RequestBody['OldPoID']) ? $RequestBody['OldPoID'] : $RequestBody['PoID'];
        $OldMixedBatch = isset($RequestBody['OldMixedBatch']) ? $RequestBody['OldMixedBatch'] : $RequestBody['MixedBatchCode'];

        
        $sqlProductionDataCount = "SELECT *  FROM `CsDailyProductionData`
        WHERE TrackID='$TrackID'and ProductionDate='$OldProductionDate' 
        and PackingTypeID='$OldPackingTypeID' and ProductCategoryID='$OldProductCategoryID' and PackingStyleID='$OldPackingStyleID'
        and AbStatus='$OldAbStatus'  and LineNumber='$OldLineNumber' and PoID='$OldPoID' and PoGradeId='$OldPoGradeId' and Grade='$OldGrade' 
        and FreezingType='$OldFreezingType'and VarietyID='$OldVarietyID' and TubNo='$TubNo' AND MixedBatch='$OldMixedBatch'";
        $stmtProductionDataCount = $this->conn->prepare($sqlProductionDataCount);
        $stmtProductionDataCount->execute();
        $dataRowProductionDataCount = $stmtProductionDataCount->fetch(PDO::FETCH_ASSOC);

        $OldProductionID = $dataRowProductionDataCount['ProductionID'];


        if ($OldProductionID != '' && $OldProductionID != '0') {
            $sqlStock = "SELECT COUNT(*) AS Total FROM `CsStockData` WHERE ProductionID =$OldProductionID";
            $stmtStock = $this->conn->prepare($sqlStock);
            $stmtStock->execute();
            $dataStock = $stmtStock->fetch(PDO::FETCH_ASSOC);

            $Total = $dataStock['Total'];
            if ($Total > 0) {
                return true;
            }

        }

        return false;
    }

}
