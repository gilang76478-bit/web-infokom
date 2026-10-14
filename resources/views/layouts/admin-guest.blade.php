<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }} | SMK INFOKOM</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/admin.css'])
</head>
<body class="admin-auth-page">
  <main class="admin-auth">
    <a class="admin-back" href="{{ route('home') }}">← Website SMK INFOKOM</a>
    @yield('content')
    <p class="admin-auth-foot">SMK INFOKOM · Sistem pengelolaan konten</p>
  </main>
</body>
</html>
