<?php
include_once '../Global/Util.php';

class MicroBiologyReportController
{
    private $conn;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getMicroBiologyReportData($RequestBody)
    {

        $FromDate = $RequestBody['StartDate'];
        $ToDate   = $RequestBody['EndDate'];
        $LotIds   = isset($RequestBody['LotIds']) ? $RequestBody['LotIds'] : '';
       $loadAll  = isset($RequestBody['loadAll']) && $RequestBody['loadAll'] == true ? true : false;


        // Fetch all reference data
        $allLots        = $this->fetchLookupMap($this->conn, "SELECT RmLotID, LotNumber FROM RmLotDetatils", 'RmLotID', ['LotNumber']);
        $allPlants      = $this->fetchLookupMap($this->conn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations", 'ProcessingStationID', ['StationName']);
        $allVarieties   = $this->fetchLookupMap($this->conn, "SELECT ProductID, ShortName FROM ScrmProductList", 'ProductID', ['ShortName']);
        $allParameters  = $this->fetchLookupMap($this->conn, "SELECT TestID, TestName FROM QcTestList", 'TestID', ['TestName']);
        $allMedia       = $this->fetchLookupMap($this->conn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', ['MediaName']);
        $allInstruments = $this->fetchLookupMap($this->conn, "SELECT InstrumentId, InstrumentName FROM instrumentmaster", 'InstrumentId', ['InstrumentName']);

        $sqlQuery  = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ";
        $stmtUsers = $this->conn->prepare($sqlQuery);
        $stmtUsers->execute();
        $RowUsersLists = [];
        while ($RowUsers = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
            $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
        }


    $conditions = [];

    if (!$loadAll) {
        if (!empty($FromDate) && !empty($ToDate)) {
            $conditions[] = "SampleDate BETWEEN '$FromDate' AND '$ToDate'";
        }
    }

if (!empty($LotIds)) {
    if (!is_array($LotIds)) {
        $LotIds = explode(",", $LotIds); 
    }

    $lotConditions = [];
    foreach ($LotIds as $lotId) {
        $lotId = trim($lotId);
        if ($lotId !== '') {
            $lotConditions[] = "FIND_IN_SET('$lotId', BatchNumber)";
        }
    }

    if (!empty($lotConditions)) {
        $conditions[] = "(" . implode(" OR ", $lotConditions) . ")";
    }
}


    $searchQuery = count($conditions) > 0 ? implode(" AND ", $conditions) : "1=1";

    $sqlQuery = "SELECT * FROM swabproducts WHERE $searchQuery ORDER BY id DESC";
    //echo $sqlQuery; exit;
    $stmtSelect = $this->conn->prepare($sqlQuery);
    $stmtSelect->execute();


        $ArrayMediaData = [];

        $i = 0;
        while ($RowLeaveList = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $i++;
            $singlerow = array();

            $singlerow['Sno'] = $i;
            // Batch number(s)
            $batchIDs   = explode(",", $RowLeaveList['BatchNumber']);
            $batchNames = array_map(function ($id) use ($allLots) {
                return $allLots[$id]['LotNumber'] ? $allLots[$id]['LotNumber'] : '';
            }, $batchIDs);
            $singlerow['BatchNumber'] = implode(",", array_filter($batchNames));
            $singlerow['SampleDate']  = $RowLeaveList['SampleDate'];
            $singlerow['DrawnBy']     = $RowUsersLists[$RowLeaveList['DrawnBy']] ? $RowUsersLists[$RowLeaveList['DrawnBy']] : '';

            $singlerow['AnalysisDate'] = $RowLeaveList['AnalysisDate'];
            $singlerow['ReportDate']   = $RowLeaveList['ReportDate'];
            $singlerow['Variety']      = $allVarieties[$RowLeaveList['Variety']]['ShortName'] ? $allVarieties[$RowLeaveList['Variety']]['ShortName'] : '';
            $singlerow['sample_collection']      = $RowLeaveList['sample_collection'];
            $singlerow['Plant']        = $allPlants[$RowLeaveList['Plant']]['StationName'] ? $allPlants[$RowLeaveList['Plant']]['StationName'] : '';

            $singlerow['Parameter']         = $allParameters[$RowLeaveList['Parameter']]['TestName'] ? $allParameters[$RowLeaveList['Parameter']]['TestName'] : '';
            
            $NameMediaUsedIDs   = explode(",", $RowLeaveList['NameMediaUsed']);
            $NameMediaUsedNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $NameMediaUsedIDs);

            $singlerow['NameMediaUsed'] = implode(",", array_filter($NameMediaUsedNames));
            $EquipmentUsedIDs   = explode(",", $RowLeaveList['EquipmentUsed']);
            $EquipmentUsedNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $EquipmentUsedIDs);

            $singlerow['EquipmentUsed'] = implode(",", array_filter($EquipmentUsedNames));
            $tpc_bgcolor                    = $this->setColor($RowLeaveList['TpcResult']);
            $singlerow['TpcResult_color']   = $tpc_bgcolor;
            $singlerow['TpcResult']         = $RowLeaveList['TpcResult'];
            $singlerow['tp_completed_date'] = $RowLeaveList['tp_completed_date'];

            $singlerow['ec_parameter']      = $allParameters[$RowLeaveList['ec_parameter']]['TestName'] ? $allParameters[$RowLeaveList['ec_parameter']]['TestName'] : '';
            $ec_nameMediaUsedIDs   = explode(",", $RowLeaveList['ec_nameMediaUsed']);
            $ec_nameMediaUsedNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $ec_nameMediaUsedIDs);

            $singlerow['ec_nameMediaUsed'] = implode(",", array_filter($ec_nameMediaUsedNames));
            $ec_equipmentUsedIDs   = explode(",", $RowLeaveList['ec_equipmentUsed']);
            $ec_equipmentUsedNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $ec_equipmentUsedIDs);

            $singlerow['ec_equipmentUsed'] = implode(",", array_filter($ec_equipmentUsedNames));
            $singlerow['EcoliResult']       = $RowLeaveList['EcoliResult'];
            $singlerow['ec_completed_date'] = $RowLeaveList['ec_completed_date'];

            $singlerow['sa_parameter']      = $allParameters[$RowLeaveList['sa_parameter']]['TestName'] ? $allParameters[$RowLeaveList['sa_parameter']]['TestName'] : '';
            $sa_nameMediaUsedIDs   = explode(",", $RowLeaveList['sa_nameMediaUsed']);
            $sa_nameMediaUsedNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $sa_nameMediaUsedIDs);

            $singlerow['sa_nameMediaUsed'] = implode(",", array_filter($sa_nameMediaUsedNames));
            $sa_equipmentUsedIDs   = explode(",", $RowLeaveList['sa_equipmentUsed']);
            $sa_equipmentUsedNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $sa_equipmentUsedIDs);

            $singlerow['sa_equipmentUsed'] = implode(",", array_filter($sa_equipmentUsedNames));
            $singlerow['SaureusResult']     = $RowLeaveList['SaureusResult'];
            $singlerow['sa_completed_date'] = $RowLeaveList['sa_completed_date'];

            $singlerow['vc_parameter']      = $allParameters[$RowLeaveList['vc_parameter']]['TestName'] ? $allParameters[$RowLeaveList['vc_parameter']]['TestName'] : '';
             $vc_nameMediaUsedIDs   = explode(",", $RowLeaveList['vc_nameMediaUsed']);
            $vc_nameMediaUsedNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $vc_nameMediaUsedIDs);

            $singlerow['vc_nameMediaUsed']   = implode(",", array_filter($vc_nameMediaUsedNames));
            $vc_equipmentUsedIDs   = explode(",", $RowLeaveList['vc_equipmentUsed']);
            $vc_equipmentUsedNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $vc_equipmentUsedIDs);

            $singlerow['vc_equipmentUsed']   = implode(",", array_filter($vc_equipmentUsedNames));
            $singlerow['VcholeraeResult']   = $RowLeaveList['VcholeraeResult'];
            $singlerow['vc_completed_date'] = $RowLeaveList['vc_completed_date'];

            $singlerow['vp_parameter']            = $allParameters[$RowLeaveList['vp_parameter']]['TestName'] ? $allParameters[$RowLeaveList['vp_parameter']]['TestName'] : '';
            $vp_nameMediaUsedIDs   = explode(",", $RowLeaveList['vp_nameMediaUsed']);
            $vp_nameMediaUsedNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $vp_nameMediaUsedIDs);

            $singlerow['vp_nameMediaUsed']           = implode(",", array_filter($vp_nameMediaUsedNames));
            
            $vp_equipmentUsedIDs   = explode(",", $RowLeaveList['vp_equipmentUsed']);
            $vp_equipmentUsedNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $vp_equipmentUsedIDs);

            $singlerow['vp_equipmentUsed']           = implode(",", array_filter($vp_equipmentUsedNames));
            $singlerow['VparahaemolyticusResult'] = $RowLeaveList['VparahaemolyticusResult'];
            $singlerow['vp_completed_date']       = $RowLeaveList['vp_completed_date'];

            $singlerow['sal_parameter']      = $allParameters[$RowLeaveList['sal_parameter']]['TestName'] ? $allParameters[$RowLeaveList['sal_parameter']]['TestName'] : '';
            $sal_nameMediaUsedIDs   = explode(",", $RowLeaveList['sal_nameMediaUsed']);
            $sal_nameMediaUsedNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $sal_nameMediaUsedIDs);            

            $singlerow['sal_nameMediaUsed'] = implode(",", array_filter($sal_nameMediaUsedNames));

            $sal_equipmentUsedIDs   = explode(",", $RowLeaveList['sal_equipmentUsed']);
            $sal_equipmentUsedNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $sal_equipmentUsedIDs);

            $singlerow['sal_equipmentUsed'] = implode(",", array_filter($sal_equipmentUsedNames));
            $singlerow['Salmonella']         = $RowLeaveList['Salmonella'];
            $singlerow['sal_completed_date'] = $RowLeaveList['sal_completed_date'];

            $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
            $singlerow['CheckedBy']   = $RowUsersLists[$RowLeaveList['CheckedBy']] ? $RowUsersLists[$RowLeaveList['CheckedBy']] : '';
            $singlerow['VerfiedBy']   = $RowUsersLists[$RowLeaveList['VerfiedBy']] ? $RowUsersLists[$RowLeaveList['VerfiedBy']] : '';
            $singlerow['Remarks']     = $RowLeaveList['Remarks'];
            $data[]                   = $singlerow;
        }

        $data['Error'] = null;
        $data['Msg']   = 'Success';
        $data['Data']  = $data;
        return $data;
    }

    public function fetchLookupMap($iconn, $query, $keyField, $valueFields)
    {
        $result = $iconn->prepare($query);
        $result->execute();
        $map = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $key   = $row[$keyField];
            $value = [];
            foreach ($valueFields as $field) {
                $value[$field] = $row[$field];
            }
            $map[$key] = $value;
        }
        return $map;
    }

    public function setColor($val)
    {
        $color = "";
        if (is_numeric($val) && $val > 40000) {
            $color = "Red";
        } else if (strtolower($val) == "positive") {
            $color = "Red";
        } else if (strtolower($val) == "suspect") {
            $color = "Orange";
        }
        return $color;
    }

}
