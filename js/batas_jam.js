document.addEventListener('DOMContentLoaded', function() {
    var waktuMulai = document.getElementById('waktu_mulai');
    var waktuSelesai = document.getElementById('waktu_selesai');

    waktuMulai.addEventListener('change', validateTime);
    waktuSelesai.addEventListener('change', validateTime);

    function validateTime() {
        if (waktuMulai.value && waktuSelesai.value) {
            if (waktuSelesai.value <= waktuMulai.value) {
                alert('Waktu selesai harus lebih besar dari waktu mulai.');
                waktuSelesai.value = '';
            }
        }
    }
});