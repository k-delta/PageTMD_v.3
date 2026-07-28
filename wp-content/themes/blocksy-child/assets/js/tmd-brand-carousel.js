document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-tmd-brand-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-brand-track]');
    const previous = carousel.querySelector('[data-brand-prev]');
    const next = carousel.querySelector('[data-brand-next]');
    const slides = Array.from(track?.children || []);
    const visible = 3;
    let index = 0;

    if (!track || slides.length <= visible) {
      previous?.setAttribute('hidden', '');
      next?.setAttribute('hidden', '');
      return;
    }

    const render = () => {
      const slideWidth = carousel.querySelector('.tmd-brand-carousel__viewport')?.clientWidth / visible || 0;
      track.style.transform = `translate3d(${-index * slideWidth}px, 0, 0)`;
      slides.forEach((slide, slideIndex) => {
        const shown = slideIndex >= index && slideIndex < index + visible;
        slide.setAttribute('aria-hidden', shown ? 'false' : 'true');
      });
    };

    previous?.addEventListener('click', () => {
      index = index === 0 ? Math.max(0, slides.length - visible) : Math.max(0, index - visible);
      render();
    });

    next?.addEventListener('click', () => {
      index = index >= slides.length - visible ? 0 : Math.min(slides.length - visible, index + visible);
      render();
    });

    window.addEventListener('resize', render, { passive: true });
    render();
  });
});
