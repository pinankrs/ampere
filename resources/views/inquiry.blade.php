@extends('master')

@section('title')
    AMPERE
@endsection

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Inquiry</h5>

                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-bordered dt-responsive nowrap table-striped"
                                        style="width:100%" id="inquiryTable">
                                        <thead>
                                            <tr>
                                                <th style="text-align: left;">Sr. No</th>
                                                <th style="text-align: left;">Inq No.</th>
                                                <th style="text-align: left;">Name</th>
                                                <th style="text-align: left;">Mobile</th>
                                                <th style="text-align: left;">Vehicle No</th>
                                                <th style="text-align: left;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" id="statusInquiryId">
                            <label>Status</label>
                            <select class="form-select" id="statusId">
                                <option value="">Select</option>
                                <option value="2">Completed</option>
                                <option value="3">Rejected</option>
                                <option value="4">Confirmed</option>
                            </select>
                            <div class="status_error"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="changeStatusBtn">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script>
        $(document).ready(async function() {
            await inquiryDetails();
        });

        async function inquiryDetails() {
            $('#inquiryTable').DataTable({
                serverSide: true,
                processing: true,
                destroy: true,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('inquiry') }}',
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'inquiry_no',
                        name: 'inquiry_no'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'vehicle_no',
                        name: 'vehicle_no'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ],
                order: [
                    [1, 'desc']
                ],
                createdRow: function(row, data, index) {
                    $('td', row).eq(0).css('text-align', 'left');
                    $('td', row).eq(1).css('text-align', 'left');
                    $('td', row).eq(2).css('text-align', 'left');
                    $('td', row).eq(3).css('text-align', 'left');
                    $('td', row).eq(4).css('text-align', 'left');
                    $('td', row).eq(5).css('text-align', 'left');
                },

            });
        }

        $(document).on('click', '.change-status', function() {
            let id = $(this).data('id');
            $('#statusInquiryId').val(id);
            $('#statusId').val('').trigger('change');
            $('#statusModal').modal('show');
        });

        $(document).on('click', '#changeStatusBtn', function() {
            let id = $('#statusInquiryId').val();
            let statusId = $('#statusId').val();

            if (statusId == '') {
                $('.status_error').html('Please select a status');
                return false;
            }

            $.ajax({
                url: '{{ route('inquiry.change-status') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    statusId: statusId
                },
                success: async function(response) {
                    if (response.code == '1') {
                        $('#statusModal').modal('hide');
                        await inquiryDetails();
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        $(document).on('change', '#statusId', function() {
            $('.status_error').html('');
        });
    </script>
@endsection
