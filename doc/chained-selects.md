# Chained Selects (Reusable)

A small utility is included to build chained (dependent) dropdowns that fetch child options when the parent value changes. It works with plain Bootstrap selects and with Select2.

## How It Works
- The parent select declares where to send an AJAX request and which target select to populate using data attributes.
- The endpoint should return either an array of objects like `[{ id, text }, ...]` or a key/value map `{ value: label, ... }`. The utility also supports `{ data: [...] }` envelopes.

## Helper Include
Use the reusable include to render a pair of parent/child fields quickly:

```blade
@include('form.chain-select', ['var' => [
  'parent' => [
    'name' => 'country_id',
    'label' => 'Country',
    'model' => \App\Models\Country::class,
    'null_option' => true,
    'select2' => true,
    'chain' => [
      'target' => 'state_id',
      'url' => url('/api/states?country={value}'),
      'param' => 'country',
      'id_field' => 'id',
      'text_field' => 'name',
    ],
  ],
  'child' => [
    'name' => 'state_id',
    'id' => 'state_id',
    'label' => 'State/Province',
    'placeholder' => '— Select State —',
    'null_option' => true,
    'select2' => true,
  ],
]])
```

## Manual Wiring (Parent Only)
If you want to wire it manually, add these attributes to the parent select:

```html
<select
  id="country_id"
  name="country_id"
  class="form-select select2"
  data-chain-target="state_id"
  data-chain-url="/api/states"         
  data-chain-param="country"           
  data-id-field="id"
  data-text-field="name"
>
  ...
</select>
```

The child select should exist with the given id (`state_id`) and may be empty initially. Add `data-placeholder` and `data-include-empty` to control UX:

```html
<select id="state_id" name="state_id" class="form-select select2" data-placeholder="— Select State —" data-include-empty></select>
```

## Notes
- Endpoint response formats supported:
  - Array: `[{ id: 1, text: "Alaska" }, ...]`
  - Map: `{ "1": "Alaska", "2": "Arizona" }`
  - Wrapped: `{ data: [...] } | { items: [...] } | { options: [...] }`
- If using Select2, the utility triggers `change()` after repopulating.
- If the parent has a pre-selected value, the utility auto-loads the child on page load.
- You can pass a preselected child value to the include via `'child' => ['value' => old('state_id', 5)]`. A temporary option will be rendered until the AJAX fills real options.
- Security: The fetch uses GET and sends `X-Requested-With=XMLHttpRequest`. If your endpoint requires auth, keep it under `auth` middleware.

## Backend Example (Laravel)
```php
Route::get('/api/states', function (Illuminate\Http\Request $request) {
    $countryId = $request->get('country');
    $states = \App\Models\State::where('country_id', $countryId)
        ->orderBy('name')
        ->get(['id','name']);
    return $states->map(fn($s) => ['id' => $s->id, 'text' => $s->name]);
});
```
