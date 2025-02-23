<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        if ($user->role === 'reviewerA' || $user->role === 'reviewerB') {
            $todos = Todo::with('tasks')->get();
        } elseif ($user->role === 'revieweeB') {
            $todos = Todo::whereHas('user', function ($query) {
                $query->where('role', 'revieweeA');
            })->with('tasks')->get();
        } else {
            $todos = Todo::where('user_id', $user->id)->with('tasks')->get();
        }

        return response()->json($todos);
    }

    public function show($id)
    {
        $user = Auth::user();

        $todo = Todo::with('tasks')->find($id);

        if (!$todo) {
            return response()->json(['error' => 'Todo not found'], 404);
        }

        if ($user->role === 'reviewerA' || $user->role === 'reviewerB') {
            return response()->json($todo);
        } elseif ($user->role === 'revieweeB') {
            if ($todo->user->role !== 'revieweeA') {
                return response()->json(['error' => 'Unauthorized to view this Todo'], 403);
            }
            return response()->json($todo);
        } else {
            if ($todo->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized to view this Todo'], 403);
            }
            return response()->json($todo);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'revieweeA') {
            return response()->json(['error' => 'Unauthorized action. Only Reviewee A can create Todos.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'progress' => 'nullable|string', // Tambahkan progress sebagai string
        ]);

        $progress = $request->progress ? $this->convertToPercent($request->progress) : 0;

        $todo = Todo::create([
            'name' => $request->name,
            'user_id' => $user->id,
            'progress' => $progress,
            'status' => $progress == 100 ? 'completed' : 'in progress',
        ]);

        return response()->json(['success' => 'Todo berhasil ditambahkan!', 'todo' => $todo]);
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('query');

        if ($user->role === 'reviewerA' || $user->role === 'reviewerB') {
            $todos = Todo::where('name', 'LIKE', "%{$query}%")->with('tasks')->get();
        } elseif ($user->role === 'revieweeB') {
            $todos = Todo::where('name', 'LIKE', "%{$query}%")
                ->whereHas('user', function ($q) {
                    $q->where('role', 'revieweeA');
                })
                ->with('tasks')
                ->get();
        } else {
            $todos = Todo::where('name', 'LIKE', "%{$query}%")
                ->where('user_id', $user->id)
                ->with('tasks')
                ->get();
        }

        return response()->json(['todos' => $todos, 'query' => $query]);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $todo = Todo::findOrFail($id);

            if ($todo->user_id != $user->id) {
                return response()->json(['error' => 'Unauthorized action. You can only update your own Todos.'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'progress' => 'required|string', // Progress sebagai string
                'status' => 'required|string|in:in progress,completed',
            ]);

            $progress = $this->convertToPercent($request->progress);

            $todo->update([
                'name' => $request->name,
                'progress' => $progress,
                'status' => $request->status,
            ]);

            return response()->json($todo);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memperbarui Todo.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $todo = Todo::findOrFail($id);

        if ($todo->user_id != $user->id) {
            return response()->json(['error' => 'Unauthorized action. You can only delete your own Todos.'], 403);
        }

        $todo->delete();

        return response()->json(['success' => 'Todo berhasil dihapus']);
    }

    private function convertToPercent($input)
    {
        $input = trim($input);

        if (str_contains($input, '%')) {
            return min(100, max(0, (int)str_replace('%', '', $input)));
        } elseif (preg_match('/[Rp$]/', $input)) {
            $number = preg_replace('/[^0-9]/', '', $input);
            $max_value = 1000000; // Anggap Rp1.000.000 adalah 100%
            return min(100, max(0, ($number / $max_value) * 100));
        } else {
            return min(100, max(0, (int)$input));
        }
    }
}
