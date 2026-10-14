@extends('layouts.admin', [
    'title' => 'Pendaftar PPDB',
    'backLink' => route('filament.admin.pages.dashboard'),
    'backLabel' => 'Dashboard',
])

@section('content')
  <div class="admin-page-heading"><div><p class="admin-eyebrow">Penerimaan murid baru</p><h1>Pendaftar PPDB</h1><p>{{ $applicants->total() }} pendaftar tercatat</p></div></div>
  @if (session('status'))<p class="admin-status" role="status">{{ session('status') }}</p>@endif
  <section class="admin-data-panel"><div class="admin-panel-heading"><div><h2>Daftar pendaftar</h2><p>Periksa data dan perbarui status seleksi.</p></div><span class="admin-result-count" data-total="{{ $applicants->total() }}" data-page-count="{{ $applicants->count() }}">{{ $applicants->count() }} dari {{ $applicants->total() }}</span></div>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Nomor</th><th>Nama</th><th>Telepon</th><th>Asal sekolah</th><th>Program</th><th>Status</th></tr></thead><tbody>
      @forelse ($applicants as $applicant)
        <tr data-search-item><td>{{ $applicant->registration_number }}</td><td>{{ $applicant->full_name }}</td><td>{{ $applicant->phone }}</td><td>{{ $applicant->origin_school }}</td><td>{{ $applicant->program }}</td><td><form class="admin-status-form" method="post" action="{{ route('admin.ppdb.update', $applicant) }}">@csrf @method('patch')<select name="status" aria-label="Status {{ $applicant->registration_number }}"><option @selected($applicant->status === 'Menunggu Verifikasi')>Menunggu Verifikasi</option><option @selected($applicant->status === 'Terverifikasi')>Terverifikasi</option><option @selected($applicant->status === 'Perlu Perbaikan')>Perlu Perbaikan</option></select><button class="admin-small-button" type="submit">Simpan</button></form></td></tr>
      @empty
        <tr><td colspan="6" class="admin-empty">Belum ada pendaftar masuk.</td></tr>
      @endforelse
      <tr class="admin-no-results" hidden><td colspan="6">Tidak ada data yang cocok dengan pencarian.</td></tr>
    </tbody></table></div>
    <div class="admin-pagination">{{ $applicants->links() }}</div>
  </section>
@endsection
