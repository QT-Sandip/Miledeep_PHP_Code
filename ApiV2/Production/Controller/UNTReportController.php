<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class UNTReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = 'Success';
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getUNTReportDetails($RequestBody)
    {

        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PlantID = $RequestBody['PlantID'];
        $TypeProduct = $RequestBody['ProductType'];
        $FreezingReportGrouping = $RequestBody['FreezingReportGrouping'];
        $Query = '';


        //  echo print_r($RequestBody);exit;


        if ($PlantID != '') {
            $Query .= " AND  pfd.PlantID='$PlantID' ";
        }

        if ($TypeProduct != '') {
            $Query .= " and SPV.TypeProduct='$TypeProduct'";
        }

        $DateFilter = " AND rm.ArrivalDate between '$FromDate' and '$ToDate'";

        $i = 0;


        $sqlSoakingBreakup = "SELECT  rm.`ArrivalDate`,pfd.Grade,TrackCode,pgb.Treatment,
                                pfd.FreezingType,
                                CASE WHEN  SPV1.TypeProduct IS NOT NULL THEN SPV1.TypeProduct ELSE SPV.TypeProduct END AS TypeProduct,
                                CASE WHEN  SPV1.ShortName IS NOT NULL THEN SPV1.ShortName ELSE SPV.ShortName END AS Variety,
                                CASE WHEN  SPV1.Species IS NOT NULL THEN SPV1.Species ELSE SPV.Species END AS Species
                                ,StationName,
                                -- pgb.HLWeight AS InQty,
                               --  (SELECT SUM(`WtAftrVA`) FROM PpcValueAddition WHERE `GradingBreakupID`=pgb.DataID  ) AS    InQty,
                               pgb.HLCount AS InCnt, 
                              -- CASE WHEN  PVA.SentWeight IS NOT NULL THEN PVA.SentWeight ELSE pgb.HLWeight END AS InQty,
                               CASE WHEN  PVA.WtAftrVA IS NOT NULL THEN PVA.WtAftrVA ELSE pgb.HLWeight END AS InQty,
                               pfd.ProductionWeight AS OutQty,
                                -- pfd.PackingCount AS OutCnt,
                                 pgb.HLCount AS OutCnt,
                               -- COALESCE(PFD.TotalFreezingWeight, 0) AS TotalFreezingWeight,
                                pfd.TubNo  AS TubNo 
                                FROM `PpcHLGradingBreakup` pgb        
                                -- LEFT JOIN ProdFreezingData pfd ON pfd.GradingBreakupID=pgb.DataID 

                                LEFT JOIN PpcValueAddition PVA ON PVA.GradingBreakupID = pgb.DataID
                                LEFT JOIN PpcHLVAVarietyList SPV1 ON SPV1.VarietyID = PVA.VarietyID

                                left JOIN (
                                            SELECT GradingBreakupID,PlantID,RmLotID,Grade,FreezingType,TrackID, TubNo,
                                            SUM(ProductionWeight + RejectionWeight) ProductionWeight,PackingCount
                                            -- SUM(ProductionWeight + RejectionWeight) AS TotalFreezingWeight
                                            FROM ProdFreezingData 
                                             WHERE SockingType = 'UNT'
                                             GROUP BY TrackID, TubNo
                                        ) pfd 
                                            ON pfd.GradingBreakupID = pgb.DataID  AND (pgb.DataID = pfd.TubNo OR PVA.VLID = pfd.TubNo)

                                LEFT JOIN PpcHLVAVarietyList SPV ON SPV.VarietyID=pgb.VarietyID 

                                LEFT JOIN RmReceivingDetails  rm ON pfd.TrackID=rm.ReceivingID
                                LEFT JOIN RmProcessingStations rps ON pfd.PlantID=rps.ProcessingStationID
                                LEFT JOIN RmLotDetatils rld ON pfd.RmLotID=rld.RmLotID
                                WHERE pgb.Treatment='UNT'  $DateFilter  $Query      

                                UNION
                                
                                SELECT  rm.`ArrivalDate`,pfd.Grade,TrackCode,psd.SoakingTreatment Treatment,
                                pfd.FreezingType,SPV.TypeProduct,SPV.ShortName AS Variety,Species,StationName,
                                pgb.`HLCount` AS InCnt,
                                -- pgb.`HLWeight` AS InQty,
                                -- psd.`SoakOutQty` AS OutQty,
                                psd.`SoakInQty` AS InQty,
                                CASE WHEN pfd.TotalFreezingWeight != 0 THEN pfd.TotalFreezingWeight ELSE psd.`SoakOutCount` END  AS OutQty,
                                psd.`SoakOutCount` AS OutCnt,
                                -- COALESCE(PFD.TotalFreezingWeight, 0) AS TotalFreezingWeight,
                                TubNumber AS TubNo
                                 FROM `ProdSoakingDetails` psd        
                                -- LEFT JOIN ProdFreezingData pfd ON pfd.SoakingID=psd.SckId 
                                LEFT JOIN (
                                            SELECT SoakingID,PlantID,RmLotID,Grade,FreezingType,TrackID, TubNo, 
                                            COALESCE(SUM(ProductionWeight + RejectionWeight), 0) AS TotalFreezingWeight 
                                            FROM ProdFreezingData 
                                             WHERE SockingType = 'UNT'
                                             GROUP BY TrackID, TubNo) pfd 
                                            ON pfd.SoakingID=psd.SckId 
                                LEFT JOIN PpcHLVAVarietyList SPV ON SPV.VarietyID=psd.Variety 
                                LEFT JOIN RmReceivingDetails  rm ON pfd.TrackID=rm.ReceivingID
                                LEFT JOIN RmProcessingStations rps ON pfd.PlantID=rps.ProcessingStationID
                                LEFT JOIN RmLotDetatils rld ON pfd.RmLotID=rld.RmLotID
                                LEFT JOIN PpcHLGradingBreakup pgb ON psd.VABreakupID=pgb.`DataID`
                                WHERE psd.SoakingTreatment='UNT' $DateFilter  $Query 
                                ORDER BY `ArrivalDate` ASC";
                               //  echo '<pre>';echo($sqlSoakingBreakup);die;
        if($FreezingReportGrouping){
            $sqlSoakingBreakup = "SELECT *,SUM(InQty) AS InQty,SUM(OutQty) AS OutQty FROM ($sqlSoakingBreakup) a GROUP BY TrackCode,Variety,TypeProduct,Grade,TubNo";
        }
       // echo '<pre>';echo($sqlSoakingBreakup);die;


        // echo print_r($sqlSoakingBreakup);
        // exit;


        $stmtUAT = $this->conn->prepare($sqlSoakingBreakup);
        $stmtUAT->execute();
        $TotalSoakOutQty = 0;
        $TotalInQuantity = 0;
        $TotalOutQuantity = 0;
        $TotalSoakingYield = 0;
        $YieldCount = 0;
        while ($dataRowUAT = $stmtUAT->fetch(PDO::FETCH_ASSOC)) {
            // $TotalSoakOutQty += $dataRowUAT['TotalFreezingWeight'];
            /*  $SoakOutQty = $dataRowUAT['OutQuantity'];
             $SoakInQty = $dataRowUAT['InQuantity']; */
            $AryBreakUp['SNo'] = ++$i;
            $AryBreakUp['ArrivalDate'] = $this->utilController->getFormatedDate($dataRowUAT['ArrivalDate']);
            $AryBreakUp['TrackCode'] = $dataRowUAT['TrackCode'];
            $AryBreakUp['TubNo'] = $dataRowUAT['TubNo'];
            $AryBreakUp['Plant'] = $dataRowUAT['StationName'];
            $AryBreakUp['Variety'] = $dataRowUAT['Variety'];
            $AryBreakUp['Grade'] = $dataRowUAT['Grade'];
            $AryBreakUp['TypeProduct'] = $dataRowUAT['TypeProduct'];
            $AryBreakUp['FreezingType'] = $dataRowUAT['FreezingType'];
            $AryBreakUp['SourceCount'] = $dataRowUAT['InCnt'];
            $AryBreakUp['InQuantity'] = $dataRowUAT['InQty'];
            $AryBreakUp['OutCount'] = $dataRowUAT['OutCnt'];
            $AryBreakUp['OutQuantity'] = $dataRowUAT['OutQty'];
            $AryBreakUp['Treatment'] = $dataRowUAT['Treatment'];
            // $AryBreakUp['OutQuantity'] = $dataRowUAT['TotalFreezingWeight'];
            // $AryBreakUp['Remarks'] = $dataRowUAT['Remarks'];
            $AryBreakUp['Species'] = $dataRowUAT['Species'];
            $SoakingYield = ((float) $dataRowUAT['OutQty'] / (float) $dataRowUAT['InQty']) * 100;
            // $SoakingYield = (($AryBreakUp['OutQuantity']-$AryBreakUp['InQuantity']) / $AryBreakUp['InQuantity']) * 100;
            $AryBreakUp['SoakingYield'] = number_format($SoakingYield, 2);
            //$AryBreakUp['SoakingYield'] = $SoakingYield;
            $ArrayBreakupData[] = $AryBreakUp;

            $TotalInQuantity += $AryBreakUp['InQuantity'];
            $TotalOutQuantity += $AryBreakUp['OutQuantity'];
            $TotalSoakingYield += $AryBreakUp['SoakingYield'];
            $YieldCount++;
        }

        if ($YieldCount > 0) {
            $AverageSoakingYield = $TotalSoakingYield / $YieldCount;
        }

        $ArrayTotals['GrandTotalInQuantity'] = number_format($TotalInQuantity, 2);
        $ArrayTotals['GrandTotalOutQuantity'] = number_format($TotalOutQuantity, 2);
        $ArrayTotals['AverageSoakingYield'] = number_format($AverageSoakingYield, 2);

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['ArrivalData'] = $ArrayBreakupData;
        $Data['Totals'] = $ArrayTotals;


        // echo print_r($Data);
        // exit;
        return $Data;
    }
}
