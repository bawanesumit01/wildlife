@extends('includes.main')
@section('content')
    <div class="breadcrumbs">
        <div class="col-sm-4">
            <div class="page-header float-left">
                <div class="page-title">
                    <h1>Dashboard</h1>
                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="page-header float-right">
                <div class="page-title">
                    <ol class="breadcrumb text-right">
                        <li class="active">Add Reptile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}"
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('error') }}"
            });
        </script>
    @endif

    @if (session('delete'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('delete') }}"
            });
        </script>
    @endif
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <strong>Add Reptile</strong>
            </div>
            <form action="{{ route('reptile.store') }}" method="post">
                @csrf
                <div class="card-body card-block d-flex">
                    <div class="form-group col-md-6">
                        <label for="reptile_name" class=" form-control-label">Reptile Name</label>
                        <input type="text" id="reptile_name" name="reptile_name" placeholder="Enter Reptile Name.."
                            class="form-control">
                        @error('reptile_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="status_id" class=" form-control-label">Status</label>
                        <select name="status_id" class="form-control pro-edt-select form-control-primary">
                            <option selected disabled>Select Status</option>
                            @foreach ($status as $item)
                                <option value="{{ $item->id }}">{{ $item->status }}
                                </option>
                            @endforeach
                        </select>
                        @error('status_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div><br>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-dot-circle-o"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="content mt-3">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Reptile List</strong>
                        </div>
                        <div class="card-body">
                            <table id="bootstrap-data-table-export" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sr.No</th>
                                        <th>Reptile Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reptileList as $item)
                                        <tr data-reptile-id="{{ $item->id }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->reptile_name }}</td>
                                            <td>
                                                <select name="status_id" class="form-control" onchange="ChangeStatus(this)">
                                                    @foreach ($status as $data)
                                                        <option value="{{ $data->id }}"
                                                            {{ $data->id == $item->status->id ? 'selected' : '' }}>
                                                            {{ $data->status }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <form id="deleteForm{{ $item->id }}"
                                                    action="{{ route('reptile.destroy', ['id' => $item->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" onclick="confirmDelete({{ $item->id }})"
                                                        class="btn btn-danger text-white">Delete</button>
                                                </form>
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
@section('script')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this reptile!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm' + id).submit();
                }
            });
        }
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function ChangeStatus(sel) {
            var statusId = sel.value;
            var reptileId = $(sel).closest('tr').data('reptile-id');

            if (statusId && reptileId) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('update-status-reptile') }}",
                    data: {
                        "status_id": statusId,
                        "ID": reptileId
                    },
                    success: function(res) {
                        console.log(res);

                        if (res === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Status Updated Successfully',
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Status not updated.',
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong. Status not updated.',
                        });
                    }
                });
            } else {
                console.error('Invalid statusId or reptileId');
            }
        }
    </script>
@endsection
