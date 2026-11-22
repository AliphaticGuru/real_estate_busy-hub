<?php

namespace App\Http\Controllers;

use App\Enums\ProfessionalType;
use App\Models\Professional;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    /**
     * Public directory of verified professionals with an optional type filter.
     */
    public function index(Request $request): View
    {
        $currentType = ProfessionalType::tryFrom((string) $request->input('type'));

        $professionals = Professional::query()
            ->whereNotNull('credentials_verified_at')
            ->with(['user', 'company'])
            ->when($currentType !== null, fn ($query) => $query->where('type', $currentType->value))
            ->orderBy('type')
            ->orderBy('id')
            ->get();

        return view('professionals.index', [
            'professionals' => $professionals,
            'types' => ProfessionalType::cases(),
            'currentType' => $currentType?->value,
        ]);
    }

    /**
     * Public profile for a single verified professional.
     */
    public function show(Professional $professional): View
    {
        if ($professional->credentials_verified_at === null) {
            abort(404);
        }

        $professional->load(['user', 'company']);

        return view('professionals.show', ['professional' => $professional]);
    }
}
