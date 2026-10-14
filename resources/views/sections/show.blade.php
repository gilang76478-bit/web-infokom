@extends('layouts.site', [
    'activeSection' => $section,
    'title' => $pages[$section]['title'] . ' | SMK INFOKOM',
    'description' => $pages[$section]['intro'],
])

@section('content')
  <section class="detail-hero">
    <div class="container">
      <p class="detail-eyebrow">{{ $pages[$section]['eyebrow'] }}</p>
      <h1>{{ $pages[$section]['title'] }}</h1>
      <p>{{ $pages[$section]['intro'] }}</p>
      @if ($section === 'profil')
        <nav class="profile-hero-nav" aria-label="Navigasi cepat profil sekolah">
          <a href="#tentang">Tentang sekolah <span aria-hidden="true">↓</span></a>
          <a href="#visi-misi">Visi &amp; misi <span aria-hidden="true">↓</span></a>
          <a href="#sejarah">Sejarah <span aria-hidden="true">↓</span></a>
          <a href="#struktur-organisasi">Struktur <span aria-hidden="true">↓</span></a>
          <a href="#fasilitas">Fasilitas <span aria-hidden="true">↓</span></a>
        </nav>
      @endif
    </div>
  </section>

  <section @class(['sec', 'detail-content', 'detail-content-profile' => $section === 'profil'])>
    <div class="container">
      @if ($section === 'profil')
        {{-- 2. SAMBUTAN KEPALA SEKOLAH --}}
        <section class="sec" aria-labelledby="sambutanTitle" id="sambutan">
          <div class="container">
            <div class="kepala-sekolah-shell">
              <div class="kepala-sekolah-copy">
                <span class="detail-tag">SAMBUTAN KEPALA SEKOLAH</span>
                <h2 id="sambutanTitle">Selamat Datang di SMK INFOKOM</h2>
                <div class="kepala-sekolah-text">
                  <blockquote>
                    <p>Assalamu'alaikum Warahmatullahi Wabarakatuh.</p>
                    <p>Selamat datang di website resmi SMK INFOKOM. Website ini kami hadirkan sebagai sarana informasi dan komunikasi bagi siswa, orang tua, calon peserta didik, alumni, dunia industri, serta masyarakat.</p>
                    <p>SMK INFOKOM berkomitmen menyelenggarakan pendidikan kejuruan yang relevan dengan perkembangan teknologi dan kebutuhan dunia kerja. Kami mendorong setiap peserta didik untuk tidak hanya menguasai pengetahuan, tetapi juga memiliki keterampilan, karakter, kreativitas, dan kesiapan menghadapi dunia industri.</p>
                    <p>Semoga website ini dapat memberikan informasi yang bermanfaat mengenai SMK INFOKOM dan menjadi jembatan untuk mengenal sekolah kami lebih dekat.</p>
                  </blockquote>
                  <div class="kepala-sekolah-cardata">
                    <strong>Kepala Sekolah</strong>
                    <span>Kepala SMK INFOKOM</span>
                  </div>
                </div>
              </div>
              <div class="kepala-sekolah-portrait">
                <div class="kepala-sekolah-portrait-placeholder">
                  <span class="portrait-initials" aria-hidden="true">IF</span>
                  Foto kepala sekolah
                  <span>Foto resmi akan ditampilkan setelah ditambahkan.</span>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- 3. SEKILAS TENTANG SMK INFOKOM --}}
        <section class="sec" aria-labelledby="tentangTitle" id="tentang">
          <div class="container">
            <div class="tentang-shell">
              <div class="tentang-copy">
                <span class="detail-tag">TENTANG KAMI</span>
                <h2 id="tentangTitle">Belajar dengan Mengerjakan Hal yang Nyata</h2>
                <p>SMK INFOKOM berfokus pada pembelajaran kejuruan yang menghubungkan pengetahuan di kelas dengan praktik, proyek, dan dunia kerja. Siswa didorong untuk membangun keterampilan, karakter, kreativitas, dan kesiapan menghadapi perkembangan industri.</p>
                <a class="tentang-cta" href="#identitas">Selengkapnya <span>→</span></a>
              </div>
              <aside class="tentang-aside">
                <div class="nilai-utama-card">
                  <span class="detail-tag">NILAI UTAMA</span>
                  <h3>SMK INFOKOM</h3>
                  <ul>
                    <li>Berorientasi pada industri</li>
                    <li>Praktik terarah dan relevan</li>
                    <li>Mengembangkan kreativitas</li>
                    <li>Membentuk karakter profesional</li>
                    <li>Siap menghadapi dunia kerja</li>
                  </ul>
                </div>
              </aside>
            </div>
          </div>
        </section>

        {{-- 4. IDENTITAS SEKOLAH --}}
        <section class="sec identitas-shell" aria-labelledby="identitasTitle" id="identitas">
          <div class="container">
            <div class="sh-row">
              <div class="sh">
                <span class="detail-eyebrow">PROFIL</span>
                <h2 id="identitasTitle">Identitas Sekolah</h2>
                <p>Data sekolah yang dapat diubah melalui panel admin.</p>
              </div>
            </div>
            <div class="identitas-grid">
              <div class="identitas-item">
                <div class="label">Nama Sekolah</div>
                <div class="value">SMK INFOKOM</div>
              </div>
              <div class="identitas-item">
                <div class="label">Status Sekolah</div>
                <div class="value">Swasta</div>
              </div>
              <div class="identitas-item placeholder">
                <div class="label">NPSN</div>
                <div class="value">Belum dikonfirmasi</div>
              </div>
              <div class="identitas-item placeholder">
                <div class="label">Tahun Berdiri</div>
                <div class="value">Belum dikonfirmasi</div>
              </div>
              <div class="identitas-item placeholder">
                <div class="label">Akreditasi</div>
                <div class="value">Belum dikonfirmasi</div>
              </div>
              <div class="identitas-item placeholder">
                <div class="label">Kepala Sekolah</div>
                <div class="value">Informasi belum tersedia</div>
              </div>
              <div class="identitas-item placeholder">
                <div class="label">Alamat</div>
                <div class="value">Lengkapi melalui pengaturan sekolah</div>
              </div>
              <div class="identitas-item placeholder">
                <div class="label">Email</div>
                <div class="value">Lengkapi melalui pengaturan sekolah</div>
              </div>
            </div>
          </div>
        </section>

        {{-- 5. VISI & MISI --}}
        <section class="visi-misi-shell" aria-labelledby="visiTitle" id="visi-misi">
          <div class="container">
            <div class="sh-row">
              <div class="sh">
                <p class="detail-eyebrow">VISI & MISI</p>
                <h2 id="visiTitle">Visi & Misi</h2>
              </div>
            </div>
            <div class="visi-misi-section">
              <div class="visi-block">
                <span class="visi-label">VISI</span>
                <p class="visi-text">Menjadi sekolah kejuruan yang unggul, berkarakter, adaptif terhadap perkembangan teknologi, dan relevan dengan kebutuhan dunia industri.</p>
              </div>
              <div class="misi-grid">
                <div class="misi-item">
                  <span class="misi-number">01</span>
                  <p class="misi-text">Menyelenggarakan pembelajaran kejuruan yang berkualitas dan relevan.</p>
                </div>
                <div class="misi-item">
                  <span class="misi-number">02</span>
                  <p class="misi-text">Mengembangkan kompetensi siswa sesuai kebutuhan dunia industri.</p>
                </div>
                <div class="misi-item">
                  <span class="misi-number">03</span>
                  <p class="misi-text">Membangun karakter disiplin, bertanggung jawab, kreatif, dan profesional.</p>
                </div>
                <div class="misi-item">
                  <span class="misi-number">04</span>
                  <p class="misi-text">Mendorong pemanfaatan teknologi dalam proses pembelajaran.</p>
                </div>
                <div class="misi-item">
                  <span class="misi-number">05</span>
                  <p class="misi-text">Membangun kemitraan yang kuat dengan dunia usaha dan dunia industri.</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- 6. SEJARAH SEKOLAH --}}
        <section class="sejarah-shell" aria-labelledby="sejarahTitle" id="sejarah">
          <div class="container">
            <div class="sh-row">
              <div class="sh">
                <span class="detail-eyebrow">SEJARAH</span>
                <h2 id="sejarahTitle">Perjalanan SMK INFOKOM</h2>
              </div>
            </div>
            <div class="timeline">
              <article class="timeline-item timeline-pending">
                <span class="timeline-dot" aria-hidden="true"></span>
                <span class="timeline-year">Data resmi</span>
                <p class="timeline-text"><strong>Riwayat sekolah sedang dilengkapi.</strong> Informasi tahun pendirian dan tonggak perkembangan akan ditampilkan setelah dikonfirmasi oleh pihak sekolah.</p>
              </article>
            </div>
          </div>
        </section>

        {{-- 7. STRUKTUR ORGANISASI --}}
        <section class="struktur-shell" aria-labelledby="strukturTitle" id="struktur-organisasi">
          <div class="container">
            <div class="sh-row">
              <div class="sh">
                <span class="detail-eyebrow">STRUKTUR</span>
                <h2 id="strukturTitle">Struktur Organisasi</h2>
              </div>
            </div>
            <div class="org-tree">
              <div class="org-level">
                <div class="org-card">
                  <div class="photo">
                    <div class="photo-placeholder">KHS</div>
                  </div>
                  <div class="role">Pimpinan</div>
                  <div class="name">Drs. H. M. Syaiful, M.Pd.</div>
                </div>
              </div>
              <div class="org-level diamond">
                <div class="org-card">
                  <div class="photo"><div class="photo-placeholder">WKS</div></div>
                  <div class="role">Bidang</div>
                  <div class="name">Wakil Kepala Sekolah</div>
                </div>
              </div>
              <div class="org-level">
                <div class="org-card">
                  <div class="photo"><div class="photo-placeholder">W</div></div>
                  <div class="role">Kurikulum</div>
                  <div class="name">Wakil Kepala Bidang Kurikulum</div>
                </div>
                <div class="org-card">
                  <div class="photo"><div class="photo-placeholder">W</div></div>
                  <div class="role">Kesiswaan</div>
                  <div class="name">Wakil Kepala Bidang Kesiswaan</div>
                </div>
                <div class="org-card">
                  <div class="photo"><div class="photo-placeholder">W</div></div>
                  <div class="role">Sarana &amp; Prasarana</div>
                  <div class="name">Wakil Kepala Bidang Sarana &amp; Prasarana</div>
                </div>
                <div class="org-card">
                  <div class="photo"><div class="photo-placeholder">W</div></div>
                  <div class="role">Hubungan Industri</div>
                  <div class="name">Wakil Kepala Bidang Hub. Industri</div>
                </div>
              </div>
              <div class="org-level diamond">
                <div class="org-card">
                  <div class="photo"><div class="photo-placeholder">PK</div></div>
                  <div class="role">Program</div>
                  <div class="name">Ketua Program Keahlian</div>
                </div>
              </div>
              <div class="org-level">
                <div class="org-card">
                  <div class="photo"><div class="photo-placeholder">G</div></div>
                  <div class="role">Tenaga</div>
                  <div class="name">Guru &amp; Tenaga Kependidikan</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- 8. FASILITAS SEKOLAH --}}
        <section class="fasilitas-shell" aria-labelledby="fasilitasTitle" id="fasilitas">
          <div class="container">
            <div class="sh-row">
              <div class="sh">
                <span class="detail-eyebrow">FASILITAS</span>
                <h2 id="fasilitasTitle">Fasilitas Pendukung Pembelajaran</h2>
              </div>
            </div>
            <div class="fasilitas-grid">
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Laboratorium Komputer</h3><p>Ruang praktik untuk pembelajaran teknologi informasi dan komputasi.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Ruang Kelas</h3><p>Lingkungan belajar yang nyaman dan mendukung proses pembelajaran.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Perpustakaan</h3><p>Sumber bacaan dan referensi untuk mendukung tugas dan proyek siswa.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Bengkel / Workshop</h3><p>Tempat praktik kejuruan sesuai bidang keahlian masing-masing.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Ruang Praktik</h3><p>Fasilitas praktik khusus untuk proyek dan tugas kejuruan.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Aula</h3><p>Ruang pertemuan, upacara, dan kegiatan sekolah bersama.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Lapangan</h3><p>Area olahraga dan kegiatan fisik luar ruangan.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>UKS</h3><p>Unit Kesehatan Sekolah untuk layanan kesehatan siswa.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Musala</h3><p>Ruang ibadah untuk aktivitas keagamaan dan spiritual siswa.</p></article>
              <article class="fasilitas-card"><div class="fasilitas-thumb"><div class="fasilitas-thumb-placeholder">Fasilitas</div></div><h3>Kantin</h3><p>Fasilitas wadah untuk kebutuhan sehari-hari siswa dan guru.</p></article>
            </div>
          </div>
        </section>

        {{-- 9. KEUNGGULAN / NILAI SEKOLAH --}}
        <section class="keunggulan-shell" aria-labelledby="keunggulanTitle" id="keunggulan">
          <div class="container">
            <div class="sh-row">
              <div class="sh">
                <span class="detail-eyebrow">KEUNGGULAN</span>
                <h2 id="keunggulanTitle">Kenapa Memilih SMK INFOKOM?</h2>
              </div>
            </div>
            <div class="keunggulan-grid">
              <article class="keunggulan-card">
                <span class="keunggulan-badge">01</span>
                <h3>Pembelajaran Berbasis Praktik</h3>
                <p>Siswa terbiasa mengerjakan proyek nyata agar keterampilan lebih siap pakai di dunia kerja.</p>
              </article>
              <article class="keunggulan-card">
                <span class="keunggulan-badge">02</span>
                <h3>Kurikulum Relevan dengan Industri</h3>
                <p>Materi dan proyek disesuaikan dengan kebutuhan kompetensi dunia kerja saat ini.</p>
              </article>
              <article class="keunggulan-card">
                <span class="keunggulan-badge">03</span>
                <h3>Pengembangan Soft Skill</h3>
                <p>Disiplin, komunikasi, kerja sama, dan tanggung jawab dibangun sejak masa belajar.</p>
              </article>
              <article class="keunggulan-card">
                <span class="keunggulan-badge">04</span>
                <h3>Lingkungan Belajar yang Mendukung</h3>
                <p>Fasilitas dan suasana belajar yang memudahkan siswa berkarya dan berkembang.</p>
              </article>
              <article class="keunggulan-card">
                <span class="keunggulan-badge">05</span>
                <h3>Kerja Sama dengan Dunia Industri</h3>
                <p>Mitra industri terlibat dalam praktik, magang, dan kesiapan siswa memasuki pasar kerja.</p>
              </article>
              <article class="keunggulan-card">
                <span class="keunggulan-badge">06</span>
                <h3>Mempersiapkan Siswa untuk Dunia Kerja</h3>
                <p>Lulusan memiliki keterampilan, portofolio, dan kesiapan menghadapi profesionalisme.</p>
              </article>
            </div>
          </div>
        </section>

        {{-- 10. CTA --}}
        <section class="profil-cta-shell" aria-labelledby="profilCtaTitle" id="profil-cta">
          <div class="container">
            <div class="profil-cta-copy">
              <span class="detail-eyebrow" style="margin-bottom:0.5rem">LANGKAH BERIKUTNYA</span>
              <h2 id="profilCtaTitle">Kenali SMK INFOKOM Lebih Dekat</h2>
              <p>Temukan program keahlian, fasilitas, kegiatan, dan berbagai peluang yang tersedia di SMK INFOKOM.</p>
              <div class="profil-cta-buttons">
                <a class="btn btn-accent" href="{{ route('site.section', 'akademik') }}">Lihat Program Keahlian</a>
                <a class="btn btn-brand" href="{{ route('site.section', 'ppdb') }}">Daftar PPDB <span aria-hidden="true">→</span></a>
              </div>
            </div>
            <aside class="profil-cta-aside">
              <div class="profil-cta-aside-item">
                <div class="profil-cta-aside-icon">P</div>
                <div>
                  <h4>Program Keahlian</h4>
                  <p>Pilihan jurusan yang relevan dengan kebutuhan industri.</p>
                </div>
              </div>
              <div class="profil-cta-aside-item">
                <div class="profil-cta-aside-icon">F</div>
                <div>
                  <h4>Fasilitas Lengkap</h4>
                  <p>Laboratorium, ruang praktik, dan ruang belajar yang mendukung.</p>
                </div>
              </div>
              <div class="profil-cta-aside-item">
                <div class="profil-cta-aside-icon">J</div>
                <div>
                  <h4>Magang Jepang</h4>
                  <p>Peluang pengalaman kerja dan pengembangan internasional.</p>
                </div>
              </div>
            </aside>
          </div>
        </section>

      @elseif ($section === 'akademik')
        <div class="academic-shell">
          <div class="academic-intro-panel">
            <span class="detail-tag">Pembelajaran yang relevan</span>
            <h2>Program keahlian terarah untuk masa depan yang siap kerja</h2>
            <p>Setiap jurusan dirancang agar siswa tidak hanya memahami teori, tetapi juga mampu menerapkan keterampilan dalam proyek, praktik, dan kerja nyata.</p>
            <div class="academic-feature-list">
              <span>Project Based Learning</span>
              <span>Praktik industri</span>
              <span>Kompetensi terukur</span>
            </div>
          </div>
          <div class="detail-grid academic-grid">
            @foreach ($data as $program)
              <article class="detail-card academic-card">
                <span class="detail-tag">{{ $program->short }}</span>
                <h2>{{ $program->name }}</h2>
                <p>{{ $program->description }}</p>
                <h3>Kompetensi</h3>
                <ul>@foreach ($program->skills as $skill)<li>{{ $skill }}</li>@endforeach</ul>
                <h3>Prospek karier</h3>
                <p>{{ implode(' · ', $program->jobs) }}</p>
              </article>
            @endforeach
          </div>
        </div>
      @elseif ($section === 'kesiswaan')
        <div class="detail-split">
          <div><h2>Kegiatan siswa</h2><div class="detail-grid detail-grid-compact">
            @forelse ($data['activities'] as $activity)
              <article class="detail-card"><span class="detail-tag">{{ $activity->category }}</span><h2>{{ $activity->title }}</h2><p>Pengalaman belajar, berkarya, dan bertumbuh bersama.</p></article>
            @empty<p class="detail-empty">Informasi kegiatan siswa akan segera tersedia.</p>@endforelse
          </div></div>
          <aside class="detail-agenda"><h2>Pencapaian siswa</h2>
            @forelse ($data['achievements'] as $achievement)
              <article><time>{{ $achievement->year }}</time><div><h3>{{ $achievement->title }}</h3><p>{{ $achievement->who }} · {{ $achievement->level }}</p></div></article>
            @empty<p class="detail-empty">Belum ada pencapaian yang dipublikasikan.</p>@endforelse
          </aside>
        </div>
      @elseif ($section === 'prestasi')
        <div class="detail-list">
          @forelse ($data as $achievement)
            <article class="detail-row"><span class="detail-tag">{{ $achievement->year }}</span><div><h2>{{ $achievement->title }}</h2><p>{{ $achievement->who }} · Tingkat {{ $achievement->level }}</p></div></article>
          @empty
            <p class="detail-empty">Belum ada pencapaian yang dipublikasikan.</p>
          @endforelse
        </div>
      @elseif ($section === 'industri')
        <div class="industry-shell">
          <div class="industry-intro-panel">
            <span class="detail-tag">Kolaborasi industri</span>
            <h2>Mitra yang membangun pengalaman kerja dan kesiapan lulusan</h2>
            <p>Kerja sama dengan dunia industri memberi siswa akses pada praktik, pemahaman profesi, serta peluang belajar yang relevan dengan kebutuhan pasar kerja.</p>
            <div class="industry-badges">
              <span>Praktik industri</span>
              <span>Kerja sama strategis</span>
              <span>Persiapan kerja</span>
            </div>
          </div>
          <div class="detail-grid detail-grid-compact industry-grid">
            @forelse ($data as $partner)
              <article class="detail-card industry-card">
                <span class="detail-tag">{{ $partner->category }}</span>
                <h2>{{ $partner->name }}</h2>
                <p>{{ $partner->description ?? 'Mitra industri yang mendukung proses pembelajaran, praktik, dan kesiapan siswa memasuki dunia kerja.' }}</p>
                <div class="industry-meta">
                  <span>Industri</span>
                  <span>{{ $partner->category ?: 'Mitra sekolah' }}</span>
                </div>
              </article>
            @empty
              <p class="detail-empty">Informasi mitra akan segera tersedia.</p>
            @endforelse
          </div>
        </div>
      @elseif ($section === 'jepang')
        <ol class="detail-steps">
          @foreach ($data as $step)
            <li><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><h2>{{ $step->title }}</h2><p>{{ $step->description }}</p><small>{{ $step->duration }}</small></div></li>
          @endforeach
        </ol>
      @elseif ($section === 'ppdb')
        <div class="detail-ppdb">
          <div>
            <div class="ppdb-intro-panel">
              <span class="detail-tag">Pendaftaran 2026</span>
              <h2>Daftar sebagai calon murid</h2>
              <p>Isi data berikut. Nomor pendaftaran akan ditampilkan setelah formulir berhasil dikirim.</p>
              <div class="ppdb-checklist">
                <span>✅ Proses sederhana</span>
                <span>✅ Jadwal informasi jelas</span>
                <span>✅ Informasi program keahlian lengkap</span>
              </div>
            </div>
            @if (session('registration_number'))<div class="admin-status" role="status">Pendaftaran berhasil. Simpan nomor Anda: <b>{{ session('registration_number') }}</b></div>@endif
            <form class="ppdb-form" method="post" action="{{ route('ppdb.apply') }}">
              @csrf
              <label for="full_name">Nama lengkap</label><input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" autocomplete="name" required>
              @error('full_name')<small class="admin-field-error">{{ $message }}</small>@enderror
              <label for="phone">Nomor telepon</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required>
              @error('phone')<small class="admin-field-error">{{ $message }}</small>@enderror
              <label for="origin_school">Asal sekolah</label><input id="origin_school" name="origin_school" type="text" value="{{ old('origin_school') }}" required>
              @error('origin_school')<small class="admin-field-error">{{ $message }}</small>@enderror
              <label for="program">Program keahlian pilihan</label><select id="program" name="program" required><option value="">Pilih program</option>@foreach ($data['programs'] as $program)<option value="{{ $program->short }}" @selected(old('program') === $program->short)>{{ $program->short }} · {{ $program->name }}</option>@endforeach</select>
              @error('program')<small class="admin-field-error">{{ $message }}</small>@enderror
              <label class="ppdb-consent"><input type="checkbox" name="consent" value="1" required> Saya menyatakan data yang diisi benar dan dapat dihubungi sekolah.</label>
              @error('consent')<small class="admin-field-error">{{ $message }}</small>@enderror
              <button class="btn btn-brand" type="submit">Kirim pendaftaran</button>
            </form>
          </div>
          <aside class="detail-agenda ppdb-sidebar">
            <h2>Informasi pendaftaran</h2>
            @forelse ($data['posts'] as $post)<article><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale('id')->translatedFormat('j M') }}</time><div><h3>{{ $post->title }}</h3><p>{{ $post->excerpt }}</p></div></article>@empty<p class="detail-empty">Silakan hubungi sekolah untuk jadwal terbaru.</p>@endforelse
          </aside>
        </div>
      @elseif ($section === 'informasi')
        <div class="detail-split">
          <div><h2>Berita terbaru</h2><div class="detail-list">
            @forelse ($data['posts'] as $post)
              <article class="detail-row"><span class="detail-tag">{{ $post->category }}</span><div><h3>{{ $post->title }}</h3><p>{{ $post->excerpt }}</p><small>{{ $post->published_at->locale('id')->translatedFormat('j F Y') }}</small></div></article>
            @empty<p class="detail-empty">Belum ada berita yang dipublikasikan.</p>@endforelse
          </div></div>
          <aside class="detail-agenda"><h2>Agenda</h2>
            @forelse ($data['agendas'] as $agenda)
              <article><time datetime="{{ $agenda->date->toDateString() }}">{{ $agenda->date->locale('id')->translatedFormat('j M') }}</time><div><h3>{{ $agenda->title }}</h3><p>{{ $agenda->place_time }}</p></div></article>
            @empty<p class="detail-empty">Belum ada agenda mendatang.</p>@endforelse
          </aside>
        </div>
      @elseif ($section === 'galeri')
        <div class="detail-grid detail-grid-compact">
          @forelse ($data as $item)
            <article class="detail-card"><span class="detail-tag">{{ $item->category }}</span><h2>{{ $item->title }}</h2><p>Dokumentasi kegiatan SMK INFOKOM.</p></article>
          @empty<p class="detail-empty">Galeri akan diperbarui dengan dokumentasi kegiatan sekolah.</p>@endforelse
        </div>
      @elseif ($section === 'alumni')
        <div class="detail-split">
          <div><h2>Cerita alumni</h2><div class="detail-grid detail-grid-compact">
            @forelse ($data['alumni'] as $alumnus)
              <blockquote class="detail-card"><p>“{{ $alumnus->quote }}”</p><footer><b>{{ $alumnus->name }}</b><span>{{ $alumnus->role_desc }}</span></footer></blockquote>
            @empty<p class="detail-empty">Cerita alumni akan segera tersedia.</p>@endforelse
          </div></div>
          <aside class="detail-agenda"><h2>Tracer study</h2>
            @forelse ($data['tracer'] as $stat)
              <article><span class="detail-tag">{{ $stat->value }}%</span><div><h3>{{ $stat->label }}</h3><meter class="detail-meter" min="0" max="100" value="{{ min($stat->value, 100) }}">{{ $stat->value }}%</meter></div></article>
            @empty<p class="detail-empty">Data tracer study belum tersedia.</p>@endforelse
          </aside>
        </div>
      @elseif ($section === 'kontak')
        <div class="contact-layout">
          <div class="contact-main">
            <section class="contact-block contact-identity" aria-labelledby="schoolIdentityTitle">
              <span class="detail-tag">Sekolah vokasi</span>
              <h2 id="schoolIdentityTitle">{{ $data['name'] }}</h2>
              <p>SMK INFOKOM memfasilitasi siswa belajar melalui praktik, teknologi, dan pengembangan karakter siap kerja.</p>
              <div class="contact-quick-stats">
                <div>
                  <strong>{{ $data['hours'][0]['time'] ?? '07.00-15.00' }}</strong>
                  <span>Jam layanan</span>
                </div>
                <div>
                  <strong>{{ collect($data['socials'] ?? [])->filter()->count() ?: '0' }}</strong>
                  <span>Kanal resmi</span>
                </div>
                <div>
                  <strong>{{ filled($data['emails']['general']) ? '1' : '0' }}</strong>
                  <span>Email utama</span>
                </div>
              </div>
              <div class="contact-cta-bar">
                @if (filled($data['whatsapp']['general'] ?? ''))
                  <a class="contact-cta-button whatsapp" href="{{ $data['whatsapp_url']($data['whatsapp']['general'], 'Halo ' . ($data['name'] ?? 'SMK INFOKOM') . ', saya ingin bertanya seputar sekolah.') }}" target="_blank" rel="noopener">Chat WhatsApp</a>
                @endif
                @if ($data['maps_url'])
                  <a class="contact-cta-button map" href="{{ $data['maps_url'] }}" target="_blank" rel="noopener">Lihat Google Maps</a>
                @endif
              </div>
            </section>

            <section class="contact-block" aria-labelledby="contactAddressTitle">
              <div class="contact-block-heading"><div><span class="detail-tag">Lokasi sekolah</span><h2 id="contactAddressTitle">{{ $data['name'] }}</h2></div><button class="contact-copy-button" type="button" data-copy-address @disabled(blank($data['full_address']))>Salin alamat</button></div>
              @if ($data['address_lines']->isNotEmpty())
                <dl class="contact-address">
                  @foreach ($data['address_lines'] as $label => $value)
                    <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                  @endforeach
                </dl>
                <p class="contact-full-address" id="fullAddress">{{ $data['name'] }}, {{ $data['full_address'] }}</p>
                <div class="contact-map-actions">
                  @if ($data['maps_url'])<a class="btn btn-brand" href="{{ $data['maps_url'] }}" target="_blank" rel="noopener">Buka di Google Maps <span aria-hidden="true">↗</span></a>@endif
                </div>
                @if ($data['maps_embed_url'])
                  <div class="contact-map-shell">
                    <iframe class="contact-map" src="{{ $data['maps_embed_url'] }}" title="Peta lokasi {{ $data['name'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                  </div>
                @endif
              @else
                <div class="contact-not-configured">
                  <strong>Alamat sekolah belum diisi.</strong>
                  <p>Silakan hubungi administrator untuk menambahkan informasi lokasi, jam layanan, dan jalur komunikasi resmi.</p>
                </div>
              @endif
            </section>

            <section class="contact-block" aria-labelledby="contactChannelsTitle">
              <span class="detail-tag">Pilih jalur yang sesuai</span><h2 id="contactChannelsTitle">Kontak berdasarkan kebutuhan</h2>
              <div class="contact-channel-list">
                @php
                  $channels = [
                    ['label' => 'Informasi umum', 'phone' => $data['phones']['general'], 'whatsapp' => $data['whatsapp']['general'], 'email' => $data['emails']['general']],
                    ['label' => 'PPDB', 'phone' => $data['phones']['ppdb'], 'whatsapp' => $data['whatsapp']['ppdb'], 'email' => $data['emails']['ppdb']],
                    ['label' => 'TU / Administrasi', 'phone' => $data['phones']['administration'], 'whatsapp' => '', 'email' => $data['emails']['administration']],
                    ['label' => 'Kerja sama', 'phone' => '', 'whatsapp' => '', 'email' => $data['emails']['industry']],
                    ['label' => 'Hubungan industri', 'phone' => $data['phones']['industry'], 'whatsapp' => '', 'email' => $data['emails']['industry']],
                    ['label' => 'Alumni', 'phone' => $data['phones']['alumni'], 'whatsapp' => '', 'email' => $data['emails']['alumni']],
                  ];
                @endphp
                @foreach ($channels as $channel)
                  @if (filled($channel['phone']) || filled($channel['whatsapp']) || filled($channel['email']))
                    <article class="contact-channel"><h3>{{ $channel['label'] }}</h3><div>
                      @if (filled($channel['phone']))<a href="tel:{{ preg_replace('/[^0-9+]/', '', $channel['phone']) }}">Telepon {{ $channel['phone'] }}</a>@endif
                      @if (filled($channel['whatsapp']))<a class="contact-whatsapp" href="{{ $data['whatsapp_url']($channel['whatsapp'], 'Halo ' . $data['name'] . ', saya ingin bertanya mengenai ' . strtolower($channel['label']) . '.') }}" target="_blank" rel="noopener">Chat via WhatsApp <span aria-hidden="true">↗</span></a>@endif
                      @if (filled($channel['email']))<a href="mailto:{{ $channel['email'] }}">{{ $channel['email'] }}</a>@endif
                    </div></article>
                  @endif
                @endforeach
                @if (! collect($channels)->contains(fn ($channel) => filled($channel['phone']) || filled($channel['whatsapp']) || filled($channel['email'])))
                  <div class="contact-not-configured">
                    <strong>Nomor telepon, WhatsApp, dan email resmi belum dikonfigurasi.</strong>
                    <p>Data telepon, WhatsApp, dan email sekolah akan tampil otomatis di sini saat admin mengisi konfigurasi di panel sekolah.</p>
                  </div>
                @endif
              </div>
            </section>

            <section class="contact-block" aria-labelledby="contactSocialTitle">
              <span class="detail-tag">Kanal resmi</span><h2 id="contactSocialTitle">Media sosial</h2>
              <div class="contact-socials">
                @forelse (collect($data['socials'])->filter() as $label => $url)
                  <a href="{{ $url }}" target="_blank" rel="noopener">{{ $label }} <span aria-hidden="true">↗</span></a>
                @empty
                  <div class="contact-not-configured">
                    <strong>Belum ada media sosial resmi.</strong>
                    <p>Tambahkan tautan Instagram, TikTok, atau YouTube agar masyarakat dapat mengikuti kegiatan sekolah.</p>
                  </div>
                @endforelse
              </div>
            </section>
          </div>

          <aside class="contact-aside">
            <section class="contact-hours" aria-labelledby="contactHoursTitle"><span class="detail-tag">Waktu layanan</span><h2 id="contactHoursTitle">Jam operasional</h2>
              <dl>@foreach ($data['hours'] as $hours)<div><dt>{{ $hours['day'] }}</dt><dd>{{ $hours['time'] }}</dd></div>@endforeach</dl>
            </section>
            <section class="contact-form-panel" aria-labelledby="contactFormTitle"><span class="detail-tag">Ada yang ingin ditanyakan?</span><h2 id="contactFormTitle">Hubungi kami</h2>
              @if (session('contact_message_sent'))<div class="contact-success" role="status">Pesan Anda berhasil dikirim. Tim sekolah akan menghubungi Anda.</div>@endif
              <form class="contact-form" method="post" action="{{ route('contact-messages.store') }}">
                @csrf
                <div class="contact-honeypot" aria-hidden="true"><label for="company_website">Website</label><input id="company_website" name="company_website" type="text" tabindex="-1" autocomplete="off"></div>
                <label for="contact_name">Nama</label><input id="contact_name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="160" required>@error('name')<small>{{ $message }}</small>@enderror
                <label for="contact_email">Email</label><input id="contact_email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="190" required>@error('email')<small>{{ $message }}</small>@enderror
                <label for="contact_whatsapp">Nomor WhatsApp</label><input id="contact_whatsapp" name="whatsapp" type="tel" value="{{ old('whatsapp') }}" autocomplete="tel" maxlength="30" required>@error('whatsapp')<small>{{ $message }}</small>@enderror
                <label for="contact_subject">Subjek</label><select id="contact_subject" name="subject" required><option value="">Pilih keperluan</option>@foreach ($data['inquiry_types'] as $subject)<option value="{{ $subject }}" @selected(old('subject') === $subject)>{{ $subject }}</option>@endforeach</select>@error('subject')<small>{{ $message }}</small>@enderror
                <label for="contact_message">Pesan</label><textarea id="contact_message" name="message" rows="5" minlength="10" maxlength="5000" required>{{ old('message') }}</textarea>@error('message')<small>{{ $message }}</small>@enderror
                <button class="btn btn-brand" type="submit">Kirim pesan <span aria-hidden="true">→</span></button>
              </form>
            </section>
          </aside>
        </div>
      @endif
    </div>
  </section>
@endsection
