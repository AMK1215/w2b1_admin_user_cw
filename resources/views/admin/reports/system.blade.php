@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">System Reports & Statistics</h3>
                </div>
                <div class="card-body">
                    <!-- Date Filter -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_from">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_to">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Wallet Statistics -->
                    @if(isset($walletStats))
                    <div class="row">
                        <div class="col-12">
                            <h4>Wallet Statistics</h4>
                        </div>
                        @foreach($walletStats as $key => $value)
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($value, 2) }}</h3>
                                    <p>{{ ucfirst(str_replace('_', ' ', $key)) }} (MMK)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Transaction Statistics by Type -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>Transactions by Type</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Transaction Type</th>
                                            <th>Count</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transactionStats as $stat)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        {{ ucfirst(str_replace('_', ' ', $stat->type)) }}
                                                    </span>
                                                </td>
                                                <td>{{ number_format($stat->count) }}</td>
                                                <td>{{ number_format($stat->total, 2) }} MMK</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">No transaction statistics available</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent System Transactions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>Recent System Transactions</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Type</th>
                                            <th>User</th>
                                            <th>Target User</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTransactions as $transaction)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $transaction->user->user_name ?? 'N/A' }}
                                                    <br>
                                                    <small class="text-muted">{{ $transaction->user->name ?? '' }}</small>
                                                </td>
                                                <td>
                                                    {{ $transaction->targetUser->user_name ?? 'N/A' }}
                                                    <br>
                                                    <small class="text-muted">{{ $transaction->targetUser->name ?? '' }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        {{ number_format($transaction->amount, 2) }} MMK
                                                    </span>
                                                </td>
                                                <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No transactions found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-3">
                                {{ $recentTransactions->appends(['date_from' => $dateFrom, 'date_to' => $dateTo])->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

