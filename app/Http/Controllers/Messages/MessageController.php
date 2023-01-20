<?php

namespace App\Http\Controllers\Messages;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

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

    public function sendMessage(Request $request)
    {
        $message = [
            'from_id' => auth()->user()->id,
            'to_id' => $request->to_id,
            'message' => $request->text,
        ];

        $message = Message::create($message);

        return response()->json([
            'status' => true,
            'message' => $message,
        ], 200);
    }
}
