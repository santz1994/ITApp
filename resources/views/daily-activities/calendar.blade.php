@extends('layouts.app')

@section('main-content')
    @include('components.page-header', [
        'title' => 'Activity Calendar',
        'subtitle' => 'Visual calendar view of all daily activities',
        'icon' => 'fa-calendar',
        'breadcrumbs' => [
            ['label' => 'Home', 'url' => url('/home'), 'icon' => 'dashboard'],
            ['label' => 'Daily Activities', 'url' => route('daily-activities.index')],
            ['label' => 'Calendar View']
        ]
    ])

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-calendar"></i> Activity Calendar</h3>
                    </div>
                    <div class="box-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script>
$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        editable: false,
        eventLimit: true,
        events: function(start, end, timezone, callback) {
            // AJAX call to fetch activities
            $.ajax({
                url: '{{ route("daily-activities.calendar-data") }}',
                type: 'GET',
                data: {
                    start: start.format(),
                    end: end.format()
                },
                success: function(data) {
                    var events = [];
                    if (data && Array.isArray(data)) {
                        events = data.map(function(activity) {
                            return {
                                id: activity.id,
                                title: activity.title,
                                start: activity.start,
                                allDay: activity.allDay,
                                backgroundColor: activity.backgroundColor,
                                borderColor: activity.borderColor,
                                extendedProps: activity.extendedProps
                            };
                        });
                    }
                    callback(events);
                },
                error: function(xhr, status, error) {
                    console.error('Could not fetch calendar data:', error);
                    callback([]);
                }
            });
        },
        eventClick: function(event) {
            if (event.id) {
                window.location.href = '/daily-activities/' + event.id;
            }
        },
        dayClick: function(date) {
            window.location.href = '/daily-activities/create?date=' + date.format('YYYY-MM-DD');
        }
    });
});
</script>
@endpush

@endsection
