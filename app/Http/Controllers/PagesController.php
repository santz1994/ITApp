<?php

namespace App\Http\Controllers;

class PagesController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function getTicketConfig()
  {
    $pageTitle = 'Admin Configurations';
    return view('admin.admin', compact('pageTitle'));
  }
}
