document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  /**
   * Theme Switcher
   */
  const themeSwitcher = document.getElementById('theme-switcher');
  const sunIcon = '<i class="bi bi-sun-fill"></i>';
  const moonIcon = '<i class="bi bi-moon-fill"></i>';

  // Function to set the theme
  const setTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    if (theme === 'light') {
      themeSwitcher.innerHTML = moonIcon;
    } else {
      themeSwitcher.innerHTML = sunIcon;
    }
  };

  // Get the saved theme from localStorage or default to dark
  const savedTheme = localStorage.getItem('theme') || 'dark';
  setTheme(savedTheme);

  // Event listener for the switcher button
  themeSwitcher.addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
  });

  /**
   * AOS (Animate on Scroll) Initialization
   */
  AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    mirror: false,
  });

  /**
   * Scrolled Header
   */
  const header = document.querySelector('.navbar');
  if (header) {
    const handleScroll = () => {
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    };
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Initial check
  }

  /**
   * Counter Up Animation
   */
  const counters = document.querySelectorAll('.counter');
  const animationDuration = 1500; // Duração da animação em milissegundos

  const animateCounter = (counter) => {
    const target = +counter.getAttribute('data-target');
    const prefix = counter.getAttribute('data-prefix') || '';
    const suffix = counter.getAttribute('data-suffix') || '';
    let startTime = null;

    const updateCount = (timestamp) => {
      if (!startTime) startTime = timestamp;
      const progress = timestamp - startTime;

      // Calcula a proporção do tempo decorrido
      const ratio = Math.min(progress / animationDuration, 1);

      // Calcula o valor atual baseado na proporção
      const currentValue = Math.floor(ratio * target);

      // Formata e exibe
      counter.innerText =
        prefix + currentValue.toLocaleString('pt-BR') + suffix;

      // Continua a animação até que a proporção seja 1 (100% concluído)
      if (ratio < 1) {
        requestAnimationFrame(updateCount);
      } else {
        // Garante que o valor final exato seja exibido
        counter.innerText = prefix + target.toLocaleString('pt-BR') + suffix;
      }
    };

    requestAnimationFrame(updateCount);
  };

  const observer = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.5,
    },
  );

  counters.forEach((counter) => {
    observer.observe(counter);
  });
});

// --- LÓGICA DO PRELOADER DE NAVEGAÇÃO ---
document.addEventListener('DOMContentLoaded', () => {

    const loginLink = document.getElementById('login-link');
    const preloader = document.getElementById('navigation-preloader');

    if (loginLink && preloader) {
        loginLink.addEventListener('click', function(event) {
            // Previne a navegação imediata
            event.preventDefault();

            // Guarda o destino do link e a URL da imagem de loading
            const destination = this.href;
            const preloaderImgSrc = preloader.querySelector('img').src;

            // Cria uma nova imagem em memória para verificar o carregamento
            const img = new Image();
            img.src = preloaderImgSrc;

            // Esta função só executa DEPOIS que a imagem de loading estiver pronta
            img.onload = () => {
                // Mostra a tela verde com a animação já carregada
                preloader.classList.add('active');

                // Aguarda um tempo maior e então redireciona
                setTimeout(() => {
                    window.location.href = destination;
                }, 2850);
            };

            // Fallback: se a imagem falhar ao carregar, navega imediatamente
            img.onerror = () => {
                window.location.href = destination;
            };
        });
    }
});
