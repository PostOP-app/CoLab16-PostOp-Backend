<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todos = Todo::where('user_id', auth()->user()->id)->latest()->paginate(15);
        return $todos;
    }

    /**
     * Create a new todo.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validateTodo = $this->validator($request);
        if ($validateTodo->fails()) {
            return response([
                'status' => false,
                'errors' => $validateTodo->errors()->messages(),
            ], 400);
        }

        $todo = new Todo();
        $this->store($request, $todo);

        return response([
            'status' => true,
            'message' => 'Todo created successfully',
            'data' => $todo,
        ], 201);
    }

    /**
     * Todo data validator
     * @param Request $request
     * @param array $customRules
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(Request $request)
    {
        // $status = ['pending', 'completed', 'in-progress'];

        return Validator::make($request->all(), [
            'title' => 'required|unique:todos,title|string|max:255',
            'description' => 'required|string|max:255',
            // 'status' => 'required' . Rule::in($status),
            // 'user_id' => 'required|integer|exists:users,id',
            'due_date' => 'required|date',
            // 'completed' => 'boolean',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($request, $todo)
    {
        $todo->title = $request->title;
        $todo->slug = Str::slug($request->title . '-' . time());
        $todo->description = $request->description;
        // $todo->completed = $request->completed;
        // $todo->status = "$request->status";
        $todo->user_id = auth()->user()->id;
        $todo->due_date = $request->due_date;

        $todo->save();
    }

    /**
     * Update a todo.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
        if ($request->title) {
            $request->validate([
                'title' => 'required|unique:todos,title|string|max:255',
            ]);

            $todo->title = $request->title;
            $todo->slug = Str::slug($request->title);
        }

        if ($request->description) {
            $request->validate([
                'description' => 'required|string|max:255',
            ]);

            $todo->description = $request->description;
        }

        if ($request->due_date) {
            $request->validate([
                'due_date' => 'required|date',
            ]);

            $todo->due_date = $request->due_date;
        }

        if ($request->status) {
            $request->validate([
                'status' => 'required|string|max:255',
            ]);

            $todo->status = $request->status;

            if ($request->status == 'completed') {
                $todo->completed = true;
            }
        }

        $todo->save();

        return response([
            'status' => true,
            'message' => 'Todo updated successfully',
            'data' => $todo,
        ], 200);
    }

    /**
     * Update a todo's status.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Todo $todo)
    {
        $todo->status = 'completed';
        $todo->completed = true;
        $todo->save();

        return response([
            'status' => true,
            'message' => 'Todo completed successfully',
            'data' => $todo,
        ], 200);
    }

    /**
     * Archive a todo.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function archive(Todo $todo)
    {
        $todo->delete();

        return response([
            'status' => true,
            'message' => 'Todo archived successfully',
        ], 200);
    }

    /**
     * Restore a todo.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function restore($slug)
    {
        $todo = Todo::withTrashed()->where('slug', $slug)->first();
        $todo->restore();

        return response([
            'status' => true,
            'message' => 'Todo restored successfully',
        ], 200);
    }

    /**
     * Forcefully delete a todo.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $todo = Todo::withTrashed()->where('slug', $slug)->first();
        $todo->forceDelete();

        return response([
            'status' => true,
            'message' => 'Todo deleted successfully',
        ], 200);
    }
}
