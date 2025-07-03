   
   <!-- ========== App Menu ========== -->
    <div class="app-menu navbar-menu">
        <!-- LOGO -->
        <div class="navbar-brand-box text-start">
            <!-- Dark Logo-->
            {{-- <a href="index.html" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="assets/images/logo-sm.png" alt="" height="22">
                </span>
                <span class="logo-lg">
                    <img src="assets/images/logo-dark.png" alt="" height="17">
                </span>
            </a> --}}
            <!-- Light Logo-->
            <a href="/" class="logo logo-light">
                <span class="logo-sm">
                    {{-- <img src="assets/images/logo-sm.png" alt="" height="22"> --}}
                    <h6 class="text-light">Prepcenter</h6>
                </span>
                <span class="logo-lg">
                    {{-- <img src="assets/images/logo-light.png" alt="" height="17"> --}}
                    <h2 class="text-light mt-4 mb-3 mx-2">Prepcenter</h2>
                </span>
            </a>
            <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
        </div>

        <div id="scrollbar">
            <div class="container-fluid">

                <div id="two-column-menu">
                </div>
                <ul class="navbar-nav" id="navbar-nav">
                    {{-- @if(Auth::user()->role == 1) --}}

                    @php
                        $user = Auth()->user();
                        $permissions = $user ? explode(",", $user->permission) : [];
                    @endphp

                    @if($user && in_array('dashboard', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                                <i class="bx bx-category"></i> <span data-key="t-widgets">Dashboard</span>
                            </a>
                        </li>
                    @endif
                    
                    @if($user && in_array('daily_input', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('daily-input') ? 'active' : '' }}" href="{{ route('daily-input.index') }}">
                                <i class="ri-checkbox-circle-line"></i> <span data-key="t-widgets">Daily Input</span>
                            </a>
                        </li>
                    @endif

                    @if($user && in_array('employees', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('employees') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                                <i class="mdi mdi-alarm-panel"></i> <span data-key="t-widgets">Employee</span>
                            </a>
                        </li>
                    @endif
                    
                    @if($user && in_array('products', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('products') ? 'active' : '' }}" href="{{ route('products.index') }}">
                                <i class="mdi mdi-clipboard-edit-outline"></i> <span data-key="t-widgets">Products</span>
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->role == 1) 
                    <li class="menu-title"><span data-key="t-menu">REPORT PAGES</span></li>
                    @endif

                    @if($user && in_array('report_by_employee', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('report-by-employee', 'employee-search') ? 'active' : '' }}" href="{{ route('report.by.employee') }}">
                                <i class="bx bx-add-to-queue"></i> <span data-key="t-widgets">Report By Employee</span>
                            </a>
                        </li>
                    @endif

                    @if($user && in_array('report_by_time', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('report-by-time', 'time-search') ? 'active' : '' }}" href="{{ route('report.by.time') }}">
                                <i class="bx bx-add-to-queue"></i> <span data-key="t-widgets">Report By Time</span>
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->role == 1) 
                    <li class="menu-title"><span data-key="t-menu">SUMMARY</span></li>
                    @endif
                    @if($user && in_array('monthly_date_summary', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('monthly-summary', 'summary-search') ? 'active' : '' }}" href="{{ route('monthly.summary') }}">
                                <i class="las la-calendar"></i> <span data-key="t-widgets">Monthly Date Summary</span>
                            </a>
                        </li>
                    @endif
                    @if($user && in_array('monthly_product_report', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('monthly-product-report', 'monthly-product-report-search') ? 'active' : '' }}" href="{{ route('monthly.product.report') }}">
                                <i class="las la-calendar"></i> <span data-key="t-widgets">Monthly Product Report</span>
                            </a>
                        </li>
                    @endif
                    
                    @if($user && in_array('system_setting', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('system-setting', 'system-setting-add') ? 'active' : '' }}" href="{{ route('system.setting') }}">
                                <i class="ri-settings-5-line"></i> <span data-key="t-widgets">System Setting</span>
                            </a>
                        </li>
                    @endif
                    @if($user && in_array('merge_products', $permissions))
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('merge-products', 'merge-products') ? 'active' : '' }}" href="{{ route('merge.product.menu') }}">
                                <i class="ri-settings-5-line"></i> <span data-key="t-widgets">Merge Products</span>
                            </a>
                        </li>
                    @endif
                    <!--<li class="menu-title"><span data-key="t-menu">Prep Work Order</span></li>-->
                    @if($user && in_array('prep_order', $permissions))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('prep-orders.index') ? 'active' : '' }}" href="{{ route('prep-orders.index') }}">
                            <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Prep Work Order List</span>
                        </a>
                    </li>
                    @endif
                    {{-- <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('prep-orders-create') ? 'active' : '' }}" href="{{ route('prep-orders.create') }}">
                            <i class="las la-calendar"></i> <span data-key="t-widgets">Prep Work Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('prep-orders') ? 'active' : '' }}" href="{{ route('prep-orders.index') }}">
                            <i class="las la-calendar"></i> <span data-key="t-widgets">Prep Work Orders List</span>
                        </a>
                    </li> --}}
                    <!--<li class="nav-item">-->
                    <!--    <a class="nav-link menu-link collapsed" href="#prep-work-order" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('prep-orders.*') ? 'true' : 'false' }}" aria-controls="prep-work-order" {{ request()->routeIs('prep-orders.*') ? 'active' : '' }}>-->
                    <!--        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Prep Work Order</span>-->
                    <!--    </a>-->
                    <!--    <div class="menu-dropdown collapse {{ request()->routeIs('prep-orders.*') ? 'show' : '' }}" id="prep-work-order" style="">-->
                    <!--        <ul class="nav nav-sm flex-column">-->
                    <!--            <li class="nav-item">-->
                    <!--                <a  href="{{ route('prep-orders.create') }}" class="nav-link {{ request()->routeIs('prep-orders.create') ? 'active' : '' }}" data-key="t-analytics">Create</a>-->
                    <!--            </li>-->
                    <!--            <li class="nav-item">-->
                    <!--                <a  href="{{ route('prep-orders.index') }}" class="nav-link  {{ request()->routeIs('prep-orders.index') ? 'active' : '' }}" data-key="t-crm">List</a>-->
                    <!--            </li>-->
                    <!--        </ul>-->
                    <!--    </div>-->
                    <!--</li>-->
                    @if($user && in_array('expense_manage', $permissions))
                    <li class="nav-item">
                        <a class="nav-link menu-link collapsed" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('categories.*', 'expenses.*') ? 'true' : 'false' }}" aria-controls="sidebarDashboards" {{ request()->routeIs('categories.*', 'expenses.*') ? 'active' : '' }}>
                            <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Expense Managment</span>
                        </a>
                        <div class="menu-dropdown collapse {{ request()->routeIs('categories.*', 'expenses.*') ? 'show' : '' }}" id="sidebarDashboards" style="">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a  href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" data-key="t-analytics"> Categories </a>
                                </li>
                                <li class="nav-item">
                                    <a  href="{{ route('expenses.index') }}" class="nav-link  {{ request()->routeIs('expenses.*') ? 'active' : '' }}" data-key="t-crm"> Expeneses </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                        
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                    <li class="nav-item">
                        <a class="nav-link menu-link" style="cursor: pointer" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout"></i> <span data-key="t-widgets">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Sidebar -->
        </div>

        <div class="sidebar-background"></div>
    </div>
    <!-- Left Sidebar End -->
    <!-- Vertical Overlay-->
    <div class="vertical-overlay"></div>