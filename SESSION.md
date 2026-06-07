# Session Notes — Events Archive Region Drawer & Scroll

> **Date:** June 7, 2026  
> **Context:** Fixing the events archive page's region-based selection sidebar (collapsible vertical drawer menu) to properly toggle regions, navigate to sub-regions, and scroll to visible content.

---

## Architecture Overview

```
theme/templates/archive-events.html
  └── block: runpartner/events-archive
        ├── render.php          ← PHP: server-renders hero + sections + sidebar
        ├── view.js             ← JS: Interactivity API store (actions + callbacks)
        └── style.scss          ← CSS: sidebar, cards, pagination, carousel
```

- The block is registered in `advanced-multi-block` plugin
- Built with `@wordpress/scripts` using `--experimental-modules --blocks-manifest`
- Uses the Interactivity API (`@wordpress/interactivity`) via `viewScriptModule`
- Client-side navigation via `@wordpress/interactivity-router`

---

## Issue 1: Sidebar Region Click Doesn't Scroll to Content

### Problem
Clicking a region/state link in the sidebar navigated via the Interactivity Router but the page stayed at the top (hero section). The user had to manually scroll down to see the results.

### Root Cause
The `navigate` action in `view.js` had scroll logic that only worked for **pagination links** (inside `.event-archive-section`):

```javascript
// Only finds scroll target if click is inside .event-archive-section
const section = e.currentTarget.closest('.event-archive-section');
const scrollTarget = section?.querySelector('.event-archive-section-title')?.id;
```

Sidebar links are in `.event-archive-sidebar` — not inside `.event-archive-section`. So `section` was `null`, `scrollTarget` was `undefined`, and no scroll happened.

### Fix
Added a fallback: after navigation, if the click came from the sidebar, scroll to the **first visible `.event-archive-section-title`** in the DOM. PHP already skips empty sections (`continue` when no posts), so the first title is always the first section with content (Upcoming Events or Race Recaps).

```javascript
const isSidebar = e.currentTarget.closest('.event-archive-sidebar');
if (isSidebar) {
    const firstSection = document.querySelector('.event-archive-section-title');
    firstSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
```

**File:** `view.js` — `navigate` action (lines 35-39)

---

## Issue 2: Main Region Headings Should Toggle, Not Navigate

### Problem
Clicking a main region heading like "India" or "American Continent" triggered full Router navigation + scroll-to-content. But the user expected it to **expand/collapse** the sub-region list (states), not navigate away.

### Root Cause
The main headings were `<a>` tags with `data-wp-on--click="actions.navigate"`:

```php
<a href="..." data-wp-on--click="actions.navigate"
   class="event-archive-sidebar-item ...">
    <span>India</span>
    <span class="event-archive-sidebar-chevron">▼</span>
</a>
```

### Fix
Split headings into two types:

**Regions WITH children** (e.g., India → Maharashtra, Gujarat) → `<button>` with `toggleRegion` action:

```php
<button type="button"
    data-wp-on--click="actions.toggleRegion"
    data-region-slug="india"
    aria-expanded="true/false"
    class="event-archive-sidebar-item event-archive-sidebar-toggle ...">
    <span>India</span>
    <span class="event-archive-sidebar-chevron">▼</span>
</button>
```

**Regions WITHOUT children** → keep `<a>` with navigation (no sub-regions to choose from).

**Sub-links** ("All India", "Maharashtra") → remain `<a>` with navigation + scroll.

**File:** `render.php` — `rp_render_sidebar()` function (lines 314-333)

---

## Issue 3: Chevron Rotation Styling

### Problem
The chevron on toggle buttons only rotated when the parent had `.active` class. For the new toggle buttons, we needed rotation on `aria-expanded="true"` as well.

### Root Cause
The CSS selector only targeted `.active`:

```scss
.active & {
    transform: rotate(180deg);
}
```

### Fix
Added `aria-expanded` selector:

```scss
.active &,
.event-archive-sidebar-toggle[aria-expanded="true"] & {
    transform: rotate(180deg);
}
```

**File:** `style.scss` (lines 300-303)

---

## Issue 4: Stale Active State After Toggling

### Problem
1. Click "Maharashtra" → India gets `.active` (lime green + chevron up) — correct
2. Click "American Continent" → India **kept** `.active` (same lime green + chevron up), while American Continent also showed chevron up
3. Only selecting a sub-region in the new region would finally clear India's active state

### Root Cause
The `toggleRegion` action managed `aria-expanded` and `.expanded` class on the children container, but never touched the `.active` class on the heading itself. The `.active` class was **server-rendered** based on URL query params and never cleaned up by the toggle.

### Fix
In `toggleRegion`, sync `.active` alongside `aria-expanded`:

```javascript
// Close others:
otherBtn.setAttribute('aria-expanded', 'false');
otherBtn.classList.remove('active');
otherChildren.classList.remove('expanded');

// Toggle clicked:
button.setAttribute('aria-expanded', !currentlyExpanded);
button.classList.toggle('active', !currentlyExpanded);
childrenContainer.classList.toggle('expanded');
```

Now:
- Toggle one region → others lose `.active` (no lime green, chevron down)
- Toggle same region again → collapses, `.active` removed
- Navigate to a sub-region → server re-renders with correct `.active`

**File:** `view.js` — `toggleRegion` action (lines 42-74)

---

## Issue 5: Orange Button Background on Sidebar Items

### Problem
The toggle buttons showed an orange background and large rounded pill shape, inherited from theme.json's global button element styles.

### Root Cause
`theme.json` sets styles for ALL `<button>` elements:

```json
"button": {
    "color": {
        "background": "var(--wp--preset--color--orange)"
    },
    "border": {
        "radius": "999px"
    },
    "spacing": {
        "padding": { "top": "0.8rem", "left": "2rem", ... }
    }
}
```

Our `.event-archive-sidebar-item` class overrode `border-radius` and `color` but not `background`.

### Fix
Added `background: var(--wp--preset--color--accent-7)` to `.event-archive-sidebar-item`:

```scss
.event-archive-sidebar-item {
    background: var(--wp--preset--color--accent-7);
    ...
}
```

`.active` items still get their lime-green tinted background via the `.active` selector (higher specificity in cascade).

**File:** `style.scss` (line 280)

---

## Issue 6: First Click After Page Refresh Doesn't Scroll

### Problem
After a hard refresh, the **first** click on any sub-region didn't scroll to content. The second click on the same link worked. The pattern reset on every refresh.

### Root Cause #1 (Attempted)
The dynamic `import('@wordpress/interactivity-router')` was used inside the `navigate` action. On the first click, the Router module wasn't cached yet — the import triggered a network fetch. This may have caused the Router's `navigate` function to behave differently (returning `undefined` instead of a promise), causing the generator to continue before the DOM swap completed.

**Attempted fix:** Changed to eager import:
```javascript
import { actions as routerActions } from '@wordpress/interactivity-router';
```
❌ Didn't fix the issue.

### Root Cause #2 (Attempted)
Timing issue — the scroll code ran before the Interactivity API finished processing the Router's DOM swap and re-initializing directives.

**Attempted fixes:**
- `requestAnimationFrame` — deferred to before next paint
- `setTimeout(fn, 50)` — deferred to next event loop cycle
❌ Neither fixed the issue.

### Root Cause #3 (Solved)
The `scrollIntoView` was called **after** `yield routerActions.navigate(href)`, by which point the DOM mutation had already happened. But the Router's internal scroll restoration (which scrolls to top or maintains position) was overriding our scroll. On the first navigation, the Router's scroll management and our scroll were fighting.

### Fix
**Two-pronged approach:**

1. **MutationObserver** (set up BEFORE `navigate()`):
   ```javascript
   const observer = new MutationObserver(() => {
       const firstSection = region.querySelector('.event-archive-section-title');
       if (firstSection) {
           firstSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
           observer.disconnect();
       }
   });
   observer.observe(region, { childList: true, subtree: true });
   setTimeout(() => observer?.disconnect(), 10000);
   ```
   - Set up before `yield routerActions.navigate(href)`
   - Catches the DOM mutation **during** navigation
   - Fires in a microtask after the DOM swap
   - 10-second safety timeout

2. **Direct scroll fallback** (after `navigate()`):
   ```javascript
   if (isSidebar) {
       const firstSection = document.querySelector('.event-archive-section-title');
       if (firstSection) {
           firstSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
           observer?.disconnect();
       }
   }
   ```
   - Handles cases where the observer missed the mutation
   - Runs after the Router's promise resolves (for subsequent clicks where timing is reliable)

**File:** `view.js` — `navigate` action (full rewrite, lines 19-60)

---

## Final State of `view.js` (annotated)

```javascript
import { store, withSyncEvent, getElement } from '@wordpress/interactivity';
import { actions as routerActions } from '@wordpress/interactivity-router';

store('runpartner/events-archive', {
    actions: {
        // Handles pagination and sidebar navigation clicks
        navigate: withSyncEvent(function* (e) {
            e.preventDefault();
            const href = e.currentTarget.href;
            if (!href) return;

            const section = e.currentTarget.closest('.event-archive-section');
            const scrollTarget = section?.querySelector('.event-archive-section-title')?.id;
            const isSidebar = e.currentTarget.closest('.event-archive-sidebar');

            // SET UP OBSERVER BEFORE NAVIGATION (catches DOM mutation)
            let observer;
            if (isSidebar) {
                const region = document.querySelector(
                    '[data-wp-router-region="events-archive-region"]'
                );
                if (region) {
                    observer = new MutationObserver(() => {
                        const firstSection =
                            region.querySelector('.event-archive-section-title');
                        if (firstSection) {
                            firstSection.scrollIntoView({
                                behavior: 'smooth', block: 'start'
                            });
                            observer?.disconnect();
                        }
                    });
                    observer.observe(region, { childList: true, subtree: true });
                    setTimeout(() => observer?.disconnect(), 10000);
                }
            }

            // NAVIGATE
            yield routerActions.navigate(href);

            // PAGINATION: scroll to that section
            if (scrollTarget) {
                const el = document.getElementById(scrollTarget);
                el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }

            // SIDEBAR: direct scroll fallback
            if (isSidebar) {
                const firstSection =
                    document.querySelector('.event-archive-section-title');
                if (firstSection) {
                    firstSection.scrollIntoView({
                        behavior: 'smooth', block: 'start'
                    });
                    observer?.disconnect();
                }
            }
        }),

        // Accordion toggle for region headings with children
        toggleRegion() {
            const button = getElement().ref;
            const currentlyExpanded =
                button.getAttribute('aria-expanded') === 'true';
            const list = button.closest('.event-archive-sidebar-list');

            // Close all other regions
            list?.querySelectorAll('.event-archive-sidebar-toggle').forEach(
                (otherBtn) => {
                    if (otherBtn !== button &&
                        otherBtn.getAttribute('aria-expanded') !== 'false') {
                        otherBtn.setAttribute('aria-expanded', 'false');
                        otherBtn.classList.remove('active');
                        const otherChildren = otherBtn.nextElementSibling;
                        if (otherChildren?.classList.contains(
                            'event-archive-sidebar-children')) {
                            otherChildren.classList.remove('expanded');
                        }
                    }
                }
            );

            // Toggle clicked region
            button.setAttribute('aria-expanded', !currentlyExpanded);
            button.classList.toggle('active', !currentlyExpanded);
            const childrenContainer = button.nextElementSibling;
            if (childrenContainer?.classList.contains(
                'event-archive-sidebar-children')) {
                childrenContainer.classList.toggle('expanded');
            }
        },
        // ... carousel actions ...
    },
    // ... callbacks ...
});
```

---

## Key Lessons

| Lesson | Detail |
|--------|--------|
| **DOM timing** | When using client-side routing, `scrollIntoView` after `await navigate()` is unreliable on first call. DOM mutation may already be complete, and the Router's own scroll restoration may fight your scroll. |
| **MutationObserver** | Setting up a `MutationObserver` **before** navigation is the most reliable way to detect DOM swaps. The observer fires in a microtask when the Router replaces content. |
| **Generator + yield** | The Interactivity API's `withSyncEvent` processes yielded promises correctly, but the Router's `navigate` promise resolution timing can vary between first and subsequent calls. |
| **Dynamic import** | `import('@wordpress/interactivity-router')` on first click vs eager `import` — eager import didn't fix our issue, but it's worth trying first when debugging routing problems. |
| **Server vs client state** | When mixing server-rendered `.active` classes with client-side toggling, the toggle action must explicitly manage all visual state (`.active`, `aria-expanded`, `.expanded`) — don't rely on the server re-render. |
| **Theme.json bleed** | Global button element styles in `theme.json` apply to ALL `<button>` elements, even unexpected ones. Always check `theme.json` when adding new interactive elements. |
