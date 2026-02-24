<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GenaralController extends Controller
{
    public function index(){


        return view('site.home');
    }

    public function about(){


        return view('site.about');
    }

    public function contact(){


        return view('site.contact');
    }

    public function sellerRegistration(){
        return view('site.seller-registration');
    }

    public function learnMore(){
        return view('site.learn-more');
    }

    public function home(){

        if (auth()->check()) {
                $user = auth()->user();

                if ($user->hasRole('Marketer')) {

                    if ($user && $user->marketer->is_blocked) {
                        Auth::guard('web')->logout();
                            return redirect()->route('blocked');
                    }
                    return redirect()->route('marketerDashboard');
                } elseif ($user->hasRole('Admin')) {
                    return redirect()->route('adminDashboard');
                } elseif ($user->hasRole('Seller')) {
                    return redirect()->route('sellerDashboard');
                } else {
                    return redirect()->route('setDashboard');
                }
        } else {
            return view('site.home');
        }
    }


    public function setDashboard(Request $request)
    {
        if (!auth()->check()) {
            return redirect('/');
        }

        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            return redirect()->route('adminDashboard');
        }

        if ($user->hasRole('Seller')) {
            return redirect()->route('sellerDashboard');
        }

        return redirect('/');
    }
}
