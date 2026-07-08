// assets/js/app.js

// Live Order Ticker Component dynamic messages list
const liveMessages = [
    "🟢 LIVE: Chef Ajay is preparing a Maharaja Crispy Burger right now in the Malviya Nagar Kitchen! 🍔",
    "🟢 LIVE: Order #1482 of Special Butter Pav Bhaji has just dispatched from the Raja Park Station! 🛵",
    "🟢 LIVE: Fresh batch of Glazed Chocolate Donuts has been baked at Vaishali Nagar Kitchen! 🍩",
    "🟢 LIVE: Chef Rohan is tossing Spicy Schezwan Noodles in the Wok at C-Scheme Kitchen! 🍜"
];

// Rotate ticker messages
let currentTickerIdx = 0;
function rotateTickerMessage() {
    const tickerEl = document.getElementById('liveTickerMessage');
    if (tickerEl) {
        tickerEl.style.opacity = '0';
        setTimeout(() => {
            tickerEl.innerText = liveMessages[currentTickerIdx];
            tickerEl.style.opacity = '1';
            currentTickerIdx = (currentTickerIdx + 1) % liveMessages.length;
        }, 300);
    }
}

// Taste Profiler visual filtering logic
function filterByTaste(mood) {
    const cards = document.querySelectorAll('#real-products-grid .col-md-6, #street-food-grid .col-6');
    
    // Reset visual indicator active states
    document.querySelectorAll('.taste-profiler-badge').forEach(b => b.classList.remove('border-dark'));
    event.currentTarget.classList.add('border-dark');

    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        let matches = false;
        
        if (mood === 'all') {
            matches = true;
        } else if (mood === 'spicy') {
            matches = text.includes('spicy') || text.includes('schezwan') || text.includes('chilli') || text.includes('peri peri');
        } else if (mood === 'creamy') {
            matches = text.includes('butter') || text.includes('cheese') || text.includes('cream') || text.includes('muffin') || text.includes('donut');
        } else if (mood === 'diet') {
            matches = text.includes('salad') || text.includes('diet') || text.includes('bhel puri') || text.includes('sprout') || text.includes('veg');
        }

        if (matches) {
            card.style.display = 'block';
            card.style.animation = 'fadeInUp 0.3s ease forwards';
        } else {
            card.style.display = 'none';
        }
    });
}

// Initialize animations and dynamic features
document.addEventListener('DOMContentLoaded', function() {
    // Start ticker rotation
    setInterval(rotateTickerMessage, 5000);
    rotateTickerMessage();

    // Smoothly hide index animated loader
    const loader = document.getElementById('index-loader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 600);
        }, 800);
    }
});
