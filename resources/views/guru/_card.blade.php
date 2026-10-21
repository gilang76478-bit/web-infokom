<article class="org-card">
  <div class="photo">
    @if (filled($teacher->photo_url))
      <img src="{{ $teacher->photo_url }}" alt="Foto {{ $teacher->name }}" loading="lazy">
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
