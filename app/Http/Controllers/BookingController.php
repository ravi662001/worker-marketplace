<?php
namespace App\Http\Controllers;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Review;
use App\Services\BookingService;
use Illuminate\Http\Request;
class BookingController extends Controller {
 public function create(Service $service){$workers=\App\Models\User::where('role','worker')->whereHas('workerProfile',fn($q)=>$q->where('verification_status','approved')->where('is_available',true))->with('workerProfile')->get(); return view('bookings.create',compact('service','workers'));}
 public function store(Request $request, Service $service, BookingService $bookingService){$data=$request->validate(['worker_id'=>['required','exists:users,id'],'customer_name'=>['required','string','max:120'],'customer_phone'=>['required','string','max:20'],'address'=>['required','string','max:500'],'city'=>['required','string','max:100'],'pincode'=>['required','digits:6'],'booking_date'=>['required','date','after_or_equal:today'],'start_time'=>['required','date_format:H:i'],'notes'=>['nullable','string','max:1000']]); $booking=$bookingService->create($request->user(),$service,$data); return redirect()->route('bookings.index')->with('success','Booking request submitted. Booking ID: '.$booking->booking_code);}
 public function index(Request $request){$bookings=Booking::with(['service','worker'])->where('customer_id',$request->user()->id)->latest()->paginate(10);return view('bookings.index',compact('bookings'));}
 public function review(Request $request, Booking $booking){abort_unless($booking->customer_id===$request->user()->id && $booking->status==='completed' && !$booking->review,403);$data=$request->validate(['rating'=>['required','integer','between:1,5'],'comment'=>['nullable','string','max:1000']]);Review::create($data+['booking_id'=>$booking->id,'customer_id'=>$request->user()->id,'worker_id'=>$booking->worker_id]);return back()->with('success','Thank you for your review.');}
}
