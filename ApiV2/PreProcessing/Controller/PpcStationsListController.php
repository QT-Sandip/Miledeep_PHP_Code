<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class PpcStationsListController
{
    private $conn;
    private $db_table = "ScrmPIData";
    private $utilController;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetPpcStationsDetails($RequestBody)
    {

        $RecordsPerPage = 100;
        $StationID = $RequestBody['StationID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($StationID != "") {
            $Query .= " and PpcVAStations.StationID = '$StationID'";
        }

        $RecordsPerPage = 100;

        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', UnitName,UnitLocation,StationID,ExtraPercPerKgs) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `PpcVAStations`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        if ($StationID != '') {
            $SqlPpcStationsQuery = "SELECT  * FROM `PpcVAStations` WHERE 1=1 $Query";
        } else {
            $SqlPpcStationsQuery = "SELECT  * FROM `PpcVAStations` WHERE 1=1 $Query ORDER BY StationID " . $Limit;
        }

        $stmt = $this->conn->prepare($SqlPpcStationsQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayStationsList['SNO'] = ++$i;
            $ArrayStationsList['StationID'] = $dataRow['StationID'];
            $ArrayStationsList['UnitName'] = $dataRow['UnitName'];
            $ArrayStationsList['UnitLocation'] = $dataRow['UnitLocation'];
            $ArrayStationsList['UnitInchargeID'] = $dataRow['UnitInchargeID'];
            $ArrayStationsList['UnitStatus'] = $dataRow['UnitStatus'];
            $ArrayStationsList['ExtraPercPerKgs'] = $dataRow['ExtraPercPerKgs'];
            $ArrayStationsList['ExtraAmountPerPerson'] = $dataRow['ExtraAmountPerPerson'];

            $AryStationsData[] = $ArrayStationsList;
        }

        if (count($AryStationsData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "create-ppc-station?StationID=";
        $data['Data'] = $AryStationsData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
