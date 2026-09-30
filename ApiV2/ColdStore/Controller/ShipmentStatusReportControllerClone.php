<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ShipmentStatusReportController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getShipmentStatusDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ProductionPlant = $RequestBody['ProductionPlant'];

        $Query = '';
        if ($ProductionPlant != "") {
            $Query .= " AND CPID.PlantID = '$ProductionPlant'";
        }

        $DataFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.PoID, SPD.PoNo, CPID.VarietyID, CPID.Grade, CPID.FreezingType, CPID.PlantID,
        CPID.SockingType, CPID.PackingTypeID, CPID.PackingStyleID,SPVL.ShortName,APS.PackingStyle,
        Mcs, (MCS*TotalSlabs*SlabWeight*MFactor) as OrderKgs, (Mcs*TotalSlabs) AS TotalSlabs, SPVL.ProductDescription, 
        CPID.ProductionTypeID, CPID.PackingTypeID, CustomsInvNo, ContainerNo, VehicelNumber,CDPD.ProdBatchCode";
        $sqlQuery = "SELECT $sqlFields FROM CsStockData AS CPID
        LEFT JOIN ScrmPoData AS SPD ON CPID.PoID = SPD.PoID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON CPID.VarietyID = SPVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
        LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId=SPD.PoID
       left join CsDailyProductionData AS CDPD ON CPID.ProductionID=CDPD.ProductionID
        WHERE CPID.ProductionDate $DataFilters $Query AND CPID.PoID != '' GROUP BY CPID.VarietyID";
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);


        $ArrayShipmentData = [];
        $TotalOrderedMcs = 0;
        $TotalOrderedSlabs = 0;
        $TotalOrderedKGs = 0;
        $GrandProductionMcs = 0;
        $GrandProductionSlabs = 0;
        $GrandProductionKGs = 0;
        $ShimentTotals = [];
        $i = 0;
        $customsInvNoCounts = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $CustomsInvNo = $dataRow['CustomsInvNo'];
            if (!empty($CustomsInvNo)) {
                if (!isset($customsInvNoCounts[$CustomsInvNo])) {
                    $customsInvNoCounts[$CustomsInvNo] = 1;
                } else {
                    $customsInvNoCounts[$CustomsInvNo]++;
                }
                $FormattedCustomsInvNo = $CustomsInvNo . '-' . $customsInvNoCounts[$CustomsInvNo];
            } else {
                $FormattedCustomsInvNo = '';
            }


            $VarietyID = $dataRow['VarietyID'];
            $PoID = $dataRow['PoID'];
            $BreakUpData = $this->getShipmentBreakUpData($VarietyID, $PoID, $Query, $DataFilters);
            $ArrayShipmentList['SNO'] = ++$i;
            $ArrayShipmentList['PoID'] = $dataRow['PoID'];
            $ArrayShipmentList['PoNo'] = $dataRow['PoNo'];
            $ArrayShipmentList['CustomsInvNo'] = $dataRow['CustomsInvNo'];
            $ArrayShipmentList['CustomsInvNoTwo'] = $FormattedCustomsInvNo;
            // $ArrayShipmentList['ProductionTypeID'] = $dataRow['ProductionTypeID'];
            $ArrayShipmentList['VarietyID'] = $dataRow['VarietyID'];
            $ArrayShipmentList['ShortName'] = $dataRow['ShortName'];
            $ArrayShipmentList['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayShipmentList['Grade'] = $dataRow['Grade'];
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['PlantID'] = $dataRow['PlantID'];
            $ArrayShipmentList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayShipmentList['SockingType'] = $dataRow['SockingType'];
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayShipmentList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayShipmentList['OrderedMcs'] = $dataRow['Mcs'];
            $ArrayShipmentList['OrderKgs'] = round($dataRow['OrderKgs'], 3);
            $ArrayShipmentList['OrderSlabs'] = $dataRow['TotalSlabs'];
            $ArrayShipmentList['ContainerNo'] = $dataRow['ContainerNo'];
            $ArrayShipmentList['VehicelNumber'] = $dataRow['VehicelNumber'];
            $ArrayShipmentList['rowCount'] = $BreakUpData['rowCount'];
            $ArrayShipmentList['ArrayBreakUpData'] = $BreakUpData['ArrayBreakUpData'];
              $ArrayShipmentList['ProdBatchCode'] = $BreakUpData['ProdBatchCode'];

            $TotalOrderedMcs += $dataRow['Mcs'];
            $TotalOrderedSlabs += $dataRow['TotalSlabs'];
            $TotalOrderedKGs += $dataRow['OrderKgs'];
            $GrandProductionMcs += $BreakUpData['TotalProductionMcs'];
            $GrandProductionSlabs += $BreakUpData['TotalProductionSlabs'];
            $GrandProductionKGs += $BreakUpData['TotalProductionKGs'];
            $ArrayShipmentData[] =  $ArrayShipmentList;

            $ShimentTotals = [
                "TotalOrderedMcs" => $TotalOrderedMcs,
                "TotalOrderedSlabs" => $TotalOrderedSlabs,
                "TotalOrderedKGs" => $TotalOrderedKGs,
                "TotalProductionMcs" => $GrandProductionMcs,
                "TotalProductionSlabs" => $GrandProductionSlabs,
                "TotalProductionKGs" => $GrandProductionKGs,
            ];
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['ArrayShipmentData'] = $ArrayShipmentData;
        $data['ShipmentTotals'] = $ShimentTotals;

        return $data;
    }

    public function getShipmentBreakUpData($VarietyID, $PoID, $Query, $DataFilters)
    {
        $sqlBreakUpFields = "ActualMcs, ActualLooseBags, (TotalSlabs*ActualMcs) AS McsSlabs,
        (ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs, 
        RackName, CSBD.BlockID,CPID.ProductionTypeID, BlockName, StoreName, CPID.PlantID, RPS.StationName";
        $sqlBreakUpSelect = "SELECT $sqlBreakUpFields FROM CsStockData AS CPID
        LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID = CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID = CSRD.BlockID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CPID.PackingStyleID
        LEFT JOIN CsStores AS CS ON CS.StoreID = CPID.UnitID 
        LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
        WHERE CPID.VarietyID = '$VarietyID' AND CPID.PoID = '$PoID' AND CPID.ProductionDate $DataFilters $Query";
        $stmtBreakUpSelect = $this->conn->prepare($sqlBreakUpSelect);
        $stmtBreakUpSelect->execute();
        $rowCount = $stmtBreakUpSelect->rowCount();

        $ArrayBreakUpData = [];
        $TotalProductionMcs = 0;
        $TotalProductionSlabs = 0;
        $TotalProductionKGs = 0;
        while ($rowBreakUpData = $stmtBreakUpSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBreakUpList['ProductionMcs'] = $rowBreakUpData['ActualMcs'];
            $ArrayBreakUpList['ProductionLooseBags'] = $rowBreakUpData['ActualLooseBags'];
            $ProductionSlabs = $rowBreakUpData['McsSlabs'] + $rowBreakUpData['ActualLooseBags'];
            $ArrayBreakUpList['ProductionSlabs'] = $ProductionSlabs;
            $ProductionKGs = $rowBreakUpData['Kgs'] + $rowBreakUpData['LKgs'];
            $ArrayBreakUpList['ProductionKGs'] = round($ProductionKGs, 3);
            $ArrayBreakUpList['StationName'] = $rowBreakUpData['StationName'];
            $ArrayBreakUpList['ProductionDate'] = $rowBreakUpData['ProductionDate'] ? date('d-m-Y', strtotime($rowBreakUpData['ProductionDate'])) : 'NA';;
            $ArrayBreakUpList['StoreName'] = $rowBreakUpData['StoreName'];
            $ArrayBreakUpList['ProductionTypeID'] = $rowBreakUpData['ProductionTypeID'];
            $ArrayBreakUpList['BlockID'] = $rowBreakUpData['BlockID'];
            $ArrayBreakUpList['Location'] = $rowBreakUpData['BlockName'] . '-' . $rowBreakUpData['RackName'];

            $TotalProductionMcs += $rowBreakUpData['ActualMcs'];
            $TotalProductionSlabs += $ProductionSlabs;
            $TotalProductionKGs += $ProductionKGs;
            $ArrayBreakUpData[] = $ArrayBreakUpList;
        }
        $Data['rowCount'] = $rowCount;
        $Data['ArrayBreakUpData'] = $ArrayBreakUpData;
        $Data['TotalProductionMcs'] = $TotalProductionMcs;
        $Data['TotalProductionSlabs'] = $TotalProductionSlabs;
        $Data['TotalProductionKGs'] = $TotalProductionKGs;

        return $Data;
    }
}
