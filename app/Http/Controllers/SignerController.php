<?php

namespace App\Http\Controllers;

use App\Models\Signer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Firmantes de los certificados.
 * Las imágenes de firma nunca se borran del disco: los certificados ya emitidos
 * guardan la ruta de la firma que tenían y la siguen necesitando para generar el PDF.
 */
class SignerController extends Controller
{
    public function index(): Response
    {
        $signers = Signer::withCount('courses')
            ->orderBy('name')
            ->get()
            ->map(fn (Signer $s) => [
                'id'            => $s->id,
                'name'          => $s->name,
                'position'      => $s->position,
                'signature_url' => $s->signature_url,
                'courses'       => $s->courses_count,
            ]);

        return Inertia::render('Admin/Signers/Index', ['signers' => $signers]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(creating: true), $this->messages());

        Signer::create([
            'name'       => $data['name'],
            'position'   => $data['position'] ?? null,
            'signature'  => $request->file('signature')->store('signatures', 'public'),
        ]);

        return redirect()->route('admin.signers.index')->with('success', 'Firmante agregado.');
    }

    public function edit(Signer $signer): Response
    {
        return $this->form($signer);
    }

    public function update(Request $request, Signer $signer)
    {
        $data = $request->validate($this->rules(creating: false), $this->messages());

        $signer->fill([
            'name'       => $data['name'],
            'position'   => $data['position'] ?? null,
        ]);

        // Firma nueva: se guarda con otro nombre y la anterior queda en el disco (la usan certificados viejos)
        if ($request->hasFile('signature')) {
            $signer->signature = $request->file('signature')->store('signatures', 'public');
        }

        $signer->save();

        return redirect()->route('admin.signers.index')->with('success', 'Firmante actualizado.');
    }

    public function destroy(Signer $signer)
    {
        // Se quita de los cursos (los certificados ya emitidos no cambian); el archivo de firma se conserva
        $signer->delete();

        return redirect()->route('admin.signers.index')->with('success', 'Firmante eliminado.');
    }

    private function form(?Signer $signer): Response
    {
        return Inertia::render('Admin/Signers/Form', [
            'signer' => $signer ? [
                'id'            => $signer->id,
                'name'          => $signer->name,
                'position'      => $signer->position,
                'signature_url' => $signer->signature_url,
                'courses'       => $signer->courses()->count(),
            ] : null,
        ]);
    }

    private function rules(bool $creating): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'position'   => ['nullable', 'string', 'max:255'],
            // PNG con fondo transparente es lo ideal para el PDF
            'signature'  => [$creating ? 'required' : 'nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }

    private function messages(): array
    {
        return [
            'signature.required' => 'Subí la imagen de la firma.',
            'signature.mimes'    => 'La firma tiene que ser PNG o JPG.',
            'signature.max'      => 'La imagen no puede pesar más de 2 MB.',
        ];
    }
}
