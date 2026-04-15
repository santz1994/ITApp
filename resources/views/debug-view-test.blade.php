@extends('layouts.app')

@section('title', 'Debug View Test')

@section('main-content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">🔍 Debug View Test</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-success">
                    <h4>✅ SUCCESS!</h4>
                    <p>Jika Anda melihat ini, berarti:</p>
                    <ul>
                        <li>✅ Layout app.blade.php berfungsi</li>
                        <li>✅ View rendering berfungsi</li>
                        <li>✅ CSS AdminLTE ter-load dengan benar</li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <h4>🧪 JavaScript Test:</h4>
                    <button onclick="testJS()" class="btn btn-primary">Test JavaScript</button>
                    <p id="js-result">JavaScript belum ditest</p>
                </div>
                
                <div class="alert alert-warning">
                    <h4>🎯 Kesimpulan:</h4>
                    <p>Jika halaman ini tampil dengan benar, masalah blank page ada di <strong>controller data</strong> atau <strong>JavaScript error</strong> di halaman spesifik.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function testJS() {
    document.getElementById('js-result').innerHTML = '✅ JavaScript berfungsi normal!';
    console.log('JavaScript test berhasil');
}

// Test console log
console.log('🔍 Debug view loaded successfully');
</script>
@endsection
