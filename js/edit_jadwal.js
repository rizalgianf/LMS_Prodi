function editJadwal(jadwal) {
    document.getElementById('id').value = jadwal.id;
    document.getElementById('mata_kuliah').value = jadwal.mata_kuliah;
    document.getElementById('dosen').value = jadwal.dosen_id;
    document.getElementById('hari').value = jadwal.hari;
    document.getElementById('tanggal').value = jadwal.tanggal;
    document.getElementById('waktu_mulai').value = jadwal.waktu_mulai;
    document.getElementById('waktu_selesai').value = jadwal.waktu_selesai;
}