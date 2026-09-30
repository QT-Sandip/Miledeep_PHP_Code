<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class NotifyPartyController
{
    private $conn;
    private $db_table = "ScrmNotifyparty";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getNotifyPartyDetails()
    {
        $SqlFields = "NotifyPartyName AS NPartyName,NPartyAddress,NPartyCountry,NPartyEmailID";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " where Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayNotifyList['SNO'] = ++$i;
            $ArrayNotifyList['NotifyPartyName'] = $dataRow['NPartyName'];
            $ArrayNotifyList['NotifyPartyAddress'] = $dataRow['NPartyAddress'];

            $Country = $dataRow['NPartyCountry'];
            $CountryDetails = $Controller->getCountryDetails($Country);

            $ArrayNotifyList['Country'] = $CountryDetails['CountryName'];

            $ArrayNotifyList['EmailAddress'] = $dataRow['NPartyEmailID'];
            $Data[] = $ArrayNotifyList;
        }
        return $Data;
    }
}
?>