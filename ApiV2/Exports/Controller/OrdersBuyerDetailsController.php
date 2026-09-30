<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class OrdersBuyerDetailsController
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

    function getOrderBuyersDetails($RequestBody)
    {
        // $RecordsPerPage = 10;
        $ExpID = $RequestBody['ExpID'];
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $CountryID = $RequestBody['CountryID'];
        $Brand = $RequestBody['Brand'];
        $Month = $RequestBody['Month'];
        $FinClsYear = $RequestBody['Year'];
        $Processor = $RequestBody['Processor'];
        $ReqType = $RequestBody['ReqType'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DashBoard = $RequestBody['DashBoard'];
        

        $DateFilter = prepareDateFilter($FinClsYear, $Month);
        if($DashBoard){
            if($FinClsYear != ''){
				$FromDate = $FinClsYear.'-04-01';
				$ToDate = ($FinClsYear+1) .'-03-31';
			}
            $DateFilter = " BETWEEN '$FromDate' and '$ToDate'";
        }
        $Query = '';
        $PendingQuery = '';
        $PendingJoins = '';

        if ($ExpID != '') {
            // Add condition for ExporterID
            $Query .= " SPD.ExporterID = '$ExpID'";
        }

        if ($ConsigneeID != '') {
            if ($Query != '') {
                $Query .= " AND ";
            }
            // Add condition for ConsigneeID
            $Query .= " SPD.ConsigneeID = '$ConsigneeID'";
        }

        if ($CountryID != '') {
            if ($Query != '') {
                $Query .= " AND ";
            }
            // Add condition for DestinationCountry
            $Query .= " SPD.DestinationCountry = '$CountryID'";
        }

        if ($Brand != '') {
            if ($Query != '') {
                $Query .= " AND ";
            }
            // Add condition to check Brand in ScrmPoGradeData
            $Query .= " SPD.PoID IN (SELECT DISTINCT(InvId) FROM ScrmPoGradeData WHERE Brand = '$Brand')";
        }

        if ($Processor != '') {
            if ($Query != '') {
                $Query .= " AND ";
            }
            // Add condition for ProcessorID
            $Query .= " SPD.ProcessorID = '$Processor'";
        }

        if ($ReqType != '') {
            if ($ReqType == 'Shipped') {
                if ($Query != '') {
                    $Query .= " AND ";
                }
                // Condition for Shipped (StockOutDate is non-null or non-empty)
                $Query .= "  (SCI.StockOutDate IS NOT NULL AND SCI.StockOutDate != '')";
            } elseif ($ReqType == 'Delivered') {
                if ($Query != '') {
                    $Query .= " AND ";
                }
                // Condition for Delivered (both StockOutDate and EmptyReturn are non-null or non-empty)
                $Query .= "  (SCI.StockOutDate IS NOT NULL AND SCI.StockOutDate != '')";
                $Query .= " AND (ISF.EmptyReturn IS NOT NULL AND ISF.EmptyReturn != '')";
            } elseif ($ReqType == 'Pending' || $ReqType == 'Total') {
                // Clear DateFilter for 'Pending' requests
                $PendingQuery = 'PendingQuery.PiID IS NULL';
                // For Pending, check if PoID is NOT in the list of invoices with StockOutDate
                $PendingJoins .= "LEFT JOIN (
                    SELECT PiID
                    FROM ScrmCustomInvoices
                    WHERE StockOutDate IS NOT NULL AND StockOutDate != ''
                    ) AS PendingQuery ON SPD.PoID = PendingQuery.PiID";
            }

            if ($ReqType == 'Total') {
                $PendingQuery = '(PendingQuery.PiID IS NULL OR ShippedQuery.PiID IS NOT NULL)';
                $PendingJoins .= " LEFT JOIN (
                    SELECT PiID FROM ScrmCustomInvoices WHERE  StockOutDate IS NOT NULL AND StockOutDate != ''
                   AND CInvDate $DateFilter ) AS ShippedQuery ON SPD.PoID = ShippedQuery.PiID";
            }
        }


        $sqlFields = "PoNo,PoID,PoDate,QtyUnit,StockOutDate,EmptyReturn,UnitCurrency";

        // $sqlQuery = "SELECT $sqlFields FROM `ScrmPoData` as SPD
        // LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
        // WHERE  SPD.PoDate $DateFilter  $Query
        // ORDER by PoID ASC"; 

        $sqlQuery = "SELECT $sqlFields FROM ScrmPoData as SPD
      LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
      left join ScrmISFDetails  ISF on ISF.PIID=SPD.PoID
      LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID
      
      WHERE  SPD.PoID in ( select SCI.PiID from  ScrmCustomInvoices as SCI 
                          left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
                          where  SCI.CInvDate  $DateFilter  $Query  and StockOutDate!=''   
                          and (ISF.EmptyReturn is null or  ISF.EmptyReturn=''))
                          or  SPD.PoID in  (select SCI.PiID from  ScrmCustomInvoices as SCI 
                          left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
                          where  SCI.CInvDate $DateFilter $Query  and StockOutDate!=''   and ISF.EmptyReturn!='')
       or PoID NOT in 
              (SELECT PiID FROM ScrmCustomInvoices where StockOutDate!='') $Query and SPD.Status not in (2,3,4)
     
      ORDER by PoID ASC";

        //New code

        $newQuery = '';
        if ($PendingQuery) {
            $newQuery = $PendingQuery;
        } else {
            $newQuery = 'SCI.CInvDate ' . $DateFilter;
        }

        if ($Query != '') {
            $newQuery .= " AND " . $Query;
        }




        // $sqlCount = "SELECT COUNT(PoID) AS Total FROM `ScrmPoData`
        // WHERE SPD.PoDate $DateFilter $Query
        // ORDER by PoID ASC";

        // $stmt = $this->conn->prepare($sqlCount);
        // $stmt->execute();
        // $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        // $TotalCount = $dataRow['Total'];

        // Final SQL query
        $sqlQuery = "SELECT $sqlFields 
            FROM 
            ScrmPoData AS SPD
            LEFT JOIN ScrmConsigneeDetails AS SCD
            ON SPD.ConsigneeID = SCD.ConID
            LEFT JOIN ScrmCustomInvoices AS SCI
            ON SPD.PoID = SCI.PiID
            LEFT JOIN ScrmISFDetails AS ISF
            ON ISF.PIID = SPD.PoID
            $PendingJoins
            WHERE $newQuery 
            AND SPD.Status NOT IN (2, 3, 4)
            ORDER BY SPD.PoID ASC";


        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayOrderBuyersData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayOrderBuyersList['SNO'] = ++$i;
            $ArrayOrderBuyersList['PoID'] = $dataRow['PoID'];
            $ArrayOrderBuyersList['PoNo'] = $dataRow['PoNo'];
            $ArrayOrderBuyersList['PoDate'] = $dataRow['PoDate'];
            $ArrayOrderBuyersList['QtyUnit'] = $dataRow['QtyUnit'];
            $ArrayOrderBuyersList['UnitCurrency'] = $dataRow['UnitCurrency'];
            $StockOutDate = $dataRow['StockOutDate'];
            $EmptyReturn = $dataRow['EmptyReturn'];
            $ArrayOrderBuyersList['StockOutDate'] = $StockOutDate;
            if ($StockOutDate != '' && $EmptyReturn != '') {
                $ArrayOrderBuyersList['Status'] = "Delivered";
            } else if ($StockOutDate != '') {
                $ArrayOrderBuyersList['Status'] = "Shipped";
            } else {
                $ArrayOrderBuyersList['Status'] = "Pending";
            }

            $PoID = $ArrayOrderBuyersList['PoID'];
            $UnitCurrency = $ArrayOrderBuyersList['UnitCurrency'];

            $sqlBreakupFields = "VL.ProductDescription,VL.Species,VL.FreezingType,VL.PreparationType,ShortName as Variety,
            PGD.Grade,Mcs,Brand,APS.PackingStyle,Price,SCM.CurrenySymbol as Symbol,
            sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
            sum(Mcs*TotalSlabs*SlabWeight*Mfactor ) as Kgs,
            sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
            sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs";

            $sqlBreakup = "SELECT $sqlBreakupFields FROM ScrmPoGradeData as PGD
            left join ScrmCurrencyMaster as SCM on SCM.CurrencyCode='$UnitCurrency' 
            LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
            LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
            WHERE  
            InvId='$PoID' GROUP BY PGD.ID";

            $stmtBreakup = $this->conn->prepare($sqlBreakup);
            $stmtBreakup->execute();

            $ArrayBreakupData = array();

            $j = 0;
            while ($dataBreakupRow = $stmtBreakup->fetch(PDO::FETCH_ASSOC)) {

                $Variety = $dataBreakupRow['Species'] . '-' . $dataBreakupRow['Variety'] . '-' . $dataBreakupRow['PreparationType'] . '-' . $dataBreakupRow['FreezingType'];
                $Variety = $dataBreakupRow['ProductDescription'] ? $dataBreakupRow['ProductDescription'] : $Variety;
                $ArrayBreakupList['SNO'] = ++$j;
                $ArrayBreakupList['PoID'] = $PoID;
                $ArrayBreakupList['ProductDescription'] = $dataBreakupRow['ProductDescription'];
                $ArrayBreakupList['Species'] = $dataBreakupRow['Species'];
                $ArrayBreakupList['FreezingType'] = $dataBreakupRow['FreezingType'];
                $ArrayBreakupList['PreparationType'] = $dataBreakupRow['PreparationType'];
                // $ArrayBreakupList['Variety'] = $dataBreakupRow['Variety'];
                $ArrayBreakupList['Variety'] = $Variety;
                $ArrayBreakupList['Grade'] = $dataBreakupRow['Grade'];
                $ArrayBreakupList['Mcs'] = $dataBreakupRow['Mcs'];
                $ArrayBreakupList['Brand'] = $dataBreakupRow['Brand'];
                $ArrayBreakupList['PackingStyle'] = $dataBreakupRow['PackingStyle'];
                $ArrayBreakupList['CurrencySymbol'] = $dataBreakupRow['Symbol'];
                $ArrayBreakupList['Price'] = $dataBreakupRow['Symbol'] . $dataBreakupRow['Price'];
                $ArrayBreakupList['Lbs'] = round($dataBreakupRow['Lbs'], 3);
                $ArrayBreakupList['Kgs'] = $dataBreakupRow['Kgs'];
                $ArrayBreakupList['PriceKgs'] = round($dataBreakupRow['PriceKgs'], 3);
                $ArrayBreakupList['PriceLbs'] = $dataBreakupRow['Symbol'] . round($dataBreakupRow['PriceLbs'], 2);

                $ArrayBreakupData[] = $ArrayBreakupList;
            }

            $ArrayOrderBuyersList['BreakUpData'] = $ArrayBreakupData;
            $ArrayOrderBuyersData[] = $ArrayOrderBuyersList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $i;
        $data['Data'] = $ArrayOrderBuyersData;

        return $data;
    }
}
