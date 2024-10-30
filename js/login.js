// js/login.js

// Fungsi untuk mengecek parameter URL dan menampilkan alert
function checkError() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        switch (error) {
            case 'wrongpassword':
                alert('Password salah!');
                break;
            case 'usernotfound':
                alert('Username tidak ditemukan!');
                break;
            case 'emptyfields':
                alert('Silakan isi form login terlebih dahulu.');
                break;
            case 'invalidrole':
                alert('Role tidak valid!');
                break;
            case 'sqlerror':
                alert('Kesalahan pada sistem. Silakan coba lagi nanti.');
                break;
        }
    }
}

// Panggil fungsi checkError saat halaman selesai dimuat
window.onload = checkError;
