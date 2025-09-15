@props([
    // The audited Eloquent model instance
    'model',
    // Optional title override
    'title' => 'Recent Changes',
    // Max items to show (default 10)
    'limit' => 10,
])

@php
    $audits = collect();
    $supportsAudits = false;
    try {
        // Check if model has audits() relation
        if ($model && method_exists($model, 'audits')) {
            $supportsAudits = true;
            $audits = $model->audits()
                ->latest()
                ->limit((int) $limit)
                ->get();
        }
    } catch (\Throwable $e) {
        $audits = collect();
    }

    // Helper to format changes in a readable way: "field: old → new"
    $formatChanges = function ($old, $new) {
        $old = is_array($old) ? $old : (array) ($old ?? []);
        $new = is_array($new) ? $new : (array) ($new ?? []);
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $lines = [];
        foreach ($keys as $key) {
            $ov = array_key_exists($key, $old) ? $old[$key] : null;
            $nv = array_key_exists($key, $new) ? $new[$key] : null;
            if ($ov === $nv) {
                // Skip unchanged
                continue;
            }
            // Normalize scalars to strings for display
            $ovDisp = is_scalar($ov) || is_null($ov) ? (is_null($ov) ? 'null' : (string) $ov) : json_encode($ov);
            $nvDisp = is_scalar($nv) || is_null($nv) ? (is_null($nv) ? 'null' : (string) $nv) : json_encode($nv);
            $lines[] = e($key) . ': ' . e($ovDisp) . ' 	→ ' . e($nvDisp);
        }
        if (empty($lines)) {
            return '<span class="text-muted">No changes</span>';
        }
        // Join with <br>
        return implode('<br>', $lines);
    };
@endphp

<div class="card mt-3">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <h3 class="h6 mb-0">{{ $title }}</h3>
        <span class="badge bg-light text-secondary">{{ __('Last') }} {{ (int) $limit }}</span>
    </div>
    <div class="card-body">
        @if(!$supportsAudits)
            <div class="text-muted small">Auditing is not enabled for this model.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable-minimal table-sm w-100" id="audit-table-{{ $model->getTable() }}-{{ $model->getKey() }}">
                    <thead>
                        <tr>
                            <th>{{ __('When') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Event') }}</th>
                            <th>{{ __('Changes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($audits as $audit)
                            @php
                                $userName = optional($audit->user)->name ?? '—';
                                $when = optional($audit->created_at)?->toDateTimeString();
                                $event = $audit->event ?? '';
                                // Old vs New values via OwenIt\Auditing fields
                                $oldValues = (array) ($audit->old_values ?? []);
                                $newValues = (array) ($audit->new_values ?? []);
                            @endphp
                            <tr>
                                <td>{{ $when }}</td>
                                <td>{{ $userName }}</td>
                                <td class="text-capitalize">{{ $event }}</td>
                                <td>{!! $formatChanges($oldValues, $newValues) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function(){
                    const sel = '#audit-table-{{ $model->getTable() }}-{{ $model->getKey() }}';
                    if (document.querySelector(sel)) {
                        new DataTable(sel, {
                            serverSide: false,
                            processing: false,
                            order: [[0, 'desc']],
                            pageLength: 10,
                            lengthChange: false,
                            searching: false,
                            pagingType: 'simple_numbers',
                            layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' }
                        });
                    }
                });
            </script>
            @endpush
        @endif
    </div>
</div>
