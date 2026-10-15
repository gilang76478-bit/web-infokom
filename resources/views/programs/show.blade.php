@extends('layouts.site', [
    'activeSection' => 'akademik',
    'title' => $program->name . ' | Program Keahlian SMK INFOKOM',
    'description' => \Illuminate\Support\Str::limit(strip_tags($program->description), 160),
])

@section('content')
  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">Program keahlian</p>
      <span class="detail-tag">{{ $program->short }}</span>
      <h1>{{ $program->name }}</h1>
      <p>{{ $program->description }}</p>
      <div class="program-actions">
        <a class="btn btn-accent" href="{{ route('site.section', 'ppdb') }}">Daftar PPDB</a>
        <a class="btn btn-brand" href="{{ route('site.section', 'akademik') }}">Semua program keahlian</a>
      </div>
    </div>
  </section>

  <section class="sec detail-content">
    <div class="container">
      <div class="detail-grid academic-grid">
        <article class="detail-card academic-card">
          <span class="detail-tag">KOMPETENSI</span>
          <h2>Kompetensi yang dikuasai</h2>
          <p>Lulusan program ini dilatih pada keterampilan berikut.</p>
          <ul>
            @foreach ($program->skills as $skill)
              <li>{{ $skill }}</li>
            @endforeach
          </ul>
        </article>

        <article class="detail-card academic-card">
          <span class="detail-tag">FASILITAS</span>
          <h2>Fasilitas pendukung belajar</h2>
          <p>Ruang belajar dan perangkat yang dipakai selama pembelajaran.</p>
          <ul>
            @foreach ($program->facilities as $facility)
              <li>{{ $facility }}</li>
            @endforeach
          </ul>
        </article>

        <article class="detail-card academic-card">
          <span class="detail-tag">PELUANG KERJA</span>
          <h2>Peluang karier setelah lulus</h2>
          <p>Pilihan jalur kerja dan lanjutan studi bagi lulusan program ini.</p>
          <ul>
            @foreach ($program->jobs as $job)
              <li>{{ $job }}</li>
            @endforeach
          </ul>
        </article>

        <article class="detail-card academic-card">
          <span class="detail-tag">TENTANG PROGRAM</span>
          <h2>Mengapa memilih {{ $program->short }}?</h2>
          <p>{{ $program->description }}</p>
          <p>Pembelajaran menggabungkan teori di kelas dengan praktik langsung, proyek nyata, dan pengalaman industri agar keterampilan siswa relevan dengan kebutuhan dunia kerja.</p>
        </article>
      </div>

      <section class="program-teachers" aria-labelledby="programTeachersTitle" id="guru">
        <div class="sh-row">
          <div class="sh">
            <span class="detail-eyebrow">PENDIDIK</span>
            <h2 id="programTeachersTitle">Guru Program Keahlian Ini</h2>
            <p>Tim pengajar yang mendampingi siswa selama belajar di program {{ $program->short }}.</p>
          </div>
        </div>
        @if ($program->teachers->isNotEmpty())
          <div class="org-tree">
            <div class="org-level">
              @foreach ($program->teachers as $teacher)
                <article class="org-card">
                  <div class="photo">
                    @if (filled($teacher->photo))
                      <img src="{{ $teacher->photo }}" alt="Foto {{ $teacher->name }}" loading="lazy">
                    @else
                      <div class="photo-placeholder">{{ $teacher->initials }}</div>
                    @endif
                  </div>
                  <div class="role">{{ $teacher->position }}</div>
                  <div class="name">{{ $teacher->name }}</div>
                  <div class="org-card-meta">
                    @if (filled($teacher->nip))<span>{{ $teacher->nip }}</span>@endif
                    @if ($teacher->program?->short)<span>{{ $teacher->program->short }}</span>@endif
                  </div>
                </article>
              @endforeach
            </div>
          </div>
        @else
          <p class="detail-empty">Data guru program ini akan segera tersedia.</p>
        @endif
      </section>
    </div>
  </section>
@endsection
