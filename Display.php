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
 <form action= "test.php" method= "post" id= "testform">
 <div style= "background: #E6E6E6; width: 30%; padding: 5px; margin-left: 5px; margin-bottom: 10px; border-style: solid; border-width: 1px; border-radius: 7px; border-color: #BDBDBD;">
 <label><u>Credentials:</u></label><br>
 API Key: <input type= "text" name= "api"><br>
 Secret Key: <input type= "text" name= "secret"><br>
 </div>
 <button type= "submit">Validate</button>
 </form>
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
 <?php
	#function that sends the curl request to the server to get Contacts table
	function getRows() {
		#create uri string to be used in generating uri call
		$url = "http://api.rightonin.com/api/v1/Tables/contacts/?apiKey=".$GLOBALS['api']."&Limit=200&ID=1_";

		#Create the Curl Request
		$curl_handle = curl_init($url);
		
		#create the hash, send it to authorize function with curl handle and secret key
		$hash = authorize2($curl_handle, $GLOBALS['secret']);
		
		#set global hash variable
		$GLOBALS['hash'] = 'Authorization: ROI '.$hash;
		
		#set the authentication header with timestamp, nonce, and hash signature
		$headers = array($GLOBALS['time'], $GLOBALS['nonce'], $GLOBALS['hash']);
		
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
		
		#return the result of the curl request
		return $request;
	}
	#function to retrieve multiple pages of results
	function getMoreRows($nu) 
	{
		#create uri string to be used in generating uri call
		$url = "http://api.rightonin.com".$nu."?apiKey=".$GLOBALS['api']."&Limit=200&ID=1_";

		#Create the Curl Request
		$curl_handle = curl_init($url);
		
		#create the hash, send it to authorize function with the curl request and secret key
		$hash = authorize2($curl_handle, $GLOBALS['secret']);
		
		#set the global hash variable
		$GLOBALS['hash'] = 'Authorization: ROI '.$hash;
		
		#set the authentication header
		$headers = array($GLOBALS['time'], $GLOBALS['nonce'], $GLOBALS['hash']);
	
		#SET THE REQUIRED OPTIONS FOR CURL REQUEST
		
		#add the headers into curl request
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);

		#force use of a new connection (don't reuse connection if used previously)
		curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT, True);
		
		#set option to return the result
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		
		#execute the curl request
		$request = curl_exec($curl_handle);
		
		#close the curl request
		curl_close($curl_handle);
		
		#return the result of the curl request
		return $request;
	}
	
	#function takes an initialized curl handle and the secret key and returns a SHA256 hashed signature
	function authorize2($ch, $secret) 
	{
		#CREATE HEADERS
		$NonceHeaderName = "Nonce";
		$TimestampHeaderName = "Timestamp";
		
		#copy the curl handle to a temporary handle
		$temp_curl = curl_copy_handle($ch);
	
		#set the necessary options to get the headers for the temporary curl handle

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
			
			#set global variable to generate nonce with  correct format
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
	 
		#return the hash
		return $hash2;
	}
	
	#function for generating a ISO 8601 formatted timezone
	function udate($format = 'u', $utimestamp = null) 
	{
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
	
	#adds the data to the table
	function addRows($fname, $lname, $email, $phone) {
		#add a row to the table with the populated cells
		#if statement for table style formatting
		if ($GLOBALS['rowcolor'] == 1) {
			echo '<tr align= "center" bgcolor= "#BDBDBD">';
			
			#add the information to each cell
			echo "<td>".$fname."</td>";
			echo "<td>".$lname."</td>";
			echo "<td>".$email."</td>";
			echo "<td>".$phone."</td>";
			echo "</tr>";
			
			#switch style color variable
			$GLOBALS['rowcolor'] = 0;
		}
		else {
			echo '<tr align= "center" bgcolor= "#E6E6E6">';
			
			#add the information to each cell
			echo "<td>".$fname."</td>";
			echo "<td>".$lname."</td>";
			echo "<td>".$email."</td>";
			echo "<td>".$phone."</td>";
			echo "</tr>";
			
			#switch style color variable
			$GLOBALS['rowcolor'] = 1;
		}	
	}
	
	#set api & secret from inputed text
	$GLOBALS['api'] = $_POST["api"];
	$GLOBALS['secret'] = $_POST["secret"];
	
	#initialize table style variable
	$GLOBALS['rowcolor'] = 0;
	
	#get JSON data by calling getRows() function
	$result = getRows();
	
	#Check if the call was successful & set valid variable accordingly
	if (substr($result,2,5) == "Total")
	{
		$GLOBALS['valid'] = true;
	}
	else {
		$GLOBALS['valid'] = false;
	}
	
	#make sure call was successful
	if ($GLOBALS['valid'] == true) {
	#build start of table
	echo '<table cols= "4" align= "center" width= "80%" border= "1">';
	echo '<tr align= "center" bgcolor= "#BDBDBD">';
	echo '<th>First Name</th>';
	echo '<th>Last Name</th>';
	echo '<th>Email</th>';
	echo '<th>Phone Number</th>';
	echo '</tr>';
	
	#READ THE JSON
	$arr = json_decode($result, true);
	
	#get the total number of results
	$total = $arr['Total'];
	
	#Display total number of results
	echo '<h3 align= "center">Displaying <u>'.$total.'</u> Total Results</h3>';
	
	#counter for displaying multiple pages of results
	$leftToShow = $total;
	
	#display data while there are still results to pull
	while ($leftToShow > 0) {
		#get to the results part of the JSON
		$data = $arr['Results'];
		
		#get object's information for each individual object
		foreach ($data as $obj) {
			#get the useful information
			$fName = $obj['FirstName'];
			$lName = $obj['LastName'];
			$email = $obj['Email'];
			$phone = $obj['Phone'];
		
			#call addRows
			addRows($fName, $lName, $email, $phone);
			
			#decrement counter
			$leftToShow = $leftToShow - 1;
		}
		#get nextPage url
		$nextUrl = $arr['NextPage'];
		
		#call getMoreRows() function to get next page of results
		$result = getMoreRows($nextUrl);
		
		#reset $arr with the new results
		$arr = json_decode($result, true);
	}
	}
	#display error message for a failed api call
	else {
		echo "<b><u>Error:</u></b> Could not validate api or secret key. Please check to make sure they were entered correctly.";
	}
 ?>
 
 
 
 </body>
</html>