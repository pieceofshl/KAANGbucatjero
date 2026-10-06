/* =====================================================
   EXPLORE PAGE — Interactive Enhancements
   ===================================================== */
(function() {
  'use strict';

  /* ---------- 1. Scroll Reveal untuk setiap kartu ---------- */
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add('revealed');
        }, i * 60);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -80px 0px' });

  document.querySelectorAll('.card, .list-item').forEach(el => {
    el.classList.add('reveal');
    observer.observe(el);
  });

  /* ---------- 2. Hover effect: parallax tilt pada kartu ---------- */
  document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      card.style.transform = `translateY(-8px) rotateX(${-y * 6}deg) rotateY(${x * 6}deg)`;
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });

  /* ---------- 3. Search bar auto-focus + typing animation ---------- */
  const searchInput = document.querySelector('.explore-search input');
  if (searchInput && !searchInput.value) {
    const phrases = [
      'Cari tempat, kuliner, alamat...',
      'Coba: "Situ Bagendit"',
      'Coba: "Kopi Hejo Cikajang"',
      'Coba: "hidden gem"'
    ];
    let idx = 0;
    searchInput.placeholder = phrases[0];
    setInterval(() => {
      idx = (idx + 1) % phrases.length;
      let text = '';
      let i = 0;
      const target = phrases[idx];
      const typeInterval = setInterval(() => {
        text += target[i];
        searchInput.placeholder = text;
        i++;
        if (i >= target.length) clearInterval(typeInterval);
      }, 40);
    }, 4000);
  }

  /* ---------- 4. Filter pill — smooth ripple saat klik ---------- */
  document.querySelectorAll('.filter-pill').forEach(pill => {
    pill.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      ripple.className = 'pill-ripple';
      const rect = this.getBoundingClientRect();
      ripple.style.left = (e.clientX - rect.left) + 'px';
      ripple.style.top = (e.clientY - rect.top) + 'px';
      this.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });
  });

  /* ---------- 5. Counter animation untuk mini stats ---------- */
  const counters = document.querySelectorAll('.explore-mini-stats b');
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.textContent, 10);
        if (isNaN(target)) return;
        let current = 0;
        const step = Math.max(1, Math.ceil(target / 30));
        const tick = setInterval(() => {
          current += step;
          if (current >= target) {
            current = target;
            clearInterval(tick);
          }
          el.textContent = current;
        }, 30);
        counterObserver.unobserve(el);
      }
    });
  }, { threshold: 0.5 });
  counters.forEach(c => counterObserver.observe(c));

  /* ---------- 6. Sticky filter bar — shadow saat scroll ---------- */
  const filterBar = document.querySelector('.filter-bar');
  if (filterBar) {
    window.addEventListener('scroll', () => {
      filterBar.classList.toggle('scrolled', window.scrollY > 200);
    });
  }

})();