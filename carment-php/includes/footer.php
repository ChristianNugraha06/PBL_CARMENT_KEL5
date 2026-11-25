</main>
<?php /* footer dikembalikan ke versi sederhana, sebelum footer 4 kolom */ ?>
<footer>CARMENT - Kelompok 5 TI-2B, Politeknik Negeri Malang</footer>
<?php /* tombol "Hubungi Staff" langsung ke WhatsApp (untuk pengunjung & pelanggan) */ ?>
<?php
$uw = current_user();
if (!$uw || $uw['role'] === 'customer'):
?>
    <a class="wa-float" href="<?= e(wa_link('Halo admin CARMENT, ' . ($uw ? 'saya ' . $uw['nama'] . ', ' : '') . 'saya ingin bertanya.')) ?>" target="_blank" rel="noopener" aria-label="Hubungi staff lewat WhatsApp">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 20.5l1.7-5.4A8.5 8.5 0 1 1 21 11.5z" />
            <path d="M9 9.5c.3 2.6 2.4 4.7 5 5l1.2-1.2-2-1-.9.8a4 4 0 0 1-2.2-2.2l.8-.9-1-2L9 9.5z" fill="currentColor" stroke="none" />
        </svg>
        <span>Hubungi Staff</span>
    </a>
<?php endif; ?>
<?php ?>
<script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>