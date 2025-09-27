@extends('layouts.adminlte')

@section('title', 'Store Settings')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Store Settings</h1>
        <div>
            <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#importModal">
                <i class="fas fa-upload"></i> Import
            </button>
            <a href="{{ route('modules.store-settings.export') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-download"></i> Export
            </a>
            <form method="POST" action="{{ route('modules.store-settings.clear-cache') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm" onclick="return confirm('Clear all settings cache?')">
                    <i class="fas fa-trash"></i> Clear Cache
                </button>
            </form>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('modules.store-settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Settings Tabs -->
        <div class="card">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    @foreach($groups as $groupKey => $groupLabel)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                               id="{{ $groupKey }}-tab"
                               data-toggle="tab"
                               href="#{{ $groupKey }}"
                               role="tab">
                                <i class="fas fa-{{ \App\Modules\StoreSettings\Http\Controllers\StoreSettingsController::getGroupIcon($groupKey) }}"></i> {{ $groupLabel }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="settingsTabContent">
                    @foreach($groups as $groupKey => $groupLabel)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                             id="{{ $groupKey }}"
                             role="tabpanel">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>{{ $groupLabel }}</h4>
                                <form method="POST" action="{{ route('modules.store-settings.reset') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="group" value="{{ $groupKey }}">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm"
                                            onclick="return confirm('Reset all {{ strtolower($groupLabel) }} to defaults?')">
                                        <i class="fas fa-undo"></i> Reset to Defaults
                                    </button>
                                </form>
                            </div>

                            @if(isset($settings[$groupKey]))
                                <div class="row">
                                    @foreach($settings[$groupKey] as $setting)
                                        <div class="col-md-6 mb-3">
                                            @include('StoreSettings::partials.setting-field', ['setting' => $setting])
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No settings available for this group.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('modules.store-settings.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Settings</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="settings_file">Settings File (JSON)</label>
                            <input type="file" class="form-control-file" name="settings_file" accept=".json" required>
                            <small class="form-text text-muted">Upload a JSON file exported from this system.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Currency symbol mapping
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'CAD': 'C$',
        'AUD': 'A$',
        'JPY': '¥',
        'CNY': '¥',
        'INR': '₹',
        'BDT': '৳'
    };

    // Initialize file input previews
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        const preview = $(this).siblings('.file-preview');
        const label = $(this).siblings('.custom-file-label');

        // Update the label with the file name
        if (file) {
            label.text(file.name);
        } else {
            label.text('Choose file...');
        }

        // Show image preview for image files
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" class="img-thumbnail mt-2" style="max-height: 100px;">');
            };
            reader.readAsDataURL(file);
        } else {
            preview.html('');
        }
    });

    // Initialize Select2 for select fields
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Auto-update currency symbol when currency code changes
    $('#currency_code').on('change', function() {
        const selectedCurrency = $(this).val();
        const symbolField = $('#currency_symbol');

        if (currencySymbols[selectedCurrency]) {
            symbolField.val(currencySymbols[selectedCurrency]);

            // Show a notification
            toastr.info('Currency symbol updated automatically to: ' + currencySymbols[selectedCurrency]);
        }
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop

