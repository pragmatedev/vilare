document.addEventListener('alpine:init', () => {
  window.Alpine.data('block_guide', () => ({
    tooltip: false,

    copy(content) {
      navigator.clipboard.writeText(content);

      this.tooltip = true;

      if (this.timeout) {
        clearTimeout(this.timeout);
      }

      this.timeout = setTimeout(() => {
        this.tooltip = false;
        this.timeout = null;
      }, 1000);
    },
  }));
});
