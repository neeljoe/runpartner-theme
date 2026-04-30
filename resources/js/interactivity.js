import { store } from '@wordpress/interactivity';

let lastScrollY = 0;
let scrollDirection = 'none';
const SCROLL_THRESHOLD = 100;

const { state, actions, callbacks } = store('runpartner', {
	state: {
		scrollY: 0,
		scrollDirection: 'none',
		headerHidden: false,
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
			state.scrollY = currentScrollY;
			state.scrollDirection = scrollDirection;

			// Hide header on scroll down past threshold
			if (scrollDirection === 'down' && currentScrollY > SCROLL_THRESHOLD) {
				state.headerHidden = true;
			}
			// Show header on scroll up
			else if (scrollDirection === 'up') {
				state.headerHidden = false;
			}
		},
	},
	callbacks: {
		initScroll() {
			window.addEventListener('scroll', () => {
				actions.handleScroll();
			}, { passive: true });

			// Sync initial scroll position on page load
			actions.handleScroll();
		},
	},
});