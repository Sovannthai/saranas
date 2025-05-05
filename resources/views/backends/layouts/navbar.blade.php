<div class="main-header-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
        <a href="index.html" class="logo">
            <img src="{{ asset('backends/assets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand" class="navbar-brand"
                height="20" />
        </a>
        <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
            </button>
            <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
            </button>
        </div>
        <button class="topbar-toggler more">
            <i class="gg-more-vertical-alt"></i>
        </button>
    </div>
    <!-- End Logo Header -->
</div>
<!-- Navbar Header -->
<nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom" data-background-color="dark">
    <div class="container-fluid">
        {{-- <nav
            class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="submit" class="btn btn-search pe-1">
                        <i class="fa fa-search search-icon"></i>
                    </button>
                </div>
                <input type="text" placeholder="Search ..." class="form-control" />
            </div>
        </nav> --}}

        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
            <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                    aria-expanded="false" aria-haspopup="true">
                    <i class="fa fa-search"></i>
                </a>
                <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                        <div class="input-group">
                            <input type="text" placeholder="Search ..." class="form-control" />
                        </div>
                    </form>
                </ul>
            </li>
            {{-- <li class="nav-item topbar-icon dropdown hidden-caret">
                <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-comment"></i>
                    <span class="notification">{{ $unreadMessagesCount }}</span>
                </a>
                <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                    <li>
                        <div class="notif-scroll scrollbar-outer">
                            <div class="notif-center">
                                <a href="{{ route('user-request.index') }}">
                                    <div class="notif-icon notif-success">
                                        <i class="fa fa-comment"></i>
                                    </div>
                                    <div class="notif-content">

                                        <span class="block">
                                            @lang('Go to view messaages') <span
                                                class=" badge badge-danger">{{ $unreadMessagesCount }}</span>
                                        </span>
                                        <span class="time">1 minutes ago</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </li> --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ asset('backends/assets/img/flags/' . (Session::get('locale', 'en') == 'en' ? 'gb.png' : Session::get('locale') . '.png')) }}"
                        alt="" style="width: 20px;">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                    <li>
                        <a href="{{ route('change_language', 'kh') }}"
                            class="dropdown-item @if (Session::get('locale') == 'kh') active @endif">
                            <img src="{{ asset('backends/assets/img/flags/kh.png') }}" alt=""
                                style="width: 20px;" class="me-2"> @lang('Khmer')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('change_language', 'en') }}"
                            class="dropdown-item @if (Session::get('locale', 'en') == 'en') active @endif">
                            <img src="{{ asset('backends/assets/img/flags/gb.png') }}" alt=""
                                style="width: 20px;" class="me-2"> @lang('English')
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <div class="avatar-sm">
                        <img src="{{ auth()->user()->image ? asset('uploads/all_photo/' . auth()->user()->image) : auth()->user()->avatar }}"
                            alt="..." class="avatar-img rounded-circle" />
                    </div>
                    <span class="profile-username">
                        <span class="op-7">@lang('Hello'),</span>
                        <span class="fw-bold">{{ auth()->user()->name }}</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                        <li>
                            <div class="user-box">
                                <div class="avatar-lg">
                                    <img src="{{ auth()->user()->image ? asset('uploads/all_photo/' . auth()->user()->image) : auth()->user()->avatar }}"
                                        alt="image profile" class="avatar-img rounded" />
                                </div>
                                <div class="u-text text-white">
                                    <h4>{{ auth()->user()->name }}</h4>
                                    <p class="text-muted">{{ auth()->user()->email }}</p>
                                    <a href="{{ route('user.view_profile', ['id' => auth()->user()->id]) }}"
                                        class="btn btn-xs btn-secondary btn-sm">
                                        @lang('View Profile') <i class="fas fa-chevron-right fa-sm"></i></a>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item logout">
                                    <i class="fas fa-sign-out-alt"></i> @lang('Logout')</button>
                            </form>
                        </li>
                    </div>
                </ul>
            </li>
        </ul>
    </div>
</nav>
