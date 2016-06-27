<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        @if (! Auth::guest())
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="{{ Gravatar::fallback(asset('la-assets/img/user2-160x160.jpg'))->get(Auth::user()->email) }}" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>{{ Auth::user()->name }}</p>
                    <!-- Status -->
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
        @endif

        <!-- search form (Optional) -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
        </form>
        <!-- /.search form -->

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="header">MODULES</li>
            <!-- Optionally, you can add icons to the links -->
            <li class="active"><a href="{{ url(config('laraadmin.adminRoute')) }}"><i class='fa fa-home'></i> <span>Dashboard</span></a></li>
            <!--
            <li><a href="#"><i class='fa fa-folder-open'></i> <span>Projects</span> <small class="label pull-right bg-red">2 Bugs</small></a></li>
            <li><a href="#"><i class='fa fa-building'></i> <span>Organisations</span></a></li>
            <li><a href="#"><i class='fa fa-newspaper-o'></i> <span>Contacts</span> <small class="label pull-right bg-green">2 New</small></a></li>
            <li><a href="#"><i class='fa fa-calendar'></i> <span>Calendar</span></a></li>
            -->
            <li><a href="{{ url(config('laraadmin.adminRoute') . '/books') }}"><i class='fa fa-book'></i> <span>Books</span></a></li>
            <!-- LAMenus -->
            
            
            <li class="treeview">
                <a href="#"><i class='fa fa-group'></i> <span>Team</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{ url(config('laraadmin.adminRoute') . '/employees') }}"><i class="fa fa-circle-o text-green"></i> <span>Employees</span></a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Departments</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Roles</a></li>
                    <!--
                    <li><a href="#"><i class="fa fa-circle-o text-red"></i> Access Control</a></li>
                    -->
                </ul>
            </li>
            <!--
            <li class="treeview">
                <a href="#"><i class='fa fa-paint-brush'></i> <span>My Settings</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Edit Profile</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Change Password</a></li>
                </ul>
            </li>
            <li class="treeview">
                <a href="#"><i class='fa fa-cogs'></i> <span>Company Settings</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o text-red"></i> Company Profile</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Work Types</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Task Modeling</a></li>
                </ul>
            </li>
            -->
        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
