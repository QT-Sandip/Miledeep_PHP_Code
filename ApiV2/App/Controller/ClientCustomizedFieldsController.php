<?php

include_once '../Global/Util.php';
include_once '../Config/Token.php';
class ClientCustomizedFieldsController
{

    private $conn;
    private $db_table = "ScrmClientInputFields";

    public $id;
    public $name;
    
    // Db connection
    public function __construct($db)
    {
        //$this->conn = $db;
        $database = new Database(); 
        $this->restApi = new REST(); 
        $this->conn =$database->getAuthConnection();
    }
   
    public function getClientCustomizedFieldsList($RequestBody)
    {

        $authToken = $this->restApi->getRequestHeader();
       $payload = Token::Verify($authToken);
       //var_dump($payload); exit;
       $ClientID=$payload['ClientID'];
       $DeptID=$payload['DeptID'];
        

        //$sqlQuery="SELECT FieldID FROM `ScrmClientInputFields` WHERE ClientID='$ClientID' and DeptID in ($DeptID)and Status='1'";
        $sqlQuery="SELECT FieldID FROM `ScrmInputFields` WHERE ClientID='$ClientID' and DeptID in ($DeptID)and Status='1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC); 
        $FieldID=$dataRow['FieldID'];

     // $sqlDataQuery="SELECT * FROM `ScrmInputFields` WHERE ID in ($FieldID)"; 
      $sqlDataQuery="SELECT * FROM `ScrmInputFieldsMaster` WHERE ID in ($FieldID)"; 
      $stmtDataQuery = $this->conn->prepare($sqlDataQuery);
     $stmtDataQuery->execute();
   
        while( $dataRowInputFields = $stmtDataQuery->fetch(PDO::FETCH_ASSOC)){
            $ArrayCustomizedInputFields['InputType'] = $dataRowInputFields['InputType'];
            $ArrayCustomizedInputFields['Length'] = $dataRowInputFields['Length'];
            $ArrayCustomizedInputFields['DbTable'] = $dataRowInputFields['DbTable'];
            $ArrayCustomizedInputFields['Stage'] = $dataRowInputFields['Stage'];
            $ArrayCustomizedInputFields['Label'] = $dataRowInputFields['Label'];
            $ArrayCustomizedInputFields['DbTable'] = $dataRowInputFields['DbTable'];
            $ArrayCustomizedInputFields['ColumnName'] = $dataRowInputFields['ColumnName'];
            $ArrayCustomizedInputFields['PageLink'] = $dataRowInputFields['PageLink'];

            $AryCustomizedInputFields[] = $ArrayCustomizedInputFields;
      } 
      if($stmtDataQuery){
      $data['Error'] = null;
      $data['Msg'] = 'Success';
      $data['Data'] = $AryCustomizedInputFields;
       return $data;
    }

    else{
        $data['Error'] = null; 
        $data['Msg'] = 'Data not found';
    }
}
}
?>