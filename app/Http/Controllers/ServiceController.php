<?php
namespace App\Http\Controllers;
use App\Models\Service;
use Illuminate\Http\Request;
class ServiceController extends Controller {
 public function index(Request $request){$query=Service::with('category')->where('is_active',true); if($request->filled('q'))$query->where('name','like','%'.$request->q.'%'); if($request->filled('category'))$query->whereHas('category',fn($q)=>$q->where('slug',$request->category)); $services=$query->orderBy('name')->paginate(12)->withQueryString(); return view('services.index',compact('services'));}
 public function show(Service $service){abort_unless($service->is_active,404); $workers=\App\Models\User::where('role','worker')->whereHas('workerProfile',fn($q)=>$q->where('verification_status','approved')->where('is_available',true))->with('workerProfile')->get(); return view('services.show',compact('service','workers'));}
}
