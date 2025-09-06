<x-layouts.main-layout pageTitle="Email enviado">
  <div class="container mt-5">
        <div class="row">
            <div class="col text-center">
                <div class="card p-5 text-center">
                    <p class="display-6">Foi enviado um email de confirmação para:</p>
                    <p class="display-6 text-info fw-bold">{{ $email }}</p>
                    <p>Por favor confirme no link existente nesse email para poder concluir o registro.</p>
                    <div class="mt-5">
                        <a href="{{ route('login') }}" class="btn btn-secondary px-5">PÁGINA INICIAL</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.main-layout>