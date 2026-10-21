<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminContentRequest;
use App\Support\ContentTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $values = $this->handleFileFields($request, $definition, $request->validated());
        $definition['model']::create($this->normalize($values, $definition));

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
        $values = $this->handleFileFields($request, $definition, $request->validated(), $record);
        $record->update($this->normalize($values, $definition));

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

    /**
     * Tangani field bertipe berkas (mis. foto guru): berkas baru disimpan ke
     * disk publik, berkas lama yang diganti atau diminta dihapus ikut
     * dibersihkan agar tidak menumpuk, dan ketika admin tidak mengirim
     * berkas apa pun nilai lama dipertahankan (bukan ditimpa null).
     *
     * @return array<string, mixed>
     */
    private function handleFileFields(Request $request, array $definition, array $values, ?Model $record = null): array
    {
        foreach ($definition['fields'] as $name => $field) {
            if (($field['type'] ?? null) !== 'file') {
                continue;
            }

            if ($request->hasFile($name)) {
                $this->deleteStoredFile($record?->{$name});
                $values[$name] = $request->file($name)->store('photos/'.$definition['model']::query()->getModel()->getTable(), 'public');
            } elseif ($request->boolean($name.'_remove')) {
                $this->deleteStoredFile($record?->{$name});
                $values[$name] = null;
            } else {
                unset($values[$name]);
            }
        }

        return $values;
    }

    /** Hapus berkas hasil unggahan; nilai URL luar (http) tidak disentuh. */
    private function deleteStoredFile(mixed $path): void
    {
        if (is_string($path) && filled($path) && ! preg_match('#^(https?:)?//#i', $path)) {
            Storage::disk('public')->delete($path);
        }
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
