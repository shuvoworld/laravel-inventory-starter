<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

    <!-- AdminLTE 3 CSS & dependencies (via CDN for minimal setup) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

    @stack('styles')

    <style>
        /* Make sure page content fills height */
        html, body { height: 100%; }
        .content-header .breadcrumb { margin-bottom: 0; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ url('/') }}" class="nav-link">Home</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- User Dropdown -->
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
                        <i class="far fa-user"></i> {{ auth()->user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ route('settings.profile.edit') }}" class="dropdown-item">
                            <i class="fas fa-id-badge mr-2"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger"><i class="fas fa-sign-out-alt mr-2"></i> Logout</button>
                        </form>
                    </div>
                </li>
            @endauth
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ url('/') }}" class="brand-link">
            <img src="{{ asset('favicon.ico') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">{{ config('app.name') }}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- User Management -->
                    <li class="nav-item has-treeview {{ request()->routeIs('modules.users.*') || request()->routeIs('modules.roles.*') || request()->routeIs('modules.permissions.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('modules.users.*') || request()->routeIs('modules.roles.*') || request()->routeIs('modules.permissions.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>
                                User Management
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('users.view')
                                <li class="nav-item">
                                    <a href="{{ route('modules.users.index') }}" class="nav-link {{ request()->routeIs('modules.users.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Users</p>
                                    </a>
                                </li>
                            @endcan
                            @can('roles.view')
                                <li class="nav-item">
                                    <a href="{{ route('modules.roles.index') }}" class="nav-link {{ request()->routeIs('modules.roles.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Roles</p>
                                    </a>
                                </li>
                            @endcan
                            @can('permissions.view')
                                <li class="nav-item">
                                    <a href="{{ route('modules.permissions.index') }}" class="nav-link {{ request()->routeIs('modules.permissions.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Permissions</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>

                    <!-- Inventory & Sales -->
                    <li class="nav-item has-treeview {{ request()->routeIs('modules.products.*') || request()->routeIs('modules.suppliers.*') || request()->routeIs('modules.customers.*') || request()->routeIs('modules.purchase-orders.*') || request()->routeIs('modules.sales-orders.*') || request()->routeIs('modules.stock-movements.*') || request()->routeIs('modules.reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('modules.products.*') || request()->routeIs('modules.suppliers.*') || request()->routeIs('modules.customers.*') || request()->routeIs('modules.purchase-orders.*') || request()->routeIs('modules.sales-orders.*') || request()->routeIs('modules.stock-movements.*') || request()->routeIs('modules.reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-pills"></i>
                            <p>
                                Inventory & Sales
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @if (Route::has('modules.products.index'))
                            <li class="nav-item">
                                <a href="{{ route('modules.products.index') }}" class="nav-link {{ request()->routeIs('modules.products.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Products</p>
                                </a>
                            </li>
                            @endif

                            @if (Route::has('modules.suppliers.index'))
                            <li class="nav-item">
                                <a href="{{ route('modules.suppliers.index') }}" class="nav-link {{ request()->routeIs('modules.suppliers.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Suppliers</p>
                                </a>
                            </li>
                            @endif

                            @if (Route::has('modules.customers.index'))
                            <li class="nav-item">
                                <a href="{{ route('modules.customers.index') }}" class="nav-link {{ request()->routeIs('modules.customers.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Customers</p>
                                </a>
                            </li>
                            @endif

                            @if (Route::has('modules.purchase-orders.index'))
                            <li class="nav-item">
                                <a href="{{ route('modules.purchase-orders.index') }}" class="nav-link {{ request()->routeIs('modules.purchase-orders.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Purchase Orders</p>
                                </a>
                            </li>
                            @endif

                            @if (Route::has('modules.sales-orders.index'))
                            <li class="nav-item">
                                <a href="{{ route('modules.sales-orders.index') }}" class="nav-link {{ request()->routeIs('modules.sales-orders.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Sales Orders</p>
                                </a>
                            </li>
                            @endif

                            @if (Route::has('modules.stock-movements.index'))
                            <li class="nav-item">
                                <a href="{{ route('modules.stock-movements.index') }}" class="nav-link {{ request()->routeIs('modules.stock-movements.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Stock Movements</p>
                                </a>
                            </li>
                            @endif

                            @if (Route::has('modules.reports.index'))
                            <li class="nav-item">
                                <a href="{{ route('modules.reports.index') }}" class="nav-link {{ request()->routeIs('modules.reports.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Reports</p>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>

                    @role('admin')
                        <li class="nav-item">
                            <a href="{{ route('admin.modules.index') }}" class="nav-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cubes"></i>
                                <p>Modules</p>
                            </a>
                        </li>
                    @endrole
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('page-title')</h1>
                    </div>
                    <div class="col-sm-6">
                        @hasSection('breadcrumb')
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumb')
                            </ol>
                        @endif
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            v3
        </div>
        <strong>&copy; {{ date('Y') }} {{ config('app.name') }}.</strong> All rights reserved.
    </footer>
</div>

<!-- REQUIRED SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

<script>
    // Auto-initialize Select2 on elements with .select2
    (function() {
        function initSelect2(context) {
            if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) { return; }
            const $ = jQuery;
            const $ctx = context ? $(context) : $(document);
            $ctx.find('select.select2').not('[data-select2-initialized]')
                .each(function() {
                    const $el = $(this);
                    const placeholder = $el.attr('data-placeholder') || '— Select —';
                    const allowClear = $el.is('[data-allow-clear]') || $el.find('option[value=""]').length > 0;
                    $el.select2({
                        theme: 'default',
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
            if (!rawUrl) { return null; }
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
            if (!$target.length) { return; }
            const value = $src.val();
            const resolved = resolveUrl($src, url, value, paramName);
            if (!resolved) { return; }
            $target.prop('disabled', true);
            fetch(resolved)
                .then(function(r){ return r.json(); })
                .then(function(data){ populateSelect($target, data, idField, textField, placeholder, includeEmpty); })
                .catch(function(){ $target.prop('disabled', false); });
        }

        document.addEventListener('DOMContentLoaded', function(){
            if (!window.jQuery) { return; }
            jQuery(document).on('change', '[data-chained-select]', handleChange);
        });
    })();
</script>

<script>
    // Auto-attach confirmation to forms/buttons
    (function(){
        function onConfirmClick(e){
            const text = e.currentTarget.getAttribute('data-confirm');
            if (!text) { return; }
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
            if (!window.toastr) { return; }
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
                if (type && msg) { showToast(type, msg, title); }
            }
        });
    })();
</script>

@stack('scripts')
</body>
</html>
