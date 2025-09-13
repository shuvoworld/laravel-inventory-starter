@props([
    'name',
    'label' => null,
    'options' => null, // array|Collection of [value => label] or list of models
    'model' => null, // FQCN or class-string to auto-fetch options
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'selected' => null, // value|string|array
    'placeholder' => '— Select —',
    'includeEmpty' => true,
    'multiple' => false,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'class' => 'form-select',
    'size' => null,
    'select2' => false,
])

@php
    // Resolve options: allow passing a collection/array or fetch from model
    $resolvedOptions = collect();
    if ($options instanceof \Illuminate\Support\Collection) {
        $resolvedOptions = $options;
    } elseif (is_array($options)) {
        $resolvedOptions = collect($options);
    } elseif ($options === null && $model) {
        try {
            $query = $model::query();
            // Avoid selecting * if custom columns desired
            $items = $query->orderBy($optionLabel)->get([$optionValue, $optionLabel]);
            $resolvedOptions = $items->pluck($optionLabel, $optionValue);
        } catch (\Throwable $e) {
            // Fallback to empty if model query fails
            $resolvedOptions = collect();
        }
    }

    // Determine current value(s): prefer old() over provided selected
    $isMultiple = (bool) $multiple;
    $oldValue = old($name, $selected);
    if ($isMultiple) {
        $currentValues = collect($oldValue ?? [])->map(fn($v) => (string) $v)->all();
    } else {
        $currentValue = is_array($oldValue) ? (string) ($oldValue[0] ?? '') : (string) ($oldValue ?? '');
    }

    $id = $attributes->get('id') ?: $name;
    // Add .select2 class if requested
    if ($select2) {
        $class = trim($class . ' select2');
    }
    $classes = trim($class . ' ' . ($errors->has($name) ? 'is-invalid' : ''));
@endphp

@if($label)
    <label for="{{ $id }}" class="form-label">{{ $label }}@if($required)<span class="text-danger"> *</span>@endif</label>
@endif

<select
    id="{{ $id }}"
    name="{{ $name }}{{ $isMultiple ? '[]' : '' }}"
    @class([$classes])
    @if($isMultiple) multiple @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    @if($size) size="{{ $size }}" @endif
>
    @if($includeEmpty && !$isMultiple)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach($resolvedOptions as $value => $text)
        @php($valueStr = (string) $value)
        <option value="{{ $value }}"
            @if($isMultiple)
                @selected(in_array($valueStr, $currentValues, true))
            @else
                @selected($valueStr === $currentValue)
            @endif
        >{{ $text }}</option>
    @endforeach
</select>

@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror

@if($help)
    <div class="form-text">{{ $help }}</div>
@endif
