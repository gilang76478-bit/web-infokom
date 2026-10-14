@extends('layouts.admin', [
    'title' => 'Pesan masuk',
    'backLink' => route('filament.admin.pages.dashboard'),
    'backLabel' => 'Dashboard',
])

@section('content')
  <div class="admin-page-heading"><div><p class="admin-eyebrow">Formulir hubungi kami</p><h1>Pesan masuk</h1><p>{{ $messages->total() }} pesan · {{ $unreadCount }} belum dibaca</p></div></div>
  @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
  <section class="admin-data-panel"><div class="admin-panel-heading"><div><h2>Inbox sekolah</h2><p>Pesan dari formulir kontak website publik.</p></div><span class="admin-result-count" data-total="{{ $messages->total() }}" data-page-count="{{ $messages->count() }}">{{ $messages->count() }} dari {{ $messages->total() }}</span></div>
    <div class="admin-table-wrap"><table class="admin-table admin-message-table"><thead><tr><th>Status</th><th>Pengirim</th><th>Kontak</th><th>Subjek</th><th>Pesan</th><th>Diterima</th><th>Aksi</th></tr></thead><tbody>
      @forelse ($messages as $message)
        <tr data-search-item @class(['admin-message-unread' => ! $message->is_read])>
          <td><span class="admin-message-status {{ $message->is_read ? 'is-read' : 'is-unread' }}">{{ $message->is_read ? 'Dibaca' : 'Baru' }}</span></td>
          <td><b>{{ $message->name }}</b></td>
          <td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a><a href="https://wa.me/{{ preg_replace('/\D+/', '', $message->whatsapp) }}" target="_blank" rel="noopener">{{ $message->whatsapp }}</a></td>
          <td>{{ $message->subject }}</td>
          <td class="admin-message-body">{{ $message->message }}</td>
          <td>{{ $message->created_at->locale('id')->translatedFormat('j M Y, H:i') }}</td>
          <td>@if (! $message->is_read)<form method="post" action="{{ route('admin.messages.read', $message) }}">@csrf @method('patch')<button class="admin-small-button" type="submit">Tandai dibaca</button></form>@else<span class="admin-muted">Selesai</span>@endif</td>
        </tr>
      @empty
        <tr><td colspan="7" class="admin-empty">Belum ada pesan masuk.</td></tr>
      @endforelse
      <tr class="admin-no-results" hidden><td colspan="7">Tidak ada pesan yang cocok dengan pencarian.</td></tr>
    </tbody></table></div>
    <div class="admin-pagination">{{ $messages->links() }}</div>
  </section>
@endsection
