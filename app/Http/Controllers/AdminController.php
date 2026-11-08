<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
class AdminController extends Controller {
 public function dashboard(){ $stats=['customers'=>User::where('role','customer')->count(),'workers'=>User::where('role','worker')->count(),'pending_workers'=>User::whereHas('workerProfile',fn($q)=>$q->where('verification_status','pending'))->count(),'bookings'=>Booking::count(),'completed'=>Booking::where('status','completed')->count(),'revenue'=>Booking::where('payment_status','paid')->sum('total_amount')];$workers=User::whereHas('workerProfile')->with('workerProfile')->latest()->paginate(12);return view('admin.dashboard',compact('stats','workers'));}
 public function approveWorker(Request $request, User $user){abort_unless($user->workerProfile,404);$data=$request->validate(['verification_status'=>['required','in:approved,rejected']]);$user->workerProfile->update(['verification_status'=>$data['verification_status'],'is_available'=>$data['verification_status']==='approved']);return back()->with('success','Worker verification updated.');}
 public function bookings(){ $bookings=Booking::with(['customer','worker','service'])->latest()->paginate(20);return view('admin.bookings',compact('bookings'));}
}
