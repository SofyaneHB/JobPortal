<?php

// Get unread count for badge
$unread_notif_count = 0;

if (isset($pdo) && isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $unread_notif_count = (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        $unread_notif_count = 0;
    }
}

?>


<!-- Top Navbar for Candidate -->
<nav class="ml-64 border-b border-slate-900 bg-slate-950/40 backdrop-blur-md sticky top-0 z-30">
    <div class="flex items-center justify-between px-8 py-3">
        <div class="text-xs text-slate-500 font-medium">
            <?= date('l, d F Y') ?>
        </div>
        
        <div class="flex items-center gap-4">

            <!-- Notification Bell -->
            <a href="dashboard.php" class="relative p-2 text-slate-400 hover:text-slate-200 transition rounded-lg hover:bg-slate-900/40">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                </svg>

                <?php if ($unread_notif_count > 0): ?>

                <span class="absolute top-1 right-1 w-4 h-4 bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center rounded-full">
                    <?= $unread_notif_count > 9 ? '9+' : $unread_notif_count ?>
                </span>

                <?php endif; ?>
            </a>
            
            <!-- User Menu -->
            <div class="flex items-center gap-2 pl-4 border-l border-slate-900">

                <div class="w-7 h-7 bg-gradient-to-tr from-emerald-500 to-cyan-500 text-white flex items-center justify-center font-bold text-xs rounded-lg">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                </div>
                
                <span class="text-xs font-medium text-slate-300 hidden md:block"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
            </div>
        </div>
    </div>
</nav>