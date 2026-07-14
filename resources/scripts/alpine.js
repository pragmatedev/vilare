import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('vilare:init', () => {
  Alpine.start();
});
