<!--This sample source code is provided as-is. Use this at your own discretion.
	We reserve the right to update or remove this source code at any time.-->
<!--Author: Devin Edmundowicz-->
<html>
 <head>
  <title>API Authentication</title>
 </head>
 <body>
 <h3>Enter information and click submit to validate credentials</h3>
 <div style= "width: 50%; border-width: 1px; border-color: #E6E6E6; border-style: solid; border-radius: 7px; padding: 5px; margin-left: 5px; margin-bottom: 10px;">
 <p><u>Sample Test 1:</u><br>
This is a sample test that allows you to test whether your API credentials work properly or not. Simply enter in your API and secret key into the text boxes below and press validate.</p>
</div>
 <form action= "test.php" method= "post"id= "testform">
 <div style= "background: #E6E6E6; width: 30%; padding: 5px; margin-left: 5px; margin-bottom: 10px; border-style: solid; border-width: 1px; border-radius: 7px; border-color: #BDBDBD;">
 <label><u>Credentials:</u></label><br>
 API Key: <input type= "text" name= "api"><br>
 Secret Key: <input type= "text" name= "secret"><br>
 </div>
 <button type= "submit">Validate</button>
 </form>
 
<div style= "background: #E6E6E6; width: 15%; padding: 5px; margin-left: 5px; margin-bottom: 10px; border-style: solid; border-width: 1px; border-radius: 7px; border-color: #BDBDBD;">
<b><u>Results:</u></b><br>

<?php
	
	function makeCall() {
		#create uri string to be used in generating uri call
		$url = "http://api.rightonin.com/api/v1/Tables/Schema?apiKey=".$GLOBALS['api'];
		
		#initiate the curl request
		$curl_handle = curl_init($url);
		
		#create the hash, send it to authorize function with curl request and secret key
		$hash = authorize2($curl_handle, $GLOBALS['secret']);
		
		#set the global hash function
		$GLOBALS['hash'] = 'Authorization: ROI '.$hash;
		
		#set the authentication header correctly
		$headers = array($GLOBALS['time'], $GLOBALS['nonce'], 'Authorization: ROI '.$hash);
		
		#SET THE REQUIRED OPTIONS FOR CURL REQUEST
		
		#add the headers into curl request
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
	
		#force use of a new connection (don't reuse old connection if used previously)
		curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT, True);
		
		#set option to return the result
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		
		#execute the curl request
		$request = curl_exec($curl_handle);
		
		#close the curl request
		curl_close($curl_handle);
		return $request;	
	}
	
	#used to get the current timestamp with milliseconds included
	function udate($format = 'u', $utimestamp = null) {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
	
	
	#function takes an initialized curl handle and the secret key and returns a SHA256 hashed signature
	function authorize2($ch, $secret) {
		#CREATE HEADERS
		$NonceHeaderName = "Nonce";
		$TimestampHeaderName = "Timestamp";
	
		#copy the curl handle to a temporary handle
		$temp_curl = curl_copy_handle($ch);
	
		#set the necessary options to get the headers

		curl_setopt($temp_curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($temp_curl, CURLINFO_HEADER_OUT, True);
	
		#execute the temporary curl request (so we can check if timestamp and nonce headers exist yet)
		$resp = curl_exec($temp_curl);
	
		#set the curl request headers to a string so that info can be pulled when building the query string
		$headers = curl_getinfo($temp_curl,CURLINFO_HEADER_OUT);
	
		#close the curl request
		curl_close($temp_curl);
	
		#check for timestamp, if not found, add one
		if (stripos($headers, 'Timestamp:') == false) {
			#ADD THE TIMESTAMP
			#set the timezone to UTC
			date_default_timezone_set("UTC");
	 
			#format the date so that it can be sent correctly (y-m-d'T'h:i:s.d'Z')
			$formatedDate = udate('Y-m-d\TH:i:s.u\Z');
		
			#set timestamp variable to be used when generating query string
			$TimeStampHeaderName = "timestamp=".$formatedDate;
		
			#set global variable to generate timestamp with correct format
			$GLOBALS['time']= "Timestamp: ".$formatedDate;
		}
	
		#check for nonce, if not found, add one
		if (stripos($headers, 'Nonce:') == false) {
			#ADD THE NONCE
			#generate a random nonce number
			$nonceGUID = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
			
			#set nonce variable to be used when generating query string
			$NonceHeaderName = "nonce=".$nonceGUID;
		
			#set global variable to generate nonce with correct format
			$GLOBALS['nonce']= "Nonce: ".strtolower($nonceGUID);
		}
	
		#GET THE PARTS TO BUILD THE QUERY STRING
		#add the Request method
		$requestMethod = substr($headers,0, stripos($headers," "));
	
		#remove the request method type from $headers string
		$headers = substr($headers,strlen($requestMethod)+1);
	
	
		#add the URL endpoint
		$requestEndpoint = strtolower(substr($headers, 0, stripos($headers,"?")));
	
		#remove the endpoint that was grabbed in previous line from $headers string
		$headers = substr($headers,strlen($requestEndpoint));
	
		#add the Query String
		$requestQuery = substr($headers, 0, stripos($headers, " "));
	
		#remove the api from the $headers string
		$headers = substr($headers, strlen($requestQuery));
	
		#add the timestamp and nonce
		$requestTN = strtolower($NonceHeaderName."&".$TimeStampHeaderName);
	
		#BUILD THE QUERY STRING
		$requestString = $requestMethod."\r\n";
		$requestString .= $requestEndpoint."\r\n";
		$requestString .= $requestQuery."\r\n";
		$requestString .= $requestTN."\r\n";
		#append content "doesn't work for non-GET requests yet"
		#test if there's content. If there is, add it to requestString, if not, add carriage return
		if ($requestMethod != "GET") {
			$requestString .= $GLOBALS['body']."\r\n";
		}
		else {
			$requestString .= "\r\n";
		}
		#append secret key
		$requestString .= $secret;
	
		#HASH THE STRING USING SHA256 ENCRYPTION
		$hash = hash("sha256", $requestString, true);
		
		#hash base-64 conversion
		$hash2 = base64_encode($hash);
	 
		#return the hash signature
		return $hash2;
	}
	
	#Set global variables
	$GLOBALS['api'] = $_POST["api"];
	$GLOBALS['secret'] = $_POST["secret"];
	
	#check if either textboxes were left empty
	if ($GLOBALS['api'] == "") {
		echo "<u>Error:</u> Please enter an API Key<br>";
	}
	if ($GLOBALS['secret'] == "") {
		echo "<u>Error:</u> Please enter a Secret Key<br>";
	}
	
	$test= makeCall();

	#Test if the call was successful by seeing if the result has "Name" at string index = 3.
	if (substr($test,3,4)=='Name') {
		echo "Authorized";
	}
	else {
		echo "Not Authorized";
	}
?>
<br></div>
<hr size= "1" />
 <h3>Display contacts table</h3>
 <div style= "width: 50%; padding: 5px; margin-left: 5px; margin-bottom: 10px; border-style: solid; border-width: 1px; border-radius: 7px; border-color: #BDBDBD;">
 <p><u>Sample Test 2:</u><br>
 This is a second sample test that will allow you to view the contents of your contacts table as well as check to see if your API key and Secret key are valid. Simply enter in your API key and secret key into the blanks below and press Show Table. If your credentials are approved, you will be able to view information from your Contacts table. If an error occurred, your API key or secret key didn't were not approved.</p>
 </div>
 <form action= "Display.php" method= "post">
 <div style= "background: #E6E6E6; width: 25%; padding: 5px; margin-left: 5px; margin-bottom: 10px; border-style: solid; border-width: 1px; border-radius: 7px; border-color: #BDBDBD;">
 <label><u>Credentials:</u></label><br>
 API Key: <input type= "text" name= "api"><br>
 Secret Key: <input type= "text" name= "secret"><br></div>
 <button type= "submit">Show Table</button>
 </form>
 </body>
</html>