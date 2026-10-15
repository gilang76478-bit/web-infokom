<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminContentRequest;
use App\Support\ContentTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminContentController extends Controller
{
    public function index(string $type): View
    {
        $definition = $this->definition($type);
        $records = $definition['model']::query()->latest('id')->paginate(20);

        return view('admin.content.index', compact('type', 'definition', 'records'));
    }

    public function create(string $type): View
    {
        $definition = $this->definition($type);

        return view('admin.content.form', ['type' => $type, 'definition' => $definition, 'record' => null]);
    }

    public function store(AdminContentRequest $request, string $type): RedirectResponse
    {
        $definition = $this->definition($type);
        $definition['model']::create($this->normalize($request->validated(), $definition));

        return redirect()->route('admin.content.index', $type)->with('status', 'Data berhasil ditambahkan.');
    }

    public function edit(string $type, int $record): View
    {
        $definition = $this->definition($type);
        $record = $definition['model']::findOrFail($record);

        return view('admin.content.form', compact('type', 'definition', 'record'));
    }

    public function update(AdminContentRequest $request, string $type, int $record): RedirectResponse
    {
        $definition = $this->definition($type);
        $record = $definition['model']::findOrFail($record);
        $record->update($this->normalize($request->validated(), $definition));

        return redirect()->route('admin.content.index', $type)->with('status', 'Perubahan berhasil disimpan.');
    }

    public function destroy(string $type, int $record): RedirectResponse
    {
        $definition = $this->definition($type);
        $definition['model']::findOrFail($record)->delete();

        return redirect()->route('admin.content.index', $type)->with('status', 'Data berhasil dihapus.');
    }

    private function definition(string $type): array
    {
        abort_unless(isset(ContentTypes::ALL[$type]), 404);

        return ContentTypes::ALL[$type];
    }

    private function normalize(array $values, array $definition): array
    {
        foreach ($definition['fields'] as $name => $field) {
            if ($field['type'] === 'lines') {
                $values[$name] = collect(preg_split('/\r\n|\r|\n/', $values[$name] ?? ''))
                    ->map(fn ($item) => trim($item))
                    ->filter()
                    ->values()
                    ->all();
            }

            if ($field['type'] === 'select') {
                $values[$name] = filled($values[$name] ?? null) ? (int) $values[$name] : null;
            }
        }

        return $values;
    }
}