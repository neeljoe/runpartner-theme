# RunPartner Build Reference

> Auto-generated reference documenting the current state of the project.
> Generated: May 2026

---

## Architecture

```
mu-plugin (data/API)  →  plugin (presentation blocks)  →  theme (layout/templates)
```

**Data flow:**
1. **mu-plugin** defines the data model (CPT, taxonomy, meta, REST API)
2. **Plugin** defines presentation (dynamic blocks render meta via `render.php`)
3. **Theme** defines layout (block templates compose blocks into page structure)

---

## 1. Events CPT

**File:** `mu-plugins/runpartner-events/includes/class-events-cpt.php`

| Property | Value |
|----------|-------|
| Post type | `events` |
| REST base | `wp/v2/events` |
| REST composite field | `event_data` (read + write, bundles all meta) |
| Supports | `title`, `editor`, `thumbnail`, `excerpt` |
| Menu icon | `dashicons-calendar` |
| Rewrite | `/events/%postname%` (with_front: false) |

### Taxonomies

| Taxonomy | Type | REST base | Purpose |
|----------|------|-----------|---------|
| `event_type` | Hierarchical | `event_type` | General event categories |
| `event_region` | Hierarchical | `event_regions` | Geographic region (auto-seeded with countries/states) |

### Meta Fields

| Key | Meta Key | Type | Notes |
|-----|----------|------|-------|
| `subtitle` | `_rp_event_subtitle` | string | |
| `location` | `_rp_event_location` | string | Display value on cards |
| `country` | `_rp_event_country` | string | Display value on cards |
| `month` | `_rp_event_month` | string | Deprecated — kept for compat |
| `website` | `_rp_event_website` | string (uri) | |
| `registration` | `_rp_event_registration` | string (uri) | |
| `year` | `_rp_event_year` | integer | First edition year |
| `distances` | `_rp_event_distances` | array | Standard + custom distances |
| `date` | `_rp_event_date` | string (date) | Start date — drives archive queries |
| `date_end` | `_rp_event_date_end` | string (date) | End date — optional; drives archive upcoming/recaps split for multi-day events |
| `records` | `_rp_event_records` | array | `[{gender, event_name, time, holder, nationality, year}]` |
| `categories` | `_rp_event_categories` | array | Legacy — kept for compat, no longer used in admin |
| `history` | `_rp_event_history` | string | History tab content |
| `course_overview` | `_rp_event_course_overview` | string | Course tab content |
| `editions` | `_rp_event_editions` | array | `[{year, report}]` — Reports tab |
| `featured` | `_rp_event_featured` | boolean | Archive hero selection |
| `famous_athletes` | `_rp_event_famous_athletes` | array | `[{athlete_id (int), performance, year}]` |

### Standard Distances (`ALLOWED_DISTANCES`)

Fun Run, 5K, 10K, Half Marathon, 20-miler, Marathon, 50K, 100K, Ultra, 100-miler

Custom distances can be added via textarea in admin (one per line).

### Course Records (`_rp_event_records`)

**Schema (each entry):**
```php
[
    'gender'      => 'men' | 'women',     // radio toggle
    'event_name'  => 'Kharduungla Marathon', // free text
    'time'        => '05:41:36',
    'holder'      => 'Jigment Namgial',
    'nationality' => 'Indian',
    'year'        => '2024',
]
```

**Backward compat:** Old records with `category`/`distance` keys are auto-mapped to `gender`/`event_name` on read.

### Archive Upcoming/Recaps Split

**File:** `plugins/advanced-multi-block/src/blocks/events-archive/render.php`

The archive page splits events into **Upcoming** and **Race Recaps**. The query uses end_date when available so multi-day events don't move to recaps mid-event:

```php
Upcoming meta_query (OR):
  _rp_event_date_end >= today
  OR (no end_date AND _rp_event_date >= today)

Recaps meta_query (OR):
  _rp_event_date_end < today
  OR (no end_date AND _rp_event_date < today)
```

**Behavior by scenario:**

| Scenario | Start | End | On Sep 11 |
|----------|-------|-----|-----------|
| Single-day Sep 10 | Sep 10 | — | Recap |
| Multi-day Sep 10-13 | Sep 10 | Sep 13 | Upcoming |
| Multi-day Sep 14-16 | Sep 14 | Sep 16 | Upcoming |
| Multi-day ended Sep 8-9 | Sep 8 | Sep 9 | Recap |

**Ordering** always uses `_rp_event_date` (start date) — ASC for upcoming, DESC for recaps.

### Custom REST Query Params

| Param | Type | Description |
|-------|------|-------------|
| `location` | string | LIKE search on location |
| `country` | string | Exact match |
| `distance` | string | LIKE search on distances array |
| `year` | integer | Exact match |
| `month` | string | Exact match |
| `event_date` | string (date) | Exact match on start date |

### Admin Meta Box Order

```
subtitle → location → country → month → year → website → registration
→ Distances (checkboxes + custom textarea)
→ Start Date / End Date (side-by-side)
→ Course Records (flat repeater: gender toggle + event_name + time + holder + nationality + year + remove)
→ Event History
→ Course Overview
→ Past Editions (repeater: year + report)
→ Famous Athletes (repeater: athlete select + performance + year)
→ Featured checkbox
```

#### Date Range Display Formatting

| Scenario | Display |
|----------|---------|
| Single day | `September 10, 2025` |
| Same month | `September 10–13, 2025` |
| Cross-month | `September 30 – October 2, 2025` |
| Cross-year | `December 30, 2025 – January 2, 2026` |

---

## 2. Coaches CPT

**File:** `mu-plugins/runpartner-coaches/includes/class-coaches-cpt.php`

| Property | Value |
|----------|-------|
| Post type | `coach` |
| REST base | `wp/v2/coaches` |
| REST composite field | `coach_data` |
| Supports | `title`, `editor`, `thumbnail`, `excerpt` |

### Meta Fields

| Key | Meta Key | Type |
|-----|----------|------|
| `subtitle` | `_rp_coach_subtitle` | string |
| `nationality` | `_rp_coach_nationality` | string |
| `birth_year` | `_rp_coach_birth_year` | integer |
| `death_year` | `_rp_coach_death_year` | integer |
| `era_start` | `_rp_coach_era_start` | integer |
| `era_end` | `_rp_coach_era_end` | integer |
| `approach` | `_rp_coach_approach` | string |
| `notable_athletes` | `_rp_coach_notable_athletes` | string |
| `contributions` | `_rp_coach_contributions` | string |

### Custom REST Query Params

`nationality` (LIKE), `era_start`, `era_end`, `year`

---

## 3. Athletes CPT

**File:** `mu-plugins/runpartner-athletes/includes/class-athletes-cpt.php`

| Property | Value |
|----------|-------|
| Post type | `athlete` |
| REST base | `wp/v2/athletes` |
| REST composite field | `athlete_data` |
| Supports | `title`, `editor`, `thumbnail`, `excerpt` |

### Taxonomy: `discipline` (hierarchical, REST base `disciplines`)

### Meta Fields

| Key | Meta Key | Type | Notes |
|-----|----------|------|-------|
| `subtitle` | `_rp_athlete_subtitle` | string | |
| `nationality` | `_rp_athlete_nationality` | string | |
| `birth_year` | `_rp_athlete_birth_year` | integer | |
| `death_year` | `_rp_athlete_death_year` | integer | |
| `achievements` | `_rp_athlete_achievements` | string | |
| `coach` | `_rp_athlete_coach` | integer | Post ID of coach |

### Coach Relationship

Stored as `_rp_athlete_coach` (integer, coach post ID). Reverse lookup via `WP_Query` with `meta_key = _rp_athlete_coach` in the `coach-athletes` block.

---

## 4. Plugin Blocks

**File:** `plugins/advanced-multi-block/advanced-multi-block.php`

### Active Event Blocks (8)

| Block | Slug | Type | Meta Key(s) |
|-------|------|------|-------------|
| Event Subtitle | `runpartner/event-subtitle` | Dynamic | `_rp_event_subtitle` |
| Event Year | `runpartner/event-year` | Dynamic | `_rp_event_year` |
| Event Website | `runpartner/event-website` | Dynamic (button) | `_rp_event_website` |
| Event Registration | `runpartner/event-registration` | Dynamic (button) | `_rp_event_registration` |
| Event Content | `runpartner/event-content` | Dynamic + Interactivity API + Router | All event meta |
| Events Archive | `runpartner/events-archive` | Dynamic + Interactivity API + Router | `_rp_event_*` meta, `event_region` taxonomy — uses end_date-aware upcoming/recaps split |

### Other Active Blocks (4)

| Block | Slug | Type |
|-------|------|------|
| Coach Header | `runpartner/coach-header` | Dynamic + Interactivity API |
| Coach Athletes | `runpartner/coach-athletes` | Dynamic |
| Athlete Header | `runpartner/athlete-header` | Dynamic |
| Pace Calculator | `runpartner/pace-calculator` | Dynamic + Interactivity API |

### Deprecated (Excluded from Registration)

`event-location`, `event-month`, `event-distances`

### Event Content Block — Tab System

Tabs: `Details`, `Records`, `Course`, `History`, `Reports`, `Famous Athletes`

**Records tab** sub-navigation by gender (`Men` / `Women`), derived from actual record data. Table columns: Event, Time, Holder, Nationality, Year. Query param: `?gender=men|women`.

Uses `@wordpress/interactivity-router` for client-side navigation (`data-wp-on--click="actions.navigate"`).

---

## 5. Theme

**File:** `themes/runpartner-theme/`

**Type:** WordPress FSE Block Theme (theme.json v3)
**CSS:** PostCSS (10 modules → `public/style.css`)
**JS:** `public/js/interactivity.js` (IntersectionObserver scroll animations)

### Event Templates

| Template | Sections |
|----------|----------|
| `single-events.html` | Hero Cover → Event Details Card → **event-content block** → CTA Card → Footer |
| `archive-events.html` | **events-archive block** (hero + upcoming/recaps + region filter) |

### CSS Classes (event-related)

`.event-content-*` — tab system, records table, intro card, countdown
`.event-archive-*` — hero, cards, pagination, sidebar region filter

---

## 6. Key Conventions

| Convention | Rule |
|------------|------|
| Textdomain | `runpartner` for all event/coach/athlete blocks + mu-plugins |
| Meta prefix | `_rp_event_`, `_rp_coach_`, `_rp_athlete_` |
| Block prefix | `runpartner/event-*`, `runpartner/coach-*`, `runpartner/athlete-*` |
| Build output | `build/` for plugins, `public/` for theme |
| Interactivity | Interactivity API first (`data-wp-*`), vanilla JS fallback |
| Meta prefix underscore | All private meta keys start with `_` (WordPress convention) |

---

## 7. Build Commands

### Plugin (advanced-multi-block)

```bash
cd wp-content/plugins/advanced-multi-block
npm run build    # wp-scripts build --experimental-modules --blocks-manifest
npm start        # wp-scripts start --experimental-modules --blocks-manifest
```

### Theme

```bash
cd wp-content/themes/runpartner-theme
npm run build          # Full production build (JS + CSS)
npm run build:js       # JS build via wp-scripts
npm run build:styles   # CSS build via PostCSS
npm run start          # Dev mode (webpack watch)
npm run watch:styles   # Watch + rebuild CSS
```

---

## 8. REST API Quick Reference

### Events

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp/v2/events` | GET | List events (supports custom params: location, country, distance, year, month, event_date) |
| `/wp/v2/events` | POST | Create event |
| `/wp/v2/events/{id}` | GET | Single event (includes `event_data` composite field) |
| `/wp/v2/events/{id}` | PATCH | Update event |
| `/wp/v2/events/{id}` | DELETE | Delete event |
| `/wp/v2/event_type` | GET/POST | Event type taxonomy |
| `/wp/v2/event_regions` | GET/POST | Event region taxonomy |

### Coaches

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp/v2/coaches` | GET | List coaches |
| `/wp/v2/coaches` | POST | Create coach |
| `/wp/v2/coaches/{id}` | GET/PATCH/DELETE | Single coach (includes `coach_data`) |

### Athletes

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp/v2/athletes` | GET | List athletes (supports `disciplines` taxonomy filter) |
| `/wp/v2/athletes` | POST | Create athlete |
| `/wp/v2/athletes/{id}` | GET/PATCH/DELETE | Single athlete (includes `athlete_data`) |
| `/wp/v2/discipline` | GET/POST | Discipline taxonomy |
