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
                    <!-- Success Message -->
                    @session('status')
                        <div x-data="{ showStatusMessage: true }" x-show="showStatusMessage"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="mb-6 bg-green-50 dark:bg-green-900 border-l-4 border-green-500 p-4 rounded-md">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-500 dark:text-green-400"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700 dark:text-green-200">{{ session('status') }}</p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <div class="-mx-1.5 -my-1.5">
                                        <button @click="showStatusMessage = false"
                                            class="inline-flex rounded-md p-1.5 text-green-500 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <span class="sr-only">{{ __('Dismiss') }}</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endsession

                    {{ $slot }}

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
                const targetId = $src.attr('data-chain-target');
                if (!targetId) return;
                const $tgt = jQuery('#' + targetId);
                if ($tgt.length === 0) return;
                const value = $src.val();
                const rawUrl = $src.attr('data-chain-url') || $src.attr('data-chain-url-template');
                const paramName = $src.attr('data-chain-param') || 'parent';
                const idField = $src.attr('data-id-field') || 'id';
                const textField = $src.attr('data-text-field') || 'text';
                const placeholder = $tgt.attr('data-placeholder') || '— Select —';
                const includeEmpty = $tgt.is('[data-include-empty]') || $tgt.find('option[value=""]').length > 0;

                if (!value) {
                    populateSelect($tgt, [], idField, textField, placeholder, includeEmpty);
                    return;
                }

                const url = resolveUrl($src, rawUrl, value, paramName);
                if (!url) return;

                $tgt.prop('disabled', true);
                $tgt.addClass('is-loading');
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(function(res){ return res.json(); })
                .then(function(json){
                    // Support DataTables-like {data: [...]} or plain array/map
                    const items = Array.isArray(json) ? json : (json.data ?? json.items ?? json.options ?? json);
                    populateSelect($tgt, items, idField, textField, placeholder, includeEmpty);
                })
                .catch(function(){
                    populateSelect($tgt, [], idField, textField, placeholder, includeEmpty);
                })
                .finally(function(){
                    $tgt.removeClass('is-loading');
                });
            }

            function init(context){
                if (!window.jQuery) return;
                const $ctx = context ? jQuery(context) : jQuery(document);
                $ctx.find('select[data-chain-target], select[data-chain-url], select[data-chain-url-template]')
                    .off('change.chainselect').on('change.chainselect', handleChange)
                    .each(function(){
                        const $src = jQuery(this);
                        // Auto-run if has preset value
                        const hasValue = $src.val() && ($src.val() !== '');
                        const autorun = $src.is('[data-chain-autoload]') || hasValue;
                        if (autorun) {
                            $src.trigger('change');
                        }
                    });
            }

            document.addEventListener('DOMContentLoaded', function(){ init(); });
            if (window.bootstrap) {
                document.addEventListener('shown.bs.modal', function(e){ init(e.target); });
            }
            window.initChainSelects = init;
        })();
    </script>

    @stack('scripts')
</body>

</html>
