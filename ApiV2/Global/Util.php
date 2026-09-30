<?php

include_once 'AppKeys.php';



function getTimeStamp()
{
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
	return $CurrentDateTime = $dateTime->format("Y-m-d  H:i:s");
}

function getFormatedDate($RawDate)
{
	return $RawDate ;
}

function getCurrentDateTime(){
    $TimeFormat = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
    $TimeStamp=$TimeFormat->format("Y-m-d  H:i:s"); 
    $Year=$TimeFormat->format("Y");
    $AryCurrentDate['TimeStamp']=$TimeStamp;			
    $AryCurrentDate['Year']=$Year;			
    
    return $AryCurrentDate;
}

function getFinancialYear()
{
    // Get current date/time in IST
    $time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));

    // Extract year and month
    $currentYear = (int)$time->format('Y');
    $currentMonth = (int)$time->format('n');

    // Determine financial year range (April–March)
    if ($currentMonth <= 3) {
        $startYear = $currentYear - 1;
        $endYear = $currentYear;
    } else {
        $startYear = $currentYear;
        $endYear = $currentYear + 1;
    }

    $financialYear = substr($startYear, -2) . substr($endYear, -2);
    return $financialYear;
}

function ConvertNumbersToINR($number,$Currency,$SubCurrency) {
	//echo $number;
	$number=str_replace(',','',$number);

    $hyphen      = ' ';
    $conjunction = ' and ';
    $separator   = ' ';
    $negative    = 'negative ';
    $decimal     = ' '.$Currency .' ';
    $dictionary  = array(
       
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'forty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        100000             => 'Lakh',
        10000000          => 'crore'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number),$Currency,$SubCurrency);
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
        case $number < 100000:
            $thousands   = ((int) ($number / 1000));
            $remainder = $number % 1000;

            $thousands =convert_number_to_words($thousands,$Currency,$SubCurrency);

            $string .= $thousands . ' ' . $dictionary[1000];
            if ($remainder) {
                $string .= $separator .convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
        case $number < 10000000:
            $lakhs   = ((int) ($number / 100000));
            $remainder = $number % 100000;

            $lakhs = convert_number_to_words($lakhs,$Currency,$SubCurrency);

            $string = $lakhs . ' ' . $dictionary[100000];
            if ($remainder) {
                $string .= $separator . convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
        case $number < 1000000000:
            $crores   = ((int) ($number / 10000000));
            $remainder = $number % 10000000;

            $crores = convert_number_to_words($crores,$Currency,$SubCurrency);

            $string = $crores . ' ' . $dictionary[10000000];
            if ($remainder) {
                $string .= $separator . convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits,$Currency,$SubCurrency) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
    }

  if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal; 
	  
        $words = array();
		if($fraction>0){
		$string .= convert_number_to_words($fraction,$Currency,$SubCurrency);
		$string .= ' '.$SubCurrency;
		}
       /* foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);*/
		
    }

    return strtoupper($string);
}



function convert_number_to_words($number,$Currency,$SubCurrency) { //uma this is replace with number_to_words method i think this is not using any where 
	// echo $number;
	 $number=str_replace(',','',$number);

    $hyphen      = ' ';
    $conjunction = ' and ';
    $separator   = ' ';
    $negative    = 'negative ';
    $decimal     = ' '.$Currency .' ';
    $dictionary  = array(
       
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'forty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        100000             => 'hundred',
        10000000          => 'crore'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number),$Currency,$SubCurrency);
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder,$Currency,$SubCurrency);
            }

           
            break;
        case $number <= 100000:
            $thousands   = ((int) ($number / 1000)); 
            $remainder = $number % 1000;

            $thousands =convert_number_to_words($thousands,$Currency,$SubCurrency);

            $string .= $thousands . ' ' . $dictionary[1000];
            if ($remainder) {
                $string .= $separator .convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
        case $number < 10000000:
            $lakhs   = ((int) ($number / 100000));
            $remainder = $number % 100000;

            $lakhs = convert_number_to_words($lakhs,$Currency,$SubCurrency);

            $string = $lakhs . ' ' . $dictionary[100000];
            if ($remainder) {
                $string .= $separator . convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
        case $number < 1000000000:
            $crores   = ((int) ($number / 10000000));
            $remainder = $number % 10000000;

            $crores = convert_number_to_words($crores,$Currency,$SubCurrency);

            $string = $crores . ' ' . $dictionary[10000000];
            if ($remainder) {
                $string .= $separator . convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits,$Currency,$SubCurrency) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder,$Currency,$SubCurrency);
            }
            break;
    }

  if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal; 
	  
        $words = array();
		if($fraction>0){
		$string .= convert_number_to_words($fraction,$Currency,$SubCurrency);
		$string .= ' '.$SubCurrency;
		}
       /* foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);*/
		
    }
    // echo $string; 
    return strtoupper($string);
}

function getAmountInWords($number,$Currency,$SubCurrency)
{

	//echo $number; exit;
	$number=number_format($number, 2, '.', '');
    list($integer, $fraction) = explode(".", (string) $number);

    $output = "";

    if ($integer[0] == "-"){
        $output = "negative ";
        $integer    = ltrim($integer, "-");
    }
    else if ($integer[0] == "+")
    {
        $output = "positive ";
        $integer    = ltrim($integer, "+");
    }

    if ($integer[0] == "0")
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
            $groups2[] = convertThreeDigit($g[0], $g[1], $g[2]);
        }

        for ($z = 0; $z < count($groups2); $z++)
        {
            if ($groups2[$z] != "")
            {
                $output .= $groups2[$z] . convertGroup(11 - $z) . (
                        $z < 11
                        && !array_search('', array_slice($groups2, $z + 1, -1))
                        && $groups2[11] != ''
                        && $groups[11][0] == '0'
                            ? "  "
                            : " "
                    );
            }
        }

        $output = rtrim($output, ", ");
		
    }
/*
    if ($fraction > 0)
    {
        $output .= " Dollar and";
        for ($i = 0; $i < strlen($fraction); $i++)
        {
            $output .= " " . convertDigit($fraction{$i})." cents";
        }
    }*/
	
		
	/***uma 13-06-2023 cmmented with permission of surya***/
	//echo $fraction; exit;
	
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
 

} 
else{
	//$output .= " Dollars and no cents";
	$output .= " ";
}
	
	/***uma 13-06-2023 cmmented with permission of surya***/
	

    return strtoupper($output);
	//uma 
}


// Encryption function
function encryptData($value)
{
	global $appsecretKey;
	$key=$appsecretKey;
	$cipher = "AES-256-CBC";
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
	$encrypted = openssl_encrypt($value, $cipher, $key, 0, $iv);
	return base64_encode($iv . $encrypted);
}

// Decryption function
function decryptData($encryptedValue)
{
	global $appsecretKey;
	$key=$appsecretKey;
	$cipher = "AES-256-CBC";
	$encryptedValue = base64_decode($encryptedValue);
	$ivLength = openssl_cipher_iv_length($cipher);
	$iv = substr($encryptedValue, 0, $ivLength);
	$encryptedData = substr($encryptedValue, $ivLength);
	return openssl_decrypt($encryptedData, $cipher, $key, 0, $iv);
}





function getEncryptedToken($payload){
	global $appsecretKey;
	$secretKey=$appsecretKey;

	$secret_key = "my_secret_key";

	// Create the header array.
	$header = [
		"alg" => "HS256",
		"typ" => "JWT"
	];
	
	// Create the payload array.
	$payload = [
		"iss" => "https://example.com",
		"sub" => "1234567890",
		"exp" => time() + (60 * 60 * 24), // Expires in 24 hours.
	];
	
	// Encode the header and payload arrays into a JSON string.
	$json_string = json_encode($header) . "." . json_encode($payload);
	
	// Sign the JSON string using the secret key.
	$signature = hash_hmac("sha256", $json_string, $secret_key);
	
	// Base64 encode the signature.
//	$signature = base64_encode($signature);
	
	// Combine the header, payload, and signature into a single string, separated by dots.
	return $token =  $signature;
	//return 'test';
}




/*


// Decrypt the token.
$header = json_decode(explode(".", $token)[0], true);
$payload = json_decode(explode(".", $token)[1], true);



// Verify the signature.
if (hash_hmac("sha256", $json_string, $secret_key) == $signature) {
    // The token is valid.
} else {
    // The token is invalid.
}
*/


//uma bil to word//
function numberTowords($number,$CurrencyName)
{

	//echo $number; exit;
	$number = number_format($number, 2, '.', '');
	list($integer, $fraction) = explode(".", (string) $number);

	$output = "";

	if ($integer { 0} == "-") {
		$output = "negative ";
		$integer = ltrim($integer, "-");
	} else if ($integer { 0} == "+") {
		$output = "positive ";
		$integer = ltrim($integer, "+");
	}

	if ($integer { 0} == "0") {
		$output .= "zero";

		
	} else {
		$integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
		$group = rtrim(chunk_split($integer, 3, " "), " ");
	
		$groups = explode(" ", $group);
			
		$groups2 = array();
		foreach ($groups as $g) {
			$groups2[] = convertThreeDigit($g { 0}, $g { 1}, $g { 2});
			
			//VAR_DUMP ($groups2); 
		}

	//	EXIT; 

		for ($z = 0; $z < count($groups2); $z++) {
		
			if ($groups2[$z] != "") {

				$output .= $groups2[$z] . convertGroup(11 - $z) . (
					$z < 11
					&& !array_search('', array_slice($groups2, $z + 1, -1))
					&& $groups2[11] != ''
					&& $groups[11]{ 0} == '0'
					? "  "
					: " "
				);

				
			}
		}
		
		$output = rtrim($output, ", ");
		
	}

	
	/*
		if ($fraction > 0)
		{
			$output .= " Dollar and";
			for ($i = 0; $i < strlen($fraction); $i++)
			{
				$output .= " " . convertDigit($fraction{$i})." cents";
			}
		}*/


	/***uma 13-06-2023 cmmented with permission of surya***/
	//echo $fraction; exit;

	if ($fraction > 0) {
		// $output .= " Dollars and ";
		$output .=' '.$CurrencyName." and ";
		if ($fraction < 20) {
			$output .= convertTwoDigit($fraction) . " cents";
		} elseif ($fraction < 100) {
			$output .= convertTwoDigit(substr($fraction, 0, 1));
			if (convertDigit(substr($fraction, 1, 1)) != 'zero') {
				$output .= "  " . convertDigit(substr($fraction, 1, 1)) . " cents";
			} else {
				$output .= " cents";

			}
		}


	} else {
		$output .= ' '.$CurrencyName;
		// $output .= " ";
	}

	/***uma 13-06-2023 cmmented with permission of surya***/

    // echo $output; exit;
	return strtoupper($output);
	//uma 
}

function convertThreeDigit($digit1, $digit2, $digit3)
{
	$buffer = "";
	// $And = 'And ';
	
	if ($digit2 != "0" && $digit3 != "0") {
		$And = '';
	}
	if ($digit1 == "0" && $digit2 == "0") {
		$And = '';
	}
	
	if ($digit1 == "0" && $digit2 == "0" && $digit3 == "0") {
		return "";
	}

	if ($digit1 != "0") {
		$buffer .= convertDigit($digit1) . " hundred";
		if ($digit2 != "0" || $digit3 != "0") {
			$buffer .= "  ";
		}
	}

	if ($digit2 != "0") {
		// if($digit2 != "0"&& $digit3 == "0" ){
			//uma//
		if( $buffer=="" ){
			   $buffer .= convertTwoDigit($digit2, $digit3); 
			  }
			  else{
                //    $buffer .= "And ".convertTwoDigit($digit2, $digit3);
				$buffer .= $And . convertTwoDigit($digit2, $digit3);
				
				
			  }
			  //uma//
		// $buffer .= $And . convertTwoDigit($digit2, $digit3);
		
		
	} else if ($digit3 != "0") {


		// $buffer .= $And . convertDigit($digit3); //20-03-2024
		$buffer .=  convertDigit($digit3); //20-03-2024
	}

	return $buffer;
}

function convertTwoDigit($digit1, $digit2)
{
	if ($digit2 == "0") {
		switch ($digit1) {
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
	} else if ($digit1 == "1") {
		switch ($digit2) {
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
	} else {
		$temp = convertDigit($digit2);
		switch ($digit1) {
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
	switch ($digit) {
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

function convertGroup($index)
{
	switch ($index) {
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
//uma bil to word//

function removeStartingString($input) {
    // Use a regular expression to remove everything up to and including the first special character
    $output = preg_replace('/^[^a-zA-Z0-9]+/', '', $input);
    
    // Use a regular expression to remove everything up to and including the first special character and then return the remaining string
    $output = preg_replace('/^[a-zA-Z0-9]+[^a-zA-Z0-9]+/', '', $input);

    return $output;
}

function prepareDateFilter($Year, $Month)
{
	$DateStart = '';
	$DateEnd = '';
	if ($Month != '') {
		$Month = sprintf('%02d', $Month);
		if ($Month > 3) {
			$DateStart = $Year . '-' . $Month . '-01';
			$DateEnd = ($Year) . '-' . $Month . '-31';
		} else {
			$DateStart = ($Year + 1) . '-' . $Month . '-01';
			$DateEnd = ($Year + 1) . '-' . $Month . '-31';
		}
	} else {
		$DateStart = $Year . '-04-01';
		$DateEnd = ($Year + 1) . '-03-31';
	}

	return " BETWEEN '$DateStart' and '$DateEnd'";
}

function debug_report($data)
{
    $filename = "/home/u514614285/domains/camaroo.in/public_html/debug-log.php";
    $fh = fopen($filename, "a");
    fwrite($fh, "<html><body>");
    fwrite($fh, print_r($data,true));
    fwrite($fh, "<br/>");
    fwrite($fh, "<br/>");
    fwrite($fh, "<hr/>");
    fwrite($fh, "</body></html>");
    fclose($fh);
}

?>