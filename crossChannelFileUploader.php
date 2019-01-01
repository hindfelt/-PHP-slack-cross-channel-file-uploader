
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

function uploadFileToSlack($fileName){
        $file = new CurlFile('_FILEDIR_/' . $fileName);
        
        $post = array(
                "token" => "xoxp-TOKEN",
                "file" => $file,
                "channels" => "CHANNEL_to_which_file_will_be_uploaded",
                "filename" => $fileName,
                "title" => "Latest uploaded file"
        );

        $url = "https://slack.com/api/files.upload";
        curlReq($url, $post);

}


function sendMessToSlack(){

        $post = array(
                "token" => "xoxp-TOKEN",
                "text" => "TEXT",
                "channel" => "CHANNEL_to_post_message"
        );

        $url = 'https://slack.com/api/chat.postMessage';
        curlReq($url, $post);
}


function downloadTempStore($url, $fileName){
    //namesanitizer
    
    $str = $fileName;
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

$localFile = "_FILEDIR_/" . $str;

touch ($localFile);

$ch = curl_init($url); 
$fp = fopen($localFile, 'wb'); 
curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer xoxp-TOKEN'
            )); 
curl_exec($ch); 
curl_close($ch); 
fclose($fp); 
}



function cleanUp($fileName){
        unlink("_FILEDIR_/" . $fileName);
}

function getJSONtoLog($da){
        if (isset($da["type"])){
                echo "fuckin eh";
                file_put_contents('_FILEDIR_/_logfilename_.txt', print_r($da, true));
        }else {echo "F*cked up!";}
}

$da = json_decode(file_get_contents('php://input'), true);
  if (isset($da["type"])){
        if ($da["token"] === "SLACK_APP_TOKEN"){
                if (($da["event"]["type"] === "message") && ($da["event"]["subtype"] === "file_share") && && ($da["event"]["channel"] === "CHANNEL_where_file_orginially_is_posted")){
                        downloadTempStore($da["event"]["files"]["0"]["url_private_download"],$da["event"]["files"]["0"$
                        uploadFileToSlack($da["event"]["files"]["0"]["name"]);
                }
        }

  }

?>
