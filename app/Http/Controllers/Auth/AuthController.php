<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Library\Imap\ImapException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function postLoginGMail(Request $request)
    {

        $connection = \App::make('Imap\Connection\GMail')
            ->setUsername($request->get('email'))
            ->setPassword($request->get('password'));

        try {
            $client = $connection->connect();
        }catch (ImapException $e){
            return $this->sendFailedLoginResponse($request);
        }

        $credentials = [
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ];

        Session::put('credentials', $credentials);

        return redirect('inbox');

    }
}