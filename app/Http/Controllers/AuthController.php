<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller {
 public function showLogin(){return view('auth.login');}
 public function login(Request $request){$credentials=$request->validate(['email'=>['required','email'],'password'=>['required']]); if(!Auth::attempt($credentials,$request->boolean('remember'))) return back()->withErrors(['email'=>'Email or password is incorrect.'])->onlyInput('email'); $request->session()->regenerate(); return redirect()->intended(route('dashboard'));}
 public function showRegister(){return view('auth.register');}
 public function register(Request $request){$data=$request->validate(['name'=>['required','string','max:120'],'email'=>['required','email','max:190','unique:users'],'phone'=>['nullable','string','max:20'],'password'=>['required','confirmed',Password::min(8)]]); $user=User::create($data+['role'=>'customer']); Auth::login($user); return redirect()->route('home')->with('success','Your account has been created.');}
 public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('home');}
}
