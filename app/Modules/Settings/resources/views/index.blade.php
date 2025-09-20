@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Store Settings</h1>
</div>

<form method="POST" action="{{ route('modules.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-8">
            <!-- Store Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-store me-2"></i>Store Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="store_name" class="form-label">Store Name</label>
                            <input type="text" id="store_name" name="store_name" class="form-control @error('store_name') is-invalid @enderror" value="{{ old('store_name', $settings['store']['store_name']) }}">
                            @error('store_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="store_email" class="form-label">Store Email</label>
                            <input type="email" id="store_email" name="store_email" class="form-control @error('store_email') is-invalid @enderror" value="{{ old('store_email', $settings['store']['store_email']) }}">
                            @error('store_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="store_description" class="form-label">Store Description</label>
                            <textarea id="store_description" name="store_description" class="form-control @error('store_description') is-invalid @enderror" rows="3">{{ old('store_description', $settings['store']['store_description']) }}</textarea>
                            @error('store_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="store_address" class="form-label">Address</label>
                            <textarea id="store_address" name="store_address" class="form-control @error('store_address') is-invalid @enderror" rows="2">{{ old('store_address', $settings['store']['store_address']) }}</textarea>
                            @error('store_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="store_city" class="form-label">City</label>
                            <input type="text" id="store_city" name="store_city" class="form-control @error('store_city') is-invalid @enderror" value="{{ old('store_city', $settings['store']['store_city']) }}">
                            @error('store_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="store_state" class="form-label">State/Province</label>
                            <input type="text" id="store_state" name="store_state" class="form-control @error('store_state') is-invalid @enderror" value="{{ old('store_state', $settings['store']['store_state']) }}">
                            @error('store_state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="store_postal_code" class="form-label">Postal Code</label>
                            <input type="text" id="store_postal_code" name="store_postal_code" class="form-control @error('store_postal_code') is-invalid @enderror" value="{{ old('store_postal_code', $settings['store']['store_postal_code']) }}">
                            @error('store_postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="store_country" class="form-label">Country</label>
                            <input type="text" id="store_country" name="store_country" class="form-control @error('store_country') is-invalid @enderror" value="{{ old('store_country', $settings['store']['store_country']) }}">
                            @error('store_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="store_phone" class="form-label">Phone Number</label>
                            <input type="tel" id="store_phone" name="store_phone" class="form-control @error('store_phone') is-invalid @enderror" value="{{ old('store_phone', $settings['store']['store_phone']) }}">
                            @error('store_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="store_website" class="form-label">Website</label>
                            <input type="url" id="store_website" name="store_website" class="form-control @error('store_website') is-invalid @enderror" value="{{ old('store_website', $settings['store']['store_website']) }}">
                            @error('store_website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="store_currency" class="form-label">Currency</label>
                            <select id="store_currency" name="store_currency" class="form-control @error('store_currency') is-invalid @enderror">
                                <option value="USD" {{ old('store_currency', $settings['store']['store_currency']) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ old('store_currency', $settings['store']['store_currency']) == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ old('store_currency', $settings['store']['store_currency']) == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                <option value="CAD" {{ old('store_currency', $settings['store']['store_currency']) == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                <option value="AUD" {{ old('store_currency', $settings['store']['store_currency']) == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                <option value="JPY" {{ old('store_currency', $settings['store']['store_currency']) == 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                            </select>
                            @error('store_currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="store_tax_rate" class="form-label">Default Tax Rate (%)</label>
                            <input type="number" id="store_tax_rate" name="store_tax_rate" class="form-control @error('store_tax_rate') is-invalid @enderror" value="{{ old('store_tax_rate', $settings['store']['store_tax_rate']) }}" step="0.01" min="0" max="100">
                            @error('store_tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>Business Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="business_registration" class="form-label">Business Registration Number</label>
                            <input type="text" id="business_registration" name="business_registration" class="form-control @error('business_registration') is-invalid @enderror" value="{{ old('business_registration', $settings['business']['business_registration']) }}">
                            @error('business_registration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tax_id" class="form-label">Tax ID / VAT Number</label>
                            <input type="text" id="tax_id" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" value="{{ old('tax_id', $settings['business']['tax_id']) }}">
                            @error('tax_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $settings['business']['bank_name']) }}">
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="bank_account" class="form-label">Bank Account Number</label>
                            <input type="text" id="bank_account" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror" value="{{ old('bank_account', $settings['business']['bank_account']) }}">
                            @error('bank_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Store Logo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-image me-2"></i>Store Logo
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($settings['store']['store_logo'])
                            <img src="{{ asset('storage/' . $settings['store']['store_logo']) }}" alt="Store Logo" class="img-fluid rounded mb-3" style="max-height: 200px;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="store_logo" class="form-label">Upload New Logo</label>
                        <input type="file" id="store_logo" name="store_logo" class="form-control @error('store_logo') is-invalid @enderror" accept="image/*">
                        @error('store_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Recommended: 200x200px, PNG or JPG, max 2MB</small>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Products:</span>
                        <strong>{{ \App\Modules\Products\Models\Product::count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Customers:</span>
                        <strong>{{ \App\Modules\Customers\Models\Customer::count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sales Orders:</span>
                        <strong>{{ \App\Modules\SalesOrder\Models\SalesOrder::count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Purchase Orders:</span>
                        <strong>{{ \App\Modules\PurchaseOrder\Models\PurchaseOrder::count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Settings
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>
@endsection
