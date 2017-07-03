<?php
require 'config.php';
require 'class-http-request.php';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

$chatID = $update["message"]["chat"]["id"];
$userID = $update["message"]["from"]["id"];
$msg = $update["message"]["text"];
$username = $update["message"]["chat"]["username"];

$query = "SELECT * FROM $table WHERE userID = $userID";
$result = $dbuser->query($query) or die("0");
$numrows = mysqli_num_rows($result);
if($numrows == 0){
  $query = "INSERT INTO $table (userID, username) VALUES ($userID, '$username')";
  $result = $dbuser->query($query) or die("0");
} else {
  $row = $result->fetch_array(MYSQLI_ASSOC);
  $lang = $row['lang'];
}

if ($msg == "/start") {
  langmenu();
  updatelang("selectlang");
} elseif($msg == "English 🇬🇧" && $lang == "selectlang"){
  $text = "Welcome!🇬🇧
⏩ With this Bot you can get the HTTP status code and the redirects instantly for any website. Just send me a link.";
  $menu[] = array("Help ❓");
  $menu[] = array("Language 🏳️‍🌈");
  updatelang("en");
  sm($chatID, $text, $menu);
  $text = "If you want to support me, vote the Bot on StoreBot or make a donation using the links below. Thank you!💚";
  sm($chatID, $text, false, donation());
} elseif($msg == "Italiano 🇮🇹" && $lang == "selectlang"){
  $text = "Benvenuto!🇮🇹
Con questo Bot puoi ottenere il codice di stato HTTP e i reindirizzamenti di qualsiasi sito istantaneamente. Inviami semplicemente un link.";
  $menu[] = array("Aiuto ❓");
  $menu[] = array("Lingua 🏳️‍🌈");
  updatelang("it");
  sm($chatID, $text, $menu);
  $text = "Se mi vuoi supportare, vota il Bot su StoreBot o fai una donazione usando i link qui sotto. Grazie!💚";
  sm($chatID, $text, false, donation());
} elseif($msg == "Help ❓" && $lang == "en") {
  $text = "With this Bot you can get the HTTP status code and the redirects instantly for any website. Send me a link when you want.
⏩ - <a href='https://en.wikipedia.org/wiki/List_of_HTTP_status_codes'>List of HTTP status codes</a>
⏩ - If you want to support me, vote the Bot on StoreBot or make a donation using the links below. Thank you!💚
⏩ - For news and update follow me on @franci22channel (only 🇮🇹).
⏩ - This Bot is avaiable on GitHub at <a href='https://github.com/franci22/httpresponsebot'>this link</a>!
⏩ - If you want to report bad translations or send suggestions write me on @franci22.";
  sm($chatID, $text, false, donation());
} elseif($msg == "Aiuto ❓" && $lang == "it") {
  $text = "Con questo Bot puoi ottenere il codice di stato HTTP e i reindirizzamenti di qualsiasi sito istantaneamente. Inviami un link quando vuoi.
⏩ - <a href='https://it.wikipedia.org/wiki/Codici_di_stato_HTTP'>Lista dei codici di stato HTTP</a>
⏩ - Puoi supportarmi votando il Bot su StoreBot o donando attraverso i link qui sotto. Grazie!💚
⏩ - Questo Bot è disponibile su GitHub a <a href='https://github.com/franci22/httpresponsebot'>questo link</a>!
⏩ - Seguimi sul canale @franci22channel per eventuali aggiornamenti o comunicazioni.
⏩ - Se vuoi inviarmi suggerimenti scrivimi a @franci22.";
  sm($chatID, $text, false, donation());
} elseif($msg == "Language 🏳️‍🌈" || $msg == "Lingua 🏳️‍🌈"){
  langmenu();
  updatelang("selectlang");
} elseif(filter_var($msg, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) == TRUE || filter_var($msg, FILTER_VALIDATE_IP) == TRUE){
  if($lang == "it"){ $text2 = "Il codice di risposta HTTP di $msg è: "; } else { $text2 = "The HTTP status code of $msg is: "; }
  $text = $text2 . get_http_response_code($msg);
  sm($chatID, $text);
} else {
  $text = ($lang == "it") ? "Comando o URL non valido. Ricordati di anteporre http(s):// all'URL." : "Command or URL invalid. Remember to prefix http(s)://.";
  sm($chatID, $text);
}

function langmenu(){
  global $chatID;
  $text = "🇬🇧 - Welcome! Select a language:\n🇮🇹 - Benvenuto! Seleziona una lingua:";
  $menu[] = array("English 🇬🇧");
  $menu[] = array("Italiano 🇮🇹");
  sm($chatID, $text, $menu);
}

function donation(){
  $inline[] = array(array(
    "text" => "StoreBot ⭐️",
    "url" => "https://telegram.me/storebot?start=httpresponsebot"
  ));
  $inline[] = array(array(
    "text" => "PayPal 💳",
    "url" => "https://PayPal.me/franci22"
  ), array(
    "text" => "BitCoin 💰",
    "url" => "https://paste.ubuntu.com/24299810/"
  ));
  return $inline;
}

function sm($chatID, $text, $rmf = false, $inline = false, $pm = 'HTML', $dis = false, $replyto = false, $preview = true){
  global $api;
  global $userID;
  global $update;

  if($inline){
    $rm = array('inline_keyboard' => $inline);
  } elseif ($rmf) {
    $rm = array('keyboard' => $rmf,
      'resize_keyboard' => true
    );
  }
  $rm = json_encode($rm);

  $args = array(
    'chat_id' => $chatID,
    'text' => $text,
    'disable_notification' => $dis,
    'parse_mode' => $pm
  );
  if($replyto) $args['reply_to_message_id'] = $update["message"]["message_id"];
  if($rmf OR $inline) $args['reply_markup'] = $rm;
  if($preview) $args['disable_web_page_preview'] = $preview;
  if($text){
    new HttpRequest("post", "https://api.telegram.org/$api/sendmessage", $args);
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

function updatelang($text){
  global $userID;
  global $dbuser;
  global $table;
  $dbuser->query("UPDATE $table SET lang='$text' WHERE userID=$userID");
}
?>
