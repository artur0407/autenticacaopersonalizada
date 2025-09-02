<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\password;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        // validacao do form
        $credentials = $request->validate(
            [
                'name' => 'required|min:3|max:30',
                'password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
            [
                'name.required' => 'O usuário é obrigatório' ,
                'name.min' => 'O usuário deve ter no mínimo :min caracteres',
                'name.max' => 'O usuário deve ter no maximo :max caracteres',
                'password.required' => 'A senha é obrigatória',
                'password.min' => 'A senha deve ter no mínimo :min caracteres',
                'password.max' => 'A senha deve ter no maximo :max caracteres',
                'password.regex' => 'A senha deve conter pelo menos uma letra maiúscula, uma letra minúscula e um número'
            ]
        );

        // login tradicional do laravel (em caso de email and password )
        // if (Auth::attempt($credentials))
        //     $request->session()->regenerate();
        //     return redirect()->route('home');
        // } 

        // verificar se o user existe
        $user = User::where('name', $credentials['name'])
            ->where('active', true)
            ->where(function($query){
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '<=', now());
            })
            ->whereNotNull('email_verified_at')
            ->whereNull('deleted_at')
            ->first();
        
        // verifica se o user existe
        if (!$user) {
            return back()->withInput()->with([
                'invalid_login' => 'Login inválido'
            ]);
        }

        // verificar se password é valida
        if(!password_verify($credentials['password'], $user->password)) {
            return back()->withInput()->with([
                'invalid_login' => 'Login inválido'
            ]);
        }

        // atualizar o ultimo login (last_login_at)
        $user->last_login_at = now();
        $user->blocked_until = null;
        $user->save();

        // efetuar o login
        $request->session()->regenerate();
        Auth::login($user);

        // redirecionar para a página que usuário estava tentando acessar ou a home se a rota não existir
        return redirect()->intended(route('home'));
    }
}