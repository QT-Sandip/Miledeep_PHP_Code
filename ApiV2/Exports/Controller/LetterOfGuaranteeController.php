<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class LetterOfGuaranteeController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }


    function getDetails($RequestBody)
    {
        $PIID = $RequestBody['PIID'];

        $sqlCountry = "SELECT DestinationCountry FROM ScrmPoData WHERE PoID='$PIID'";
        $stmtCountry = $this->conn->prepare($sqlCountry);
        $stmtCountry->execute();
        $dataCountry = $stmtCountry->fetch(PDO::FETCH_ASSOC);
        $CountryID = $dataCountry['DestinationCountry'];

        if ($CountryID == 110) {

            $sqlFeilds = "CustomsInvNo, SCI.ExporterID, PiID, CInvDate, ContainerNo, ExpName, ConsigneeName, ConID,  ConsigneeID, PoNo, PoID";
            $sqlQuery = "SELECT $sqlFeilds FROM ScrmCustomInvoices AS SCI
            LEFT JOIN AdminExporterDetails AS AED ON SCI.ExporterID = AED.ExpID
            LEFT JOIN ScrmPoData AS SPD ON SCI.PiID = SPD.PoID
            LEFT JOIN ScrmConsigneeDetails SCD ON SPD.ConsigneeID = SCD.ConID
            WHERE PiID='$PIID'";
            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();

            // $errorInfo = $stmtSelect->errorInfo();
            // var_dump($errorInfo);

            $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            $ExporterID = $dataRow['ExporterID'];
            
            $sqlHF = "SELECT LogoUrl,HeaderUrl,FooterUrl,WaterMark,ExpName, InvShortCode FROM  `QcDocLetterHeads` as QDLH
            LEFT JOIN `AdminExporterDetails` as AED on QDLH.ProcessorID=AED.ExpID where QDLH.ProcessorID='$ExporterID'";
            $stmtHF = $this->conn->prepare($sqlHF);
            $stmtHF->execute();
            $dataHF = $stmtHF->fetch(PDO::FETCH_ASSOC);

            $ArrayList['PiID'] = $dataRow['PiID'];
            $ArrayList['CustomsInvNo'] = $dataRow['CustomsInvNo'];
            $ArrayList['ExporterID'] = $ExporterID;
            $ArrayList['HeaderUrl'] = $dataHF['HeaderUrl'];
            $ArrayList['FooterUrl'] = $dataHF['FooterUrl'];
            $ArrayList['Client'] = $dataHF['InvShortCode'];
            $ArrayList['CInvDate'] = $dataRow['CInvDate'];
            $ArrayList['ContainerNo'] = $dataRow['ContainerNo'];
            $ArrayList['ExpName'] = $dataRow['ExpName'];
            $ArrayList['ConsigneeName'] = $dataRow['ConsigneeName'];
            $ArrayList['ConsigneeID'] = $dataRow['ConsigneeID'];
            $ArrayList['PoNo'] = $dataRow['PoNo'];


            $ArrayData[] =  $ArrayList;


            $data['Error'] = null;
            $data['Msg'] = 'Success';
            $data['Data'] = $ArrayData;
        } else {
            $data['Error'] = "This Report is only for New Zealand Country";
        }

        return $data;
    }
}
