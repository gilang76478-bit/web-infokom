<?php

namespace App\Http\Controllers;

use App\Models\PpdbApplicant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPpdbController extends Controller
{
    public function index(): View
    {
        $applicants = PpdbApplicant::latest()->paginate(20);

        return view('admin.ppdb.index', compact('applicants'));
    }

    public function update(Request $request, PpdbApplicant $applicant): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Menunggu Verifikasi', 'Terverifikasi', 'Perlu Perbaikan'])],
        ]);
        $applicant->update($data);

        return back()->with('status', 'Status pendaftar berhasil diperbarui.');
    }
}