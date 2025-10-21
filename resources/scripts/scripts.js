import { provide } from '@scripts/utilities';

document.addEventListener('alpine:init', () => {
  window.Alpine.data('vilare', () => ({
    init() {
      provide(window.vilare);
    },
  }));
});
