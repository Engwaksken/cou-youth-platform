@extends('admin.layout')

@section('title', 'System Health')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">System Health</h1>
            <p class="text-muted mb-0">Release {{ $release['version'] }} · Build {{ $release['build'] }}</p>
        </div>
    </div>

    <div class="row g-3">
        @foreach($checks as $name => $check)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-start border-4 {{ $check['ok'] ? 'border-success' : 'border-danger' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="h6 text-uppercase">{{ str_replace('_', ' ', $name) }}</h2>
                                <p class="mb-0">{{ $check['message'] }}</p>
                            </div>
                            <span class="badge {{ $check['ok'] ? 'bg-success' : 'bg-danger' }}">{{ $check['ok'] ? 'Healthy' : 'Action needed' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
