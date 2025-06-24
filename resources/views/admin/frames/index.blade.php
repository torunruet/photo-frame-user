@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Frames</h2>
            <a href="{{ route('admin.frames.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Frame
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($frames as $frame)
                                <tr>
                                    <td>{{ $frame->id }}</td>
                                    <td>
                                        <img src="{{ url('storage/' . $frame->image_path) }}" alt="{{ $frame->name }}"
                                            class="img-thumbnail" style="max-width: 100px;">
                                    </td>
                                    <td>{{ $frame->name }}</td>
                                    <td>{{ $frame->category }}</td>
                                    <td>${{ number_format($frame->price, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $frame->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $frame->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.frames.edit', $frame) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.frames.destroy', $frame) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this frame?');"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
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

                <div class="mt-4">
                    {{ $frames->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
