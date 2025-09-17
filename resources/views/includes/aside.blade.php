<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu">Menu</li>


                @if(auth()->user()->role === 'admin')
                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-home-circle"></i>
                        <span key="t-dashboards">User Management</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false" style="">
                        <li><a href="{{ route('account') }}" key="t-users">Users</a></li>
                        <li><a href="{{ route('departments') }}" key="t-dept">Departments</a></li>
                        <li><a href="{{ route('role') }}" key="t-role">Roles</a></li>

                    </ul>
                </li>
                @endif


                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-home-circle"></i>
                        <span key="t-dashboards">PR Menu</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('pr_create') }}" key="t-default">Create PR</a></li>
                        <ul class="sub-menu" aria-expanded="true">
                            <li>
                                <a href="javascript: void(0);" class="has-arrow" key="t-vertical">Request Status</a>
                                <ul class="sub-menu" aria-expanded="true">
                                    <li><a href="{{ route('pending') }}" key="t-pending">Pending</a></li>
                                    <li><a href="{{ route('approved') }}" key="t-approved">Approved</a></li>
                                    <li><a href="{{ route('rejected') }}" key="t-rejected">Rejected</a></li>
                                </ul>
                            </li>
                        </ul>
                    </ul>
                </li>
                {{-- 
                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-home-circle"></i>
                        <span key="t-dashboards">Meeting Room Menu</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('booking_room') }}" key="t-default">Request a Meeting</a></li>
                        <ul class="sub-menu" aria-expanded="true">
                            <li>
                                <a href="javascript: void(0);" class="has-arrow" key="t-vertical">Meeting Status</a>
                                <ul class="sub-menu" aria-expanded="true">
                                    <li><a href="layouts-light-sidebar.html" key="t-light-sidebar">Pending</a></li>
                                    <li><a href="layouts-compact-sidebar.html" key="t-compact-sidebar">Approved</a></li>
                                    <li><a href="layouts-icon-sidebar.html" key="t-icon-sidebar">Rejected</a></li>
                                </ul>
                            </li>
                        </ul>
                    </ul>
                </li> --}}

                @if(auth()->user()->role === 'purchasing' || auth()->user()->role === 'admin')
                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-home-circle"></i>
                        <span key="t-dashboards">Purchasing</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('partlist') }}" key="t-saas">Inventory</a></li>
                        <li><a href="{{ route('partlistLog') }}" key="t-default">Inventory Log</a></li>
                    </ul>
                </li>
                @endif


                <li>
                    <a href="{{ route('user_log') }}" class="waves-effect">
                        <i class="bx bx-file"></i>
                        <span key="t-file-manager">Log History</span>
                    </a>
                </li>
                {{-- <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-layout"></i>
                                    <span key="t-layouts">PR Menu</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="true">
                                    <li>
                                        <a href="javascript: void(0);" class="has-arrow" key="t-vertical">Vertical</a>
                                        <ul class="sub-menu" aria-expanded="true">
                                            <li><a href="layouts-light-sidebar.html" key="t-light-sidebar">Light Sidebar</a></li>
                                            <li><a href="layouts-compact-sidebar.html" key="t-compact-sidebar">Compact Sidebar</a></li>
                                            <li><a href="layouts-icon-sidebar.html" key="t-icon-sidebar">Icon Sidebar</a></li>
                                            <li><a href="layouts-boxed.html" key="t-boxed-width">Boxed Width</a></li>
                                            <li><a href="layouts-preloader.html" key="t-preloader">Preloader</a></li>
                                            <li><a href="layouts-colored-sidebar.html" key="t-colored-sidebar">Colored Sidebar</a></li>
                                            <li><a href="layouts-scrollable.html" key="t-scrollable">Scrollable</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li> --}}
            </ul>
        </div>

        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
