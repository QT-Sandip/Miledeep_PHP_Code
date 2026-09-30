<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class PortsListDetailsController
{

    private $conn;
    private $db_table = "ScrmPortCities";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getPortsListDetails($Country)
    {

        $SqlFields = "Country,ReferencePortID,PortCode";

        $sqlQuery = "SELECT  $SqlFields FROM " . $this->db_table . " ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


        $i = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPortsList['SNO'] = ++$i;
           
            $Country = $dataRow['Country'];
            $CountryDetails = $Controller->getCountryDetails($Country);
            $ArrayPortsList['Country'] = $CountryDetails['CountryName'];

            $ArrayPortsList['port'] = $dataRow['ReferencePortID'];
            $ArrayPortsList['PortCode'] = $dataRow['PortCode'];
            $Data[] = $ArrayPortsList;



        }
        return $Data;
    }

}
?>