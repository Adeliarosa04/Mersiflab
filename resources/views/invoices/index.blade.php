@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Invoices</h4>
                    <a href="{{ back_url(route('home')) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Item</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td>
                                                <strong>{{ $invoice->invoice_number }}</strong>
                                            </td>
                                            <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                            <td>{{ $invoice->item_description }}</td>
                                            <td>{{ $invoice->formatted_total_amount }}</td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->status_badge }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                                @if($invoice->isOverdue())
                                                    <small class="text-danger d-block">Overdue</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $invoice->due_date->format('d M Y') }}
                                                @if($invoice->isOverdue())
                                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('invoices.show', $invoice->invoice_number) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    @if($invoice->status === 'paid')
                                                        <a href="{{ route('invoices.download', $invoice->invoice_number) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-download"></i> PDF
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $invoices->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada invoice</h5>
                            <p class="text-muted">Anda belum memiliki invoice. Mulai belanja course atau berlangganan untuk melihat invoice di sini.</p>
                            <a href="{{ route('courses') }}" class="btn btn-primary">
                                <i class="fas fa-graduation-cap"></i> Lihat Course
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
