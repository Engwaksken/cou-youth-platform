<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller; use App\Models\{User,YouthProfile}; use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash; use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
 public function login(Request $r){$d=$r->validate(['email'=>'required|email','password'=>'required|string','device_name'=>'nullable|string|max:120']);$u=User::where('email',$d['email'])->first();if(!$u||!Hash::check($d['password'],$u->password))throw ValidationException::withMessages(['email'=>['The provided credentials are incorrect.']]);if(!method_exists($u,'createToken')) return response()->json(['message'=>'Laravel Sanctum is required for mobile authentication.'],500);$u->tokens()->where('name','mobile')->delete();$token=$u->createToken('mobile')->plainTextToken;return response()->json(['token'=>$token,'user'=>['id'=>$u->id,'name'=>$u->name,'email'=>$u->email]]);}
 public function logout(Request $r){$r->user()->currentAccessToken()?->delete();return response()->json(['message'=>'Signed out successfully.']);}
 public function me(Request $r){return response()->json(['user'=>$r->user(),'youth_profile'=>YouthProfile::where('user_id',$r->user()->id)->first()]);}
}
