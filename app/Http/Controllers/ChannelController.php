<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Channel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChannelController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/Channel/All",
     *      operationId="getAllChannel",
     *      tags={"channel"},
     *      summary="Get list of channels",
     *      description="Returns list of channels",
     *      security={{"bearer":{}}},
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful Query",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad user Input",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     *     )
     */

    public function getAllChannels()
    {
        $results = DB::table('channels')->join('users', function($join){
            $join->on('users.id', '=', 'channels.channel_user');})
            ->get(['channels.id as channel','users.fname as channel_owner_fname','users.lname as channel_owner_lname','channels.channel_name','channels.channel_profile','channels.created_at']);
            if($results->count() != 0)
            {
                return response()->json(['channels'=>$results,], 200);
            }
            else
            {
                return response()->json(["Message"=>"No channel found"],200);
            }
    }
    /**
     * @OA\Post(
     *      path="/api/Channel/Create",
     *      operationId="createChannel",
     *      tags={"channel"},
     *      summary="create new channel",
     *      description="create a new channel to the site",
     *      security={{"bearer":{}}},
     *      @OA\RequestBody(
    *         @OA\MediaType(
    *            mediaType="multipart/form-data",
    *            @OA\Schema(
    *               type="object",
    *               @OA\Property(property="channel_name", type="string",description=" Channel title."),
    *               @OA\Property(property="channel_profile", type="file",description=" Choose image cahnnel"),
    *            ),
    *        ),
    *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad user Input",
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Operation ok",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     *     )
     */

    public function createChannel(Request $request)
    {
        $user_id = Auth()->user()->id;
        $validator = Validator::make($request->all(),[
            'channel_name' => 'required|min:4|string',
            'channel_profile' =>'image|mimes:png,jpg,jpeg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        if($request->hasFile('channel_profile'))
        {
            $channel = Channel::create([
                'channel_name'=>$request->channel_name,
                'channel_user'=>$user_id,
                'channel_profile'=>$request->file('channel_profile')->getClientOriginalName(),
            ]);
            $image = $request->file('channel_profile');
            $destinationPath = 'assets/img/channel';
            $profileImage = $request->channel_profile->getClientOriginalName();
            $image->move($destinationPath, $profileImage);

            return response()->json([
                'message'=> 'Channel created successfuly',
                'channel' =>$channel
            ],201);
        }
        else
        {
            return response()->json(['message'=>'please choose channel profile'], 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/Channel/Id/{id}",
     *      operationId="getChannelById",
     *      tags={"channel"},
     *      summary="Get Channel using id",
     *      description="Search Channel By Id",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Channel id you need.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad user Input",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     *     )
     */

    public function getChannelById($id)
    {
        $results = DB::table('channels')->join('users', function($join){
            $join->on('users.id', '=', 'channels.channel_user');})
            ->where('channels.id', $id)
            ->get(['channels.id as channel','users.fname as channel_owner_fname','users.lname as channel_owner_lname','channels.channel_name','channels.channel_profile','channels.created_at']);

        if ($results->count()!=0) {
            return response()->json([
                "Channel"=>$results
            ],200);
        }else {
            return response()->json(["Message"=>"No channel found"],200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/Channel/User",
     *      operationId="getChannelByUser",
     *      tags={"channel"},
     *      summary="Get your own Channel",
     *      description="Loggedin user can show his/her channel info",
     *      security={{"bearer":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad user Input",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     *     )
     */

    public function getChannelByUser()
    {
        $results = DB::table('channels')->where('channel_user', Auth()->User()->id)
        ->get(['channel_name', 'channel_profile','created_at']);
        if ($results->count()>0) {
            return response()->json(["Channels"=>$results],200);
        }else {
            return response()->json(["Message"=>"You don't have any channel"],422);
        }
    }


    /**
     * @OA\post(
     *      path="/api/Channel/Update/{id}",
     *      operationId="channelUpdate",
     *      tags={"channel"},
     *      summary="update your channel",
     *      description="Update your channel information",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="channel id to update.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *       ),
     *      @OA\RequestBody(
    *         @OA\MediaType(
    *            mediaType="multipart/form-data",
    *            @OA\Schema(
    *               type="object",
    *               @OA\Property(property="channel_name", type="string",description=" Channel name."),
    *               @OA\Property(property="channel_profile", type="file",description=" Choose channel profile"),
    *            ),
    *        ),
    *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad user Input",
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Operation ok",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     *     )
     */

    public function channelUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'channel_name' => 'required|min:4|string',
            'channel_profile' =>'image|mimes:png,jpg,jpeg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if($request->hasFile('channel_profile'))
        {
            $channel = Channel::find($id);
            $channel->channel_name = $request->channel_name;
            $channel->channel_profile = $request->file('channel_profile')->getClientOriginalName();
            $channel->save();

            $image = $request->file('channel_profile');
            $destinationPath = 'assets/img/channel';
            $profileImage = $request->channel_profile->getClientOriginalName();
            $image->move($destinationPath, $profileImage);

            return response()->json([
                'message'=> 'Channel update successfuly',
                'channel' =>$channel
            ],201);
        }
        else
        {
            return response()->json(['message'=>'please choose channel profile'], 204);
        }
        
    }

    /**
     * @OA\Delete(
     *      path="/api/Channel/Delete/{id}",
     *      operationId="channelDelete",
     *      tags={"channel"},
     *      summary="delete Channel",
     *      description="delete your channels using channel id",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="channel id you need to remove.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad user Input",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     *     )
     */

    public function channelDelete($id)
    {
        if(Auth()->user()->is_admin)
        {
            $result = DB::table('channels')->where('id', $id);
        }
        else
        {
            $result = DB::table('channels')->where('id', $id)->where('channel_user', Auth()->user()->id);
        }
        if ($result->count()!=0) {
            $result->delete();
            return response()->json([
                "Message"=>"Channel deleted successful"
            ],200);
        }else {
            return response()->json(["Message"=>"This Channel not exits"],200);
        }
    }

}
