<?php
//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

function getExporterDetails($Id) {
global $iconn;
global $TblAdminExporterDetails;

 $TblAdminExporterDetails;
 $sql_exp="SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$Id'";
$stmt_exp=$iconn->query($sql_exp);
return $row_exp=$stmt_exp->fetch_array();


}
function getImporterDetails($Id) {
	global $iconn;
	global $TblAdminExporterDetails;
 $sql_pro="SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$Id'";
$stmt_pro=$iconn->query($sql_pro);
return $row_pro=$stmt_pro->fetch_array();
}
function getFormatedMonthDate($DATE){

$TIME_STAMP = strtotime($DATE);
 $formated_date = date("F d,Y", $TIME_STAMP);
 if($formated_date=='January 01,1970'){
	$formated_date=''; 
 }
return $formated_date;
}
/*
function getFormatedDateNA($DATE){

$TIME_STAMP = strtotime($DATE);
 $formated_date = date("d-m-Y", $TIME_STAMP);
 if($formated_date=='01-01-1970'){
	$formated_date='NA'; 
 }
return $formated_date;
}

*/
/*
function getFormatedDate($DATE){

$TIME_STAMP = strtotime($DATE);
 $formated_date = date("d-m-Y", $TIME_STAMP);
 if($formated_date=='01-01-1970'){
	$formated_date=''; 
 }
return $formated_date;
}
/*/

/*****decimal******/

function numberTowords($number)
{
	$number=number_format($number, 2, '.', '');
    list($integer, $fraction) = explode(".", (string) $number);

    $output = "";

    if ($integer{0} == "-")
    {
        $output = "negative ";
        $integer    = ltrim($integer, "-");
    }
    else if ($integer{0} == "+")
    {
        $output = "positive ";
        $integer    = ltrim($integer, "+");
    }

    if ($integer{0} == "0")
    {
        $output .= "zero";
    }
    else
    {
        $integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
        $group   = rtrim(chunk_split($integer, 3, " "), " ");
        $groups  = explode(" ", $group);

        $groups2 = array();
        foreach ($groups as $g)
        {
            $groups2[] = convertThreeDigit($g{0}, $g{1}, $g{2});
        }

        for ($z = 0; $z < count($groups2); $z++)
        {
            if ($groups2[$z] != "")
            {
                $output .= $groups2[$z] . convertGroup(11 - $z) . (
                        $z < 11
                        && !array_search('', array_slice($groups2, $z + 1, -1))
                        && $groups2[11] != ''
                        && $groups[11]{0} == '0'
                            ? "  "
                            : " "
                    );
            }
        }

        $output = rtrim($output, ", ");
		
    }

   /* if ($fraction > 0)
    {
        $output .= " Dollar and";
        for ($i = 0; $i < strlen($fraction); $i++)
        {
            $output .= " " . convertDigit($fraction{$i})." cents";
        }
    }*/
	
		
	//uma
	
	if($fraction > 0){ 
$output .= " Dollars and "; 
if($fraction < 20){ 
$output .= convertTwoDigit($fraction)." cents"; 
}elseif($fraction <100){ 
$output .= convertTwoDigit(substr($fraction,0,1)); 
if(convertDigit(substr($fraction,1,1))!='zero'){
$output .= "  ".convertDigit(substr($fraction,1,1))." cents"; 
}
ELSE{
	$output .= " cents"; 

}
}
 
//$rettxt .= "Dollar and no cents";
} 
else{
	$output .= " Dollars and no cents";
}
	
	
	

    return strtoupper($output);
	//uma 
}

function numberTowordsRupees($number)
{
    list($integer, $fraction) = explode(".", (string) $number);

    $output = "Ruppees ";

    if ($integer{0} == "-")
    {
        $output = "negative ";
        $integer    = ltrim($integer, "-");
    }
    else if ($integer{0} == "+")
    {
        $output = "positive ";
        $integer    = ltrim($integer, "+");
    }

    if ($integer{0} == "0")
    {
        $output .= "zero";
    }
    else
    {
        $integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
        $group   = rtrim(chunk_split($integer, 3, " "), " ");
        $groups  = explode(" ", $group);

        $groups2 = array();
        foreach ($groups as $g)
        {
            $groups2[] = convertThreeDigit($g{0}, $g{1}, $g{2});
        }

        for ($z = 0; $z < count($groups2); $z++)
        {
            if ($groups2[$z] != "")
            {
                $output .= $groups2[$z] . convertGroup(11 - $z) . (
                        $z < 11
                        && !array_search('', array_slice($groups2, $z + 1, -1))
                        && $groups2[11] != ''
                        && $groups[11]{0} == '0'
                            ? "  "
                            : " "
                    );
            }
        }

        $output = rtrim($output, ", ");
		
    }

   
		
	//uma
	
	if($fraction > 0){ 
$output .= "   Only"; 
if($fraction < 20){ 
$output .= convertTwoDigit($fraction); 
}elseif($fraction < 100){ 
$output .= convertTwoDigit(substr($fraction,0,1)); 
$output .= "  ".convertDigit(substr($fraction,1,1)); 
} 
//$rettxt .= "Dollar and no cents";
} 
else{
	$output .= "  Only";
}
	
	
	

    return strtoupper($output);
	//uma
}








function convertGroup($index)
{
    switch ($index)
    {
        case 11:
            return " decillion";
        case 10:
            return " nonillion";
        case 9:
            return " octillion";
        case 8:
            return " septillion";
        case 7:
            return " sextillion";
        case 6:
            return " quintrillion";
        case 5:
            return " quadrillion";
        case 4:
            return " trillion";
        case 3:
            return " billion";
        case 2:
            return " million";
        case 1:
            return " thousand";
        case 0:
            return "";
    }
}

function convertThreeDigit($digit1, $digit2, $digit3)
{
    $buffer = "";

    if ($digit1 == "0" && $digit2 == "0" && $digit3 == "0")
    {
        return "";
    }

    if ($digit1 != "0")
    {
        $buffer .= convertDigit($digit1) . " hundred";
        if ($digit2 != "0" || $digit3 != "0")
        {
            $buffer .= "  ";
        }
    }

    if ($digit2 != "0")
    {
        $buffer .= convertTwoDigit($digit2, $digit3);
    }
    else if ($digit3 != "0")
    {
        $buffer .= convertDigit($digit3);
    }

    return $buffer;
}

function convertTwoDigit($digit1, $digit2)
{
    if ($digit2 == "0")
    {
        switch ($digit1)
        {
            case "1":
                return "ten";
            case "2":
                return "twenty";
            case "3":
                return "thirty";
            case "4":
                return "forty";
            case "5":
                return "fifty";
            case "6":
                return "sixty";
            case "7":
                return "seventy";
            case "8":
                return "eighty";
            case "9":
                return "ninety";
        }
    } else if ($digit1 == "1")
    {
        switch ($digit2)
        {
            case "1":
                return "eleven";
            case "2":
                return "twelve";
            case "3":
                return "thirteen";
            case "4":
                return "fourteen";
            case "5":
                return "fifteen";
            case "6":
                return "sixteen";
            case "7":
                return "seventeen";
            case "8":
                return "eighteen";
            case "9":
                return "nineteen";
        }
    } else
    {
        $temp = convertDigit($digit2);
        switch ($digit1)
        {
            case "2":
                return "twenty $temp";
            case "3":
                return "thirty $temp";
            case "4":
                return "forty $temp";
            case "5":
                return "fifty $temp";
            case "6":
                return "sixty $temp";
            case "7":
                return "seventy $temp";
            case "8":
                return "eighty $temp";
            case "9":
                return "ninety $temp";
        }
    }
}

function convertDigit($digit)
{
    switch ($digit)
    {
        case "0":
            return "zero";
        case "1":
            return "one";
        case "2":
            return "two";
        case "3":
            return "three";
        case "4":
            return "four";
        case "5":
            return "five";
        case "6":
            return "six";
        case "7":
            return "seven";
        case "8":
            return "eight";
        case "9":
            return "nine";
    }
}
/********decimal */

?>
