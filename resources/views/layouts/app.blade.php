<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Aplikasi Cukai')</title>

    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
    @stack('vendor-styles')
    @stack('styles')

    <style>
        :root {
            --ui-bg: #f4f7fb;
            --ui-surface: #ffffff;
            --ui-surface-muted: #f8fafc;
            --ui-border: #e2e8f0;
            --ui-border-strong: #d7e0ec;
            --ui-text: #0f172a;
            --ui-text-soft: #64748b;
            --ui-primary: #2563eb;
            --ui-success: #16a34a;
            --ui-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--ui-bg);
            color: var(--ui-text);
            font-size: .92rem;
        }

        #content {
            background: var(--ui-bg);
        }

        .sidebar .nav-item .nav-link {
            letter-spacing: 0;
            padding-top: .8rem;
            padding-bottom: .8rem;
        }

        .sidebar .nav-item .nav-link span,
        .sidebar .collapse-item {
            font-size: .89rem;
        }

        .sidebar .collapse-inner {
            border-radius: .75rem;
            margin: .35rem .8rem .7rem;
            padding: .45rem;
        }

        .sidebar .collapse-item {
            border-radius: .6rem;
            margin-bottom: .2rem;
            padding: .6rem .85rem;
        }

        .sidebar .collapse-item.active,
        .sidebar .collapse-item:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .topbar {
            border-bottom: 1px solid rgba(226, 232, 240, .8);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04) !important;
        }

        .topbar .navbar-search input,
        .form-control,
        .custom-file-label,
        .custom-select {
            font-size: .9rem;
        }

        .container-fluid {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .content-card {
            background: var(--ui-surface);
            border: 1px solid var(--ui-border);
            border-radius: .8rem;
            box-shadow: var(--ui-shadow);
        }

        .card {
            background: var(--ui-surface);
            border: 1px solid var(--ui-border);
            border-radius: .8rem;
            box-shadow: var(--ui-shadow) !important;
        }

        .card-header {
            background: var(--ui-surface);
            border-bottom: 1px solid var(--ui-border);
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .card-header h6,
        .card-header .m-0 {
            color: var(--ui-text);
            font-size: .95rem;
            font-weight: 700;
        }

        .btn {
            border-radius: .7rem;
            font-size: .88rem;
            font-weight: 600;
            letter-spacing: 0;
            min-height: 38px;
            padding: .52rem .88rem;
        }

        .btn-sm {
            min-height: 34px;
            padding: .42rem .75rem;
        }

        .btn-light {
            background: #fff;
            border-color: var(--ui-border);
            color: #334155;
        }

        .btn-outline-secondary,
        .btn-outline-primary,
        .btn-outline-success,
        .btn-outline-danger {
            background: #fff;
        }

        .badge {
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 0;
            padding: .42rem .6rem;
        }

        .form-control,
        .custom-file-label,
        .custom-select {
            background: #fff;
            border: 1px solid var(--ui-border-strong);
            border-radius: .72rem;
            box-shadow: none;
            color: var(--ui-text);
            min-height: 42px;
        }

        textarea.form-control {
            min-height: 84px;
        }

        .form-control:focus,
        .custom-select:focus,
        .custom-file-input:focus ~ .custom-file-label {
            border-color: #93c5fd;
            box-shadow: 0 0 0 .18rem rgba(37, 99, 235, .12);
        }

        .custom-file-label::after {
            background: #f8fafc;
            border-left: 1px solid var(--ui-border);
            border-radius: 0 .72rem .72rem 0;
            color: #334155;
            font-weight: 600;
            height: calc(100% - 2px);
        }

        .form-group label,
        label {
            color: #334155;
            font-size: .82rem;
            font-weight: 700;
            margin-bottom: .45rem;
        }

        .form-text,
        small.text-muted,
        .text-muted {
            color: var(--ui-text-soft) !important;
        }

        .table th,
        .table td {
            border-color: #edf2f7;
            font-size: .875rem;
            padding: .7rem .72rem;
            vertical-align: middle;
        }

        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .table-hover tbody tr:hover {
            background: rgba(37, 99, 235, .035);
        }

        .table-responsive {
            scrollbar-width: thin;
        }

        .alert {
            border: 0;
            border-radius: .8rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        }

        .modal-content {
            border: 1px solid var(--ui-border);
            border-radius: .9rem;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .12);
        }

        .modal-header {
            border-bottom: 1px solid var(--ui-border);
        }

        .page-subtitle {
            color: var(--ui-text-soft);
            font-size: .9rem;
            margin-top: .2rem;
        }

        .page-subtitle:empty {
            display: none;
        }

        [title] {
            text-underline-offset: 2px;
        }

        .tooltip {
            font-size: .76rem;
        }

        .tooltip-inner {
            background: #0f172a;
            border-radius: .65rem;
            line-height: 1.45;
            max-width: 320px;
            padding: .55rem .7rem;
            text-align: left;
            white-space: pre-line;
        }

        .sticky-footer {
            background: transparent !important;
        }

        @media (max-width: 767.98px) {
            body {
                font-size: .9rem;
            }

            .container-fluid {
                padding-left: .9rem;
                padding-right: .9rem;
            }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Cukai App</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item {{ request()->routeIs('home') || request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <a class="nav-link {{ request()->routeIs('projects.*') ? '' : 'collapsed' }}"
                   href="#"
                   data-toggle="collapse"
                   data-target="#collapseProject"
                   aria-expanded="{{ request()->routeIs('projects.*') ? 'true' : 'false' }}"
                   aria-controls="collapseProject">
                    <i class="fas fa-fw fa-folder-open"></i>
                    <span>Project</span>
                </a>
                <div id="collapseProject"
                     class="collapse {{ request()->routeIs('projects.*') ? 'show' : '' }}"
                     data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item {{ request()->routeIs('projects.index') || request()->routeIs('projects.create') || request()->routeIs('projects.edit') || request()->routeIs('projects.show') ? 'active' : '' }}"
                           href="{{ route('projects.index') }}">
                            Daftar Project
                        </a>
                        <a class="collapse-item {{ request()->routeIs('projects.monitoring') ? 'active' : '' }}"
                           href="{{ route('projects.monitoring') }}">
                            Monitoring Project
                        </a>
                    </div>
                </div>
            </li>

            <li class="nav-item {{ request()->routeIs('perusahaan.*') || request()->routeIs('pegawai.*') || request()->routeIs('tujuan-st.*') ? 'active' : '' }}">
                <a class="nav-link {{ request()->routeIs('perusahaan.*') || request()->routeIs('pegawai.*') || request()->routeIs('tujuan-st.*') ? '' : 'collapsed' }}"
                   href="#"
                   data-toggle="collapse"
                   data-target="#collapseReferensi"
                   aria-expanded="{{ request()->routeIs('perusahaan.*') || request()->routeIs('pegawai.*') || request()->routeIs('tujuan-st.*') ? 'true' : 'false' }}"
                   aria-controls="collapseReferensi">
                    <i class="fas fa-fw fa-book"></i>
                    <span>Referensi</span>
                </a>
                <div id="collapseReferensi"
                     class="collapse {{ request()->routeIs('perusahaan.*') || request()->routeIs('pegawai.*') || request()->routeIs('tujuan-st.*') ? 'show' : '' }}"
                     aria-labelledby="headingReferensi"
                     data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item {{ request()->routeIs('perusahaan.*') ? 'active' : '' }}" href="{{ route('perusahaan.index') }}">
                            Perusahaan
                        </a>
                        <a class="collapse-item {{ request()->routeIs('pegawai.*') ? 'active' : '' }}" href="{{ route('pegawai.index') }}">
                            Pegawai
                        </a>
                        <a class="collapse-item {{ request()->routeIs('tujuan-st.*') ? 'active' : '' }}" href="{{ route('tujuan-st.index') }}">
                            Tujuan ST
                        </a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle" type="button"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" type="button">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="d-none d-sm-inline-block text-gray-700">
                        <span class="font-weight-bold">@yield('topbar-title', 'Aplikasi Cukai')</span>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    {{ Auth::user()->name ?? 'User' }}
                                </span>
                                <i class="fas fa-user-circle fa-lg text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                 aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    @yield('page-title', 'Dashboard')
                                @endisset
                            </h1>
                            <p class="mb-0 page-subtitle">@yield('page-subtitle')</p>
                        </div>
                        @yield('page-actions')
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger shadow-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Aplikasi Cukai {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>
    @stack('vendor-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && jQuery.fn.tooltip) {
                jQuery('[data-toggle="tooltip"], [title]').tooltip({
                    container: 'body',
                    trigger: 'hover'
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
