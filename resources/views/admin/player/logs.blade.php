@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.players.index') }}">Player Lists</a></li>
                        <li class="breadcrumb-item active">Transfer Logs</li>
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
                    <div class="card" style="border-radius: 20px;">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3>Transfer Logs - {{ $player->user_name }}</h3>
                                <a href="{{ route('admin.players.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5>Player Information</h5>
                                    <p><strong>Name:</strong> {{ $player->name }}</p>
                                    <p><strong>Phone:</strong> {{ $player->phone }}</p>
                                    <p><strong>Current Balance:</strong> {{ number_format($player->balance, 2) }} MMK</p>
                                </div>
                            </div>

                            <table id="mytable" class="table table-bordered table-hover">
                                <thead class="text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($logs as $log)
                                        <tr class="text-center">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>
                                                @if($log->type == 'top_up')
                                                    <span class="badge badge-success">Top Up</span>
                                                @elseif($log->type == 'withdraw')
                                                    <span class="badge badge-warning">Withdraw</span>
                                                @else
                                                    <span class="badge badge-info">{{ ucfirst($log->type) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->fromUser)
                                                    {{ $log->fromUser->user_name }}
                                                    <br>
                                                    <small class="text-muted">{{ $log->fromUser->name }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->toUser)
                                                    {{ $log->toUser->user_name }}
                                                    <br>
                                                    <small class="text-muted">{{ $log->toUser->name }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-bold">
                                                {{ number_format($log->amount, 2) }} MMK
                                            </td>
                                            <td>{{ $log->description ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No transfer logs found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="mt-3">
                                {{ $logs->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

