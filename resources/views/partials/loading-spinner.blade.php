{{-- Loading Spinner Partial --}}
<div id="loading-spinner" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
        <p class="loading-text">{{ $loadingText ?? 'Loading...' }}</p>
    </div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.loading-content {
    text-align: center;
    color: white;
}

.spinner {
    width: 70px;
    text-align: center;
    margin: 0 auto 20px;
}

.spinner > div {
    width: 18px;
    height: 18px;
    background-color: #fff;
    border-radius: 100%;
    display: inline-block;
    animation: sk-bouncedelay 1.4s infinite ease-in-out both;
}

.spinner .bounce1 {
    animation-delay: -0.32s;
}

.spinner .bounce2 {
    animation-delay: -0.16s;
}

@keyframes sk-bouncedelay {
    0%, 80%, 100% { 
        transform: scale(0);
    } 40% { 
        transform: scale(1.0);
    }
}

.loading-text {
    font-size: 16px;
    margin: 0;
}
</style>