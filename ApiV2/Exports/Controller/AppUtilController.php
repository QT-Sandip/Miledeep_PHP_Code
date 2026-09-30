<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include_once '../Global/Util.php';
include_once '../Config/Config.php';
include_once '../Config/Rest.php';
include_once '../Config/Token.php';

class AppUtilController
{

  //  
    private $database;
    private $restApi;
    private $conn;
 
    // Db connection
    public function __construct($db)
    {
        $database = new Database(); 
        $this->restApi = new REST(); 
        $this->conn =$database->getAuthConnection();
    }


 
 
    public function getItemTableLables($requestBody)
    {

        $authToken = $this->restApi->getRequestHeader();
        $payload = Token::Verify($authToken);
        $ClientID=$payload['ClientID'];
        $DocType=$requestBody['DocType'];
        $Buyer=$requestBody['Buyer'];
        $Country=$requestBody['Country'];

        if($ClientID==''){
            $Qry .= "and ClientID in ('0')";
        }else{
            // $Qry .= "and ClientID in ('$ClientID')";
            $Qry .= " and FIND_IN_SET('$ClientID', ClientID)";  //uma
        }

        if($DocType==''){
            $Qry .= "and DocType in ('0')"; 
        }else{
            $Qry .= "and DocType in ('$DocType')"; 
        } 

        if($Buyer==''){
            $Qry .= "and BuyerID in (0)";
        }else{
            $Qry .= "and BuyerID in (0,'$Buyer')"; 
        }

        if($Country==''){
            $Qry .= "and CountryID in (0)";
        }else{
            $Qry .= "and CountryID in (0,'$Country')"; 
        }


           // $sqlQuery = "SELECT * FROM `ScrmInvItemListLabels` WHERE Status=1 $Qry order by SortOrder asc";
            $sqlQuery = "SELECT FieldID FROM `ScrmInvoiceItemLabels` WHERE Status=1 $Qry order by  BuyerID desc ,CountryID desc limit 1";
            
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
     if($stmt->rowCount()==0){
       // $sqlQuery = "SELECT * FROM `ScrmInvoiceLabels ` WHERE Status=1 and DefaultKeys=1 order by SortOrder asc";
        $sqlQuery = "SELECT FieldID FROM `ScrmInvoiceItemLabels` WHERE Status=1  $Qry  order by BuyerID desc ,CountryID desc limit 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();   
    }
    $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $FieldID=$dataRow['FieldID'];

    $sqlItemLabelsQuery = "SELECT * FROM `ScrmInvoiceItemLabelsMaster` WHERE ItemLabelID in ($FieldID) and Status=1 order by  Field(ItemLabelID,$FieldID)";
    $stmtItemLabels = $this->conn->prepare($sqlItemLabelsQuery);
    $stmtItemLabels->execute(); 
    
 
        while($dataRowItemLabels = $stmtItemLabels->fetch(PDO::FETCH_ASSOC)){
            if(trim(($dataRowItemLabels['ExtKey']))!=''){
                $ext= $this->getExt($requestBody,$dataRowItemLabels['ExtKey']);
            }else{
                $ext='';
                        }
            
        
        $arykeys[] = $dataRowItemLabels['Key'];
        $aryLables[$dataRowItemLabels['Key']] = $dataRowItemLabels['Value'] .' '.$ext;
       
        
    }
    $ItemData['TableKeys']=$arykeys;
    $ItemData['TableLables']=$aryLables;
        return $ItemData;
    }  


    function getExt($requestBody,$Key){
       
       return isset($requestBody[$Key])?$requestBody[$Key] :'';


  
    }
    


}

?> 