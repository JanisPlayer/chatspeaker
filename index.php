<!DOCTYPE html>
<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
?>

<?php
//header('Access-Control-Allow-Origin: *');
// Speichern des Tokens in der Session
function getUserID(string $auth_token)  {
  $client_id = '4f4q2je3cxhhkqh9lp4c36qwa3dvyj';

  $api_url = 'https://api.twitch.tv/helix/users';

  $ch = curl_init($api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Authorization: Bearer ' . $auth_token,
      'Client-ID: ' . $client_id
  ));

  $response = curl_exec($ch);

  if(curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
  }

  curl_close($ch);

  $data = json_decode($response, true);

  if(isset($data['data'][0]['id'])) {
    if ((int)$data['data'][0]['id'] != null && (int)$data['data'][0]['id'] != 0 && is_numeric((int)$data['data'][0]['id']) != false) { //UserID darf nicht 0 oder leer oder keine Zahl sein.
      return (int)$data['data'][0]['id'];
    } else {
            die('Error: Failed to retrieve user ID.');
    }
  } else {
      die('Error: Failed to retrieve user ID.');
  }
}

session_set_cookie_params(86400); //Session Dauer 1 Tag
session_cache_expire(1440);

session_start();

//if (!isset($_SESSION['csrf_token'])) {
if (!isset($_SESSION['csrf_token']) && empty($_SESSION['csrf_token']) || empty($_COOKIE['csrf_token'])) {
  // Generieren des CSRF-Tokens
  $csrf_token = bin2hex(random_bytes(32));
  $_SESSION['csrf_token'] = $csrf_token;
  setcookie("csrf_token", $csrf_token, time()+3600, "/", "heldendesbildschirms.de", true, true);



  /*// Einbetten des Tokens als Cookier über JavaScript
    echo '<script> setCookie("csrf_token", "' . $csrf_token  . '", 30);
    	function setCookie(cname, cvalue, exdays) {
    			const d = new Date();
    			d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    			let expires = "expires=" + d.toUTCString();
    			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    		} </script>';*/
}
//}
// Überprüfen des Tokens bei der Formular-Submission
//if (isset($_POST['csrf_token']) && empty($_SESSION['csrf_token']) ) {
/*if (isset($_COOKIE['csrf_token']) && !empty($_COOKIE['csrf_token']) ) {
if ($_SESSION['csrf_token'] !== $_COOKIE['csrf_token']) {
  setcookie("csrf_token", $csrf_token, time()+3600, "/", "heldendesbildschirms.de", true, true);
}
}*/

//if (isset($_GET['code']) && !empty($_GET['code']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
if (isset($_GET['code']) && !empty($_GET['code']) && $_SESSION['csrf_token'] == $_COOKIE['csrf_token']) {
$client_id = '4f4q2je3cxhhkqh9lp4c36qwa3dvyj';
$client_secret = 'secret';
$redirect_uri = 'https://heldendesbildschirms.de/chatspeaker/';

//$authorization_code = $_GET['code'];
$authorization_code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);

$url = "https://id.twitch.tv/oauth2/token"
    . "?client_id={$client_id}"
    . "&client_secret={$client_secret}"
    . "&code={$authorization_code}"
    . "&grant_type=authorization_code"
    . "&redirect_uri={$redirect_uri}";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

curl_close($ch);
if (json_decode($response)->access_token != "") {
$access_token = "oauth:" . json_decode($response)->access_token;
$_SESSION["UserID"] = getUserID(json_decode($response)->access_token);
}
//echo $response;
} /*elseif (isset($_GET['code']) && !empty($_GET['code'])) {
$_COOKIE['YOUR COOKIE NAME'];*/

    // code...
  /*echo "<script>
  function getCookie(cname) {
    let name = cname + '=';
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return '';
  }

const csrf_token = getCookie('csrf_token');
const url = 'https://example.com/your_endpoint';
const params = `csrf_token=${encodeURIComponent(csrf_token)}`;

const xhr = new XMLHttpRequest();
xhr.open('POST', url, true);
xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

xhr.onreadystatechange = function() {
    if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
            console.log(xhr.responseText);
        } else {
            console.error('Error:', xhr.statusText);
        }
    }
};

xhr.send(params); </script>";*/

//}
?>

<html>

<head>
	<title>Twitch-Nachrichten vorlesen</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body>
  <h1>Twitch-Nachrichten vorlesen</h1>
	<button onclick="window.location = 'https://id.twitch.tv/oauth2/authorize?client_id=4f4q2je3cxhhkqh9lp4c36qwa3dvyj&redirect_uri=https://heldendesbildschirms.de/chatspeaker/&response_type=code&scope=chat:read'">Twich Login</button>
  <br><br>
	<label for="username_input">Username:</label>
	<input type="text" id="username_input"><br>

	<label for="token_input">Token:</label>
	<input type="password" id="token_input"><br>

	<label for="channel_input">Channel:</label>
	<input type="text" id="channel_input"><br>

	<button id="connect_button" onclick="init()">Connect</button>
  <br><br>
  <label for="slider">Sprech Geschwindikeit:</label>
  <input type="range" id="slider" name="slider" oninput="document.getElementById('sliderValue').innerHTML = document.getElementById('slider').value" min="0.1" max="2.0" value="1.0" step="0.1">
  <label id="sliderValue">1.0</label>
  <br>
  <label for="slider_min_zeichen">Minimale Zeichen:</label>
  <input type="range" id="slider_min_zeichen" name="slider" oninput="document.getElementById('sliderValue_min_zeichen').innerHTML = document.getElementById('slider_min_zeichen').value; //Platzhaltersollteeineventsein2() " min="1" max="500" value="1">
  <label id="sliderValue_min_zeichen">1</label>
  <br>
  <label for="slider_max_zeichen">Miximale Zeichen:</label>
  <input type="range" id="slider_max_zeichen" name="slider" oninput="document.getElementById('sliderValue_max_zeichen').innerHTML = document.getElementById('slider_max_zeichen').value; //Platzhaltersollteeineventsein2()" min="1" max="500" value="500">
  <label id="sliderValue_max_zeichen">500</label>
  <br>
  <label for="slider_autoreadDelayn">Vertögerung von Automatischen Nachrichten in Sek:</label>
  <input type="range" id="slider_autoreadDelayn" name="slider" oninput="document.getElementById('sliderValue_autoreadDelayn').innerHTML = document.getElementById('slider_autoreadDelayn').value" min="0" max="60" value="0">
  <label id="sliderValue_autoreadDelayn">0</label>
  <br>
  <label for="slider_TTS">TTS wählen:</label>
  <input type="range" id="slider_TTS" name="slider" oninput="document.getElementById('sliderValue_TTS').innerHTML = getText(document.getElementById('slider_TTS').value)" min="0" max="4" value="0">
  <label id="sliderValue_TTS">Browser TTS</label>
  <br>
  <label for="slider_volume">Lautstärke:</label>
  <input type="range" id="slider_volume" name="slider" oninput="document.getElementById('sliderValue_volume').innerHTML = setVolume(document.getElementById('slider_volume').value)" min="0" max="100" value="100">
  <label id="sliderValue_volume">100</label>
  <br>
  <label for="key_input" id="key_input_label" style="display:none;">Dein Zugriffscode:</label>
  <input type="password" id="key_input" style="display:none;">
  <label for="eigenes_tts_input" id="eigenes_tts_label" style="display:none;">Deine TTS Server URL:</label>
  <input type="password" id="eigenes_tts_input" style="display:none;">
  <label for="eigenes_tts_show" id="eigenes_tts_show_label" style="display:none;">URL anzeigen</label>
  <input type="checkbox" id="eigenes_tts_show" onclick="showPassword('eigenes_tts_input')">
  <script>
    function showPassword(eid) {
        var passwordInput = document.getElementById(eid);

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
        } else {
            passwordInput.type = "password";
        }
    }
  </script>
  <br>
  Dieser Wert wird gesetzt, wennn Minimale Aufrufe aktiviert überschritten wurde. <br>
  <label for="slider_min_aufrufe">Minimale Aufrufe pro Minute:</label>
  <input type="range" id="slider_min_aufrufe" name="slider" oninput="document.getElementById('sliderValue_min_aufrufe').innerHTML = document.getElementById('slider_min_aufrufe').value;" min="1" max="60" value="60">
  <label id="sliderValue_min_aufrufe">60</label>
  <br>
  <label for="slider_max_aufrufe_um_minimalte_aufrufe_zu_aktivieren">Minimale Aufrufe aktiviert wenn diese Aufrufe erreicht sind:</label>
  <input type="range" id="slider_max_aufrufe_um_minimalte_aufrufe_zu_aktivieren" name="slider" oninput="document.getElementById('sliderValue_max_aufrufe_um_minimalte_aufrufe_zu_aktivieren').innerHTML = document.getElementById('slider_max_aufrufe_um_minimalte_aufrufe_zu_aktivieren').value;" min="1" max="60" value="60">
  <label id="sliderValue_max_aufrufe_um_minimalte_aufrufe_zu_aktivieren">60</label>
  <br>
  <label for="slider_max_aufrufe">Maximale Aufrufe pro Minute:</label>
  <input type="range" id="slider_max_aufrufe" name="slider" oninput="document.getElementById('sliderValue_max_aufrufe').innerHTML = document.getElementById('slider_max_aufrufe').value;" min="1" max="60" value="60">
  <label id="sliderValue_max_aufrufe">60</label>
  <br>
  <label for="select_model">Select Model:</label>
  <select class="" id="select_model" name="select_model">
    <option value="0">de/thorsten/tacotron2-DDC</option>
  </select>
  <br>
  <label for="select_format">Select Format:</label>
  <select class="" id="select_format" name="select_format">
    <option value="wav">wav</option>
    <option value="aac">aac</option>
    <option value="mp3">mp3</option>
  </select>
  <br>
  <label for="select_bitrate">Select Bitrate:</label>
  <select class="" id="select_bitrate" name="select_bitrate">
    <option value="0">96 Kbits</option>
    <option value="1">128 Kbits</option>
    <option value="2">256 Kbits</option>
    <option value="3">320 Kbits</option>
  </select>
  <br>
  <label for="select_server">Force select server:</label>
  <select class="" id="select_server" name="server">
    <option value="false">Auto Load Balance</option>
    <option value="1">VPS 4c AMD EPYC 7513</option>
    <option value="2">NUC i5-8259U</option>
  </select>
  <br>

  <label for="api_counter" id="api_counter_label" style="display:none;">Ja was weiß ich:</label>

  <br><br>
  <button id="audio_pause_button" onclick="audio.pause()">Pause</button>
  <!-- <button id="audio_stop_id_button" onclick="audio_stop_id = 'all'; setTimeout(function() {audio_stop_id = 0;}, 3000);">Alle geplanten Nachrichten abbrechen</button> -->
  <button id="audio_stop_id_button" onclick="audio_stop('all');">Alle geplanten Nachrichten abbrechen</button>
	<h1>Liste von Namen die erlaubt sind</h1>

	<script>

  var Global_Volume = 100;

  function setVolume(volumevalue)  {
    audio.volume = volumevalue / 100;
    //return audio.volume;
   Global_Volume = volumevalue;
   return Global_Volume;
  }

  function toggle(el) {
    el = document.getElementById(el);
    el.style.display = el.style.display != 'none' ? 'none' : '';
  }

  var api = 0;
  var key = "";

  function getText(sliderValue) {
    if (sliderValue == 2) {
        document.getElementById("key_input").style.display = "";
        document.getElementById("key_input_label").style.display = "";
    }else {
        document.getElementById("key_input").style.display = "none";
        document.getElementById("key_input_label").style.display = "none";
    }

    if (sliderValue == 4) {
        document.getElementById("eigenes_tts_input").style.display = "";
        document.getElementById("eigenes_tts_label").style.display = "";
        document.getElementById("eigenes_tts_show").style.display = "";
        document.getElementById("eigenes_tts_show_label").style.display = "";
    }else {
        document.getElementById("eigenes_tts_input").style.display = "none";
        document.getElementById("eigenes_tts_label").style.display = "none";
        document.getElementById("eigenes_tts_show").style.display = "none";
        document.getElementById("eigenes_tts_show_label").style.display = "none";
    }

		//setCookie("sliderTTSValue", sliderValue, 30);
    api = sliderValue;

    if (sliderValue == 0) {
      document.getElementById("api_counter_label").style.display = "none";
      return "Browser TTS";
    } else if (sliderValue == 1) {
      document.getElementById("api_counter_label").style.display = "";
      return "Google Übersetzer TTS";
    } else if (sliderValue == 2) {
      document.getElementById("api_counter_label").style.display = "";
      return "Google API TTS";
      document.getElementById("api_counter_label").style.display = "";
    } else if (sliderValue == 3) {
      return "HeldendesBildschirms TTS";
    } else if (sliderValue == 4) {
      return "Eigenes TTS";
    }

  }

    function update_counter() {
      if (api <= 3  && api >= 1) {
      var jf = new XMLHttpRequest();
      jf.open('GET', './user_data/' + user_id + '_' +  api + '.json', false);
      //jf.open('GET', 'https://heldendesbildschirms.de/chatspeaker/user_data/' + user_id + '_' +  api + '.json', false);
      jf.send(null)
      //console.log(jf);
      var count = JSON.parse(jf.response).count;
      var countmax = JSON.parse(jf.response).countmax;
      var zeichen = JSON.parse(jf.response).zeichen;
      var zeichenmax = JSON.parse(jf.response).max_zeichen;
      document.getElementById("api_counter_label").innerHTML = 'Aufrufe: ' + (countmax - count) + ' Zeichen: ' +  (zeichenmax - zeichen) + ' verfügbar';
      }
    }

		var whitelist_names = [];
		var whitelist_names_HTMLElements = [];

    function getUrlParam(Param) {
      var url_string = window.location;
      var url = new URL(url_string);
      return url.searchParams.get(Param);
    }

		function addNamebyElement() {
			// get input value
			var nameInput = document.getElementById("name-input");
			addName(nameInput.value, false, 0, true);
		}

		function addName(newName, ban, max_counts, Cookie) {
			index = checkNames(newName)
			if (checkNames(newName) === false) { // add new name to list
				var nameInput = document.getElementById("name-input");
				var nameList = document.getElementById("name-list");
				var newItem = document.createElement("li");

        //newItem.textContent = newName;
        //newItem.setAttribute("href", "#");
        //newItem.addEventListener("click", function() {
        //  addName(newName, true);
        //});

        var newItemname = document.createElement("label");
				newItemname.textContent = newName;
				newItemname.setAttribute("href", "#");
				newItemname.addEventListener("click", function() {
					addName(newName, ban, 0, true);
				});

        var newItemspace = document.createElement("label");
				newItemspace.textContent = " ";

        var newItemban = document.createElement("label");

        //newItemban.for="li"
        if (ban == false) {
          newItemban.textContent = "++";
        } else {
        	newItemban.textContent = "--";
        }
				newItemban.setAttribute("href", "#");
        var newNametemp = newName;
				newItemban.addEventListener("click", function() {
					Namesbann(checkNames(newNametemp));
				});

        var newItemspace2 = document.createElement("label");
        newItemspace2.textContent = " ";

        // <label for="slider_max_aufrufe">Maximale Aufrufe pro Minute:</label>
        // <input type="range" id="slider_max_aufrufe" name="slider" oninput="document.getElementById('sliderValue_max_aufrufe').innerHTML = document.getElementById('slider_max_aufrufe').value;" min="1" max="60" value="60">
        // <label id="sliderValue_max_aufrufe">60</label>

        var counts = 0;
        var newItem_counts_label = document.createElement("label");
        newItem_counts_label.textContent = counts;

        var newItem_max_counts_label = document.createElement("label");
        newItem_max_counts_label.textContent = max_counts;

        var newItem_max_counts_slider = document.createElement("input");
        newItem_max_counts_slider.type="range"
        newItem_max_counts_slider.name = "slider";
        newItem_max_counts_slider.value = 0;
        newItem_max_counts_slider.min = 0;
        newItem_max_counts_slider.max = 60;
        //newItem_max_counts_slider.oninput = "document.getElementById("+newItem_max_counts_label+").innerHTML = document.getElementById('"+newItem_max_counts_slider+"').value;";
        newItem_max_counts_slider.oninput = function() {
          newItem_max_counts_label.innerHTML = newItem_max_counts_slider.value;

          //document.getElementById(newItem_max_counts_label).innerHTML = document.getElementById(newItem_max_counts_slider).value;
        };

				nameList.appendChild(newItem);
      	newItem.appendChild(newItemban);
        newItem.appendChild(newItemspace);
      	newItem.appendChild(newItemname);
        newItem.appendChild(newItemspace2);
        newItem.appendChild(newItem_counts_label);
        newItem.appendChild(newItem_max_counts_slider);
        newItem.appendChild(newItem_max_counts_label);

        whitelist_names.push({name: newName, ban: ban, counts: counts, max_counts: max_counts});

				/*const userLink = document.createElement("a");
				userLink.setAttribute("href", "#");
				userLink.textContent = newName;
				userLink.addEventListener("click", function() {
					addName(newName,true)
				});
				nameList.appendChild(userLink);*/


				//whitelist_names.push(newItem)
				// clear input field
				nameInput.value = "";
				if (Cookie == true) {
					addNamesAsCookie(newName)
				}
			} else {
				whitelist_names.splice(index, 1);
				//alert(whitelist_names)
				var nameList = document.getElementById("name-list");
				nameList.removeChild(nameList.childNodes[(index)]);
				setCookie("names", JSON.stringify(whitelist_names), 30);
			}
		}

		//document.addEventListener("DOMContentLoaded", () => {
		function addNamesAsCookie(newName) {
			//var nameList = document.getElementById("name-list");
			//var names = document.getElementsByTagName("li");

			// loop through names and check for match

			//	let names = ["Alice", "Bob", "Charlie"];
			//	for (var i = 0; i < whitelist_names.length; i++) {

			//whitelist_names.push(whitelist_names[i])//.textContent) crash wtf habe ich da für blödsinn gemacht. :D
			//	}

			// ein Array mit Namen
			//whitelist_names.push({name: newName, ban: false});//.textContent) //Das muss als Variable weg sonnst dupliziert es sich.
			// das Array als JSON-Zeichenkette speichern
			//document.cookie = "names=" + JSON.stringify(names);
			//setCookie("names", JSON.stringify(whitelist_names), 30);
			setCookie("names", JSON.stringify(whitelist_names), 30);
		}

		function ReadNamesAsCookie() {
			// das Array aus dem Cookie lesen und wieder in ein JavaScript-Array umwandeln
			//let cookieValue = document.cookie.replace(/(?:(?:^|.*;\s*)names\s*\=\s*([^;]*).*$)|^.*$/, "$1");
			//let parsedValue = JSON.parse(cookieValue);
      var names = getCookie("names")

      if (names != "undefine" && names != "") {
    	//whitelist_names = JSON.parse(names)
      var whitelist_names_temp = JSON.parse(names)
        for (var i = 0; i < whitelist_names_temp.length; i++) {
            addName(whitelist_names_temp[i].name, whitelist_names_temp[i].ban, 0, false)
        }
      }
		}


		//});

    function Namesbann(index) {
      if (whitelist_names[index].ban == false) {
        whitelist_names[index].ban = true;
        document.getElementById("name-list").childNodes[index].childNodes[0].textContent = "--";
      } else {
        whitelist_names[index].ban = false;
        document.getElementById("name-list").childNodes[index].childNodes[0].textContent  = "++";
      }
        addNamesAsCookie("trash");
    }

    function checkNamesbanned(index) {
      if (whitelist_names[index] && whitelist_names[index].ban == true) {
        return true;
      }
      return false;
    }

    /*if (names[i].textContent.charAt(0) === "-") {
      return false;
    }*/

		function checkNamesHTMLElement(name) {
			// get list of names
			var nameList = document.getElementById("name-list");
			var names = nameList.getElementsByTagName("li");

			// loop through names and check for match
			for (var i = 0; i < names.length; i++) {
				if (names[i].textContent === name) {
					return i;
					break;
				}
			}
			return false;
		}

    function checkNames(name) {
      for (var i = 0; i < whitelist_names.length; i++) {
        if (whitelist_names[i].name === name) {
          return i;
          break;
        }
      }
      return false;
    }

    function Platzhaltersollteeineventsein() {
      var min = document.getElementById('slider_min_zeichen');
      var max = document.getElementById('slider_max_zeichen');
      if (min.value <= max.value) {
        max.value = min.value;
      }
    }

    function Platzhaltersollteeineventsein2() {
      var min = document.getElementById('slider_min_zeichen');
      var max = document.getElementById('slider_max_zeichen');
      if (min.value >= max.value) {
        max.value = min.value;
      }
    }
	</script>

	<input type="text" id="name-input">
	<button onclick="addNamebyElement()">Hinzufügen & löschen</button>
	<!-- <button onclick="checkNames()">Namen löschen</button> -->
	<ul id="name-list"></ul>

	<div class="chat" style="height: 200px; overflow-y: scroll;">
	</div>

	<!-- <button id="speak">Nachrichten vorlesen</button> -->

	<script>
		// Nachrichten vorlesen
		function speakMessage(message) {
			const synth = window.speechSynthesis;
			const utterance = new SpeechSynthesisUtterance(message);
      utterance.volume = Global_Volume / 100;
			synth.speak(utterance);
		}

      var audio_stop_id = [];
      var audio = new Audio();

    //Diese Funktion fügt ein zu löschendes Element dem Array hinzu und entfernt es nach 3 Sek wieder aus dem Array.
    //Der Timer scannt jede Sekunde nach allen Elementen und überprüft ob sie die Voraussetzung zum beenden erfüllen, also ob User oder Nachricht vorhanden sind, sind diese Voraussetzungen erfüllt wird der Timer beendet und die Nachricht wird nicht vorgelesen.
    function audio_stop(id) {
      audio_stop_id.push(id);
        setTimeout(function() {
        let index = audio_stop_id.indexOf(id);
          if (index !== -1) {
          audio_stop_id.splice(index, 1);
        }
      }, 3000);
    }

		function speakMessagetranslate(message) {
      if (audio.paused == true) {
			audio = new Audio();
      audio.volume = Global_Volume / 100;
			//audio.src = 'https://translate.google.com/translate_tts?ie=UTF-8&q=' + encodeURI(message) + '&tl=de&client=tw-ob';
      var slider_TTS = document.getElementById('slider_TTS').value //muss später in eine global war beim ändern geschrieben werden.
      var convert_format = document.getElementById('select_format');
      var convert_bitrate = document.getElementById('select_bitrate');
      var select_server_val = document.getElementById('select_server');
			audio.src = 'https://heldendesbildschirms.de/chatspeaker/bettervoice.php?text=' + message + '&api=' + slider_TTS + '&convert_format=' + convert_format + '&convert_bitrate=' + convert_bitrate + '&server=' + select_server_val + '&key=' + key;
      //var sliderValue = document.getElementById("slider").value; // Beispielwert zwischen 0 und 20
      //var floatValue = (sliderValue / 10) + 0.1; // Beispielwert zwischen 0,1 und 2,0
      //audio.playbackRate = floatValue;
      audio.playbackRate = document.getElementById("slider").value;
      audio.addEventListener('canplaythrough', function() {
        audio.play();
      });

      /*              const stopmsg = setInterval(() => {
                      // Wenn das Audio pausiert ist, die Aktion ausführen
                      if (audio.paused) {
                        clearInterval(timer);
                      }
                      if (audio_stop_id == id) {
                        // Führen Sie hier Ihre Aktion aus
                        audio.stop();
                        // Timer beenden
                        clearInterval(timer);
                      }
                    }, 1000); // Timer alle 1000 Millisekunden ausführen
                  }*/

    } else {
      // Timer initialisieren
const timer = setInterval(() => {
  // Wenn das Audio pausiert ist, die Aktion ausführen

  if (audio_stop_id === "all" ) {
    clearInterval(timer);
  }

  if (audio.paused) {
    // Führen Sie hier Ihre Aktion aus
    speakMessageSelectTTS(message)
    // Timer beenden
    clearInterval(timer);
  }
}, 1000); // Timer alle 1000 Millisekunden ausführen
    }
		}

    function speakMessagegoogleTTS(message) {
      if (audio.paused == true) {
      audio = new Audio();
      audio.volume = Global_Volume / 100;
      //audio.src = 'https://translate.google.com/translate_tts?ie=UTF-8&q=' + encodeURI(message) + '&tl=de&client=tw-ob';
      var slider_TTS = document.getElementById('slider_TTS').value //muss später in eine global war beim ändern geschrieben werden.
      audio.src = 'https://heldendesbildschirms.de/chatspeaker/bettervoice.php?text=' + message + '&api=' + slider_TTS + '&key=' + key;

      audio.playbackRate = document.getElementById("slider").value;
      audio.addEventListener('canplaythrough', function() {
        audio.play();
      });

    } else {
      // Timer initialisieren
const timer = setInterval(() => {
  // Wenn das Audio pausiert ist, die Aktion ausführen
  for (var i = 0; i < audio_stop_id.length; i++) {
    if (audio_stop_id === "all" ) {
      clearInterval(timer);
    }
  }
  if (audio.paused) {
    // Führen Sie hier Ihre Aktion aus
    speakMessageSelectTTS(message)
    // Timer beenden
    clearInterval(timer);
  }
}, 1000); // Timer alle 1000 Millisekunden ausführen
    }
    }

    function speakMessageMyTTS(message) {
      if (audio.paused == true) {
      audio = new Audio();
      audio.volume = Global_Volume / 100;
      //audio.src = 'https://translate.google.com/translate_tts?ie=UTF-8&q=' + encodeURI(message) + '&tl=de&client=tw-ob';
      var slider_TTS = document.getElementById('slider_TTS').value //muss später in eine global war beim ändern geschrieben werden.
      audio.src = 'https://heldendesbildschirms.de/chatspeaker/bettervoice.php?text=' + message + '&api=' + slider_TTS + '&key=' + key;

      //Das möchte ich vielleicht noch Hinzufügen, falls zulange keine Antwort kommt, dass dann ein anderes TSS genutzt wird.
      /*
      var timeoutMilliseconds = 5000; // Zeit in Millisekunden (hier 5 Sekunden)

      var timeout = setTimeout(function() {
        // Diese Funktion wird nach Ablauf des Timeouts aufgerufen
        andereFunktion();
      }, timeoutMilliseconds);

      function andereFunktion() {
        // Code für die andere Funktion hier einfügen
        console.log("Timeout erreicht. Andere Funktion wird aufgerufen.");
      }

      // Beispielhafte Verwendung mit Audio-Quelle
      var audio = new Audio();
      audio.src = 'https://heldendesbildschirms.de/chatspeaker/bettervoice.php?text=' + message + '&api=' + slider_TTS + '&key=' + key;

      // Wenn der Timeout abgelaufen ist, wird die andereFunktion aufgerufen
      timeout;
      */
      /*
      var timeoutMilliseconds = 5000; // Zeit in Millisekunden (hier 5 Sekunden)

      var controller = new AbortController();
      var signal = controller.signal;

      var timeout = setTimeout(function() {
        // Diese Funktion wird nach Ablauf des Timeouts aufgerufen
        controller.abort(); // Abbruch des Requests
        andereFunktion();
      }, timeoutMilliseconds);

      fetch('https://heldendesbildschirms.de/chatspeaker/bettervoice.php?text=' + message + '&api=' + slider_TTS + '&key=' + key, { signal })
        .then(function(response) {
          // Erfolgreiche Antwort verarbeiten
          clearTimeout(timeout); // Timeout löschen, da der Request erfolgreich war
          return response.json();
        })
        .then(function(data) {
          // Daten verarbeiten
        })
        .catch(function(error) {
          if (error.name === 'AbortError') {
            // Der Request wurde abgebrochen (Timeout erreicht)
            // Hier können Sie den entsprechenden Fehlerbehandlungscode einfügen
          } else {
            // Anderer Fehler trat auf
            console.error(error);
          }
        });

      function andereFunktion() {
        // Code für die andere Funktion hier einfügen
        console.log("Timeout erreicht. Andere Funktion wird aufgerufen.");
      }
      */

      audio.playbackRate = document.getElementById("slider").value;
      audio.addEventListener('canplaythrough', function() {
        audio.play();
      });

    } else {
      // Timer initialisieren
const timer = setInterval(() => {
  // Wenn das Audio pausiert ist, die Aktion ausführen
  for (var i = 0; i < audio_stop_id.length; i++) {
    if (audio_stop_id === "all" ) {
      clearInterval(timer);
    }
  }
  if (audio.paused) {
    // Führen Sie hier Ihre Aktion aus
    speakMessageSelectTTS(message)
    // Timer beenden
    clearInterval(timer);
  }
}, 1000); // Timer alle 1000 Millisekunden ausführen
    }
    }

    function speakMessageTTSbyURL(message) {
      if (audio.paused == true) {
      audio = new Audio();
      audio.volume = Global_Volume / 100;
      var slider_TTS = document.getElementById('slider_TTS').value //muss später in eine global war beim ändern geschrieben werden.
      var baseUrl = document.getElementById('eigenes_tts_input').value;
      var modifiedUrl = baseUrl.replace(/text=.*?/, "text=" + message)
      audio.src = modifiedUrl;

      audio.playbackRate = document.getElementById("slider").value;
      audio.addEventListener('canplaythrough', function() {
        audio.play();
      });

    } else {
      // Timer initialisieren
const timer = setInterval(() => {
  // Wenn das Audio pausiert ist, die Aktion ausführen
  for (var i = 0; i < audio_stop_id.length; i++) {
    if (audio_stop_id === "all" ) {
      clearInterval(timer);
    }
  }
  if (audio.paused) {
    // Führen Sie hier Ihre Aktion aus
    speakMessageSelectTTS(message)
    // Timer beenden
    clearInterval(timer);
  }
}, 1000); // Timer alle 1000 Millisekunden ausführen
    }
    }

    function speakMessageDelay(user, message, delay, id) {
      var use = false;
      var i = 0;
      const stopmsg = setInterval(() => {
        i++;
        // Wenn das Audio pausiert ist, die Aktion ausführen
        /*if (audio.paused && use == true) {
          clearInterval(stopmsg);
        }*/
        for (var si = 0; si < audio_stop_id.length; si++) {
          if (audio_stop_id[si] == id || audio_stop_id[si] === "all" || audio_stop_id[si] === message || audio_stop_id[si] === user) {
            // Führen Sie hier Ihre Aktion aus
            // Timer beenden
            clearInterval(stopmsg);
          }
        }

        if (i == delay) {
          speakMessageSelectTTS(createMessage(user,message))
          clearInterval(stopmsg);
        }
        use = true;
      }, 1000); // Timer alle 1000 Millisekunden ausführen
    }

    function speakMessageSelectTTS(message) {
      var sliderValue = document.getElementById('slider_TTS').value
      if (sliderValue == 0) {
        speakMessage(message);
      } else if (sliderValue == 1) {
        speakMessagetranslate(message);
      } else if (sliderValue == 2) {
        speakMessagegoogleTTS(message);
      } else if (sliderValue == 3) {
        speakMessageMyTTS(message);
      } else if (sliderValue == 4) {
        speakMessageTTSbyURL(message);
    }
      update_counter();
    }

		var lastUser = "";
    function createMessage(user, message) {
          if (lastUser != user) {
                lastUser = user;
            return (user + " schreibt: " +  message);
          } else {
            return (message);
          }
      }

      function scrollToBottom() {
  const messages = document.getElementsByClassName("chat")[0];
  messages.scrollTop = messages.scrollHeight;
}
		function getEmotes(emote_id) {
      fetch('https://api.twitch.tv/helix/chat/emotes/set?emote_set_id=' + emote_id, {
        headers: {
          'Authorization': 'Bearer ' + token.substring(6),
          'Client-ID': "4f4q2je3cxhhkqh9lp4c36qwa3dvyj"
        }
      })
        .then(response => response.json())
        .then(data => {
          console.log(JSON.stringify(data));
          alert(JSON.stringify(data));
          return JSON.stringify(data);
      })
        .catch(error => console.error(error)) ;
    }


		function parseMessage(message) {

			message = String(message)

			let parsedMessage = { // Contains the component parts.
				tags: null,
				source: null,
				command: null,
				parameters: null
			};

			// The start index. Increments as we parse the IRC message.

			let idx = 0;

			// The raw components of the IRC message.

			let rawTagsComponent = null;
			let rawSourceComponent = null;
			let rawCommandComponent = null;
			let rawParametersComponent = null;

			// If the message includes tags, get the tags component of the IRC message.

			if (message[idx] === '@') { // The message includes tags.
				let endIdx = message.indexOf(' ');
				rawTagsComponent = message.slice(1, endIdx);
				idx = endIdx + 1; // Should now point to source colon (:).
			}

			// Get the source component (nick and host) of the IRC message.
			// The idx should point to the source part; otherwise, it's a PING command.

			if (message[idx] === ':') {
				idx += 1;
				let endIdx = message.indexOf(' ', idx);
				rawSourceComponent = message.slice(idx, endIdx);
				idx = endIdx + 1; // Should point to the command part of the message.
			}

			// Get the command component of the IRC message.

			let endIdx = message.indexOf(':', idx); // Looking for the parameters part of the message.
			if (-1 == endIdx) { // But not all messages include the parameters part.
				endIdx = message.length;
			}

			rawCommandComponent = (message.slice(idx, endIdx)).trim();

			// Get the parameters component of the IRC message.

			if (endIdx != message.length) { // Check if the IRC message contains a parameters component.
				idx = endIdx + 1; // Should point to the parameters part of the message.
				rawParametersComponent = message.slice(idx);
			}

			// Parse the command component of the IRC message.

			parsedMessage.command = parseCommand(rawCommandComponent);

			// Only parse the rest of the components if it's a command
			// we care about; we ignore some messages.

			if (null == parsedMessage.command) { // Is null if it's a message we don't care about.
				return null;
			} else {
				if (null != rawTagsComponent) { // The IRC message contains tags.
					parsedMessage.tags = parseTags(rawTagsComponent);
				}

				parsedMessage.source = parseSource(rawSourceComponent);

				parsedMessage.parameters = rawParametersComponent;
				if (rawParametersComponent && rawParametersComponent[0] === '!') {
					// The user entered a bot command in the chat window.
					parsedMessage.command = parseParameters(rawParametersComponent, parsedMessage.command);
				}
			}

			return parsedMessage;
		}

		// Parses the tags component of the IRC message.

		function parseTags(tags) {
			// badge-info=;badges=broadcaster/1;color=#0000FF;...

			const tagsToIgnore = { // List of tags to ignore.
				'client-nonce': null,
				'flags': null
			};

			let dictParsedTags = {}; // Holds the parsed list of tags.
			// The key is the tag's name (e.g., color).
			let parsedTags = tags.split(';');

			parsedTags.forEach(tag => {
				let parsedTag = tag.split('='); // Tags are key/value pairs.
				let tagValue = (parsedTag[1] === '') ? null : parsedTag[1];

				switch (parsedTag[0]) { // Switch on tag name
					case 'badges':
					case 'badge-info':
						// badges=staff/1,broadcaster/1,turbo/1;

						if (tagValue) {
							let dict = {}; // Holds the list of badge objects.
							// The key is the badge's name (e.g., subscriber).
							let badges = tagValue.split(',');
							badges.forEach(pair => {
								let badgeParts = pair.split('/');
								dict[badgeParts[0]] = badgeParts[1];
							})
							dictParsedTags[parsedTag[0]] = dict;
						} else {
							dictParsedTags[parsedTag[0]] = null;
						}
						break;
					case 'emotes':
						// emotes=25:0-4,12-16/1902:6-10

						if (tagValue) {
							let dictEmotes = {}; // Holds a list of emote objects.
							// The key is the emote's ID.
							let emotes = tagValue.split('/');
							emotes.forEach(emote => {
								let emoteParts = emote.split(':');

								let textPositions = []; // The list of position objects that identify
								// the location of the emote in the chat message.
								let positions = emoteParts[1].split(',');
								positions.forEach(position => {
									let positionParts = position.split('-');
									textPositions.push({
										startPosition: positionParts[0],
										endPosition: positionParts[1]
									})
								});

								dictEmotes[emoteParts[0]] = textPositions;
							})

							dictParsedTags[parsedTag[0]] = dictEmotes;
						} else {
							dictParsedTags[parsedTag[0]] = null;
						}

						break;
					case 'emote-sets':
						// emote-sets=0,33,50,237

						let emoteSetIds = tagValue.split(','); // Array of emote set IDs.
						dictParsedTags[parsedTag[0]] = emoteSetIds;
						break;
					default:
						// If the tag is in the list of tags to ignore, ignore
						// it; otherwise, add it.

						if (tagsToIgnore.hasOwnProperty(parsedTag[0])) {
							;
						} else {
							dictParsedTags[parsedTag[0]] = tagValue;
						}
				}
			});

			return dictParsedTags;
		}

		// Parses the command component of the IRC message.

		function parseCommand(rawCommandComponent) {
			let parsedCommand = null;
			commandParts = (rawCommandComponent).split(' ');

			switch (commandParts[0]) {
				case 'JOIN':

				case 'PART':
				case 'NOTICE':
				case 'CLEARCHAT':
				case 'HOSTTARGET':
				case 'PRIVMSG':
					parsedCommand = {
						command: commandParts[0],
						channel: commandParts[1]
					}
					break;
        case 'CLEARMSG':
          parsedCommand = {
            command: commandParts[0],
						channel: commandParts[1]
          }
          break;
        case 'CLEARCHAT':
            parsedCommand = {
              command: commandParts[0],
  						channel: commandParts[1]
          }
          break;
				case 'PING':
					parsedCommand = {
						command: commandParts[0]
					}
					break;
				case 'CAP':
					parsedCommand = {
						command: commandParts[0],
						isCapRequestEnabled: (commandParts[2] === 'ACK') ? true : false,
						// The parameters part of the messages contains the
						// enabled capabilities.
					}
					break;
				case 'GLOBALUSERSTATE': // Included only if you request the /commands capability.
					// But it has no meaning without also including the /tags capability.
					parsedCommand = {
						command: commandParts[0]
					}
					break;
				case 'USERSTATE': // Included only if you request the /commands capability.
				case 'ROOMSTATE': // But it has no meaning without also including the /tags capabilities.
					parsedCommand = {
						command: commandParts[0],
						channel: commandParts[1]
					}
					break;
				case 'RECONNECT':
					console.log('The Twitch IRC server is about to terminate the connection for maintenance.')
					parsedCommand = {
						command: commandParts[0]
					}
					break;
				case '421':
					console.log(`Unsupported IRC command: ${commandParts[2]}`)
					return null;
				case '001': // Logged in (successfully authenticated).
					parsedCommand = {
						command: commandParts[0],
						channel: commandParts[1]
					}
					break;
				case '002': // Ignoring all other numeric messages.
				case '003':
				case '004':
				case '353': // Tells you who else is in the chat room you're joining.
				case '366':
				case '372':
				case '375':
				case '376':
					console.log(`numeric message: ${commandParts[0]}`)
					return null;
				default:
					console.log(`\nUnexpected command: ${commandParts[0]}\n`);
					return null;
			}

			return parsedCommand;
		}

		// Parses the source (nick and host) components of the IRC message.

		function parseSource(rawSourceComponent) {
			if (null == rawSourceComponent) { // Not all messages contain a source
				return null;
			} else {
				let sourceParts = rawSourceComponent.split('!');
				return {
					nick: (sourceParts.length == 2) ? sourceParts[0] : null,
					host: (sourceParts.length == 2) ? sourceParts[1] : sourceParts[0]
				}
			}
		}
		/*function parseSource(source) {
  if (null === source) {
    return null;
  }

  let nick = null;
  let host = null;
  let idx = source.indexOf('!');

  if (-1 != idx) { // The IRC message includes a nickname.
    nick = source.slice(0, idx);
    idx += 1;
    host = source.slice(idx);
  } else {
    nick = source;
  }

  return {
    nick: nick,
    host: host
  };
}*/

		// Parsing the IRC parameters component if it contains a command (e.g., !dice).

		function parseParameters(rawParametersComponent, command) {
			let idx = 0
			let commandParts = rawParametersComponent.slice(idx + 1).trim();
			let paramsIdx = commandParts.indexOf(' ');

			if (-1 == paramsIdx) { // no parameters
				command.botCommand = commandParts.slice(0);
			} else {
				command.botCommand = commandParts.slice(0, paramsIdx);
				command.botCommandParams = commandParts.slice(paramsIdx).trim();
				// TODO: remove extra spaces in parameters string
			}

			return command;
		}

		//Alte Funktion
		/*const parseMessage = (message) => {
		  const parsedMessage = {};
		message = message.replace(/(\r\n|\n|\r)/gm, "");;
		console.log(message)
		  const match = message.match(/^:(\S+)!\S+\s+PRIVMSG\s+#(\S+)\s+:(.*)$/);
		  if (match) {
		    parsedMessage.sender = match[1];
		    parsedMessage.channel = match[2];
		    parsedMessage.message = match[3];
		  return parsedMessage;
		  } else {
		      return false;
		}
		};*/

		function setCookie(cname, cvalue, exdays) {
			const d = new Date();
			d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			let expires = "expires=" + d.toUTCString();
			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}

		function getCookie(cname) {
			let name = cname + "=";
			let decodedCookie = decodeURIComponent(document.cookie);
			let ca = decodedCookie.split(';');
			for (let i = 0; i < ca.length; i++) {
				let c = ca[i];
				while (c.charAt(0) == ' ') {
					c = c.substring(1);
				}
				if (c.indexOf(name) == 0) {
					return c.substring(name.length, c.length);
				}
			}
			return "";
		}

    var token = "";
		function checkCookie(cookie_input, cookie_name) {

			/*cookie_temp = getCookie(cookie_name);
			if (session_save_cookie == "false" || session_save_cookie == "") {
			  return cookie_input;
			}*/

			cookie_temp = getCookie(cookie_name);
			if (cookie_temp == "" || cookie_temp == null) {
				return cookie_input;
			} else {
				return cookie_temp;
			}
		}

    var user_id = "";
		document.addEventListener("DOMContentLoaded", () => {
			//addName("heldendesbildschirms",false);

			api = getCookie("sliderTTSValue");
      if (api !== undefined && api != "") {
        document.getElementById("slider_TTS").value = api;
      } else {
        document.getElementById("slider_TTS").value = 3;
      }

      document.getElementById('sliderValue_TTS').innerHTML = getText(api);

      key = getCookie("keyAPISValue");
      document.getElementById("key_input").value = key;


			ReadNamesAsCookie()
			token = document.getElementById("token_input").value
			token = checkCookie(token, "token");
      //token = etUrlParam("token") ist zu unsicher, kann ich nicht machen.
			document.getElementById("token_input").value = token;

      <?php
      // Send the access token to the client
      if (isset($_GET['code']) && !empty($_GET['code']) && isset($access_token)) {
        //if ($access_token != undefine) {
        echo 'document.getElementById("token_input").value = "' .  $access_token . '";
        token = "'  .  $access_token . '";';
        //}
      }

       //echo '<script> document.addEventListener("DOMContentLoaded", () => { document.getElementById("token_input").value = "' .  $access_token . '";
       //token =" '  .  $access_token . ' "; }); </script>';
      ?>
			var username = document.getElementById("username_input").value
			username = checkCookie(username, "username");
			document.getElementById("username_input").value = username;

      if (token != "") {
      fetch('https://api.twitch.tv/helix/users', {
        headers: {
          'Authorization': 'Bearer ' + token.substring(6),
          'Client-ID': "4f4q2je3cxhhkqh9lp4c36qwa3dvyj"
        }
      })
      .then(response => response.json())
      .then(data => {
        if (typeof data.data[0].login !== 'undefined' || typeof data.data[0].id !== 'undefined') {
        username = data.data[0].login;
        document.getElementById("username_input").value = username;
        user_id = data.data[0].id;
        //console.log('Benutzername: ' + username);1
        } else {
        alert("Hinweis ihere Seitzung scheint abgelaufen zu sein, bitte melden sie sich an.");
        }

      })
      //.catch(error => console.error(error)) ;
      .catch(error => alert("Hinweis ihere Seitzung scheint abgelaufen zu sein, bitte melden sie sich an.")) ;
      }

      //fetch('https://api.twitch.tv/kraken/chat/:channel_name/emoticons', {
      /*fetch('https://api.twitch.tv/helix/chat/emotes/global', {
        headers: {
          'Authorization': 'Bearer ' + token.substring(6),
          'Client-ID': "4f4q2je3cxhhkqh9lp4c36qwa3dvyj"
        }
      })
      .then(response => response.json())
      .then(data => {
        console.log(data + data.data[0]);
      })
      .catch(error => console.error(error)) ;*/

			var channel = document.getElementById("channel_input").value
			channel = checkCookie(channel, "channel");
			document.getElementById("channel_input").value = channel;
		});

		/*function record(user, message) {
			// Do what you want with your data
			const content_box = document.getElementsByClassName("chat")[0];
			const p = document.createElement("p");
			p.appendChild(document.createTextNode(data));
			content_box.appendChild(p);

			//scrollToBottom();
		}*/

    var recordCounter = 0;

		function record(user, message, autoread, autoreadDelay) {
      recordCounter++;

      // Do what you want with your data
			const content_box = document.getElementsByClassName("chat")[0];
			const p = document.createElement("p");
			const userLink = document.createElement("a");
			const messageLink = document.createElement("a");
      const autoread_a = document.createElement("a");
      const autoreadDelay_a = document.createElement("a");

			userLink.setAttribute("href", "#");
			userLink.textContent = user;
			userLink.addEventListener("click", function() {
				// Add user to list
				addName(user, false, 0, true);
			});

			messageLink.setAttribute("href", "#");
			messageLink.textContent = message;
			messageLink.addEventListener("click", function() {
				// Read message
				//speakMessage(message);
				//speakMessageSelectTTS(message);
        speakMessageSelectTTS(createMessage(user,message));
			});

      messageLink.setAttribute("href", "#");
			messageLink.textContent = message;

      p.appendChild(userLink);
			p.appendChild(document.createTextNode(" "));
			p.appendChild(messageLink);
    	p.appendChild(document.createTextNode(" "));
    	//p.appendChild(autoread_a);
  		//p.appendChild(autoreadDelay_a);
      if (autoread == true) {
              autoread_a.textContent = "auto read";
              autoread_a.setAttribute("href", "#");
              autoread_a.addEventListener("click", function() {
        				audio.pause();
        			});
              p.appendChild(autoread_a);

              if (autoreadDelay != false) {
                  p.appendChild(document.createTextNode(" "));
                  speakMessageDelay(user, message, autoreadDelay, recordCounter);
                  autoreadDelay_a.textContent = "Verzögerung: " + autoreadDelay + " Sek abbrechen";
                  autoreadDelay_a.setAttribute("href", "#");
                  var recordCounterTemp = recordCounter;
                  autoreadDelay_a.addEventListener("click", function() {
            				//audio_stop_id = parseInt(recordCounter);
                    audio_stop(recordCounterTemp);
            			});
                  p.appendChild(autoreadDelay_a);
              } else {
                speakMessageSelectTTS(createMessage(user,message));
              }

            }
			content_box.appendChild(p);

			scrollToBottom();
		}

		function encode_utf8(s) {
			return unescape(encodeURIComponent(s));
		}

		function decode_utf8(s) {
			return decodeURIComponent(escape(s));
		}
	</script>

	<script>
		const moveMessage = 'Get up and move, your body will thank you!';
		const defaultMoveInterval = 1000 * 60 * 1; // Set to 1 minute for testing.
		let moveInterval = defaultMoveInterval;

		const socket = 404;
		//const parsedMessage = parseMessage("@badge-info=subscriber/12;badges=broadcaster/1,subscriber/2,premium/1;client-nonce=a8a58f1dd79d7cc9ff2a87f976343306;color=#0000FF;display-name=HeldendesBildschirms;emote-only=1;emotes=emotesv2_50f94e2bff784399bc649e5a36abbedf:0-8,10-18/emotesv2_124c8fc37a4b43c3977acd181f46cf47:20-46/emotesv2_6260072355ed436dbdd0c4fc07c24ab3:48-59/emotesv2_456b2122c2384047a7e09a9ed695466c:61-71/emotesv2_288821f79fa146858bd4e4afb40f61cd:73-82;first-msg=0;flags=;id=791201eb-253b-421a-9aa4-fceacff8e883;mod=0;returning-chatter=0;room-id=89617799;subscriber=1;tmi-sent-ts=1685925466193;turbo=0;user-id=89617799;user-type= :heldendesbildschirms!heldendesbildschirms@heldendesbildschirms.tmi.twitch.tv PRIVMSG #heldendesbildschirms :helden9HI helden9HI helden9Heldendesbildschirms samisa13Buxe helden9Feet helden9Fun")
    //const parsedMessage = parseMessage("@badge-info=subscriber/12;badges=broadcaster/1,subscriber/2,premium/1;client-nonce=6ab09707bb78ae33d8ada356475dd68b;color=#0000FF;display-name=HeldendesBildschirms;emotes=emotesv2_6260072355ed436dbdd0c4fc07c24ab3:9-20/emotesv2_8b18398ab3a44f3cabf971c5bb2196ff:28-39/emotesv2_124c8fc37a4b43c3977acd181f46cf47:58-84/emotesv2_d7a6a562bc18429d86496446050a966b:89-112/emotesv2_288821f79fa146858bd4e4afb40f61cd:117-126/62835:131-141/emotesv2_456b2122c2384047a7e09a9ed695466c:146-156,161-171;first-msg=0;flags=;id=7ec4ceeb-f115-40ba-9a74-5f7d10be70b4;mod=0;returning-chatter=0;room-id=89617799;subscriber=1;tmi-sent-ts=1685933627171;turbo=0;user-id=89617799;user-type= :heldendesbildschirms!heldendesbildschirms@heldendesbildschirms.tmi.twitch.tv PRIVMSG #heldendesbildschirms :scheint  samisa13Buxe   zu  samisa13Lieb   funktionieren  helden9Heldendesbildschirms    helden9Unterweaschemodel    helden9Fun    bleedPurple    helden9Feet    helden9Feet")

    //var test = decode_utf8(parsedMessage.command.botCommandParams)
		//alert(`PRIVMSG :${test}`)
		//alert(parsedMessage.command.command)
		//alert(parsedMessage.command.botCommand)
		//alert(parsedMessage.command.botCommandParams)
		//alert(parsedMessage.parameters)
		//alert(parsedMessage.source.nick)
		/*const emotes = parsedMessage.tags.emotes;
		console.log(emotes)
    var parsedMessageEmoteFilter = parsedMessage.parameters;

    var emote_length = [Object.keys(emotes)][0].length
    var emotes_name_list_temp = [emote_length];

    for (var i = 0; i < emote_length; i++) {
      var emote_id = ([Object.keys(emotes)[i]][0]);
      //var emote_start = (emotes[Object.keys(emotes)[i]][0].startPosition);
      //var emote_end = (emotes[Object.keys(emotes)[i]][0].endPosition);
      var emote_start = parseInt(emotes[emote_id][0].startPosition);
      var emote_end = parseInt(emotes[emote_id][0].endPosition)+1;
      //console.log(getEmotes())

      console.log(parsedMessageEmoteFilter.substring(emote_start,emote_end))
      emotes_name_list_temp[i] = parsedMessageEmoteFilter.substring(emote_start,emote_end);
      console.log(emotes_name_list_temp[i]);
    }

    for (var i = 0; i < emotes_name_list_temp.length; i++) {
      var emote_name = emotes_name_list_temp[i];
      var regex = new RegExp(emote_name, 'g');
      parsedMessageEmoteFilter = parsedMessageEmoteFilter.replace(regex, '');
    }

    console.log(parsedMessageEmoteFilter);*/
		//console.log(parsedMessage)
    //const firstEmote = emotes[Object.keys(emotes)[0]][0];
    /*for (const emoteKey in emotes) {
      if (emotesData.hasOwnProperty(emoteKey)) {
        const emoteList = emotesData[emoteKey];

        // Iteriere über die Emote-Objekte in der Liste
        for (const emote of emoteList) {
          const startPosition = emote.startPosition;
          const endPosition = emote.endPosition;

          console.log(`Emote Key: ${emoteKey}`);
          console.log(`Startposition: ${startPosition}`);
          console.log(`Endposition: ${endPosition}`);
          console.log('---');
        }
      }
    }*/
		document.addEventListener("DOMContentLoaded", () => {
			//var msg = parsedMessage.source.nick + " schreibt: " + parsedMessage.parameters
			//if (checkNames(parsedMessage.source.nick) == true) {
			//speakMessage(msg);
			//record(parsedMessage.source.nick, parsedMessage.parameters)
			//}
		});

		function init() {
      //if (socket.readyState !== WebSocket.OPEN && (socket !== 404)) {
      if (true) {
			var username = document.getElementById('username_input').value
			var token = document.getElementById('token_input').value
			var channel = document.getElementById('channel_input').value

			var key = document.getElementById('key_input').value
      if (key != "") {
          setCookie("keyAPISValue", key, 30);
      }
      setCookie("sliderTTSValue", api, 30);
			setCookie("username", username, 30);
			setCookie("channel", channel, 30);
			setCookie("token", token, 30);

			const socket = new WebSocket('wss://irc-ws.chat.twitch.tv:443');

			socket.onopen = function() {
        document.getElementById('connect_button').textContent = "disconnect";
      	document.getElementById('connect_button').onclick	="socket.close();";				//newItem.addEventListener("click", function() {addName(newName, true)});
				// Verbindung zum Twitch-IRC-Server hergestellt, sende Authentifizierungsnachricht
				socket.send(`PASS ${token}`);
				socket.send(`NICK ${username}`);
				socket.send('CAP REQ :twitch.tv/tags twitch.tv/commands');
				/*let intervalObj = setInterval(() => {
		        connection.send(`PRIVMSG ${channel} :${moveMessage}`);
		    }, moveInterval);*/
			};

			//var lastUser = "";
			socket.onmessage = function(event) {
				// Nachricht vom Twitch-IRC-Server empfangen
				let message = event.data //.utf8Data.trimEnd();
				let messages = message.split('\r\n'); // The IRC message may contain one or more messages.

				const parsedMessage = parseMessage(messages)
				if (parsedMessage) {
					switch (parsedMessage.command.command) {
						case 'PRIVMSG':
							// Ignore all messages except the '!move' bot
							// command. A user can post a !move command to change the
							// interval for when the bot posts its move message.

							/*if ('move' === parsedMessage.command.botCommand) {

								// Assumes the command's parameter is well formed (e.g., !move 15).

								let updateInterval = (parsedMessage.command.botCommandParams) ?
									parseInt(parsedMessage.command.botCommandParams) * 1000 * 60 : defaultMoveInterval;

								socket.send(`PRIVMSG ${channel} :${moveMessage}`);
								//socket.send(`PRIVMSG ${channel} :Du hast Move mit ${parseInt(updateInterval)} gesendet aber ich bin offensichtlich nicht klug genug das zu übersetzen.`);
								if (moveInterval != updateInterval) {
									// Valid range: 1 minute to 60 minutes
									if (updateInterval >= 60000 && updateInterval <= 3600000) {
										moveInterval = updateInterval;

										// Reset the timer.
										clearInterval(intervalObj);
										intervalObj = null;
										intervalObj = setInterval(() => {
											socket.send(`PRIVMSG ${channel} :${moveMessage}`);
										}, moveInterval);
									}
								}
							} else if ('moveoff' === parsedMessage.command.botCommand) {
								clearInterval(intervalObj);
								socket.send(`PART ${channel}`);
								socket.close();
							} */

							//var msg = parsedMessage.source.nick + " schreibt: " + parsedMessage.parameters
							//record(parsedMessage.source.nick, parsedMessage.parameters)
							//record(parsedMessage.source.nick, msg)
              //record(parsedMessage.source.nick,parsedMessage.parameters);

              var parsedMessageEmoteFilter = parsedMessage.parameters;
              var emotes = parsedMessage.tags.emotes;

              if (emotes && typeof emotes === 'object') {
                var emote_length = [Object.keys(emotes)][0].length
                var emotes_name_list_temp = [emote_length];

                for (var i = 0; i < emote_length; i++) {
                  var emote_id = ([Object.keys(emotes)[i]][0]);
                  //var emote_start = (emotes[Object.keys(emotes)[i]][0].startPosition);
                  //var emote_end = (emotes[Object.keys(emotes)[i]][0].endPosition);
                  var emote_start = parseInt(emotes[emote_id][0].startPosition);
                  var emote_end = parseInt(emotes[emote_id][0].endPosition)+1;
                  //console.log(getEmotes())

                  emotes_name_list_temp[i] = parsedMessageEmoteFilter.substring(emote_start,emote_end).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                }

                for (var i = 0; i < emotes_name_list_temp.length; i++) {
                  var emote_name = emotes_name_list_temp[i];
                  var regex = new RegExp(emote_name, 'g');
                  parsedMessageEmoteFilter = parsedMessageEmoteFilter.replace(regex, '');
                }
              }

              var autoreadBool = false;
              //if ((checkNames(parsedMessage.source.nick) !== false) || ((checkNames("*") !== false) && (checkNamesbanned(checkNames(parsedMessage.source.nick)) == false)) ) {
              //if ((checkNames(parsedMessage.source.nick) !== false) && (checkNamesbanned(checkNames(parsedMessage.source.nick)) == false) || (checkNames("*") !== false) ) {
              var checkNames_Temp = checkNames(parsedMessage.source.nick);
              var checkNames_all_Temp = checkNames("*");

              if (((checkNames_Temp !== false) || (checkNames_all_Temp !== false) ) && parsedMessage.tags["emote-only"] == undefined) {
                if (checkNamesbanned(checkNames_Temp) == false && checkNamesbanned(checkNames_all_Temp) == false) {
								//speakMessage(msg);
              //  alter(parsedMessage.command.botCommand)

              if (parsedMessage.parameters.indexOf('@') === -1 && parsedMessage.command.botCommand === undefined) {
                  //speakMessageSelectTTS(createMessage(parsedMessage.source.nick,parsedMessage.parameters));
                  if (parsedMessage.parameters.length >= document.getElementById('slider_min_zeichen').value && parsedMessage.parameters.length <= document.getElementById('slider_max_zeichen').value) {
                     autoreadBool = true
                  }
              }

								/*if (parsedMessage.parameters.indexOf('@') === -1 && parsedMessage.command.botCommand === undefined) {
									if (lastUser != parsedMessage.source.nick) {
										speakMessageSelectTTS(msg);
                    lastUser = parsedMessage.source.nick
									} else {
										speakMessageSelectTTS(parsedMessage.parameters);
									}
								}*/
              }
							}

              record(parsedMessage.source.nick,parsedMessageEmoteFilter, autoreadBool, document.getElementById('slider_autoreadDelayn').value);

							/*if ('papagei' === parsedMessage.command.botCommand) {
								socket.send(`PRIVMSG ${channel} :${parsedMessage.command.botCommandParams}`);
							}*/

							break;
            case 'CLEARMSG':
              audio_stop(parsedMessage.parameters);
              break;
            case 'CLEARCHAT':
              audio_stop(parsedMessage.parameters);
              break;
						case 'PING':
							socket.send('PONG ' + parsedMessage.parameters);
							break;
						case '001':
							// Successfully logged in, so join the channel.
							socket.send(`JOIN #${channel}`);
							break;
						case 'JOIN':
							// Send the initial move message. All other move messages are
							// sent by the timer.
							//socket.send(`PRIVMSG ${channel} :${moveMessage}`);
							break;
						case 'PART':
							console.log('The channel must have banned (/ban) the bot.');
							socket.close();
							break;
						case 'NOTICE':
							// If the authentication failed, leave the channel.
							// The server will close the socket.
							if ('Login authentication failed' === parsedMessage.parameters) {
								console.log(`Authentication failed; left ${channel}`);
								socket.send(`PART ${channel}`);
							} else if ('You don’t have permission to perform that action' === parsedMessage.parameters) {
								console.log(`No permission. Check if the access token is still valid. Left ${channel}`);
								socket.send(`PART ${channel}`);
							}
							break;
						default:
							; // Ignore all other IRC messages.
					}
				}

				// Verarbeite die Nachricht
				//alert((message));
				//record(message)

				/*if (message == "PING") { // Platzhalter
					socket.send(`PONG`);
				}

				const parsedMessage = parseMessage(message)
				if (parsedMessage != false) {
					var msg = parsedMessage.sender + " schreibt: " + parsedMessage.message
					speakMessage(msg);
					record(msg)
				}*/
			};
      }
		}
	</script>
  Tutorial: <a href="https://youtu.be/CT_BSBgXtyQ" target="_blank">https://youtu.be/CT_BSBgXtyQ</a> <br>
  Unterstützen Sie das Projekt mit einem Pull Request oder Bug Report auf GitHub: <a href="https://github.com/JanisPlayer/chatspeaker" target="_blank">https://github.com/JanisPlayer/chatspeaker</a> <br><br>

  Es handelt sich bisher noch um eine Testanwendung: <br>
  Es fehlt - Benutzername, um auch mit * alle vorherigen Nachrichten zu blockieren. <br>
  Beim erneuten Klicken auf einen Nutzer diesen - zu blockieren und nicht nur zu entfernen. <br>
  Mod-Entscheidungen sollten berücksichtigt werden. <br>
  Die Bedienung der Benutzeroberfläche. <br>
  Daten exportieren über JSON oder Server. <br>
  Ein WS- oder PHP-Server für die Steuerung in OBS mit dem dazugehörigen Client. <br>
  Warnung: Momentan noch ohne csrf_token; die Anfrage ist somit noch nicht vor Man-in-the-Middle-Angriffen sicher. Bitte achten Sie darauf, sich in einem sicheren Netzwerk zu befinden. <br>
  Die Sitzung wird nun testweise durch einen csrf_token geschützt. <br>
  100% sicher ist das Ganze jedoch nicht, weil ich es gerade noch der Einfachheit halber in einem Cookie speichere. Über eine HTTP-Verbindung kann dieser gestohlen werden. Akzeptieren Sie bitte bis zu einer Verbesserung keine HTTP-Verbindungen zu meiner Seite. <br><br>

  Über das HeldendesBildschirms TTS kannst du 1000 Nachrichten und 50 TSD Zeichen pro Tag senden. Es wird jedoch empfohlen, ein eigenes TTS zu nutzen, wie im Tutorial gezeigt, da sich dies wahrscheinlich ändern wird und viel Leistung kosten kann. <br><br>

  Hinweis: Das Google TTS verursacht API-Kosten, die ich mir als Hobby-Entwickler nicht leisten kann. Falls dies gewünscht ist, empfehle ich die Lösung, ein eigenes TTS zu nutzen. Hier könnt ihr bettervoice.php als Vorlage verwenden und den Regler auf "Eigenes TTS" setzen. Fügt eure URL wie im Tutorial gezeigt ein. <br>
  https://cloud.google.com/text-to-speech/pricing?hl=de<br>
  8 USD kosten 1000 Nachrichten mit 500 Zeichen (500 TSD Zeichen).<br>
  Die Frage ist halt, was ist realistisch. :D
  Und ja, jeder, der sich über die Seite anmeldet, hat jetzt für 24 Stunden 1000 Nachrichten und maximal 1000 Zeichen zum Verbrauchen. Die Nachricht darf 500 Zeichen umfassen.<br>
  Und maximal 1 Nachricht pro Sekunde.<br>
  Für alle anderen gilt das normale Limit: 10 Nachrichten pro Minute mit maximal 200 Zeichen.<br>
  Ein Emotefilter ist hinzugefügt, aber es braucht noch weitere Filter, die auf ausgewählte Zeichen beschränken und vielleicht Spam besser erkennen.<br>
  Nachrichten-Cache für die letzten 500 Namen und letzten 30 Nachrichten (serverseitig).<br>
  Nachrichtenlimit pro Benutzer.<br>
  Chatbefehl fürs Sprechen (optional).<br>
  <?php
  //error_log('Fehlermeldung', 0);
  ?>
</body>

</html>
