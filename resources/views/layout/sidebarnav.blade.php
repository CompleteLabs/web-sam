 <!-- Main Sidebar Container -->
 <aside class="main-sidebar main-sidebar-custom sidebar-dark-primary elevation-4">
     <!-- Brand Logo -->
     <a href="#" class="brand-link">
         <img src="{{ asset('icon') }}/samsam.png"
             alt="Logo" class="brand-image" style="opacity: .8">
         <span class="brand-text font-weight-light"><strong>DASHBOARD</strong></span>
     </a>

     <!-- Sidebar -->
     <div class="sidebar">
         <!-- Sidebar user (optional) -->
         <div class="user-panel mt-3 pb-3 mb-3 d-flex">
             <div class="info">
                 <a href="/dashboard" class="d-block"><strong>HOME</strong></a>
             </div>
         </div>
         <!-- Sidebar Menu -->
         <nav class="mt-2">
             <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                 <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                @if (Auth::user()->role_id === 5)
                 <li class="nav-item">
                     <a href="/user" class="nav-link {{ $active === 'user' ? 'active' : '' }}">
                         <i class="nav-icon fas fa-users"></i>
                         <p>User</p>
                     </a>
                 </li>
                @endif
                @if (Auth::user()->role_id === 5 || Auth::user()->role_id === 4)
                 <li class="nav-item">
                     <a href="/outlet" class="nav-link {{ $active === 'outlet' ? 'active' : '' }}">
                         <i class="nav-icon fas fa-store"></i>
                         <p>Outlet</p>
                     </a>
                 </li>
                @endif
                @if (Auth::user()->role_id === 5 || Auth::user()->role_id === 4)
                 <li class="nav-item">
                     <a href="/noo" class="nav-link {{ $active === 'noo' ? 'active' : '' }}">
                         <i class="nav-icon fas fa-store-alt"></i>
                         <p>NOO</p>
                     </a>
                 </li>
                @endif
                @if (Auth::user()->role_id === 5 || Auth::user()->role_id === 4)
                 <li class="nav-item">
                     <a href="/visit" class="nav-link {{ $active === 'visit' ? 'active' : '' }}">
                         <i class="nav-icon fas fa-map-marker-alt"></i>
                         <p>Visit</p>
                     </a>
                 </li>
                @endif
                @if (Auth::user()->role_id === 5)
                 <li class="nav-item">
                     <a href="/planvisit" class="nav-link {{ $active === 'planvisit' ? 'active' : '' }}">
                         <i class="nav-icon fas fa-map-marked"></i>
                         <p>Plan Visit</p>
                     </a>
                 </li>
                @endif
                @if (Auth::user()->role_id === 5)
                <li class="nav-item">
                    <a href="#" class="nav-link {{ $active === 'report' ? 'active' : '' }}">
                        <i class="nav-icon far fa-file-alt"></i>
                        <p>
                            Report
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/report/noo" class="nav-link">
                                <i class="fas fa-store-alt nav-icon"></i>
                                <p>Noo/mounth</p>

                            </a>
                        </li>
                    </ul>
                </li>
            @endif
                @if (Auth::user()->role_id === 5)
                 <li class="nav-item">
                     <a href="#" class="nav-link {{ $active === 'setting' ? 'active' : '' }}">
                         <i class="nav-icon fas fa-cogs"></i>
                         <p>
                             Settings
                             <i class="right fas fa-angle-left"></i>
                         </p>
                     </a>
                     <ul class="nav nav-treeview">
                         <li class="nav-item">
                             <a href="/setting/role" class="nav-link">
                                 <i class="fas fa-user-cog nav-icon"></i>
                                 <p>Role</p>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a href="/setting/badanusaha" class="nav-link">
                                 <i class="fas fa-building nav-icon"></i>
                                 <p>Badan Usaha</p>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a href="/setting/divisi" class="nav-link">
                                 <i class="fas fa-briefcase nav-icon"></i>
                                 <p>Divisi</p>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a href="/setting/region" class="nav-link">
                                 <i class="fas fa-map-signs nav-icon"></i>
                                 <p>Region</p>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a href="/setting/cluster" class="nav-link">
                                 <i class="fas fa-map-pin nav-icon"></i>
                                 <p>Cluster</p>
                             </a>
                         </li>
                     </ul>
                 </li>
                @endif
             </ul>
         </nav>
         <!-- /.sidebar-menu -->
     </div>
     <!-- /.sidebar -->

     <div class="sidebar-custom">
         <a href="/dashboard/logout" class="btn btn-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
     </div>
     <!-- /.sidebar-custom -->
 </aside>
