<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class HLVARatesPrintController
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
    public function GetHLVARatesPrint($RequestBody)
    {
        $RateID = $RequestBody['RateID'];


        // $sqlFields = "Grade,RateID,PricePerKg,EffectiveDate";

        $sqlFields = "PHR.Grade,PHR.RateID,PHR.PricePerKg,PHH.EffectiveDate,PHVL.ShortName,PHH.Shift,PHH.ContractorType";


        // echo 
        // $SqlHLVAQuery = "SELECT $sqlFields FROM PpcHLVARates AS PHR 
        // LEFT JOIN PpcHLVARatesHistory AS PHH ON PHR.RateID = PHH.ID 
        // WHERE PHR.RateID='$RateID'";
        // exit;


        $SqlHLVAQuery = "SELECT $sqlFields 
FROM PpcHLVARates AS PHR
LEFT JOIN PpcHLVARatesHistory AS PHH ON PHR.RateID = PHH.ID
LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHH.Variety = PHVL.VarietyID
WHERE PHR.RateID = '$RateID'";


        $stmt = $this->conn->prepare($SqlHLVAQuery);
        $stmt->execute();

        $AryHLVARate = [];
        // $AryHLVARate['FromDate'];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayHLVARates['Grade'] = $dataRow['Grade'];
            $ArrayHLVARates['RateID'] = $dataRow['RateID'];
            $ArrayHLVARates['PricePerKg'] = $dataRow['PricePerKg'];
            $ArrayHLVARates['EffectiveDate'] = $dataRow['EffectiveDate'];
            $ArrayHLVARates['ShortName'] = $dataRow['ShortName'];
            $ArrayHLVARates['Shift'] = $dataRow['Shift'];
            $ArrayHLVARates['ContractorType'] = ($dataRow['ContractorType'] == 1) ? 'Contractor' : 'Mestri';

            $AryHLVARate[] = $ArrayHLVARates;
        }
        $data['Data'] = $AryHLVARate;

        return $data;
    }
}
