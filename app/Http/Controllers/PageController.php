<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the about page.
     *
     * @return \Illuminate\Http\Response
     */
    public function about()
    {
        // Return the about page view
        return view('pages.about');
    }

    /**
     * Display the contact page.
     *
     * @return \Illuminate\Http\Response
     */
    public function contact()
    {
        // Return the contact page view
        return view('pages.contact');
    }

    /**
     * Display the privacy page.
     *
     * @return \Illuminate\Http\Response
     */
    public function privacy()
    {
        // Return the privacy page view
        return view('pages.privacy');
    }

    /**
     * Display the terms page.
     *
     * @return \Illuminate\Http\Response
     */
    public function terms()
    {
        // Return the terms page view
        return view('pages.terms');
    }

    /**
     * Display the help page.
     *
     * @return \Illuminate\Http\Response
     */
    public function help()
    {
        // Return the help page view
        return view('pages.help');
    }

    /**
     * Display the FAQ page.
     *
     * @return \Illuminate\Http\Response
     */
    public function faq()
    {
        // Return the FAQ page view
        return view('pages.faq');
    }

    /**
     * Display the shipping page.
     *
     * @return \Illuminate\Http\Response
     */
    public function shipping()
    {
        // Return the shipping page view
        return view('pages.shipping');
    }
    
    /**
     * Process contact form submission.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function contactSubmit(Request $request)
    {
        // Validate form data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        // TODO: Send email to admin
        // TODO: Save to database
        
        // Redirect back with success message
        return redirect()->back()->with('success', 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً');
    }
}
