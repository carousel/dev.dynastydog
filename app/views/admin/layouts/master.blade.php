@include('admin/layouts/_header')

<div class="main row">
    @if ( ! empty($sidebarGroups))
    <div class="sidebar col-xs-3">
        <div class="panel panel-default">
            @foreach($sidebarGroups as $sidebarGroup)
            <ul class="list-group">
                <li class="list-group-item">
                    <h4 class="list-group-item-heading">{{ $sidebarGroup['heading'] }}</h4>
                </li>
                @foreach($sidebarGroup['items'] as $sidebarGroupItem)
                <a href="{{ $sidebarGroupItem['url'] }}" class="list-group-item">{{ $sidebarGroupItem['title'] }}</a>
                @endforeach
            </ul>
            @endforeach
        </div>
    </div>
    <div class="content col-xs-9">
    @else
    <div class="content col-xs-12">
    @endif
        <div class="panel panel-default">
            <div class="panel-body">
                <!-- Breadcrumbs -->
                {{ Breadcrumbs::renderIfExists() }}

                <!-- Notifications -->
                @include('frontend/notifications/basic')

                <!-- Content -->
                @yield('content')
            </div>
        </div>
    </div>
</div>

@include('admin/layouts/_footer')