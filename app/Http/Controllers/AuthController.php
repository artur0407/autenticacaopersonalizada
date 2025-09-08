<?php

namespace App\Http\Controllers;

use App\Mail\NewUserConfirmation;
use App\Mail\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        if (!Hash::check($credentials['password'], $user->password)) {
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
        $user->password = $request->password;
        $user->token = Str::random(64);

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
        $user->active = 1;
        $user->save();

        // autenticação automática (login) do usuário confirmado
        Auth::login($user);

        // apresenta mensagem de sucesso
        return view('auth.new_user_confirmation');
    }

    public function profile(): View
    {
        return view('auth.profile');
    }

    public function change_password(Request $request)
    {
        $request->validate(
            [
                'current_password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'new_password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|different:current_password',
                'new_password_confirmation' => 'required|same:new_password'
            ],
            [
                'current_password.required' => 'A senha atual é obrigatória' ,
                'current_password.min' => 'A senha atual deve conter no mínimo :min caracteres',
                'current_password.max' => 'A senha atual deve conter no máximo :max caracteres',
                'current_password.regex' => 'A senha atual deve conter pelo menos uma letra maiúscula, uma letra minúscula e um número',
                'new_password.required' => 'A nova senha é obrigatória' ,
                'new_password.min' => 'A nova senha deve conter no mínimo :min caracteres',
                'new_password.max' => 'A nova senha deve conter no máximo :max caracteres',
                'new_password.regex' => 'A senha atual deve conter pelo menos uma letra maiúscula, uma letra minúscula e um número',
                'new_password.different' => 'A nova senha deve ser diferente da senha atual',
                'new_password_confirmation.required' => 'A confirmação da nova senha é obrigatória',
                'new_password_confirmation.same' => 'A confirmação da nova senha deve ser igual à nova senha',
            ]
        );

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->with([
                'server-error' => 'A senha atual não está correta'
            ]);
        }

        // atualizar a senha na base dados
        $user = Auth::user();
        $user->password = $request->new_password;
        $user->save();

        // atualizar a senha na sessão
        Auth::user()->password = $request->new_password;

        // apresenta uma mensagem de sucesso
        return redirect()->route('profile')->with([
            'success' => 'A senha foi atualizada com sucesso'
        ]);
    }

    public function forgot_password(): View
    {
        return view('auth.forgot_password');
    }

    public function send_reset_password_link(Request $request)
    {
       // validação form
       $request->validate(
            [
                'email' => 'required|email',
            ],
            [
                'email.required' => 'O email é opbrigatório' ,
                'email.email' => 'O email deve ser um endereço de email válido',
            ]
        );

       $generic_message = "Verifique a sua caixa de email para prosseguir com a recuperação da senha";

        // verificar se email existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with([
                'server_message' => $generic_message
            ]);
        }

        // criar o link com token para enviar no email
        $user->token = Str::random(64);

        $token_link = route('reset_password', ['token' => $user->token]);

        // envio de email com link para recuperar a senha
        $result = Mail::to($user->email)->send(new ResetPassword($user->name, $token_link));

        // verificar se o email foi enviado
        if (!$result) {
            return back()->with([
                'server_message' => $generic_message
            ]);
        }

        // guarda o token na base de dados
        $user->save();

        return back()->with([
            'server_message' => $generic_message
        ]);
    }

    public function reset_password($token): View | RedirectResponse
    {
        // verificar se o token é valido
        $user = User::where('token', $token)->first();

        if (!$user) {
            return redirect()->route('login');
        }

        return view('auth.reset_password', ['token' => $token]);
    }

    public function reset_password_update(Request $request): RedirectResponse
    {
        // form validation
        $request->validate(
            [
                'token' => 'required',
                'new_password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'new_password_confirmation' => 'required|same:new_password'
            ],
            [
                // não coloco mensagem para o token porque não é suposto o utilizador alterar o token
                // nem ter acesso a essa informação
                'new_password.required' => 'A nova senha deve conter no mínimo :min caracteres',
                'new_password.min' => 'A nova senha deve conter no mínimo :min caracteres',
                'new_password.max' => 'A nova senha deve conter no máximo :max caracteres',
                'new_password.regex' => 'A nova senha deve conter pelo menos uma letra maiúscula, uma letra minúscula e um número',
                'new_password_confirmation.required' => 'A confirmação da nova senha é obrigatória',
                'new_password_confirmation.same' => 'A confirmação da nova senha deve ser igual à nova senha',
            ]
        );

        // verifica se o token é válido
        $user = User::where('token', $request->token)->first();

        if (!$user) {
            return redirect()->route('login');
        }

        // actualizar a senha na base de dados
        $user->password = $request->new_password;
        $user->token = null;
        $user->save();

        return redirect()->route('login')->with([
            'success' => true
        ]);
    }
}