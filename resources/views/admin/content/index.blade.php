@extends('layouts.admin', [
    'title' => $definition['label'],
    'backLink' => route('filament.admin.pages.dashboard'),
    'backLabel' => 'Dashboard',
])

@section('content')
  <div class="admin-page-heading"><div><p class="admin-eyebrow">Pengelolaan konten</p><h1>{{ $definition['label'] }}</h1><p>{{ $records->total() }} data tersimpan</p></div><a class="admin-primary-link" href="{{ route('admin.content.create', $type) }}"><span aria-hidden="true">＋</span> Tambah data</a></div>
  @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
  <section class="admin-data-panel"><div class="admin-panel-heading"><div><h2>Daftar {{ strtolower($definition['label']) }}</h2><p>Kelola data yang tampil di website publik.</p></div><span class="admin-result-count" data-total="{{ $records->total() }}" data-page-count="{{ $records->count() }}">{{ $records->count() }} dari {{ $records->total() }}</span></div>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr>@foreach ($definition['fields'] as $field)<th>{{ $field['label'] }}</th>@endforeach<th>Aksi</th></tr></thead><tbody>
      @forelse ($records as $record)
        <tr data-search-item>@foreach ($definition['fields'] as $key => $field)<td>{{ $field['type'] === 'select' ? \App\Support\ContentTypes::optionLabel($field['options'] ?? '', $record->{$key}) : (is_array($record->{$key}) ? implode(', ', $record->{$key}) : $record->{$key}) }}</td>@endforeach<td class="admin-actions"><a href="{{ route('admin.content.edit', [$type, $record->id]) }}">Edit</a><form method="post" action="{{ route('admin.content.destroy', [$type, $record->id]) }}" data-confirm-delete data-confirm-message="Hapus data ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('delete')<button type="submit">Hapus</button></form></td></tr>
      @empty
        <tr><td colspan="{{ count($definition['fields']) + 1 }}" class="admin-empty">Belum ada data. Tambahkan data pertama untuk ditampilkan di website.</td></tr>
      @endforelse
      <tr class="admin-no-results" hidden><td colspan="{{ count($definition['fields']) + 1 }}">Tidak ada data yang cocok dengan pencarian.</td></tr>
    </tbody></table></div>
    <div class="admin-pagination">{{ $records->links() }}</div>
  </section>
@endsection
