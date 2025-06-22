@extends('layouts.master')

@section('title', 'Dashboard')
@section('description', 'Dashboard page description')

@section('content')
    <!-- begin:: Content -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Starter Page</h4>



            </div>
        </div>
    </div>
    <!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
    <!-- Add any page-specific scripts here -->
    <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
@endsection
