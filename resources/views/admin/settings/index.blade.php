@extends('layouts.admin', [
    'title' => 'Pengaturan sekolah',
    'backLink' => route('filament.admin.pages.dashboard'),
    'backLabel' => 'Dashboard',
    'formArea' => 'admin-form-area',
])

@section('content')
  <div class="admin-page-heading">
    <div>
      <p class="admin-eyebrow">Pengaturan website</p>
      <h1>Profil sekolah & kontak</h1>
      <p>Ubah data yang sering diperbarui tanpa menyentuh coding.</p>
    </div>
  </div>

  @if (session('status'))
    <p class="admin-status" role="status">{{ session('status') }}</p>
  @endif

  <form class="admin-content-form" method="post" action="{{ route('admin.settings.update') }}" data-admin-form>
    @csrf
    @method('put')

    @php
      $sections = [
        'Identitas sekolah' => ['school_name', 'school_address_line', 'school_address_rtrw', 'school_address_village', 'school_address_district', 'school_address_city', 'school_address_province', 'school_address_postal_code'],
        'Kontak & WhatsApp' => ['school_phone_general', 'school_phone_admin', 'school_phone_ppdb', 'school_phone_industry', 'school_phone_alumni', 'school_whatsapp_general', 'school_whatsapp_ppdb', 'school_email_general', 'school_email_ppdb', 'school_email_admin', 'school_email_industry', 'school_email_alumni'],
        'Lokasi & jam operasional' => ['school_maps_url', 'school_hours'],
        'Media sosial' => ['school_instagram_url', 'school_tiktok_url', 'school_facebook_url', 'school_youtube_url', 'school_other_social_url'],
        'Penerimaan murid baru (SPMB)' => ['ppdb_status', 'ppdb_period', 'ppdb_requirements', 'ppdb_steps', 'ppdb_faq'],
      ];
    @endphp

    @foreach ($sections as $title => $keys)
      <section class="admin-settings-group">
        <div class="admin-settings-group-head">
          <div>
            <p class="admin-eyebrow">Bagian</p>
            <h2>{{ $title }}</h2>
          </div>
        </div>
        <div class="admin-settings-grid">
          @foreach ($keys as $key)
            @php
              $field = $fields[$key];
              $value = old($key, $settings[$key] ?? '');
              $required = ! str_contains(($field['rules'] ?? ''), 'nullable');
            @endphp
            <div class="admin-setting-field">
              <label for="field-{{ $key }}">{{ $field['label'] }}</label>
              @if (str_contains(($field['rules'] ?? ''), 'url') || str_contains(($field['rules'] ?? ''), 'email'))
                <input id="field-{{ $key }}" name="{{ $key }}" type="{{ str_contains(($field['rules'] ?? ''), 'email') ? 'email' : 'url' }}" value="{{ $value }}" placeholder="{{ str_contains(($field['rules'] ?? ''), 'email') ? 'nama@sekolah.sch.id' : 'https://...' }}" @if($required) required @endif>
              @elseif (($field['type'] ?? null) === 'textarea' || str_contains(($field['rules'] ?? ''), 'textarea'))
                <textarea id="field-{{ $key }}" name="{{ $key }}" rows="4" @if($required) required @endif>{{ $value }}</textarea>
              @else
                <input id="field-{{ $key }}" name="{{ $key }}" type="text" value="{{ $value }}" placeholder="Masukkan {{ strtolower($field['label']) }}" @if($required) required @endif>
              @endif
              @error($key)<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
          @endforeach
        </div>
      </section>
    @endforeach

    <div class="admin-form-actions">
      <a class="admin-secondary-link" href="{{ route('filament.admin.pages.dashboard') }}">Batal</a>
      <button class="admin-primary-link" type="submit">Simpan pengaturan <span aria-hidden="true">→</span></button>
    </div>
  </form>
@endsection
