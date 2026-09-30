<?php
include_once 'Token.php';
include_once 'Rest.php';
include_once __DIR__.'/../../config/Dotenv.php';

$restApi = new REST();
class Database
{
    private $host;
    private $database_name;
    private $username;
    private $password;

    public function __construct()
    {
        $this->host = (getenv('DB_HOST')) ? getenv('DB_HOST') : $_ENV['DB_HOST'];
        $this->database_name = (getenv('DB_NAME')) ? getenv('DB_NAME') : $_ENV['DB_NAME'];
        $this->username = (getenv('DB_USER')) ? getenv('DB_USER') : $_ENV['DB_USER'];
        $this->password = (getenv('DB_PASSWORD')) ? getenv('DB_PASSWORD') : $_ENV['DB_PASSWORD'];
    }

    // private $host = 'localhost';
    // private $database_name = 'u681530362_serp';
    // private $username = 'u681530362_serp';
    // private $password = '@4pU3XSq@$'; 
    
    // private $host = '193.202.44.54';
    // private $database_name = 'u681530362_serp';
    // private $username = 'u681530362_serp';
    // private $password = '@4pU3XSq@$';  
/*
    private $host = 'localhost';
    private $database_name = 'u177334400_CamarooAdmin';
    private $username = 'u177334400_CamarooAdmin';
    private $password = '0$Kzi|mT1^~>';

    */
    public $conn;
    public function getAuthConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->database_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Database could not be connected: " . $exception->getMessage();
        }
        return $this->conn;
    }


    public function getConnection($authToken)
    {
        global $restApi;
        try {

            $DbDetails = $this->getDbConfig($authToken);
            $this->conn = null;


            if ($DbDetails != null) {



                $host = $DbDetails['AuthDbHost'];
                $database_name = $DbDetails['AuthDbName'];
                $username = $DbDetails['AuthDbUserName'];
                $password = $DbDetails['AuthDbPassword'];
               
             /*   $host = 'localhost';
                $database_name = 'camaroo_dfl';
                $username = 'camaroo_dfl_test_user';
                $password = 'dfl_test_user';
 */

                $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $database_name, $username, $password);
                $this->conn->exec("set names utf8");
            //error_log("@@@@@@@@@@@ : connected");
            //error_log($database_name);                
                return $this->conn;
            } else {
              
               $responseData['Error']='Unauthrized Token';
               $responseData=json_encode($responseData);
               $restApi->response($responseData,401);
            }
        } catch (PDOException $exception) {
 
            $responseData['Error']='Database Connection Error';
            error_log("@@@@@@@@@@@ : Database Connection Error - " . $exception->getMessage());
            $responseData=json_encode($responseData);
            $restApi->response($responseData,200);

        }

    }



    public function getConnectionV1($authToken)
    {
        global $restApi;
        try {

            $DbDetails = $this->getDbConfig($authToken);
            $this->conn = null;


            if ($DbDetails != null) {



                $host = $DbDetails['AuthDbHost'];
                 $database_name = $DbDetails['AuthDbName'];
                $username = $DbDetails['AuthDbUserName'];
                $password = $DbDetails['AuthDbPassword'];
               
             /*   $host = 'localhost';
                $database_name = 'camaroo_dfl';
                $username = 'camaroo_dfl_test_user';
                $password = 'dfl_test_user';
 */

                //$this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $database_name, $username, $password);
               // $this->conn->exec("set names utf8");
                 $this->conn=null;
                $this->conn = mysql_connect($host,$username,$password);
                mysql_select_db($database_name,$this->conn);
                return $this->conn;
            } else {
              
               $responseData['Error']='Unauthrized Token ?';
               $responseData=json_encode($responseData);
               $restApi->response($responseData,401);
            }
        } catch (PDOException $exception) {
 
            $responseData['Error']='Database Connection Error';
            $responseData=json_encode($responseData);
            $restApi->response($responseData,200);

        }

    }


    public function getDbConfig($authToken)
    {
       
        $payload = Token::Verify($authToken);
         //  var_dump($authToken);
        if ($payload) {

             $AuthUserID = $payload['UserID'];
             

            $db = $this->getAuthConnection();
           
            $sqlFields = 'DbHost as AuthDbHost,DbName as AuthDbName, DbUserName as AuthDbUserName,DbPassword as AuthDbPassword';
            $sqlQuery = "SELECT $sqlFields FROM `AdminLoginDetails` as ALD Left join AdminClientDbDetails as ACDB  on ACDB.ClientID=ALD.ClientID WHERE UserID= :AuthUserID";
            $stmt = $db->prepare($sqlQuery);
            $stmt->bindParam(':AuthUserID', $AuthUserID);

            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            return $dataRow;
        } else {
            return null;
        }



    }
}
?>