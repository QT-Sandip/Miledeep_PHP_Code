<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class StoreDeliveryDetailsListController
{

    private $conn;
    private $db_table = "ScrmStoreDeliveryDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getStoreDeliveryDetails()
    {
        $SqlFields = "StoreName,StoreAddress1,BuyerId,PortofDischarge,finalDestination";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayStoreDeliveryList['SNO'] = ++$i;
            $ArrayStoreDeliveryList['StoreName'] = $dataRow['StoreName'];
            $ArrayStoreDeliveryList['StoreAddress'] = $dataRow['StoreAddress1'];
          
            $Buyer = $dataRow['BuyerId'];
            $BuyerDetails = $Controller->getConsigneeDetails($Buyer);
            $ArrayStoreDeliveryList['Buyer'] = $BuyerDetails['ConsigneeName'];

            $ArrayStoreDeliveryList['PortOfDischarge'] = 'Doubt';
            $ArrayStoreDeliveryList['FinalDestination'] = 'Doubt';
            $Data[] = $ArrayStoreDeliveryList;


        }
        return $Data;
    }

}
?>