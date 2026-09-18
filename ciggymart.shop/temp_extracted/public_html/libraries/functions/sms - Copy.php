<?php

function sendsms($mobile,$text)
	{
	$host = "sms.suncrossonline.com";
	$request = ""; 					//initialize the request variable
	$param["username"] = "wwwtrading"; 		//	this is the username of your SMS account
	$param["api_password"] = "91e0cjp1zcm00ww9u"; 	//this is the password of our SMS account
	$param["message"] = substr($text,0,159); 	//this is the message that we want to send
	$param["sender"] = "trad24"; 		
	$param["to"] = $mobile;
	$param["priority"] = "2";
	foreach($param as $key=>$val) // Traverse through each member of the param array
{
$request.= $key."=".urlencode($val); //we have to urlencode the values
$request.= "&"; //append the ampersand (&) sign after each paramter/value pair
}
echo "<br/>";
echo $request = substr($request, 0, strlen($request)-1); //remove the final ampersand sign from the request
echo "<br/>";
//echo $request; exit();
$script = "/pushsms.php";
$request_length = strlen($request);
$method = "POST";
if ($method == "GET")
{
$script .= "?$request";
}
//Now comes the header which we are going to post.
$header = "$method $script HTTP/1.1\r\n";
$header .= "Host: $host\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: $request_length\r\n";
$header .= "Connection: close\r\n\r\n";
$header .= "$request\r\n";

$output = "";
//Now we open up the connection
$socket = @fsockopen($host, 80, $errno, $errstr);
if ($socket) //if its open, then...
{
fputs($socket, $header); // send the details over
while(!feof($socket))
{
$output=$output.fgets($socket); //get the results
}
fclose($socket);
}

$pos=strpos($output,"Submitted");
if ($pos===false)
return(0); // SMS Not Successful
else
return(1); // SMS Was successful
	}


?>