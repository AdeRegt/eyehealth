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
        padding: 40px;

        position:fixed;
        top: 50%;
        left: 50%;
        width:24em;
        height:24em;
        margin-top: -12em;
        margin-left: -12em; 

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

      /* SVG Cirkel Timer Stijlen */
      .timer-container {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto 15px auto;
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
      Begin the session by clicking on "begin"<br/>
      <br/>
      <button id="beginstart">Begin</button>
    </div>
    <div class="dialog" id="seconddialog">
      <div class="timer-container">
        <svg class="timer-ring" viewBox="0 0 160 160">
          <circle class="timer-ring-bg" cx="80" cy="80" r="70"></circle>
          <circle id="timer-progress" class="timer-ring-progress" cx="80" cy="80" r="70"></circle>
        </svg>
        <div class="timer-text" id="timer-time">00:00</div>
      </div>
      <h1 id="status"></h1>
      <h2 id="substatus"></h2>
    </div>
    <div id="time-info">Laden...</div>
    <div class="sky-overlay" id="sky"></div>
    <div class="stars" id="stars"></div>
    <canvas id="canvas"></canvas>
    <div class="mountains" id="mtn"></div>
    <script>
      let audioUnlocked = false;
      const audioCache = {};

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
          
          const max = 1200; // 20 minuten totaal (18 min werken + 2 min rust)
          const ringThreshold = 1080; // 18 minuten werken (1080 sec)
          const restDuration = 120; // 2 minuten rust (120 sec)

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
            // Cirkel vullen van 0 tot 1 tijdens de 18 minuten (1080 sec)
            progressFraction = e / ringThreshold;
          }else{
            this.status = "resting";
            eee = max - e;
            // Cirkel leegmaken/vullen tijdens de 2 minuten rust (120 sec)
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
        }

      };
      document.getElementById("seconddialog").style.display = "none";
      document.getElementById("beginstart").addEventListener("click",function(event){
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
