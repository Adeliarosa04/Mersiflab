@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        {{ $invoice->title }}
                        <small class="text-muted">{{ $invoice->invoice_number }}</small>
                    </h4>
                    <div>
                        <a href="{{ back_url(route('invoices.index')) }}" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        @if($invoice->status === 'paid')
                            <a href="{{ route('invoices.download', $invoice->invoice_number) }}" 
                               class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Invoice Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Detail Invoice</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>No. Invoice:</strong></td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td>{{ $invoice->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jatuh Tempo:</strong></td>
                                    <td>
                                        {{ $invoice->due_date->format('d M Y H:i') }}
                                        @if($invoice->isOverdue())
                                            <span class="badge bg-danger ms-2">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $invoice->status_badge }}">
                                            {{ $invoice->status === 'paid' ? 'Paid' : 'Unpaid' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Buyer Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td>{{ $invoice->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $invoice->user->email }}</td>
                                </tr>
                                @if($invoice->user->phone)
                                    <tr>
                                        <td><strong>Telepon:</strong></td>
                                        <td>{{ $invoice->user->phone }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Purchase Details</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th class="text-end">Harga</th>
                                            <th class="text-end">Diskon</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($invoice->invoiceItems && $invoice->invoiceItems->count() > 0)
                                            @foreach($invoice->invoiceItems as $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->item_name }}</strong>
                                                        @if($item->item_description)
                                                            <br><small class="text-muted">{{ $item->item_description }}</small>
                                                        @endif
                                                        @if($item->course)
                                                            <br><small class="text-info">Course: {{ $item->course->name }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">{{ $item->formatted_amount }}</td>
                                                    <td class="text-end">{{ $item->formatted_discount_amount }}</td>
                                                    <td class="text-end"><strong>{{ $item->formatted_total_amount }}</strong></td>
                                                </tr>
                                            @endforeach
                                        @elseif(isset($invoice->metadata['items']) && is_array($invoice->metadata['items']))
                                            @foreach($invoice->metadata['items'] as $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item['name'] ?? $item['title'] ?? 'Course Item' }}</strong>
                                                        @if(isset($item['description']))
                                                            <br><small class="text-muted">{{ $item['description'] }}</small>
                                                        @endif
                                                        @if(isset($item['purchase_code']))
                                                            <br><small class="text-info">Kode: {{ $item['purchase_code'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">Rp{{ number_format($item['amount'] ?? $item['price'] ?? 0, 0, ',', '.') }}</td>
                                                    <td class="text-end">Rp{{ number_format($item['discount_amount'] ?? 0, 0, ',', '.') }}</td>
                                                    <td class="text-end"><strong>Rp{{ number_format($item['total_amount'] ?? $item['amount'] ?? $item['price'] ?? 0, 0, ',', '.') }}</strong></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>
                                                    <strong>{{ $invoice->item_description }}</strong>
                                                    @if($invoice->type === 'subscription' && isset($invoice->metadata['plan_features']))
                                                        <ul class="list-unstyled mt-2 mb-0">
                                                            @foreach($invoice->metadata['plan_features'] as $feature)
                                                                <li><small class="text-muted">• {{ $feature }}</small></li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ $invoice->formatted_amount }}</td>
                                                <td class="text-end">{{ $invoice->formatted_discount_amount }}</td>
                                                <td class="text-end"><strong>{{ $invoice->formatted_total_amount }}</strong></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6 offset-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td><strong>Subtotal:</strong></td>
                                            <td class="text-end">{{ $invoice->formatted_amount }}</td>
                                        </tr>
                                        @if($invoice->discount_amount > 0)
                                            <tr>
                                                <td><strong>Discount:</strong></td>
                                                <td class="text-end text-success">-{{ $invoice->formatted_discount_amount }}</td>
                                            </tr>
                                        @endif
                                        @if($invoice->tax_amount > 0)
                                            <tr>
                                                <td><strong>Tax:</strong></td>
                                                <td class="text-end">{{ $invoice->formatted_tax_amount }}</td>
                                            </tr>
                                        @endif>
                                        <tr class="border-top">
                                            <td><h5 class="mb-0">Total:</h5></td>
                                            <td class="text-end"><h5 class="mb-0">{{ $invoice->formatted_total_amount }}</h5></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    @if($invoice->status === 'pending')
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0">
                                            <i class="fas fa-info-circle"></i> Payment Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Transfer ke Rekening Berikut:</h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Bank:</strong></td>
                                                        <td>BCA</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>No. Rekening:</strong></td>
                                                        <td>123-456-7890</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>A/n:</strong></td>
                                                        <td>PT MersifLab Education</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Jumlah:</strong></td>
                                                        <td><strong>{{ $invoice->formatted_total_amount }}</strong></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Payment Confirmation:</h6>
                                                <p class="text-muted">After making the transfer, please confirm the payment via WhatsApp to speed up activation.</p>
                                                <a href="https://wa.me/088806658440?text={{ urlencode('Hello MersifLab, I would like to confirm payment for invoice ' . $invoice->invoice_number . ' amounting to ' . $invoice->formatted_total_amount) }}" 
                                                   class="btn btn-success btn-lg w-100" target="_blank">
                                                    <i class="fab fa-whatsapp"></i> Confirm Payment via WhatsApp
                                                </a>
                                                <div class="alert alert-warning mt-3 mb-0">
                                                    <small>
                                                        <i class="fas fa-exclamation-triangle"></i> 
                                                        <strong>IMPORTANT:</strong> Keep the transfer proof for verification if needed.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Status -->
                    @if($invoice->status === 'paid')
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-check-circle"></i> Payment Successful
                                    </h5>
                                    <p class="mb-0">
                                        Thank you! Your payment has been received. 
                                        @if($invoice->type === 'course')
                                            The course is now accessible from your dashboard.
                                        @elseif($invoice->type === 'subscription')
                                            Your subscription is now active. Happy learning!
                                        @endif
                                    </p>
                                    @if($invoice->paid_at)
                                        <small class="text-muted">
                                            Dibayarkan pada: {{ $invoice->paid_at->format('d M Y H:i') }}
                                            @if($invoice->payment_method)
                                                via {{ ucfirst($invoice->payment_method) }}
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif($invoice->status === 'cancelled')
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-danger">
                                            <h5 class="alert-heading">
                                                <i class="fas fa-times-circle"></i> Invoice Cancelled
                                            </h5>
                                            <p class="mb-0">This invoice has been cancelled.</p>
                                        </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    @if($invoice->status === 'pending')
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button class="btn btn-primary" onclick="window.print()">
                                            <i class="fas fa-print"></i> Cetak Invoice
                                        </button>
                                        <a href="{{ route('invoices.download', $invoice->invoice_number) }}" 
                                           class="btn btn-outline-primary ms-2">
                                            <i class="fas fa-download"></i> Download PDF
                                        </a>
                                    </div>
                                    <div class="text-end">
                                        <a href="https://wa.me/088806658440?text={{ urlencode('Halo MersifLab, saya ingin konfirmasi pembayaran untuk invoice ' . $invoice->invoice_number . ' sebesar ' . $invoice->formatted_total_amount) }}" 
                                           class="btn btn-success btn-lg" target="_blank">
                                            <i class="fab fa-whatsapp"></i> Konfirmasi Pembayaran
                                        </a>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-clock"></i> 
                                            Sisa waktu: {{ $invoice->due_date->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
