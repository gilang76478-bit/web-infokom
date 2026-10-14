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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
