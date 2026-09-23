<?php

namespace App\Modules\Todos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Todos\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);
        $todo = Todo::create($data + [
            'user_id' => $request->user()->id,
            'company_id' => $request->user()->company_id,
        ]);

        return response()->json(['success' => true, 'todo' => $todo], 201);
    }

    public function update(Request $request, Todo $todo): JsonResponse
    {
        $this->ensureOwner($request, $todo);
        $todo->update($this->validatedData($request));

        return response()->json(['success' => true, 'todo' => $todo->fresh()]);
    }

    public function toggle(Request $request, Todo $todo): JsonResponse
    {
        $this->ensureOwner($request, $todo);
        $completed = $request->boolean('is_completed');
        $todo->update([
            'is_completed' => $completed,
            'completed_at' => $completed ? now() : null,
        ]);

        return response()->json(['success' => true, 'todo' => $todo->fresh()]);
    }

    public function destroy(Request $request, Todo $todo): JsonResponse
    {
        $this->ensureOwner($request, $todo);
        $todo->delete();

        return response()->json(['success' => true]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'priority' => ['required', 'in:low,normal,high'],
            'visibility' => ['required', 'in:private,public'],
            'due_date' => ['required', 'date'],
        ]);
    }

    private function ensureOwner(Request $request, Todo $todo): void
    {
        abort_unless($todo->user_id === $request->user()->id, 403);
    }
}
