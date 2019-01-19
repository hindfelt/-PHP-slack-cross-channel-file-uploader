<?php

function curlReq($url,$post){ //curl statement builder and runner
        $ch = curl_init();

        //set options 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //needed so that the $result=curl_exec() output is the file an$

        //execute post
        $result = curl_exec($ch);

        echo $result;
        curl_close ($ch);

}

function uploadFileToSlack($fileName,$fileType){
//channel where to the message should be posted

        $file = new CurlFile('/_COMPLETE_FILELOCATION_/' . $fileName);
        
        $post = array(
                "token" => "USER TOKEN xoxp-",
                "file" => $file,
                "channels" => "CHANNEL_WHERE_TO_UPLOAD_THE_FILE",
                "filename" => $fileName,
		"filetype" => $fileType,
                "title" => "Latest uploaded file"
        );

        $url = "https://slack.com/api/files.upload";
        curlReq($url, $post);

}


function sendMessToSlack($mess){
	//channel where to the message should be posted
        $post = array(
                "token" => "USER TOKEN xoxp-",
                "text" => $mess,
                "channel" => "CHANNEL_TO_POST_MESS_IN"  
        );

        $url = 'https://slack.com/api/chat.postMessage';
        curlReq($url, $post);
}


function downloadTempStore($url, $fileName){
    //Dont forget to set the ownership of the folder to web 'sudo chown -R www-data: _FILELOCATION_/''


    //namesanitizer
    sendMessToSlack("url:" . $url);
    $str = $fileName;
    //sendMessToSlack("filename: " . $str);
    $str = strip_tags($str); 
    $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
    $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
    $str = strtolower($str);
    $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
    $str = htmlentities($str, ENT_QUOTES, "utf-8");
    $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
    $str = str_replace(' ', '-', $str);
    $str = rawurlencode($str);
    $str = str_replace('%', '-', $str);

$localFile = "/_COMPLETE_FILELOCATION_/" . $str;

touch ($localFile);

$ch = curl_init($url); 
$fp = fopen($localFile, 'wb'); 
curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
//workspace oauth token!
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer xoxp-WORKSPACE-OAUTH-TOKEN'
            )); 
curl_exec($ch); 
curl_close($ch); 
fclose($fp); 
}



function cleanUp($fileName){
        unlink("/_COMPLETE_FILELOCATION_/" . $fileName);
}

function getJSONtoLog($da){
        if (isset($da["type"])){
                echo "fuckin eh";
                file_put_contents('/_COMPLETE_FILELOCATION_/log.txt', print_r($da, true));
        }else {echo "F*cked up!";}
}


//-- START -- //

$da = json_decode(file_get_contents('php://input'), true);
getJSONtoLog($da);

  if (isset($da["type"])){
        if ($da["token"] === "APPLICATION_VERIFICATION_TOKEN"){
                if (($da["event"]["type"] === "message") && ($da["event"]["subtype"] === "file_share")  && ($da["event"]["channel"] === "CHANNEL_WHERE_ORIGINAL_FILE_POST_BEEN_MADE")){
			downloadTempStore($da["event"]["files"]["0"]["url_private_download"],$da["event"]["files"]["0"]["name"]);
			uploadFileToSlack($da["event"]["files"]["0"]["name"],$da["event"]["files"]["0"]["filetype"]);
                }
        }

  }

?>
