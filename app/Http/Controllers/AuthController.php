<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:api',['except' =>['login', 'register']]);
    }
      /**
     *   @OA\Post(
     *   path="/api/User/Register",
     *   summary="Create Account",
     *   description="Fill in your information to register new account",
     *   operationId="register",
     *   tags={"auth"},
     *     @OA\Parameter(
     *          name="fname",
     *          description="Enter First Name",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="lname",
     *          description="Enter Last Name",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="email",
     *          description="Enter your Email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="phone",
     *          description="Enter User Phone Number Ex: 07***",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="gender",
     *          description="Enter User Gender",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="address",
     *          description="Enter your address",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="dob",
     *          description="Date of Birth",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="password",
     *          description="Enter Password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *          @OA\Parameter(
     *          name="password_confirmation",
     *          description="Confirm Password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="User Registered Successfull."
     *     ),
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
     * )
     */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'fname' => 'required|string',
            'lname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone|starts_with:078,072,073,079|min:10|max:10',
            'gender' => 'nullable',
            'address' => 'nullable',
            'dob' => 'nullable',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' =>bcrypt($request->password),
            'role'=>2]
        ));
        return response()->json([
            'message' => "Account created successfull",
            'info' => $user
        ],201);
    }


    /**
     * @OA\Post(
     * path="/api/v1/login",
     * summary="Login",
     * description="Login by email, password",
     * operationId="authLogin",
     * tags={"auth"},
     *      @OA\Parameter(
     *          name="email",
     *          description="Enter your Email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="password",
     *          description="Enter Your Password",
     
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfull logged in."
     *     ),
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
     * )
     */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$token=auth()->attempt($validator->validated())) {
            return response()->json(['message'=>'Incorrect Email or Password'], 401);
        }
        return $this->createNewToken($token);
    }

    public function createNewToken($token )
    {
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expired_in'=>auth()->factory()->getTTL()*60,
            'user'=>auth()->user()
        ],200);
    }

    /**
     * @OA\put(
     *      path="/api/v1/user/update",
     *      operationId="updateUser",
     *      tags={"auth"},
     *      summary="Update info",
     *      description="Update loggedin user and return user info", 
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *          name="name",
     *          description="Update your name",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="email",
     *          description="Update your email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="phone",
     *          description="Update your phone",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="gender",
     *          description="Update your gender",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="password",
     *          description="new password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="password_confirmation",
     *          description="confirm new password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *       ),
     *       @OA\Response(
     *          response=201,
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
     * )
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'gender' => 'required',
            'phone' => 'required|starts_with:078,072,073,079|min:10|max:10',
            'password' => 'required|min:6|confirmed',
        ]);
        if($validator->fails())
        {
            return response()->json([
                $validator->errors()
            ], 400);
        }
        $user = User::find(Auth()->user()->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->gender = $request->gender;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'info'=>$user,
            'message'=>'Informatin updated successfully'
        ], 201);
    }

    /**
     * @OA\get(
     *      path="/api/v1/user",
     *      operationId="getUserInfo",
     *      tags={"auth"},
     *      summary="Logged in user info",
     *      description="Returns logged in user information",
     *      security={{"bearer":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */

    public function userInformation()
    {
        return response()->json(auth()->user(),200);
    }
    
    
    /**
     * @OA\Get(
     *      path="/api/v1/allUser",
     *      operationId="getAllUser",
     *      tags={"participant"},
     *      summary="User List",
     *      description="Returns list of of all users not doctor and admin",
     *      security={{"bearer":{}}},
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
    public function allUser()
    {
        $users = User::all()->where('role_id',3);
        return response()->json($users,200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/logout",
     *      operationId="logout",
     *      tags={"auth"},
     *      summary="Get out of System",
     *      description="Take out of sytem the user",
     *      security={{"bearer":{}}},
     *      @OA\Response(
     *          response=200,
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
    
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => "User successfull logged out"],200);
    }
}
