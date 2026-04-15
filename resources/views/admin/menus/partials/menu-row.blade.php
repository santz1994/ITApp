<tr data-menu-id="{{ $menu->id }}" data-level="{{ $level }}" data-parent-id="{{ $menu->parent_id }}">
    <td class="text-center">
        <span class="badge bg-gray">{{ $menu->id }}</span>
    </td>
    <td class="menu-row-level-{{ $level }}">
        @if($menu->children && $menu->children->count() > 0)
            <button class="btn-toggle-children" data-menu-id="{{ $menu->id }}">
                <i class="fa fa-minus-square"></i>
            </button>
        @endif
        
        @if($level > 0)
            <i class="fa fa-level-up fa-rotate-90 text-muted" style="margin-right: 5px;"></i>
        @endif
        
        <strong>{{ $menu->label }}</strong>
        
        @if($menu->description)
            <i class="fa fa-info-circle text-muted" 
               title="{{ $menu->description }}" 
               data-toggle="tooltip"></i>
        @endif
    </td>
    <td>
        @if($menu->route)
            <span class="label label-primary" title="Laravel Route">
                <i class="fa fa-code"></i> {{ $menu->route }}
            </span>
        @elseif($menu->url)
            <span class="label label-info" title="Direct URL">
                <i class="fa fa-link"></i> {{ strlen($menu->url) > 25 ? substr($menu->url, 0, 25) . '...' : $menu->url }}
            </span>
            @if($menu->is_external)
                <span class="label label-warning" title="Opens in new window">
                    <i class="fa fa-external-link"></i> External
                </span>
            @endif
        @else
            <span class="text-muted"><i class="fa fa-folder-o"></i> Parent Menu</span>
        @endif
    </td>
    <td class="text-center">
        @if($menu->icon)
            <div>
                <span class="menu-icon-preview">
                    <i class="{{ $menu->icon }}"></i>
                </span>
                <br>
                <small class="text-muted">{{ $menu->icon }}</small>
            </div>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-center">
        <span class="badge {{ $menu->order_index == 0 ? 'bg-gray' : 'bg-blue' }}">
            {{ $menu->order_index }}
        </span>
    </td>
    <td class="text-center">
        @if($menu->is_active)
            <span class="label label-success badge-status">
                <i class="fa fa-check-circle"></i> Active
            </span>
        @else
            <span class="label label-danger badge-status">
                <i class="fa fa-times-circle"></i> Inactive
            </span>
        @endif
    </td>
    <td style="white-space: nowrap; vertical-align: middle; text-align: center;">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('admin.menus.edit', $menu->id) }}" 
               class="btn btn-sm btn-primary" 
               title="Edit Menu">
                <i class="fa fa-edit"></i>
            </a>
            <a href="{{ route('admin.menus.permissions', $menu->id) }}" 
               class="btn btn-sm btn-info" 
               title="Manage Permissions">
                <i class="fa fa-shield"></i>
            </a>
            <button type="button" 
                    class="btn btn-sm btn-{{ $menu->is_active ? 'warning' : 'success' }} btn-toggle-status" 
                    data-id="{{ $menu->id }}"
                    data-status="{{ $menu->is_active }}"
                    title="{{ $menu->is_active ? 'Deactivate Menu' : 'Activate Menu' }}">
                <i class="fa fa-toggle-{{ $menu->is_active ? 'on' : 'off' }}"></i>
            </button>
            <button type="button" 
                    class="btn btn-sm btn-danger btn-delete-menu" 
                    data-id="{{ $menu->id }}"
                    data-name="{{ $menu->label }}"
                    title="Delete Menu">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

{{-- Recursively display children --}}
@if($menu->children && $menu->children->count() > 0)
    @foreach($menu->children as $child)
        @include('admin.menus.partials.menu-row', ['menu' => $child, 'level' => $level + 1])
    @endforeach
@endif
