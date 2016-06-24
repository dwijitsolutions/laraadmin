<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        @yield('contentheader_title', 'Page Header here')
        <small>@yield('contentheader_description')</small>
    </h1>
    @hasSection('headerElems')
        <span class="headerElems">
        @yield('headerElems')
        </span>
    @else 
        @hasSection('section')
        <ol class="breadcrumb">
            <li><a href="@yield('section_url')"><i class="fa fa-dashboard"></i> @yield('section')</a></li>
            @hasSection('sub_section')<li class="active"> @yield('sub_section') </li>@endif
        </ol>
        @endif
    @endif
</section>