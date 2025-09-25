document.addEventListener('alpine:init', () => {
  window.Alpine.data('block_guide', () => ({
    tooltip: false,

    copy(content) {
      navigator.clipboard.writeText(content);
      this.tooltip = true;
      setTimeout(() => {
        this.tooltip = false;
      }, 1500);
    },
  }));
});
