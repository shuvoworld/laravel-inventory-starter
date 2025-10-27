@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Edit Variant</h1>
    <a href="{{ route('modules.product-variant.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Variants
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit Variant: {{ $variant->getDisplayName() }}</h3>
        <small class="text-muted">Product: {{ $variant->product->name }}</small>
    </div>
    <form method="POST" action="{{ route('modules.product-variant.update', $variant->id) }}" enctype="multipart/form-data" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <!-- Variant Image -->
                <div class="col-12">
                    <label class="form-label">Variant Image</label>
                    @if($variant->image)
                        <div class="mb-3">
                            <img id="currentImage" src="{{ asset('storage/' . $variant->image) }}" alt="{{ $variant->variant_name }}" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeImage()">
                                    <i class="fas fa-trash me-1"></i> Remove Image
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <img src="{{ $variant->product->getImageOrPlaceholder() }}" alt="No image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                        </div>
                    @endif

                    <input id="image" type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="previewImage(event)">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Max size: 2MB. Supported formats: JPEG, PNG, GIF, WebP</small>

                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <p class="text-sm text-muted">New image preview:</p>
                        <img id="preview" src="" alt="Image Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                    </div>

                    <input type="hidden" id="removeImageInput" name="remove_image" value="0">
                </div>

                <!-- Basic Information -->
                <div class="col-12 col-md-4">
                    <label for="sku" class="form-label">SKU</label>
                    <input id="sku" type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $variant->sku) }}">
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="barcode" class="form-label">Barcode</label>
                    <input id="barcode" type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode', $variant->barcode) }}">
                    @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="variant_name" class="form-label">Variant Name</label>
                    <input id="variant_name" type="text" name="variant_name" class="form-control @error('variant_name') is-invalid @enderror" value="{{ old('variant_name', $variant->variant_name) }}">
                    @error('variant_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Variant Options -->
                <div class="col-12">
                    <h5 class="mb-3">Variant Options</h5>
                    @foreach($options as $option)
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3">
                                <label class="form-label">{{ $option->name }}</label>
                            </div>
                            <div class="col-12 col-md-9">
                                <select name="option_values[{{ $option->id }}]" class="form-select @error('option_values.'.$option->id) is-invalid @enderror" required>
                                    <option value="">Select {{ $option->name }}</option>
                                    @foreach($option->values as $value)
                                        <option value="{{ $value->id }}" {{ in_array($value->id, $variant->optionValues->pluck('id')->toArray()) ? 'selected' : '' }}>
                                            {{ $value->value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('option_values.'.$option->id)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pricing -->
                <div class="col-12 col-md-3">
                    <label for="cost_price" class="form-label">Cost Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input id="cost_price" type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $variant->cost_price) }}">
                    </div>
                    @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-3">
                    <label for="minimum_profit_margin" class="form-label">Min Profit Margin</label>
                    <div class="input-group">
                        <input id="minimum_profit_margin" type="number" step="0.01" class="form-control @error('minimum_profit_margin') is-invalid @enderror" value="{{ old('minimum_profit_margin', $variant->minimum_profit_margin) }}">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('minimum_profit_margin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-3">
                    <label for="standard_profit_margin" class="form-label">Standard Profit Margin</label>
                    <div class="input-group">
                        <input id="standard_profit_margin" type="number" step="0.01" class="form-control @error('standard_profit_margin') is-invalid @enderror" value="{{ old('standard_profit_margin', $variant->standard_profit_margin) }}">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('standard_profit_margin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-3">
                    <label for="target_price" class="form-label">Target Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input id="target_price" type="number" step="0.01" class="form-control @error('target_price') is-invalid @enderror" value="{{ old('target_price', $variant->target_price) }}">
                    </div>
                    @error('target_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Inventory -->
                <div class="col-12 col-md-3">
                    <label for="quantity_on_hand" class="form-label">Quantity on Hand</label>
                    <input id="quantity_on_hand" type="number" class="form-control @error('quantity_on_hand') is-invalid @enderror" value="{{ old('quantity_on_hand', $variant->quantity_on_hand) }}" required>
                    @error('quantity_on_hand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-3">
                    <label for="reorder_level" class="form-label">Reorder Level</label>
                    <input id="reorder_level" type="number" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', $variant->reorder_level) }}">
                    @error('reorder_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-3">
                    <label for="weight" class="form-label">Weight (kg)</label>
                    <input id="weight" type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $variant->weight) }}">
                    @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Options -->
                <div class="col-12 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $variant->is_default) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">
                            Set as default variant
                        </label>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $variant->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Update Variant
            </button>
            <a href="{{ route('modules.product-variant.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    }

    function removeImage() {
        if (confirm('Are you sure you want to remove the variant image?')) {
            document.getElementById('removeImageInput').value = '1';
            document.getElementById('currentImage').style.display = 'none';
            document.getElementById('imagePreview').style.display = 'none';
        }
    }

    // Auto-calculate target price based on cost price and margin
    document.getElementById('cost_price').addEventListener('input', updateTargetPrice);
    document.getElementById('minimum_profit_margin').addEventListener('input', updateTargetPrice);
    document.getElementById('standard_profit_margin').addEventListener('input', updateTargetPrice);

    function updateTargetPrice() {
        const costPrice = parseFloat(document.getElementById('cost_price').value) || 0;
        const margin = parseFloat(document.getElementById('standard_profit_margin').value) || 0;
        const targetPrice = costPrice * (1 + margin / 100);

        if (targetPrice > 0) {
            document.getElementById('target_price').value = targetPrice.toFixed(2);
        }
    }
</script>
@endpush
@endsection