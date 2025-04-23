<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technovation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Permanent+Marker&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> <!--Icon Link https://fontawesome.com/v4/ -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <link rel="stylesheet" href="assets/css/landing_page.css">
    <link rel="stylesheet" href="assets/css/preloader.css">
    <link rel="stylesheet" href="assets/css/navigator.css">
</head>

<body>

    <!--Website Preloader-->
    <div id="preloader">
        <img src="assets/images/logo.png" alt="Technovation House Rental System">
        <h3>Loading Page</h3>
        <div class="loader"></div>
    </div>

    <section class="header">

        <!-- Website Logo -->
        <div class="logo">
            <img src="assets/images/logo.png" alt="Technovation House Rental System">
            <h3>Technovation House Rental System</h3>
        </div>

        <!-- Navigation Bar -->
        <div class="navigation">
            <input type="checkbox" class="navigation__checkbox" id="toggle">
            <label for="toggle" class="navigation__button"><span class="navigation__icon">&nbsp;</span></label>
            <nav class="navigation__nav">
                <ul class="navigation__list">
                    <li><a href="#home" class="navigation__link">Home</a></li>
                    <li><a href="#services" class="navigation__link">Services</a></li>
                    <li><a href="#review" class="navigation__link">Review</a></li>
                    <li><a href="#contact" class="navigation__link">Contact Us</a></li>
                </ul>
            </nav>
            <a href="registration.php"><button class="btn sign-in-button">Sign in</button></a>
        </div>
    </section>

    <!-- Website Banner -->
    <section class="home" id="home">
        <div class="swiper home-slider">
            <div class="swiper-wrapper">

                <div class="swiper-slide" data-slider="1"><!-- Banner 1 -->
                    <div class="box " style="background: url(assets/images/banner1.png) no-repeat;"></div>
                </div>

                <div class="swiper-slide" data-slider="2"><!-- Banner 2 -->
                    <div class="box " style="background: url(assets/images/banner2.png) no-repeat;"></div>
                </div>

                <div class="swiper-slide" data-slider="3"><!-- Banner 3 -->
                    <div class="box " style="background: url(assets/images/banner3.png) no-repeat;"></div>
                </div>

                <div class="swiper-slide" data-slider="3"><!-- Banner 3 -->
                    <div class="box " style="background: url(assets/images/banner4.png) no-repeat;"></div>
                </div>
            </div>
        </div>

        <div class="mouse-button">
            <a href="#experiences-section"><i class='bx bx-mouse'></i></a>
            <p>Scroll Down To View More Info</p>
        </div>

    </section>

    <section class="experiences" id="experiences-section">

        <div class="experiences-info-container">
            <h1>MANAGE YOUR RENTAL PROPERTIES WITH EASE USING TECHNOVATION HOUSE RENTAL SYSTEM</h1>
            <p>At Technovation House Rental System, we empower landlords with the tools they need to efficiently manage their rental properties. Our system simplifies the entire rental process, from listing your properties and screening tenants to collecting rental fees and handling maintenance requests. Whether you own a single property or multiple units, our innovative solutions ensure you have complete control and oversight, providing a seamless experience for both landlords and tenants.</p>

            <a href="#services" class="btn">Discover Now</a>
        </div>

        <div class="about-image">
            <div class="image-background">
                <img src="assets/images/house_rental_vector.png" alt="House Rental">
            </div>
        </div>

    </section>

    <section class="services-offered" id="services">
        <div class="services-wrapper">
            <div class="services-title">
                <img src="assets/images/support_icon.png" alt="offered_services">
                <h2>Our Offered Services</h2>
                <p>Explore our comprehensive services, meticulously designed to meet your rental management needs</p>
            </div>
            <div class="services-card-wrapper">
                <div class="services-card">
                    <img src="assets/images/service1.png" alt="Property Listing">
                    <h3>Property Listing</h3>
                    <p>List your rental properties effortlessly with our user-friendly platform, ensuring maximum visibility and engagement</p>
                </div>
                <div class="services-card">
                    <img src="assets/images/service2.png" alt="Maintenance Requests">
                    <h3>Maintenance Requests</h3>
                    <p>Our maintenance request service ensures that any issues with your properties are promptly addressed, keeping your tenants satisfied</p>
                </div>
                <div class="services-card">
                    <img src="assets/images/service3.png" alt="Rent Collection">
                    <h3>Rent Collection</h3>
                    <p>Streamline your rent collection process with our secure and efficient payment solutions, making it easier for both landlords and tenants</p>
                </div>
            </div>
        </div>
    </section>

    <section class="logos-slide">
        <h2>COLLABORATING PARTNERS</h2>
        <p>Proudly partnering with industry leaders to bring you exceptional experiences</p>
        <div class="logos-container">
            <img src="assets/images/uow-logo.png" alt="Company 1 Logo">
            <img src="assets/images/intel-logo.png" alt="Company 2 Logo">
            <img src="assets/images/Airbnb_Logo.png" alt="Company 3 Logo">
            <img src="assets/images/iBilik_logo.png" alt="Company 4 Logo">
            <img src="assets/images/tripadvisor-logo.png" alt="Company 5 Logo">
            <img src="assets/images/booking-logo.png" alt="Company 6 Logo">
            <img src="assets/images/tarumt_logo.png" alt="Company 7 Logo">
            <img src="assets/images/tesla-logo.png" alt="Company 8 Logo">
        </div>
    </section>

    <section class="customer-review" id="review">
        <div class="review-title">
            <img src="assets/images/rating-icon.png" alt="rating">
            <h2>Our Client Says</h2>
            <p>Don't just take our word for it, read what people say about us</p>
        </div>

        <div class="review-container">
            <div class="testimonial reviewSwiper">
                <div class="testi-content swiper-wrapper">

                    <div class="slide swiper-slide">
                        <img src="assets/images/face1.jpg" alt="" class="image" />
                        <div class="rating">
                            <i class='bx bxs-star' id="star"></i>
                        </div>
                        <p>Managing my rental properties has never been easier! Technovation House Rental System simplifies everything from tenant screening to rent collection. Their support team is always available, making my experience stress-free and efficient. Highly recommended!</p>
                        <i class="bx bxs-quote-alt-left quote-icon"></i>
                        <div class="details">
                            <span class="name">Amanda Lee</span>
                            <span class="name">Landlord</span>
                        </div>
                    </div>

                    <div class="slide swiper-slide">
                        <img src="assets/images/face2.jpg" alt="" class="image" />
                        <div class="rating">
                            <i class='bx bxs-star' id="star"></i>
                        </div>
                        <p>As a tenant, I appreciate the seamless experience Technovation House Rental System provides. From finding the perfect apartment to making secure online rent payments, everything is so convenient. Their platform is user-friendly and reliable. A great service for both landlords and tenants!</p>
                        <i class="bx bxs-quote-alt-left quote-icon"></i>
                        <div class="details">
                            <span class="name">David Morgan</span>
                            <span class="name">Tenant</span>
                        </div>
                    </div>

                    <div class="slide swiper-slide">
                        <img src="assets/images/face3.jpg" alt="" class="image" />
                        <div class="rating">
                            <i class='bx bxs-star' id="star"></i>
                        </div>
                        <p>The best rental management system I have ever used! Technovation House Rental System takes care of every detail, allowing me to focus on other aspects of my business. Their platform is intuitive, and their customer support is top-notch. I couldn't ask for a better experience!</p>
                        <i class="bx bxs-quote-alt-left quote-icon"></i>
                        <div class="details">
                            <span class="name">Jacob Thompson</span>
                            <span class="name">Landlord</span>
                        </div>
                    </div>
                </div>

                <div class="swiper-button-next nav-btn"></div>
                <div class="swiper-button-prev nav-btn"></div>
                <div class="swiper-pagination"></div>
            </div>
    </section>

    <section class="contact-container" id="contact">
        <div class="contact-title">
            <img src="assets/images/contact-icon.png" alt="contact">
            <h2>Contact Us</h2>
        </div>
        <div class="contact-content">
            <div class="content">

                <div class="address detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="topic">Address</div>
                    <div class="first-text">UOW Malaysia KDU Penang University College</div>
                    <div class="second-text">Lebuhraya Bandar Cassia, Batu Kawan, 14100, Penang</div>
                </div>

                <div class="phone detail">
                    <i class="fas fa-phone-alt"></i>
                    <div class="topic">Phone</div>
                    <div class="first-text">+045636000</div>
                    <div class="second-text">Technovation Consultant</div>
                </div>

                <div class="email detail">
                    <i class="fas fa-envelope"></i>
                    <div class="topic">Email</div>
                    <div class="first-text">technovation@gmail.com</div>
                </div>
            </div>

        </div>
    </section>

    <!--Footer Section-->
    <section class="footer">
        <div class="icons">
            <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ&ab_channel=RickAstley"><i class="fa fa-twitch"></i></a>
            <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ&ab_channel=RickAstley"><i class="fa fa-instagram"></i></a>
            <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ&ab_channel=RickAstley"><i class="fa fa-steam"></i></a>
            <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ&ab_channel=RickAstley"><i class="fa fa-youtube-play"></i></a>
        </div>
        <h6 style="color: rgb(255, 255, 255);">© <span id="currentYear"></span> Coded With Visual Studio Code - Website owned by Technovation Team</h6></a>
        <h6 style="color: rgb(255, 255, 255);">Coded By Chua Jun De & Chan Yi Soon Copyright Reserved</h6>
    </section>

    <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/slider.js"></script>
    <script src="assets/js/preloader.js"></script>
</body>

</html>