<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class ApplicantController
{
    private $conn;
    private $db_table = "ScrmApplicants";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getApplicantDetails()
    {
        $SqlFields = "ID,ConID,ApplicantName,ApplicantAddress,ApplicantCountry";

        $sqlQuery = "SELECT  $SqlFields FROM " . $this->db_table . " where 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayApplicantList['SNO'] = ++$i;
            $ArrayApplicantList['ID'] = $dataRow['ID'];
            $ArrayApplicantList['BuyerName'] = 'Doubt';
            
            $CountryID = $dataRow['ApplicantCountry'];
            $CountryDetails = $Controller->getCountryDetails($CountryID);
            $ArrayApplicantList['Country'] = $CountryDetails['CountryName'];

            $ArrayApplicantList['ApplicantName'] = $dataRow['ApplicantName'];
            $ArrayApplicantList['ApplicantAddress'] = $dataRow['ApplicantAddress'];
            $Data[] = $ArrayApplicantList;
        }

        return $Data;
    }
}
?>