document.addEventListener("DOMContentLoaded", function() {
    // Select all rating elements
    const ratings = document.querySelectorAll(".rating");
    
    ratings.forEach(rating => {
        for (let i = 0; i < 4; i++) {
            let duplicate = rating.querySelector("#star").cloneNode(true);
            rating.appendChild(duplicate);
        }
    });
});

const yearSpan = document.querySelector('#currentYear');
const currentYear = new Date();
yearSpan.innerHTML = currentYear.getFullYear();

const stars = document.querySelectorAll('.stars-widget input'); 
let rating = 0;
stars.forEach((star, index) => {
    star.addEventListener('click', function() {
        rating = index + 1; 
        stars.forEach(s => s.nextElementSibling.classList.remove('selected'));
        for (let i = 0; i <= index; i++) {
            stars[i].nextElementSibling.classList.add('selected'); 
        }
        document.querySelector('.star-error').textContent = '';
    });
});



var reviewSwiper = new Swiper(".reviewSwiper", 
    {
        slidesPerView: 1,
        grabCursor: true,
        loop: true,
    
        pagination: 
        {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: 
        {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
    
    });












