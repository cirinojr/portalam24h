(function () {
  'use strict';

  const copyButtons = document.querySelectorAll('[data-share-copy]');

  if (!copyButtons.length) {
    return;
  }

  const statusElement = document.querySelector('[data-share-copy-status]');
  const container = statusElement ? statusElement.closest('.cc-share-bar') : null;
  const successMessage = container?.dataset.copySuccess || 'Link copied';
  const fallbackMessage = container?.dataset.copyFallback || 'Press Ctrl+C to copy the link';

  const announce = (message) => {
    if (!statusElement) {
      return;
    }

    statusElement.textContent = message;
    globalThis.setTimeout(() => {
      statusElement.textContent = '';
    }, 1800);
  };

  const focusForManualCopy = (url) => {
    const input = document.createElement('input');
    input.type = 'text';
    input.value = url;
    document.body.appendChild(input);
    input.focus();
    input.select();

    globalThis.setTimeout(() => {
      input.remove();
    }, 2000);

    return false;
  };

  copyButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const shareUrl = button.dataset.shareUrl;

      if (!shareUrl) {
        return;
      }

      if (navigator?.clipboard && typeof navigator.clipboard.writeText === 'function') {
        navigator.clipboard
          .writeText(shareUrl)
          .then(() => {
            announce(successMessage);
          })
          .catch(() => {
            if (!focusForManualCopy(shareUrl)) {
              announce(fallbackMessage);
            }
          });

        return;
      }

      if (!focusForManualCopy(shareUrl)) {
        announce(fallbackMessage);
      }
    });
  });
})();
