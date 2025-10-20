@extends('layouts.master')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Game Types</li>
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
                                <h5 class="mb-0">Game Types Dashboard</h5>
                                <span class="badge badge-primary badge-lg">
                                    Total Types: {{ $gameTypes->total() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Name (MM)</th>
                                            <th>Image</th>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($gameTypes as $gameType)
                                        <tr>
                                            <td>{{ $loop->iteration + $gameTypes->firstItem() - 1 }}</td>
                                            <td><strong>{{ $gameType->code }}</strong></td>
                                            <td>{{ $gameType->name }}</td>
                                            <td>{{ $gameType->name_mm }}</td>
                                            <td class="text-center">
                                                <img src="{{ asset('assets/img/game_types/' . $gameType->img) }}" 
                                                     alt="{{ $gameType->name }}" 
                                                     width="60px" 
                                                     class="img-thumbnail">
                                            </td>
                                            <td class="text-center">{{ $gameType->order }}</td>
                                            <td>
                                                <span class="badge badge-{{ $gameType->status == 1 ? 'success' : 'danger' }}">
                                                    {{ $gameType->status == 1 ? 'Open' : 'Closed' }}
                                                </span>
                                            </td>
                                            <td>
                                                <!-- Toggle Game Type Status (Open/Close) -->
                                                <form action="{{ route('admin.game-types.toggleStatus', $gameType->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-{{ $gameType->status == 1 ? 'danger' : 'success' }} btn-sm">
                                                        <i class="fas fa-{{ $gameType->status == 1 ? 'lock' : 'unlock' }}"></i>
                                                        {{ $gameType->status == 1 ? 'Close Type' : 'Open Type' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No game types found.</p>
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
                                        Showing {{ $gameTypes->firstItem() ?? 0 }} to {{ $gameTypes->lastItem() ?? 0 }} of {{ $gameTypes->total() }} game types
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        {{ $gameTypes->links('pagination::bootstrap-4') }}
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

