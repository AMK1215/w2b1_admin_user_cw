@extends('layouts.master')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
@endsection
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        <li class="breadcrumb-item active">GSCPLUS GameList</li>
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

                    <div class="d-flex justify-content-end mb-3">

                    </div>
                    <div class="card " style="border-radius: 20px;">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Game List Dashboard</h5>
                                <span class="badge badge-primary badge-lg">
                                    Total Games: {{ $game_lists->total() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Game Name</th>
                                            <th>Game Type</th>
                                            <th>Provider</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                            <th>Hot Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($game_lists as $game_list)
                                        <tr>
                                            <td>{{ $loop->iteration + $game_lists->firstItem() - 1 }}</td>
                                            <td><strong>{{ $game_list->game_name }}</strong></td>
                                            <td>{{ $game_list->game_type }}</td>
                                            <td>{{ $game_list->provider }}</td>
                                            <td class="text-center">
                                                <img src="{{ $game_list->image_url }}" alt="{{ $game_list->game_name }}" width="80px" class="img-thumbnail">
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $game_list->status == 'ACTIVATED' ? 'success' : 'danger' }}">
                                                    {{ $game_list->status == 'ACTIVATED' ? 'Open' : 'Closed' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $game_list->hot_status == 1 ? 'warning' : 'secondary' }}">
                                                    {{ $game_list->hot_status == 1 ? 'Hot Game' : 'Normal' }}
                                                </span>
                                            </td>
                                            <td>
                                                <!-- Toggle Game Status (Open/Close) -->
                                                <form action="{{ route('admin.gameLists.toggleStatus', $game_list->id) }}" method="POST" style="display:inline;" class="mb-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-{{ $game_list->status == 'ACTIVATED' ? 'danger' : 'success' }} btn-sm">
                                                        <i class="fas fa-{{ $game_list->status == 'ACTIVATED' ? 'lock' : 'unlock' }}"></i>
                                                        {{ $game_list->status == 'ACTIVATED' ? 'Close Game' : 'Open Game' }}
                                                    </button>
                                                </form>
                                                
                                                <!-- Toggle Hot Status -->
                                                <form action="{{ route('admin.HotGame.toggleStatus', $game_list->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-{{ $game_list->hot_status == 1 ? 'secondary' : 'warning' }} btn-sm">
                                                        <i class="fas fa-{{ $game_list->hot_status == 1 ? 'fire-extinguisher' : 'fire' }}"></i>
                                                        {{ $game_list->hot_status == 1 ? 'Set Normal' : 'Set Hot' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No games found.</p>
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
                                        Showing {{ $game_lists->firstItem() ?? 0 }} to {{ $game_lists->lastItem() ?? 0 }} of {{ $game_lists->total() }} games
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        {{ $game_lists->links('pagination::bootstrap-4') }}
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

