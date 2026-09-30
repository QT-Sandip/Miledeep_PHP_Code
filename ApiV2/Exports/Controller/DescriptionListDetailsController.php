<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class DescriptionListDetailsController
{

    private $conn;
    private $db_table = "ScrmDescriptionMaster";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getDescriptionListDetails()
    {
        $SqlFields = "ExporterID,CustomNote,ChapterNote,ShipmentNote,DrawBackNote,TaxNote,DeclarationNote,
                      AdditionalNote,CommercialNote,ProformaNote,Notifications,EffectiveFromDate";

        $sqlQuery = "SELECT  $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayDescriptionList['SNO'] = ++$i;
            $date = date_create($dataRow['EffectiveFromDate']);
            $ArrayDescriptionList['ApplicableDate'] = date_format($date, "d-m-Y");

            $ExporterID = $dataRow['ExporterID'];
            $CountryID ="";
            $DocType = "";
            $ExporterDetails = $Controller->getExporterDetails($ExporterID, $CountryID, $DocType);
            $ArrayDescriptionList['Exporter'] = $ExporterDetails['ExporterName'];

            $ArrayDescriptionList['CustomNote'] = $dataRow['CustomNote'];
            $ArrayDescriptionList['ChapterNote'] = $dataRow['ChapterNote'];
            $ArrayDescriptionList['ShipmentNote'] = $dataRow['ShipmentNote'];
            $ArrayDescriptionList['DrawBackNote'] = $dataRow['DrawBackNote'];
            $ArrayDescriptionList['TaxNote'] = $dataRow['TaxNote'];
            $ArrayDescriptionList['DeclarationNote'] = $dataRow['DeclarationNote'];
            $ArrayDescriptionList['AdditionalNote'] = $dataRow['AdditionalNote'];
            $ArrayDescriptionList['ChapterNote'] = $dataRow['ChapterNote'];
            $ArrayDescriptionList['C.InvoiceNote'] = 'Doubt';
            $ArrayDescriptionList['ProformaNote'] = $dataRow['ProformaNote'];
            $ArrayDescriptionList['Notifications'] = $dataRow['Notifications'];

            $Data[] = $ArrayDescriptionList;


        }
        return $Data;
    }

}
?>