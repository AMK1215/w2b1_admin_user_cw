@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Transfer Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Make Transfer</h3>
                </div>
                <div class="card-body">
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

                    <form action="{{ route('admin.make-transfer.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="to_user_id">Select Player <span class="text-danger">*</span></label>
                                    <select name="to_user_id" id="to_user_id" class="form-control @error('to_user_id') is-invalid @enderror" required>
                                        <option value="">-- Select Player --</option>
                                        @foreach($players as $player)
                                            <option value="{{ $player->id }}" {{ old('to_user_id') == $player->id ? 'selected' : '' }}>
                                                {{ $player->user_name }} - {{ $player->name }} (Balance: {{ number_format($player->balance, 2) }} MMK)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_user_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Amount <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="amount" 
                                           id="amount" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           placeholder="Enter amount"
                                           step="0.01"
                                           min="1"
                                           value="{{ old('amount') }}"
                                           required>
                                    @error('amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description (Optional)</label>
                                    <textarea name="description" 
                                              id="description" 
                                              rows="3" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              placeholder="Enter description (optional)">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <p class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Your current balance: <strong>{{ number_format(Auth::user()->balance, 2) }} MMK</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Transfer
                                </button>
                                <a href="{{ route('admin.home') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Transfers -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Recent Transfers</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransfers as $transfer)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $transfer->fromUser->user_name ?? 'N/A' }}
                                            <br>
                                            <small class="text-muted">{{ $transfer->fromUser->name ?? '' }}</small>
                                        </td>
                                        <td>
                                            {{ $transfer->toUser->user_name ?? 'N/A' }}
                                            <br>
                                            <small class="text-muted">{{ $transfer->toUser->name ?? '' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                {{ number_format($transfer->amount, 2) }} MMK
                                            </span>
                                        </td>
                                        <td>{{ $transfer->description ?? '-' }}</td>
                                        <td>{{ $transfer->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No recent transfers found</td>
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
@endsection

