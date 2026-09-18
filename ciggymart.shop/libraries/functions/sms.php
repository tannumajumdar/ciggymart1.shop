<?php
/*http://gayatrimobads.in/smsapi.aspx?

username=TRAD24&password=12345&sender=BUYSEL&to=9826396462&message=OPTION CALL TYPE:CURRENCY
PAIR:USD/JPY
CALL:BUY AT 1.2333 TO 1.2345
EXPIRE:TODAY 00:01 TO 01:01-BUYSEL&route=Priority*/

function get_data($mobile,$msg)
								{
									$url="http://www.smsvalley.co.in/apit.php?username=trade24&password=917545&senderid=BUYSEL&mnumber=$mobile&message=$msg";
								  $ch = curl_init();
								  $timeout = 5;
								  curl_setopt($ch,CURLOPT_URL,$url);
								  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
								  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
								  $data = curl_exec($ch);
								  curl_close($ch);
								  return $data;
								}
function sendsms($mobile,$text)
	{
	$host = "http://gayatrimobads.in";
	$request = ""; 					//initialize the request variable
	$param["username"] = "TRAD24"; 		//	this is the username of your SMS account
	$param["password"] = "12345"; 	//this is the password of our SMS account
	$param["message"] = substr($text,0,159); 	//this is the message that we want to send
	$param["sender"] = "BUYSEL"; 		
	$param["to"] = $mobile;
	$param["priority"] = "Priority";
	foreach($param as $key=>$val) // Traverse through each member of the param array
{
$request.= $key."=".urlencode($val); //we have to urlencode the values
$request.= "&"; //append the ampersand (&) sign after each paramter/value pair
}
echo "<br/>";
 $request = substr($request, 0, strlen($request)-1); //remove the final ampersand sign from the request
echo "<br/>";
//echo $request; exit();
$script = "/smsapi.aspx";
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
echo $header;
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