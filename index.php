<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Progressive Eye Health Monitoring Application">
    <meta name="theme-color" content="#0c0d46">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Eyes">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 192 192'><rect fill='%23050b1a' width='192' height='192'/><circle cx='96' cy='96' r='60' fill='%2387ceeb'/><circle cx='96' cy='96' r='40' fill='%230c0d46'/><circle cx='96' cy='96' r='20' fill='white'/></svg>">
    <style>

      .dialog{
        /* From https://css.glass */
        background: rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 25px 35px;

        position:fixed;
        top: 50%;
        left: 50%;
        width: 26em;
        max-width: 90vw;
        max-height: 90vh;
        overflow-y: auto;
        transform: translate(-50%, -50%);
        margin: 0;

        z-index: 100;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        font-family: sans-serif;
      }

      .dialog h1 {
        margin: 10px 0 5px 0;
        font-size: 1.8rem;
        text-transform: capitalize;
      }

      .dialog h2 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: normal;
        opacity: 0.9;
      }

      .dialog label {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
        margin-bottom: 10px;
        font-size: 0.9rem;
        font-weight: 500;
      }

      .dialog input[type="number"], .dialog input[type="text"] {
        width: 100%;
        box-sizing: border-box;
        margin-top: 4px;
        padding: 7px 10px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        background: rgba(0, 0, 0, 0.2);
        color: white;
        font-size: 0.95rem;
        outline: none;
      }

      .dialog input[type="number"]:focus, .dialog input[type="text"]:focus {
        border-color: #87ceeb;
      }

      .pauses-config-container {
        width: 100%;
        max-height: 120px;
        overflow-y: auto;
        margin-bottom: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 5px;
        background: rgba(0, 0, 0, 0.1);
      }

      .pause-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 6px;
      }

      .pause-row input {
        flex: 1;
        margin-top: 0 !important;
      }

      .dialog button {
        margin-top: 8px;
        padding: 9px 20px;
        border-radius: 8px;
        border: none;
        background: #87ceeb;
        color: #050b1a;
        font-weight: bold;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
      }

      .dialog button:hover {
        background: #fff;
      }

      .small-btn {
        padding: 4px 10px !important;
        font-size: 0.85rem !important;
        margin-top: 0 !important;
        background: rgba(255, 255, 255, 0.3) !important;
        color: white !important;
      }
      .small-btn:hover {
        background: rgba(255, 255, 255, 0.5) !important;
      }

      /* SVG Cirkel Timer Stijlen */
      .timers-wrapper {
        display: flex;
        gap: 20px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 10px;
      }

      .timer-container {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0;
      }

      .timer-ring {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
      }

      .timer-ring-bg {
        fill: none;
        stroke: rgba(255, 255, 255, 0.2);
        stroke-width: 10;
      }

      .timer-ring-progress {
        fill: none;
        stroke: #87ceeb;
        stroke-width: 10;
        stroke-linecap: round;
        stroke-dasharray: 439.82; /* 2 * pi * 70 */
        stroke-dashoffset: 439.82;
        transition: stroke-dashoffset 0.5s linear, stroke 0.5s ease;
      }

      .timer-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        font-weight: bold;
      }
    
        :root {
            /* We gebruiken CSS variabelen die we met JS updaten */
            --cycle-factor: 0; 
            --sky-day: #87ceeb;
            --sky-night: #050b1a;
            --mtn-day: #2d5a27;
            --mtn-night: #0a0f1d;
        }

        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            transition: background-color 1s linear;
        }

        /* Dynamische luchtachtergrond */
        .sky-overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--sky-night);
            z-index: 0;
        }

        /* Canvas voor Noorderlicht - de opacity wordt gekoppeld aan de nacht */
        #canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 80%;
            filter: blur(25px);
            z-index: 2;
            pointer-events: none;
        }

        /* De Bergen */
        .mountains {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 60%;
            z-index: 10;
            clip-path: polygon(
                0% 100%, 0% 85%, 8% 55%, 15% 80%, 25% 30%, 
                35% 70%, 45% 20%, 55% 75%, 65% 35%, 75% 85%, 
                85% 25%, 92% 70%, 100% 40%, 100% 100%
            );
            transition: background 1s linear;
        }

        .stars {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Info paneel voor testen */
        #time-info {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-family: sans-serif;
            z-index: 100;
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <title>Waiting | Eyes</title>
  </head>
  <body>
    <div class="dialog" id="begindialog">
      <h1>Eye Health & Werkdag</h1>
      <p style="margin: 3px 0 10px 0; font-size: 0.85rem; opacity: 0.9;">Configureer pauzes en oog-oefening timers</p>
      
      <label>
        Oog-oefening Werkduur (min):
        <input type="number" id="work-input" value="18" min="1" max="120">
      </label>
      <label>
        Oog-oefening Rustduur (min):
        <input type="number" id="rest-input" value="2" min="1" max="60">
      </label>

      <div style="display: flex; gap: 8px; width: 100%;">
        <label style="flex:1;">
          Start werktijd:
          <input type="text" id="workday-start" value="09:00">
        </label>
        <label style="flex:1;">
          Eind werktijd:
          <input type="text" id="workday-end" value="17:00">
        </label>
      </div>

      <label style="margin-top: 4px;">
        Pauzes (tijdstip & duur in min):
      </label>
      <div class="pauses-config-container" id="pauses-config-list">
        <!-- Dynamische pauze rijen worden hier toegevoegd -->
      </div>
      <button type="button" class="small-btn" id="add-pause-btn">+ Pauze toevoegen</button>

      <button id="beginstart" style="margin-top: 12px;">Begin</button>
    </div>

    <div class="dialog" id="seconddialog">
      <div class="timers-wrapper">
        <!-- Oog-oefening timer cirkel -->
        <div class="timer-container">
          <svg class="timer-ring" viewBox="0 0 160 160">
            <circle class="timer-ring-bg" cx="80" cy="80" r="70"></circle>
            <circle id="timer-progress" class="timer-ring-progress" cx="80" cy="80" r="70"></circle>
          </svg>
          <div class="timer-text" id="timer-time" style="font-size: 1.2rem;">00:00</div>
        </div>
        
        <!-- Werkdag voortgang cirkel -->
        <div class="timer-container">
          <svg class="timer-ring" viewBox="0 0 160 160">
            <circle class="timer-ring-bg" cx="80" cy="80" r="70"></circle>
            <circle id="workday-progress" class="timer-ring-progress" cx="80" cy="80" r="70" style="stroke: #55efc4;"></circle>
          </svg>
          <div class="timer-text" id="workday-time" style="font-size: 1rem; line-height: 1.2;">00:00<br><span style="font-size: 0.75rem; font-weight: normal; opacity: 0.9;" id="workday-subtext">resterend</span></div>
        </div>
      </div>

      <h1 id="status" style="font-size: 1.5rem; margin: 5px 0 2px 0;"></h1>
      <h2 id="substatus" style="font-size: 0.95rem;"></h2>
      <div id="break-substatus" style="font-size: 0.85rem; margin-top: 4px; opacity: 0.9;"></div>
    </div>
    <div id="time-info">Laden...</div>
    <div class="sky-overlay" id="sky"></div>
    <div class="stars" id="stars"></div>
    <canvas id="canvas"></canvas>
    <div class="mountains" id="mtn"></div>
    <script>
      let audioUnlocked = false;
      const audioCache = {};

      let workDurationSec = 1080; // default 18 min
      let restDurationSec = 120;  // default 2 min

      let workdayConfig = {
        start: "09:00",
        end: "17:00",
        pauses: [
          { time: "13:00", duration: 30 }
        ]
      };

      // Initialiseer standaard pauzes in de configuratie UI
      function renderPausesConfig() {
        const container = document.getElementById("pauses-config-list");
        container.innerHTML = "";
        workdayConfig.pauses.forEach((p, idx) => {
          const row = document.createElement("div");
          row.className = "pause-row";
          row.innerHTML = `
            <input type="text" class="pause-time" value="${p.time}" placeholder="HH:MM" title="Tijdstip">
            <input type="number" class="pause-duration" value="${p.duration}" min="1" max="180" placeholder="Min" title="Duur in minuten">
            <button type="button" class="small-btn remove-pause" style="background: rgba(255,100,100,0.4) !important;">×</button>
          `;
          row.querySelector(".remove-pause").addEventListener("click", () => {
            workdayConfig.pauses.splice(idx, 1);
            renderPausesConfig();
          });
          container.appendChild(row);
        });
      }

      document.getElementById("add-pause-btn").addEventListener("click", () => {
        workdayConfig.pauses.push({ time: "12:30", duration: 15 });
        renderPausesConfig();
      });

      renderPausesConfig();

      const us = {
        _status: 0,
        _count: 0,

        get status(){
          return this._status===0?"uninitialised":this._status;
        },

        set status(e){
          if(e!=this._status){
            speak(e);
          }
          this._status = e;
          document.getElementById("status").innerHTML = e;
          
          // Update cirkelkleur op basis van status
          const progressCircle = document.getElementById("timer-progress");
          if (progressCircle) {
            if (e === "working") {
              progressCircle.style.stroke = "#87ceeb"; // Lichtblauw voor werk
            } else if (e === "resting") {
              progressCircle.style.stroke = "#ff7675"; // Zachte rood/oranje tint voor rust
            }
          }
        },

        get count(){
          return this._count;
        },

        set count(e){
          this._count = e;
          
          const max = workDurationSec + restDurationSec;
          const ringThreshold = workDurationSec;
          const restDuration = restDurationSec;

          if(e >= max){
            this.count = 0;
            return;
          }

          var eee = 0;
          let progressFraction = 0;
          const circumference = 439.82; // 2 * pi * 70

          if(e < ringThreshold){
            this.status = "working";
            eee = ringThreshold - e;
            // Cirkel vullen van 0 tot 1 tijdens de werkperiode
            progressFraction = e / ringThreshold;
          }else{
            this.status = "resting";
            eee = max - e;
            // Cirkel leegmaken/vullen tijdens de rustperiode
            const restElapsed = e - ringThreshold;
            progressFraction = 1 - (restElapsed / restDuration);
          }

          // Update SVG cirkel dashoffset (van vol naar leeg of vice versa)
          const progressCircle = document.getElementById("timer-progress");
          if(progressCircle){
            const offset = circumference - (progressFraction * circumference);
            progressCircle.style.strokeDashoffset = offset;
          }

          // Formatteer seconden naar mm:ss
          const minsLeft = Math.floor(eee / 60);
          const secsLeft = eee % 60;
          const timeFormatted = String(minsLeft).padStart(2, '0') + ":" + String(secsLeft).padStart(2, '0');
          
          const timerTimeEl = document.getElementById("timer-time");
          if(timerTimeEl){
            timerTimeEl.innerHTML = timeFormatted;
          }

          let things = eee + " seconds left";
          document.getElementById("substatus").innerHTML = things;
          document.title = things;

          // Update Werkdag Voortgangscirkel op basis van echte kloktijd
          updateWorkdayProgress();
        }

      };

      function parseTimeToMinutes(timeStr) {
        const parts = timeStr.split(":");
        if (parts.length !== 2) return 0;
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
      }

      function updateWorkdayProgress() {
        const now = new Date();
        const currentTotalMins = now.getHours() * 60 + now.getMinutes() + now.getSeconds() / 60;

        const startMins = parseTimeToMinutes(workdayConfig.start);
        const endMins = parseTimeToMinutes(workdayConfig.end);

        // Bereken totale netto werktijd (exclusief pauzes) en actuele voortgang
        let totalPauseMins = 0;
        workdayConfig.pauses.forEach(p => {
          totalPauseMins += Number(p.duration);
        });

        const grossDayMins = endMins - startMins;
        const netDayMins = Math.max(1, grossDayMins - totalPauseMins);

        const workdayProgressCircle = document.getElementById("workday-progress");
        const workdayTimeEl = document.getElementById("workday-time");
        const workdaySubtextEl = document.getElementById("workday-subtext");
        const breakSubstatusEl = document.getElementById("break-substatus");

        const circumference = 439.82;

        if (currentTotalMins < startMins) {
          // Werkdag nog niet begonnen
          if (workdayProgressCircle) workdayProgressCircle.style.strokeDashoffset = circumference;
          const diffMins = Math.ceil(startMins - currentTotalMins);
          if (workdayTimeEl) workdayTimeEl.innerHTML = `Start<br><span style="font-size:0.75rem">${workdayConfig.start}</span>`;
          if (workdaySubtextEl) workdaySubtextEl.innerHTML = `over ${diffMins} min`;
          if (breakSubstatusEl) breakSubstatusEl.innerHTML = `Werktijd start om ${workdayConfig.start}`;
          return;
        }

        if (currentTotalMins >= endMins) {
          // Werkdag voorbij
          if (workdayProgressCircle) workdayProgressCircle.style.strokeDashoffset = 0;
          if (workdayTimeEl) workdayTimeEl.innerHTML = `Klaar!`;
          if (workdaySubtextEl) workdaySubtextEl.innerHTML = `werkdag voorbij`;
          if (breakSubstatusEl) breakSubstatusEl.innerHTML = `Fijne avond!`;
          return;
        }

        // Bepaal of we in een pauze zitten of aan het werk zijn
        let inPause = false;
        let currentPauseObj = null;
        let nextPauseTime = null;
        let timeToNextPause = Infinity;

        // Sorteer pauzes op tijd
        const sortedPauses = [...workdayConfig.pauses].map(p => ({
          start: parseTimeToMinutes(p.time),
          duration: Number(p.duration),
          end: parseTimeToMinutes(p.time) + Number(p.duration),
          rawTime: p.time
        })).sort((a, b) => a.start - b.start);

        let elapsedNetWorkMins = 0;
        let passedPauseMins = 0;

        for (const p of sortedPauses) {
          if (currentTotalMins >= p.start && currentTotalMins < p.end) {
            inPause = true;
            currentPauseObj = p;
            break;
          } else if (currentTotalMins >= p.end) {
            passedPauseMins += p.duration;
          } else {
            // Komende pauze
            const t2p = (p.start - currentTotalMins) * 60; // in seconden
            if (t2p < timeToNextPause) {
              timeToNextPause = t2p;
              nextPauseTime = p.rawTime;
            }
          }
        }

        if (inPause) {
          const pauseRemainingSecs = Math.max(0, Math.ceil((currentPauseObj.end - currentTotalMins) * 60));
          const pMins = Math.floor(pauseRemainingSecs / 60);
          const pSecs = pauseRemainingSecs % 60;
          if (workdayTimeEl) workdayTimeEl.innerHTML = `${String(pMins).padStart(2,'0')}:${String(pSecs).padStart(2,'0')}`;
          if (workdaySubtextEl) workdaySubtextEl.innerHTML = `pauze (${currentPauseObj.rawTime})`;
          if (breakSubstatusEl) breakSubstatusEl.innerHTML = `☕ Je hebt nu pauze tot ${Math.floor(currentPauseObj.end/60).toString().padStart(2,'0')}:${(currentPauseObj.end%60).toString().padStart(2,'0')}`;
          
          // Houd voortgangcirkel gelijk aan stand tijdens pauze
          const netElapsed = Math.min(netDayMins, Math.max(0, (currentPauseObj.start - startMins) - passedPauseMins));
          const fraction = netElapsed / netDayMins;
          if (workdayProgressCircle) workdayProgressCircle.style.strokeDashoffset = circumference - (fraction * circumference);
          return;
        }

        // Normale werktijd
        let netElapsed = (currentTotalMins - startMins) - passedPauseMins;
        netElapsed = Math.max(0, Math.min(netDayMins, netElapsed));

        const fraction = netElapsed / netDayMins;
        if (workdayProgressCircle) {
          workdayProgressCircle.style.strokeDashoffset = circumference - (fraction * circumference);
        }

        const remainingNetMins = Math.max(0, Math.ceil(netDayMins - netElapsed));
        const remH = Math.floor(remainingNetMins / 60);
        const remM = remainingNetMins % 60;
        const timeStr = remH > 0 ? `${remH}u ${remM}m` : `${remM}m`;

        if (workdayTimeEl) workdayTimeEl.innerHTML = timeStr;
        if (workdaySubtextEl) workdaySubtextEl.innerHTML = `werktijd over`;

        let breakInfoText = `⏱️ Nog ${timeStr} werktijd te gaan.`;
        if (nextPauseTime !== null && timeToNextPause !== Infinity) {
          const npMins = Math.floor(timeToNextPause / 60);
          const npH = Math.floor(npMins / 60);
          const npRemM = npMins % 60;
          const npStr = npH > 0 ? `${npH}u ${npRemM}m` : `${npRemM}m`;
          breakInfoText += ` | Volgende pauze om ${nextPauseTime} (over ${npStr})`;
        } else {
          breakInfoText += ` | Geen pauzes meer vandaag.`;
        }
        if (breakSubstatusEl) breakSubstatusEl.innerHTML = breakInfoText;
      }

      document.getElementById("seconddialog").style.display = "none";
      document.getElementById("beginstart").addEventListener("click",function(event){
        const workInput = parseInt(document.getElementById("work-input").value, 10);
        const restInput = parseInt(document.getElementById("rest-input").value, 10);
        const startInput = document.getElementById("workday-start").value.trim();
        const endInput = document.getElementById("workday-end").value.trim();

        if (!isNaN(workInput) && workInput > 0) {
          workDurationSec = workInput * 60;
        }
        if (!isNaN(restInput) && restInput > 0) {
          restDurationSec = restInput * 60;
        }

        if (startInput) workdayConfig.start = startInput;
        if (endInput) workdayConfig.end = endInput;

        // Lees alle dynamische pauze rijen uit
        const pauseRows = document.querySelectorAll("#pauses-config-list .pause-row");
        const newPauses = [];
        pauseRows.forEach(row => {
          const t = row.querySelector(".pause-time").value.trim();
          const d = parseInt(row.querySelector(".pause-duration").value, 10);
          if (t && !isNaN(d) && d > 0) {
            newPauses.push({ time: t, duration: d });
          }
        });
        workdayConfig.pauses = newPauses;

        document.getElementById("begindialog").style.display = "none";
        document.getElementById("seconddialog").style.display = "block";
        audioUnlocked = true;

        us.count = 0;

        window.setInterval(() => {
          us.count++;
        }, 1000);
      });

      function getAudio(url){
        if (!audioCache[url]) {
          audioCache[url] = new Audio(url);
        }
        return audioCache[url];
      }

      function speak(msg){
        if (!audioUnlocked) return;

        const audioMap = {
          working: "/audio/working.wav",
          resting: "/audio/resting.wav",
          uninitialised: "/audio/resting.wav"
        };

        const source = audioMap[msg];
        if (!source) return;

        const audio = getAudio(source);
        audio.currentTime = 0;
        audio.volume = 1;
        audio.play().catch(() => {
          // Ignore play failures after interaction has already started.
        });
      }

        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const info = document.getElementById('time-info');
        
        let width, height;

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resize);
        resize();

        function updateCycle() {
            const now = new Date();
            const mins = now.getMinutes();
            const secs = now.getSeconds();
            const totalProgress = (mins + secs / 60); // 0 tot 60

            // Bereken de 'lightFactor' (0 = nacht, 1 = dag)
            // Gebruikt een sinusgolf zodat 0 en 60 minuut donker zijn, en 30 minuut licht.
            // Formule: we willen een piek bij 30.
            const lightFactor = Math.abs(Math.sin((totalProgress / 60) * Math.PI));

            // Update kleuren en opacity
            const skyColor = interpolateColor('#050b1a', '#87ceeb', lightFactor);
            const mtnColor = interpolateColor('#0a0f1d', '#2d5a27', lightFactor);
            
            document.getElementById('sky').style.backgroundColor = skyColor;
            document.getElementById('mtn').style.background = mtnColor;
            
            // Noorderlicht en sterren vervagen als het licht wordt
            canvas.style.opacity = 1 - lightFactor;
            document.getElementById('stars').style.opacity = 1 - (lightFactor * 1.2);

            info.innerText = `Tijd: ${now.getHours()}:${mins.toString().padStart(2, '0')} | Mode: ${us.status} | Git: <?php $gitHash = @file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".git" . DIRECTORY_SEPARATOR . "ORIG_HEAD"); echo substr(trim((string) $gitHash), 0, 7); ?>`;
        } 

        // Helper functie om kleuren te mengen
        function interpolateColor(color1, color2, factor) {
            const hex = (x) => x.toString(16).padStart(2, '0');
            const r1 = parseInt(color1.substring(1,3), 16);
            const g1 = parseInt(color1.substring(3,5), 16);
            const b1 = parseInt(color1.substring(5,7), 16);

            const r2 = parseInt(color2.substring(1,3), 16);
            const g2 = parseInt(color2.substring(3,5), 16);
            const b2 = parseInt(color2.substring(5,7), 16);

            const r = Math.round(r1 + factor * (r2 - r1));
            const g = Math.round(g1 + factor * (g2 - g1));
            const b = Math.round(b1 + factor * (b2 - b1));

            return `#${hex(r)}${hex(g)}${hex(b)}`;
        }

        // Noorderlicht Logica
        class AuroraWave {
            constructor() {
                this.init();
            }
            init() {
                this.x = Math.random() * width;
                this.y = Math.random() * height * 0.4;
                this.length = Math.random() * 400 + 200;
                this.speed = Math.random() * 0.4 + 0.1;
                this.color = ['rgba(0, 255, 150,', 'rgba(0, 200, 255,', 'rgba(150, 100, 255,'][Math.floor(Math.random()*3)];
                this.offset = Math.random() * 100;
            }
            draw() {
                this.offset += 0.005;
                let waveX = this.x + Math.sin(this.offset) * 30;
                let gradient = ctx.createLinearGradient(waveX, this.y, waveX, this.y + this.length);
                gradient.addColorStop(0, 'rgba(0,0,0,0)');
                gradient.addColorStop(0.5, this.color + '0.4)');
                gradient.addColorStop(1, 'rgba(0,0,0,0)');
                ctx.fillStyle = gradient;
                ctx.fillRect(waveX, this.y, 60, this.length);
                this.x += this.speed;
                if (this.x > width) this.x = -60;
            }
        }

        const waves = Array.from({length: 12}, () => new AuroraWave());

        function animate() {
            ctx.clearRect(0, 0, width, height);
            waves.forEach(w => w.draw());
            updateCycle();
            requestAnimationFrame(animate);
        }

        // Sterren genereren
        const starsContainer = document.getElementById('stars');
        for (let i = 0; i < 150; i++) {
            const star = document.createElement('div');
            star.style.cssText = `position:absolute; width:2px; height:2px; background:white; top:${Math.random()*100}%; left:${Math.random()*100}%; border-radius:50%;`;
            starsContainer.appendChild(star);
        }

        animate();
    </script>

    <!-- PWA Service Worker Registration -->
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/sw.js')
            .then(registration => {
              console.log('Service Worker registered successfully:', registration);
              
              // Check for updates
              registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                newWorker.addEventListener('statechange', () => {
                  if (newWorker.state === 'activated') {
                    console.log('New service worker activated');
                    // Optionally notify user about update
                    if (confirm('A new version is available! Reload to update?')) {
                      window.location.reload();
                    }
                  }
                });
              });
            })
            .catch(error => console.error('Service Worker registration failed:', error));
        });
        
        // Listen for messages from service worker
        navigator.serviceWorker.addEventListener('message', (event) => {
          if (event.data.type === 'UPDATE_AVAILABLE') {
            console.log('Update available');
          }
        });
      }
    </script>

  </body>
</html>
