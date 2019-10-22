<?php

namespace App\Http\Controllers;

class IndexController extends \App\Http\Controllers\Controller
{

    /**
     * Index method
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            return view('welcome', ['name' => 'James']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }

    }


}
