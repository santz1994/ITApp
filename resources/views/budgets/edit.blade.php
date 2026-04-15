@extends('layouts.app')

@section('main-content')

{{-- All styles from centralized CSS: public/css/ui-enhancements.css --}}

{{-- Page Header --}}
@include('components.page-header', [
    'title' => 'Edit Budget',
    'subtitle' => $budget->division->name . ' - ' . $budget->year,
    'breadcrumbs' => [
        ['label' => 'Home', 'url' => route('home'), 'icon' => 'home'],
        ['label' => 'Budgets', 'url' => url('budgets')],
        ['label' => 'Edit']
    ]
])

<div class="container-fluid">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-warning"></i> Validation Errors</h4>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Budget Metadata --}}
    <div class="alert alert-info metadata-alert">
        <div class="row">
            <div class="col-md-4">
                <strong><i class="fa fa-hashtag"></i> Budget ID:</strong> #{{ $budget->id }}
            </div>
            <div class="col-md-4">
                <strong><i class="fa fa-calendar"></i> Created:</strong> 
                {{ $budget->created_at ? $budget->created_at->format('M d, Y') : 'N/A' }}
            </div>
            <div class="col-md-4">
                <strong><i class="fa fa-clock"></i> Last Updated:</strong> 
                {{ $budget->updated_at ? $budget->updated_at->format('M d, Y') : 'N/A' }}
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Form --}}
        <div class="col-md-8">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Edit Budget Details</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ url('budgets/' . $budget->id) }}" id="editBudgetForm">
                        @method('PATCH')
                        @csrf

                        <fieldset>
                            <legend>
                                <span class="form-section-icon"><i class="fa fa-money-bill-wave"></i></span>
                                Budget Information
                            </legend>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'division_id') }}">
                                        <label for="division_id">
                                            <i class="fa fa-sitemap"></i> Division <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control division_id" name="division_id" id="division_id" required>
                                            @foreach($divisions as $division)
                                                <option value="{{ $division->id }}" {{ $budget->division_id == $division->id ? 'selected' : '' }}>
                                                    {{ $division->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="help-text">Select the division for this budget</small>
                                        {{ hasErrorForField($errors, 'division_id') }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group {{ hasErrorForClass($errors, 'year') }}">
                                        <label for="year">
                                            <i class="fa fa-calendar"></i> Fiscal Year <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               name="year" 
                                               id="year" 
                                               class="form-control" 
                                               value="{{ $budget->year }}"
                                               min="2020"
                                               max="2099"
                                               placeholder="e.g., {{ date('Y') }}"
                                               required>
                                        <small class="help-text">Enter the fiscal year (e.g., {{ date('Y') }})</small>
                                        {{ hasErrorForField($errors, 'year') }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {{ hasErrorForClass($errors, 'total') }}">
                                        <label for="total">
                                            <i class="fa fa-money-bill-wave"></i> Budget Amount <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-addon">Rp</div>
                                            <input type="number" 
                                                   name="total" 
                                                   id="total" 
                                                   class="form-control" 
                                                   value="{{ $budget->total }}"
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="0.00"
                                                   required>
                                        </div>
                                        <small class="help-text">
                                            Enter the total budget amount in Rands. Current: <strong>Rp {{ number_format($budget->total, 2) }}</strong>
                                        </small>
                                        {{ hasErrorForField($errors, 'total') }}
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-warning btn-lg btn-gradient">
                                <i class="fa fa-save"></i> Update Budget
                            </button>
                            <a href="{{ url('budgets') }}" class="btn btn-default btn-lg">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-md-4">
            {{-- Edit Tips --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-lightbulb"></i> Edit Tips</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong><i class="fa fa-exclamation-triangle text-warning"></i> Important Notes:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><strong>Division Change:</strong> Changing the division may affect financial reports</li>
                        <li><strong>Fiscal Year:</strong> Ensure the year matches your accounting period</li>
                        <li><strong>Budget Amount:</strong> Changes will be reflected in spending tracking</li>
                        <li><strong>Historical Data:</strong> Past allocations and expenses remain unchanged</li>
                    </ul>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="box-body">
                    <a href="{{ url('budgets') }}" class="btn btn-default btn-block">
                        <i class="fa fa-arrow-left"></i> Back to Budgets
                    </a>
                    @if(url()->previous() != url()->current())
                        <a href="{{ url('budgets/' . $budget->id) }}" class="btn btn-info btn-block">
                            <i class="fa fa-eye"></i> View Budget Details
                        </a>
                    @endif
                    <hr>
                    <form method="POST" action="{{ url('budgets/' . $budget->id) }}" onsubmit="return confirm('Are you sure you want to delete this budget? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Delete Budget
                        </button>
                    </form>
                </div>
            </div>

            {{-- Best Practices --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-check-circle"></i> Best Practices</h3>
                </div>
                <div class="box-body">
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong>Budget Management:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li><strong>Regular Reviews:</strong> Quarterly budget vs. actual comparisons</li>
                        <li><strong>Variance Analysis:</strong> Track over/under spending patterns</li>
                        <li><strong>Reallocation:</strong> Adjust budgets mid-year if needed</li>
                        <li><strong>Documentation:</strong> Keep notes on budget changes</li>
                    </ul>
                    <hr>
                    <p style="font-size: 13px; margin-bottom: 10px;"><strong>Financial Planning:</strong></p>
                    <ul style="margin-left: 20px; font-size: 12px;">
                        <li>Monitor spending against budget monthly</li>
                        <li>Plan for known recurring expenses</li>
                        <li>Reserve contingency funds (10-15%)</li>
                        <li>Track asset lifecycle replacement costs</li>
                    </ul>
                </div>
            </div>

            {{-- Budget Summary --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Budget Summary</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed" style="font-size: 12px; margin-bottom: 0;">
                        <tr>
                            <td><strong><i class="fa fa-sitemap"></i> Division:</strong></td>
                            <td>{{ $budget->division->name }}</td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-calendar"></i> Year:</strong></td>
                            <td><span class="badge bg-blue">{{ $budget->year }}</span></td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-money-bill-wave"></i> Amount:</strong></td>
                            <td><strong style="color: #28a745;">Rp {{ number_format($budget->total, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong><i class="fa fa-calendar-plus"></i> Created:</strong></td>
                            <td>{{ $budget->created_at ? $budget->created_at->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $(".division_id").select2({
            placeholder: "-- Select Division --"
        });

        // Form validation
        $('#editBudgetForm').on('submit', function(e) {
            var division = $('#division_id').val();
            var year = $('#year').val();
            var total = $('#total').val();

            if (!division || !year || !total) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            if (parseFloat(total) <= 0) {
                e.preventDefault();
                alert('Budget amount must be greater than 0.');
                return false;
            }

            var yearNum = parseInt(year);
            if (yearNum < 2020 || yearNum > 2099) {
                e.preventDefault();
                alert('Please enter a valid fiscal year between 2020 and 2099.');
                return false;
            }
        });

        // Auto-dismiss alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@endpush


