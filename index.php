<!DOCTYPE html>
<html>
  <head>
    <style>

      .dialog{
        /* From https://css.glass */
        background: rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 50px;

        position:fixed;
        top: 50%;
        left: 50%;
        width:30em;
        height:18em;
        margin-top: -9em;
        margin-left: -15em; 

        z-index: 100;
      }

      button{
        background-color: #0c0d46;
        padding: 20px;
        font-weight: bold;
        font-size: 30;
        border-radius: 15px;
        color: white;
      }
    </style>
    <style>
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
  </head>
  <body>
    <div class="dialog" id="begindialog">
      Begin the session by clicking on "begin"<br/>
      <br/>
      <button id="beginstart">Begin</button>
    </div>
    <div class="dialog" id="seconddialog">
      <progress id="file" value="0" max="1320" ring="1200" style="width:100%"></progress> 
      <h1 id="status"></h1>
      <h2 id="substatus"></h2>
    </div>
    <script>
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
        },

        get count(){
          return this._count;
        },

        set count(e){
          this._count = e;
          document.getElementById("file").setAttribute("value",e);
          if(e==document.getElementById("file").getAttribute("max")){
            this.count = 0;
            return;
          }
          var eee = 0;
          if(e<Number(document.getElementById("file").getAttribute("ring"))){
            this.status = "working";
            eee = Number(document.getElementById("file").getAttribute("ring")) - e;
          }else{
            this.status = "resting";
            eee = Number(document.getElementById("file").getAttribute("max")) - e;
          }
          things = eee + " seconds left";
          document.getElementById("substatus").innerHTML = things;
          document.title = things;
        }

      };
      document.getElementById("seconddialog").style.display = "none";
      document.getElementById("beginstart").addEventListener("click",function(event){
        document.getElementById("begindialog").style.display = "none";
        document.getElementById("seconddialog").style.display = "block";

        us.count = 0;

        window.setInterval(() => {
          us.count++;
        }, 1000);
      });

      function speak(msg){
        var usg = new SpeechSynthesisUtterance(msg);
        usg.rate = 0.8;
        usg.pitch = 0.2;
        window.speechSynthesis.speak(usg);
      }
    </script>




    <div id="time-info">Laden...</div>
    <div class="sky-overlay" id="sky"></div>
    <div class="stars" id="stars"></div>
    <canvas id="canvas"></canvas>
    <div class="mountains" id="mtn"></div>
    <script>
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

            info.innerText = `Tijd: ${now.getHours()}:${mins.toString().padStart(2, '0')} | Lichtsterkte: ${Math.round(lightFactor * 100)}% | Mode: ${us.status} | Git: <?php echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".git" . DIRECTORY_SEPARATOR . "ORIG_HEAD") ?>`;
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

  </body>
</html>
