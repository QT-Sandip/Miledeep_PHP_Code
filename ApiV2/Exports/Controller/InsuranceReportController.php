<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class InsuranceReportController
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
    public function GetInsuranceReportDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $Year = $RequestBody['Year'];
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $ExporterID = $RequestBody['ExporterID'];
        $CountryID = $RequestBody['CountryID'];
        $Hide10PercentColumn = !empty($RequestBody['Hide10PercentColumn'])?$RequestBody['Hide10PercentColumn']:'';
        $Query = '';


        if ($ExporterID != "") {
            $Query .= ' and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($ConsigneeID != "") {
            $Query .= ' and POD.ConsigneeID=' . $ConsigneeID;
        }

        if ($CountryID != "") {
            $Query .= ' and POD.DestinationCountry=' . $CountryID;
        }

        $DateStart = $Year . '-04-01';
        $DateEnd = ($Year + 1) . '-03-31';

        $DateFilter = " and SCI.CInvDate between '$DateStart' and '$DateEnd'";
        $InsuranceDateFilter = " where SIP.PaymentDate between '$DateStart' and '$DateEnd'"; //uma 23-05-2024

        $DocType = '';


        $SqlFieldsList = 'SIP.InsuranceID,
        SIP.PaymentFullNo as PaymentNo,        
        SIP.OurReferenceNo,
        SIP.PaymentDate,SIP.PostedDateTime,
        SIP.Remarks,
        SIP.PaymentInsExchangeRate,
        SID.PoIDs,SID.InsuranceFullNo,SID.InsuranceTypePercentage,SID.ExcessValue ';


        //$SqlInsuranceIDs = "SELECT $SqlFieldsList  FROM `ScrmInsurancePayments` where InsurancePaymentID='$InsurancePaymentID'";
        //   echo  $SqlInsuranceIDs = "SELECT $SqlFieldsList  FROM `ScrmInsurancePayments` as SIP 
        //             left join ScrmInsuranceData as SID on SIP.InsuranceID=SID.InsuranceID ";  exit; 



        /* $SqlInsuranceIDs = "SELECT $SqlFieldsList  FROM `ScrmInsurancePayments` as SIP 

                left join ScrmInsuranceData as SID on SIP.InsuranceID=SID.InsuranceID $InsuranceDateFilter"; */

            $SqlInsuranceIDs = "SELECT $SqlFieldsList  FROM `ScrmInsurancePayments` as SIP 

                        left join ScrmInsuranceData as SID on SIP.InsuranceID=SID.InsuranceID 

                        LEFT JOIN ScrmCustomInvoices SCI ON FIND_IN_SET(SCI.PiID,SID.PoIDs)>0

                        LEFT JOIN ScrmPoData AS POD ON SCI.PiID=POD.PoID

                        WHERE 1 $DateFilter $Query  AND POD.Status NOT IN (2,3,4,7)

                        GROUP BY SIP.InsuranceID";

        $StmtInsuranceIDs = $this->conn->prepare($SqlInsuranceIDs);
        $StmtInsuranceIDs->execute();

        unset($ArrayPoIDs);
        $TotalPremiumValue = 0;
        $TotalInvoiceValuewithPercentage = 0;
        $TotalInvoiceValue = 0;


        while ($RowInsuranceIDs = $StmtInsuranceIDs->fetch(PDO::FETCH_ASSOC)) {



            $PoIDs = $RowInsuranceIDs['PoIDs'];
            $ExcessValue = explode(',', $RowInsuranceIDs['ExcessValue']);


            $SqlFields = 'POD.PoID,
                SCI.CustomsInvNo,
                SCI.ContainerNo,        
                SIFD.ShippingOnboardDt,
                SPCL.Name as POL,
                SPCD.Name as POD,
                SCD.name as DestinationCountry,
                SCL.name as PortofLoadingCountry,
                POD.ExporterID,
                POD.ProcessorID,
                POD.PoNO,
                SCI.CInvDate
                ';

            $SqlPoDetails = "SELECT $SqlFields FROM ScrmCustomInvoices as SCI 
                    left join ScrmPoData as POD on SCI.PiID=POD.PoID
                    left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID 
                    left join ScrmPortCities as SPCL on POD.PortofLoading=SPCL.PortId 
                    left join ScrmPortCities as SPCD on POD.PortOfDischarge=SPCD.PortId 
                    left join ScrmCountriesList as SCD on POD.DestinationCountry=SCD.id 
                    left join ScrmCountriesList as SCL on SPCL.Country=SCL.id 
                    WHERE POD.PoID in ($PoIDs) $DateFilter $Query and POD.Status not in (2,3,4,7) order by SCI.CInvNo desc";
            $StmtPoDetails = $this->conn->prepare($SqlPoDetails);


            $StmtPoDetails->execute();

            $row_count = $StmtPoDetails->rowCount();

            $index = 0;
            //$i = 0;
            if ($row_count != 0) {
                while ($RowPoDetails = $StmtPoDetails->fetch(PDO::FETCH_ASSOC)) {
                    $PoID = $RowPoDetails['PoID'];
                    $POL = $RowPoDetails['POL'];
                    $POD = $RowPoDetails['POD'];
                    $DestinationCountry = $RowPoDetails['DestinationCountry'];
                    $PortofDischarge = $POD . ',' . $DestinationCountry;
                    $PaymentInsExchangeRate = $RowInsuranceIDs['PaymentInsExchangeRate'];

                    $PaymentInsExchangeRate = $PaymentInsExchangeRate ? $PaymentInsExchangeRate : 0;

                    $SqlOrgVessel = "SELECT VesselName,VOYNO FROM `ScrmTranshipmentPortsList`  WHERE 
                                     FromTOPID='$POL' and PIID='$PoID'";

                    $StmtOrgVessel = $this->conn->prepare($SqlOrgVessel);

                    $StmtOrgVessel->execute();
                    $RowOrgVessel = $StmtOrgVessel->fetch(PDO::FETCH_ASSOC);

                    $OrgVesselName = $RowOrgVessel['VesselName'];
                    $OrgVOYNO = $RowOrgVessel['VOYNO'];
                    $VesselName = $OrgVesselName . '  ' . $OrgVOYNO;
                    $ArrayPaymentValues = $this->utilController->getPaymentValues($PoID, $DocType);
                    $InvoiceValue = $ArrayPaymentValues['InvValueUSD'];
                    $InsuranceTypePercentage = $RowInsuranceIDs['InsuranceTypePercentage'];
                    // $InvoiceValuewithPercentage = $ExcessValue[$index];
                    $InvoiceValuewithPercentage = (($InvoiceValue * 10) / 100) + $InvoiceValue;

                     $dateFilter = $RowPoDetails['CInvDate'];
                    //$dateFilter = !empty($RowInsuranceIDs['PaymentDate'])?$RowInsuranceIDs['PaymentDate']:$RowInsuranceIDs['PostedDateTime'];
				    //$year = date('Y', strtotime($dateFilter));
                    if(!empty($Hide10PercentColumn) && $Hide10PercentColumn != '0000-00-00' && $dateFilter >= $Hide10PercentColumn){
                        $PremiumValue = ($InvoiceValue * $InsuranceTypePercentage) / 100;
                    }else{
                        $PremiumValue = ($InvoiceValuewithPercentage * $InsuranceTypePercentage) / 100;
                    }

                    $AmountRs = ($PremiumValue * $PaymentInsExchangeRate);

                    $TotalInvoiceValue += $InvoiceValue;
                    $TotalPremiumValue += $PremiumValue;
                    $TotalInvoiceValuewithPercentage += $InvoiceValuewithPercentage;



                    $AryResponse['SNo'] = ++$i;
                    $AryResponse['CoverGiven'] = $RowInsuranceIDs['InsuranceFullNo'];
                    $AryResponse['InvoiceNo'] = $RowPoDetails['CustomsInvNo'];
                    $AryResponse['ShippedonVessel'] = $VesselName;
                    $AryResponse['ContainerNo'] = $RowPoDetails['ContainerNo'];
                    $AryResponse['PortofDischarge'] = $PortofDischarge;
                    $AryResponse['InvoiceValue'] = number_format($InvoiceValue, 2);
                    $AryResponse['InvoiceValuewithPercentage'] = number_format($InvoiceValuewithPercentage, 2);
                    $AryResponse['Percentage'] = $InsuranceTypePercentage;
                    $AryResponse['Premiums'] = number_format($PremiumValue, 2);
                    $AryResponse['ExchangeRate'] = number_format($PaymentInsExchangeRate, 2);
                    $AryResponse['AmountRs'] = number_format($AmountRs, 2);
                    $AryResponse['OurReferenceNo'] = $RowInsuranceIDs['OurReferenceNo'];
                    $AryResponse['PaymentDate'] = $this->utilController->getFormatedDatelines($RowInsuranceIDs['PaymentDate']);
                    $AryResponse['CInvDate'] = $RowPoDetails['CInvDate'];



                    $ArrayResponse[] = $AryResponse;
                    $index++;
                }
            }

            // else ($row_count) { // here! as simple as that
            //     //echo 'No data found';
            // }



        }
        // if ($row_count!=0) {
        $ArrayTotals['TotalInvoices'] = $i;
        $ArrayTotals['TotalInvoiceValue'] = number_format($TotalInvoiceValue, 2);
        $ArrayTotals['TotalInvoiceValuewithPercentage'] = number_format($TotalInvoiceValuewithPercentage, 2);
        $ArrayTotals['TotalPremiums'] = number_format($TotalPremiumValue, 2);
        if (count($ArrayResponse) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Data'] = $ArrayResponse;
        $data['Totals'] = $ArrayTotals;
        $data['RecordsAval'] = $RecordsAval;
        // }
        // else{
        //     $data['Data'] = "No data Available";
        // }
        return $data;
    }
}
