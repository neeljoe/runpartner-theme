
## Interactivity API (Primary Frontend Interactivity Tool)

### Core Principle

WordPress 6.9+ ships with the Interactivity API as the standard for frontend interactivity. **Use this first** — it integrates with the block editor, handles hydration correctly, and manages state reactively without writing custom JavaScript.

### Required Setup

**1. Enqueue the view script module** (in `functions.php` or block metadata):

```php
function runpartner_enqueue_interactivity() {
    wp_enqueue_script_module(
        'runpartner-view-script',
        get_template_directory_uri() . '/view-script.js',
        array( '@wordpress/interactivity' ),
        '1.0.0',
        array( 'in_footer' => true )
    );
}
add_action( 'wp_enqueue_scripts', 'runpartner_enqueue_interactivity' );
```

**2. Import the store and directives** in your view script:

```js
import { store, navigate } from '@wordpress/interactivity';

// Register store with state, actions, and callbacks
store( 'runpartner', {
    state: {
        isMenuOpen: false,
        activeFilter: 'all',
    },
    actions: {
        toggleMenu: () => {
            // Use toggle pattern for boolean state
            state.isMenuOpen = !state.isMenuOpen;
        },
        setFilter: ( filter ) => {
            state.activeFilter = filter;
        },
    },
    callbacks: {
        logIsVisible: () => {
            // Called when element visibility changes via data-wp-visibility
        }
    }
} );
```

### data-wp-* Directives Reference

| Directive | Purpose |
|-----------|---------|
| `data-wp-interactive` | Marks root element and declares store namespace |
| `data-wp-context` | Local state (per-element, not global) |
| `data-wp-on--*` | Event binding (click, keydown, etc.) |
| `data-wp-bind--*` | Bind attribute to state |
| `data-wp-class--*` | Toggle class based on boolean state |
| `data-wp-visibility` | Show/hide based on state |
| `data-wp-text` | Set text content from state |
| `data-wp-html` | Set HTML content from state |

### Common Patterns

**Toggle / Accordion:**
```html
<!-- wp:group {"className":"accordion-item","data-wp-interactive":"runpartner","data-wp-context":"{\"isOpen\":false}"} -->
<div class="wp-block-group">
    <button data-wp-on--click="actions.toggle" aria-expanded="false">
        <span data-wp-text="state.isOpen ? 'Close' : 'Open'">Open</span>
    </button>
    <div data-wp-visibility="state.isOpen" hidden>
        <!-- content -->
    </div>
</div>
<!-- /wp:group -->
```

**Modal Dialog:**
```html
<!-- wp:group {"data-wp-interactive":"runpartner","data-wp-context":"{\"isModalOpen\":false}"} -->
<div>
    <button data-wp-on--click="actions.openModal">Open Modal</button>
    <dialog data-wp-class--is-open="state.isModalOpen" data-wp-on--click="actions.closeModal">
        <button data-wp-on--click="actions.closeModal">Close</button>
        <p>Modal content</p>
    </dialog>
</div>
<!-- /wp:group -->
```

**Navigation with Active State:**
```html
<!-- wp:navigation-link {"label":"Home","url":"/","data-wp-class--is-active":"state.currentPath === '/'" / -->
```

### store() Best Practices

- **State is reactive** — mutating `state.property` automatically updates bound elements
- **Use actions to modify state** — never mutate state directly in templates
- **Context is per-element** — `data-wp-context` creates isolated state per block instance
- **Global state lives in the store** — shared state (menu open, user preferences) goes in `store()`
- **Avoid deep nesting** — flat state structure performs better

### viewScriptModule vs viewScript

- **`viewScriptModule`**: Use this for Interactivity API (ES modules, modern browsers)
- **`viewScript`**: Legacy non-module scripts — avoid for new development

### When CSS Is Still Required vs Interactivity

| Need | Approach |
|------|----------|
| Show/hide on click | Interactivity API (`data-wp-visibility`) |
| Class toggle on click | Interactivity API (`data-wp-class--*`) |
| Animated transitions on visibility | Interactivity API for state + CSS for transition |
| Scroll-triggered reveals | CSS animations + IntersectionObserver (inline script in footer) |
| Hover states | CSS only |
| Keyboard navigation | Interactivity API handles via `data-wp-on--*` |

### Interactivity API Script Enqueuing

```php
function runpartner_enqueue_interactivity() {
    // Only load on front end, not block editor
    if ( ! is_admin() ) {
        wp_enqueue_script_module(
            'runpartner-view-script',
            get_template_directory_uri() . '/view-script.js',
            array( '@wordpress/interactivity' ),
            '1.0.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'runpartner_enqueue_interactivity' );
```



### Scroll Animation Observer

```php
function runpartner_scroll_animations() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var els = document.querySelectorAll('.animate-on-scroll');
        if (!els.length) return;
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        els.forEach(function(el) { observer.observe(el); });
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'runpartner_scroll_animations' );
```





