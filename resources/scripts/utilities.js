/**
 * Send a POST request to the AJAX endpoint.
 *
 * @param {FormData} payload
 * @returns {Promise<{success: boolean, data: object}>}
 */
/**
 * Send a POST request to the AJAX endpoint.
 *
 * @param {object} payload
 * @param {FormData} body
 * @returns {Promise<{success: boolean, data: object}>}
 */
export const ajax = async(payload, body = new FormData()) => {
  try {
    Object.keys(payload).forEach(key => body.append(key, payload[key]));

    const response = await fetch(window.vilare.ajax_url, {
      method: 'POST',
      body,
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
 * Smooth scroll to a target element.
 *
 * @param {HTMLElemnt} target
 * @param {number} duration
 * @param {number} offset
 * @returns {Promise<void>}
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

/**
 * Create a new node from HTML string.
 *
 * @param {string} html
 * @returns {HTMLElement}
 */
export const node = (html) => {
  const template = document.createElement('template');

  template.innerHTML = html.trim();

  return template.content.firstChild;
};

/**
 * Provide the utilities to the global object.
 *
 * @param {object} object
 * @returns {void}
 */
export const provide = (object) => {
  if (!object.ajax) {
    object.ajax = ajax;
  }

  if (!object.scroll) {
    object.scroll = scroll;
  }
};
