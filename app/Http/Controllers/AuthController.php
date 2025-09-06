<?php

namespace App\Http\Controllers;

use App\Mail\NewUserConfirmation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
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

    public function logout()
    {
        // logout
        Auth::logout();
        return redirect()->route('login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function storeUser(Request $request): RedirectResponse | View
    {
        // form validation
        $request->validate(
            [
                'name' => 'required|min:3|max:30|unique:users,name',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'password_confirmation' => 'required|same:password'
            ],
            [
                'name.required' => 'O usuário é obrigatório' ,
                'name.min' => 'O usuário deve ter no mínimo :min caracteres',
                'name.max' => 'O usuário deve ter no maximo :max caracteres',
                'name.unique' => 'Este nome não pode ser usado',
                'email.required' => 'O email é obrigatório' ,
                'email.email' => 'O email deve ser um endereço de email válido',
                'email.unique' => 'Este email não pode ser usado',
                'password.required' => 'A senha é obrigatória',
                'password.min' => 'A senha deve ter no mínimo :min caracteres',
                'password.max' => 'A senha deve ter no maximo :max caracteres',
                'password.regex' => 'A senha deve conter pelo menos uma letra maiúscula, uma letra minúscula e um número',
                'password_confirmation.required' => 'A confirmação de senha é obrigatória' ,
                'password_confirmation.same' => 'A confirmação da senha deve ser igual à senha' ,
            ]
        );

        // criação de novo usuário definindo token de verificação de email
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->token = Str::random(64);
        $user->active = 0;

        // gerar link
        $confirmation_link = route('newUserConfirmation', ['token' => $user->token]);

        // enviar email
        $result = Mail::to($user->email)->send(new NewUserConfirmation($user->name, $confirmation_link));

        // verificar se o email foi enviado com sucesso
        if (!$result) {
            return back()->withInput()->with([
                'server_error' => 'Ocorreu um erro ao enviar o email de confirmação'
            ]);
        }

        // criar o usuário na base de dados
        $user->save();

        // apresentar view de sucesso
        return view('auth.email_sent', ['email' => $user->email]);
    }

    public function newUserConfirmation($token)
    {
        // verificar se o token é válido
        $user = User::where('token', $token)->first();

        if (!$user) {
            return redirect()->route('login');
        }

        // confirmar o registro do usuário
        $user->email_verified_at = Carbon::now();
        $user->token = null;
        $user->save();

        // autenticação automática (login) do usuário confirmado
        Auth::login($user);

        // apresenta mensagem de sucesso
        return view('auth.new_user_confirmation');
    }
}