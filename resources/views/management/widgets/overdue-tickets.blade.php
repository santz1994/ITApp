<div class="col-md-4">
    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Recent Overdue Tickets</h3>
            <div class="box-tools pull-right">
                <a href="{{ route('tickets.index', ['status' => 'overdue']) }}" class="btn btn-sm btn-danger">View All</a>
            </div>
        </div>
        <div class="box-body">
            @if(isset($overview['recent_overdue_tickets']) && count($overview['recent_overdue_tickets']) > 0)
                <ul class="list-group">
                    @foreach($overview['recent_overdue_tickets'] as $t)
                        <li class="list-group-item">
                            <a href="{{ route('tickets.show', $t->id) }}">{{ $t->ticket_code }} - {{ \Illuminate\Support\Str::limit($t->subject, 40) }}</a>
                            <span class="pull-right text-muted small">Due {{ $t->due_date ? \Carbon\Carbon::parse($t->due_date)->diffForHumans() : 'Unknown' }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">No overdue tickets</p>
            @endif
        </div>
    </div>
</div>
