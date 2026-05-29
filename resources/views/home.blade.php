@extends('layouts.app')

@section('main-content')

@component('components.page-header')
    @slot('icon') fa-dashboard @endslot
    @slot('title') Dashboard @endslot
    @slot('subtitle') Welcome back, {{ auth()->user()->name }}! @endslot
@endcomponent

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<section class="dashboard-container">
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ \App\MeetingRoomBooking::where('status', 'confirmed')->whereDate('booking_date', today())->count() }}</h3>
                    <p>Today's Meetings</p>
                </div>
                <div class="icon"><i class="fa fa-calendar"></i></div>
                <a href="{{ route('meeting-room-bookings.index') }}" class="small-box-footer">View All <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ \App\Vehicle::where('status', 'available')->count() }}</h3>
                    <p>Available Vehicles</p>
                </div>
                <div class="icon"><i class="fa fa-car"></i></div>
                <a href="{{ route('vehicles.index') }}" class="small-box-footer">View All <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ \App\InventoryItem::whereColumn('current_stock', '<', 'minimum_stock')->count() }}</h3>
                    <p>Low Stock Items</p>
                </div>
                <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
                <a href="{{ route('inventory.low-stock') }}" class="small-box-footer">View Alert <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ \App\ApprovalInstance::where('status', 'in_progress')->count() }}</h3>
                    <p>Pending Approvals</p>
                </div>
                <div class="icon"><i class="fa fa-check-circle"></i></div>
                <a href="{{ route('approvals.pending') }}" class="small-box-footer">Review <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o"></i> Recent Bookings</h3>
                </div>
                <div class="box-body">
                    @php
                        $recentBookings = \App\MeetingRoomBooking::with(['meetingRoom', 'user'])->latest()->take(5)->get();
                    @endphp
                    @if($recentBookings->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBookings as $booking)
                                <tr>
                                    <td>{{ $booking->meetingRoom->name ?? 'N/A' }}</td>
                                    <td>{{ $booking->subject ?? '-' }}</td>
                                    <td>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : '-' }}</td>
                                    <td><span class="label label-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'default') }}">{{ ucfirst($booking->status) }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center" style="padding: 20px;">No recent bookings.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-solid box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ route('meeting-room-bookings.create') }}" class="btn btn-info btn-block" style="margin-bottom: 10px;">
                        <i class="fa fa-calendar-plus-o"></i> Book Meeting Room
                    </a>
                    <a href="{{ route('vehicles.booking.create') }}" class="btn btn-success btn-block" style="margin-bottom: 10px;">
                        <i class="fa fa-car"></i> Book Vehicle
                    </a>
                    <a href="{{ route('inventory.request.create') }}" class="btn btn-warning btn-block" style="margin-bottom: 10px;">
                        <i class="fa fa-cubes"></i> Request Inventory
                    </a>
                    <a href="{{ route('approvals.pending') }}" class="btn btn-danger btn-block">
                        <i class="fa fa-check-circle"></i> Pending Approvals
                    </a>
                </div>
            </div>

            <div class="box box-solid box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> System Overview</h3>
                </div>
                <div class="box-body">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Users</span>
                            <span class="info-box-number">{{ \App\User::where('is_active', true)->count() }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-building"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Meeting Rooms</span>
                            <span class="info-box-number">{{ \App\MeetingRoom::count() }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-cube"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Inventory Items</span>
                            <span class="info-box-number">{{ \App\InventoryItem::count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
