<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

class UserNotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return auth()->user()->unreadNotifications;
    }

    public function destroy(User $user, $notification_id)
    {
        auth()->user()->notifications()->findOrFail($notification_id)->markAsRead();
    }
}
