@extends('layouts.master')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Providers</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="card" style="border-radius: 20px;">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Providers Dashboard</h5>
                                <span class="badge badge-primary badge-lg">
                                    Total Providers: {{ $providers->total() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Provider</th>
                                            <th>Product Name</th>
                                            <th>Game Type</th>
                                            <th>Product Code</th>
                                            <th>Currency</th>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($providers as $provider)
                                        <tr>
                                            <td>{{ $loop->iteration + $providers->firstItem() - 1 }}</td>
                                            <td><strong>{{ $provider->provider }}</strong></td>
                                            <td>{{ $provider->product_name }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $provider->game_type }}</span>
                                            </td>
                                            <td>{{ $provider->product_code }}</td>
                                            <td>{{ $provider->currency }}</td>
                                            <td class="text-center">{{ $provider->order }}</td>
                                            <td>
                                                <span class="badge badge-{{ $provider->status == 'ACTIVATED' ? 'success' : 'danger' }}">
                                                    {{ $provider->status == 'ACTIVATED' ? 'Open' : 'Closed' }}
                                                </span>
                                            </td>
                                            <td>
                                                <!-- Toggle Provider Status (Open/Close) -->
                                                <form action="{{ route('admin.providers.toggleStatus', $provider->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-{{ $provider->status == 'ACTIVATED' ? 'danger' : 'success' }} btn-sm">
                                                        <i class="fas fa-{{ $provider->status == 'ACTIVATED' ? 'lock' : 'unlock' }}"></i>
                                                        {{ $provider->status == 'ACTIVATED' ? 'Close' : 'Open' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No providers found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer bg-white">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <p class="text-muted mb-0">
                                        Showing {{ $providers->firstItem() ?? 0 }} to {{ $providers->lastItem() ?? 0 }} of {{ $providers->total() }} providers
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        {{ $providers->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
@endsection

