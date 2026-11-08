<?php
namespace App\Http\Controllers;
use App\Models\WorkerProfile;
use App\Models\Booking;
use Illuminate\Http\Request;
class WorkerController extends Controller {
 public function registerForm(){return view('worker.register');}
 public function register(Request $request){$data=$request->validate(['bio'=>['required','string','max:1000'],'city'=>['required','string','max:100'],'state'=>['required','string','max:100'],'pincode'=>['required','digits:6'],'experience_years'=>['required','integer','min:0','max:60'],'service_radius_km'=>['required','integer','min:1','max:100']]);WorkerProfile::updateOrCreate(['user_id'=>$request->user()->id],$data+['verification_status'=>'pending','is_available'=>false]);$request->user()->update(['role'=>'worker']);return redirect()->route('worker.dashboard')->with('success','Worker application submitted. Admin approval is required.');}
 public function dashboard(Request $request){$profile=$request->user()->workerProfile;$bookings=Booking::with(['service','customer'])->where('worker_id',$request->user()->id)->latest()->paginate(12);return view('worker.dashboard',compact('profile','bookings'));}
 public function updateStatus(Request $request, Booking $booking){abort_unless($booking->worker_id===$request->user()->id,403);$data=$request->validate(['status'=>['required','in:accepted,rejected,on_the_way,in_progress,completed']]);$allowed=['pending'=>['accepted','rejected'],'accepted'=>['on_the_way','in_progress'],'on_the_way'=>['in_progress'],'in_progress'=>['completed']];abort_unless(in_array($data['status'],$allowed[$booking->status]??[],true),422,'Invalid booking status transition.');$booking->update(['status'=>$data['status']]);return back()->with('success','Booking status updated.');}
}
