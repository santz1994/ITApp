<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

trait FlashMessageTrait
{
    /**
     * Flash success message
     */
    protected function flashSuccess($title, $message)
    {
        Session::flash('status', 'success');
        Session::flash('title', $title);
        Session::flash('message', $message);
    }

    /**
     * Flash error message
     */
    protected function flashError($title, $message)
    {
        Session::flash('status', 'error');
        Session::flash('title', $title);
        Session::flash('message', $message);
    }

    /**
     * Flash warning message
     */
    protected function flashWarning($title, $message)
    {
        Session::flash('status', 'warning');
        Session::flash('title', $title);
        Session::flash('message', $message);
    }

    /**
     * Flash info message
     */
    protected function flashInfo($title, $message)
    {
        Session::flash('status', 'info');
        Session::flash('title', $title);
        Session::flash('message', $message);
    }
}