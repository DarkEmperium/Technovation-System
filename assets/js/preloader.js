var loader = document.getElementById("preloader");
window.addEventListener("load", function(){
  loader.style.opacity = 0;
  setTimeout(function() {loader.style.display="none";}, 500); // delay for 0.5 seconds (duration of the fade out animation)
});