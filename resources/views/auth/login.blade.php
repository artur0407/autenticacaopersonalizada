<x-layouts.main-layout pageTitle="Login">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-6">
                <div class="card p-5">
                    <p class="display-6 text-center">LOGIN</p>

                    <form action="{{ route('authenticate') }}" method="post">

                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password">
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mt-4">
                            <div class="col">
                                <div class="mb-3">
                                    <a href="{{ route('register') }}">Não tenho conta de usuário</a>
                                </div>
                                <div>
                                    <a href="{{ route('forgot_password') }}">Esqueci a minha senha</a>
                                </div>
                            </div>
                            <div class="col text-end align-self-center">
                                <button type="submit" class="btn btn-secondary px-5">ENTRAR</button>
                            </div>
                        </div>

                    </form>

                    @if(session('invalid_login'))
                        <div class="alert alert-danger text-center mt-3">
                           {{ session('invalid_login') }}
                        </div>
                    @endif

                    @if(session('success'))
                       <p class="mt-3 alert alert-success text-center p-2">
                            Senha redefinida com sucesso
                       </p>
                    @endif

                    @if(session('account_deleted'))
                       <p class="mt-3 alert alert-success text-center p-2">
                            Conta de usuário removida com sucesso
                       </p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-layouts.main-layout>