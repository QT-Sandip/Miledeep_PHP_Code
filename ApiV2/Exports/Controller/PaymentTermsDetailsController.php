<?php

include_once '../Global/Util.php';
class PaymentTermsDetailsController
{

    private $conn;
    private $db_table = "ScrmPaymentTerms";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getPaymentTermsDetails()
    {
        $SqlFields = "PaymentName,PaymentMode,ConditionDate,NoOfDays";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 1;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPaymentList['SNO'] = $i;
            $ArrayPaymentList['PaymentName'] = $dataRow['PaymentName'];
            $ArrayPaymentList['PaymentMode'] = $dataRow['PaymentMode'];
            $ArrayPaymentList['Condition'] = $dataRow['ConditionDate'];
            $ArrayPaymentList['NumberOfDays'] = $dataRow['NoOfDays'];

            $Data[] = $ArrayPaymentList;

            $i++;
        }
        return $Data;
    }

}
?>