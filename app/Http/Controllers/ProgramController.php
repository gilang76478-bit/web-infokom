<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function show(Program $program): View
    {
        $program->load('teachers');

        return view('programs.show', compact('program'));
    }
}
