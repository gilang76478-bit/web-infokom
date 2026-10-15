@extends('layouts.admin', [
    'title' => ($record ? 'Edit' : 'Tambah') . ' ' . $definition['label'],
    'backLink' => route('admin.content.index', $type),
    'backLabel' => $definition['label'],
    'formArea' => 'admin-form-area',
])

@section('content')
  <div class="admin-page-heading"><div><p class="admin-eyebrow">Pengelolaan konten</p><h1>{{ $record ? 'Edit' : 'Tambah' }} {{ $definition['label'] }}</h1><p>Lengkapi informasi untuk ditampilkan di website.</p></div></div>
  <form class="admin-content-form" method="post" action="{{ $record ? route('admin.content.update', [$type, $record->id]) : route('admin.content.store', $type) }}" data-admin-form>
    @csrf
    @if ($record) @method('put') @endif
    @foreach ($definition['fields'] as $key => $field)
      @php
        $value = old($key, $record?->{$key});
        if ($field['type'] === 'lines' && is_array($value)) $value = implode("\n", $value);
        if ($field['type'] === 'date' && $value instanceof \DateTimeInterface) $value = $value->format('Y-m-d');
        $required = ! collect((array) $field['rules'])->contains(fn ($rule) => is_string($rule) && str_starts_with($rule, 'nullable'));
      @endphp
      <label for="field-{{ $key }}">{{ $field['label'] }}</label>
      @if ($field['type'] === 'textarea' || $field['type'] === 'lines')
        <textarea id="field-{{ $key }}" name="{{ $key }}" rows="{{ $field['type'] === 'lines' ? 5 : 4 }}" @if ($required) required @endif>{{ $value }}</textarea>
      @elseif ($field['type'] === 'select')
        <select id="field-{{ $key }}" name="{{ $key }}" @if ($required) required @endif>
          <option value="">Tanpa {{ strtolower($field['label']) }}</option>
          @foreach (\App\Support\ContentTypes::options($field['options'] ?? '') as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) ($value ?? '') === (string) $optionValue)>{{ $optionLabel }}</option>
          @endforeach
        </select>
      @else
        <input id="field-{{ $key }}" name="{{ $key }}" type="{{ $field['type'] }}" value="{{ $value }}" @if ($required) required @endif>
      @endif
      @error($key)<span class="admin-field-error">{{ $message }}</span>@enderror
    @endforeach
    <div class="admin-form-actions"><a class="admin-secondary-link" href="{{ route('admin.content.index', $type) }}">Batal</a><button class="admin-primary-link" type="submit">Simpan perubahan <span aria-hidden="true">→</span></button></div>
  </form>
@endsection
