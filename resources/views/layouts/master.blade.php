<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShweDragon | Dashboard</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    


    <style>
        .dropdown-menu {
            z-index: 1050 !important;
        }

        /* Role Badge Styling */
        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Navbar User Info Styling */
        .navbar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav .nav-link i {
            font-size: 1rem;
        }

        /* Sidebar Menu Icons */
        .nav-sidebar .nav-icon {
            margin-right: 0.5rem;
        }

        /* Active Menu Item */
        .nav-sidebar .nav-link.active {
            background-color: #007bff !important;
            color: #fff !important;
        }

        /* Menu Open State */
        .nav-item.menu-open > .nav-link {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Submenu Styling */
        .nav-treeview > .nav-item > .nav-link {
            padding-left: 3rem;
        }

        /* Balance Display */
        .nav-link[title="Current Balance"] {
            font-weight: 600;
            color: #28a745 !important;
        }

        /* Role Badge Colors */
        .badge-danger {
            background-color: #dc3545;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
    </style>

    @yield('style')


</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light sticky-top">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('admin.home') }}" class="nav-link">Home</a>
                </li>
            </ul>



            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- User Role Badge -->
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        @php
                            $userType = \App\Enums\UserType::from(auth()->user()->type);
                            $badgeClass = match($userType) {
                                \App\Enums\UserType::Owner => 'badge-danger',
                                \App\Enums\UserType::Player => 'badge-primary',
                                \App\Enums\UserType::SystemWallet => 'badge-warning',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $userType->label() }}</span>
                    </a>
                </li>

                <!-- User Profile -->
                <li class="nav-item">
                    <a class="nav-link"
                        href="{{ route('admin.profile_index',$id = \Illuminate\Support\Facades\Auth::id()) }}"
                        title="View Profile">
                        <i class="fas fa-user-circle"></i>
                        {{ auth()->user()->user_name }}
                    </a>
                </li>

                <!-- Balance Display -->
                <li class="nav-item">
                    <a class="nav-link" href="#" title="Current Balance">
                        <i class="fas fa-wallet"></i>
                        {{ number_format(auth()->user()->balance, 2) }} MMK
                    </a>
                </li> 

                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
             <a href="{{ route('admin.home') }}" class="brand-link">
            <img src="{{ asset('assets/img/logo/1.png') }}" alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">ShweDragon</span>
            </a>
            <!-- Brand Logo -->

            

            <!-- Sidebar -->
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Dashboard - Only for Owner and SystemWallet -->
                        @php
                            $userType = \App\Enums\UserType::from(auth()->user()->type);
                            $isPlayer = $userType === \App\Enums\UserType::Player;
                        @endphp

                        @unless($isPlayer)
                        <li class="nav-item">
                            <a href="{{ route('admin.home') }}"
                                class="nav-link {{ Route::current()->getName() == 'admin.home' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        @endunless

                        <!-- Owner Menu Items -->
                        @can('owner_access')
                            <!-- Player Management -->
                            @can('player_index')
                            <li class="nav-item {{ request()->routeIs('admin.players.*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>
                                        Player Management
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.players.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.players.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Players List</p>
                                        </a>
                                    </li>
                                    @can('player_create')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.players.create') }}"
                                           class="nav-link {{ request()->routeIs('admin.players.create') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Create Player</p>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                            @endcan

                            <!-- System Wallet -->
                            @can('system_wallet_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.system-wallet.index') }}"
                                   class="nav-link {{ request()->routeIs('admin.system-wallet.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-wallet"></i>
                                    <p>System Wallet</p>
                                </a>
                            </li>
                            @endcan

                            <!-- Transactions & Banking -->
                            @can('transfer_log')
                            <li class="nav-item {{ request()->routeIs('admin.transactions.*', 'admin.transfers.*', 'admin.bank.*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-exchange-alt"></i>
                                    <p>
                                        Transactions
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.transfer-log.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.transfer-log.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Transfer Log</p>
                                        </a>
                                    </li>
                                    @can('make_transfer')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.make-transfer.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.make-transfer.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Make Transfer</p>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                            @endcan

                            <!-- Deposits & Withdrawals -->
                            @can('deposit')
                            <li class="nav-item {{ request()->routeIs('admin.deposits.*', 'admin.withdrawals.*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-money-bill-wave"></i>
                                    <p>
                                        Finance
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.deposits.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Deposit Requests</p>
                                        </a>
                                    </li>
                                    @can('withdraw')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.withdrawals.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Withdrawal Requests</p>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('bank')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.banks.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.banks.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Bank Management</p>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                            @endcan

                            <!-- Reports -->
                            @can('report_check')
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.index') }}"
                                   class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>Reports</p>
                                </a>
                            </li>
                            @endcan

                            <!-- Game Management -->
                            @can('game_type_access')
                            <li class="nav-item {{ request()->routeIs('admin.game-types.*', 'admin.gameLists.*', 'admin.providers.*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-gamepad"></i>
                                    <p>
                                        Game Management
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.game-types.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.game-types.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Game Types</p>
                                        </a>
                                    </li>
                                    @can('game_list_access')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.gameLists.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.gameLists.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Game Lists</p>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('provider_access')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.providers.index') }}"
                                           class="nav-link {{ request()->routeIs('admin.providers.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Providers</p>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                            @endcan

                            <!-- General Settings -->
                            @can('banner_access')
                            <li class="nav-item {{ in_array(Route::currentRouteName(), ['admin.text.index', 'admin.banners.index', 'admin.adsbanners.index', 'admin.promotions.index', 'admin.video-upload.index']) ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-tools"></i>
                                    <p>
                                        General Settings
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.video-upload.index') }}"
                                           class="nav-link {{ Route::current()->getName() == 'admin.video-upload.index' ? 'active' : '' }}">
                                            <i class="fas fa-video nav-icon"></i>
                                            <p>Ads Video</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.text.index') }}"
                                           class="nav-link {{ Route::current()->getName() == 'admin.text.index' ? 'active' : '' }}">
                                            <i class="fas fa-font nav-icon"></i>
                                            <p>Banner Text</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.banners.index') }}"
                                           class="nav-link {{ Route::current()->getName() == 'admin.banners.index' ? 'active' : '' }}">
                                            <i class="fas fa-image nav-icon"></i>
                                            <p>Banner</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.adsbanners.index') }}"
                                           class="nav-link {{ Route::current()->getName() == 'admin.adsbanners.index' ? 'active' : '' }}">
                                            <i class="fas fa-ad nav-icon"></i>
                                            <p>Banner Ads</p>
                                        </a>
                                    </li>
                                    @can('promotion_access')
                                    <li class="nav-item">
                                        <a href="{{ route('admin.promotions.index') }}"
                                           class="nav-link {{ Route::current()->getName() == 'admin.promotions.index' ? 'active' : '' }}">
                                            <i class="fas fa-bullhorn nav-icon"></i>
                                            <p>Promotions</p>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                            @endcan

                            <!-- Contact Management -->
                            @can('contact')
                            <li class="nav-item">
                                <a href="{{ route('admin.contacts.index') }}"
                                   class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-address-book"></i>
                                    <p>Contact Management</p>
                                </a>
                            </li>
                            @endcan
                        @endcan

                        <!-- Player Menu Items -->
                        @can('player_access')
                            @cannot('owner_access')
                                <!-- Games -->
                                @can('game_type_access')
                                <li class="nav-item">
                                    <a href="{{ route('admin.games.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.games.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-gamepad"></i>
                                        <p>Games</p>
                                    </a>
                                </li>
                                @endcan

                                <!-- My Transactions -->
                                <li class="nav-item {{ request()->routeIs('admin.my-deposits.*', 'admin.my-withdrawals.*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-money-bill-wave"></i>
                                        <p>
                                            My Finance
                                            <i class="fas fa-angle-left right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @can('deposit')
                                        <li class="nav-item">
                                            <a href="{{ route('admin.my-deposits.index') }}"
                                               class="nav-link {{ request()->routeIs('admin.my-deposits.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>My Deposits</p>
                                            </a>
                                        </li>
                                        @endcan
                                        @can('withdraw')
                                        <li class="nav-item">
                                            <a href="{{ route('admin.my-withdrawals.index') }}"
                                               class="nav-link {{ request()->routeIs('admin.my-withdrawals.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>My Withdrawals</p>
                                            </a>
                                        </li>
                                        @endcan
                                        @can('bank')
                                        <li class="nav-item">
                                            <a href="{{ route('admin.my-banks.index') }}"
                                               class="nav-link {{ request()->routeIs('admin.my-banks.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>My Bank Accounts</p>
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </li>

                                <!-- Contact -->
                                @can('contact')
                                <li class="nav-item">
                                    <a href="{{ route('admin.contact.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.contact.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-phone"></i>
                                        <p>Contact Us</p>
                                    </a>
                                </li>
                                @endcan
                            @endcannot
                        @endcan

                        <!-- SystemWallet Menu Items -->
                        @can('system_wallet_access')
                            @cannot('owner_access')
                                <!-- System Wallet Dashboard -->
                                <li class="nav-item">
                                    <a href="{{ route('admin.system-wallet-dashboard.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.system-wallet-dashboard.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-wallet"></i>
                                        <p>Wallet Dashboard</p>
                                    </a>
                                </li>

                                <!-- System Reports -->
                                @can('report_check')
                                <li class="nav-item">
                                    <a href="{{ route('admin.system-reports.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.system-reports.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-chart-line"></i>
                                        <p>System Reports</p>
                                    </a>
                                </li>
                                @endcan

                                <!-- System Transactions -->
                                <li class="nav-item {{ request()->routeIs('admin.system-deposits.*', 'admin.system-withdrawals.*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-exchange-alt"></i>
                                        <p>
                                            System Finance
                                            <i class="fas fa-angle-left right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @can('deposit')
                                        <li class="nav-item">
                                            <a href="{{ route('admin.system-deposits.index') }}"
                                               class="nav-link {{ request()->routeIs('admin.system-deposits.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Deposits</p>
                                            </a>
                                        </li>
                                        @endcan
                                        @can('withdraw')
                                        <li class="nav-item">
                                            <a href="{{ route('admin.system-withdrawals.index') }}"
                                               class="nav-link {{ request()->routeIs('admin.system-withdrawals.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Withdrawals</p>
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcannot
                        @endcan
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <div class="content-wrapper">

            @yield('content')
        </div>
        <footer class="main-footer">
            <strong>Copyright &copy; 2025 <a href="">ShweDragon</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 3.2.2
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('js/adminlte.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    @yield('script')
    <script>
        var errorMessage = @json(session('error'));
        var successMessage = @json(session('success'));

        @if (session()->has('success'))
            toastr.success(successMessage)
        @elseif (session()->has('error'))
            toastr.error(errorMessage)
        @endif
    </script>
    <script>
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
            $('#ponewineTable').DataTable();
            $('#slotTable').DataTable();

            $("#mytable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "order": true,
                "pageLength": 10,
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl)
            })
        });
    </script>



</body>

</html>
