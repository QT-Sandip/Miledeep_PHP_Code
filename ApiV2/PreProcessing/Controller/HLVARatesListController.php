<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class HLVARatesListController
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
    public function GetHLVARatesDetails($RequestBody)
    {

        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];
        $StationID = $RequestBody['StationID'];
        $ContractorType = $RequestBody['ContractorType'];
        $Shift = $RequestBody['Shift'];

        if($Month < 4){
            $Year = $Year+1;
        }
         $Query = "";
        if(!empty($Shift)){
            $Query .= " AND Shift=$Shift ";
        }

        $SqlFields = "ID,EffectiveDate,ShortName";

        $SqlHLVaRatesQuery = "SELECT $SqlFields FROM PpcHLVARatesHistory as PHRH
        LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHRH.Variety = PHVL.VarietyID
        WHERE Month(EffectiveDate)='$Month' and Year(EffectiveDate)='$Year' and StationID='$StationID' and ContractorType='$ContractorType'
        $Query
         ORDER BY ID DESC";
        //    exit;
        $stmt = $this->conn->prepare($SqlHLVaRatesQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayVarietyList['SNO'] = ++$i;
            $ArrayVarietyList['ID'] = $dataRow['ID'];
            $ArrayVarietyList['ShortName'] = $dataRow['ShortName'];
            $ArrayVarietyList['EffectiveDate'] = 'Rates From - ' . $this->utilController->getFormatedDate($dataRow['EffectiveDate']);
            
            $AryVarietyData[] = $ArrayVarietyList;
        }
        
        $data['EditLink'] = "AddHlVaRates?ID=";
        $data['Data'] = $AryVarietyData;

        return $data;
    }
}
