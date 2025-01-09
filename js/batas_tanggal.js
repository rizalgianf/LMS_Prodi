document.addEventListener('DOMContentLoaded', function() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal').setAttribute('min', today);
});