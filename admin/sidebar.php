<!-- ===================== SIDEBAR — FIXED ===================== -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="sidebar-brand-icon">
        <img src="gambar/logo-polije.png" width="44" height="44"/>
        <circle cx="17" cy="17" r="17"/>
        <!-- <svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="17" cy="17" r="17" fill="#1a6b3c"/>
          <path d="M17 7 C11 11, 9 19, 17 27 C25 19, 23 11, 17 7Z" fill="#4caf50"/>
          <path d="M17 27 C13 21, 9 17, 9 13 C11 17, 14 21, 17 27Z" fill="#81c784"/>
        </svg> -->
      </div>
      <span class="sidebar-brand-text">ADMIN</span>
    </div>

    <nav class="sidebar-nav">
      <a href="admin.php?menu=dashboard" class="sidebar-item active">
        <!-- <i class="bi bi-speedometer2"></i>  -->
        Dashboard
      </a>
      <div style="height:0.5rem;"></div>
      <a href="admin.php?menu=peminjaman" class="sidebar-subitem">
        <!-- <i class="bi bi-arrow-left-right"></i> -->
          <i class="bi bi-check-circle"></i>PEMINJAMAN
      </a>
      <a href="#" class="sidebar-subitem">
        <i class="bi bi-book"></i> DATA BUKU
      </a>
      <a href="#" class="sidebar-subitem">
        <i class="bi bi-file-earmark-text"></i> LAPORAN
      </a>
    </nav>
  </aside>