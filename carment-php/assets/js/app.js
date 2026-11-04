// Konfirmasi sebelum aksi berbahaya: <form data-confirm="Pesan?">
document.querySelectorAll("form[data-confirm]").forEach((f) =>
  f.addEventListener("submit", (e) => {
    if (!confirm(f.dataset.confirm)) e.preventDefault();
  }),
);

// Ringkasan biaya reservasi (hanya tampilan; validasi final & hitung ulang tetap di server)
const fr = document.getElementById("form-res");
if (fr) {
  const rp = (n) => "Rp" + n.toLocaleString("id-ID");
  const hitung = () => {
    const a = fr.tgl_mulai.value,
      b = fr.tgl_selesai.value,
      out = document.getElementById("ringkasan");
    if (!a || !b) return;
    if (b < a) {
      out.innerHTML =
        '<span class="err">Tanggal selesai harus setelah tanggal mulai.</span>';
      return;
    }
    const n = Math.round((new Date(b) - new Date(a)) / 864e5) + 1,
      sewa = n * +fr.dataset.harga,
      dep = +fr.dataset.deposit;
    out.innerHTML = `Sewa ${n} hari: <b>${rp(sewa)}</b><br>Deposit (dikembalikan): <b>${rp(dep)}</b><br>Total dibayar: <b>${rp(sewa + dep)}</b>`;
  };
  fr.tgl_mulai.addEventListener("input", hitung);
  fr.tgl_selesai.addEventListener("input", hitung);
}

// >>> REVISI [BARU] #18: hitung mundur batas pelunasan (sampai akhir file)
// Hitung mundur batas pelunasan (.hitung[data-sisa] dalam detik); reload saat habis agar status ter-update
document.querySelectorAll(".hitung").forEach((el) => {
  let s = +el.dataset.sisa;
  const tampil = () => {
    el.textContent =
      String(Math.floor(s / 60)).padStart(2, "0") +
      ":" +
      String(s % 60).padStart(2, "0");
  };
  tampil();
  const t = setInterval(() => {
    s--;
    if (s <= 0) {
      clearInterval(t);
      location.reload();
      return;
    }
    tampil();
  }, 1000);
});
