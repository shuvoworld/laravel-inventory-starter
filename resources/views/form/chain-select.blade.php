@php
    /**
     * Include usage:
     * @include('form.chain-select', ['var' => [
     *   'parent' => [ ... same API as form/select-model ... ,
     *       // plus (optional) below for chaining meta on the parent field
     *       'chain' => [
     *           'target' => 'child_select_id', // required: id of child select
     *           'url' => url('/api/children?parent={value}'), // or route(...), supports {value} placeholder
     *           'param' => 'parent', // optional if using query param mode
     *           'id_field' => 'id',  // default 'id'
     *           'text_field' => 'text', // default 'text'
     *           'autoload' => true, // auto-fetch if parent has a value (default true)
     *       ],
     *   ],
     *   'child' => [
     *       // You can use select-model config OR keep it empty to start blank
     *       'name' => 'child_id',
     *       'label' => 'Child',
     *       'placeholder' => '— Select Child —',
     *       'null_option' => true,
     *       'select2' => true,
     *       // Optionally prefill:
     *       // 'value' => old('child_id'),
     *   ],
     * ]])
     */

    $cfg = $var ?? [];
    $parentCfg = $cfg['parent'] ?? [];
    $childCfg  = $cfg['child']  ?? [];

    // Child defaults
    $childCfg['class'] = trim(($childCfg['class'] ?? 'form-select') . ' chain-child');
    $childPlaceholder = $childCfg['placeholder'] ?? '— Select —';
    $childId = $childCfg['id'] ?? ($childCfg['name'] ?? 'child');
    $childSelect2 = (bool)($childCfg['select2'] ?? false);

    // Parent chain meta
    $chain = $parentCfg['chain'] ?? [];
    $targetId = $chain['target'] ?? $childId;
    $url = $chain['url'] ?? null; // can be absolute or relative; supports {value}
    $param = $chain['param'] ?? 'parent';
    $idField = $chain['id_field'] ?? 'id';
    $textField = $chain['text_field'] ?? 'text';
    $autoload = array_key_exists('autoload', $chain) ? (bool)$chain['autoload'] : true;

    // Enforce Select2 class when requested
    if ($childSelect2 && (!isset($childCfg['class']) || !str_contains($childCfg['class'], 'select2'))) {
        $childCfg['class'] = trim(($childCfg['class'] ?? '') . ' select2');
    }
@endphp

<div class="row g-3">
    <div class="col-md-6">
        @include('form.select-model', ['var' => array_merge($parentCfg, [
            // Attach chaining data attributes to the parent <select>
            'params' => array_merge($parentCfg['params'] ?? [], [
                'data-chain-target' => $targetId,
                $url && !str_contains($url, '{value}') ? 'data-chain-url' : 'data-chain-url-template' => $url,
                'data-chain-param' => $param,
                'data-id-field' => $idField,
                'data-text-field' => $textField,
            ]),
        ])])
    </div>

    <div class="col-md-6">
        @php
            // Ensure placeholder and include empty
            $childCfg = array_merge([
                'null_option' => $childCfg['null_option'] ?? true,
                'placeholder' => $childPlaceholder,
            ], $childCfg);
        @endphp
        <div class="mb-3 {{ $childCfg['div'] ?? '' }}">
            @if(!empty($childCfg['label']))
                <label for="{{ $childId }}" class="form-label">{!! $childCfg['label'] !!}</label>
            @endif
            @php
                $params = $childCfg['params'] ?? [];
                if ($childSelect2) {
                    $params['data-placeholder'] = $childPlaceholder;
                    $params['data-allow-clear'] = 'true';
                }
                // Compose attribute string
                $attr = '';
                foreach ($params as $k => $v) {
                    if (is_bool($v)) { if ($v) { $attr .= ' ' . e($k); } continue; }
                    $attr .= ' ' . e($k) . '="' . e($v) . '"';
                }
                $class = trim(($childCfg['class'] ?? 'form-select') . ' ' . ($errors->has($childCfg['name'] ?? '') ? 'is-invalid' : ''));
                $name  = $childCfg['name'] ?? 'child';
                $value = $childCfg['value'] ?? old($name);
                $includeNull = $childCfg['null_option'] ?? true;
                $zeroOpt = $childCfg['zero_option'] ?? false;
                $zeroText = $childCfg['zero_option_text'] ?? '-All-';
            @endphp
            <select id="{{ $childId }}" name="{{ $name }}" class="{{ $class }}" data-placeholder="{{ $childPlaceholder }}" data-include-empty{{ $attr }}>
                @if($includeNull)
                    <option value="">{{ $childPlaceholder }}</option>
                @endif
                @if($zeroOpt)
                    <option value="0" @selected((string)$value === '0')>{{ $zeroText }}</option>
                @endif
                @if(!empty($value) && !in_array((string)$value, ['', '0'], true))
                    {{-- If there is a pre-selected value but no options yet, keep a temporary option so the value posts correctly; JS will replace it on load --}}
                    <option value="{{ e($value) }}" selected>Loading...</option>
                @endif
            </select>
            @error($name)
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        // kick Select2 if present
        if (window.initSelect2) { window.initSelect2(); }
        if (window.initChainSelects) { window.initChainSelects(); }
    })();
</script>
@endpush
