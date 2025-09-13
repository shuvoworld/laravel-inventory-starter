# UI and Layout

Module pages extend a common Bootstrap 5 layout at `resources/views/layouts/module.blade.php`. The main application shell uses Tailwind for the dashboard and sidebar, and Bootstrap is included for module pages to keep CRUD UIs simple and familiar.

- Bootstrap CSS/JS are loaded via CDN in the main app layout.
- Font Awesome provides icons for buttons and actions.
- Select2 assets are preloaded globally; any `<select>` with the `select2` class is auto-initialized with the Bootstrap 5 theme.

## Custom Styles
See `resources/css/app.css` for:
- Sidebar color tokens and styles
- Minimalist DataTables styling
- Dark mode adjustments
- Transitions and utilities

## Icons
Font Awesome 6 (Free) is included via CDN. Use e.g.:

```html
<i class="fas fa-save"></i>
<i class="fas fa-pen"></i>
<i class="fas fa-trash"></i>
```

## Layout Components
- `resources/views/components/layouts/app.blade.php` — main app shell
- `resources/views/layouts/module.blade.php` — simple container for module pages
- `resources/views/components/layouts/app/header.blade.php` — header with user menu
- `resources/views/components/layouts/app/sidebar.blade.php` — collapsible sidebar with access control group
