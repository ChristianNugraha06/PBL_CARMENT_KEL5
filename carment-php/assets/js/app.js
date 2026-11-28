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

// hitung mundur batas pelunasan
// Hitung mundur batas pelunasan (.hitung[data-sisa] dalam detik); reload saat habis agar status ter-update
document.querySelectorAll(".hitung").forEach((el) => {
  let s = +el.dataset.sisa;
  // hitung mundur 24 jam tampil sebagai jam:menit:detik
  const tampil = () => {
    const j = Math.floor(s / 3600),
      m = Math.floor((s % 3600) / 60);
    el.textContent =
      (j ? j + ":" : "") +
      String(m).padStart(2, "0") +
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

// popup verifikasi pembayaran (Terima/Tolak + konfirmasi tambahan) dan popup konfirmasi umum
(() => {
  const dlg = document.getElementById("dlg-verif");
  if (dlg) {
    const q = (s) => dlg.querySelector(s),
      panel = {
        aksi: q("#v-aksi"),
        terima: q("#v-form-terima"),
        tolak: q("#v-form-tolak"),
      };
    const tampil = (k) => {
      Object.entries(panel).forEach(([n, el]) => {
        el.hidden = n !== k;
      });
      if (k === "tolak") q("#v-alasan").focus();
    };
    document.querySelectorAll("[data-verif]").forEach((b) =>
      b.addEventListener("click", () => {
        const d = b.dataset;
        q("#v-info").textContent =
          d.pelanggan + " - " + d.unit + " (" + d.tgl + ") - Total " + d.total;
        const w = q("#v-bukti");
        w.innerHTML = "";
        const el = document.createElement(d.pdf === "1" ? "iframe" : "img");
        el.src = d.bukti;
        el.title = "Bukti pembayaran";
        if (d.pdf !== "1") el.alt = "Bukti pembayaran";
        w.appendChild(el);
        dlg.querySelectorAll(".v-id").forEach((i) => {
          i.value = d.id;
        });
        q("#v-alasan").value = "";
        tampil("aksi");
        dlg.showModal();
      }),
    );
    q("#v-terima").onclick = () => tampil("terima");
    q("#v-tolak").onclick = () => tampil("tolak");
    dlg.querySelectorAll("[data-kembali]").forEach((b) => {
      b.onclick = () => tampil("aksi");
    });
    dlg.querySelectorAll("[data-tutup]").forEach((b) => {
      b.onclick = () => dlg.close();
    });
  }
  // popup konfirmasi kedua di tengah layar untuk FORM dan TAUTAN: data-popup="pesan" [data-ya="label tombol"]
  const popup = (pesan, ya, bahaya, onYa) => {
    let p = document.getElementById("dlg-popup");
    if (!p) {
      p = document.createElement("dialog");
      p.id = "dlg-popup";
      p.setAttribute("aria-labelledby", "pp-judul");
      p.innerHTML =
        '<div class="dlg"><h3 id="pp-judul">Konfirmasi</h3><p id="pp-pesan"></p><div class="dlg-aksi"><button type="button" class="btn" id="pp-ya"></button><button type="button" class="btn alt" id="pp-batal">Batal</button></div></div>';
      document.body.appendChild(p);
    }
    p.querySelector("#pp-pesan").textContent = pesan;
    const y = p.querySelector("#pp-ya");
    y.textContent = ya || "Ya, lanjutkan";
    y.className = "btn" + (bahaya ? " bad" : "");
    y.onclick = () => {
      p.close();
      onYa();
    };
    p.querySelector("#pp-batal").onclick = () => p.close();
    p.showModal();
  };
  document.querySelectorAll("form[data-popup]").forEach((f) =>
    f.addEventListener("submit", (e) => {
      e.preventDefault();
      popup(f.dataset.popup, f.dataset.ya, !!f.querySelector(".btn.bad"), () =>
        HTMLFormElement.prototype.submit.call(f),
      );
    }),
  );
  document.querySelectorAll("a[data-popup]").forEach((a) =>
    a.addEventListener("click", (e) => {
      e.preventDefault();
      popup(a.dataset.popup, a.dataset.ya, false, () => {
        location.href = a.href;
      });
    }),
  );
  // kolom masalah maintenance hanya muncul (dan wajib) saat status diubah ke Maintenance
  const st = document.getElementById("st"),
    wm = document.getElementById("wrap-masalah");
  if (st && wm) {
    const t = () => {
      const perlu =
        st.value === "Maintenance" && st.dataset.awal !== "Maintenance";
      wm.hidden = !perlu;
      wm.querySelector("textarea").required = perlu;
    };
    st.addEventListener("change", t);
    t();
  }
})();

// tombol lihat/sembunyikan kata sandi, bayangan header saat scroll, animasi muncul halus saat di-scroll
(() => {
  const SVG = (d) =>
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    d +
    "</svg>";
  const MATA = SVG(
    '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
  );
  const MATA_CORET = SVG(
    '<path d="M17.9 17.9A10.9 10.9 0 0 1 12 19C5.5 19 2 12 2 12a18.4 18.4 0 0 1 5.1-5.9M9.9 4.2A10.4 10.4 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.2 3.2M1 1l22 22"/><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/>',
  );
  document.querySelectorAll('input[type="password"]').forEach((inp) => {
    const w = document.createElement("span");
    w.className = "sandi-wrap";
    inp.parentNode.insertBefore(w, inp);
    w.appendChild(inp);
    const b = document.createElement("button");
    b.type = "button";
    b.className = "lihat-sandi";
    b.innerHTML = MATA;
    const set = (tampil) => {
      inp.type = tampil ? "text" : "password";
      b.innerHTML = tampil ? MATA_CORET : MATA;
      b.setAttribute("aria-pressed", String(tampil));
      const t = tampil ? "Sembunyikan kata sandi" : "Tampilkan kata sandi";
      b.setAttribute("aria-label", t);
      b.title = t;
    };
    b.addEventListener("click", () => set(inp.type === "password"));
    set(false);
    w.appendChild(b);
  });
  const hd = document.querySelector("header");
  if (hd) {
    const f = () => hd.classList.toggle("scrolled", window.scrollY > 8);
    window.addEventListener("scroll", f, { passive: true });
    f();
  }
  // elemen di bawah layar baru muncul (fade + naik) saat tergulir; elemen yang sudah terlihat tidak disentuh, dan tanpa JS semua tetap tampak
  if (
    "IntersectionObserver" in window &&
    !(
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches
    )
  ) {
    const io = new IntersectionObserver(
      (es) =>
        es.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add("tampak");
            io.unobserve(e.target);
          }
        }),
      { threshold: 0.12 },
    );
    document
      .querySelectorAll(".grid>.card:nth-child(n+9),.ulasan>blockquote")
      .forEach((el) => {
        if (el.getBoundingClientRect().top > window.innerHeight) {
          el.classList.add("reveal");
          io.observe(el);
        }
      });
  }
})();
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

// hitung mundur batas pelunasan
// Hitung mundur batas pelunasan (.hitung[data-sisa] dalam detik); reload saat habis agar status ter-update
document.querySelectorAll(".hitung").forEach((el) => {
  let s = +el.dataset.sisa;
  // hitung mundur 24 jam tampil sebagai jam:menit:detik
  const tampil = () => {
    const j = Math.floor(s / 3600),
      m = Math.floor((s % 3600) / 60);
    el.textContent =
      (j ? j + ":" : "") +
      String(m).padStart(2, "0") +
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

// popup verifikasi pembayaran (Terima/Tolak + konfirmasi tambahan) dan popup konfirmasi umum
(() => {
  const dlg = document.getElementById("dlg-verif");
  if (dlg) {
    const q = (s) => dlg.querySelector(s),
      panel = {
        aksi: q("#v-aksi"),
        terima: q("#v-form-terima"),
        tolak: q("#v-form-tolak"),
      };
    const tampil = (k) => {
      Object.entries(panel).forEach(([n, el]) => {
        el.hidden = n !== k;
      });
      if (k === "tolak") q("#v-alasan").focus();
    };
    document.querySelectorAll("[data-verif]").forEach((b) =>
      b.addEventListener("click", () => {
        const d = b.dataset;
        q("#v-info").textContent =
          d.pelanggan + " - " + d.unit + " (" + d.tgl + ") - Total " + d.total;
        const w = q("#v-bukti");
        w.innerHTML = "";
        const el = document.createElement(d.pdf === "1" ? "iframe" : "img");
        el.src = d.bukti;
        el.title = "Bukti pembayaran";
        if (d.pdf !== "1") el.alt = "Bukti pembayaran";
        w.appendChild(el);
        dlg.querySelectorAll(".v-id").forEach((i) => {
          i.value = d.id;
        });
        q("#v-alasan").value = "";
        tampil("aksi");
        dlg.showModal();
      }),
    );
    q("#v-terima").onclick = () => tampil("terima");
    q("#v-tolak").onclick = () => tampil("tolak");
    dlg.querySelectorAll("[data-kembali]").forEach((b) => {
      b.onclick = () => tampil("aksi");
    });
    dlg.querySelectorAll("[data-tutup]").forEach((b) => {
      b.onclick = () => dlg.close();
    });
  }
  // popup konfirmasi kedua di tengah layar untuk FORM dan TAUTAN: data-popup="pesan" [data-ya="label tombol"]
  const popup = (pesan, ya, bahaya, onYa) => {
    let p = document.getElementById("dlg-popup");
    if (!p) {
      p = document.createElement("dialog");
      p.id = "dlg-popup";
      p.setAttribute("aria-labelledby", "pp-judul");
      p.innerHTML =
        '<div class="dlg"><h3 id="pp-judul">Konfirmasi</h3><p id="pp-pesan"></p><div class="dlg-aksi"><button type="button" class="btn" id="pp-ya"></button><button type="button" class="btn alt" id="pp-batal">Batal</button></div></div>';
      document.body.appendChild(p);
    }
    p.querySelector("#pp-pesan").textContent = pesan;
    const y = p.querySelector("#pp-ya");
    y.textContent = ya || "Ya, lanjutkan";
    y.className = "btn" + (bahaya ? " bad" : "");
    y.onclick = () => {
      p.close();
      onYa();
    };
    p.querySelector("#pp-batal").onclick = () => p.close();
    p.showModal();
  };
  document.querySelectorAll("form[data-popup]").forEach((f) =>
    f.addEventListener("submit", (e) => {
      e.preventDefault();
      popup(f.dataset.popup, f.dataset.ya, !!f.querySelector(".btn.bad"), () =>
        HTMLFormElement.prototype.submit.call(f),
      );
    }),
  );
  document.querySelectorAll("a[data-popup]").forEach((a) =>
    a.addEventListener("click", (e) => {
      e.preventDefault();
      popup(a.dataset.popup, a.dataset.ya, false, () => {
        location.href = a.href;
      });
    }),
  );
  // kolom masalah maintenance hanya muncul (dan wajib) saat status diubah ke Maintenance
  const st = document.getElementById("st"),
    wm = document.getElementById("wrap-masalah");
  if (st && wm) {
    const t = () => {
      const perlu =
        st.value === "Maintenance" && st.dataset.awal !== "Maintenance";
      wm.hidden = !perlu;
      wm.querySelector("textarea").required = perlu;
    };
    st.addEventListener("change", t);
    t();
  }
})();

// tombol lihat/sembunyikan kata sandi, bayangan header saat scroll, animasi muncul halus saat di-scroll
(() => {
  const SVG = (d) =>
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    d +
    "</svg>";
  const MATA = SVG(
    '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
  );
  const MATA_CORET = SVG(
    '<path d="M17.9 17.9A10.9 10.9 0 0 1 12 19C5.5 19 2 12 2 12a18.4 18.4 0 0 1 5.1-5.9M9.9 4.2A10.4 10.4 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.2 3.2M1 1l22 22"/><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/>',
  );
  document.querySelectorAll('input[type="password"]').forEach((inp) => {
    const w = document.createElement("span");
    w.className = "sandi-wrap";
    inp.parentNode.insertBefore(w, inp);
    w.appendChild(inp);
    const b = document.createElement("button");
    b.type = "button";
    b.className = "lihat-sandi";
    b.innerHTML = MATA;
    const set = (tampil) => {
      inp.type = tampil ? "text" : "password";
      b.innerHTML = tampil ? MATA_CORET : MATA;
      b.setAttribute("aria-pressed", String(tampil));
      const t = tampil ? "Sembunyikan kata sandi" : "Tampilkan kata sandi";
      b.setAttribute("aria-label", t);
      b.title = t;
    };
    b.addEventListener("click", () => set(inp.type === "password"));
    set(false);
    w.appendChild(b);
  });
  const hd = document.querySelector("header");
  if (hd) {
    const f = () => hd.classList.toggle("scrolled", window.scrollY > 8);
    window.addEventListener("scroll", f, { passive: true });
    f();
  }
  // elemen di bawah layar baru muncul (fade + naik) saat tergulir; elemen yang sudah terlihat tidak disentuh, dan tanpa JS semua tetap tampak
  if (
    "IntersectionObserver" in window &&
    !(
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches
    )
  ) {
    const io = new IntersectionObserver(
      (es) =>
        es.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add("tampak");
            io.unobserve(e.target);
          }
        }),
      { threshold: 0.12 },
    );
    document
      .querySelectorAll(".grid>.card:nth-child(n+9),.ulasan>blockquote")
      .forEach((el) => {
        if (el.getBoundingClientRect().top > window.innerHeight) {
          el.classList.add("reveal");
          io.observe(el);
        }
      });
  }
  // burger menu + label kolom tabel untuk tampilan HP
document.querySelector(".burger")?.addEventListener("click", (e) => {
  const o = document.querySelector("header").classList.toggle("open");
  e.currentTarget.setAttribute("aria-expanded", o);
});
document.querySelectorAll(".tbl").forEach((t) => {
  const h = [...t.querySelectorAll("th")].map((x) => x.textContent.trim());
  t.querySelectorAll("tr").forEach((r) => {
    if (r.cells.length === h.length)
      [...r.cells].forEach((c, i) => c.tagName === "TD" && (c.dataset.label = h[i]));
  });
});

})();

