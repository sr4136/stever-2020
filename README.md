# SteveR WordPress Theme

A custom WordPress theme based on [Underscores](https://underscores.me/), with a Gulp-based Sass build pipeline.

---

## Installation

1. Install [Node.js](https://nodejs.org/en/)
2. Install the Gulp CLI globally: `npm install --global gulp-cli`
3. Install project dependencies: `npm install`

---

## npm Scripts

| Script | Command | Description |
|--------|---------|-------------|
| `build` | `gulp sass` | Compile all Sass files once |
| `watch` / `dev` | `gulp watch` | Watch for changes and recompile |

---

## Gulp Tasks

The default `gulp` task (same as `gulp sass`) runs all four compile tasks in series. The `gulp watch` task watches `sass/**/*.scss` and reruns all four on any change.

Each task compiles an expanded `.css` file and a minified `.min.css` file to the same destination.

| Task | Source | Destination |
|------|--------|-------------|
| `sassMain` | `sass/style.scss` | `style.css` / `style.min.css` (root) |
| `sassBlocks` | `sass/blocks.scss` | `css/blocks.css` / `css/blocks.min.css` |
| `sassAdmin` | `sass/admin.scss` | `css/admin.css` / `css/admin.min.css` |
| `sassAdminBlocks` | `sass/admin-blocks.scss` | `css/admin-blocks.css` / `css/admin-blocks.min.css` |

---

## Sass Architecture

Partials live in `sass/partials/`. The four entry-point files import them as follows:

**`sass/style.scss`** — Main front-end stylesheet
- `_variables`, `helpers`, `elements`, `header`, `footer`
- `error-404`, `search-results`, `wpadminbar`
- `maps`, `quotes`, `timeline-at-bu`, `post-specifics`
- `_breakpoints-debugging`

**`sass/blocks.scss`** — Block styles (front-end and back-end)
- `_variables`, `blocks-general`, `blocks-unique-global`
- `blocks-unique-tables`, `blocks-unique-home`

**`sass/admin.scss`** — General admin styles
- `_variables`, `_elements`, `wpadminbar`, `admin-general`

**`sass/admin-blocks.scss`** — Block editor admin styles
- `_variables`, `blocks-admin`
