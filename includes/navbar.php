

<nav class="bg-white shadow-md">

    <div class="container mx-auto px-4 py-3 flex justify-between items-center">

        <!-- Logo -->
        <a href="/job-portal/public/index.php" class="text-blue-600 font-bold text-xl">
            JobPortal
        </a>

        <div class="flex items-center gap-8">
            <a href="../Public/index.php" class="text-gray-600 hover:text-black font-medium transition-colors text-sm">
                Home
            </a>
            <a href="/job-portal/public/jobs.php" class="text-gray-600 hover:text-black font-medium transition-colors text-sm">
                Jobs
            </a>

            <?php if (is_logged_in()) { ?>
                <a href="../candidate/dashboard.php" class="text-gray-600 hover:text-black font-medium transition-colors text-sm">
                    Dashboard
                </a>
                <a href="../auth/logout.php" class="bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-2 rounded-xl text-sm font-semibold transition-all">
                    Logout
                </a>
            <?php } else { ?>
                <a href="/job-portal/public/login.php" class="text-gray-600 hover:text-black font-medium transition-colors text-sm">
                    Login
                </a>
                <a href="/job-portal/public/register.php" class="bg-black text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-neutral-800 transition-all">
                    Register
                </a>
            <?php } ?>
        </div>

    </div>
</nav>

</nav>