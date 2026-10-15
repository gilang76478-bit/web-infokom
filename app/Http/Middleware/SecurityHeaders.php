<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan dasar untuk semua respons Laravel.
 *
 * Catatan: file di /build dan /storage pada umumnya dilayani langsung oleh
 * web server (bukan lewat framework); pengaturan cache untuk aset statis
 * ada di public/.htaccess (Apache) dan disertakan pada dokumentasi deploy
 * nginx di DEPLOY.md. Blok cache di bawah adalah lapis cadangan untuk
 * deployment yang mengarahkan seluruh permintaan ke front controller.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Cadangan cache hanya untuk respons sukses, sehingga 404 tidak
        // pernah ter-cache bertahun-tahun oleh klien.
        if ($response->isSuccessful() && $request->is('build/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        } elseif ($response->isSuccessful() && $request->is('storage/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=604800');
        }

        return $response;
    }
}
