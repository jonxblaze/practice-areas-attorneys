# Practice Areas & Attorneys

A clean-room WordPress code sample: a small plugin that registers **Practice Area** and **Attorney** custom post types — the same pattern behind custom client builds — with admin fields, front-end templates, and shortcodes.

## What this demonstrates

- **Custom post types + taxonomy** — `practice_area` (archived, public) and `attorney` (single), linked by a hierarchical `paa_practice` taxonomy, all registered on `init` with REST support for the block editor.
- **Secure admin fields** — meta boxes with nonce verification, capability checks, autosave/revision guards, strict sanitization (`sanitize_text_field`, `sanitize_email`), and escaped output everywhere (`esc_html`, `esc_attr`, `esc_url`).
- **Theme-friendly template loading** — `template_include` filter serves bundled `archive-practice-area.php` and `single-attorney.php` only when the active theme doesn't override them.
- **Shortcodes** — `[attorney_card id="123"]` renders a profile card anywhere; `[practice_area_grid columns="3"]` renders a responsive grid of practice areas.
- **Clean structure** — one class per concern under `includes/`, activation/deactivation hooks that flush rewrite rules, no globals leaked.

## Structure

```
practice-areas-attorneys/
├── practice-areas-attorneys.php   Plugin bootstrap, hooks, activation
├── includes/
│   ├── class-paa-post-types.php   CPT + taxonomy registration
│   ├── class-paa-meta-boxes.php   Admin fields, nonce + sanitization
│   ├── class-paa-shortcodes.php   [attorney_card], [practice_area_grid]
│   └── class-paa-templates.php    template_include fallback loader
├── templates/
│   ├── archive-practice-area.php
│   └── single-attorney.php
├── assets/css/paa-frontend.css
└── README.md
```

## Try it

1. Zip the folder, upload via Plugins → Add New → Upload.
2. Add a few Practice Areas (with taglines) and Attorneys (job title, phone, email, bar admissions, featured image, practice terms).
3. Visit `/practice-areas/` and an attorney's page; drop `[attorney_card id="X"]` into any post.
4. Deactivate cleanly — rewrite rules flush on the way out.

## Screenshots

_(Add 2–3 screenshots of the admin and front end here after installing on a demo site.)_

## License

GPL-2.0-or-later.
