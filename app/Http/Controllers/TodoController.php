<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;
use Inertia\Inertia;

class TodoController extends Controller
{
    public function index()
    {
        $statuses = ['backlog', 'to do', 'in progress', 'testing', 'done'];

        $todosByStatus = [];
        foreach ($statuses as $status) {
            $todosByStatus[$status] = Todo::where('status', $status)
                ->orderBy('position')
                ->get();
        }

        return Inertia::render('Todos/Board', [
            'todosByStatus' => $todosByStatus,
            'statuses' => $statuses,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        // Bepaal positie op basis van bestaande items in deze kolom
        $position = Todo::where('status', $data['status'])->count();

        $data['position'] = $position;

        $todo = Todo::create($data);

        return redirect()->route('todos.board');
    }

    public function update(Request $request, Todo $todo)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $todo->update($data);

        return redirect()->route('todos.board');
    }

    public function reorder(Request $request)
    {
        $data = $request->input('todosByStatus', []);

        foreach ($data as $status => $ids) {
            foreach ($ids as $position => $id) {
                Todo::where('id', $id)->update([
                    'position' => $position,
                    'status' => $status,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Order updated');
    }
}
