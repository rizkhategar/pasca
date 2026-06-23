# Frontend Asset Centralization

Shared frontend CSS and JavaScript should live in:

- `resources/css/app.css`
- `resources/js/app.js`

The Vite entrypoint is loaded through `resources/views/component/typography.blade.php`, which is included from the shared footer component.

## Already centralized

The following reusable/frontend-shared behavior has been moved to the Vite assets:

- Global typography and font smoothing
- Shared CSS variables
- About point icon overrides
- Hero slider styles and JavaScript
- Home program card ordering and mobile responsive rules
- Homepage news card layout rules
- Homepage news hover line fix
- Student service card responsive/link behavior
- Footer quick link replacement behavior
- WhatsApp admin modal styling and JavaScript
- Header dropdown cleanup JavaScript

## Remaining inline assets to migrate carefully

Some large page-specific templates still contain inline `<style>` or `<script>` blocks. These should be migrated one page at a time to avoid breaking existing layouts:

- `resources/views/component/header.blade.php`
- `resources/views/component/footer.blade.php`
- `resources/views/home.blade.php`
- `resources/views/news/index.blade.php`
- `resources/views/news/show.blade.php`
- `resources/views/contact/index.blade.php`
- `resources/views/profile/about.blade.php`
- `resources/views/profile/academic.blade.php`
- `resources/views/profile/vision-mission.blade.php`
- `resources/views/profil/struktur-organisasi.blade.php`
- `resources/views/riset&pdm/listrisetdosen.blade.php`
- `resources/views/riset&pdm/detailriset.blade.php`

## Migration rule

For every future UI change:

1. Add CSS to `resources/css/app.css`.
2. Add JavaScript to `resources/js/app.js`.
3. Keep Blade files focused on HTML and Blade data only.
4. Use data attributes or JSON data containers when server-side data must be consumed by JavaScript.
5. Avoid new inline `<style>` or executable `<script>` blocks.
