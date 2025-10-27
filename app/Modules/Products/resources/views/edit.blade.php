@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit {{ ucfirst(__('Product')) }}</h3>
    </div>
    <form method="POST" action="{{ route('modules.products.update', $item->id) }}" enctype="multipart/form-data" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
        @csrf
        @method('PUT')
        <div class="card-body form-minimal">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Product Image</label>

                    @if($item->image)
                        <div class="mb-3">
                            <img id="currentImage" src="{{ $item->image_url }}" alt="{{ $item->name }}" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeImage()">
                                    <i class="fas fa-trash me-1"></i> Remove Image
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <img src="{{ $item->getImageOrPlaceholder() }}" alt="No image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e5e7eb;">
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
                <div class="col-12 col-md-4">
                    <label for="sku" class="form-label">SKU</label>
                    <input id="sku" type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $item->sku) }}">
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="brand_id" class="form-label">Brand</label>
                    <select id="brand_id" name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                        <option value="">-- Select Brand --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $item->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="has_variants" name="has_variants" value="1"
                               {{ old('has_variants', $item->has_variants) ? 'checked' : '' }}
                               onchange="toggleVariantSection()">
                        <label class="form-check-label" for="has_variants">
                            <strong>This product has variants</strong>
                            <small class="d-block text-muted">Enable this if the product comes in different sizes, colors, or other variations</small>
                        </label>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="unit" class="form-label">Unit</label>
                    <input id="unit" type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $item->unit) }}" placeholder="pcs, box, bottle">
                    @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="reorder_level" class="form-label">Reorder Level</label>
                    <input id="reorder_level" type="number" min="0" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', $item->reorder_level) }}">
                    @error('reorder_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Current Stock</label>
                    <input type="number" class="form-control" value="{{ $item->getCurrentStock() }}" disabled>
                    <small class="text-muted">Calculated from stock movements</small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="minimum_profit_margin" class="form-label">Minimum Profit Margin (%)</label>
                    <div class="input-group">
                        <input id="minimum_profit_margin" type="number" step="0.01" min="0" max="100" name="minimum_profit_margin" class="form-control @error('minimum_profit_margin') is-invalid @enderror" value="{{ old('minimum_profit_margin', $item->minimum_profit_margin) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Minimum profit margin percentage - used to calculate floor price</small>
                    @error('minimum_profit_margin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="standard_profit_margin" class="form-label">Standard Profit Margin (%)</label>
                    <div class="input-group">
                        <input id="standard_profit_margin" type="number" step="0.01" min="0" max="100" name="standard_profit_margin" class="form-control @error('standard_profit_margin') is-invalid @enderror" value="{{ old('standard_profit_margin', $item->standard_profit_margin) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Standard profit margin percentage - used to calculate target price</small>
                    @error('standard_profit_margin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="cost_price" class="form-label">
                        Cost Price (WAC)
                        <small class="text-muted">(Auto-calculated)</small>
                    </label>
                    <input id="cost_price" type="number" step="0.01" min="0" class="form-control" value="{{ number_format($item->cost_price, 2) }}" disabled>
                    <small class="text-muted">Weighted Average Cost from purchase orders</small>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Floor Price</label>
                    <input type="number" step="0.01" min="0" class="form-control" value="{{ number_format($item->floor_price, 2) }}" disabled>
                    <small class="text-muted">Calculated: Cost Price + {{ number_format($item->minimum_profit_margin, 2) }}% = Minimum selling price</small>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Target Price</label>
                    <input type="number" step="0.01" min="0" class="form-control" value="{{ number_format($item->target_price, 2) }}" disabled>
                    <small class="text-muted">Calculated: Cost Price + {{ number_format($item->standard_profit_margin, 2) }}% = Recommended selling price</small>
                </div>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Cost Price:</strong> Product cost is automatically calculated from purchase orders using the Weighted Average Cost (WAC) method. It will be updated automatically when you receive purchase orders.
                        <br><strong>Floor Price:</strong> Cost Price + Minimum Profit Margin (minimum selling price)
                        <br><strong>Target Price:</strong> Cost Price + Standard Profit Margin (recommended selling price)
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-rotate me-1"></i> Update
            </button>
            <a href="{{ route('modules.products.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- Variant Management Section -->
<div id="variantSection" class="card mt-3" style="display: {{ $item->has_variants ? 'block' : 'none' }};">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Product Variants</h4>
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-sm" onclick="showGenerateModal()">
                    <i class="fas fa-layer-group"></i> Generate Variants
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="showAddVariantModal()">
                    <i class="fas fa-plus"></i> Add Single Variant
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="variantsTableContainer">
            @if($item->variants->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No variants created yet.
                    Use "Generate Variants" to create multiple variants at once, or "Add Single Variant" to create them one by one.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle" id="variantsTable">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Variant</th>
                                <th>SKU</th>
                                <th>Cost</th>
                                <th>Target Price</th>
                                <th style="width: 100px;">Stock</th>
                                <th style="width: 80px;">Status</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="variantsTableBody">
                            @foreach($item->variants as $variant)
                                <tr data-variant-id="{{ $variant->id }}">
                                    <td>
                                        <img src="{{ $variant->image_url ?? $item->image_url }}"
                                             alt="{{ $variant->variant_name }}"
                                             style="max-width: 50px; max-height: 50px; border-radius: 4px;">
                                    </td>
                                    <td>
                                        <strong>{{ $variant->variant_name }}</strong>
                                        @if($variant->is_default)
                                            <span class="badge bg-primary ms-1">Default</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $variant->sku }}</code></td>
                                    <td>${{ number_format($variant->getEffectiveCostPrice() ?? 0, 2) }}</td>
                                    <td><strong>${{ number_format($variant->getEffectiveTargetPrice() ?? 0, 2) }}</strong></td>
                                    <td>
                                        <span class="badge {{ $variant->isLowStock() ? 'bg-danger' : 'bg-success' }}">
                                            {{ $variant->quantity_on_hand }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $variant->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $variant->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editVariant({{ $variant->id }})" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteVariant({{ $variant->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Generate Variants Modal -->
<div class="modal fade" id="generateVariantsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Product Variants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Select options and their values to automatically generate all possible variant combinations.</p>
                <form id="generateVariantsForm">
                    <div id="optionsContainer"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addOptionSelector()">
                        <i class="fas fa-plus"></i> Add Another Option
                    </button>
                    <div class="alert alert-info mt-3">
                        <strong>Preview:</strong> <span id="variantCount">0</span> variants will be generated
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="generateVariants()">Generate Variants</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Variant Modal -->
<div class="modal fade" id="variantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="variantModalTitle">Add Variant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="variantForm">
                    <input type="hidden" id="variantId" name="variant_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" id="variantSku" name="sku">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="variantBarcode" name="barcode">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Options <span class="text-danger">*</span></label>
                            <div id="variantOptionsContainer"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cost Price</label>
                            <input type="number" step="0.01" class="form-control" id="variantCostPrice" name="cost_price"
                                   placeholder="Leave empty to inherit from product">
                            <small class="text-muted">Product cost: ${{ number_format($item->cost_price ?? 0, 2) }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity on Hand <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="variantQuantity" name="quantity_on_hand" value="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Minimum Profit Margin (%)</label>
                            <input type="number" step="0.01" class="form-control" id="variantMinMargin" name="minimum_profit_margin"
                                   placeholder="Leave empty to inherit">
                            <small class="text-muted">Product margin: {{ number_format($item->minimum_profit_margin ?? 0, 2) }}%</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Standard Profit Margin (%)</label>
                            <input type="number" step="0.01" class="form-control" id="variantStdMargin" name="standard_profit_margin"
                                   placeholder="Leave empty to inherit">
                            <small class="text-muted">Product margin: {{ number_format($item->standard_profit_margin ?? 0, 2) }}%</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" class="form-control" id="variantReorderLevel" name="reorder_level"
                                   value="{{ $item->reorder_level }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Weight</label>
                            <input type="number" step="0.01" class="form-control" id="variantWeight" name="weight">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="variantIsDefault" name="is_default" value="1">
                                <label class="form-check-label" for="variantIsDefault">
                                    Set as default variant
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="variantIsActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="variantIsActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveVariant()">Save Variant</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewImage(event) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    const file = event.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);

        // Reset remove flag when new image is selected
        document.getElementById('removeImageInput').value = '0';
    } else {
        previewContainer.style.display = 'none';
    }
}

function removeImage() {
    if (confirm('Are you sure you want to remove this image?')) {
        document.getElementById('removeImageInput').value = '1';
        const currentImage = document.getElementById('currentImage');
        if (currentImage) {
            currentImage.style.opacity = '0.3';
            currentImage.parentElement.querySelector('button').disabled = true;
            currentImage.parentElement.querySelector('button').textContent = 'Will be removed on save';
        }
    }
}

// Variant Management Functions
const productId = {{ $item->id }};
let variantOptions = [];

// Toggle variant section visibility
function toggleVariantSection() {
    const checkbox = document.getElementById('has_variants');
    const section = document.getElementById('variantSection');
    section.style.display = checkbox.checked ? 'block' : 'none';
}

// Load variant options
async function loadVariantOptions() {
    try {
        const response = await fetch('/modules/products/variant-options/all');
        const data = await response.json();
        variantOptions = data.options || [];
    } catch (error) {
        console.error('Error loading variant options:', error);
    }
}

// Show generate variants modal
async function showGenerateModal() {
    await loadVariantOptions();

    if (variantOptions.length === 0) {
        alert('No variant options available. Please create variant options first.');
        window.open('/modules/products/variant-options/create', '_blank');
        return;
    }

    const container = document.getElementById('optionsContainer');
    container.innerHTML = '';
    addOptionSelector();

    const modal = new bootstrap.Modal(document.getElementById('generateVariantsModal'));
    modal.show();
}

// Add option selector in generate modal
let optionSelectorIndex = 0;
function addOptionSelector() {
    const container = document.getElementById('optionsContainer');
    const div = document.createElement('div');
    div.className = 'option-selector mb-3';
    div.innerHTML = `
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Option Type</label>
                        <select class="form-select option-select" onchange="loadOptionValues(this, ${optionSelectorIndex})" required>
                            <option value="">Select option...</option>
                            ${variantOptions.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Values</label>
                        <select class="form-select option-values" id="option-values-${optionSelectorIndex}" multiple required disabled>
                            <option value="">Select option type first...</option>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger w-100" onclick="this.closest('.option-selector').remove(); updateVariantCount();">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.appendChild(div);
    optionSelectorIndex++;
}

// Load values for selected option
function loadOptionValues(select, index) {
    const optionId = select.value;
    const valuesSelect = document.getElementById(`option-values-${index}`);

    if (!optionId) {
        valuesSelect.disabled = true;
        valuesSelect.innerHTML = '<option value="">Select option type first...</option>';
        return;
    }

    const option = variantOptions.find(opt => opt.id == optionId);
    valuesSelect.disabled = false;
    valuesSelect.innerHTML = option.values.map(val =>
        `<option value="${val.id}">${val.value}</option>`
    ).join('');

    updateVariantCount();
}

// Update variant count preview
function updateVariantCount() {
    const selectors = document.querySelectorAll('.option-values');
    let count = 1;

    selectors.forEach(select => {
        const selectedCount = Array.from(select.selectedOptions).length;
        if (selectedCount > 0) {
            count *= selectedCount;
        }
    });

    document.getElementById('variantCount').textContent = count;
}

// Generate variants
async function generateVariants() {
    const selectors = document.querySelectorAll('.option-selector');
    const options = [];

    selectors.forEach((selector, index) => {
        const optionSelect = selector.querySelector('.option-select');
        const valuesSelect = selector.querySelector('.option-values');
        const selectedValues = Array.from(valuesSelect.selectedOptions).map(opt => opt.value);

        if (optionSelect.value && selectedValues.length > 0) {
            options.push({
                option_id: optionSelect.value,
                values: selectedValues
            });
        }
    });

    if (options.length === 0) {
        alert('Please select at least one option with values');
        return;
    }

    try {
        const response = await fetch(`/modules/products/${productId}/variants/generate-bulk`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ options })
        });

        const data = await response.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('generateVariantsModal')).hide();
            alert(data.message);
            refreshVariantsList();
        } else {
            alert(data.message || 'Error generating variants');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error generating variants');
    }
}

// Show add variant modal
async function showAddVariantModal() {
    await loadVariantOptions();

    if (variantOptions.length === 0) {
        alert('No variant options available. Please create variant options first.');
        window.open('/modules/products/variant-options/create', '_blank');
        return;
    }

    document.getElementById('variantModalTitle').textContent = 'Add Variant';
    document.getElementById('variantForm').reset();
    document.getElementById('variantId').value = '';
    document.getElementById('variantIsActive').checked = true;

    renderVariantOptions();

    const modal = new bootstrap.Modal(document.getElementById('variantModal'));
    modal.show();
}

// Render variant option selectors
function renderVariantOptions(selectedValues = []) {
    const container = document.getElementById('variantOptionsContainer');
    container.innerHTML = '';

    variantOptions.forEach(option => {
        const selected = selectedValues.filter(sv =>
            option.values.some(v => v.id == sv)
        )[0] || '';

        const div = document.createElement('div');
        div.className = 'mb-2';
        div.innerHTML = `
            <label class="form-label">${option.name}</label>
            <select class="form-select" name="option_values[]" required>
                <option value="">Select ${option.name}...</option>
                ${option.values.map(val =>
                    `<option value="${val.id}" ${val.id == selected ? 'selected' : ''}>${val.value}</option>`
                ).join('')}
            </select>
        `;
        container.appendChild(div);
    });
}

// Edit variant
async function editVariant(variantId) {
    try {
        const response = await fetch(`/modules/products/${productId}/variants/${variantId}`);
        const data = await response.json();

        if (!data.success) {
            alert('Error loading variant');
            return;
        }

        await loadVariantOptions();

        const variant = data.variant;
        document.getElementById('variantModalTitle').textContent = 'Edit Variant';
        document.getElementById('variantId').value = variant.id;
        document.getElementById('variantSku').value = variant.sku || '';
        document.getElementById('variantBarcode').value = variant.barcode || '';
        document.getElementById('variantCostPrice').value = variant.cost_price || '';
        document.getElementById('variantQuantity').value = variant.quantity_on_hand;
        document.getElementById('variantMinMargin').value = variant.minimum_profit_margin || '';
        document.getElementById('variantStdMargin').value = variant.standard_profit_margin || '';
        document.getElementById('variantReorderLevel').value = variant.reorder_level;
        document.getElementById('variantWeight').value = variant.weight || '';
        document.getElementById('variantIsDefault').checked = variant.is_default;
        document.getElementById('variantIsActive').checked = variant.is_active;

        const selectedValues = variant.option_values.map(ov => ov.id);
        renderVariantOptions(selectedValues);

        const modal = new bootstrap.Modal(document.getElementById('variantModal'));
        modal.show();
    } catch (error) {
        console.error('Error:', error);
        alert('Error loading variant');
    }
}

// Save variant
async function saveVariant() {
    const form = document.getElementById('variantForm');
    const formData = new FormData(form);

    // Get selected option values
    const optionValues = Array.from(document.querySelectorAll('#variantOptionsContainer select'))
        .map(select => select.value)
        .filter(val => val);

    if (optionValues.length === 0) {
        alert('Please select at least one option value for each option');
        return;
    }

    const variantId = document.getElementById('variantId').value;
    const url = variantId
        ? `/modules/products/${productId}/variants/${variantId}`
        : `/modules/products/${productId}/variants`;
    const method = variantId ? 'PUT' : 'POST';

    const data = {
        sku: formData.get('sku'),
        barcode: formData.get('barcode'),
        cost_price: formData.get('cost_price') || null,
        minimum_profit_margin: formData.get('minimum_profit_margin') || null,
        standard_profit_margin: formData.get('standard_profit_margin') || null,
        quantity_on_hand: formData.get('quantity_on_hand'),
        reorder_level: formData.get('reorder_level'),
        weight: formData.get('weight') || null,
        is_default: document.getElementById('variantIsDefault').checked,
        is_active: document.getElementById('variantIsActive').checked,
        option_values: optionValues
    };

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('variantModal')).hide();
            alert(result.message);
            refreshVariantsList();
        } else {
            alert(result.message || 'Error saving variant');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error saving variant');
    }
}

// Delete variant
async function deleteVariant(variantId) {
    if (!confirm('Are you sure you want to delete this variant? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/modules/products/${productId}/variants/${variantId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            refreshVariantsList();
        } else {
            alert(data.message || 'Error deleting variant');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting variant');
    }
}

// Refresh variants list without page reload
async function refreshVariantsList() {
    try {
        const response = await fetch(`/modules/products/${productId}/variants`);
        const data = await response.json();
        if (data.success) {
            // Update the variants table
            const tbody = document.getElementById('variantsTableBody');
            tbody.innerHTML = '';

            if (data.variants.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle"></i> No variants created yet.<br>
                            Use "Generate Variants" to create multiple variants at once, or "Add Single Variant" to create them one by one.
                        </td>
                    </tr>
                `;
            } else {
                data.variants.forEach(variant => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-variant-id', variant.id);
                    row.innerHTML = `
                        <td>
                            <img src="${variant.image_url || '/placeholder.svg'}"
                                 alt="${variant.variant_name}"
                                 class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                        </td>
                        <td>
                            <strong>${variant.variant_name}</strong>
                            ${variant.is_default ? '<span class="badge bg-primary ms-1">Default</span>' : ''}
                        </td>
                        <td><code>${variant.sku || ''}</code></td>
                        <td>$${number_format(variant.cost_price || 0, 2)}</td>
                        <td><strong>$${number_format(variant.target_price || 0, 2)}</strong></td>
                        <td>
                            <span class="badge ${variant.quantity_on_hand <= (variant.reorder_level || 5) ? 'bg-danger' : 'bg-success'}">
                                ${variant.quantity_on_hand}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${variant.is_active ? 'bg-success' : 'bg-secondary'}">
                                ${variant.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="editVariant(${variant.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteVariant(${variant.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            // Update the product's has_variants status if needed
            const hasVariantsCheckbox = document.getElementById('has_variants');
            if (hasVariantsCheckbox && data.variants.length > 0 && !hasVariantsCheckbox.checked) {
                hasVariantsCheckbox.checked = true;
                document.getElementById('variantSection').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error refreshing variants list:', error);
        // Fallback to page reload if there's an error
        location.reload();
    }
}

// Listen for changes on option values to update count
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('option-values')) {
        updateVariantCount();
    }
});
</script>
@endpush
@endsection
