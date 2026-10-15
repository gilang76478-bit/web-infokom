@php
    $backLink = $backLink ?? null;
    $backLabel = $backLabel ?? null;
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }} | Admin SMK INFOKOM</title>
  {{-- Font diunduh saat build Vite dan disajikan dari /build (self-hosted). --}}
  @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-shell">
  <div class="admin-layout">
    @include('admin.partials.sidebar')
    <div class="admin-main">
      @include('admin.partials.topbar')
      <main class="admin-content-area {{ $formArea ?? '' }}">
        @if ($backLink)
          <a class="admin-back" href="{{ $backLink }}">← {{ $backLabel }}</a>
        @endif
        @yield('content')
      </main>
    </div>
  </div>
</body>
</html>
