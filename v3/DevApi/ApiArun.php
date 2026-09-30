<?php

header('Access-Control-Allow-Origin: ');

require_once('Rest.inc.php');

class API{
    
    public function getCurrentTimeDate(){
        
        $TimeStamp=new DateTime("now",new DateTimeZone('Asia/Kolkata'));
        $date=$TimeStamp->format("Y-m-d H:i:s");
        $Year =$TimeStamp-> format("Y");

        $arrCurrdate['TimeStamp']=$date;
        $arrCurrdate['Year']=$Year;

        return $arrCurrdate;

    }

    public function SessionTime($USER_ID){

        $dateTime=new DateTime("now",new DateTimeZone("Asia/Kolkata"));
        $CurrDatetime=$dateTime->format("Y-m-d H:i:s");

        $sqlsession="select LastLogin from 'AdminLoginDetails' where 'UserID'=$USER_ID ";
        $SqlExecute=mysql_query($sqlsession);
        $RowData =mysql_fetch_array($SqlExecute,MYSQL_ASSOC);
        $Lastlogintime=$RowData['LastLogin'];

        $CurrDatetime=strtotime($CurrDatetime);
        $Lastlogintime=strtotime($Lastlogintime);
        $Timediff=$CurrDatetime-$Lastlogintime;
        $Timediff=round($Timediff/60);
        
        if($Timediff >10){
            return true;
        }
        else{
            return false;
        }
    }

    
}