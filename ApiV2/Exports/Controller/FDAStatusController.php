<?php

include_once '../Global/Util.php';
class FDAStatusController
{
    private $conn;
    private $db_table = "ScrmFdaDetails";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function GetFDAStatusDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $Year = $RequestBody['Year'];
        $ExporterID = $RequestBody['ExporterID'];  
        $ConsigneeID = $RequestBody['ConsigneeID'];
        $CountryID = $RequestBody['CountryID'];
        $InvType = $RequestBody['InvType'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $SearchKey = $RequestBody['SearchKey'];
        
       
        $Query = '';
        $IQuery = '';

        if ($ExporterID != "") {
            $Query .= 'and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($ConsigneeID != "") {
            $Query .= ' and POD.ConsigneeID=' . $ConsigneeID;
        }

        if ($CountryID != "") {
            $Query .= ' and POD.DestinationCountry=' . $CountryID;
        }
        $DateStart=$Year.'-04-01';
        $DateEnd=($Year+1).'-03-31';
        
        $DateFilter= " and SCI.CInvDate between '$DateStart' and '$DateEnd'";
        if ($InvType == "0") {
			$Query .= " and ( POD.PoID not in  (SELECT PIID FROM `ScrmISFDetails`) or POD.PoID not in (SELECT PIID FROM `ScrmISFDetails` where EmptyReturn!=''))";
		} else if ($InvType == "1") {
			$Query .= " and   POD.PoID  in (SELECT PIID FROM `ScrmISFDetails` where EmptyReturn!='') AND POD.Status != 4 ";
		} else if ($InvType == 4) {
            $Query .= " And POD.Status=4";
        }


        $RecordsPerPage=100;

        $LimitMax=($PageNo-1)*$RecordsPerPage;
        $Limit='LIMIT '.$RecordsPerPage.' OFFSET '.$LimitMax;

        if($LoadAll==1){
            $Limit='';
        }
        
       if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', ConsigneeName,PoNo,InvNumFull,CustomsInvNo) LIKE '%$SearchKey%'";
        } 

      /*  if ($Page == 0 || $Page == '') {
            $Limit = 'limit 0 , ' . $RecordsPerPage;
        } else {
            $Start = $Page * $RecordsPerPage;
            $End = $Start + $RecordsPerPage;
            $Limit = 'limit ' . $Start . ',' . $RecordsPerPage;
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ConsigneeName,PoNo,InvNumFull,ExpApprovalNo) LIKE '%$SearchKey%'";
        } */
       
        $SqlFDACount = "SELECT count(*) as Total FROM ScrmPoData as POD 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        left join ScrmPortCities as SPC on POD.PoID=SPC.PortId
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
        left join ScrmISFDetails  ISF on ISF.PIID=POD.PoID 
        left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID 
         where POD.PoID  in (SELECT PiID FROM `ScrmCustomInvoices`) $DateFilter
         $Query"; 
         
            
            $stmt = $this->conn->prepare($SqlFDACount);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $TotalCount=$dataRow['Total'];
            $PageCount=ceil($TotalCount/$RecordsPerPage);

     
        
        $SqlFieldsList = "
		POD.PoID,
		SCD.ConsigneeName,
		POD.PoNo,	
        SCI.CustomsInvNo as CInvNo,
        ISF.IsfID,
        SCOM.ShippingBillNo,FDAContainerStatus";

          $SqlFDAQuery = "SELECT $SqlFieldsList FROM  ScrmPoData as POD 
        left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID 
        left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID
        left join ScrmShippingBillDetails as SCOM on POD.PoID=SCOM.InvID
        left join ScrmISFDetails  ISF on ISF.PIID=POD.PoID 
        where POD.PoID  in (SELECT PiID FROM `ScrmCustomInvoices`) $DateFilter
         $Query order by SCI.CInvDate desc,SCI.CInvNo desc  $Limit";


        $stmt = $this->conn->prepare($SqlFDAQuery);
        $stmt->execute();

        $AryFDAData = [];
        $i = $LimitMax;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($dataRow['FDAContainerStatus'] != '') {
                $PoStatus = $dataRow['FDAContainerStatus'];
                $CLR = 'red';
            } 
            if($InvType==0){
                $PoStatus='Pending';
                $CLR='red';
            }else if($InvType==1){
                $PoStatus='Completed';
                $CLR='green';
            }
            
            // else if($InvType==4) {
            //     $PoStatus='Rejected';
            //     $CLR='black';
            // }
            $ArrayFDADetails['SNO'] = ++$i;
            $ArrayFDADetails['ID'] = $dataRow['PoID'];
            $ArrayFDADetails['InvNo'] = $dataRow['CInvNo'];
            $ArrayFDADetails['PoNo'] = $dataRow['PoNo'];
            $ArrayFDADetails['Buyer'] = $dataRow['ConsigneeName'];		
			$ArrayFDADetails['ShippingBillNo'] = $dataRow['ShippingBillNo'];
            $ArrayFDADetails['Status'] = $PoStatus;
            $ArrayFDADetails['Color'] = $CLR;
            $AryFDAData[] = $ArrayFDADetails; 

        }
        if (count($AryFDAData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }
         $aryActionLinks=$this->getActionLinks();
        
        $data['TotalCount']=$TotalCount ;
		$data['RemainingCount']="";
		$data['EditLink']= $aryActionLinks['EditLink'];
		$data['PrintLink']=$aryActionLinks['PrintLink'];
        $data['Data'] = $AryFDAData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        
        return $data;
    }
    function getActionLinks(){
        $PrintLink='Reports/DocFDAStatusPrint.php?id=';
       
        try {  
         $SqlFDAQuery = "SELECT * FROM `ScrmReports` where ReportCode='PI'"; 
          $stmt = $this->conn->prepare($SqlFDAQuery);
          $stmt->execute();
            if($stmt != FALSE){
                $RowReport = $stmt->fetch(PDO::FETCH_ASSOC);
                $PrintLink=$RowReport['Path'].$RowReport['PrintLink'].$RowReport['QueryID'].'=';
            }
            }catch(Exception $e){
            }    
            if($PrintLink==''){
                $PrintLink='Reports/DocFDAStatusPrint.php?id=';
            }

            $aryActionLinks['EditLink']='AddFDADetails.php?PoID=';
            $aryActionLinks['PrintLink']=$PrintLink;
            return $aryActionLinks;
        }        
}
?>

