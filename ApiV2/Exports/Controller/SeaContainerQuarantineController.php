<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class SeaContainerQuarantineController
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

        // echo $CountryID;
        // exit;

        if ($CountryID == 110) {

            $sqlQuery = "SELECT ContainerNo,CustomsInvNo, CInvDate,SCI.PiID,ExporterID,ExpName,
            (SELECT VesselName FROM ScrmTranshipmentPortsList WHERE PIID='$PIID' order by TspID asc limit 1) as VesselName ,
            (SELECT VOYNO FROM ScrmTranshipmentPortsList WHERE PIID='$PIID' order by TspID asc limit 1) as VOYNO FROM ScrmCustomInvoices AS SCI 
            LEFT JOIN AdminExporterDetails AS AED ON SCI.ExporterID = AED.ExpID WHERE PIID='$PIID'";

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

            $ArraySpeciesList['ContainerNo'] = $dataRow['ContainerNo'];
            $ArraySpeciesList['CustomsInvNo'] = $dataRow['CustomsInvNo'];
            $ArraySpeciesList['CInvDate'] = $dataRow['CInvDate'];
            $ArraySpeciesList['PiID'] = $dataRow['PiID'];
            $ArraySpeciesList['ExporterID'] = $ExporterID;
            $ArraySpeciesList['HeaderUrl'] = $dataHF['HeaderUrl'];
            $ArraySpeciesList['FooterUrl'] = $dataHF['FooterUrl'];
            $ArraySpeciesList['Client'] = $dataHF['InvShortCode'];
            $ArraySpeciesList['ExpName'] = $dataRow['ExpName'];
            $ArraySpeciesList['VesselName'] = $dataRow['VesselName'];
            $ArraySpeciesList['VOYNO'] = $dataRow['VOYNO'];

            $ArraySpeciesData[] =  $ArraySpeciesList;


            $data['Error'] = null;
            $data['Msg'] = 'Success';
            $data['Data'] = $ArraySpeciesData;
        } else {
            $data['Error'] = "This Report is only for New Zealand Country";
        }


        return $data;
    }
}
