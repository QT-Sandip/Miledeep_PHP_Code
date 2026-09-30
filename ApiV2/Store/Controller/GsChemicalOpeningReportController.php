<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class GsChemicalOpeningReportController
{
    private $conn;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getChemicalOpeningDetails($RequestBody)
    {
        $PlantID = $RequestBody['PlantID'];
        $ChemicalID = $RequestBody['ChemicalID'];
        $BillDate = $RequestBody['BillDate'];
        $FromDate = $RequestBody['fromDate'];
        $ToDate = $RequestBody['toDate'];
        $Month = $RequestBody['Month'];
        $year = $RequestBody['year'];
        if($Month && $year){
            if($Month <= 3){
                $year += 1;
            }
            $FromDate = "$year-$Month-01";
            $days = date("t", strtotime("$FromDate"));
            $ToDate = "$year-$Month-$days";
        }

                
        $CommonQuery = '';
        $TotalQuery = '';
        $GroupBy = '';
        $InnerQuery = '';
        $monthRange = [];
        if ($PlantID != '') {
            $CommonQuery .= " AND GCRQ.ProcessingPlantID = '$PlantID'";
            $GroupBy .= " ,ProcessingPlantID";
            $InnerQuery .= " AND PlantID = '$PlantID'";
        }

        if ($ChemicalID != '') {
            $CommonQuery .= " AND ChemicalID = '$ChemicalID'";
        }

        if ($BillDate != '') {
            $CommonQuery .= " AND BillDate <= '$BillDate' ";
        } else if ($FromDate != '' && $ToDate != '') {
            
            $fromDateObj = new DateTime($FromDate);
            $toDateObj = new DateTime($ToDate);

            // Ensure toDate doesn't go beyond today
            $today = new DateTime();
            if ($toDateObj > $today) {
                $toDateObj = $today;
            }

            $toDateObj->modify('last day of this month'); // for full last month if within valid range
            if ($toDateObj > $today) {
                $toDateObj = $today;
            }

            $current = clone $fromDateObj;

            $monthRange = []; // make sure this is initialized

            $monthRange[] = ['start' => $FromDate, 'end' => $ToDate]; // To filter for the entire Fiscal Year
            // Uncomment this block if you want to generate month ranges
            // while ($current <= $toDateObj) {
            //     $start = $current->format('Y-m-01'); // 1st of current month
            //     $end = $current->format('Y-m-t');    // last of current month

            //     // Clamp to original range and today
            //     if ($start < $FromDate)
            //         $start = $FromDate;
            //     if ($end > $ToDate)
            //         $end = $ToDate;
            //     if ($end > $today->format('Y-m-d'))
            //         $end = $today->format('Y-m-d');

            //     $monthRange[] = ['start' => $start, 'end' => $end];

            //     $current->modify('first day of next month');
            // }

        }


        if (isset($monthRange) && $monthRange == []) {
            if ($FromDate != '' && $ToDate != '') {
                $TotalQuery = $CommonQuery;
                $Query = $CommonQuery;
                $Query .= " AND BillDate BETWEEN '$FromDate' AND '$ToDate' ";
                $TotalQuery .= " AND BillDate < '$FromDate'";
            }

            $sqlChemicals = "SELECT * FROM GsChemicalMaster WHERE `Status`=1";
            $stmtChemicals = $this->conn->prepare($sqlChemicals);
            $stmtChemicals->execute();

            $i = 0;

            $ArrayChemOrdersData = [];
            while ($dataRowChemical = $stmtChemicals->fetch(PDO::FETCH_ASSOC)) {

                $ChemicalID = $dataRowChemical['ChemID'];
                $ChemicalName = $dataRowChemical['ChemicalName'];

                $sqlFields = "SUM(QuantityReceived) AS TotalQuantityReceived, SUM(ReceivedBags) AS TotalReceivedBags,
             ProcessingPlantID,BillDate, ChemicalID, RPS.StationName";
                $sqlSelect = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
            LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
            WHERE 1=1 $Query AND ChemicalID=$ChemicalID
                GROUP BY ChemicalID ORDER BY ChemicalID";
                // echo $sqlSelect;exit;

                $stmtSelect = $this->conn->prepare($sqlSelect);
                $stmtSelect->execute();
                // $errorInfo = $stmtSelect->errorInfo();
                // var_dump($errorInfo);

                $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);

                $ProcessingPlantID = $dataRow['ProcessingPlantID'];

                $BillQuery = "";
                $TotalBillQuery = "";
                if ($BillDate != '') {
                    $BillQuery .= " AND EntryDate <= '$BillDate' ";
                } else if ($FromDate != '' && $ToDate != '') {
                    $BillQuery .= " AND EntryDate BETWEEN '$FromDate' AND '$ToDate' ";
                    $TotalBillQuery .= " AND EntryDate < '$FromDate'";
                }

                $sqlConsumedFeilds = "SUM(BagsUsed) AS TotalBagsUsed, SUM(QuantityUsed) AS TotalQuantityUsed";
                $sqlConsumedData = "SELECT $sqlConsumedFeilds FROM GsPlantWiseConsumptionDetails
                WHERE ChemicalID = '$ChemicalID' $InnerQuery $BillQuery";
                $stmtConsumedData = $this->conn->prepare($sqlConsumedData);
                $stmtConsumedData->execute();
                $dataConsumedData = $stmtConsumedData->fetch(PDO::FETCH_ASSOC);
                $TotalBagsUsed = isset($dataConsumedData['TotalBagsUsed']) && $dataConsumedData['TotalBagsUsed'] != null ? $dataConsumedData['TotalBagsUsed'] : 0;
                $TotalQuantityUsed = isset($dataConsumedData['TotalQuantityUsed']) && $dataConsumedData['TotalQuantityUsed'] != null ? $dataConsumedData['TotalQuantityUsed'] : 0;

                $ArrayChemOrderList['SNO'] = ++$i;
                $ArrayChemOrderList['ChemicalID'] = $ChemicalID;
                $ArrayChemOrderList['ChemicalName'] = $ChemicalName;
                $ArrayChemOrderList['ProcessingPlantID'] = $dataRow['ProcessingPlantID'];
                // $ArrayChemOrderList['StationName'] = $dataRow['StationName'];
                $ArrayChemOrderList['NoOfKgPerBag'] = $dataRow['NoOfKgPerBag'];

                $TotalQuantityReceived = isset($dataRow['TotalQuantityReceived']) && $dataRow['TotalQuantityReceived'] != null ? $dataRow['TotalQuantityReceived'] : 0;
                $TotalReceivedBags = isset($dataRow['TotalReceivedBags']) && $dataRow['TotalReceivedBags'] != null ? $dataRow['TotalReceivedBags'] : 0;

                $RemainingBags = $TotalReceivedBags - $TotalBagsUsed;
                $RemainingKGs = $TotalQuantityReceived - $TotalQuantityUsed;

                $ArrayChemOrderList['TotalQuantityReceived'] = $TotalQuantityReceived;
                $ArrayChemOrderList['TotalReceivedBags'] = $TotalReceivedBags;
                $ArrayChemOrderList['TotalBagsUsed'] = $TotalBagsUsed;
                $ArrayChemOrderList['TotalQuantityUsed'] = $TotalQuantityUsed;

                $ArrayChemOrderList['RemainingBags'] = $RemainingBags;
                $ArrayChemOrderList['RemainingKGs'] = $RemainingKGs;

                if ($TotalQuery != '') {
                    $sqlTotalRcvdSelect = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
                    LEFT JOIN GsChemicalMaster AS GCM ON GCRQ.ChemicalID = GCM.ChemID
                    LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
                    WHERE GCRQ.ChemicalID='$ChemicalID' $TotalQuery";

                    $stmtTotalRcvdSelect = $this->conn->prepare($sqlTotalRcvdSelect);
                    $stmtTotalRcvdSelect->execute();
                    $dataTotalRcvdSelect = $stmtTotalRcvdSelect->fetch(PDO::FETCH_ASSOC);
                    $TotalChemQuantityReceived = $dataTotalRcvdSelect['TotalQuantityReceived'];
                    $TotalChemReceivedBags = $dataTotalRcvdSelect['TotalReceivedBags'];

                    $sqlTotalConsumedData = "SELECT $sqlConsumedFeilds FROM GsPlantWiseConsumptionDetails
                    WHERE ChemicalID = '$ChemicalID' $InnerQuery $TotalBillQuery";
                    $stmtTotalConsumedData = $this->conn->prepare($sqlTotalConsumedData);
                    $stmtTotalConsumedData->execute();
                    $dataTotalConsumedData = $stmtTotalConsumedData->fetch(PDO::FETCH_ASSOC);
                    $TotalChemBagsUsed = $dataTotalConsumedData['TotalBagsUsed'];
                    $TotalChemQuantityUsed = $dataTotalConsumedData['TotalQuantityUsed'];

                    // $ArrayChemOrderList['TotalQuantityReceivedOpening'] = $TotalQuantityReceived;
                    // $ArrayChemOrderList['TotalReceivedBags'] = $TotalReceivedBags;
                    // $ArrayChemOrderList['TotalBagsUsed'] = $TotalBagsUsed;
                    // $ArrayChemOrderList['TotalQuantityUsed'] = $TotalQuantityUsed;
                    $ArrayChemOrderList['OpeningQuanting'] = $TotalChemQuantityReceived - $TotalChemQuantityUsed;
                    $ArrayChemOrderList['OpeningBags'] = $TotalChemReceivedBags - $TotalChemBagsUsed;
                    $ArrayChemOrderList['ClosingQuanting'] = $ArrayChemOrderList['OpeningQuanting'] + $RemainingKGs;
                    $ArrayChemOrderList['ClosingBags'] = $ArrayChemOrderList['OpeningBags'] + $RemainingBags;
                }

                $ArrayChemOrdersData[] = $ArrayChemOrderList;

            }

        } else { 
            foreach ($monthRange as $range) {
                $start = $range['start'];
                $end = $range['end'];

                $TotalQuery = $CommonQuery;
                $Query = $CommonQuery;
                $Query .= " AND BillDate BETWEEN '$start' AND '$end' ";
                $TotalQuery .= " AND BillDate < '$start'";

                  $sqlChemicals = "SELECT * FROM GsChemicalMaster WHERE `Status`=1";
                if ($ChemicalID != '') {
                    $sqlChemicals .= " AND ChemID = '$ChemicalID'";
                }
            $stmtChemicals = $this->conn->prepare($sqlChemicals);
            $stmtChemicals->execute();

            $i = 0;

            $ArrayChemOrdersDataMonthly = [];
            while ($dataRowChemical = $stmtChemicals->fetch(PDO::FETCH_ASSOC)) {

                $ChemicalID = $dataRowChemical['ChemID'];
                $ChemicalName = $dataRowChemical['ChemicalName'];

                $sqlFields = "SUM(QuantityReceived) AS TotalQuantityReceived, SUM(ReceivedBags) AS TotalReceivedBags,
                 ProcessingPlantID,BillDate, ChemicalID, RPS.StationName";
                $sqlSelect = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
                LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
                WHERE 1=1 $Query AND ChemicalID=$ChemicalID
                GROUP BY ChemicalID ORDER BY ChemicalID";
                // echo $sqlSelect;exit;

                $stmtSelect = $this->conn->prepare($sqlSelect);
                $stmtSelect->execute();
                // $errorInfo = $stmtSelect->errorInfo();
                // var_dump($errorInfo);

                $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
                    // echo print_r($dataRow);

                    $BillQuery = "";
                    $TotalBillQuery = "";
                    if ($BillDate != '') {
                        $BillQuery .= " AND EntryDate <= '$BillDate' ";
                    } else if ($start != '' && $end != '') {
                        $BillQuery .= " AND EntryDate BETWEEN '$start' AND '$ToDate' ";
                        $TotalBillQuery .= " AND EntryDate < '$start'";
                    }

                    $sqlConsumedFeilds = "SUM(BagsUsed) AS TotalBagsUsed, SUM(QuantityUsed) AS TotalQuantityUsed";
                    $sqlConsumedData2 = "SELECT $sqlConsumedFeilds FROM GsPlantWiseConsumptionDetails
                    WHERE ChemicalID = '$ChemicalID' AND  UsageType = 2  $InnerQuery $BillQuery";
                    $stmtConsumedData2 = $this->conn->prepare($sqlConsumedData2);
                    $stmtConsumedData2->execute();
                    $dataConsumedData2 = $stmtConsumedData2->fetch(PDO::FETCH_ASSOC);
                    $TotalBagsUsed2 = isset($dataConsumedData2['TotalBagsUsed']) && $dataConsumedData2['TotalBagsUsed'] != null ? $dataConsumedData2['TotalBagsUsed'] : 0;
                    $TotalQuantityUsed2 = isset($dataConsumedData2['TotalQuantityUsed']) && $dataConsumedData2['TotalQuantityUsed'] != null ? $dataConsumedData2['TotalQuantityUsed'] : 0;

                    $sqlConsumedData = "SELECT $sqlConsumedFeilds FROM GsPlantWiseConsumptionDetails
                    WHERE ChemicalID = '$ChemicalID' AND  UsageType = 1  $InnerQuery $BillQuery";
                    // echo $sqlConsumedData;exit;
                    $stmtConsumedData = $this->conn->prepare($sqlConsumedData);
                    $stmtConsumedData->execute();
                    $dataConsumedData = $stmtConsumedData->fetch(PDO::FETCH_ASSOC);
                    //echo '<pre>';print_r($dataConsumedData);die;
                    $TotalBagsUsed = isset($dataConsumedData['TotalBagsUsed']) && $dataConsumedData['TotalBagsUsed'] != null ? $dataConsumedData['TotalBagsUsed'] : 0;
                    $TotalQuantityUsed = isset($dataConsumedData['TotalQuantityUsed']) && $dataConsumedData['TotalQuantityUsed'] != null ? $dataConsumedData['TotalQuantityUsed'] : 0;

                    $ArrayChemOrderList['SNO'] = ++$i;
                    $ArrayChemOrderList['ChemicalID'] = $ChemicalID;
                    $ArrayChemOrderList['ChemicalName'] = $ChemicalName;
                    $ArrayChemOrderList['ProcessingPlantID'] = $dataRow['ProcessingPlantID'];
                    // $ArrayChemOrderList['StationName'] = $dataRow['StationName'];
                    $ArrayChemOrderList['NoOfKgPerBag'] = $dataRow['NoOfKgPerBag'];

                    $TotalQuantityReceived = isset($dataRow['TotalQuantityReceived']) && $dataRow['TotalQuantityReceived'] != null ? $dataRow['TotalQuantityReceived'] : 0;
                    $TotalReceivedBags = isset($dataRow['TotalReceivedBags']) && $dataRow['TotalReceivedBags'] != null ? $dataRow['TotalReceivedBags'] : 0;

                    $RemainingBags = $TotalReceivedBags - $TotalBagsUsed - $TotalBagsUsed2;
                    $RemainingKGs = $TotalQuantityReceived - $TotalQuantityUsed - $TotalQuantityUsed2;

                    $ArrayChemOrderList['TotalQuantityReceived'] = $TotalQuantityReceived;
                    $ArrayChemOrderList['TotalReceivedBags'] = $TotalReceivedBags;
                    $ArrayChemOrderList['TotalBagsUsed'] = $TotalBagsUsed;
                    $ArrayChemOrderList['TotalBagsUsed2'] = $TotalBagsUsed2;
                    $ArrayChemOrderList['TotalQuantityUsed'] = $TotalQuantityUsed;
                     $ArrayChemOrderList['TotalQuantityUsed2'] = $TotalQuantityUsed2;

                    $ArrayChemOrderList['RemainingBags'] = $RemainingBags;
                    $ArrayChemOrderList['RemainingKGs'] = $RemainingKGs;

                    if ($TotalQuery != '') {
                        $sqlTotalRcvdSelect = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
                        LEFT JOIN GsChemicalMaster AS GCM ON GCRQ.ChemicalID = GCM.ChemID
                        LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
                        WHERE GCRQ.ChemicalID='$ChemicalID' $TotalQuery";

                        $stmtTotalRcvdSelect = $this->conn->prepare($sqlTotalRcvdSelect);
                        $stmtTotalRcvdSelect->execute();
                        $dataTotalRcvdSelect = $stmtTotalRcvdSelect->fetch(PDO::FETCH_ASSOC);
                        $TotalChemQuantityReceived = $dataTotalRcvdSelect['TotalQuantityReceived'];
                        $TotalChemReceivedBags = $dataTotalRcvdSelect['TotalReceivedBags'];

                        $sqlTotalConsumedData = "SELECT $sqlConsumedFeilds FROM GsPlantWiseConsumptionDetails
                        WHERE ChemicalID = '$ChemicalID' $InnerQuery $TotalBillQuery";
                        $stmtTotalConsumedData = $this->conn->prepare($sqlTotalConsumedData);
                        $stmtTotalConsumedData->execute();
                        $dataTotalConsumedData = $stmtTotalConsumedData->fetch(PDO::FETCH_ASSOC);
                        $TotalChemBagsUsed = $dataTotalConsumedData['TotalBagsUsed'];
                        $TotalChemQuantityUsed = $dataTotalConsumedData['TotalQuantityUsed'];

                        // $ArrayChemOrderList['TotalQuantityReceivedOpening'] = $TotalQuantityReceived;
                        // $ArrayChemOrderList['TotalReceivedBags'] = $TotalReceivedBags;
                        // $ArrayChemOrderList['TotalBagsUsed'] = $TotalBagsUsed;
                        // $ArrayChemOrderList['TotalQuantityUsed'] = $TotalQuantityUsed;
                        $ArrayChemOrderList['OpeningQuanting'] = $TotalChemQuantityReceived - $TotalChemQuantityUsed;
                        $ArrayChemOrderList['OpeningBags'] = $TotalChemReceivedBags - $TotalChemBagsUsed;
                        $ArrayChemOrderList['ClosingQuanting'] = $ArrayChemOrderList['OpeningQuanting'] + $RemainingKGs;
                        $ArrayChemOrderList['ClosingBags'] = $ArrayChemOrderList['OpeningBags'] + $RemainingBags;
                    }
                    $ArrayChemOrdersDataMonthly[] = $ArrayChemOrderList;
                }
                $result['Data'] = $ArrayChemOrdersDataMonthly;
                if(!$Month){
                    $infoMsg = (new DateTime($start))->format('F-Y') . ' to ' . (new DateTime($end))->format('F-Y');
                }else{
                    $infoMsg = (new DateTime($start))->format('F-Y');
                }
                $result['Month'] = $infoMsg;
                $ArrayChemOrdersData[] = $result;
            }
        }

        $data['Error'] = null;
        $data['Msg'] = "Success";
        $data['Data'] = $ArrayChemOrdersData;

        return $data;
    }
}
