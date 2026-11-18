// Konfirmasi sebelum aksi berbahaya: "Pesan?"
document.querySelectorAll('form[data-confirm]').forEach(f => {
  f.addEventListener('submit', e => {
    if (!confirm(f.dataset.confirm)) {
      e.preventDefault();
    }
  });
});

// Ringkasan biaya reservasi (hanya tampilan; validasi final & hitung ulang tetap di server)
const fr = document.getElementById('form-res');
if (fr) {
  const rp = n => 'Rp' + n.toLocaleString('id-ID');
  
  const hitung = () => {
    const a = fr.tgl_mulai.value;
    const b = fr.tgl_selesai.value;
    const out = document.getElementById('ringkasan');
    
    if (!a || !b) return;
    
    if (b < a) {
      out.innerHTML = '<span class="err">Tanggal selesai harus setelah tanggal mulai.</span>';
      return;
    }
    
    const n = Math.round((new Date(b) - new Date(a)) / 864e5) + 1;
    const sewa = n * +fr.dataset.harga;
    const dep = +fr.dataset.deposit;
    
    out.innerHTML = `Sewa ${n} hari: <b>${rp(sewa)}</b><br>Deposit (dikembalikan): <b>${rp(dep)}</b><br>Total dibayar: <b>${rp(sewa + dep)}</b>`;
  };

  fr.tgl_mulai.addEventListener('input', hitung);
  fr.tgl_selesai.addEventListener('input', hitung);
}

// Hitung mundur batas pelunasan (.hitung[data-sisa] dalam detik); reload saat habis agar status ter-update
document.querySelectorAll('.hitung').forEach(el => {
  let s = +el.dataset.sisa;
  
  const tampil = () => {
    el.textContent = String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
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

// popup verifikasi pembayaran (Terima/Tolak + konfirmasi tambahan) dan popup konfirmasi umum
(() => {
  const dlg = document.getElementById('dlg-verif');
  
  if (dlg) {
    const q = s => dlg.querySelector(s);
    const panel = {
      aksi: q('#v-aksi'),
      terima: q('#v-form-terima'),
      tolak: q('#v-form-tolak')
    };
    
    const tampil = k => {
      Object.entries(panel).forEach(([n, el]) => {
        el.hidden = n !== k;
      });
      if (k === 'tolak') {
        q('#v-alasan').focus();
      }
    };
    
    document.querySelectorAll('[data-verif]').forEach(b => {
      b.addEventListener('click', () => {
        const d = b.dataset;
        q('#v-info').textContent = d.pelanggan + ' - ' + d.unit + ' (' + d.tgl + ') - Total ' + d.total;
        
        const w = q('#v-bukti');
        w.innerHTML = '';
        
        const el = document.createElement(d.pdf === '1' ? 'iframe' : 'img');
        el.src = d.bukti;
        el.title = 'Bukti pembayaran';
        if (d.pdf !== '1') {
          el.alt = 'Bukti pembayaran';
        }
        w.appendChild(el);
        
        dlg.querySelectorAll('.v-id').forEach(i => {
          i.value = d.id;
        });
        
        q('#v-alasan').value = '';
        tampil('aksi');
        dlg.showModal();
      });
    });
    
    q('#v-terima').onclick = () => tampil('terima');
    q('#v-tolak').onclick = () => tampil('tolak');
    
    dlg.querySelectorAll('[data-kembali]').forEach(b => {
      b.onclick = () => tampil('aksi');
    });
    
    dlg.querySelectorAll('[data-tutup]').forEach(b => {
      b.onclick = () => dlg.close();
    });
  }
  
  // Popup konfirmasi umum: "Pesan konfirmasi"
  document.querySelectorAll('form[data-popup]').forEach(f => {
    f.addEventListener('submit', e => {
      e.preventDefault();
      let p = document.getElementById('dlg-popup');
      
      if (!p) {
        p = document.createElement('dialog');
        p.id = 'dlg-popup';
        p.innerHTML = '<div class="dlg"><h3>Konfirmasi</h3><p id="pp-pesan"></p><div class="dlg-aksi"><button type="button" class="btn" id="pp-ya">Ya, lanjutkan</button><button type="button" class="btn alt" id="pp-batal">Batal</button></div></div>';
        document.body.appendChild(p);
      }
      
      p.querySelector('#pp-pesan').textContent = f.dataset.popup;
      p.querySelector('#pp-ya').onclick = () => {
        p.close();
        HTMLFormElement.prototype.submit.call(f);
      };
      p.querySelector('#pp-batal').onclick = () => p.close();
      p.showModal();
    });
  });
})();