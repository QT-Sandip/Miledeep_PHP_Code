<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class ConsigneeListDetailsController
{

    private $conn;
    private $db_table = "ScrmConsigneeDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getConsigneeListDetails()
    {
        $SqlFields = "ConsigneeName,ConsigneeAddress,ConsigneeCountry,ConsigneeEmailAddress";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayConsigneeList['SNO'] = ++$i;
            $ArrayConsigneeList['ConsigneeName'] = $dataRow['ConsigneeName'];
            $ArrayConsigneeList['ConsigneeAddress'] = $dataRow['ConsigneeAddress'];

            $Country=$dataRow['ConsigneeCountry'];  
            $CountryDetails = $Controller->getCountryDetails($Country);
           
            $ArrayConsigneeList['Country'] = $CountryDetails['CountryName'];
            $ArrayConsigneeList['EmailAddress'] = $dataRow['ConsigneeEmailAddress'];
            $Data[] = $ArrayConsigneeList;



        }
        return $Data;
    }

}
?>