# Reusable Form Components

This project includes two forms-related building blocks: a Blade component `<x-form.select>` and an include-based helper `form/select-model`.

## Blade Component: x-form.select
Location: `resources/views/components/form/select.blade.php`

Props:
- `name` (string, required)
- `label` (string)
- `options` (array|Collection) — key/value pairs of value => label
- `model` (FQCN|string) — alternatively provide a model class; the component will pluck `optionLabel => optionValue`
- `optionValue` (string, default: `id`)
- `optionLabel` (string, default: `name`)
- `selected` (mixed) — current value; `old(name)` is used by default
- `placeholder` (string, default: `— Select —`)
- `includeEmpty` (bool, default: `true`)
- `multiple` (bool, default: `false`)
- `required`, `disabled`, `readonly` (bool)
- `class` (string, default: `form-select`)
- `help` (string) — helper text below the field
- `select2` (bool, default: `false`) — add Select2 enhancement

Examples:
```blade
<x-form.select
  name="role"
  label="Role"
  :model="Spatie\Permission\Models\Role::class"
  optionValue="name"
  optionLabel="name"
  placeholder="— No Role —"
  :includeEmpty="true"
  :select2="true"
/>
```

## Include Helper: form/select-model
Location: `resources/views/form/select-model.blade.php`

Usage:
```blade
@include('form.select-model', ['var' => [
  'name' => 'site_id',
  'label' => 'Site',
  'model' => \App\Site::class,
  'null_option' => true,
  'null_option_text' => 'Please select',
  'select2' => true,
]])
```

Supported options include: `model`, `query`, `name_field`, `value_field`, `order_by`, `show_inactive`, `null_option`, `zero_option`, `placeholder`, `editable`, `hidden`, `params` (HTML attributes), `data_attributes` (copy fields as data-*), `link` (open related module), and `dry` (skip DB query).

## Select2 Compatibility
Select2 assets are preloaded globally (jQuery + Select2 + Bootstrap 5 theme). Any `<select>` with the class `select2` is auto-initialized with placeholders and clear buttons.

- Component: pass `:select2="true"`
- Include: pass `'select2' => true`

Notes:
- Placeholder text comes from the component `placeholder` or include's `placeholder`.
- `allowClear` is enabled automatically when an empty option is present or when `select2` is used.
