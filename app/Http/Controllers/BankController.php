<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.banks');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = Bank::query()->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:banks,name'],
        ]);

        $bank = Bank::create([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'message' => 'Bank created successfully.',
            'data' => $bank,
        ], 201);
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:banks,name,' . $bank->id],
        ]);

        $bank->update([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'message' => 'Bank updated successfully.',
            'data' => $bank->fresh(),
        ]);
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();

        return response()->json([
            'message' => 'Bank deleted successfully.',
        ]);
    }
}
