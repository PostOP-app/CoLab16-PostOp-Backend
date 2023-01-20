<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function fetchMessages($id)
    {
        // $unreadMsgs = Message::select(DB::raw('`from_id` as sender_id, count(`from_id`) as messages_count'))->where('to_id', $id)->where('read', false)->groupBy('from_id')->get();

        Message::where('from_id', $id)->where('to_id', auth()->user()->id)->update(['read' => true]);
        $messages = Message::where('from_id', auth()->user()->id)->orwhere('to_id', $id)->get();

        return response()->json([
            'status' => true,
            'messages' => $messages,
        ], 200);
    }

    public function sendMessage(Request $request, $id)
    {
        // check if user id exists
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 400);
        } else if (auth()->user()->roles[0]->name == 'Patients' && $id == 1) {
            return response()->json([
                'status' => false,
                'message' => 'You can not send message to admin',
            ], 400);
        } else if (auth()->user()->id == $id) {
            return response()->json([
                'status' => false,
                'message' => 'You can not send message to yourself',
            ], 400);
        }

        $validate = $this->validator($request);
        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->messages(),
            ], 400);
        }

        $message = new Message();
        $this->store($request, $message, $id);

        return response()->json([
            'status' => true,
            'message' => $message,
        ], 200);
    }

    /**
     *  store message
     * @param Request $request
     */
    public function store(Request $request, $message, $id)
    {
        $message->from_id = auth()->user()->id;
        $message->to_id = $id;
        $message->text = $request->text;
        $message->save();
    }

    /**
     * validate message
     *
     * @param Request $request
     */
    public function validator(Request $request)
    {
        return Validator::make($request->all(), [
            // 'to_id' => 'required|integer,exists:users,id',
            'text' => 'required|string',
        ]);
    }

    /**
     * get unread messages
     *
     * @param Request $request
     */
    public function getUnreadMessages()
    {
        $unreadMsgs = Message::where('to_id', auth()->user()->id)->where('read', false)->get();

        return response()->json([
            'status' => true,
            'unreadMsgs' => $unreadMsgs,
        ], 200);
    }
}
