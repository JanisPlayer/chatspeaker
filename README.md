# chatspeaker
Liest Twitch Chat Automatisch oder Manuel vor mit Whitelist einfach auf die Nutzer klicken um sie hinzuzufügen oder selbst eintragen.

# Eigner TTS Server:
[Coqui](https://github.com/coqui-ai/TTS):
```
screen -S tts -dm bash -c 'docker run --rm -it -p 5002:5002 -v /home/tts/models/:/root/.local/share/tts --entrypoint /bin/bash ghcr.io/coqui-ai/tts-cpu -c "cd /root/TTS/server/ && python3 /root/TTS/server/server.py --model_name tts_models/de/thorsten/tacotron2-DDC"'
```
Eine Alternative für PC mit weniger Leistung wäre [Mimic3](https://github.com/MycroftAI/mimic3):
```
docker run -it -p 59125:59125 -v "${HOME}/.local/share/mycroft/mimic3:/home/mimic3/.local/share/mycroft/mimic3" 'mycroftai/mimic3'
```
Füge die URL von deinem Server unter Eigenes TTS auf der Webseite ein.  
Ohne Proxy funktioniert das nur mit dem Firefox, weil HTTP Traffic in Chrome geblockt wird auf einer HTTPS Seite.  
Coqui:
```
http://localhost:5002/api/tts?text=&speaker_id=&style_wav=&language_id=
```
Mimic 3:
```
http://localhost:59125/api/tts?text=&voice=de_DE%2Fm-ailabs_low%23angela_merkel&noiseScale=0.333&noiseW=0.333&lengthScale=1&ssml=false&audioTarget=client
```
Für Windows benötigst du [WLS](https://learn.microsoft.com/de-de/windows/wsl/install), damit du docker installieren kannst.  
Du kannst natürlich auch über Python einfach eine eigene Lösung nutzen.
