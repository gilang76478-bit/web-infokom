@extends('layouts.admin-guest', ['title' => 'Login Admin'])

@section('content')
  <section class="admin-auth-panel">
    <span class="detail-mark" aria-hidden="true">IF</span>
    <p class="detail-eyebrow">Area terbatas</p>
    <h1>Masuk ke panel admin</h1>
    <p>Gunakan akun administrator sekolah untuk mengelola informasi website.</p>
    @if ($errors->any())<div class="admin-auth-error" role="alert">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('admin.authenticate') }}">
      @csrf
      <label for="email">Email administrator</label>
      <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
      <label for="password">Kata sandi</label>
      <input id="password" name="password" type="password" autocomplete="current-password" required>
      <label class="admin-remember"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
      <button class="btn btn-brand" type="submit">Masuk dengan aman</button>
    </form>
  </section>
@endsection
