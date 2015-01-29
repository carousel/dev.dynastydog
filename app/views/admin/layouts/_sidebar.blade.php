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