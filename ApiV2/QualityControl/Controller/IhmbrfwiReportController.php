<?php
include_once '../Global/Util.php';

class IhmbrfwiReportController
{
    private $conn;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getIhmbrfwiReportData($RequestBody)
    {

        $FromDate = $RequestBody['StartDate'];
        $ToDate   = $RequestBody['EndDate'];

        // Fetch all reference data        
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

        $searchQuery = " sample_collection BETWEEN '$FromDate' and '$ToDate' and report_type = '".$RequestBody['report_type']."' ";

        $sqlQuery = "SELECT * from micro_biology_water_ice where $searchQuery";
        //echo $sqlQuery; exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayMediaData = [];

        $i = 0;
        while ($RowLeaveList = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $i++;
            $singlerow = array();

            $singlerow['Sno'] = $i;
            $singlerow['report_type'] = $RowLeaveList['report_type'];
            $singlerow['sample_collection']  = $RowLeaveList['sample_collection'];
            $singlerow['sample_drawn_by']     = $RowUsersLists[$RowLeaveList['sample_drawn_by']] ? $RowUsersLists[$RowLeaveList['sample_drawn_by']] : '';
            $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
            $singlerow['checked_by']   = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
            $singlerow['verified_by']      = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';

            $singlerow['plant_id'] = $allPlants[$RowLeaveList['plant_id']]['StationName'] ? $allPlants[$RowLeaveList['plant_id']]['StationName'] : '';
            $singlerow['sample_desc'] = $RowLeaveList['sample_desc'];
            $singlerow['location'] = $RowLeaveList['location'];
            $singlerow['tap_no_for_water'] = $RowLeaveList['tap_no_for_water'];
            $singlerow['itype_of_ice'] = $RowLeaveList['itype_of_ice'];

            $singlerow['tpc_paramater']     = $allParameters[$RowLeaveList['tpc_paramater']]['TestName'] ? $allParameters[$RowLeaveList['tpc_paramater']]['TestName'] : '';

            $tpc_mediaIDs   = explode(",", $RowLeaveList['tpc_media']);
            $tpc_mediaNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $tpc_mediaIDs);

            $singlerow['tpc_media'] = implode(",", array_filter($tpc_mediaNames));

            //equipmentused
            $tpc_equipementIDs   = explode(",", $RowLeaveList['tpc_equipement']);
            $tpc_equipementNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $tpc_equipementIDs);

            $singlerow['tpc_equipement'] = implode(",", array_filter($tpc_equipementNames));
            
            $singlerow['tpc_result1']     = $RowLeaveList['tpc_result1'];
            $singlerow['tpc_result2']     = $RowLeaveList['tpc_result2'];
            $singlerow['tpc_completion_date']     = $RowLeaveList['tpc_completion_date'];            
            $singlerow['vcholerae_paramater']     = $allParameters[$RowLeaveList['vcholerae_paramater']]['TestName'] ? $allParameters[$RowLeaveList['vcholerae_paramater']]['TestName'] : '';

            $vcholerae_mediaIDs   = explode(",", $RowLeaveList['vcholerae_media']);
            $vcholerae_mediaNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $vcholerae_mediaIDs);

            $singlerow['vcholerae_media'] = implode(",", array_filter($vcholerae_mediaNames));
            
             $vcholerae_equipementIDs   = explode(",", $RowLeaveList['vcholerae_equipement']);
            $vcholerae_equipementNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $vcholerae_equipementIDs);

            $singlerow['vcholerae_equipement'] = implode(",", array_filter($vcholerae_equipementNames));

            
            $singlerow['vcholerae_result1']     = $RowLeaveList['vcholerae_result1'];
                        
            $singlerow['vcholerae_completion_date']     = $RowLeaveList['vcholerae_completion_date'];


            $singlerow['cholerae_paramater']     = $allParameters[$RowLeaveList['cholerae_paramater']]['TestName'] ? $allParameters[$RowLeaveList['cholerae_paramater']]['TestName'] : '';

            $cholerae_mediaIDs   = explode(",", $RowLeaveList['cholerae_media']);
            $cholerae_mediaNames = array_map(function ($id) use ($allMedia) {
                return $allMedia[$id]['MediaName'] ? $allMedia[$id]['MediaName'] : '';
            }, $cholerae_mediaIDs);

            $singlerow['cholerae_media'] = implode(",", array_filter($cholerae_mediaNames));
            
             $cholerae_equipementIDs   = explode(",", $RowLeaveList['cholerae_equipement']);
            $cholerae_equipementNames = array_map(function ($id) use ($allInstruments) {
                return $allInstruments[$id]['InstrumentName'] ? $allInstruments[$id]['InstrumentName'] : '';
            }, $cholerae_equipementIDs);

            $singlerow['cholerae_equipement'] = implode(",", array_filter($cholerae_equipementNames));

            
            $singlerow['cholerae_result1']     = $RowLeaveList['cholerae_result1'];
                        
            $singlerow['cholerae_completion_date']     = $RowLeaveList['cholerae_completion_date'];




            $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
            $singlerow['checked_by']   = $RowUsersLists[$RowLeaveList['checked_by']] ? $RowUsersLists[$RowLeaveList['checked_by']] : '';
            $singlerow['verified_by']   = $RowUsersLists[$RowLeaveList['verified_by']] ? $RowUsersLists[$RowLeaveList['verified_by']] : '';
            $singlerow['remarks']     = $RowLeaveList['remarks'];
            $dbdata[] = $singlerow;
        }

        $data['Error'] = null;
        $data['Msg']   = 'Success';
        $data['Data']  = $dbdata;
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

    public function setColor($val){
        $color="";
        if( is_numeric($val) && $val>40000 ){
            $color = "Red";
        }else if( strtolower($val) == "positive" ) {
            $color = "Red";
        }else if( strtolower($val) == "suspect" ) {
            $color = "Orange";
        }    
        return $color;
    }

}
