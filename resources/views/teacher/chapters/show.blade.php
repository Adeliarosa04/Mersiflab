@extends('layouts.app')

@section('title', $chapter->title . ' - Modules')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>{{ $chapter->title }}</h4>
                    <p class="text-muted mb-0">{{ $chapter->description }}</p>
                    <small class="text-muted">Class: {{ $class->name }}</small>
                    <div class="mt-2">
                        <span class="badge bg-info">
                            <i class="fas fa-clock"></i> {{ $chapter->formatted_total_duration }}
                        </span>
                        <span class="badge bg-secondary">
                            <i class="fas fa-book"></i> {{ $chapter->modules->count() }} modules
                        </span>
                    </div>
                </div>
                <div>
                    <a href="{{ route('teacher.chapters.edit', [$class, $chapter]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Chapter
                    </a>
                    <a href="{{ back_url(route('teacher.chapters.index', $class)) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Chapters
                    </a>
                </div>
            </div>

            <!-- Modules List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Modules</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('teacher.modules.create.text', $chapter) }}" class="btn btn-outline-success">
                            <i class="fas fa-file-alt"></i> Add Text
                        </a>
                        <a href="{{ route('teacher.modules.create.document', $chapter) }}" class="btn btn-outline-info">
                            <i class="fas fa-file-pdf"></i> Add Document
                        </a>
                        <a href="{{ route('teacher.modules.create.video', $chapter) }}" class="btn btn-outline-danger">
                            <i class="fas fa-video"></i> Add Video
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($chapter->modules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">Order</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Estimasi Durasi</th>
                                        <th>Status</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chapter->modules as $module)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $module->order }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="{{ $module->file_icon }} me-2"></i>
                                                    <div>
                                                        <strong>{{ $module->title }}</strong>
                                                        @if($module->file_name)
                                                            <br><small class="text-muted">{{ $module->file_name }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ ucfirst($module->type) }}</span>
                                            </td>
                                            <td>
                                                @if($module->estimated_duration > 0)
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-clock"></i> {{ $module->estimated_duration }} menit
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-clock"></i> -
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($module->is_published)
                                                    <span class="badge bg-success">Published</span>
                                                @else
                                                    <span class="badge bg-secondary">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('teacher.modules.edit', [$chapter, $module]) }}" class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('teacher.modules.destroy', [$chapter, $module]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this module?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
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
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No modules yet</h5>
                            <p class="text-muted">Start by adding your first module to this chapter.</p>
                            <div class="btn-group">
                                <a href="{{ route('teacher.modules.create.text', $chapter) }}" class="btn btn-success">
                                    <i class="fas fa-file-alt"></i> Add Text Module
                                </a>
                                <a href="{{ route('teacher.modules.create.document', $chapter) }}" class="btn btn-info">
                                    <i class="fas fa-file-pdf"></i> Add Document
                                </a>
                                <a href="{{ route('teacher.modules.create.video', $chapter) }}" class="btn btn-danger">
                                    <i class="fas fa-video"></i> Add Video
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
