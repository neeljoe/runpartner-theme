// Scroll-triggered animations via IntersectionObserver
function initScrollAnimations() {
	const prefersReducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	);

	if (prefersReducedMotion.matches) return;

	const animatedElements = document.querySelectorAll('.animate-on-scroll');
	if (animatedElements.length === 0) return;

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					observer.unobserve(entry.target);
				}
			});
		},
		{
			threshold: 0.05,
			rootMargin: '0px 0px -20px 0px',
		}
	);

	animatedElements.forEach((el) => observer.observe(el));
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initScrollAnimations);
} else {
	initScrollAnimations();
}