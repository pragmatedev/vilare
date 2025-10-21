/**
 * @param {FormData} payload
 */
export const ajax = async(payload) => {
  try {
    const response = await fetch(window.vilare.ajax, {
      method: 'POST',
      body: payload,
    });

    if (!response.ok) {
      throw new Error(`[${response.status}] ${response.statusText}`);
    }

    const data = await response.json();

    return {
      success: data.success,
      data: data.data || {},
    };
  } catch (error) {
    return {
      success: false,
      data: { message: error.message },
    };
  }
};

/**
 * @param {string} html
 */
export const node = (html) => {
  const template = document.createElement('template');

  template.innerHTML = html.trim();

  return template.content.firstChild;
};

/**
 * @param {HTMLElemnt} target
 * @param {number} duration
 * @param {number} offset
 * @returns
 */
export const scroll = async(target, duration = 500, offset = 0) => {
  document.documentElement.classList.remove('scroll-smooth');

  return new Promise((resolve) => {
    const start = window.scrollY;
    const stop = target.getBoundingClientRect().top + start - offset;
    const timestamp = performance.now();

    const ease = (t) => {
      return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
    };

    const animate = (time) => {
      const elapsed = time - timestamp;
      const progress = Math.min(elapsed / duration, 1);
      const amount = start + (stop - start) * ease(progress);

      window.scrollTo(0, amount);

      if (progress < 1) {
        window.requestAnimationFrame(animate);
      } else {
        document.documentElement.classList.add('scroll-smooth');
        resolve();
      }
    };

    window.requestAnimationFrame(animate);
  });
};
