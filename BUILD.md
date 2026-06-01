# Build Reference — RunPartner Theme + Plugin

> Current state after hero revamp (June 2026).

---

## Build Commands

### Plugin (`advanced-multi-block`)

| Command | Description |
|---------|-------------|
| `npm run build` | Production build: `wp-scripts build --experimental-modules --blocks-manifest` |
| `npm start` | Dev watch mode with same flags |

Generates `build/blocks/{slug}/` per block + `build/blocks-manifest.php`.

### Theme (`runpartner-theme`)

| Command | Description |
|---------|-------------|
| `npm run build` | Full build: JS (`build:js`) then CSS (`build:styles`) |
| `npm run build:js` | JS via `wp-scripts build`, output to `public/js/` |
| `npm run build:styles` | CSS via PostCSS (`postcss resources/styles/style.css --output public/style.css`), production with cssnano |

---

## Block Inventory

### Plugin — Advanced Multi Block

**Active blocks registered by manifest:** 11

| Block | Slug | Type | Notes |
|-------|------|------|-------|
| Event Hero | `runpartner/event-hero` | Dynamic | Title, subtitle, meta, buttons (front-page left column) |
| Event Subtitle | `runpartner/event-subtitle` | Dynamic | `_rp_event_subtitle` — kept in source, unused in templates |
| Event Year | `runpartner/event-year` | Dynamic | `_rp_event_year` |
| Event CTA | `runpartner/event-cta` | Dynamic | Website + registration buttons (single-events.html) |
| Event Content | `runpartner/event-content` | Dynamic + Interactivity + Router | Tab system |
| Events Archive | `runpartner/events-archive` | Dynamic + Interactivity + Router | Archive hero + grids |
| Coach Header | `runpartner/coach-header` | Dynamic + Interactivity (accordion) | |
| Coach Athletes | `runpartner/coach-athletes` | Dynamic | Reverse query |
| Athlete Header | `runpartner/athlete-header` | Dynamic | |
| Pace Calculator | `runpartner/pace-calculator` | Dynamic + Interactivity | |

**Excluded from registration:** `event-location`, `event-month`, `event-distances` — source files kept, excluded via `$excluded` array in `advanced-multi-block.php`.

**Deleted from source:** `event-hero-meta`, `event-website`, `event-registration` — source + build removed.

**Global scripts:**
- `build/editor-script.js` — enqueued on `enqueue_block_editor_assets`
- `build/frontend-script.js` — enqueued on `wp_enqueue_scripts` (footer)

### Plugin — Extended Multi Block

| Block | Slug | Type |
|-------|------|------|
| News Carousel | `runpartner/news-carousel` | Dynamic + Interactivity |

---

## Glass Morphism System

Shared glass values applied to both the event-hero block and the rounded nav bar:

```css
background: rgba(0, 0, 0, 0.25);
backdrop-filter: blur(16px);
-webkit-backdrop-filter: blur(16px);
border: 1px solid rgba(255, 255, 255, 0.1);
box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
border-radius: 20px;
color: var(--wp--preset--color--contrast);
```

### Location

| Component | File | Selector |
|-----------|------|----------|
| Event Hero block | `src/blocks/event-hero/style.scss` | `.wp-block-runpartner-event-hero` |
| Rounded nav | `resources/styles/navigation.css:142` | `.post-hero .alignwide.has-accent-7-background-color` |

### Mobile Behavior

When hamburger menu opens, nav glass is fully disabled to prevent it showing through the overlay:

```css
.wp-block-cover__inner-container:has(.wp-block-navigation__responsive-container.is-menu-open) > .alignwide:first-child {
    background: transparent !important;
    backdrop-filter: none !important;
    border-color: transparent !important;
    box-shadow: none !important;
}
```

Mobile overlay close button has its own separate glass:
```css
.wp-block-navigation__responsive-close {
    background: color-mix(in srgb, var(--wp--preset--color--base) 70%, transparent);
    backdrop-filter: blur(20px);
    border-radius: 12px;
    border: 1px solid color-mix(in srgb, var(--wp--preset--color--contrast) 10%, transparent);
}
```

---

## Front Page Hero Structure

`templates/front-page.html` — featured event query loop:

```
group (constrained)
  query (events, perPage:1, featured_front_page)
    post-template
      cover (useFeaturedImage, dimRatio:30, post-hero)
        span.has-background-dim-30  ← 30% dark overlay for text visibility
        inner-container
          template-part (rounded)    ← glass nav bar
          group (bottom spacing)
            columns (alignwide)
              column (66.66%)
                event-hero            ← glass card with title, subtitle, meta, buttons
              column (33.33%)
                news-carousel
```

**Key changes from original:**
- `dimRatio: 0` → `dimRatio: 30` (added dark overlay for text readability)
- Removed `isDark: false` / `is-light` class from cover
- Replaced spacer + accent-7 group + post-title + event-subtitle + event-hero-meta + post-link + buttons with single `runpartner/event-hero` block
- `core/post-link` no longer used (was broken)

---

## Template → Nav Mapping

| Template | Cover | Nav | dimRatio |
|----------|-------|-----|----------|
| `front-page.html` | featured image, post-hero | rounded (glass) | 30 |
| `single.html` | featured image, post-hero is-light | rounded (glass) | 0 |
| `single-events.html` | featured image, post-hero is-light | rounded (glass) | 0 |
| `single-athlete.html` | featured image, post-hero is-light athlete-hero | rounded (glass) | 30 |
| `single-coach.html` | featured image, post-hero is-light coach-hero | rounded (glass) | 30 |
| `archive.html` | solid `#04100a`, post-hero is-light | rounded (glass) | 100 |
| `home.html` | solid `#04100a`, post-hero is-light | rounded (glass) | 100 |
| `index.html` | none | header (regular) | — |
| `page.html` | none | header (regular) | — |

---

## Theme CSS Architecture

10 modules compiled into `public/style.css`:

| File | Content |
|------|---------|
| `variables.css` | `--rp-transition-{fast,normal,slow}` |
| `animations.css` | Keyframes |
| `animation-classes.css` | `.fade-up`, `.animate-on-scroll`, `.skeleton`, etc. |
| `buttons.css` | Default (orange) + Outline (lime-green), wipe mechanism |
| `utilities.css` | `.hover-lift`, `.text-gradient`, selection |
| `blocks.css` | `.post-hero::after` gradient, image credits, category pills |
| `layout.css` | Coach/athlete heros, excerpts, news cards |
| `navigation.css` | Site title glow, hamburger/close buttons, pill links, mobile overlay, nav glass |
| `responsive.css` | `prefers-reduced-motion`, mobile overrides |
| `style.css` | Entry point — `@import` all above |

---

## Key File Locations

### Block source files
- `wp-content/plugins/advanced-multi-block/src/blocks/event-hero/`
- `wp-content/plugins/advanced-multi-block/src/blocks/event-cta/`
- `wp-content/plugins/advanced-multi-block/src/blocks/event-content/`
- `wp-content/plugins/advanced-multi-block/src/blocks/events-archive/`

### Theme template files
- `wp-content/themes/runpartner-theme/templates/front-page.html`
- `wp-content/themes/runpartner-theme/patterns/rounded.php` — nav pattern
- `wp-content/themes/runpartner-theme/parts/rounded.html` — references pattern
- `wp-content/themes/runpartner-theme/resources/styles/navigation.css` — nav + glass CSS

### Registration
- `wp-content/plugins/advanced-multi-block/advanced-multi-block.php` — block registration + excluded list
