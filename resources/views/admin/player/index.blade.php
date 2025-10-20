@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Player Lists</li>
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
                    @can("owner_access")
                     <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('admin.players.create') }}" class="btn btn-success" style="width: 100px;">
                            <i class="fas fa-plus text-white mr-2"></i>Create
                        </a>
                    </div>
                    @endcan
                    <div class="card" style="border-radius: 20px;">
                        <div class="card-header">
                            <h3>Player Lists</h3>
                        </div>
                        <div class="card-body">
                            <table id="mytable" class="table table-bordered table-hover">
                                <thead class="text-center">
                                    <th>#</th>
                                    <th>PlayerID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Balance</th>
                                    @can('owner_access')
                                    <th>Action</th>
                                    <th>Transaction</th>
                                    @endcan
                                </thead>
                                <tbody>
                                    @if (isset($users))
                                        @if (count($users) > 0)
                                            @foreach ($users as $user)
                                                <tr class="text-center">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <span class="d-block">{{ $user->user_name }}</span>

                                                    </td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->phone }}</td>
                                                    <td>
                                                        <small
                                                            class="badge bg-gradient-{{ $user->status == 1 ? 'success' : 'danger' }}">{{ $user->status == 1 ? 'active' : 'inactive' }}</small>
                                                    </td>
                                                    <td class="text-bold">{{ number_format($user->balance) }}</td>
                                                    @can('owner_access')
                                                    <td>
                                                        @if ($user->status == 1)
                                                            <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $user->id }}').submit();"
                                                                class="me-2" href="#" data-bs-toggle="tooltip"
                                                                data-bs-original-title="Active Player">
                                                                <i class="fas fa-user-check text-success"
                                                                    style="font-size: 20px;"></i>
                                                            </a>
                                                        @else
                                                            <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $user->id }}').submit();"
                                                                class="me-2" href="#" data-bs-toggle="tooltip"
                                                                data-bs-original-title="InActive Player">
                                                                <i class="fas fa-user-slash text-danger"
                                                                    style="font-size: 20px;"></i>
                                                            </a>
                                                        @endif
                                                        <form class="d-none" id="banUser-{{ $user->id }}"
                                                            action="{{ route('admin.players.ban', $user->id) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('PUT')
                                                        </form>
                                                        <a class="me-1"
                                                            href="{{ route('admin.players.change-password', $user->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="Change Password">
                                                            <i class="fas fa-lock text-info" style="font-size: 20px;"></i>
                                                        </a>
                                                        <a class="me-1" href="{{ route('admin.players.edit', $user->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Edit Player">
                                                            <i class="fas fa-edit text-info" style="font-size: 20px;"></i>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.players.cash-in', $user->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="Deposit To Player"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-plus text-white me-1"></i>
                                                            Deposit
                                                        </a>
                                                        <a href="{{ route('admin.players.cash-out', $user->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="Withdraw From Player"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-minus text-white me-1"></i>
                                                            Withdrawl
                                                        </a>
                                                        <a href="{{ route('admin.players.logs', $user->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Transfer Logs"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-right-left text-white me-1"></i>
                                                            Logs
                                                        </a>
                                                        <a href="{{ route('admin.players.report', $user->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Player Reports"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-chart-bar text-white me-1"></i>
                                                            Reports
                                                        </a>
                                                    </td>
                                                    @endcan
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td col-span=8>
                                                    There was no Players.
                                                </td>
                                            </tr>
                                        @endif
                                    @endif

                                </tbody>

                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
@endsection
