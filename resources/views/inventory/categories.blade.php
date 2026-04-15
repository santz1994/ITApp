@extends('layouts.app')

@section('title')
Asset Categories
@endsection

@                <a href="{{ route('assets.index', ['category' => $category['id']]) }}" class="btn btn-sm btn-primary">
                  <i class="fa fa-eye"></i> View Assets
                </a>tion('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">
          <i class="fa fa-cubes"></i> Asset Categories Overview
        </h3>
        <div class="box-tools pull-right">
          <a href="{{ route('assets.index') }}" class="btn btn-sm btn-default">
            <i class="fa fa-arrow-left"></i> Back to Assets
          </a>
        </div>
      </div>
      
      <div class="box-body">
        <div class="row">
          <div class="col-md-3">
            <div class="info-box bg-blue">
              <span class="info-box-icon"><i class="fa fa-desktop"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Total Assets</span>
                <span class="info-box-number">{{ $totalAssets }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info-box bg-green">
              <span class="info-box-icon"><i class="fa fa-check"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Categories</span>
                <span class="info-box-number">{{ $categoryData->count() }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info-box bg-yellow">
              <span class="info-box-icon"><i class="fa fa-star"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Most Common</span>
                <span class="info-box-number">{{ $categoryData->sortByDesc('count')->first()['name'] ?? 'N/A' }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info-box bg-red">
              <span class="info-box-icon"><i class="fa fa-percent"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Coverage</span>
                <span class="info-box-number">100%</span>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          @foreach($categoryData as $category)
          <div class="col-md-4">
            <div class="box box-widget">
              <div class="box-header with-border">
                <div class="user-block">
                  <span class="username">
                    <i class="fa fa-{{ $category['icon'] }}"></i> {{ $category['name'] }}
                  </span>
                  <span class="description">{{ $category['count'] }} assets</span>
                </div>
                <div class="box-tools">
                  <span class="label label-primary">{{ $category['percentage'] }}%</span>
                </div>
              </div>
              
              <div class="box-body">
                <div class="progress">
                  <div class="progress-bar progress-bar-primary" style="width: {{ $category['percentage'] }}%"></div>
                </div>
                <p>
                  {{ $category['count'] }} of {{ $totalAssets }} total assets
                </p>
                <a href="{{ route('inventory.index', ['category' => $category['id']]) }}" class="btn btn-sm btn-primary">
                  <i class="fa fa-eye"></i> View Assets
                </a>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection