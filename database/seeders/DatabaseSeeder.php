<?php
namespace Database\Seeders;
use App\Models\{User,ServiceCategory,Service,WorkerProfile};use Illuminate\Database\Seeder;use Illuminate\Support\Facades\Hash;use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder {public function run():void{
$admin=User::firstOrCreate(['email'=>'admin@example.com'],['name'=>'Platform Admin','password'=>Hash::make('password'),'role'=>'admin']);
$customer=User::firstOrCreate(['email'=>'customer@example.com'],['name'=>'Demo Customer','password'=>Hash::make('password'),'role'=>'customer']);
$worker=User::firstOrCreate(['email'=>'worker@example.com'],['name'=>'Amit Kumar','phone'=>'9876543210','password'=>Hash::make('password'),'role'=>'worker']);
WorkerProfile::firstOrCreate(['user_id'=>$worker->id],['bio'=>'Experienced home service professional.','city'=>'Varanasi','state'=>'Uttar Pradesh','pincode'=>'221001','experience_years'=>5,'service_radius_km'=>15,'verification_status'=>'approved','is_available'=>true,'hourly_rate'=>350]);
$items=['Electrician'=>[['Fan and Light Repair',499],['Wiring Inspection',699]],'Plumber'=>[['Tap and Pipe Repair',399],['Bathroom Plumbing',599]],'Carpenter'=>[['Furniture Repair',499]],'Home Cleaning'=>[['One Room Cleaning',799],['Kitchen Deep Cleaning',1299]],'AC Repair'=>[['AC Inspection and Service',699]],'Appliance Repair'=>[['Washing Machine Repair',599]]];
foreach($items as $category=>$services){$cat=ServiceCategory::firstOrCreate(['slug'=>Str::slug($category)],['name'=>$category,'description'=>'Trusted '.$category.' services','image'=>'/assets/images/service-default.svg','is_active'=>true]);foreach($services as [$name,$price])Service::firstOrCreate(['slug'=>Str::slug($name)],['category_id'=>$cat->id,'name'=>$name,'description'=>'Professional '.$name.' by a verified local service provider.','base_price'=>$price,'duration_minutes'=>60,'image'=>'/assets/images/service-default.svg','is_active'=>true]);}
}}
