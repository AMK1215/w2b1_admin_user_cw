@extends('layouts.master')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">System Wallet Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Info Boxes -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">My Balance</span>
                        <span class="info-box-number">{{ number_format($systemBalance, 2) }} MMK</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-database"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total System Balance</span>
                        <span class="info-box-number">{{ number_format($walletStats['total_system_balance'], 2) }} MMK</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total User Balance</span>
                        <span class="info-box-number">{{ number_format($walletStats['total_user_balance'], 2) }} MMK</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Balance (All)</span>
                        <span class="info-box-number">{{ number_format($walletStats['total_balance_all'], 2) }} MMK</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-header bg-gradient-primary">
                        <h3 class="card-title text-white">System Wallet Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Username:</strong> {{ $user->user_name }}</p>
                                <p><strong>Name:</strong> {{ $user->name }}</p>
                                <p><strong>Email:</strong> {{ $user->email ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phone:</strong> {{ $user->phone ?? 'N/A' }}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge badge-{{ $user->status == 1 ? 'success' : 'danger' }}">
                                        {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                                <p><strong>Current Balance:</strong> <span class="text-primary font-weight-bold">{{ number_format($systemBalance, 2) }} MMK</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 15px;">
                    <div class="card-header">
                        <h3 class="card-title">Recent Transactions</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Amount</th>
                                        <th>Fee</th>
                                        <th>Final Amount</th>
                                        <th>Balance</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTransactions as $transaction)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdraw' ? 'danger' : 'info') }}">
                                                    {{ ucfirst($transaction->type) }}
                                                </span>
                                            </td>
                                            <td>{{ $transaction->user->user_name ?? 'N/A' }}</td>
                                            <td>{{ $transaction->targetUser->user_name ?? 'N/A' }}</td>
                                            <td>{{ number_format($transaction->amount, 2) }}</td>
                                            <td>{{ number_format($transaction->fee, 2) }}</td>
                                            <td>{{ number_format($transaction->final_amount, 2) }}</td>
                                            <td>{{ number_format($transaction->balance, 2) }}</td>
                                            <td>{{ $transaction->description ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">No transactions found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

