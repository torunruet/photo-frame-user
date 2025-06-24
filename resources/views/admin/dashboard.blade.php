@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Dashboard</h2>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Frames</h5>
                        <h2 class="card-text">{{ \App\Models\Frame::count() }}</h2>
                        <a href="{{ route('admin.frames.index') }}" class="text-white">View all frames <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Active Frames</h5>
                        <h2 class="card-text">{{ \App\Models\Frame::where('is_active', true)->count() }}</h2>
                        <a href="{{ route('admin.frames.index') }}?status=active" class="text-white">View active frames <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Birthday Frames</h5>
                        <h2 class="card-text">{{ \App\Models\Frame::where('category', 'birthday')->count() }}</h2>
                        <a href="{{ route('admin.frames.index') }}?category=birthday" class="text-white">View birthday
                            frames <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Wedding Frames</h5>
                        <h2 class="card-text">{{ \App\Models\Frame::where('category', 'wedding')->count() }}</h2>
                        <a href="{{ route('admin.frames.index') }}?category=wedding" class="text-white">View wedding frames
                            <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="{{ route('admin.frames.create') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-plus-circle me-2"></i> Add New Frame
                            </a>
                            <a href="{{ route('admin.frames.index') }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-list me-2"></i> Manage Frames
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Frames</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (\App\Models\Frame::latest()->take(5)->get() as $frame)
                                        <tr>
                                            <td>{{ $frame->name }}</td>
                                            <td>{{ ucfirst($frame->category) }}</td>
                                            <td>
                                                <span class="badge {{ $frame->is_active ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $frame->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
