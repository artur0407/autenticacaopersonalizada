<x-layouts.main-layout pageTitle="Redefinir senha">

    <div class="container mt-5">

        <div class="row justify-content-center">
        
            <div class="col-6">

                <p class="display-6">DEFINIR NOVA SENHA</p>

                <form action="{{ route('reset_password_update') }}" method="post">

                    @csrf

                    {{-- campo escondido para manter o token --}}
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Defina a nova senha</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        @error('new_password')
                            <div class="text-danger"> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirmar a nova senha</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                        @error('new_password_confirmation')
                           <div class="text-danger"> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mt-4">
                        <div class="col">
                            <a href="{{ route('login') }}">Não quero alterar a senha</a>
                        </div>
                        <div class="col text-end">
                            <button type="submit" class="btn btn-secondary px-5">DEFINIR SENHA</button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

</x-layouts.main-layout>