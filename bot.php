<?php
require 'config.php';
require 'connect.php';
require 'class-http-request.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

$chatID = $update["message"]["chat"]["id"];
$userID = $update["message"]["from"]["id"];
$msg = $update["message"]["text"];
$username = $update["message"]["chat"]["username"];

global $msg;

if(file_exists('cache/' . $userID)){
 $cachefile = file_get_contents('cache/' . $userID);
} else {
 $query = "INSERT INTO httpresponsebot (userID, chatID) VALUES ('$userID', '$chatID')";
 $result = mysqli_query($myconn, $query) or die("0");
}

if($msg == "English 🇬🇧" && $cachefile == "selectlang"){
  $text = "Welcome!🇬🇧
  With this Bot you can get the HTTP status code and the redirects instantly for any website. Send me a link.
  If you want to support me, vote the Bot on https://telegram.me/storebot?start=httpresponsebot. Thank you!☺";
  $menu[] = array("Help");
  $menu[] = array("Language");
  $query = "UPDATE httpresponsebot SET lang='en' WHERE userID='$userID'";
  $result = mysqli_query($myconn, $query) or die("0");
  $file = "cache/" . $userID;
  $f2 = fopen($file, 'w');
  fwrite($f2, "en");
  fclose($f2);
  sm($chatID, $text, $menu);
} else if($msg == "Italiano 🇮🇹" && $cachefile == "selectlang"){
  $text = "Benvenuto!🇮🇹
  Con questo Bot puoi ottenere il codice di stato HTTP e i reindirizzamenti di qualsiasi sito istantaneamente. Inviami un link.
  Supportami votandomi su https://telegram.me/storebot?start=httpresponsebot. Grazie!☺";
  $menu[] = array("Aiuto");
  $menu[] = array("Lingua");
  $query = "UPDATE httpresponsebot SET lang='it' WHERE userID='$userID'";
  $result = mysqli_query($myconn, $query) or die("0");
  $file = "cache/" . $userID;
  $f2 = fopen($file, 'w');
  fwrite($f2, "it");
  fclose($f2);
  sm($chatID, $text, $menu);
} else if($msg == "Help" && $cachefile == "en") {
  $text = "With this Bot you can get the HTTP status code and the redirects instantly for any website. Send me a link when you want.
  ⏩ - List of HTTP status codes: https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
  ⏩ - If you want to support me, vote the Bot on https://telegram.me/storebot?start=httpresponsebot. Thank you!☺
  ⏩ - For news and update follow me on @franci22channel (only 🇮🇹).
  ⏩ - Report bad translations: @franci22";
  sm($chatID, $text, false, 'HTML', false, false, true);
} else if($msg == "Aiuto" && $cachefile == "it") {
  $text = "Con questo Bot puoi ottenere il codice di stato HTTP e i reindirizzamenti di qualsiasi sito istantaneamente. Inviami un link quando vuoi.
  ⏩ - Lista dei codici di stato HTTP: https://it.wikipedia.org/wiki/Codici_di_stato_HTTP
  ⏩ - Per supportarmi votami su https://telegram.me/storebot?start=httpresponsebot. Grazie!☺
  ⏩ - Seguimi sul canale @franci22channel per eventuali aggiornamenti o comunicazioni.";
  sm($chatID, $text, false, 'HTML', false, false, true);
} else if($msg == "Language" || $msg == "Lingua"){
  unlink("cache/" . $userID);
  $cachefile = null;
  $msg = "/start";
} else if(filter_var(idn_to_ascii($msg), FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) == TRUE || filter_var($msg, FILTER_VALIDATE_IP) == TRUE){
  if($cachefile == "it"){ $text2 = "Il codice di risposta HTTP di $msg è: "; } else { $text2 = "The HTTP status code of $msg is: "; }
  $text = $text2 . get_http_response_code($msg);
  sm($chatID, $text, false, 'HTML', false, false, true);
} else if(str_split($msg)[0] != "/"){
  if($cachefile == "it"){ $text = "Comando o URL non valido. Ricordati di anteporre http(s):// all'URL."; } else { $text = "Command or URL invalid. Remember to prefix http(s)://."; }
  sm($chatID, $text, false, 'HTML', false, true);
}

switch ($msg){
  case '/start':
  //	if($cachefile == null){
  	$text = "🇬🇧 - Welcome! Select a language:\n🇮🇹 - Benvenuto! Seleziona una lingua:";
  	langmenu($chatID, $text);
  	$file = "cache/" . $userID;
  	$f2 = fopen($file, 'w');
  	fwrite($f2, "selectlang");
  	fclose($f2);
  //	}
  break;
}

function langmenu($chatID, $text){
  $menu[] = array("English 🇬🇧");
  $menu[] = array("Italiano 🇮🇹");
  sm($chatID, $text, $menu);
}

function sm($chatID, $text, $rmf = false, $pm = 'HTML', $dis = false, $replyto = false, $preview = false){
  global $api;
  global $userID;
  global $update;

  $rm = array('keyboard' => $rmf,
    'resize_keyboard' => true
  );
  $rm = json_encode($rm);

  $args = array(
    'chat_id' => $chatID,
    'text' => $text,
    'disable_notification' => $dis,
    'parse_mode' => $pm
  );
  if($replyto) $args['reply_to_message_id'] = $update["message"]["message_id"];
  if($rmf) $args['reply_markup'] = $rm;
  if($preview) $args['disable_web_page_preview'] = $preview;
  if($text){
    $r = new HttpRequest("post", "https://api.telegram.org/$api/sendmessage", $args);
    //$rr = $r->getResponse();
    //$ar = json_decode($rr, true);
  }
}
function get_http_response_code($domain1) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $domain1); //set url
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36");
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_HEADER, true); //get header
	curl_setopt($ch, CURLOPT_NOBODY, true); //do not include response body
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //do not show in browser the response
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); //follow any redirects
	$response = curl_exec($ch);
	preg_match_all('/^Location:(.*)$/mi', $response, $matches);
	$new_url = !empty($matches[1]) ? trim($matches[1][0]) : 'No redirect found';
	$code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	curl_close($ch);
	if($code == null) { return "null"; } else if($code == 301 || $code == 302){ return $code . ". Redirect: " . $new_url;} else { return $code; }
}
?>
