
document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const accentSelect = document.getElementById('accentSelect');

  // Wczytanie z localStorage
    const savedTheme = localStorage.getItem('theme');
    const savedAccent = localStorage.getItem('accent');

    if (savedTheme === 'light') {
        body.classList.add('lightMode');
    }

    if (savedAccent === 'orange') {
        body.classList.add('accentOrange');
    } else {
        body.classList.add('accentGreen');
    }

  // Obsługa przełącznika motywu
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('lightMode');
            let butt = document.getElementById('themeToggle');
            butt.textContent = body.classList.contains('lightMode') ? '☀️' : '🌙';
            localStorage.setItem('theme', body.classList.contains('lightMode') ? 'light' : 'dark');
        });
    }

  // Obsługa wyboru akcentu
    if (accentSelect) {
        accentSelect.addEventListener('change', (e) => {
            body.classList.remove('accentGreen', 'accentOrange');
            const accent = e.target.value;
            body.classList.add(accent === 'orange' ? 'accentOrange' : 'accentGreen');
            localStorage.setItem('accent', accent);
        });
    }


    const burger   = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');

    burger.addEventListener('click', () => {
        const open = navLinks.classList.toggle('open');
        burger.classList.toggle('open', open);
        navLinks.classList.toggle('open',open);
        document.body.classList.toggle('menu-open', open);
        burger.setAttribute('aria-expanded', open);
    });

});

