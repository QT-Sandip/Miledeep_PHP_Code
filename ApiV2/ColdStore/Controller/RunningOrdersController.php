<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class RunningOrdersController
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

    function getRunningOrderDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $Exporter = $RequestBody['Exporter'];
        $Processor = $RequestBody['Processor'];
        $Consignee = $RequestBody['Consignee'];
        $ColdStore = $RequestBody['ColdStore'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $MCSRestriction = $RequestBody['MCSRestriction'];

        $Query = '';

        if ($Exporter != "") {
            $Query = ' and POD.ExporterID=' . $Exporter;
        }
        if ($Processor != '') {
            $Query .= ' and POD.ProcessorID=' . $Processor;
        }

        if ($Consignee != "") {
            $Query .= ' and POD.ConsigneeID=' . $Consignee;
        }
        if ($ColdStore != "") {
           // $Query .= ' and POD.ProductionPlantID IN ('.$ColdStore.')';
            $Query .= " and FIND_IN_SET($ColdStore,POD.ProductionPlantID) ";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', PoID, InvNo, PoNo, PoDate, InvDate, SCL.name, ConsigneeCountry, ConsigneeName, Color, SSC.Name) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `ScrmPoData` AS POD 
        LEFT JOIN ScrmConsigneeDetails AS SCD ON POD.ConsigneeID=SCD.ConID 
        LEFT JOIN ScrmCountriesList AS SCL ON POD.DestinationCountry=SCL.id 
        LEFT JOIN ScrmShipmentStatusCodes AS SSC ON POD.Status=SSC.Code
        LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON SDB.PiID=POD.PoID
        WHERE  (SDB.TransportedDate ='' or SDB.TransportedDate is null)
        AND POD.Status NOT IN (2, 3, 4)
         $Query order by PoID desc";
        
        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);
        $stockStatusField = "
                            IFNULL(
                                (
                                    SELECT SUM( AvalMcs + (LooseBags / NULLIF(APS.TotalSlabs, 0)) )
                                    FROM CsStockData CSD
                                    LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
                                    WHERE PoID = POD.PoID
                                ) / NULLIF(
                                        (
                                        SELECT SUM(Mcs)
                                        FROM ScrmPoGradeData
                                        WHERE InvId = POD.PoID
                                        ),
                                    0
                                ),
                            0) AS PoFilledStatus ";
        $OrderBy = " order by PoFilledStatus desc ";
        if(!empty($MCSRestriction)){
            $stockStatusField = "
                            IFNULL(
                                (
                                    SELECT SUM( AvalMcs + (LooseBags / NULLIF(APS.TotalSlabs, 0)) )
                                    FROM CsStockData CSD
                                    LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
                                    WHERE PoID = POD.PoID AND PackingTypeID = 'MASTER'
                                ) / NULLIF(
                                        (
                                        SELECT SUM(Mcs)
                                        FROM ScrmPoGradeData
                                        WHERE InvId = POD.PoID
                                        ),
                                    0
                                ),
                            0) AS PoFilledStatusMaster,
                            IFNULL(
                                (
                                    SELECT SUM( AvalMcs + (LooseBags / NULLIF(APS.TotalSlabs, 0)) )
                                    FROM CsStockData CSD
                                    LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
                                    WHERE PoID = POD.PoID AND PackingTypeID = 'DUMMY'
                                ) / NULLIF(
                                        (
                                        SELECT SUM(Mcs)
                                        FROM ScrmPoGradeData
                                        WHERE InvId = POD.PoID
                                        ),
                                    0
                                ),
                            0) AS PoFilledStatusDummy ";
            $OrderBy = " order by (PoFilledStatusMaster + PoFilledStatusDummy) desc ";
        }

        $sqlFields = "PoID, InvNo, PoNo, PoDate, InvDate, SCL.name as CountryName, ConsigneeCountry, ConsigneeName, Color, SSC.Name as PoStatus,
        POD.ProcessorID, POD.ConsigneeID, POD.ExporterID,
        $stockStatusField ";

        $sqlQuery = "SELECT $sqlFields FROM ScrmPoData AS POD 
        LEFT JOIN ScrmConsigneeDetails AS SCD ON POD.ConsigneeID=SCD.ConID 
        LEFT JOIN ScrmCountriesList AS SCL ON POD.DestinationCountry=SCL.id 
        LEFT JOIN ScrmShipmentStatusCodes AS SSC ON POD.Status=SSC.Code
        LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON SDB.PiID=POD.PoID
        WHERE  (SDB.TransportedDate ='' or SDB.TransportedDate is null)
         AND 
         POD.Status NOT IN (2, 3, 4)
         $Query 
         -- order by PoID desc 
         $OrderBy
         " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayRunningData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $PoID = $dataRow['PoID'];

            /* $SqlStockSum = "SELECT SUM(AvalMcs) AS StoreStock FROM `CsStockData` WHERE PoID='$PoID'";

            $stmtStockSum = $this->conn->prepare($SqlStockSum);
            $stmtStockSum->execute();

            $rowStockSum = $stmtStockSum->fetch(PDO::FETCH_ASSOC);
            $StoreStock = $rowStockSum['StoreStock'];

            $SqlPOSum = "SELECT SUM(Mcs) AS POStock FROM `ScrmPoGradeData` WHERE InvId='$PoID'";

            $stmtPOSum = $this->conn->prepare($SqlPOSum);
            $stmtPOSum->execute();

            $rowPOSum = $stmtPOSum->fetch(PDO::FETCH_ASSOC);
            $POStock = $rowPOSum['POStock'];

            $Percent = round((($StoreStock / $POStock) * 100), 2) . '%'; */
            if(!empty($MCSRestriction)){
                $PercentMaster = round((($dataRow['PoFilledStatusMaster']) * 100), 2) . '%';
                if ($PercentMaster >= 90) {
                    $MasterColor = '#28a745';
                    $MasterColorclass = 'badge-success';
                } elseif ($PercentMaster >= 50 && $PercentMaster<90) {
                    $MasterColor = '#ffc107';
                    $MasterColorclass = 'badge-warning';
                } else {
                    $MasterColor = '#dc3545';
                    $MasterColorclass = 'badge-danger';
                }
                $PercentDummy = round((($dataRow['PoFilledStatusDummy']) * 100), 2) . '%';
                if ($PercentDummy >= 90) {
                    $DummyColor = '#28a745';
                    $DummyColorclass = 'badge-success';
                } elseif ($PercentDummy >= 50 && $PercentDummy<90) {
                    $DummyColor = '#ffc107';
                    $DummyColorclass = 'badge-warning';
                } else {
                    $DummyColor = '#dc3545';
                    $DummyColorclass = 'badge-danger';
                }
            }else{
                $Percent = round((($dataRow['PoFilledStatus']) * 100), 2) . '%';
                if ($Percent >= 90) {
                    $Color = '#28a745';
                    $Colorclass = 'badge-success';
                } elseif ($Percent >= 50 && $Percent<90) {
                    $Color = '#ffc107';
                    $Colorclass = 'badge-warning';
                } else {
                    $Color = '#dc3545';
                    $Colorclass = 'badge-danger';
                }
            }


            $ArrayRunningList['SNO'] = ++$i;
            $ArrayRunningList['PoID'] = $dataRow['PoID'];
            $ArrayRunningList['InvNo'] = $dataRow['InvNo'];
            $ArrayRunningList['PoNo'] = $dataRow['PoNo'];
            $ArrayRunningList['PoDate'] = $dataRow['PoDate'];
            $ArrayRunningList['InvDate'] = $dataRow['InvDate'];
            $ArrayRunningList['ConsigneeName'] = $dataRow['ConsigneeName'];
            $ArrayRunningList['CountryName'] = $dataRow['CountryName'];
            $ArrayRunningList['ConsigneeCountry'] = $dataRow['ConsigneeCountry'];
            if(!empty($MCSRestriction)){
                //master
                $ArrayRunningList['MasterColor'] = $MasterColor;
                $ArrayRunningList['MasterColorclass'] = $MasterColorclass;              
                $ArrayRunningList['MasterPercent'] = $PercentMaster;
                //dummy
                $ArrayRunningList['DummyColor'] = $DummyColor;
                $ArrayRunningList['DummyColorclass'] = $DummyColorclass;             
                $ArrayRunningList['DummyPercent'] = $PercentDummy;
            }else{
                $ArrayRunningList['Color'] = $Color;
                $ArrayRunningList['Colorclass'] = $Colorclass;
                $ArrayRunningList['Percent'] = $Percent;
            }
            $ArrayRunningList['PoStatus'] = $dataRow['PoStatus'];
            $ArrayRunningList['ProcessorID'] = $dataRow['ProcessorID'];
            $ArrayRunningList['ConsigneeID'] = $dataRow['ConsigneeID'];
            $ArrayRunningList['ExporterID'] = $dataRow['ExporterID'];


            $ArrayRunningData[] =  $ArrayRunningList;
        }

        if (count($ArrayRunningData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "";
        $data['Data'] = $ArrayRunningData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
