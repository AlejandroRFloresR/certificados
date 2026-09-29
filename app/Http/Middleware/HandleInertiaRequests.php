<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'inertia';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(), // Spatie
                ] : null,
            ],
            // Las mismas claves que ya usan tus controladores con ->with(...)
            'flash' => fn () => [
                'success'   => $request->session()->get('success'),
                'error'     => $request->session()->get('error'),
                'status'    => $request->session()->get('status'),
                'failures'  => $request->session()->get('failures'),
                'cert_code' => $request->session()->get('cert_code'),
            ],
            'appName' => config('app.name'),
            'imagesUrl' => asset('storage/images'),
        ]);
    }
}
