<?php

namespace App\Http\Controllers;

use App\User;
use App\Activity;
use Illuminate\Http\Request;

class ProfilesController extends Controller
{
    public function show(User $user)
    {
        return View('profiles.show', [
            'profileUser' => $user,
            'feed'        => Activity::feed($user)
        ]);
    }
}
