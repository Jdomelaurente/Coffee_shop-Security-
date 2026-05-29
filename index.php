<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (($_SESSION['role'] ?? '') === 'superadmin') {
        header("Location: superadmin_dash.php");
    } elseif (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: admin_dash.php");
    } else {
        header("Location: pos.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalinga Coffee | Pinoy's Favorite</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/home-formal.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<header class="formal-header">
    <div class="header-container">
        <a href="#" class="formal-logo">
            <div class="logo-text-wrapper">
                <span class="logo-text">KALINGA COFFEE</span>
                <span class="logo-subtext">MASANG KAPE</span>
            </div>
        </a>
        <nav class="formal-nav">
            <a href="#home">Home</a>
            <a href="#about">Our Story</a>
            <a href="#menu">Favorites</a>
            <a href="#contact">Contact</a>
        </nav>
        <div class="header-actions">
            <button class="formal-btn-outline" onclick="window.location.href='login.php'" style="width: auto;">
                <i class="fas fa-user"></i> Login
            </button>
            <div id="mobile-menu-btn" class="fas fa-bars"></div>
        </div>
    </div>
</header>

<section class="formal-hero" id="home">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-subtitle">SINCE 2024</span>
        <h1 class="hero-title">Coffee for<br>Every Filipino</h1>
        <p class="hero-desc">Start your morning with the aroma and taste of our local coffee. Affordable, delicious, and Proudly Filipino.</p>
        <div class="hero-actions">
            <button class="formal-btn-primary" onclick="window.location.href='login.php'" style="width: auto;">Order Now</button>
            <a href="#menu" class="formal-link">View Menu <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<section class="formal-features">
    <div class="feature-grid">
        <div class="feature-card">
            <i class="fas fa-seedling"></i>
            <h3>Local Beans</h3>
            <p>Directly from our hardworking local farmers.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-wallet"></i>
            <h3>Affordable Prices</h3>
            <p>Quality blends that won't break the community's budget.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-motorcycle"></i>
            <h3>Fast Delivery</h3>
            <p>Arrives hot and fresh straight to your doorstep.</p>
        </div>
    </div>
</section>

<section class="formal-about" id="about">
    <div class="about-grid">
        <div class="about-image-wrapper">
            <div class="image-frame">
                <img src="assets/images/about-img.png" alt="Brewing coffee" class="about-img">
            </div>
            <div class="experience-badge">
                <span class="years">100%</span>
                <span class="text">Local<br>Made</span>
            </div>
        </div>
        <div class="about-content">
            <span class="section-tag">Our Story</span>
            <h2 class="section-title">A Filipino Taste You'll Keep Coming Back To</h2>
            <p class="section-desc">We believe that great coffee shouldn't be expensive. That's why we've worked hard to create coffee that fits the taste and budget of every Filipino.</p>
            <p class="section-desc">From the rich soil of Kalinga to your cup, we ensure a smile in every sip.</p>
            <a href="#" class="formal-btn-secondary">Get to Know Us</a>
        </div>
    </div>
</section>

<section class="formal-menu" id="menu">
    <div class="menu-header">
        <span class="section-tag">Aming Kape</span>
        <h2 class="section-title text-center">Paborito ng Bayan</h2>
        <p class="menu-subtitle">Subukan ang aming mga best-seller! Swak pang-agahan o pang-meryenda.</p>
    </div>
    
    <div class="menu-grid">
        <?php 
        $coffees = [
            ['name' => 'Classic Barako', 'price' => '₱65.00', 'desc' => 'Matapang at mabango mula sa lupain ng Batangas.'],
            ['name' => 'Kape at Pandesal', 'price' => '₱45.00', 'desc' => 'Ang paboritong agahan ng bawat Pinoy.'],
            ['name' => 'Rich Tsokolate', 'price' => '₱85.00', 'desc' => 'Gawa sa purong tablea, malapot at masarap.'],
            ['name' => 'Iced Sagada', 'price' => '₱95.00', 'desc' => 'Malamig na kape mula sa matataas na bundok.'],
            ['name' => 'Kapeng Matamis', 'price' => '₱75.00', 'desc' => 'May kasamang kondensada para sa tamis na hanap.'],
            ['name' => 'Paborito ng Bayan', 'price' => '₱90.00', 'desc' => 'Ang aming best seller na swak sa panlasa mo.']
        ];
        
        foreach($coffees as $index => $coffee): 
            $i = ($index % 6) + 1;
        ?>
        <div class="menu-item">
            <div class="menu-item-img-container">
                <img src="assets/coffee/Kape-<?php echo $i; ?>-removebg-preview.png" alt="<?php echo $coffee['name']; ?>">
                <div class="menu-price-tag"><?php echo $coffee['price']; ?></div>
            </div>
            <div class="menu-item-info">
                <h3><?php echo $coffee['name']; ?></h3>
                <p><?php echo $coffee['desc']; ?></p>
                <button class="add-to-cart-btn"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<footer class="formal-footer" id="contact">
    <div class="footer-top">
        <div class="footer-brand">
            <a href="#" class="formal-logo light">
                <div class="logo-text-wrapper">
                    <span class="logo-text">KALINGA COFFEE</span>
                    <span class="logo-subtext">MASANG KAPE</span>
                </div>
            </a>
            <p>Maligayang pagdating sa Kalinga Coffee! Dito, bawat higop ay parang yakap ng kaibigan.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        <div class="footer-links-group">
            <div class="footer-column">
                <h4>Aming Lokasyon</h4>
                <p>Cabadbaran</p>
                <p>Magallanes</p>
                <p>RTR</p>
                <p>Tubay</p>
            </div>
            <div class="footer-column">
                <h4>Quick Links</h4>
                <a href="#home">Home</a>
                <a href="#about">Our Story</a>
                <a href="#menu">Menu</a>
            </div>
            <div class="footer-column">
                <h4>Contact Us</h4>
                <a href="tel:09272308675">0927-230-8675</a>
                <a href="mailto:jdomelaurente@gmail.com">jdomelaurente@gmail.com</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Kalinga Coffee. All Rights Reserved.</p>
    </div>
</footer>


<script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    const header = document.querySelector('.formal-header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Mobile menu toggle
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        const nav = document.querySelector('.formal-nav');
        if(nav.style.display === 'flex') {
            nav.style.display = 'none';
        } else {
            nav.style.display = 'flex';
            nav.style.flexDirection = 'column';
            nav.style.position = 'absolute';
            nav.style.top = '100%';
            nav.style.left = '0';
            nav.style.right = '0';
            nav.style.background = '#FFFFFF';
            nav.style.padding = '20px';
            nav.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
        }
    });
</script>
</body>
</html>
