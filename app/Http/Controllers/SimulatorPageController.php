<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\SimulationHistory;
use App\Services\FormLookupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SimulatorPageController extends Controller
{
    public function form(FormLookupService $lookupService): View
    {
        return view('pages.simulation.form', [
            'lookups' => $lookupService->all(),
            'prefill' => [],
        ]);
    }

    public function results(Request $request, SimulationHistory $simulation): View
    {
        if ($simulation->user_id && optional($request->user())->id !== $simulation->user_id) {
            abort(403);
        }

        return view('pages.simulation.results', [
            'simulation' => $simulation,
            'result' => $simulation->output_data ?? [],
        ]);
    }

    public function history(Request $request): View
    {
        $simulations = collect();

        if ($user = $request->user()) {
            $simulations = SimulationHistory::query()
                ->where('user_id', $user->id)
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('pages.simulation.history', compact('simulations'));
    }

    public function docs(): View
    {
        return view('pages.docs.index');
    }

    public function ingredients(): View
    {
        $ingredients = Ingredient::query()
            ->with('category')
            ->select(['id', 'name', 'inci_name', 'effects', 'safety_notes', 'ingredient_category_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(24)
            ->get();

        return view('pages.ingredients.index', compact('ingredients'));
    }
}
