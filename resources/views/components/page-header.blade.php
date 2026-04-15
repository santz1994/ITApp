{{-- Page Header Component --}}
{{-- Usage: @include('components.page-header', ['title' => 'Page Title', 'breadcrumbs' => [...], 'actions' => '...']) --}}
<div class="content-header">
    <div class="container-fluid">
        {{--
            Flexible page header
            - Accepts: $title (required), $subtitle (optional), $breadcrumbs (optional array), $actions (optional HTML)
            - New option: $actions_position = 'column' (default) | 'inline'
              * 'column' keeps original behaviour (actions in right column)
              * 'inline' places actions on the same row as the title using flexbox (responsive)
        --}}
        @php
            $actions_position = $actions_position ?? 'column';
        @endphp

        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                @if(isset($actions) && $actions_position === 'inline')
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="m-0">{{ $title }}</h1>
                            @if(isset($subtitle))
                                <p class="text-muted mb-0">{{ $subtitle }}</p>
                            @endif
                        </div>
                        <div class="ml-3 page-header-actions">
                            {!! $actions !!}
                        </div>
                    </div>
                    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                        <ol class="breadcrumb mt-2">
                            @foreach($breadcrumbs as $crumb)
                                <li class="breadcrumb-item {{ isset($crumb['url']) ? '' : 'active' }}">
                                    @if(isset($crumb['url']))
                                        <a href="{{ $crumb['url'] }}">
                                            @if(isset($crumb['icon']))
                                                <i class="fa fa-{{ $crumb['icon'] }}"></i>
                                            @endif
                                            {{ $crumb['label'] }}
                                        </a>
                                    @else
                                        @if(isset($crumb['icon']))
                                            <i class="fa fa-{{ $crumb['icon'] }}"></i>
                                        @endif
                                        {{ $crumb['label'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                @else
                    <h1 class="m-0">{{ $title }}</h1>
                    @if(isset($subtitle))
                        <p class="text-muted">{{ $subtitle }}</p>
                    @endif

                    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                        <ol class="breadcrumb mt-2">
                            @foreach($breadcrumbs as $crumb)
                                <li class="breadcrumb-item {{ isset($crumb['url']) ? '' : 'active' }}">
                                    @if(isset($crumb['url']))
                                        <a href="{{ $crumb['url'] }}">
                                            @if(isset($crumb['icon']))
                                                <i class="fa fa-{{ $crumb['icon'] }}"></i>
                                            @endif
                                            {{ $crumb['label'] }}
                                        </a>
                                    @else
                                        @if(isset($crumb['icon']))
                                            <i class="fa fa-{{ $crumb['icon'] }}"></i>
                                        @endif
                                        {{ $crumb['label'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                @endif
            </div>

            @if(isset($actions) && $actions_position !== 'inline')
                <div class="col-sm-6">
                    <div class="float-sm-right page-header-actions" style="text-align: right;">
                        <div class="action-buttons" style="display: inline-block;">
                            {!! $actions !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
