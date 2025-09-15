<x-layouts.app>

    <div class="mb-3">
        <h1 class="h3 mb-0">{{ __('Dashboard') }}</h1>
    </div>

    <style>
        /* Smaller dashboard tiles using simple Bootstrap-friendly CSS */
        .tile-card-sm {
            aspect-ratio: 9 / 5;           /* slightly shorter than square to look smaller */
            border-radius: .5rem;          /* rounded corners */
        }
        .tile-card-sm .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;       /* center content vertically */
            padding: .6rem;                /* more compact padding */
        }
        .tile-icon { width: 28px; height: 28px; } /* smaller icon circle */
        /* Make the number smaller without changing markup */
        .tile-card-sm .fs-4 { font-size: 1.1rem !important; }
        @media (max-width: 575.98px) {
            /* allow flexible height on extra small screens */
            .tile-card-sm { aspect-ratio: auto; }
        }
    </style>

    <div class="row g-3">
        @can('users.view')
            <div class="col-3 col-sm-3">
                <a href="{{ route('modules.users.index') }}" class="text-decoration-none">
                    <!-- Smaller, colored card for Users -->
                    <div class="card tile-card-sm shadow-sm bg-primary-subtle border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small">{{ __('Users') }}</div>
                                    <div class="fs-4 fw-semibold mt-1">{{ \App\Models\User::count() }}</div>
                                </div>
                                <div class="bg-white text-primary p-2 rounded-circle d-inline-flex align-items-center justify-content-center tile-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="small text-muted mt-2">{{ __('Manage users') }}</div>
                        </div>
                    </div>
                </a>
            </div>
        @endcan

        @can('contact.view')
            <div class="col-3 col-sm-3">
                <a href="{{ route('modules.contact.index') }}" class="text-decoration-none">
                    <!-- Smaller, colored card for Contacts -->
                    <div class="card tile-card-sm shadow-sm bg-success-subtle border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small">{{ __('Contacts') }}</div>
                                    <div class="fs-4 fw-semibold mt-1">{{ \App\Modules\Contact\Models\Contact::count() }}</div>
                                </div>
                                <div class="bg-white text-success p-2 rounded-circle d-inline-flex align-items-center justify-content-center tile-icon">
                                    <i class="fas fa-address-book"></i>
                                </div>
                            </div>
                            <div class="small text-muted mt-2">{{ __('Manage contacts') }}</div>
                        </div>
                    </div>
                </a>
            </div>
        @endcan

        @can('blog-category.view')
            <div class="col-3 col-sm-3">
                <a href="{{ route('modules.blog-category.index') }}" class="text-decoration-none">
                    <!-- Smaller, colored card for Blog Categories -->
                    <div class="card tile-card-sm shadow-sm bg-warning-subtle border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small">{{ __('Blog Categories') }}</div>
                                    <div class="fs-4 fw-semibold mt-1">{{ \App\Modules\BlogCategory\Models\BlogCategory::count() }}</div>
                                </div>
                                <div class="bg-white text-warning p-2 rounded-circle d-inline-flex align-items-center justify-content-center tile-icon">
                                    <i class="fas fa-folder-tree"></i>
                                </div>
                            </div>
                            <div class="small text-muted mt-2">{{ __('Manage blog categories') }}</div>
                        </div>
                    </div>
                </a>
            </div>
        @endcan
    </div>

    <div class="row g-3 mt-1">
        @can('users.view')
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <h2 class="h6 mb-0">{{ __('Recent Users') }}</h2>
                    <a href="{{ route('modules.users.index') }}" class="small">{{ __('View all') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dashboard-users-table" class="table table-hover align-middle datatable-minimal table-sm w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        @can('contact.view')
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <h2 class="h6 mb-0">{{ __('Recent Contacts') }}</h2>
                    <a href="{{ route('modules.contact.index') }}" class="small">{{ __('View all') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dashboard-contacts-table" class="table table-hover align-middle datatable-minimal table-sm w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Created</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Users table on dashboard
            const usersEl = document.querySelector('#dashboard-users-table');
            if (usersEl) {
                new DataTable(usersEl, {
                    serverSide: true,
                    processing: true,
                    ajax: { url: '{{ route('modules.users.data') }}', dataSrc: 'data' },
                    columns: [
                        { data: 'id' },
                        { data: 'name' },
                        { data: 'email' },
                        { data: 'roles' },
                        { data: 'actions', orderable: false, searchable: false },
                    ],
                    order: [[0, 'desc']],
                    lengthChange: false,
                    searching: false,
                    pageLength: 5,
                    pagingType: 'simple_numbers',
                    layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' }
                });
            }

            // Contacts table on dashboard
            const contactsEl = document.querySelector('#dashboard-contacts-table');
            if (contactsEl) {
                new DataTable(contactsEl, {
                    serverSide: true,
                    processing: true,
                    ajax: { url: '{{ route('modules.contact.data') }}', dataSrc: 'data' },
                    columns: [
                        { data: 'id' },
                        { data: 'name' },
                        { data: 'created_at' },
                        { data: 'updated_at' },
                        { data: 'actions', orderable: false, searchable: false },
                    ],
                    order: [[0, 'desc']],
                    lengthChange: false,
                    searching: false,
                    pageLength: 5,
                    pagingType: 'simple_numbers',
                    layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' }
                });
            }
        });
    </script>
    @endpush

</x-layouts.app>
