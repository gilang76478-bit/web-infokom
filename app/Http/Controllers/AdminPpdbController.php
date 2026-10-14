<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminPpdbUpdateRequest;
use App\Models\PpdbApplicant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminPpdbController extends Controller
{
    public function index(): View
    {
        $applicants = PpdbApplicant::latest()->paginate(20);

        return view('admin.ppdb.index', compact('applicants'));
    }

    public function update(AdminPpdbUpdateRequest $request, PpdbApplicant $applicant): RedirectResponse
    {
        $applicant->update($request->validated());

        return back()->with('status', 'Status pendaftar berhasil diperbarui.');
    }
}