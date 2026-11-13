<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
      aria-controls="sidebar-menu" aria-expanded="false" aria-label="Buka navigasi">
      <span class="navbar-toggler-icon"></span>
    </button>
    <h1 class="navbar-brand navbar-brand-autodark">
      <a href="/guru">
        <img src="{{ asset('assets/img/logo-attendify-white.svg') }}" width="140" height="36" alt="Attendify"
          class="navbar-brand-image">
      </a>
    </h1>
    <div class="navbar-nav flex-row d-lg-none">
      <div class="nav-item dropdown">
        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Menu pengguna">
          <span class="avatar avatar-sm attendify-admin-avatar">GR</span>
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
          <a href="/proseslogout" class="dropdown-item">Keluar</a>
        </div>
      </div>
    </div>
    <div class="collapse navbar-collapse" id="sidebar-menu">
      <ul class="navbar-nav pt-lg-3">
        <li class="nav-item {{ request()->is('guru') ? 'active' : '' }}">
          <a class="nav-link {{ request()->is('guru') ? 'active' : '' }}" href="/guru">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
              </svg>
            </span>
            <span class="nav-link-title">
              Beranda
            </span>
          </a>
        </li>
        <li class="nav-item {{ request()->is('monitoringguru*') ? 'active' : '' }}">
          <a class="nav-link {{ request()->is('monitoringguru*') ? 'active' : '' }}" href="monitoringguru">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-heart-rate-monitor">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z" />
                <path d="M7 20h10" />
                <path d="M9 16v4" />
                <path d="M15 16v4" />
                <path d="M7 10h2l2 3l2 -6l1 3h3" />
              </svg>
            </span>
            <span class="nav-link-title">
              Monitoring Presensi
            </span>
          </a>
        </li>
        <li class="nav-item {{ request()->is('izinsakitguru*') ? 'active' : '' }}">
          <a class="nav-link {{ request()->is('izinsakitguru*') ? 'active' : '' }}" href="izinsakitguru">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-check">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                <path d="M16 3v4" />
                <path d="M8 3v4" />
                <path d="M4 11h16" />
                <path d="M15 19l2 2l4 -4" />
              </svg>
            </span>
            <span class="nav-link-title">
              Data Pengajuan Izin
            </span>
          </a>
        </li>
        <li class="nav-item dropdown {{ request()->is(['laporanguru*', 'rekapguru*']) ? 'active' : '' }}">
          <a class="nav-link dropdown-toggle {{ request()->is(['laporanguru*', 'rekapguru*']) ? 'show' : '' }}" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false"
            role="button" aria-expanded="{{ request()->is(['laporanguru*', 'rekapguru*']) ? 'true' : 'false' }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-file-description">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                <path d="M9 17h6" />
                <path d="M9 13h6" />
              </svg>
            </span>
            <span class="nav-link-title">
              Laporan
            </span>
          </a>
          <div class="dropdown-menu {{ request()->is(['laporanguru*', 'rekapguru*']) ? 'show' : '' }}">
            <div class="dropdown-menu-columns">
              <div class="dropdown-menu-column">
                <a class="dropdown-item {{ request()->is('laporanguru*') ? 'active' : '' }}" href="laporanguru">
                  Presensi
                </a>
                <a class="dropdown-item {{ request()->is('rekapguru*') ? 'active' : '' }}" href="rekapguru">
                  Rekap Presensi
                </a>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</aside>
