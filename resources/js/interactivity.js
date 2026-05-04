import { store } from '@wordpress/interactivity';

let lastScrollY = 0;
let scrollDirection = 'none';
const SCROLL_THRESHOLD = 100;

const { state, actions, callbacks } = store('runpartner', {
	state: {
		isHidden: false,
	},
	actions: {
		handleScroll() {
			const currentScrollY = window.scrollY;

			if (currentScrollY > lastScrollY) {
				scrollDirection = 'down';
			} else if (currentScrollY < lastScrollY) {
				scrollDirection = 'up';
			}

			lastScrollY = currentScrollY;

			if (scrollDirection === 'down' && currentScrollY > SCROLL_THRESHOLD) {
				state.isHidden = true;
			} else if (scrollDirection === 'up') {
				state.isHidden = false;
			}
		},
	},
	callbacks: {
		initScroll() {
			window.removeEventListener('scroll', actions.handleScroll);
			window.addEventListener('scroll', actions.handleScroll, { passive: true });

			actions.handleScroll();
		},
	},
});

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
			threshold: 0.15,
			rootMargin: '0px 0px -50px 0px',
		}
	);

	animatedElements.forEach((el) => observer.observe(el));
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initScrollAnimations);
} else {
	initScrollAnimations();
}