<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <script>
        window.setAppearance = function(appearance) {
            let setDark = () => document.documentElement.classList.add('dark')
            let setLight = () => document.documentElement.classList.remove('dark')
            let setButtons = (appearance) => {
                document.querySelectorAll('button[onclick^="setAppearance"]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(appearance === button.value))
                })
            }
            if (appearance === 'system') {
                let media = window.matchMedia('(prefers-color-scheme: dark)')
                window.localStorage.removeItem('appearance')
                media.matches ? setDark() : setLight()
            } else if (appearance === 'dark') {
                window.localStorage.setItem('appearance', 'dark')
                setDark()
            } else if (appearance === 'light') {
                window.localStorage.setItem('appearance', 'light')
                setLight()
            }
            if (document.readyState === 'complete') {
                setButtons(appearance)
            } else {
                document.addEventListener("DOMContentLoaded", () => setButtons(appearance))
            }
        }
        window.setAppearance(window.localStorage.getItem('appearance') || 'system')
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Bootstrap 5 CSS for module pages -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Icons (Font Awesome) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <!-- Select2 CSS (core and Bootstrap 5 theme) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <!-- DataTables CSS (global) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

    @stack('styles')

    <style>
        /* Sidebar styling normalization */
        aside .sidebar-transition {
            background: white !important;
            border-right: 1px solid #e5e7eb !important;
        }

        /* Sidebar navigation links */
        aside nav .nav-link {
            font-size: 0.9rem;
            line-height: 1.25rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.375rem;
            color: #4b5563;
            transition: all 0.15s ease-in-out;
            margin-bottom: 0.125rem;
        }

        aside nav .nav-link:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        aside nav .nav-link.active {
            background-color: #667eea;
            color: white;
        }

        aside nav .nav-link.active:hover {
            background-color: #5568d3;
        }

        aside nav .nav-link i {
            width: 1.25rem;
            text-align: center;
        }

        /* Section headers */
        aside nav .text-muted {
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #9ca3af !important;
        }

        /* Collapsed sidebar icons */
        aside.w-16 nav .nav-link {
            justify-content: center;
            padding: 0.75rem 0.5rem;
        }

        /* Minimal table (Bootstrap + DataTables) */
        table.table.datatable-minimal { border-color: rgba(0,0,0,.06); font-size: 0.9rem; }
        table.table.datatable-minimal th,
        table.table.datatable-minimal td {
            padding-top: .3rem;
            padding-bottom: .3rem;
            vertical-align: middle;
        }
        table.table.datatable-minimal thead th { font-weight: 600; }
        table.table.datatable-minimal tbody tr:hover { background-color: rgba(0,0,0,.02); }
        /* Hide DataTables default clutter when minimal */
        .dt-length, .dt-search { display: none !important; }
        .dt-paging .dt-paging-button { padding: .2rem .4rem; border-radius: .25rem; }
        .dt-info { font-size: .85rem; }

        /* Minimal forms */
        .form-minimal .form-label { margin-bottom: .25rem; font-weight: 500; }
        .form-minimal .form-control, .form-minimal .form-select { border-radius: .375rem; }
        .form-minimal .form-text { color: #6c757d; }
        .form-minimal .form-group + .form-group { margin-top: .75rem; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased" x-data="{
    sidebarOpen: localStorage.getItem('sidebarOpen') === null ? window.innerWidth >= 1024 : localStorage.getItem('sidebarOpen') === 'true',
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        localStorage.setItem('sidebarOpen', this.sidebarOpen);
    },
    temporarilyOpenSidebar() {
        if (!this.sidebarOpen) {
            this.sidebarOpen = true;
            localStorage.setItem('sidebarOpen', true);
        }
    },
    formSubmitted: false,
}">
    <!-- Main Container -->
    <div class="min-h-screen flex flex-col">
        <x-layouts.app.header />
        <!-- Main Content Area -->
        <div class="flex flex-1 overflow-hidden">
            <x-layouts.app.sidebar />
            <!-- Main Content -->
            <main class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-900 content-transition">
                <div class="p-6">
                    <!-- Toast notifications are handled globally via Toastr (see scripts at the bottom) -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <!-- Bootstrap 5 JS for module pages -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- jQuery (required by Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- DataTables JS (global) -->
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

    <script>
        // Auto-initialize Select2 on elements with .select2
        (function() {
            function initSelect2(context) {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
                const $ = jQuery;
                const $ctx = context ? $(context) : $(document);
                $ctx.find('select.select2').not('[data-select2-initialized]')
                    .each(function() {
                        const $el = $(this);
                        const placeholder = $el.attr('data-placeholder') || '— Select —';
                        const allowClear = $el.is('[data-allow-clear]') || $el.find('option[value=""]').length > 0;
                        $el.select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: placeholder,
                            allowClear: allowClear,
                        });
                        $el.attr('data-select2-initialized', '1');
                    });
            }
            document.addEventListener('DOMContentLoaded', function() {
                initSelect2();
            });
            // Re-init on Bootstrap modal show if forms are injected later
            if (window.bootstrap) {
                document.addEventListener('shown.bs.modal', function(e){ initSelect2(e.target); });
            }
        })();
    </script>

    <script>
        // Reusable chained select utility
        (function(){
            function populateSelect($target, items, idField, textField, placeholder, includeEmpty) {
                const hasSelect2 = !!($target.data('select2'));
                if (hasSelect2) {
                    $target.val(null).trigger('change');
                    $target.empty();
                } else {
                    $target.empty();
                }
                if (includeEmpty && !($target.prop('multiple'))) {
                    $target.append(new Option(placeholder || '— Select —', ''));
                }
                if (Array.isArray(items)) {
                    items.forEach(function(it){
                        const val = it[idField] ?? it.value ?? it.id;
                        const text = it[textField] ?? it.text ?? String(val);
                        $target.append(new Option(text, val));
                    });
                } else if (items && typeof items === 'object') {
                    Object.keys(items).forEach(function(key){
                        $target.append(new Option(items[key], key));
                    });
                }
                $target.prop('disabled', false);
                if (hasSelect2) {
                    $target.trigger('change');
                }
            }

            function resolveUrl($src, rawUrl, value, paramName) {
                if (!rawUrl) return null;
                if (rawUrl.indexOf('{value}') !== -1) {
                    return rawUrl.replace('{value}', encodeURIComponent(value ?? ''));
                }
                const url = new URL(rawUrl, window.location.origin);
                if (paramName) {
                    url.searchParams.set(paramName, value ?? '');
                }
                return url.toString();
            }

            function handleChange(e){
                const src = e.currentTarget;
                const $src = jQuery(src);
                const targetSelector = $src.attr('data-target');
                const url = $src.attr('data-url');
                const paramName = $src.attr('data-param') || 'value';
                const idField = $src.attr('data-id') || 'id';
                const textField = $src.attr('data-text') || 'text';
                const placeholder = $src.attr('data-placeholder') || '— Select —';
                const includeEmpty = $src.is('[data-include-empty]');
                const $target = jQuery(targetSelector);
                if (!$target.length) return;
                const value = $src.val();
                const resolved = resolveUrl($src, url, value, paramName);
                if (!resolved) return;
                $target.prop('disabled', true);
                fetch(resolved)
                    .then(function(r){ return r.json(); })
                    .then(function(data){ populateSelect($target, data, idField, textField, placeholder, includeEmpty); })
                    .catch(function(){ $target.prop('disabled', false); });
            }

            document.addEventListener('DOMContentLoaded', function(){
                if (!window.jQuery) return;
                jQuery(document).on('change', '[data-chained-select]', handleChange);
            });
        })();
    </script>

    <script>
        // Auto-attach confirmation to forms/buttons
        (function(){
            function onConfirmClick(e){
                const text = e.currentTarget.getAttribute('data-confirm');
                if (!text) return;
                if (!window.confirm(text)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            }
            document.addEventListener('DOMContentLoaded', function(){
                document.addEventListener('click', function(e){
                    const t = e.target.closest('[data-confirm]');
                    if (t) {
                        onConfirmClick({ currentTarget: t, preventDefault: () => e.preventDefault(), stopImmediatePropagation: () => e.stopImmediatePropagation() });
                    }
                }, true);
            });
        })();
    </script>

    <script>
        // Toastr helper
        (function(){
            function showToast(type, message, title) {
                if (!window.toastr) return;
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right',
                    timeOut: 3500,
                };
                toastr[type || 'info'](message || '', title || '');
            }
            window.appToast = showToast;
            document.addEventListener('DOMContentLoaded', function(){
                const toast = document.querySelector('[data-toast]');
                if (toast) {
                    const type = toast.getAttribute('data-toast');
                    const msg = toast.getAttribute('data-message');
                    const title = toast.getAttribute('data-title') || '';
                    if (type && msg) showToast(type, msg, title);
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
