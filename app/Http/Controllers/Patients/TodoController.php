<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $todos = Todo::latest()->paginate(15);
        return $todos;
    }

    /**
     * Create a new resource.
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
     * Tag data validator
     * @param Request $request
     * @param array $customRules
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(Request $request)
    {
        $status = ['pending', 'completed', 'in-progress'];

        return Validator::make($request->all(), [
            'title' => 'required|unique|string|max:255',
            'description' => 'required|string|max:255',
            'status' => 'required' . Rule::in($status),
            'user_id' => 'required|integer|exists:users,id',
            'due_date' => 'required|date',
            'completed' => 'boolean',
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
        $todo->description = $request->description;
        $todo->completed = $request->completed;
        $todo->status = $request->status;
        $todo->user_id = $request->user_id;
        $todo->due_date = $request->due_date;

        $todo->save();
    }
}
