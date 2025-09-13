@php
    /**
     * Include usage:
     * @include('form.select-model', ['var' => [ ...config... ]])
     */

    $cfg = $var ?? [];

    // Required
    $name = $cfg['name'] ?? null;

    // Basic meta
    $label = $cfg['label'] ?? null; // can contain HTML
    $tooltip = $cfg['tooltip'] ?? null; // plain text or short HTML

    // Data source
    $modelClass = $cfg['model'] ?? null; // FQCN
    $builder = $cfg['query'] ?? null; // optional Eloquent Builder
    $table = $cfg['table'] ?? null; // discouraged, present for legacy API

    // Fields
    $nameField = $cfg['name_field'] ?? 'name';
    $valueField = $cfg['value_field'] ?? 'id';
    $orderBy = $cfg['order_by'] ?? $nameField;
    $showInactive = (bool)($cfg['show_inactive'] ?? false);

    // Options helpers
    $includeNull = (bool)($cfg['null_option'] ?? false);
    $nullText = $cfg['null_option_text'] ?? '-';

    $includeZero = (bool)($cfg['zero_option'] ?? false);
    $zeroText = $cfg['zero_option_text'] ?? '-All-';

    // Selection and behavior
    $value = $cfg['value'] ?? old($name);
    $placeholder = $cfg['placeholder'] ?? null;

    // Appearance and wrappers
    $divClass = $cfg['div'] ?? null; // e.g. 'col-md-4'
    $id = $cfg['id'] ?? $name;
    $selectClass = trim($cfg['class'] ?? ('form-select ' . $name)); // Bootstrap 5 friendly
    $labelClass = $cfg['label_class'] ?? null;
    $select2 = (bool)($cfg['select2'] ?? false);

    // State
    $editable = array_key_exists('editable', $cfg) ? (bool)$cfg['editable'] : true;
    $hidden = (bool)($cfg['hidden'] ?? false);
    $dry = (bool)($cfg['dry'] ?? false);

    // Extra attributes on <select>
    $params = $cfg['params'] ?? [];

    // Per-option data-* attributes (array of field names)
    $dataAttributes = $cfg['data_attributes'] ?? [];

    // Link to model module index
    $link = (bool)($cfg['link'] ?? false);

    // Multiple support if explicitly passed via params or name[] convention
    $isMultiple = false;
    if (isset($params['multiple'])) {
        $isMultiple = filter_var($params['multiple'], FILTER_VALIDATE_BOOLEAN) ?? (bool)$params['multiple'];
    } elseif (is_string($name) && str_ends_with($name, '[]')) {
        $isMultiple = true;
    }

    // Normalize selected values for comparison
    $selectedValues = $isMultiple ? (array)($value ?? []) : [$value];
    $selectedValues = array_map(static fn($v) => is_null($v) ? null : (string)$v, $selectedValues);

    // Resolve data collection
    $collection = collect();
    try {
        if (!$dry) {
            if ($builder) {
                $q = $builder;
            } elseif ($modelClass && class_exists($modelClass)) {
                $q = $modelClass::query();
            } else {
                $q = null;
            }

            if ($q) {
                // Attempt to filter is_active when requested and column exists
                try {
                    $tableName = method_exists($q->getModel(), 'getTable') ? $q->getModel()->getTable() : ($table ?? null);
                    if (!$showInactive && $tableName && \Illuminate\Support\Facades\Schema::hasColumn($tableName, 'is_active')) {
                        $q->where($tableName . '.is_active', 1);
                    }
                } catch (Throwable $e) {
                    // silently ignore schema issues
                }

                // Select only required columns if possible
                $selectCols = array_values(array_unique(array_filter([
                    $valueField,
                    $nameField,
                    ...$dataAttributes,
                ])));
                if (!empty($selectCols)) {
                    // Avoid selecting '*' if columns exist
                    $q->select($selectCols);
                }

                // Order by
                if ($orderBy) {
                    $q->orderBy($orderBy);
                }

                $collection = $q->get();
            }
        }
    } catch (Throwable $e) {
        // Keep $collection empty on any failure
        $collection = collect();
    }

    // Helper to get label/value from a row/array
    $getVal = function ($row) use ($valueField) {
        if (is_array($row)) return $row[$valueField] ?? null;
        if (is_object($row)) return $row->{$valueField} ?? null;
        return null;
    };
    $getLabel = function ($row) use ($nameField) {
        if (is_array($row)) return $row[$nameField] ?? '';
        if (is_object($row)) return $row->{$nameField} ?? '';
        return '';
    };
@endphp

<div class="mb-3 {{ $divClass }} {{ $hidden ? 'd-none' : '' }}">
    @if($label)
        <label for="{{ $id }}" class="form-label {{ $labelClass }}">{!! $label !!}
            @if($tooltip)
                <span class="ms-1" data-bs-toggle="tooltip" title="{!! e($tooltip) !!}">
                    <i class="fas fa-circle-question text-muted"></i>
                </span>
            @endif
            @if($link && $modelClass)
                @php
                    $modelBase = class_exists($modelClass) ? class_basename($modelClass) : null;
                    $moduleKebab = $modelBase ? \Illuminate\Support\Str::kebab($modelBase) : null;
                    $routeName = $moduleKebab ? 'modules.' . $moduleKebab . '.index' : null;
                @endphp
                @if($routeName && \Illuminate\Support\Facades\Route::has($routeName))
                    <a href="{{ route($routeName) }}" class="ms-2 small text-decoration-none" target="_blank" title="Open {{ $modelBase }}">
                        <i class="fas fa-up-right-from-square"></i>
                    </a>
                @endif
            @endif
        </label>
    @endif

    @php
        $attr = '';
        foreach ($params as $k => $v) {
            if (is_bool($v)) {
                if ($v) { $attr .= ' ' . e($k); }
                continue;
            }
            $attr .= ' ' . e($k) . '="' . e($v) . '"';
        }
        $disabled = !$editable ? ' disabled' : '';
        $multiple = $isMultiple ? ' multiple' : '';
        $selectName = $name;
        if ($isMultiple && is_string($selectName) && !str_ends_with($selectName, '[]')) {
            $selectName .= '[]';
        }
    @endphp

    @php
        // If select2 requested, ensure class and helpful data attributes
        if ($select2 && !str_contains($selectClass, 'select2')) {
            $selectClass .= ' select2';
        }
        if ($select2) {
            if ($placeholder && !isset($params['data-placeholder'])) {
                // add placeholder for Select2
                $attr .= ' data-placeholder="' . e($placeholder) . '"';
            }
            if (!isset($params['data-allow-clear'])) {
                // allow clear when there is an empty option or explicitly requested
                $attr .= ' data-allow-clear="true"';
            }
        }
    @endphp

    <select id="{{ $id }}" name="{{ $selectName }}" class="{{ $selectClass }} @error($name) is-invalid @enderror"{!! $attr !!}{{ $disabled }}{{ $multiple }}>
        @if($placeholder && !$includeNull && !$includeZero)
            <option value="" disabled @if(!array_filter($selectedValues, fn($v) => $v !== null && $v !== '')) selected @endif hidden>{{ $placeholder }}</option>
        @endif

        @if($includeNull)
            <option value="" @if(in_array('', $selectedValues, true) || in_array(null, $selectedValues, true)) selected @endif>{{ $nullText }}</option>
        @endif

        @if($includeZero)
            <option value="0" @if(in_array('0', $selectedValues, true)) selected @endif>{{ $zeroText }}</option>
        @endif

        @foreach($collection as $row)
            @php
                $optValRaw = $getVal($row);
                $optVal = is_null($optValRaw) ? '' : (string)$optValRaw;
                $isSelected = in_array($optVal, $selectedValues, true);
                $dataAttrStr = '';
                foreach ($dataAttributes as $field) {
                    $dv = '';
                    if (is_array($row)) {
                        $dv = $row[$field] ?? '';
                    } elseif (is_object($row)) {
                        $dv = $row->{$field} ?? '';
                    }
                    $dataAttrStr .= ' data-' . e($field) . '="' . e($dv) . '"';
                }
            @endphp
            <option value="{{ e($optVal) }}"{!! $dataAttrStr !!} @if($isSelected) selected @endif>{{ $getLabel($row) }}</option>
        @endforeach
    </select>

    @if(!$editable)
        @if($isMultiple)
            @foreach((array)$value as $v)
                <input type="hidden" name="{{ $name }}[]" value="{{ e($v) }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $name }}" value="{{ e(is_array($value) ? ($value[0] ?? '') : ($value ?? '')) }}">
        @endif
    @endif

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
    (function(){
        const el = document.getElementById(@json($id));
        if (el) {
            // enable Bootstrap tooltip if present
            const tipTriggers = el.closest('.mb-3')?.querySelectorAll('[data-bs-toggle="tooltip"]') || [];
            tipTriggers.forEach(function (triggerEl) {
                if (window.bootstrap && bootstrap.Tooltip) {
                    new bootstrap.Tooltip(triggerEl);
                }
            });
        }
    })();
</script>
@endpush
