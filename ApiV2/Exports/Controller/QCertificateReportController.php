<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class QCertificateReportController
{
    private $conn;
    // private $db_table = "ScrmPoData";
    private $utilController;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetQCertificateReportData($RequestBody)
    {
        //$RecordsPerPage = 100;

        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ExporterID = $RequestBody['Exporter'];
        $ProcessorID = $RequestBody['Processor'];
        $SearchKey = $RequestBody['SearchKey'];


        $Query = '';
        if ($ExporterID != "") {
            $Query .= 'and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($ProcessorID != "") {
            $Query .= 'and POD.ProcessorID in (' . $ProcessorID . ')';
        }

        $DateFilter = "between '$FromDate' and '$ToDate'";

        /* if ($Page == 0 || $Page == '') {
             $Limit = 'limit 0 , ' . $RecordsPerPage;
         } else {
             $Start = $Page * $RecordsPerPage;
             $End = $Start + $RecordsPerPage;
             $Limit = 'limit ' . $Start . ',' . $RecordsPerPage;
         }*/
        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', CustomsInvNo) LIKE '%$SearchKey%'";
        }


        // if ($FromDate != '' && $ToDate != '') {
        $SqlFieldsList = "POD.PoID, 
            POD.QtyUnit,                        
                           SCI.CustomsInvNo,
                            SCI.QCertificateNo,
                            SPCD.Name as PortOfDischarge,
                            SPCF.Name as FinalDestination,
                            SPCL.Name as PortofLoading,
                            SCL.name as PortofLoadingCountry
                            ";

        $SqlQCertificateReport = "SELECT $SqlFieldsList FROM ScrmCustomInvoices as SCI  			 
			left join  ScrmPoData as POD on SCI.PiID=POD.PoID
			left join  ScrmPortCities as SPCD on SPCD.PortId=POD.PortOfDischarge
			left join  ScrmPortCities as SPCF on SPCF.PortId=POD.FinalDestination 
			left join  ScrmPortCities as SPCL on SPCL.PortId=POD.PortofLoading 
			left join  ScrmCountriesList as SCL on SCL.id=SPCL.Country 
            where  CInvDate $DateFilter $Query and POD.Status not in (4)";

        $StmtQCertificateReport = $this->conn->prepare($SqlQCertificateReport);
        $StmtQCertificateReport->execute();

       // $ArrayQCertificateReport = [];
        $i = 0;
        $TotalNetWeightKgs = 0;
        $TotalNetWeightLbs = 0;
        $TotalFobValueINR = 0;
        $TotalFOBValueUSD = 0;
        $TotalInvoices = 0;
        while ($RowQCertificateReport = $StmtQCertificateReport->fetch(PDO::FETCH_ASSOC)) {

            $PoID = $RowQCertificateReport['PoID'];
            $DocType = 'CMI';
            $QtyUnit = $RowQCertificateReport['QtyUnit'];
            $ArrayPaymentValues = $this->utilController->getPaymentValues($PoID, $DocType);
            $FobValueUSD = $ArrayPaymentValues['FOBValueUSD'];
            $FobValueINR = $ArrayPaymentValues['FOBValueINR'];



            $SqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,               
                sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs";


            $SqlPrice = "SELECT sum(Mcs) as MCS,  $SqlExt,
                 sum(Price) as Price,			
                 sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
                 sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` 
                 as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";

            $StmtPrice = $this->conn->prepare($SqlPrice);
            $StmtPrice->execute();
            $RowPrice = $StmtPrice->fetch(PDO::FETCH_ASSOC);

            $NetWgtLbs = $RowPrice['NetWgtLbs'];

            if ($QtyUnit == 'KGS') {

                $NetWgtKgs = $RowPrice['NetWgtKgs'];

            } else {

                $NetWgtKgs = $RowPrice['NetWgtLbs'] * 0.454;

            }

            $PortofLoadingCountry = $RowQCertificateReport['PortofLoading'] . ' , ' . $RowQCertificateReport['PortofLoadingCountry'];


            $ArrayQCertificateReport['SNO'] = ++$i;
            $ArrayQCertificateReport['InvoiceNO'] = $RowQCertificateReport['CustomsInvNo'];
            $ArrayQCertificateReport['QCertificateNo'] = $RowQCertificateReport['QCertificateNo'];
            $ArrayQCertificateReport['QuantityinLbs'] = number_format($NetWgtLbs, 3);
            $ArrayQCertificateReport['Quantityinkgs'] = number_format($NetWgtKgs, 3);
            $ArrayQCertificateReport['FobValueUSD'] =number_format($FobValueUSD,2);
            $ArrayQCertificateReport['FobValueINR'] = number_format($FobValueINR,2);
            $ArrayQCertificateReport['PortOfDischarge'] = $RowQCertificateReport['PortOfDischarge'];
            $ArrayQCertificateReport['FinalDestination'] = $RowQCertificateReport['FinalDestination'];
           $ArrayQCertificateReport['PortofLoading'] = $PortofLoadingCountry;

            $TotalNetWeightKgs += $NetWgtKgs;
            $TotalNetWeightLbs += $NetWgtLbs;
            $TotalFobValueINR += $FobValueINR;
            $TotalFOBValueUSD += $FobValueUSD;

            $ArrayQCertificateReportData[] = $ArrayQCertificateReport;
            


        }
        $ArrayTotals['TotalInvoices'] = $i;
        $ArrayTotals['TotalNetWeightKgs'] = number_format($TotalNetWeightKgs,3);
        $ArrayTotals['TotalNetWeightLbs'] = number_format($TotalNetWeightLbs,3);
        $ArrayTotals['TotalFobValueINR'] = number_format($TotalFobValueINR,2);
        $ArrayTotals['TotalFOBValueUSD'] = number_format($TotalFOBValueUSD,2);
        /* if (count($AryCustomData) < $RecordsPerPage) {
             $RecordsAval = 0;
         } else {
             $RecordsAval = 1; 
         }*/
        $data['Totals'] = $ArrayTotals;
        $data['QCertificateReportData'] = $ArrayQCertificateReportData;

        /* $data['Error'] = null;
         $data['Msg'] = "Success";
     } else {
         $data['Error'] = null;
         $data['Msg'] = "Please Select Date";
     }*/

        //$data['RecordsAval'] = $RecordsAval;
        return $data;
    }

}
?>