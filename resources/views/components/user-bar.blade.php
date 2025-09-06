<div class="container-fluid bg-black">
    <div class="row">
        <div class="col p-3">
            <a href="{{ route('home') }}">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
            </a>
        </div>
        <div class="col p-3 text-end align-content-center">
            <div class="dropdown">
                <a href="#" class="btn btn-primary dropdown-toggle px-5" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user me-3"></i> {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu p-2" aria-labelledby="dropdownMenuLink">
                    <li><a class="dropdown-item" href="{{ route('profile') }}">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('logout') }}">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>