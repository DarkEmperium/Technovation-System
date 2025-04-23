var swiper = new Swiper(".home-slider", {  
  speed: 1500,  
  spaceBetween: 0,  
  centeredSlides: true,  
  autoplay: { 
      delay: 7000, 
      disableOnInteraction: false, 
      stopOnLast: true 
  },  
  loop: true,
});

for (let i = 0; i < 2; i++) {
  let duplicate = document.querySelector(".logos-container").cloneNode(true);
  document.querySelector(".logos-slide").appendChild(duplicate);
}
