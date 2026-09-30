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

        $searchQuery = " SampleDate BETWEEN '$FromDate' and '$ToDate' ";

        $sqlQuery = "SELECT * from swabproducts where $searchQuery";
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
            $singlerow['Plant'] = $allPlants[$RowLeaveList['Plant']]['StationName'] ? $allPlants[$RowLeaveList['Plant']]['StationName'] : '';

            $singlerow['Parameter']     = $allParameters[$RowLeaveList['Parameter']]['TestName'] ? $allParameters[$RowLeaveList['Parameter']]['TestName'] : '';
            $singlerow['NameMediaUsed'] = $allMedia[$RowLeaveList['NameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['NameMediaUsed']]['MediaName'] : '';
            $singlerow['EquipmentUsed'] = $allInstruments[$RowLeaveList['EquipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] : '';
            $singlerow['TpcResult']     = $RowLeaveList['TpcResult'];
            $singlerow['tp_completed_date']     = $RowLeaveList['tp_completed_date'];            

            $singlerow['ec_parameter']     = $allParameters[$RowLeaveList['ec_parameter']]['TestName'] ? $allParameters[$RowLeaveList['ec_parameter']]['TestName'] : '';
            $singlerow['ec_nameMediaUsed'] = $allMedia[$RowLeaveList['ec_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['ec_nameMediaUsed']]['MediaName'] : '';
            $singlerow['ec_equipmentUsed'] = $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] : '';
            $singlerow['EcoliResult']   = $RowLeaveList['EcoliResult'];
            $singlerow['ec_completed_date']     = $RowLeaveList['ec_completed_date'];

            $singlerow['sa_parameter']     = $allParameters[$RowLeaveList['sa_parameter']]['TestName'] ? $allParameters[$RowLeaveList['sa_parameter']]['TestName'] : '';
            $singlerow['sa_nameMediaUsed'] = $allMedia[$RowLeaveList['sa_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['sa_nameMediaUsed']]['MediaName'] : '';
            $singlerow['sa_equipmentUsed'] = $allInstruments[$RowLeaveList['sa_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['sa_equipmentUsed']]['InstrumentName'] : '';
            $singlerow['SaureusResult'] = $RowLeaveList['SaureusResult'];
            $singlerow['sa_completed_date']     = $RowLeaveList['sa_completed_date'];

            $singlerow['vc_parameter']       = $allParameters[$RowLeaveList['vc_parameter']]['TestName'] ? $allParameters[$RowLeaveList['vc_parameter']]['TestName'] : '';
            $singlerow['vc_nameMediaUsed']   = $allMedia[$RowLeaveList['vc_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['vc_nameMediaUsed']]['MediaName'] : '';
            $singlerow['vc_equipmentUsed']   = $allInstruments[$RowLeaveList['vc_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['vc_equipmentUsed']]['InstrumentName'] : '';
            $singlerow['VcholeraeResult'] = $RowLeaveList['VcholeraeResult'];
            $singlerow['vc_completed_date']     = $RowLeaveList['vc_completed_date'];

            $singlerow['vp_parameter']               = $allParameters[$RowLeaveList['vp_parameter']]['TestName'] ? $allParameters[$RowLeaveList['vp_parameter']]['TestName'] : '';
            $singlerow['vp_nameMediaUsed']           = $allMedia[$RowLeaveList['vp_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['vp_nameMediaUsed']]['MediaName'] : '';
            $singlerow['vp_equipmentUsed']           = $allInstruments[$RowLeaveList['vp_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['vp_equipmentUsed']]['InstrumentName'] : '';
            $singlerow['VparahaemolyticusResult'] = $RowLeaveList['VparahaemolyticusResult'];
            $singlerow['vp_completed_date']     = $RowLeaveList['vp_completed_date'];

            $singlerow['sal_parameter']     = $allParameters[$RowLeaveList['sal_parameter']]['TestName'] ? $allParameters[$RowLeaveList['sal_parameter']]['TestName'] : '';
            $singlerow['sal_nameMediaUsed'] = $allMedia[$RowLeaveList['sal_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['sal_nameMediaUsed']]['MediaName'] : '';
            $singlerow['sal_equipmentUsed'] = $allInstruments[$RowLeaveList['sal_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['sal_equipmentUsed']]['InstrumentName'] : '';
            $singlerow['Salmonella']    = $RowLeaveList['Salmonella'];
            $singlerow['sal_completed_date']     = $RowLeaveList['sal_completed_date'];

            $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['CheckedBy']] : '';
            $singlerow['CheckedBy']   = $RowUsersLists[$RowLeaveList['CheckedBy']] ? $RowUsersLists[$RowLeaveList['CheckedBy']] : '';
            $singlerow['VerfiedBy']   = $RowUsersLists[$RowLeaveList['VerfiedBy']] ? $RowUsersLists[$RowLeaveList['VerfiedBy']] : '';
            $singlerow['Remarks']     = $RowLeaveList['Remarks'];
            $data[] = $singlerow;
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

}
