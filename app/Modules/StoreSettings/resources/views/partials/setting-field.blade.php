@php
    $fieldName = "settings[{$setting['key']}]";
    $currentValue = old($fieldName, $setting['raw_value']);
@endphp

<div class="form-group">
    <label for="{{ $setting['key'] }}">
        {{ $setting['label'] }}
        @if($setting['description'])
            <i class="fas fa-info-circle text-muted" data-toggle="tooltip" title="{{ $setting['description'] }}"></i>
        @endif
    </label>

    @switch($setting['type'])
        @case('textarea')
            <textarea name="{{ $fieldName }}"
                      id="{{ $setting['key'] }}"
                      class="form-control"
                      rows="3"
                      placeholder="{{ $setting['description'] }}">{{ $currentValue }}</textarea>
            @break

        @case('boolean')
            <div class="custom-control custom-switch">
                <input type="hidden" name="{{ $fieldName }}" value="0">
                <input type="checkbox"
                       class="custom-control-input"
                       id="{{ $setting['key'] }}"
                       name="{{ $fieldName }}"
                       value="1"
                       {{ $setting['value'] ? 'checked' : '' }}>
                <label class="custom-control-label" for="{{ $setting['key'] }}">
                    {{ $setting['description'] }}
                </label>
            </div>
            @break

        @case('select')
            @if(isset($setting['options']['options']))
                <select name="{{ $fieldName }}"
                        id="{{ $setting['key'] }}"
                        class="form-control select2">
                    @foreach($setting['options']['options'] as $value => $label)
                        <option value="{{ $value }}" {{ $currentValue == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="text"
                       name="{{ $fieldName }}"
                       id="{{ $setting['key'] }}"
                       class="form-control"
                       value="{{ $currentValue }}"
                       placeholder="{{ $setting['description'] }}">
            @endif
            @break

        @case('integer')
            <input type="number"
                   name="{{ $fieldName }}"
                   id="{{ $setting['key'] }}"
                   class="form-control"
                   value="{{ $currentValue }}"
                   @if(isset($setting['options']['min'])) min="{{ $setting['options']['min'] }}" @endif
                   @if(isset($setting['options']['max'])) max="{{ $setting['options']['max'] }}" @endif
                   placeholder="{{ $setting['description'] }}">
            @break

        @case('decimal')
        @case('float')
            <input type="number"
                   name="{{ $fieldName }}"
                   id="{{ $setting['key'] }}"
                   class="form-control"
                   value="{{ $currentValue }}"
                   step="0.01"
                   @if(isset($setting['options']['min'])) min="{{ $setting['options']['min'] }}" @endif
                   @if(isset($setting['options']['max'])) max="{{ $setting['options']['max'] }}" @endif
                   @if(isset($setting['options']['step'])) step="{{ $setting['options']['step'] }}" @endif
                   placeholder="{{ $setting['description'] }}">
            @break

        @case('email')
            <input type="email"
                   name="{{ $fieldName }}"
                   id="{{ $setting['key'] }}"
                   class="form-control"
                   value="{{ $currentValue }}"
                   placeholder="{{ $setting['description'] }}">
            @break

        @case('url')
            <input type="url"
                   name="{{ $fieldName }}"
                   id="{{ $setting['key'] }}"
                   class="form-control"
                   value="{{ $currentValue }}"
                   placeholder="{{ $setting['description'] }}">
            @break

        @case('file')
            <div class="custom-file">
                <input type="file"
                       name="{{ $fieldName }}"
                       id="{{ $setting['key'] }}"
                       class="custom-file-input"
                       @if(isset($setting['options']['accept'])) accept="{{ $setting['options']['accept'] }}" @endif>
                <label class="custom-file-label" for="{{ $setting['key'] }}">Choose file...</label>
            </div>
            @if($setting['value'])
                <div class="mt-2">
                    <small class="text-muted">Current file:</small>
                    @if(isset($setting['options']['accept']) && str_contains($setting['options']['accept'], 'image/'))
                        <div class="mt-1">
                            <img src="{{ $setting['value'] }}" alt="{{ $setting['label'] }}" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    @else
                        <div class="mt-1">
                            <a href="{{ $setting['value'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> View Current File
                            </a>
                        </div>
                    @endif
                </div>
            @endif
            <div class="file-preview"></div>
            @break

        @case('json')
            <textarea name="{{ $fieldName }}"
                      id="{{ $setting['key'] }}"
                      class="form-control font-monospace"
                      rows="4"
                      placeholder="{{ $setting['description'] }}">{{ is_array($setting['value']) ? json_encode($setting['value'], JSON_PRETTY_PRINT) : $currentValue }}</textarea>
            <small class="form-text text-muted">Enter valid JSON format</small>
            @break

        @default
            <input type="text"
                   name="{{ $fieldName }}"
                   id="{{ $setting['key'] }}"
                   class="form-control"
                   value="{{ $currentValue }}"
                   placeholder="{{ $setting['description'] }}">
    @endswitch

    @if($setting['description'] && !in_array($setting['type'], ['boolean']))
        <small class="form-text text-muted">{{ $setting['description'] }}</small>
    @endif
</div>