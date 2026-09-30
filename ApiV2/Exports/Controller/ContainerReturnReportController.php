<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class ContainerReturnReportController
{
    private $conn;
    private $db_table = "";
    private $utilController;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function ContainerReturnReportData($RequestBody)
    {
        $RecordsPerPage = 100;
        $Year = $RequestBody['Year'];
        // $ExporterID = $RequestBody['Exporter'];
        $ExporterID = $RequestBody['ExporterID'];
        $CountryID = $RequestBody['CountryID'];
        $ConsigneeID = $RequestBody['ConsigneeID'];

        $DateStart = $Year . '-04-01';
        $DateEnd = ($Year + 1) . '-03-31';
        $DateFilter = "between '$DateStart' and '$DateEnd'";

        $Query = '';
        if ($ExporterID != "") {
            $Query .= ' and SPD.ExporterID in (' . $ExporterID . ')';
        }
        if ($ConsigneeID != "") {
            $Query .= ' and SPD.ConsigneeID=' . $ConsigneeID;
        }

        if ($CountryID != "") {
            $Query .= ' and SPD.DestinationCountry=' . $CountryID;
        }

        $DocType = 'CMI';

        $SqlFields = "SCI.CustomsInvNo,SCI.CInvDate,QtyUnit,PoNo,PODate,ConsigneeName,DeliveryTerms,ShippingTerms,SPC.Name as PortofLoading,StockOutDate,
        SPFD.Name as DischargePortName,SPF.Name as PortofFinalDestination,ShippingOnboardDt,ShippingBillNo,ShippingBillDate,BLDate,BLNo,ISF.ContainerNo,SSBD.ExchangeRate as ShippingBillExchangeRate,
        SPCL.name as PortofLoadingCountry,SPFDC.name as PortofDischargeCountry,
        SPT.PaymentName as PoPaymentTerm,SCPT.PaymentName as CommercialPaymentTerm,
        SCL.name as DestinationCountry,SCI.PiID,
        ReturnContainerStockReceivedDate,ReturnBLNo,ReturnContainerNo,ReturnContainerNetWeightKgs,
        SPFR.Name as ContainerReturnPort,SPFRC.name as ContainerReturnPortCountry,
        BillofEntryNo,BillofEntryDate,SCRD.Remarks AS ReturnRemarks,FDAContainerStatus";


        $SqlContainerReturnQuery = "SELECT $SqlFields
        FROM ScrmCustomInvoices as  SCI 
        left join ScrmPoData as SPD  on SPD.PoID=SCI.PiID
        LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
        LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortofLoading 
        LEFT JOIN ScrmCountriesList as SPCL on SPCL.id=SPC.Country 
        LEFT JOIN ScrmPortCities as SPF on SPF.PortId=SPD.FinalDestination 
        LEFT JOIN ScrmPortCities as SPFD on SPFD.PortId=SPD.PortOfDischarge 
        left join ScrmCountriesList  SCL on SCL.id=SPD.DestinationCountry
        left join ScrmCountriesList  SPFDC on SPFDC.id=SPFD.Country
        left join ScrmISFDetails  ISF on ISF.PIID=SPD.PoID
        left join ScrmShippingBillDetails  SSBD on SSBD.InvID=SPD.PoID
        left join ScrmCommercialData  SCOD on SCOD.PoID=SPD.PoID
        left join ScrmPaymentTerms  SPT on SPT.ID=SPD.PaymentTerms
        left join ScrmPaymentTerms  SCPT on SCPT.ID=SCOD.ComPaymentTerms
        left join ScrmCotainerReturnDetails  SCRD on SCRD.PoID=SCI.PiID
        LEFT JOIN ScrmPortCities as SPFR on SPFR.PortId=SCRD.ContainerReturnPort 
        left join ScrmCountriesList  SPFRC on SPFRC.id=SPFR.Country
        WHERE  SCI.CInvDate $DateFilter and SPD.Status = 4 $Query ";

        $stmtContainerReturn = $this->conn->prepare($SqlContainerReturnQuery);
        $stmtContainerReturn->execute();


        $i = '0';
        $TotalNetWeightKgs = 0;
        $TotalNetWeightLbs = 0;
        $TotalInvoiceValueUSD = 0;
        $TotalFOBValueUSD = 0;
        $TotalInvoices = 0;
        while ($RowContainerReturnData = $stmtContainerReturn->fetch(PDO::FETCH_ASSOC)) {
            $ArrayContainerReturnReportData = array();

            $InvNo = $RowContainerReturnData['CustomsInvNo'];
            $InvDate = $this->utilController->getFormatedDatelines($RowContainerReturnData['CInvDate']);
            $PODate = $this->utilController->getFormatedDatelines($RowContainerReturnData['PODate']);
            $ShippingOnboardDt = $this->utilController->getFormatedDatelines($RowContainerReturnData['ShippingOnboardDt']);
            $ShippingBillDate = $this->utilController->getFormatedDatelines($RowContainerReturnData['ShippingBillDate']);
            $StockOutDate = $this->utilController->getFormatedDatelines($RowContainerReturnData['StockOutDate']);
            $BLDate = $this->utilController->getFormatedDatelines($RowContainerReturnData['BLDate']);
            $PoID = $RowContainerReturnData['PiID'];
            $SBFOBUSD = $RowContainerReturnData['SBFOBUSD'];
            $ShippingBillExchangeRate = $RowContainerReturnData['ShippingBillExchangeRate'];
            $ArrayPaymentValues = $this->utilController->getPaymentValues($PoID, $DocType);
            //$ArrayPaymentValues=$this->getPaymentValues($PoID);
            $InvValueUSD = $ArrayPaymentValues['InvValueUSD'];
            $FOBValueUSD = $ArrayPaymentValues['FOBValueUSD'];
            $FrieghtCharge = $ArrayPaymentValues['FrieghtCharge'];

            if ($RowContainerReturnData['CommercialPaymentTerm'] == '') {
                $PaymentName = $RowContainerReturnData['PoPaymentTerm'];
            } else {
                $PaymentName = $RowContainerReturnData['CommercialPaymentTerm'];
            }
            $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
            sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
            sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
            sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


            $SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt,
            sum(Price) as Price,sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
            sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` 
            as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";

            $StmtTotal = $this->conn->prepare($SqlTotal);
            $StmtTotal->execute();

            $RowTotal = $StmtTotal->fetch(PDO::FETCH_ASSOC);
            $NetWgtLbs = $RowTotal['NetWgtLbs'];

            if ($RowContainerReturnData['QtyUnit'] == 'KGS') {

                $NetWgtKgs = $RowTotal['NetWgtKgs'];
            } else {

                $NetWgtKgs = $RowTotal['NetWgtLbs'] * 0.454;
            }

            $ArrayContainerReturnReportData['sno'] = ++$i;
            $ArrayContainerReturnReportData['InvNo'] = $InvNo;
            $ArrayContainerReturnReportData['InvoiceDate'] = $InvDate;
            $ArrayContainerReturnReportData['PoNo'] = $RowContainerReturnData['PoNo'];
            $ArrayContainerReturnReportData['PoDate'] = $PODate;
            $ArrayContainerReturnReportData['StockOutDate'] = $StockOutDate;
            $ArrayContainerReturnReportData['Consignee'] = $RowContainerReturnData['ConsigneeName'];
            $ArrayContainerReturnReportData['CYSD'] = $RowContainerReturnData['DeliveryTerms'];
            $ArrayContainerReturnReportData['TermsofDelivery'] = $RowContainerReturnData['ShippingTerms'];
            $ArrayContainerReturnReportData['TermsofPayment'] = $PaymentName;
            $ArrayContainerReturnReportData['PortofLoading'] = $RowContainerReturnData['PortofLoading'] . ' , ' . $RowContainerReturnData['PortofLoadingCountry'];
            $ArrayContainerReturnReportData['Country'] = $RowContainerReturnData['DestinationCountry'];
            $ArrayContainerReturnReportData['PortOfDischarge'] = $RowContainerReturnData['DischargePortName'] . ' , ' . $RowContainerReturnData['PortofDischargeCountry'];
            $ArrayContainerReturnReportData['FinalDestination'] = $RowContainerReturnData['PortofFinalDestination'];
            $ArrayContainerReturnReportData['OnBoardDate'] = $ShippingOnboardDt;
            $ArrayContainerReturnReportData['ShippingBillNo'] = $RowContainerReturnData['ShippingBillNo'];
            $ArrayContainerReturnReportData['ShippingBillDate'] = $ShippingBillDate;
            $ArrayContainerReturnReportData['ShippingBillExchangeRate'] = $ShippingBillExchangeRate;
            $ArrayContainerReturnReportData['BLNo'] = $RowContainerReturnData['BLNo'];
            $ArrayContainerReturnReportData['BLDate'] = $BLDate;
            $ArrayContainerReturnReportData['Containerno'] = $RowContainerReturnData['ContainerNo'];
            $ArrayContainerReturnReportData['QuantityNetWeightKgs'] = number_format($NetWgtKgs, 2);
            $ArrayContainerReturnReportData['QuantityNetWeightLbs'] = number_format($NetWgtLbs, 2);
            $ArrayContainerReturnReportData['InvoiceValueUSD'] = number_format($InvValueUSD, 2);
            $ArrayContainerReturnReportData['FOBValue'] = number_format($FOBValueUSD, 2);
            $ArrayContainerReturnReportData['Freight'] = number_format($FrieghtCharge, 2);
            $ArrayContainerReturnReportData['ReturnContainerStockReceivedDate'] = $this->utilController->getFormatedDatelines($RowContainerReturnData['ReturnContainerStockReceivedDate']);
            $ArrayContainerReturnReportData['ReturnBLNo'] = $RowContainerReturnData['ReturnBLNo'];
            $ArrayContainerReturnReportData['ReturnContainerNo'] = $RowContainerReturnData['ReturnContainerNo'];
            $ArrayContainerReturnReportData['ReturnContainerNetWeightKgs'] = $RowContainerReturnData['ReturnContainerNetWeightKgs'] ? number_format($RowContainerReturnData['ReturnContainerNetWeightKgs'], 2) : '';
            if ($RowContainerReturnData['ContainerReturnPort'] != '' && $RowContainerReturnData['ContainerReturnPortCountry'] != '') {
                $ArrayContainerReturnReportData['ContainerReturnPort'] = $RowContainerReturnData['ContainerReturnPort'] . ', ' . $RowContainerReturnData['ContainerReturnPortCountry'];
            }
            $ArrayContainerReturnReportData['BillofEntryNo'] = $RowContainerReturnData['BillofEntryNo'];
            $ArrayContainerReturnReportData['BillofEntryDate'] = $this->utilController->getFormatedDatelines($RowContainerReturnData['BillofEntryDate']);
            $ArrayContainerReturnReportData['FDAContainerStatus'] = $RowContainerReturnData['FDAContainerStatus'];
            $ArrayContainerReturnReportData['ReturnRemarks'] = $RowContainerReturnData['ReturnRemarks'];

            $TotalNetWeightKgs += $NetWgtKgs;
            $TotalNetWeightLbs += $NetWgtLbs;
            $TotalInvoiceValueUSD += $InvValueUSD;
            $TotalFOBValueUSD += $FOBValueUSD;


            $AryContainerReturnData[] = $ArrayContainerReturnReportData;
        }
        // }
        $ArrayTotals['TotalInvoices'] = $i;
        $ArrayTotals['TotalNetWeightKgs'] = number_format($TotalNetWeightKgs, 2);
        $ArrayTotals['TotalNetWeightLbs'] = number_format($TotalNetWeightLbs, 2);
        $ArrayTotals['TotalInvoiceValueUSD'] = number_format($TotalInvoiceValueUSD, 2);
        $ArrayTotals['TotalFOBValueUSD'] = number_format($TotalFOBValueUSD, 2);

        if (count($AryContainerReturnData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
        $data['Totals'] = $ArrayTotals;
        $data['RecordsAval'] = $RecordsAval;
        $data['ContainerReturnData'] = $AryContainerReturnData;


        return $data;
    }
}
