# RunPartner Theme - Interactivity API Review Notes

## Scroll Responsive Sticky Header Implementation

**Date:** 2026-04-29
**Files Reviewed:** `parts/header.html`, `functions.php`, `resources/js/interactivity.js`, `style.css`, `public/js/interactivity.js`, `public/js/interactivity.asset.php`

---

## Current Implementation Overview

The implementation uses the Interactivity API at **theme-level** (not block-level) via:
- `wp_enqueue_script_module()` to load the interactivity script
- `wp_interactivity_state()` to pass server-side data to client store
- `data-wp-interactive`, `data-wp-init`, `data-wp-class--*` directives on the header block
- `render_block_core/group` filter not used here (directives in template markup instead)

**Store structure:**
```
runpartner
├── state
│   ├── scrollY, scrollDirection, isScrolled, headerHidden
├── actions
│   └── handleScroll() — computes direction, hides/shows header
└── callbacks
    └── initScroll() — adds passive scroll listener
```

---

## Suggestions for Improvement

### 1. Unused Server State Initialization

**Location:** `functions.php` lines 39-44

```php
wp_interactivity_state('runpartner', [
    'scrollY' => 0,
    'scrollDirection' => 'none',
    'isScrolled' => false,
    'headerHidden' => false,
]);
```

**Problem:** This server-side state is never used for server-side rendering. The `headerHidden` derived state is computed entirely client-side. Since no `data-wp-bind--*` directive needs pre-computed values for initial render, this adds unnecessary overhead.

**Recommendation:**
- Remove if no plans to pre-compute header state server-side
- Or keep and use `headerHidden` to render initial `is-hidden` class server-side to avoid hydration flicker

---

### 2. CSS Class vs Directive Naming Inconsistency

**Location:** `style.css` line 430 vs `parts/header.html` line 2

CSS uses `.is-hidden`:
```css
.runpartner-header.is-hidden {
    transform: translateY(-100%);
}
```

Directive maps state to class via:
```html
data-wp-class--is-hidden="state.headerHidden"
```

**Problem:** The mapping from `headerHidden` (camelCase state) to `is-hidden` (dash-case class) works but is indirect and could be confusing.

**Recommendation:** Choose one naming convention and align:
- Option A: Rename state to `isHidden` → class becomes `.is-hidden` (semantically linked)
- Option B: Rename class to `.header-hidden` → directive becomes `data-wp-class--header-hidden="state.headerHidden"`

---

### 3. Dead Code: `isScrolled` State

**Location:** `resources/js/interactivity.js` lines 11, 27

```javascript
state: {
    // ...
    isScrolled: false,  // Never used in any directive
}
```

**Problem:** `isScrolled` is computed in `handleScroll` but never referenced in any template directive. It's dead code that adds to reactive state overhead.

**Recommendation:** Remove `isScrolled` from both:
- Store state definition (JS)
- `wp_interactivity_state()` (PHP)

---

### 4. Unused State Properties: `scrollY` and `scrollDirection`

**Location:** `header.html` — neither is used in any directive

**Problem:** These state properties are tracked but not used anywhere:
- No progress bar or scroll indicator referencing `state.scrollY`
- No direction-based behavior using `state.scrollDirection`

**Recommendation:**
- Remove if no planned use for scroll position or direction
- Keep if planning future features (progress bar, back-to-top, parallax)

---

### 5. Missing Initial Scroll State Sync

**Location:** `resources/js/interactivity.js` callbacks.initScroll

```javascript
callbacks: {
    initScroll() {
        window.addEventListener('scroll', () => {
            actions.handleScroll();
        }, { passive: true });
        // Missing: initial state sync
    },
},
```

**Problem:** If a user lands mid-page (e.g., via anchor link), the header state won't reflect their actual scroll position until they scroll. This can cause incorrect initial header visibility.

**Recommendation:** Call `handleScroll()` once on init:
```javascript
callbacks: {
    initScroll() {
        window.addEventListener('scroll', () => {
            actions.handleScroll();
        }, { passive: true });

        // Sync initial scroll position
        actions.handleScroll();
    },
},
```

---

### 6. Hardcoded Scroll Threshold

**Location:** `resources/js/interactivity.js` line 5

```javascript
const SCROLL_THRESHOLD = 100;
```

**Problem:** Threshold is hardcoded. If you want to make it configurable via Customizer or theme settings, it's not currently exposed.

**Recommendation (if configurability is needed):**
```php
// functions.php
wp_interactivity_state('runpartner', [
    'scrollThreshold' => get_theme_mod('header_scroll_threshold', 100),
]);
```

```javascript
// interactivity.js
actions: {
    handleScroll() {
        // Use state.scrollThreshold instead of hardcoded constant
    }
}
```

---

### 7. Alternative: Use `data-wp-watch` for Class Binding

**Current approach:**
```html
data-wp-class--is-hidden="state.headerHidden"
```

**Alternative using watch callback:**
```html
data-wp-interactive="runpartner"
data-wp-watch="callbacks.updateHeaderVisibility"
```

```javascript
callbacks: {
    updateHeaderVisibility() {
        const header = document.querySelector('.runpartner-header');
        if (header) {
            header.classList.toggle('is-hidden', state.headerHidden);
        }
    },
}
```

**When to use watch:** When you need to coordinate multiple class changes or trigger side effects when visibility changes.

**Current approach is fine** if simple class toggling is all you need.

---

## Priority Summary

| Priority | Issue | Effort |
|----------|-------|--------|
| **High** | Remove dead code (`isScrolled`) | Low |
| **Medium** | Add initial `handleScroll()` call in `initScroll` | Low |
| **Low** | Remove unused `scrollY`/`scrollDirection` if no future use | Low |
| **Low** | Align CSS class naming with state property names | Medium |
| **Low** | Expose `scrollThreshold` via `wp_interactivity_state()` if needed | Medium |

---

## General Best Practices Observed

- Uses `{ passive: true }` for scroll listener (good for performance)
- Uses `will-change: transform` on animated element (good)
- CSS transitions on `transform` (GPU-accelerated, good)
- Passive state updates (no unnecessary re-renders)
- Store namespace uses theme prefix (`runpartner`) to avoid collisions

---

## Potential Future Enhancements

1. **Back-to-top button** — Could use `scrollY` state to show/hide
2. **Reading progress bar** — Could bind to scroll position
3. **Scroll velocity indicator** — Could use `scrollDirection` for dynamic effects
4. **Configurable threshold** — Customizer option for scroll sensitivity
5. **Reduced motion support** — Check `prefers-reduced-motion` and disable animations