<x-layouts.main-layout pageTitle="Esqueci a senha">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-6">
                <div class="card p-5">
                    <p class="display-6 text-center">RECUPERAR SENHA</p>

                    <form action="{{ route('send_reset_password_link') }}" method="post">

                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Indique o seu email</label>
                            <input type="email" class="form-control" id="email" name="email">
                            @error('email')
                                 <div class="text-danger"> {{ $message }} </div>
                            @enderror
                        </div>

                        <div class="row mt-4">
                            <div class="col">
                                <div class="mb-3">
                                    <a href="{{ route('login') }}">Já sei a minha senha</a>
                                </div>
                            </div>
                            <div class="col text-end align-self-center">
                                <button type="submit" class="btn btn-secondary px-5">RECUPERAR</button>
                            </div>
                        </div>

                    </form>

                    @if (session('server_message'))
                        <div class="alert alert-primary text-center mt-4">
                            {{ session('server_message') }}
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="btn btn-primary px-5">Voltar</a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-layouts.main-layout>