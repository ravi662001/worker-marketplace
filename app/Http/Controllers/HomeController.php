<?php
namespace App\Http\Controllers;
use App\Models\Service;
use App\Models\ServiceCategory;
class HomeController extends Controller { public function index(){ $categories=ServiceCategory::where('is_active',true)->withCount('services')->orderBy('name')->get(); $services=Service::where('is_active',true)->with('category')->latest()->take(8)->get(); return view('home',compact('categories','services')); } }
