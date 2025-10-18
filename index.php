<?php
// echo "hello world";
?>
Worktime <input type="number" value="20" name="worktyme"> minutes<br/>
Pausetime <input type="number" value="2" name="sleeptyme"> minutes<br/>
<button>Notify me!</button>
<script>
document.querySelector("button").addEventListener("click", notifyMe);

function sleepcycle(){
        new Notification("Worktime starts!");
        window.setTimeout(function(){
                const notification = new Notification("Pausetime starts!");
                window.setTimeout(sleepcycle,Number(sleeptyme.value)*60*1000);
        },Number(worktyme.value)*60*1000);
}

function notifyMe() {
  if (!("Notification" in window)) {
    alert("This browser does not support desktop notification");
  } else if (Notification.permission === "granted") {
    sleepcycle();
  } else if (Notification.permission !== "denied") {
    Notification.requestPermission().then((permission) => {
      if (permission === "granted") {
        sleepcycle();
      }
    });
  }
}
</script>
