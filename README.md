# Practice Areas & Attorneys

A clean-room WordPress code sample: a small plugin that registers **Practice Area** and **Attorney** custom post types — the same pattern behind custom client builds — with admin fields, front-end templates, and shortcodes.

## What this demonstrates

- **Custom post types + shared taxonomy** — `practice_area` (archived, public) and `attorney` (single), linked by a hierarchical `paa_practice` taxonomy registered on both, all on `init` with REST support for the block editor.
- **Secure admin fields** — meta boxes with nonce verification, capability checks, autosave/revision guards, strict sanitization (`sanitize_text_field`, `sanitize_email`), and escaped output everywhere (`esc_html`, `esc_attr`, `esc_url`).
- **Theme-friendly template loading** — `template_include` filter serves bundled `archive-practice-area.php`, `single-attorney.php`, and `single-practice-area.php` only when the active theme doesn't override them. The single practice page lists the attorneys assigned to that practice.
- **Shortcodes** — `[attorney_card id="123"]` renders a profile card anywhere; `[practice_area_grid columns="3"]` renders a responsive grid of practice areas; `[attorney_directory]` renders a filterable, searchable attorney directory (`?paa_practice=slug`, `?paa_search=name`); `[consultation_cta url="/contact/" phone="(713) 555-0100"]` renders a consultation button with click-to-call; `[testimonials]` renders star-rated client reviews with Review schema; `[firm_contact_card]` renders the firm card from the settings page; `[case_results]` renders case outcomes with the prior-results disclaimer; `[faqs]` renders practice FAQs.
- **Case results + FAQs** — two more content types on the shared practice taxonomy, so a practice page automatically lists its attorneys, its case results, and its FAQs, and carries FAQPage schema for those FAQs.
- **Consultation request form** — `[consultation_form]` collects name, email, phone, practice area (dropdown from the taxonomy), other parties for a conflict check, and the matter, behind a required no-attorney-client-relationship disclaimer checkbox and a honeypot. Submissions are saved as private posts (never public files) and emailed to the firm's inbox, and are registered with WordPress's privacy exporter and eraser.
- **Search-friendly output** — Schema.org `Attorney` JSON-LD on attorney profile pages, `LegalService` JSON-LD on the front page (the type schema.org recommends for firms), `Review` JSON-LD with testimonials, and `FAQPage` JSON-LD on practice pages, all built only from fields the plugin stores.
- **Real ordering** — both post types support `page-attributes`, so the grid and directory honor the Order field instead of falling back to title sort.
- **Clean structure** — one class per concern under `includes/`, activation/deactivation hooks that flush rewrite rules, no globals leaked.

## Structure

```
practice-areas-attorneys/
├── practice-areas-attorneys.php   Plugin bootstrap, hooks, activation
├── includes/
│   ├── class-paa-post-types.php   CPT + taxonomy registration
│   ├── class-paa-meta-boxes.php   Admin fields, nonce + sanitization
│   ├── class-paa-shortcodes.php   [attorney_card], [practice_area_grid], [attorney_directory], [consultation_cta], [testimonials], [firm_contact_card], [case_results], [faqs]
│   ├── class-paa-templates.php    template_include fallback loader + Attorney/LegalService/FAQPage schema
│   ├── class-paa-settings.php     Firm details settings page
│   └── class-paa-consultation-form.php  [consultation_form] + privacy tools
├── templates/
│   ├── archive-practice-area.php
│   ├── single-attorney.php
│   ├── single-practice-area.php
│   └── single-case-result.php
├── assets/css/paa-frontend.css
└── README.md
```

## Try it

1. Zip the folder, upload via Plugins → Add New → Upload.
2. Add a few Practice Areas (with taglines) and Attorneys (job title, phone, email, bar admissions, featured image, practice terms). Assign the same practice terms to a practice area and its attorneys, and set the Order field to control listing order.
3. Under Settings → Practice Areas & Attorneys, fill in the firm details and the consultation inbox.
4. Visit `/practice-areas/`, a practice area page (attorneys, case results, and FAQs listed at the bottom), and an attorney's page; drop `[attorney_directory]`, `[testimonials]`, `[firm_contact_card]`, `[case_results]`, `[faqs]`, and `[consultation_form]` into pages. Consultation requests land as private posts under Attorneys → Consultation Requests.
5. Deactivate cleanly — rewrite rules flush on the way out.

## Screenshots

_(Add 2–3 screenshots of the admin and front end here after installing on a demo site.)_

## Changelog

### 1.3.0

- New Case Result post type (outcome, year, practice terms) with `[case_results]` shortcode, single template, and the prior-results disclaimer everywhere outcomes appear. Practice pages list their case results automatically.
- New FAQ post type (question, answer, practice terms) with `[faqs]` shortcode, automatic FAQ section on practice pages, and FAQPage schema.
- Attorney profiles gain education, experience, and languages fields, shown on profile pages.
- Attorney directory gains a name search box; the shared practice taxonomy now shows as an admin column on every content list.

### 1.2.0

- New `[consultation_form]` shortcode: practice-area dropdown, other-parties conflict-check field, required disclaimer checkbox, honeypot spam filter, private saved entries, firm email, privacy exporter/eraser.
- New Testimonial post type with star ratings and `[testimonials]` shortcode with Review schema.
- New firm settings page; `LegalService` schema on the front page and `[firm_contact_card]` shortcode.

### 1.1.0

- Practice taxonomy is now shared by practice areas and attorneys, so a practice page can list its attorneys.
- New `single-practice-area.php` fallback template with an attorneys-in-this-practice section.
- New `[attorney_directory]` shortcode with a practice filter.
- New `[consultation_cta]` shortcode for a consultation button and click-to-call link.
- Attorney JSON-LD schema on profile pages.
- Both post types support ordering via the Order field.

## License

GPL-2.0-or-later.
