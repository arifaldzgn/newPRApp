 {{-- Create PR Modal --}}
    <div class="modal fade" id="createPR" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
        aria-labelledby="createPRLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="createPrLabel">New Purchase Requisition</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <form id="createPrForm" method="POST" action="">
                            <div class="card mb-3 card-body border border-primary">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                    <label>Enable advance cash</label>
                                </div>
                                <div class="form-group">
                                    <input type="number" id="cashAdvance" class="form-control" name="advance_cash" value="0" disabled>
                                    <small class="form-text text-muted">Optional. This will refer to the total amount of this PR (default: 0).</small>
                                </div>
                            </div>
                            <div id="prRequestForm">
                                @csrf
                                <!-- Material Request Information -->
                            </div>
                    </div>
                    <div class="d-grid col-6 mx-auto">
                        <button class="btn btn-primary btn-block" id="addItem" type="button">Add New Items</button>
                    </div>
                </div>
                </form>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-block" id="submitRequest" disabled>Submit Request</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- View Details & Update Material Modal --}}
    <div class="modal fade" id="materialModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Part Request Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3 card-body border border-primary">
                        <label>Advance cash</label>
                        <input type="number" id="typeNumber" class="form-control" name="advance_cash">
                        <small class="form-text text-muted">This will refer to the total advance cash amount of this PR</small>
                    </div>
                    <form id="materialDataForm" method="POST">
                        <!-- Form content will be appended here by JavaScript -->
                    </form>
                </div>
                <div class="modal-footer">
                    @if (Route::currentRouteName() !== 'rejected')
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'pic' || auth()->user()->role === 'hod' || auth()->user()->role === 'purchasing')
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                            <button type="button" class="btn btn-danger" id="rejectButton">Reject</button>
                            <button type="button" class="btn btn-success" id="approveButton">Approve</button>
                        @endif
                    @endif
                    <button type="button" class="btn btn-primary" id="saveMaterialChanges">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this ticket?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="cancelDelete" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Reason Modal --}}
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectReasonModalLabel">Reject Purchase Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectReasonForm">
                        <div class="mb-3">
                            <label for="rejectReasonTextarea" class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="rejectReasonTextarea" name="reason" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectButton">Reject</button>
                </div>
            </div>
        </div>
    </div>