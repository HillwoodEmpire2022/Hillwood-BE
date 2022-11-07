<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Subscriber;
use App\Models\Channel;

class SubsribeController extends Controller
{
    
    /**
     * @OA\Post(
     *      path="/api/Subscribe/Channel/{id}",
     *      operationId="subscribeChannel",
     *      tags={"subscriber"},
     *      summary="subscribe new subscriber",
     *      description="Logged in user can subscribe new channel",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Channel id you need to subscribe",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
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

    public function createSubscribe($id)
    {
        $user_id = Auth()->user()->id;
        $cha = Channel::all()->where('id', $id)->where('channel_user', $user_id);
        if($cha->count() == 1)
        {
            return response()->json(['message'=>"You can't subscribe your own channel"], 200);
        }
        $chanel = Subscriber::all()->where('subs_channel', $id)->where('subs_user', $user_id);
        if($chanel->count() != 1)
        {
            $sub = new Subscriber();
            $sub->subs_channel = $id;
            $sub->subs_user = $user_id;
            $sub->save();

            return response()->json([
                'message'=>'channel subscribed successful',
                'Subscriber'=>$sub
            ],201);
        }
        else
        {
            return response()->json([
                'message'=>'You already subscribed this channel',
                "Channel"=>$chanel
        ], 200);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/Unsubscribe/Channel/{id}",
     *      operationId="unsubscribeChannel",
     *      tags={"subscriber"},
     *      summary="Unsubscriber channel",
     *      description="Logged in user can unsubscribe any chaannel he/she subscribed",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Channel id you need to unsubscribe",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
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

    public function createUnsubscribe($id)
    {
        $user_id = Auth()->user()->id;
        $cha = Channel::all()->where('id', $id)->where('channel_user', $user_id);
        if($cha->count() == 1)
        {
            return response()->json(['message'=>"You're owner of selected channel"], 200);
        }
        $chanel =  DB::table('subscribers')->where('subs_channel', $id)->where('subs_user', $user_id);
        if($chanel->count() == 1)
        {
            $chanel->delete();
            return response()->json([
                "Message"=>"Channel unsubscribed successful"
            ],200);
        }
        else
        {
            return response()->json(['message'=>"You're not subscriber of this channel"], 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/Channel/Subscribers/{id}",
     *      operationId="channelSubscribers",
     *      tags={"subscriber"},
     *      summary="get channel subscribers",
     *      description="Get any Channel subscribers",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Channel id you need to show subscribers.",
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
     *          description="Successful operation"
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

    public function getChannelSubscribers($id)
    {
        $channel = DB::table('channels')->where('id',$id)->get('channel_name');
        // return $channel;
        $results = DB::table('subscribers')->join('channels', function($join){
            $join->on('channels.id', '=', 'subscribers.subs_channel');})
            // ->join('users', function($join2){
            //     $join2->on('users.id', '=', 'subscribers.subs_user');})
            ->where('channels.id', $id)
            ->get(['channels.channel_name as Channel','channels.channel_profile as Profile']);

        if ($results->count()!=0) {
            return response()->json([
                "Channel"=>$channel,
                "Subscribers"=>$results->count()
            ],200);
        }else {
            return response()->json([
                "Message"=>"No subscriber found on selected channel",
            ],200);
        }
    }


    
}
