<?php

include_once '../Global/Util.php';
class BanksListDetailsController
{

    private $conn;
    private $db_table = "ScrmBankDetailsMaster";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getBanksListDetails()
    {
        $SqlFields = "ID,ExpId,AccntName,AccountNumber,BankName,BankAddress,SwiftCode,
        ABA_Numnber,BankDetails,RoutingThrough,NegotiatingBank";

        $sqlQuery = "SELECT  $SqlFields FROM " . $this->db_table . " ";
       
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBankList['SNO'] = ++$i;
            $ArrayBankList['ID'] = $dataRow['ID'];

           /* $ExporterID = $dataRow['ExpId'];
            $CountryID ="";
            $DocType = "";
            $ExporterDetails = $Controller->getExporterDetails($ExporterID, $CountryID, $DocType);
            $ArrayBankList['Exporter'] = $ExporterDetails['ExporterName']; */

            $ArrayBankList['AccountName'] = $dataRow['AccntName'];
            $ArrayBankList['AccountNumber'] = $dataRow['AccountNumber'];
            $ArrayBankList['BankName'] = $dataRow['BankName'];
            $ArrayBankList['BankAddress'] = $dataRow['BankAddress'];
            $ArrayBankList['SwitftCode'] = $dataRow['SwiftCode'];
            $ArrayBankList['ABANumber'] = $dataRow['ABA_Numnber'];
            $ArrayBankList['BankDetails'] = $dataRow['BankDetails'];
            $ArrayBankList['RoutingThrough'] = $dataRow['RoutingThrough'];
            $ArrayBankList['NegotiatingBank'] = $dataRow['NegotiatingBank'];
            $Data[] = $ArrayBankList;


        }
        return $Data;
    }

}
?>