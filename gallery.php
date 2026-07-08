<?php
// gallery.php
require_once __DIR__ . '/includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/scrollreveal"></script>

<style>
:root {
    --primary: #FF6B35;
    --accent: #FFBE0B;
    --dark: #1a1a2e;
}
body { font-family: 'Inter', sans-serif; background: #f8f7f4; }
h1,h2,h3,h4,h5,h6 { font-family: 'Poppins', sans-serif; }

/* ===== HERO ===== */
.gallery-hero {
    background: linear-gradient(rgba(0,0,0,0.55),rgba(0,0,0,0.65)),
                url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1400') center/cover fixed;
    min-height: 420px; border-radius: 28px;
    display: flex; align-items: center; justify-content: center;
    text-align: center; color: white;
    position: relative; overflow: hidden;
}
.gallery-hero::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,107,53,0.25) 0%, transparent 60%);
}
.hero-content { position: relative; z-index: 1; }

/* ===== FILTER PILLS ===== */
.filter-bar { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
.filter-pill {
    padding: 9px 20px; border-radius: 50px; font-weight: 600; font-size: 13px;
    border: 2px solid #e0e0e0; background: white; color: #555; cursor: pointer;
    transition: all 0.25s ease; display: flex; align-items: center; gap: 6px;
}
.filter-pill:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }
.filter-pill.active {
    background: var(--primary); color: white; border-color: var(--primary);
    box-shadow: 0 6px 20px rgba(255,107,53,0.35);
}
body.dark-mode .filter-pill { background: #1e1e30; border-color: #333; color: #ccc; }

/* ===== MASONRY GRID ===== */
.masonry-grid { column-count: 4; column-gap: 18px; }
@media(max-width:1199px){ .masonry-grid { column-count: 3; } }
@media(max-width:767px)  { .masonry-grid { column-count: 2; } }
@media(max-width:479px)  { .masonry-grid { column-count: 1; } }

.masonry-item {
    display: inline-block; width: 100%; margin-bottom: 18px;
    background: white; border-radius: 18px; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    border: 1px solid rgba(0,0,0,0.04);
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    break-inside: avoid;
}
.masonry-item:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.13); }
body.dark-mode .masonry-item { background: #1e1e30; }

.masonry-img-wrap { position: relative; overflow: hidden; cursor: pointer; }
.masonry-img { width: 100%; height: auto; object-fit: cover; display: block; transition: transform 0.4s ease; }
.masonry-item:hover .masonry-img { transform: scale(1.06); }

.img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.55) 0%, transparent 60%);
    opacity: 0; transition: opacity 0.3s ease;
    display: flex; align-items: flex-end; padding: 14px;
}
.masonry-item:hover .img-overlay { opacity: 1; }
.overlay-icon {
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,0.2); backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.4);
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 1rem; margin-left: auto;
}

.img-badge {
    position: absolute; top: 10px; left: 10px;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);
    color: white; font-size: 10px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;
}
.img-badge.hot { background: linear-gradient(135deg, #FF6B35, #e65100); }
.img-badge.new { background: linear-gradient(135deg, #22c55e, #16a34a); }
.img-badge.top { background: linear-gradient(135deg, #a18cd1, #c084fc); }

.card-meta { padding: 12px 14px 14px; }
.card-tag { font-size: 10px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; }
.card-name { font-size: 13px; font-weight: 700; color: #1a1a2e; margin: 3px 0 6px; line-height: 1.3; }
body.dark-mode .card-name { color: #f0f0f0; }
.card-stats { display: flex; justify-content: space-between; color: #999; font-size: 11px; padding-top: 8px; border-top: 1px solid #f0f0f0; }
body.dark-mode .card-stats { border-color: #333; }

/* ===== LIGHTBOX ===== */
.lightbox-modal {
    position: fixed; inset: 0; background: rgba(0,0,0,0.94);
    z-index: 10000; display: none; align-items: center; justify-content: center;
    backdrop-filter: blur(12px); padding: 20px;
}
.lightbox-modal.open { display: flex; }
.lightbox-inner { position: relative; max-width: 900px; width: 100%; text-align: center; }
.lightbox-img {
    max-height: 80vh; max-width: 100%; border-radius: 16px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.8);
    animation: lbFadeIn 0.3s ease;
}
@keyframes lbFadeIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }
.lightbox-title { color: white; font-weight: 700; font-size: 1.1rem; margin-top: 14px; font-family: 'Poppins',sans-serif; }
.lightbox-close {
    position: fixed; top: 20px; right: 24px;
    background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(8px); color: white; border-radius: 50%;
    width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1.2rem; transition: all 0.2s; z-index: 10001;
}
.lightbox-close:hover { background: rgba(255,255,255,0.25); transform: scale(1.1); }
.lightbox-nav {
    position: fixed; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(8px); color: white; border-radius: 50%;
    width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1.2rem; transition: all 0.2s; z-index: 10001;
}
.lightbox-nav:hover { background: var(--primary); border-color: var(--primary); transform: translateY(-50%) scale(1.1); }
.lightbox-prev { left: 20px; }
.lightbox-next { right: 20px; }

/* ===== VIDEO SECTION ===== */
.video-card { border-radius: 18px; overflow: hidden; position: relative; box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
.video-card video { width: 100%; height: 200px; object-fit: cover; display: block; }
.video-label {
    position: absolute; top: 10px; left: 10px;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(6px);
    color: white; font-size: 11px; font-weight: 700;
    padding: 4px 12px; border-radius: 20px;
}

/* ===== STATS ===== */
.stats-bar {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #FF6B35 100%);
    border-radius: 24px; padding: 48px 32px;
}
.stat-num { font-size: clamp(1.8rem,4vw,2.8rem); font-weight: 900; color: white; font-family:'Poppins',sans-serif; }
.stat-lbl { color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 600; margin-top: 4px; }

/* ===== INSTAGRAM GRID ===== */
.insta-grid { display: grid; grid-template-columns: repeat(6,1fr); gap: 4px; border-radius: 16px; overflow: hidden; }
@media(max-width:767px) { .insta-grid { grid-template-columns: repeat(3,1fr); } }
.insta-cell { position: relative; overflow: hidden; aspect-ratio: 1; cursor: pointer; }
.insta-cell img { width:100%; height:100%; object-fit:cover; transition: transform 0.4s ease; }
.insta-cell:hover img { transform: scale(1.1); }
.insta-cell-overlay {
    position: absolute; inset: 0; background: rgba(255,107,53,0.7);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.3s;
    color: white; font-size: 1.5rem;
}
.insta-cell:hover .insta-cell-overlay { opacity: 1; }
</style>

<!-- ===== HERO ===== -->
<div class="container py-4">
    <div class="gallery-hero mb-5">
        <div class="hero-content px-4">
            <span class="badge px-3 py-2 rounded-pill fw-bold mb-3" style="background:rgba(255,190,11,0.2); border:1px solid rgba(255,190,11,0.4); color:#FFBE0B;">
                <i class="fa-solid fa-camera me-1"></i> Visual Food Journey
            </span>
            <h1 class="display-4 fw-extrabold text-white mb-3">Crispy Bytes Gallery</h1>
            <p class="lead text-white/85 mb-4">500+ stunning food photos from our kitchen — burgers, pizzas, Indian street food & more!</p>
            <a href="#gallery-masonry" class="btn rounded-pill px-5 py-2 fw-bold" style="background:var(--accent); color:#1a1a2e;">
                <i class="fa-solid fa-images me-2"></i>Explore Gallery
            </a>
        </div>
    </div>

    <!-- ===== FILTER BAR ===== -->
    <div class="filter-bar mb-5" id="filterBar">
        <button class="filter-pill active" onclick="filterGallery('all')" id="gallery-btn-all">
            <i class="fa-solid fa-grid-2"></i> All <span class="badge bg-white/20 rounded-pill ms-1" style="color:inherit; background:rgba(255,255,255,0.25)!important;">42</span>
        </button>
        <button class="filter-pill" onclick="filterGallery('burgers')" id="gallery-btn-burgers">🍔 Burgers</button>
        <button class="filter-pill" onclick="filterGallery('pizza')" id="gallery-btn-pizza">🍕 Pizza</button>
        <button class="filter-pill" onclick="filterGallery('street')" id="gallery-btn-street">🇮🇳 Street Food</button>
        <button class="filter-pill" onclick="filterGallery('noodles')" id="gallery-btn-noodles">🍜 Noodles</button>
        <button class="filter-pill" onclick="filterGallery('fries')" id="gallery-btn-fries">🍟 Fries</button>
        <button class="filter-pill" onclick="filterGallery('desserts')" id="gallery-btn-desserts">🍩 Desserts</button>
        <button class="filter-pill" onclick="filterGallery('drinks')" id="gallery-btn-drinks">🥤 Drinks</button>
        <button class="filter-pill" onclick="filterGallery('sandwich')" id="gallery-btn-sandwich">🥙 Sandwich</button>
        <button class="filter-pill" onclick="filterGallery('kitchen')" id="gallery-btn-kitchen">👨‍🍳 Kitchen</button>
        <button class="filter-pill" onclick="filterGallery('restaurant')" id="gallery-btn-restaurant">🏪 Restaurant</button>
    </div>

    <!-- ===== MASONRY GALLERY ===== -->
    <div class="masonry-grid mb-5" id="gallery-masonry">

        <?php
        $galleryItems = [
            // BURGERS
            ['cat'=>'burgers','badge'=>'hot','title'=>'Maharaja Crispy Chicken Burger','tag'=>'🍔 Burgers','likes'=>340,'views'=>820,'chef'=>'Rahul','loc'=>'Grill Station','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500'],
            ['cat'=>'burgers','badge'=>'top','title'=>'Double Cheese Smash Burger','tag'=>'🍔 Burgers','likes'=>270,'views'=>640,'chef'=>'Ravi','loc'=>'Counter 2','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=500'],
            ['cat'=>'burgers','badge'=>'new','title'=>'Spicy Paneer Lava Burger','tag'=>'🍔 Burgers','likes'=>190,'views'=>430,'chef'=>'Aman','loc'=>'Grill Station','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1586816001966-79b736744398?w=500'],
            ['cat'=>'burgers','badge'=>'','title'=>'Classic Crispy Chicken Burger','tag'=>'🍔 Burgers','likes'=>210,'views'=>510,'chef'=>'Priya','loc'=>'Counter 1','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1608767221051-2b9d18f35a2f?w=500'],

            // PIZZA
            ['cat'=>'pizza','badge'=>'hot','title'=>'Stone Oven Margherita Pizza','tag'=>'🍕 Pizza','likes'=>320,'views'=>780,'chef'=>'Sonu','loc'=>'Baking Room','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500'],
            ['cat'=>'pizza','badge'=>'top','title'=>'Spicy Chicken Pepperoni Pizza','tag'=>'🍕 Pizza','likes'=>290,'views'=>690,'chef'=>'Kavya','loc'=>'Pizza Station','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500'],
            ['cat'=>'pizza','badge'=>'new','title'=>'Veg Supreme Cheese Burst Pizza','tag'=>'🍕 Pizza','likes'=>215,'views'=>500,'chef'=>'Divya','loc'=>'Oven Room','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=500'],
            ['cat'=>'pizza','badge'=>'','title'=>'BBQ Chicken Ranch Pizza','tag'=>'🍕 Pizza','likes'=>180,'views'=>420,'chef'=>'Arjun','loc'=>'Pizza Zone','rating'=>4.6,
             'img'=>'https://images.unsplash.com/photo-1528137871618-79d2761e3fd5?w=500'],

            // INDIAN STREET FOOD
            ['cat'=>'street','badge'=>'hot','title'=>'Crispy Aloo Samosa (2 Pcs)','tag'=>'🇮🇳 Street Food','likes'=>410,'views'=>980,'chef'=>'Meena','loc'=>'Street Corner','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1601050690597-df056fb4ce78?w=500'],
            ['cat'=>'street','badge'=>'top','title'=>'Special Butter Pav Bhaji','tag'=>'🇮🇳 Street Food','likes'=>370,'views'=>860,'chef'=>'Seema','loc'=>'Indian Counter','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1606491959413-26f60032e57a?w=500'],
            ['cat'=>'street','badge'=>'new','title'=>'Bombay Chatpata Bhel Puri','tag'=>'🇮🇳 Street Food','likes'=>285,'views'=>640,'chef'=>'Ritu','loc'=>'Chaat Zone','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1545231027-63b3f1e37be1?w=500'],
            ['cat'=>'street','badge'=>'hot','title'=>'Ghati Masala Vada Pav','tag'=>'🇮🇳 Street Food','likes'=>430,'views'=>1020,'chef'=>'Ganesh','loc'=>'Mumbai Corner','rating'=>5.0,
             'img'=>'https://images.unsplash.com/photo-1606491959007-aa7b6f88d7d9?w=500'],
            ['cat'=>'street','badge'=>'','title'=>'Spicy Mint Pani Puri (8 Pcs)','tag'=>'🇮🇳 Street Food','likes'=>500,'views'=>1180,'chef'=>'Anita','loc'=>'Chaat Counter','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=500'],
            ['cat'=>'street','badge'=>'top','title'=>'Punjabi Chole Bhature','tag'=>'🇮🇳 Street Food','likes'=>360,'views'=>820,'chef'=>'Gurpreet','loc'=>'Punjab Zone','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=500'],
            ['cat'=>'street','badge'=>'new','title'=>'Masala Dosa Classic','tag'=>'🇮🇳 Street Food','likes'=>290,'views'=>680,'chef'=>'Lakshmi','loc'=>'South Indian Zone','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1668236543090-82eba5ee5976?w=500'],
            ['cat'=>'street','badge'=>'','title'=>'Dahi Papdi Aloo Tikki Chaat','tag'=>'🇮🇳 Street Food','likes'=>310,'views'=>710,'chef'=>'Pooja','loc'=>'Chaat Corner','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1606491956689-2ea866880c84?w=500'],
            ['cat'=>'street','badge'=>'','title'=>'Khasta Pyaz Kachori (2 Pcs)','tag'=>'🇮🇳 Street Food','likes'=>250,'views'=>580,'chef'=>'Radha','loc'=>'Snacks Bar','rating'=>4.6,
             'img'=>'https://images.unsplash.com/photo-1601050690117-7eea8ae42de1?w=500'],

            // NOODLES
            ['cat'=>'noodles','badge'=>'hot','title'=>'Spicy Schezwan Noodles','tag'=>'🍜 Noodles','likes'=>380,'views'=>890,'chef'=>'Wei','loc'=>'Indo-Chinese','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=500'],
            ['cat'=>'noodles','badge'=>'new','title'=>'Veg Hakka Noodles','tag'=>'🍜 Noodles','likes'=>240,'views'=>560,'chef'=>'Raj','loc'=>'Wok Station','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=500'],
            ['cat'=>'noodles','badge'=>'','title'=>'Garlic Butter Noodles','tag'=>'🍜 Noodles','likes'=>190,'views'=>440,'chef'=>'Sunita','loc'=>'Indo-Chinese','rating'=>4.6,
             'img'=>'https://images.unsplash.com/photo-1552611052-33e04de081de?w=500'],
            ['cat'=>'noodles','badge'=>'top','title'=>'Creamy White Sauce Pasta','tag'=>'🍜 Pasta','likes'=>260,'views'=>610,'chef'=>'Rohini','loc'=>'Italian Corner','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=500'],
            ['cat'=>'noodles','badge'=>'','title'=>'Spicy Arrabbiata Red Pasta','tag'=>'🍜 Pasta','likes'=>200,'views'=>470,'chef'=>'Nikhil','loc'=>'Italian Corner','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=500'],

            // FRIES
            ['cat'=>'fries','badge'=>'hot','title'=>'Peri Peri Masala Fries','tag'=>'🍟 Fries','likes'=>420,'views'=>990,'chef'=>'Jay','loc'=>'Fry Station','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1576107232684-1279f390859f?w=500'],
            ['cat'=>'fries','badge'=>'new','title'=>'Cheesy Loaded Potato Fries','tag'=>'🍟 Fries','likes'=>350,'views'=>830,'chef'=>'Sam','loc'=>'Counter 3','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1630384060421-cb20d0e0649d?w=500'],
            ['cat'=>'fries','badge'=>'','title'=>'Golden Salted French Fries','tag'=>'🍟 Fries','likes'=>280,'views'=>660,'chef'=>'Kiran','loc'=>'Fry Zone','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1619881585019-7af6b1caad37?w=500'],
            ['cat'=>'fries','badge'=>'top','title'=>'Sriracha Crispy Fries Platter','tag'=>'🍟 Fries','likes'=>310,'views'=>740,'chef'=>'Tushar','loc'=>'Fry Station','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=500'],

            // DESSERTS
            ['cat'=>'desserts','badge'=>'hot','title'=>'Glazed Chocolate Donut','tag'=>'🍩 Desserts','likes'=>490,'views'=>1150,'chef'=>'Mia','loc'=>'Dessert Corner','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=500'],
            ['cat'=>'desserts','badge'=>'top','title'=>'Double Choco Chip Muffin','tag'=>'🍩 Desserts','likes'=>380,'views'=>880,'chef'=>'Aarti','loc'=>'Bakery','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1587960389148-81d2fae7c6cf?w=500'],
            ['cat'=>'desserts','badge'=>'new','title'=>'Choco Chip Cookie (3 Pcs)','tag'=>'🍪 Desserts','likes'=>310,'views'=>720,'chef'=>'Nisha','loc'=>'Bakery Counter','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=500'],
            ['cat'=>'desserts','badge'=>'hot','title'=>'Sizzling Chocolate Brownie','tag'=>'🍰 Desserts','likes'=>450,'views'=>1060,'chef'=>'Priya','loc'=>'Dessert Station','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1606312619070-d48b4c652a52?w=500'],
            ['cat'=>'desserts','badge'=>'','title'=>'Hot Chocolate Lava Cake','tag'=>'🍰 Desserts','likes'=>395,'views'=>920,'chef'=>'Sonia','loc'=>'Baking Room','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?w=500'],
            ['cat'=>'desserts','badge'=>'','title'=>'Double Scoop Vanilla Ice Cream','tag'=>'🍦 Desserts','likes'=>320,'views'=>760,'chef'=>'Rohan','loc'=>'Ice Cream Bar','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1497034825429-c343d7c6a68f?w=500'],
            ['cat'=>'desserts','badge'=>'new','title'=>'Strawberry Cheesecake Slice','tag'=>'🍰 Desserts','likes'=>270,'views'=>630,'chef'=>'Fiona','loc'=>'Dessert Corner','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1567171466295-4afa63d45416?w=500'],

            // DRINKS
            ['cat'=>'drinks','badge'=>'hot','title'=>'Premium Chocolate Milkshake','tag'=>'🥤 Drinks','likes'=>430,'views'=>1010,'chef'=>'Amit','loc'=>'Beverage Bar','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=500'],
            ['cat'=>'drinks','badge'=>'new','title'=>'Fresh Mint Mojito Mocktail','tag'=>'🥤 Drinks','likes'=>310,'views'=>740,'chef'=>'Sana','loc'=>'Mocktail Bar','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=500'],
            ['cat'=>'drinks','badge'=>'','title'=>'Classic Oreo Milkshake','tag'=>'🥤 Drinks','likes'=>280,'views'=>660,'chef'=>'Tina','loc'=>'Shake Counter','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500&blur=0'],
            ['cat'=>'drinks','badge'=>'top','title'=>'Creamy Cold Coffee Frappe','tag'=>'☕ Drinks','likes'=>350,'views'=>820,'chef'=>'Vikram','loc'=>'Coffee Bar','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=500'],
            ['cat'=>'drinks','badge'=>'','title'=>'Fresh Lemon Soda & Fizz','tag'=>'🍋 Drinks','likes'=>220,'views'=>520,'chef'=>'Rati','loc'=>'Beverage Bar','rating'=>4.6,
             'img'=>'https://images.unsplash.com/photo-1523371054106-bbf80586c38c?w=500'],

            // SANDWICH
            ['cat'=>'sandwich','badge'=>'hot','title'=>'Bombay Masala Grilled Sandwich','tag'=>'🥙 Sandwich','likes'=>300,'views'=>710,'chef'=>'Deepa','loc'=>'Grill Counter','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1528736235302-52922df5c122?w=500'],
            ['cat'=>'sandwich','badge'=>'','title'=>'Club Sandwich Tower','tag'=>'🥙 Sandwich','likes'=>250,'views'=>590,'chef'=>'Neha','loc'=>'Counter 2','rating'=>4.7,
             'img'=>'https://images.unsplash.com/photo-1627308595171-d1b5d67129c4?w=500'],
            ['cat'=>'sandwich','badge'=>'new','title'=>'Spinach Corn Cheese Sandwich','tag'=>'🥙 Sandwich','likes'=>195,'views'=>460,'chef'=>'Layla','loc'=>'Veg Counter','rating'=>4.6,
             'img'=>'https://images.unsplash.com/photo-1539252554453-80ab65ce3586?w=500'],

            // KITCHEN
            ['cat'=>'kitchen','badge'=>'','title'=>'Hygienic Kitchen Setup','tag'=>'👨‍🍳 Kitchen','likes'=>260,'views'=>620,'chef'=>'Chef Team','loc'=>'Main Kitchen','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=500'],
            ['cat'=>'kitchen','badge'=>'','title'=>'Pizza Dough Kneading Process','tag'=>'👨‍🍳 Kitchen','likes'=>220,'views'=>520,'chef'=>'Sonu','loc'=>'Baking Room','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1509722747041-616f39b57ef3?w=500'],
            ['cat'=>'kitchen','badge'=>'','title'=>'Chef Grilling the Patties','tag'=>'👨‍🍳 Kitchen','likes'=>310,'views'=>730,'chef'=>'Rahul','loc'=>'Grill Zone','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1507048331197-7d4ac70811cf?w=500'],

            // RESTAURANT
            ['cat'=>'restaurant','badge'=>'','title'=>'Premium Dining Ambience','tag'=>'🏪 Restaurant','likes'=>400,'views'=>950,'chef'=>'Management','loc'=>'Jaipur Lounge','rating'=>4.9,
             'img'=>'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=500'],
            ['cat'=>'restaurant','badge'=>'','title'=>'Outdoor Garden Seating','tag'=>'🏪 Restaurant','likes'=>330,'views'=>780,'chef'=>'Management','loc'=>'Garden Area','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=500'],
            ['cat'=>'restaurant','badge'=>'new','title'=>'Vibrant Cosy Interior Decor','tag'=>'🏪 Restaurant','likes'=>290,'views'=>680,'chef'=>'Design Team','loc'=>'Main Hall','rating'=>4.8,
             'img'=>'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=500'],
        ];

        foreach ($galleryItems as $idx => $item):
        $badgeClass = $item['badge'] === 'hot' ? 'hot' : ($item['badge'] === 'new' ? 'new' : ($item['badge'] === 'top' ? 'top' : ''));
        $badgeLabel = $item['badge'] === 'hot' ? '🔥 Hot' : ($item['badge'] === 'new' ? '✨ New' : ($item['badge'] === 'top' ? '⭐ Top Pick' : ''));
        ?>
        <div class="masonry-item gallery-card" data-category="<?= $item['cat'] ?>" data-idx="<?= $idx ?>">
            <div class="masonry-img-wrap"
                 onclick="openLightbox(<?= $idx ?>)"
                 title="<?= htmlspecialchars($item['title']) ?>">
                <img src="<?= $item['img'] ?>"
                     class="masonry-img"
                     alt="<?= htmlspecialchars($item['title']) ?>"
                     loading="lazy"
                     onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=500'">
                <?php if ($badgeLabel): ?>
                <span class="img-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                <?php endif; ?>
                <div class="img-overlay">
                    <div class="overlay-icon"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                </div>
            </div>
            <div class="card-meta">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="card-tag"><?= $item['tag'] ?></span>
                    <span style="color:#f59e0b; font-size:11px;"><i class="fa-solid fa-star"></i> <?= $item['rating'] ?></span>
                </div>
                <div class="card-name"><?= htmlspecialchars($item['title']) ?></div>
                <div style="font-size:11px; color:#999; margin-bottom:6px;">
                    👨‍🍳 <?= $item['chef'] ?> &nbsp;|&nbsp; 📍 <?= $item['loc'] ?>
                </div>
                <div class="card-stats">
                    <span><i class="fa-regular fa-heart me-1"></i><?= $item['likes'] ?> Likes</span>
                    <span><i class="fa-regular fa-eye me-1"></i><?= number_format($item['views']) ?> Views</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div><!-- /masonry-grid -->

    <!-- ===== INSTAGRAM-STYLE QUICK GRID ===== -->
    <div class="mb-5 reveal-section">
        <h4 class="fw-bold text-center mb-1"><i class="fa-brands fa-instagram me-2" style="color:#e1306c;"></i>Instagram Wall</h4>
        <p class="text-center text-muted small mb-4">Follow us <strong>@crispybytes.jaipur</strong> for daily food updates</p>
        <div class="insta-grid">
            <?php
            $instaImgs = [
                'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300',
                'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=300', // Biryani
                'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=300',
                'https://images.unsplash.com/photo-1576107232684-1279f390859f?w=300',
                'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=300',
                'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=300',
                'https://images.unsplash.com/photo-1544025162-d76694265947?w=300', // Sizzling meat / paneer
                'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=300',
                'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=300',
                'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300', // Veg bowl
                'https://images.unsplash.com/photo-1668236543090-82eba5ee5976?w=300',
                'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=300',
            ];
            foreach ($instaImgs as $iImg):
            ?>
            <div class="insta-cell">
                <img src="<?= $iImg ?>" alt="Food" loading="lazy">
                <div class="insta-cell-overlay"><i class="fa-brands fa-instagram"></i></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ===== VIDEO SECTION ===== -->
    <div class="mb-5 reveal-section">
        <h4 class="fw-bold text-center mb-1"><i class="fa-solid fa-circle-play text-danger me-2"></i>Kitchen Recipe Reels</h4>
        <p class="text-center text-muted small mb-4">Watch our chefs craft every dish with passion and precision</p>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="video-card">
                    <span class="video-label">🍕 Pizza Dough (0:45)</span>
                    <video autoplay loop muted playsinline>
                        <source src="https://v1.mixkit.co/videos/preview/mixkit-kneading-dough-on-a-floured-surface-41551-large.mp4" type="video/mp4">
                    </video>
                </div>
                <h6 class="fw-bold text-center mt-2 mb-0">Pizza Base Kneading</h6>
                <p class="text-center text-muted small">Watch our expert baker craft the perfect crust</p>
            </div>
            <div class="col-md-4">
                <div class="video-card">
                    <span class="video-label">🍔 Burger Grill (1:05)</span>
                    <video autoplay loop muted playsinline>
                        <source src="https://v1.mixkit.co/videos/preview/mixkit-cooking-hamburgers-on-a-hot-grill-41589-large.mp4" type="video/mp4">
                    </video>
                </div>
                <h6 class="fw-bold text-center mt-2 mb-0">Burger Grilling Station</h6>
                <p class="text-center text-muted small">Flame-kissed patties on our premium grill</p>
            </div>
            <div class="col-md-4">
                <div class="video-card">
                    <span class="video-label">🍰 Dessert (0:30)</span>
                    <video autoplay loop muted playsinline>
                        <source src="https://v1.mixkit.co/videos/preview/mixkit-pouring-chocolate-sauce-on-a-souffle-41595-large.mp4" type="video/mp4">
                    </video>
                </div>
                <h6 class="fw-bold text-center mt-2 mb-0">Chocolate Lava Souffle</h6>
                <p class="text-center text-muted small">Rich dark chocolate poured over fluffy souffle</p>
            </div>
        </div>
    </div>

    <!-- ===== BEFORE/AFTER ===== -->
    <div class="card border-0 p-4 p-md-5 mb-5 shadow-sm text-center reveal-section" style="border-radius:24px; background:white;">
        <h5 class="fw-bold mb-1"><i class="fa-solid fa-wand-magic-sparkles text-warning me-2"></i>From Farm to Plate</h5>
        <p class="text-muted small mb-4">See how fresh ingredients transform into your favourite dishes</p>
        <div class="row g-4 justify-content-center align-items-center">
            <div class="col-md-5">
                <h6 class="fw-bold text-muted mb-2">Fresh Farm Ingredients</h6>
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500" class="img-fluid rounded-4 shadow-sm" alt="Fresh Ingredients">
            </div>
            <div class="col-md-2 d-none d-md-flex flex-column align-items-center">
                <div style="font-size:2.5rem; color:var(--primary);">→</div>
                <small class="text-muted fw-bold">Magic</small>
            </div>
            <div class="col-md-5">
                <h6 class="fw-bold text-muted mb-2">Final Premium Dish</h6>
                <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500" class="img-fluid rounded-4 shadow-sm" alt="Final Dish">
            </div>
        </div>
    </div>

    <!-- ===== STATS ===== -->
    <div class="stats-bar mb-5 reveal-section">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="stat-num" id="stat-photos">0</div>
                <div class="stat-lbl">Gallery Photos</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num" id="stat-vids">0</div>
                <div class="stat-lbl">Recipe Videos</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num" id="stat-views">0</div>
                <div class="stat-lbl">Total Views</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-num" id="stat-likes">0</div>
                <div class="stat-lbl">Total Likes</div>
            </div>
        </div>
    </div>

    <!-- ===== CTA ===== -->
    <div class="card border-0 p-4 p-md-5 text-white mb-4 shadow-sm reveal-section"
         style="border-radius:24px; background:linear-gradient(135deg,#1a1a2e 0%,#FF6B35 100%);">
        <div class="row align-items-center g-4">
            <div class="col-md-8">
                <h3 class="fw-extrabold mb-2">Hungry After Scrolling? 😋</h3>
                <p class="mb-3" style="color:rgba(255,255,255,0.8);">Order any dish you saw in the gallery — delivered fresh to your doorstep in 30 minutes!</p>
                <div class="d-flex flex-wrap gap-3 text-sm mb-4" style="color:rgba(255,255,255,0.7);">
                    <span>⭐ 4.9 Rating</span>
                    <span>🚚 30-Min Delivery</span>
                    <span>🎁 Free Delivery above ₹299</span>
                </div>
                <a href="menu.php" class="btn rounded-pill px-5 py-2 fw-bold" style="background:var(--accent); color:#1a1a2e;">
                    <i class="fa-solid fa-utensils me-2"></i>Order Now
                </a>
            </div>
            <div class="col-md-4 text-center d-none d-md-block">
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=350" class="img-fluid rounded-4" alt="Order Now" style="box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            </div>
        </div>
    </div>

</div><!-- /container -->

<!-- ===== LIGHTBOX ===== -->
<div id="lightbox-container" class="lightbox-modal" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="fa-solid fa-xmark"></i></button>
    <button class="lightbox-nav lightbox-prev" onclick="event.stopPropagation(); navigateLightbox(-1)">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button class="lightbox-nav lightbox-next" onclick="event.stopPropagation(); navigateLightbox(1)">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
    <div class="lightbox-inner" onclick="event.stopPropagation()">
        <img id="lightbox-img" src="" class="lightbox-img" alt="">
        <div class="lightbox-title" id="lightbox-title"></div>
        <div style="color:rgba(255,255,255,0.5); font-size:12px; margin-top:6px;" id="lightbox-counter"></div>
    </div>
</div>

<script>
// ===== GALLERY DATA for lightbox =====
const galleryData = [
    <?php foreach ($galleryItems as $item): ?>
    { img: '<?= str_replace("'","\\'",$item['img']) ?>', title: '<?= htmlspecialchars(addslashes($item['title'])) ?>', cat: '<?= $item['cat'] ?>' },
    <?php endforeach; ?>
];

let currentLbIdx = 0;
let visibleIndices = [];

function buildVisible() {
    visibleIndices = [];
    document.querySelectorAll('.gallery-card:not([style*="none"])').forEach(el => {
        visibleIndices.push(parseInt(el.dataset.idx));
    });
}

function openLightbox(idx) {
    buildVisible();
    currentLbIdx = visibleIndices.indexOf(idx);
    if (currentLbIdx === -1) currentLbIdx = 0;
    showLightboxItem();
    document.getElementById('lightbox-container').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function showLightboxItem() {
    const idx = visibleIndices[currentLbIdx];
    const item = galleryData[idx];
    const img = document.getElementById('lightbox-img');
    img.style.opacity = '0';
    img.src = item.img.replace('w=500','w=900');
    img.onload = () => { img.style.transition = 'opacity 0.3s'; img.style.opacity = '1'; };
    document.getElementById('lightbox-title').innerText = item.title;
    document.getElementById('lightbox-counter').innerText = `${currentLbIdx + 1} / ${visibleIndices.length}`;
}

function navigateLightbox(dir) {
    currentLbIdx = (currentLbIdx + dir + visibleIndices.length) % visibleIndices.length;
    showLightboxItem();
}

function closeLightbox() {
    document.getElementById('lightbox-container').classList.remove('open');
    document.body.style.overflow = '';
}

// Keyboard navigation
document.addEventListener('keydown', e => {
    if (!document.getElementById('lightbox-container').classList.contains('open')) return;
    if (e.key === 'ArrowRight') navigateLightbox(1);
    if (e.key === 'ArrowLeft')  navigateLightbox(-1);
    if (e.key === 'Escape')     closeLightbox();
});

// ===== FILTER =====
function filterGallery(cat) {
    // Update pills
    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    const activeBtn = document.getElementById('gallery-btn-' + cat);
    if (activeBtn) activeBtn.classList.add('active');

    // Filter cards
    document.querySelectorAll('.gallery-card').forEach(card => {
        const cardCat = card.dataset.category;
        const show = cat === 'all' || cardCat === cat;
        card.style.display = show ? 'inline-block' : 'none';
        if (show) {
            card.style.animation = 'none';
            card.offsetHeight; // reflow
            card.style.animation = 'fadeInUp 0.4s ease forwards';
        }
    });
}

// Add fadeInUp animation
const style = document.createElement('style');
style.textContent = `
  @keyframes fadeInUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
  }
`;
document.head.appendChild(style);

// ===== COUNTER =====
function animateCounter(id, target, suffix='', speed=20) {
    const el = document.getElementById(id);
    if (!el) return;
    let current = 0;
    const step = Math.ceil(target / 60);
    const t = setInterval(() => {
        current = Math.min(current + step, target);
        el.innerText = current.toLocaleString() + suffix;
        if (current >= target) clearInterval(t);
    }, speed);
}

document.addEventListener('DOMContentLoaded', function() {
    ScrollReveal().reveal('.reveal-section', { delay:150, origin:'bottom', distance:'40px', interval:100, reset:false });

    const statsOb = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) {
            animateCounter('stat-photos', 500, '+');
            animateCounter('stat-vids', 50, '+');
            animateCounter('stat-views', 20000, '+');
            animateCounter('stat-likes', 5000, '+');
            statsOb.disconnect();
        }
    });
    const statEl = document.getElementById('stat-photos');
    if (statEl) statsOb.observe(statEl.closest('.stats-bar'));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
