<?php

include_once '../Global/Util.php';

class DropdownController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";
    // private $Driver = "1";
    // private $Grader = "2";
    // private $Agent = "3";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getDropdowns($RequestBody)
    {

        $Data['Center'] = $this->getCentersList();
        $Data['Plant'] = $this->getProductionPlantsList();
        $Data['Shift'] = $this->getShiftsList();
        $Data['Chemical'] = $this->getChemical();
        $Data['ProductionGrades'] = $this->getProductionGrades();
        $Data['ProductionGradesList'] = $this->getProductionGradesList();
        $Data['SoakingTreatment'] = $this->getSoakingTreatment();
        //$Data['Supervisor']=$this->getSupervisorsList();
        // $Data['Gradingmachine']=$this->getGradingMachinesList();
        // $Data['Defects']=$this->getDefectsList();
        $Data['Variety'] = $this->getVarietiesList();
        $Data['HlVaBillVariety'] = $this->getHlVaBillVarietiesList(); //depreacted
        $Data['TimePeriod'] = $this->getTimePeriodsList();
        $Data['ProductionLines'] = $this->getProductionLines($RequestBody);
        // $Data['Contractor']=$this->getContractorsList();



        return $Data;
    }


    function getCentersList()
    {


        $sqlQuery = "SELECT StationID,UnitName FROM `PpcVAStations` WHERE UnitStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionPlantsList()
    {


        $sqlQuery = "SELECT ProcessingStationID,StationName,FullName FROM `RmProcessingStations` WHERE StationStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getChemical()
    {


        $sqlQuery = "SELECT ChemID,ChemicalName FROM `GsChemicalMaster` where Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionGrades()//Sales popup Grades
    {


        $sqlQuery = "SELECT Grade FROM `ProductionGradesMaster`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionGradesList()//Soaking Grade Devi 31/40(18-09-2024)
    {
        $sqlQuery = "SELECT value as Grade,GradeGroup FROM `ProductionGrades` order by abs(value)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getSoakingTreatment()
    {

        $sqlQuery = "SELECT * FROM `ScrmTreatmentsMaster` where Status=1 ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }





    function getShiftsList()
    {


        $sqlQuery = "SELECT ShiftID as OptID,ShiftName as Name FROM `AdminShiftDetails`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    // function getSupervisorsList(){


    //     $sqlQuery = "SELECT ID,UserName FROM `RmUsersList` WHERE UserType=5";
    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
    //     while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
    //         $Data[]=$dataRow;
    //     }
    //     return $Data;
    // }
    //   function getGradingMachinesList(){


    //         $sqlQuery = "SELECT MachineID,MachineName FROM `AdminGradingMachines`";
    //         $stmt = $this->conn->prepare($sqlQuery);
    //         $stmt->execute();
    //         while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
    //             $Data[]=$dataRow;
    //         }
    //         return $Data;
    //     }


    // function getDefectsList(){


    //     $sqlQuery = "SELECT DefectID As VarietyID,DefectName as VarietyName FROM `RmDefectsList`";
    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
    //     while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
    //         $Data[]=$dataRow;
    //     }

    //     return $Data;
    // }
    function getVarietiesList()
    {


        $sqlQuery = "SELECT VarietyID as OptID, ShortName as Name FROM `PpcHLVAVarietyList` where  Status=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getHlVaBillVarietiesList()
    {


        $sqlQuery = "SELECT VarietyID,ShortName, Species FROM `PpcHLVAVarietyList`"; //PpcHLVAVarietyList
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getTimePeriodsList()
    {


        $sqlQuery = "SELECT Value as OptID,Name FROM `AdminGeneralOptions` WHERE OptionType='Timeperiod' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getProductionLines($RequestBody = null)
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='IQFLine' and Status=1 ";
        if(!empty($RequestBody['LoadingLinersBasedOnPlant']) && !empty($RequestBody['UnitId'])){
            $sqlQuery .= "  AND FIND_IN_SET(".$RequestBody['UnitId'].", UnitIds) > 0 ";
        }
        $sqlQuery .= " ORDER by SortOrder asc";  
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    //  function getContractorsList(){


    //         $sqlQuery = "SELECT ContrctrName,ContractorID FROM `PpcContractMaster` WHERE Status=1 ";
    //         $stmt = $this->conn->prepare($sqlQuery);
    //         $stmt->execute();
    //         while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
    //             $Data[]=$dataRow;
    //         }

    //         return $Data;
    //     }

}
