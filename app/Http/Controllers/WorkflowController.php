<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class WorkflowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $response = Http::get('http://127.0.0.1:8001/workflows');
            
            if ($response->successful()) {
                $data = $response->json();
                $workflows = $data['workflows'] ?? [];
            } else {
                $workflows = [];
            }
        } catch (\Exception $e) {
            $workflows = [];
        }

        return Inertia::render('Workflows/Index', [
            'workflows' => $workflows
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Workflows/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'required|array',
            'is_active' => 'boolean'
        ]);

        try {
            $response = Http::post('http://127.0.0.1:8001/workflows', $request->all());
            
            if ($response->successful()) {
                return redirect()->route('workflows.index')
                    ->with('success', 'Workflow created successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to create workflow.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error creating workflow: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $response = Http::get("http://127.0.0.1:8001/workflows/{$id}");
            
            if ($response->successful()) {
                $data = $response->json();
                $workflow = $data['workflow'] ?? $data;
                return Inertia::render('Workflows/Show', [
                    'workflow' => $workflow
                ]);
            } else {
                return redirect()->route('workflows.index')
                    ->with('error', 'Workflow not found.');
            }
        } catch (\Exception $e) {
            return redirect()->route('workflows.index')
                ->with('error', 'Error loading workflow.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
        public function edit($id)
    {
        try {
            $response = Http::get("http://127.0.0.1:8001/workflows/{$id}");
            
            if ($response->successful()) {
                $data = $response->json();
                $workflow = $data['workflow'] ?? $data;
                return Inertia::render('Workflows/Edit', [
                    'workflow' => $workflow
                ]);
            } else {
                return redirect()->route('workflows.index')
                    ->with('error', 'Workflow not found.');
            }
        } catch (\Exception $e) {
            return redirect()->route('workflows.index')
                ->with('error', 'Error loading workflow.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'required|array',
            'is_active' => 'boolean'
        ]);

        try {
            $response = Http::put("http://127.0.0.1:8001/workflows/{$id}", $request->all());
            
            if ($response->successful()) {
                return redirect()->route('workflows.index')
                    ->with('success', 'Workflow updated successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to update workflow.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error updating workflow: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $response = Http::delete("http://127.0.0.1:8001/workflows/{$id}");
            
            if ($response->successful()) {
                return redirect()->route('workflows.index')
                    ->with('success', 'Workflow deleted successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to delete workflow.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error deleting workflow: ' . $e->getMessage()]);
        }
    }
} 