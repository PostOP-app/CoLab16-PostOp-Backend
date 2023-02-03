<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class TodoController extends Controller
{

    /**
     * setup middleware
     * @return void
     */
    public function __construct()
    {
        $this->middleware('verify_patient_id')->only(['create', 'update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todos = Todo::where('user_id', auth()->user()->id)->latest()->paginate(15);
        return response([
            'status' => true,
            'data' => $todos,
        ]);
    }

    /**
     * fetch a patient.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchPatients()
    {
        // fetch all patients
        $patients = Role::where('name', 'patient')->first()->users()->latest()->paginate(15);
        return response([
            'status' => true,
            'data' => $patients,
        ]);
    }

    /**
     * fetch a patient's todos.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchPatientTodos()
    {
        $todos = Todo::where('patient_id', auth()->user()->id)->latest()->paginate(15);
        return response([
            'status' => true,
            'data' => $todos,
        ]);
    }

    /**
     * fetch a todo.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchTodo(Todo $todo)
    {
        if (!$todo) {
            return response([
                'status' => false,
                'message' => 'Todo not found',
            ], 404);
        }
        return response([
            'status' => true,
            'data' => $todo,
        ]);
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
            'tracker' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'times' => 'required|date',
            // 'status' => 'required' . Rule::in($status),
            'patient_id' => 'required|integer|exists:users,id',
            'due_date' => 'required|date|after_or_equal:today',
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
        $todo->tracker = $request->tracker;
        $todo->frequency = $request->frequency;
        $todo->times = $request->times;
        // $todo->completed = $request->completed;
        // $todo->status = "$request->status";
        $todo->provider_id = auth()->user()->id;
        $todo->patient_id = $request->patient_id;
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
            $validateTitle = Validator::make($request->all(), [
                'title' => 'required|unique:todos,title|string|max:255',
            ]);
            if ($validateTitle->fails()) {
                return response([
                    'status' => false,
                    'errors' => $validateTitle->errors()->messages(),
                ], 400);
            }

            $todo->title = $request->title;
            $todo->slug = Str::slug($request->title) . '-' . time();
        }

        if ($request->description) {
            $validateDesc = Validator::make($request->all(), [
                'description' => 'required|string|max:255',
            ]);
            if ($validateDesc->fails()) {
                return response([
                    'status' => false,
                    'errors' => $validateDesc->errors()->messages(),
                ], 400);
            }

            $todo->description = $request->description;
        }

        if ($request->tracker) {
            $validateTracker = Validator::make($request->all(), [
                'tracker' => 'required|string|max:255',
            ]);
            if ($validateTracker->fails()) {
                return response([
                    'status' => false,
                    'errors' => $validateTracker->errors()->messages(),
                ], 400);
            }

            $todo->tracker = $request->tracker;
        }

        if ($request->frequency) {
            $validateFreq = Validator::make($request->all(), [
                'frequency' => 'required|string|max:255',
            ]);
            if ($validateFreq->fails()) {
                return response([
                    'status' => false,
                    'errors' => $validateFreq->errors()->messages(),
                ], 400);
            }

            $todo->frequency = $request->frequency;
        }

        if ($request->times) {
            $validateTimes = Validator::make($request->all(), [
                'times' => 'required|date',
            ]);
            if ($validateTimes->fails()) {
                return response([
                    'status' => false,
                    'errors' => $validateTimes->errors()->messages(),
                ], 400);
            }

            $todo->times = $request->times;
        }

        if ($request->due_date) {
            $validateDD = Validator::make($request->all(), [
                'due_date' => 'required|date',
            ]);
            if ($validateDD->fails()) {
                return response([
                    'status' => false,
                    'errors' => $validateDD->errors()->messages(),
                ], 400);
            }

            $todo->due_date = $request->due_date;
        }

        if ($request->status) {
            $validateStatus = Validator::make($request->all(), [
                'status' => 'required|string|max:255',
            ]);
            if ($validateStatus->fails()) {
                return response([
                    'status' => false,
                    'errors' => $validateStatus->errors()->messages(),
                ], 400);
            }

            $todo->status = $request->status;

            if ($request->status == 'completed') {
                $todo->completed = true;
            }
        }

        $todo->save();
        // fetch updated todo
        $updatedTodo = Todo::find($todo->id);

        return response([
            'status' => true,
            'message' => 'Todo updated successfully',
            'data' => $updatedTodo,
        ], 200);
    }

    /**
     * Update a todo's status.
     *
     * @return \Illuminate\Http\Response
     */
    public function completeTodo(Todo $todo)
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
