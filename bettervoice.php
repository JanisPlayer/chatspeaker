<?php
error_reporting(0);
?>

<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//error_log('Fehlermeldung', 0);
?>

<?php
$keys = "";

session_start();

//Ohne Witz da kannst fast wieder eine Funktion für bauen, nur weil das scheiß Teil Fehler nicht mag.
$keyval = null;
if (isset($_GET['key'])) {
$keyval = $_GET['key'];
} else {
$keyval = null; //Absulut unnötig, aber ich gehe auf Nummer sicher. :D
}

$apival = null;
if (isset($_GET['api'])) {
  $apival = $_GET['api'];
} else {
  die("Wähle die API aus.");
}

$user_id = null;
if (isset($_SESSION['UserID'])) {
  $user_id = $_SESSION['UserID'];
} else {
  $user_id = null;
}

function send_request_via_proxy($proxy_host, $proxy_port, $url) {
    // URL-Teile extrahieren
    $url_parts = parse_url($url);

    // Socket erstellen und Verbindung zum Proxy herstellen
    $proxy_socket = fsockopen($proxy_host, $proxy_port, $errno, $errstr, 30);
    if (!$proxy_socket) {
        return false;
    }

    // HTTP-Anfrage an den Proxy senden
    $request = "GET {$url} HTTP/1.1\r\n";
    $request .= "Host: {$url_parts['host']}\r\n";
    $request .= "Proxy-Connection: Keep-Alive\r\n\r\n";
    fwrite($proxy_socket, $request);

    // Antwort des Proxys empfangen und zurückgeben
    $response = '';
    while (!feof($proxy_socket)) {
        $response .= fgets($proxy_socket, 128);
    }
    fclose($proxy_socket);

    return $response;
}

function check_access_limit($text, $user_id, $api, $defmaxcalls, $defmaxtextlength) {
  global $keys;
  global $keyval;
    $max_calls_per_minute = $defmaxcalls;//100;
    $access_time = 86400;//2592000; // Zeitlimit in Sekunden
    $max_text_length = $defmaxtextlength;

    // Pfad zur JSON-Datei
    $json_file = './user_data/' . $user_id .'_' .  $api . '.json'; //Kann später alles in eine Datei, ich will aber nicht, dass die gleichzeigtigen Aufrufe unterschidlicher APIs sich blocken könnten. Datenbank wäre also besser.

    $current_time = time();

    // Prüfen, ob JSON-Datei existiert
    if (!file_exists($json_file)) {
        // Datei existiert nicht, also erstellen
        $data = array(
            'count' => 0,
            'countmax' => $max_calls_per_minute,
            'zeichen' => 0,
            'max_zeichen' => $max_text_length,
            'timestamp' => $current_time,
            'counttimestamp' => $current_time
        );
        file_put_contents($json_file, json_encode($data));
    }

    // JSON-Daten auslesen
    $data = json_decode(file_get_contents($json_file), true);

    // Zeitlimit prüfen und Zähler zurücksetzen
    //$current_time = time();
    if ($data['timestamp'] + $access_time < $current_time) {
        $data['count'] = 0;
        $data['zeichen'] = 0;
        $data['timestamp'] = $current_time;
    }

    // Prüfen, ob das Limit erreicht wurde
    if ($keyval != $keys) {

    if ($data['count'] > $data['countmax']) {
      // Abrufen der Audio-Datei
      $audio = file_get_contents("./bettervoice.mp3");

      // Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt
      ob_clean();
      header('Content-type: audio/mpeg');

      // Ausgabe der Audio-Datei
      echo $audio;
        die('Zu viele Aufrufe pro Minute');
    }

    //Limit den Nutzer auf 1 Sekunde pro Aufruf.
    /*if (empty($data['counttimestamp'])) {
      $data['counttimestamp'] = $current_time;
    }*/
    if (!empty($data['counttimestamp'])) {
    //if (!(($data['counttimestamp'] + 1)  < $current_time)) {
    //if (!((intval($data['counttimestamp']) + 1)  < $current_time)) { //BUG BUG BUG BUG BUG BUG BUG
    //if (($data['counttimestamp'] + 1) >= $current_time) { //BUG BUG BUG BUG BUG BUG BUG
    if (($data['counttimestamp'] - 1) >= $current_time) { //Ja Browser soll sich ficken, der ruft alles doppelt auf Cacht was er will. Also ist das eigentlich unnötig, deshalb -1.
      // Abrufen der Audio-Datei
      $audio = file_get_contents("./bettervoice.mp3");

      // Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt
      ob_clean();
      header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'); //Bringt nichts.
      header('Content-type: audio/mpeg');

      // Ausgabe der Audio-Datei
      echo $audio;
      //echo $data['counttimestamp'] = $current_time;
      //die(echo $data['counttimestamp'] = $current_time;);
      //file_put_contents($json_file, json_encode($data));
        die('Zu viele Aufrufe pro Sekunde');
    }
    }

    $data['counttimestamp'] = $current_time;

    if ($data['zeichen'] > $data['max_zeichen']) {
      $audio = file_get_contents("./bettervoiceTextLimit.mp3");

      // Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt
      ob_clean();
      header('Content-type: audio/mpeg');

      // Ausgabe der Audio-Datei
      echo $audio;
        die('Zu viele Zeichen eingegeben oder du hast dein Limit erreicht.');
    }


    if (strlen($text) >= 537) {
            // Abrufen der Audio-Datei
            $audio = file_get_contents("./bettervoiceText.mp3");

            // Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt
            ob_clean();
            header('Content-type: audio/mpeg');

            // Ausgabe der Audio-Datei
            echo $audio;

        die('Text ist zu lang!');
    }

    // Zugriffszähler erhöhen und speichern
    $data['count']++;
    $data['zeichen'] += strlen($text);

    }

    // Daten speichern
    file_put_contents($json_file, json_encode($data));
}


function check_access_limit_global($text) {
  global $keys;
  global $keyval;
//if (strlen($text) >= 200) {
if (strlen($text) >= 200 && $keyval != $keys) {
        // Abrufen der Audio-Datei
        $audio = file_get_contents("./bettervoiceText.mp3");

        // Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt
        ob_clean();
        header('Content-type: audio/mpeg');

        // Ausgabe der Audio-Datei
        echo $audio;

    die('Text ist zu lang!');
}

    $max_calls_per_minute = 10;
    $access_file = 'access_counter.json';

    $access_data = json_decode(file_get_contents($access_file), true);

    // Zeitlimit prüfen und Zähler zurücksetzen
    $access_time = 600; // Zeitlimit in Sekunden
    $current_time = time();

    // Zugriffszähler erhöhen und speichern
    if ($keyval != $keys) {
    $access_data['count']++;
    file_put_contents($access_file, json_encode($access_data));
    }

    if ($access_data['count'] >= $max_calls_per_minute && $keyval != $keys) {
        // Limit erreicht, weitere Zugriffe blockieren

        // Abrufen der Audio-Datei
        $audio = file_get_contents("./bettervoice.mp3");

        // Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt
        ob_clean();
        header('Content-type: audio/mpeg');

        // Ausgabe der Audio-Datei
        echo $audio;

        die('Zu viele Aufrufe pro Minute');
    } else {
        if ($access_data['timestamp'] + $access_time < $current_time) {
        // Zeitlimit überschritten, Zähler zurücksetzen
        $access_data['count'] = 0;
        $access_data['timestamp'] = $current_time;
        file_put_contents($access_file, json_encode($access_data));
    }

   }
}
// Funktion aufrufen und Zugriff prüfen

require_once __DIR__ . '/vendor/autoload.php';
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

/**
 * @param string $text Text to synthesize
 */
// Pfad zur CREDENTIALS-Datei
putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/hidden_file/CREDENTIALS-Datei.json');

function synthesize_text(string $text): string
{
    // create client object
    $client = new TextToSpeechClient();

    $input_text = (new SynthesisInput())
        ->setText($text);

    // note: the voice can also be specified by name
    // names of voices can be retrieved with $client->listVoices()
    $voice = (new VoiceSelectionParams())
        ->setLanguageCode('de-DE')
        ->setName('de-DE-Wavenet-F')
        ->setSsmlGender(SsmlVoiceGender::FEMALE);

    $audioConfig = (new AudioConfig())
        ->setAudioEncoding(AudioEncoding::MP3);

    $response = $client->synthesizeSpeech($input_text, $voice, $audioConfig);
    $audioContent = $response->getAudioContent();

    //file_put_contents('output.mp3', $audioContent);
    //print('Audio content written to "output.mp3"' . PHP_EOL);
    $client->close();

    return $audioContent;
}

function curl(string $url) {
// Erstelle eine neue cURL-Ressource
$ch = curl_init();

// Setze die URL und andere Optionen
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //führt zu doppel Aufrufen ohne CURLOPT_HEADER.
curl_setopt($ch, CURLOPT_HEADER, false);

// Sende die Anfrage und speichere die Antwort in einer Variable
$response = curl_exec($ch);

// Schließe die cURL-Ressource
curl_close($ch);
return $response;
}

//session_start();


if ($apival == "1") { //Vertraue keinem Userinput.
  $apival = 1;
} else if ($apival == "2") {
  $apival = 2;
} else if ($apival == "3") {
  $apival = 3;
}

if (isset($_GET['text']) && !empty($_GET['text'])) {
  // Holen des Textes aus der GET-Anfrage
  $text = $_GET['text'];
  //if (isset($_SESSION['UserID']) && !empty($_SESSION['UserID'])) {

  // if (!isset($access_checked)) {
     // Funktion check_access_limit() wird nur aufgerufen, wenn $access_checked nicht existiert
  if ($user_id != null) {
    switch ($apival) {
        case 1:
        check_access_limit($text, $user_id, $apival,1000,1000);
            break;
        case 2:
        check_access_limit($text, $user_id, $apival,1000,1000);
            break;
        case 3:
        check_access_limit($text, $user_id, $apival,10000,50000);
            break;
    }
  } else {
    check_access_limit_global($text);
  }
  // $access_checked = true;
  // }
    //check_access_limit_global($text);
// Abrufen der Audio-Datei
$proxy_server = '68.183.209.54';
$proxy_port = '80';

if ($apival == 1) {
// Generierung der URL zum Abrufen der Audio-Datei
$url = 'https://translate.google.com/translate_tts?ie=UTF-8&q=' . urlencode($text) . '&tl=de&client=tw-ob';

//$audio = send_request_via_proxy($proxy_server, $proxy_port, $url);
  //$audio = file_get_contents($url);
  $audio = curl($url);
} else if ($apival == 2) {
  $audio = synthesize_text($text);
} else if ($apival == 3) {
//$url = 'http://sf.heldendesbildschirms.de:59125/api/tts?text=' . urlencode($text) . '&voice=de_DE%2Fm-ailabs_low%23angela_merkel&noiseScale=0.333&noiseW=0.333&lengthScale=1&ssml=false&audioTarget=client';

$server_select = false;
if (isset($_GET['server']) && !empty($_GET['server']) && is_numeric(intval($_GET['server']))) {
    $server_select = intval($_GET['server']);
}
$filename_server_worker_temp = '/var/www/hidden_file/1';
if (!file_exists($filename_server_worker_temp) && $server_select !== 2 || $server_select === 1) {
  file_put_contents($filename_server_worker_temp, null);
//  $url = 'http://s3.heldendesbildschirms.de:5002/api/tts?text=' . urlencode($text) . '&speaker_id=&style_wav=&language_id=';
  $url = 'http://195.90.221.83:5002/api/tts?text=' . urlencode($text) . '&speaker_id=&style_wav=&language_id=';
} else {
  $url = 'http://sf.heldendesbildschirms.de:5002/api/tts?text=' . urlencode($text) . '&speaker_id=&style_wav=&language_id=';
}

// $opts = array(
//     'socket' => array(
//         'bindto' => '[::]:0',
//     ),
//     'http' => array(
//         'header' => 'Connection: close\r\n',
//     ),
// );

//$context = stream_context_create($opts);

//  $audio = file_get_contents($url, false, $context); Ist super Langsam.

    $audio = curl($url);

    if (file_exists($filename_server_worker_temp)) {
      unlink($filename_server_worker_temp);
    }

    //$audio = file_get_contents($url);
    //file_put_contents('output.wav', $audio);
     //$audio = readfile("./output.wav");
     //$audio = file_get_contents("./output.wav");

/*header( 'Content-Type: audio/wav');
header("Content-Transfer-Encoding: binary");
header("Content-Length: 102476; ");
header('filename="output.wav"; ');*/
//header("Content-Length: " . filesize($audio ) ."; ");
//header('filename="'.$audio . '"; ');
  //$audio = file_get_contents($url);
} else {
  die("Diese API exestiert nicht.");
}

//Das sparrt Traffic.
// if(!empty($audio)){
//     ob_clean();
//     header('Cache-Control: cache');
//     header('Cache-Control: max-age=3600, public');
//     header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
// }

if(empty($audio)){
     $audio = file_get_contents("./bettervoice.mp3");

  if ($user_id != null) {
     //Anrage hat nicht funktioniert, Zeichen werden zurückerstattet.
     $json_file = './user_data/' . $user_id .'_' .  $api . '.json';
     // JSON-Daten auslesen
     $data = json_decode(file_get_contents($json_file), true);
     // Zugriffszähler und Zeichen werden zurückerstattet.
     $data['count']--;
     $data['zeichen'] -= strlen($text);

     // Daten speichern
     file_put_contents($json_file, json_encode($data));
   }
}


// Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt

if ($apival == 1 || $apival == 2) {
  ob_clean();
  // header('Cache-Control: cache');
  // header('Cache-Control: max-age=3600, public');
  // header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
  header('Content-type: audio/mpeg');
}
// if ($apival == 3) {
//   ob_clean();
//   // header('Cache-Control: cache');
//   // header('Cache-Control: max-age=3600, public');
//   // header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
//   header('Content-type: audio/x-wav');
// }

//header('Cache-Control: no-cache'); // Wieso macht man so etwas, die armen Daten.
//header('Content-Transfer-Encoding: binary');

//header('Content-type: ' . mime_content_type("./output.wav"));
//header('Content-type: audio/mpeg');
//header('Content-Length: ' . filesize("./output.wav") ."; "); // sets the legth of the file (in order to the HTML5 player to access the audio duration)
//header('Accept-Ranges: bytes');
//header('Content-Disposition: inline; filename="test_audio.wav"'); // set an output file name

// Ausgabe der Audio-Datei


if (isset($_GET['convert_format'])) {
      if ($_GET['convert_format'] === "aac" || $_GET['convert_format'] === "mp3") {
        $convert_format = $_GET['convert_format'];
        $convert = true;
        if (isset($_GET['convert_bitrate']) && is_numeric($_GET['convert_bitrate'])) {
          switch ((int)$_GET['convert_bitrate']) {
            case 0:
              $convert_bitrate = "96k";
              break;
            case 1:
              $convert_bitrate = "128k";
              break;
            case 2:
              $convert_bitrate = "192k";
              break;
           case 3:
              $convert_bitrate = "320k";
              break;
          }
        }
      }
}

if ($convert == true) {
    // Prüfe das Konvertierungsformat und Bitrate
    $convert_format = isset($convert_format) ? $convert_format : 'mp3'; // Standardwert ist mp3
    $convert_bitrate = isset($convert_bitrate) ? $convert_bitrate : '128k'; // Standardwert ist 128k

    $hfpa = "/var/www/hidden_file/tts/audio/";

    // Verzeichnisinhalt auflisten
    $files = scandir($hfpa);
    //check_user_id();
    // Anzahl der Dateien im Verzeichnis ermitteln (abzüglich der Einträge "." und ".." und "/temp")
    $file_count = count($files) - 3; //

    // Generiere den Dateinamen
    $baseFileName = "audio_" . $file_count . "";
    $newFileName = $baseFileName;

    // Überprüfe, ob die Datei bereits existiert, und finde einen neuen Dateinamen
    while (file_exists($hfpa . $newFileName)) {
        $file_count++; // Erhöhe den Counter
        $newFileName = "audio_" . $file_count . ""; // Neuer Dateiname
    }

    // Abhängig vom Konvertierungsformat die entsprechende Aktion durchführen
    file_put_contents($hfpa . "temp/" . $newFileName . ".wav", $audio);

    if ($convert_format == 'aac') {
        // Konvertiere zu AAC
        shell_exec("ffmpeg -i " . $hfpa ."temp/" . $newFileName . ".wav" . " -b:a " . $convert_bitrate . " " . $hfpa . $newFileName . ".m4a &");
        unlink($hfpa ."temp/" . $newFileName . ".wav");
        ob_clean();
        header('Content-type: audio/aac');
        $_SESSION['Userfile_format'] = ".m4a";
        echo file_get_contents($hfpa . $newFileName . ".m4a");
        unlink($hfpa . $newFileName . ".m4a");
    } elseif ($convert_format == 'mp3') {
        // Konvertiere zu MP3
        //shell_exec("ffmpeg -i /var/www/hidden_file/tts/audio/temp_" . $newFileName . "-b:a 128k /var/www/hidden_file/tts/audio/". $newFileName . "test &");
        shell_exec("ffmpeg -i " . $hfpa ."temp/" . $newFileName . ".wav" . " -b:a " . $convert_bitrate . " " . $hfpa . $newFileName . ".mp3 &");
        unlink($hfpa ."temp/" . $newFileName . ".wav");
        ob_clean();
        header('Content-type: audio/mp3');
        $_SESSION['Userfile_format'] = ".mp3";
        echo file_get_contents($hfpa . $newFileName . ".mp3");
        unlink($hfpa . $newFileName . ".mp3");
    }
} else {
    // Speichere die Audiodatei
    ob_clean();
    header('Content-type: audio/x-wav');
    file_put_contents($hfp . $newFileName . ".wav", $audio);
    $_SESSION['Userfile_format'] = ".wav";
    echo $audio;
}

}

/*if (isset($_GET['text']) && !empty($_GET['text']) && $keyval == "") {

    // Holen des Textes aus der GET-Anfrage
    $text = $_GET['text'];

    if (strlen($text) >= 200) {
        // Abrufen der Audio-Datei
        $audio = file_get_contents("./bettervoiceText.mp3");

        // Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt
        header('Content-type: audio/mpeg');

        // Ausgabe der Audio-Datei
        echo $audio;

        die('Text ist zu lang!');
    }

    // Generierung der URL zum Abrufen der Audio-Datei
    $url = 'https://translate.google.com/translate_tts?ie=UTF-8&q=' . urlencode($text) . '&tl=de&client=tw-ob';

    // Abrufen der Audio-Datei über einen Proxy-Server
    $proxy_server = '68.183.209.54';
    $proxy_port = '80';
    $audio = send_request_via_proxy($proxy_server, $proxy_port, $url);

    if(empty($audio)){
         $audio = file_get_contents("./bettervoice.mp3");
    }

    // Setzen des Headers, um dem Browser mitzuteilen, dass es sich um eine Audio-Datei handelt
    //header('Content-type: audio/mpeg');

    // Ausgabe der Audio-Datei
    echo $audio;
}*/
