<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function __construct()
    {
        $roles = ['patient', 'med_provider'];
        $this->middleware('role:' . implode('|', $roles));
    }

    public function fetchMessages($id)
    {
        // $unreadMsgs = Message::select(DB::raw('`from_id` as sender_id, count(`from_id`) as messages_count'))->where('to_id', $id)->where('read', false)->groupBy('from_id')->get();

        // Message::where('from_id', $id)->where('to_id', auth()->user()->id)->update(['read' => true]);
        $messages = Message::where('from_id', auth()->user()->id)->orwhere('to_id', $id)->with('images')->get();

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
        } else if (auth()->user()->roles[0]->name == 'patient' && $id == 1) {
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

        $message = new Message();
        if (!$request->text && !$request->image) {
            return response()->json([
                'status' => false,
                'message' => 'Message can not be empty',
            ], 400);
        }

        if ($request->text) {
            $validate = $this->validator($request);
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validate->errors()->messages(),
                ], 400);
            }
            $this->store($request, $message, $id);
        } elseif ($request->has('image')) {
            $image = $request->image;

            // convert base64 to image
            $decodedImage = base64ToFile($image);
            $decodedImageName = $decodedImage->hashName();

            //Move the image to a specific directory
            $destinationPath = $decodedImage->storeAs('public/messages/images', $decodedImageName);

            $message->from_id = auth()->user()->id;
            $message->to_id = $id;
            $message->image = $destinationPath;
            $message->save();
            Image::create([
                'imageable_id' => $message->id,
                'imageable_type' => 'App\Models\Message',
                'path' => $destinationPath,
            ]);
        }

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
            'text' => 'string',
        ]);
    }

    /**
     * get unread messages
     *
     * @param Request $request
     */
    public function getUnreadMessages()
    {
        // $unreadMsgs = Message::select(DB::raw('`from_id` as sender_id, count(`from_id`) as messages_count,'))->where('to_id', auth()->user()->id)->where('read', false)->groupBy('from_id')->get();

        $unreadMsgs = Message::where('to_id', auth()->user()->id)->where('read', false)->get();
        $unreadMsgsCount = $unreadMsgs->count();

        return response()->json([
            'status' => true,
            'unreadMsgs' => $unreadMsgs,
            'unreadMsgsCount' => $unreadMsgsCount,
        ], 200);
    }

    /**
     * mark message as read
     *
     * @param Request $request
     */
    public function markMessageAsRead($id)
    {
        $message = Message::find($id);
        if (!$message) {
            return response()->json([
                'status' => false,
                'message' => 'Message not found',
            ], 400);
        }

        if ($message->to_id !== auth()->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'You can not mark this message as read',
            ], 400);

        }

        $message->read = true;
        $message->save();
        return response()->json([
            'status' => true,
            'message' => 'Message marked as read',
        ], 200);
    }

    /**
     * delete message
     *
     * @param Request $request
     */
    public function deleteMessage($id)
    {
        $message = Message::find($id);
        if (!$message) {
            return response()->json([
                'status' => false,
                'message' => 'Message not found',
            ], 400);
        }

        if ($message->to_id !== auth()->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'You can not delete this message',
            ], 400);

        }

        $message->delete();

        return response()->json([
            'status' => true,
            'message' => 'Message deleted',
        ], 200);
    }

    /**
     * get all messages
     *
     * @param Request $request
     */
    public function getAllMessages()
    {
        $messages = Message::where('from_id', auth()->user()->id)->orwhere('to_id', auth()->user()->id)->get();

        return response()->json([
            'status' => true,
            'messages' => $messages,
        ], 200);
    }
}
