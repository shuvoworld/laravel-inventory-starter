<x-layouts.app>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm mb-3">
                    <div class="card-header py-2">
                        <h2 class="h6 mb-0">My Info</h2>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Name</dt>
                            <dd class="col-sm-9">{{ auth()->user()->name }}</dd>

                            <dt class="col-sm-3">Email</dt>
                            <dd class="col-sm-9">{{ auth()->user()->email }}</dd>

                            <dt class="col-sm-3">Roles</dt>
                            <dd class="col-sm-9">{{ auth()->user()->roles->pluck('name')->join(', ') ?: '—' }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header py-2">
                        <h2 class="h6 mb-0">Quick Links</h2>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('settings.profile.edit') }}" class="btn btn-outline-primary btn-sm">Edit Profile</a>
                            <a href="{{ route('settings.password.edit') }}" class="btn btn-outline-secondary btn-sm">Change Password</a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark btn-sm">Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        @php($photo = auth()->user()->profile_photo_path ?? null)
                        @if($photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="Profile picture" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                                <span class="fw-semibold">{{ auth()->user()->initials() }}</span>
                            </div>
                        @endif
                        <div class="mt-2 small text-muted">Logged in as</div>
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
