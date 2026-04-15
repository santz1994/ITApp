{{-- Wizard Form Partial --}}
@php
    $steps = $steps ?? [];
    $currentStep = $currentStep ?? 1;
    $totalSteps = count($steps);
@endphp

<div class="wizard-form">
    {{-- Wizard Steps Header --}}
    <div class="wizard-steps">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: {{ ($currentStep / $totalSteps) * 100 }}%">
                Step {{ $currentStep }} of {{ $totalSteps }}
            </div>
        </div>
        
        <ul class="wizard-nav">
            @foreach($steps as $index => $step)
                @php
                    $stepNumber = $index + 1;
                    $stepClass = '';
                    if ($stepNumber < $currentStep) {
                        $stepClass = 'completed';
                    } elseif ($stepNumber == $currentStep) {
                        $stepClass = 'active';
                    } else {
                        $stepClass = 'pending';
                    }
                @endphp
                
                <li class="wizard-step {{ $stepClass }}">
                    <span class="step-number">{{ $stepNumber }}</span>
                    <span class="step-title">{{ $step['title'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    
    {{-- Wizard Content --}}
    <div class="wizard-content">
        <div class="step-content">
            @if(isset($steps[$currentStep - 1]))
                <h3>{{ $steps[$currentStep - 1]['title'] }}</h3>
                @if(isset($steps[$currentStep - 1]['description']))
                    <p class="text-muted">{{ $steps[$currentStep - 1]['description'] }}</p>
                @endif
            @endif
            
            {{ $slot }}
        </div>
    </div>
    
    {{-- Wizard Navigation Buttons --}}
    <div class="wizard-navigation">
        @if($currentStep > 1)
            <button type="button" class="btn btn-default wizard-prev">
                <i class="fa fa-arrow-left"></i> Previous
            </button>
        @endif
        
        @if($currentStep < $totalSteps)
            <button type="button" class="btn btn-primary wizard-next pull-right">
                Next <i class="fa fa-arrow-right"></i>
            </button>
        @else
            <button type="submit" class="btn btn-success pull-right">
                <i class="fa fa-check"></i> Complete
            </button>
        @endif
    </div>
</div>

<style>
.wizard-form {
    background: #fff;
    border-radius: 5px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.wizard-steps {
    margin-bottom: 30px;
}

.wizard-steps .progress {
    height: 4px;
    margin-bottom: 20px;
    background-color: #f0f0f0;
}

.wizard-nav {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wizard-step {
    text-align: center;
    flex: 1;
    position: relative;
}

.wizard-step .step-number {
    display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    border-radius: 50%;
    background-color: #ddd;
    color: #666;
    font-weight: bold;
    margin-bottom: 5px;
}

.wizard-step .step-title {
    display: block;
    font-size: 12px;
    color: #666;
}

.wizard-step.completed .step-number {
    background-color: #5cb85c;
    color: white;
}

.wizard-step.active .step-number {
    background-color: #337ab7;
    color: white;
}

.wizard-step.active .step-title {
    color: #337ab7;
    font-weight: bold;
}

.wizard-content {
    min-height: 300px;
    margin-bottom: 30px;
}

.wizard-navigation {
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.wizard-navigation::after {
    content: "";
    display: table;
    clear: both;
}

@media (max-width: 768px) {
    .wizard-nav {
        flex-direction: column;
    }
    
    .wizard-step {
        margin-bottom: 10px;
    }
    
    .wizard-step .step-number {
        width: 25px;
        height: 25px;
        line-height: 25px;
        font-size: 12px;
    }
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Wizard navigation
    $('.wizard-next').on('click', function() {
        // Validate current step before proceeding
        var currentStepForm = $(this).closest('form');
        var isValid = true;
        
        // Check required fields in current step
        currentStepForm.find('.step-content input[required], .step-content select[required], .step-content textarea[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (isValid) {
            var nextStep = {{ $currentStep }} + 1;
            if (nextStep <= {{ $totalSteps }}) {
                // Navigate to next step (you may need to implement navigation logic)
                window.location.href = window.location.pathname + '?step=' + nextStep;
            }
        } else {
            showError('Please fill in all required fields before proceeding.');
        }
    });
    
    $('.wizard-prev').on('click', function() {
        var prevStep = {{ $currentStep }} - 1;
        if (prevStep >= 1) {
            window.location.href = window.location.pathname + '?step=' + prevStep;
        }
    });
});
</script>
@endpush