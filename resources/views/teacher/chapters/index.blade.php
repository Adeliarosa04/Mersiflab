@extends('layouts.app')

@section('title', $class->name . ' - Chapters')

@section('content')
<style>
    .chapters-page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .chapters-page-header h2 {
        color: white;
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-size: 1.75rem;
    }
    
    .chapters-page-header p {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 1rem;
    }
    
    .info-badges {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    
    .info-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .chapters-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .chapters-card .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }
    
    .chapters-card .card-header h5 {
        font-weight: 700;
        color: #333;
        margin: 0;
        font-size: 1.25rem;
    }
    
    .chapters-table {
        margin: 0;
    }
    
    .chapters-table thead {
        background: #f8f9fa;
    }
    
    .chapters-table thead th {
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border-bottom: 2px solid #dee2e6;
    }
    
    .chapters-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .chapters-table tbody tr:hover {
        background: #f8f9fa;
        transform: translateX(2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .chapters-table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
    }
    
    .chapter-title {
        font-weight: 600;
        color: #333;
        font-size: 1rem;
    }
    
    .chapter-description {
        color: #6c757d;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    .order-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .modules-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .action-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
        border: 1px solid;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1.5rem;
    }
    
    .empty-state h5 {
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #adb5bd;
        margin-bottom: 1.5rem;
    }
    
    .header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    @media (max-width: 768px) {
        .chapters-page-header {
            padding: 1.5rem;
        }
        
        .header-actions {
            width: 100%;
            margin-top: 1rem;
        }
        
        .header-actions .btn {
            flex: 1;
        }
        
        .chapters-table {
            font-size: 0.875rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="chapters-page-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div class="flex-grow-1">
                        <h2>
                            <i class="fas fa-book-open me-2"></i>{{ $class->name }}
                        </h2>
                        @if($class->description)
                            <p class="mb-0">{{ $class->description }}</p>
                        @endif
                        <div class="info-badges">
                            <span class="info-badge">
                                <i class="fas fa-layer-group me-1"></i>
                                {{ $chapters->count() }} chapters
                            </span>
                            <span class="info-badge">
                                <i class="fas fa-book me-1"></i>
                                {{ $chapters->sum(function($c) { return $c->modules->count(); }) }} modules
                            </span>
                            @if($class->is_published)
                                <span class="info-badge" style="background: rgba(40, 167, 69, 0.3); border-color: rgba(40, 167, 69, 0.5);">
                                    <i class="fas fa-check-circle me-1"></i>Published
                                </span>
                            @else
                                <span class="info-badge">
                                    <i class="fas fa-clock me-1"></i>Draft
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('teacher.chapters.create', $class) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>Add Chapter
                        </a>
                        <a href="{{ back_url(route('teacher.manage.content')) }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Chapters List -->
            <div class="card chapters-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Chapters List
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($chapters->count() > 0)
                        <div class="table-responsive">
                            <table class="table chapters-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th width="140">Modules</th>
                                        <th width="120">Status</th>
                                        <th width="180" style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chapters as $chapter)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-bookmark text-primary me-2" style="font-size: 1.1rem;"></i>
                                                    <span class="chapter-title">{{ $chapter->title }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($chapter->description)
                                                    <span class="chapter-description">{{ Str::limit($chapter->description, 60) }}</span>
                                                @else
                                                    <span class="text-muted fst-italic">No description</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="modules-badge">
                                                    <i class="fas fa-book me-1"></i>
                                                    {{ $chapter->modules->count() }} modules
                                                </span>
                                            </td>
                                            <td>
                                                @if($chapter->is_published)
                                                    <span class="badge bg-success status-badge">
                                                        <i class="fas fa-check-circle me-1"></i>Published
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary status-badge">
                                                        <i class="fas fa-clock me-1"></i>Draft
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action-buttons justify-content-center">
                                                    <a href="{{ route('teacher.chapters.show', [$class, $chapter]) }}" 
                                                       class="btn btn-outline-primary action-btn" 
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('teacher.chapters.edit', [$class, $chapter]) }}" 
                                                       class="btn btn-outline-warning action-btn" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('teacher.chapters.destroy', [$class, $chapter]) }}" 
                                                          method="POST" 
                                                          style="display:inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this chapter? All modules will be deleted too.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-outline-danger action-btn" 
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h5>No chapters yet</h5>
                            <p>Start by adding your first chapter to this class.</p>
                            <a href="{{ route('teacher.chapters.create', $class) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Your First Chapter
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
