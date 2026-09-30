<?php

include_once '../Global/Util.php';
include_once 'Controller/TaxDetailsController.php';
include_once 'Controller/UtilController.php';
class TaxDetailsController
{

    private $conn;
    private $db_table = "ScrmMiscCharges";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getTaxDetails()
    {
        $SqlFields = " CountryID,ClearanceCharge,AntiDumpingCharge,AmcChargePerc,ApplicableDate,GeneralType,ValidUpTo,
        MerchandiseProcessFee,USClearance,HarbourMaintenanceFee,DrawbackRateperKG,DrawbackRateperPercentage,ISFStaticDuty,
        ServiceTaxPercentage,PremiumRate,BLServiceTax,BLSWCES,PostedDate";

        $sqlQuery = "SELECT  $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayTaxList['SNO'] = $i;
            $ArrayTaxList['Country'] = $dataRow['CountryID'];
            $ArrayTaxList['ClearanceCharges'] = $dataRow['ClearanceCharge'];
            $ArrayTaxList['AntiDumpingCharge'] = $dataRow['AntiDumpingCharge'];
            $ArrayTaxList['AmcChargePerc%'] = $dataRow['AmcChargePerc'];
            $date = date_create($dataRow['ApplicableDate']);
            $ArrayTaxList['ApplicableDate'] = date_format($date, "d-m-Y");
            $ArrayTaxList['GeneralType'] = $dataRow['GeneralType'];
            $ArrayTaxList['ValidUpTo'] = $dataRow['ValidUpTo'];
            $ArrayTaxList['MerchandiseProcessFee'] = $dataRow['MerchandiseProcessFee'];
            $ArrayTaxList['USClearance'] = $dataRow['USClearance'];
            $ArrayTaxList['HarbourMaintenanceFee'] = $dataRow['HarbourMaintenanceFee'];
            $ArrayTaxList['DrawbackRateperKG'] = $dataRow['DrawbackRateperKG'];
            $ArrayTaxList['DrawbackRateperPerc%'] = $dataRow['DrawbackRateperPercentage'];
            $ArrayTaxList['DISFStaticDuty'] = $dataRow['ISFStaticDuty'];
            $ArrayTaxList['ServiceTaxPerc%'] = $dataRow['ServiceTaxPercentage'];
            $ArrayTaxList['PremiumRate'] = $dataRow['PremiumRate'];
            $ArrayTaxList['BLServiceTax'] = $dataRow['BLServiceTax'];
            $ArrayTaxList['BLSWCES'] = $dataRow['BLSWCES'];
            $date = date_create($dataRow['PostedDate']);
            $ArrayTaxList['PostedDate'] = date_format($date, "d-m-Y");
            $Data[] = $ArrayTaxList;


            $i++;
        }
        return $Data;
    }

}
?>