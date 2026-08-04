<!DOCTYPE html>
<html class="scroll-smooth" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JobPortal connects candidates with top companies. Search openings, apply in a few clicks, and track every application in one place.">
    <title>JobPortal </title>
    <link rel="icon" type="image/png" href="../assets/img/logo_jp.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../output.css" rel="stylesheet">
</head>

<body class="font-['Inter',sans-serif] text-gray-900">

    <header class="p-8">

        <a href="#home" class="flex items-center">
            <h1 class="text-2xl font-bold tracking-tight">
                <span class="text-gray-900">Job</span><span class="text-purple-600">Portal</span>
            </h1>
        </a>
    </header>

<main style="padding-top: 6rem;">

<section id="home" class="relative bg-white flex flex-col items-center justify-center text-center px-4 overflow-hidden pt-28 md:pt-32 mb-24 md:mb-32">


    <div class="relative max-w-5xl">

        <h1 class="font-['Sora'] text-4xl md:text-6xl font-extrabold text-gray-900 tracking-tight leading-tight">
            Search, Apply &
            <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-indigo-600">
                Build Your Future
            </span>
        </h1>

        <p class="mt-6 text-base md:text-lg text-gray-500 max-w-2xl mx-auto leading-relaxed">
            Find the right opportunities or the right talent. Start your recruitment journey with a secure and intelligent platform
        </p>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto text-left mt-10">

            <!-- Candidate -->
            <div class="bg-purple-50 rounded-3xl p-8 shadow-sm hover:shadow-lg transition-all duration-300">

                <div class="w-14 h-14 flex items-center justify-center rounded-2xl bg-purple-100 mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-purple-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-900">
                    I am looking for a job
                </h2>

                <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                    Create your candidate account, Complete your profile,
                    Browse job opportunities, Apply easily and 
                    Track your applications

                </p>

                <a href="../jobs/index.php?role=candidate"
                   class="inline-flex mt-6 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-3 rounded-full w-fit">
                    Find Jobs 
                </a>

            </div>

            <!-- Recruiter -->
            <div class="bg-indigo-50 rounded-3xl p-8 shadow-sm hover:shadow-lg transition-all duration-300">

                <div class="w-14 h-14 flex items-center justify-center rounded-2xl bg-indigo-100 mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-indigo-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-900">
                    I want to hire
                </h2>

                <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                    Register as a recruiter, Create your company profile,
                    Post job offers, Review applications and then
                    Find the right candidates.                </p>

                <a href="Register.php?role=company"
                   class="inline-flex mt-6 bg-white hover:bg-gray-50 text-gray-800 font-semibold px-8 py-3 rounded-full shadow-sm transition border border-gray-100 w-fit">
                    Hire Talent 
                </a>

            </div>

        </div>

    </div>
</section>

    <section id="about" class="py-20 px-4 bg-white border-t border-gray-100 mb-24 md:mb-32">
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <div class="space-y-5">
                <h2 class="font-['Sora'] text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-5">
                    Connecting Talent with <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-indigo-600">Top-Tier Companies</span>
                </h2>
                <p class="text-gray-500 leading-relaxed text-sm md:text-base max-w-lg">
                    JobPortal simplifies the recruitment process by connecting candidates with companies in one secure platform. Candidates can search for job opportunities, create their profile once, apply easily, and track their applications. Recruiters can publish job offers, manage applications, and find qualified candidates efficiently
                </p>
            </div>

            <div class="p-8 bg-slate-50 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-center aspect-[1.25/1]">
                <img src="../assets/img/man-search-hiring-job-online-from-laptop.png" alt="Illustration of a recruiter reviewing candidate profiles on a laptop" class="max-h-full max-w-full h-auto w-auto object-contain">
            </div>

        </div>
    </section>




    <section id="how-it-works" class="py-20 px-4 bg-white border-t border-gray-100 mb-24 md:mb-32">
        <div class="max-w-2xl mx-auto text-center mb-14">
            <h2 class="font-['Sora'] mt-2 text-3xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight">
                Get Hired in <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-indigo-600">4 Simple Steps</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto mt-10">

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-purple-50 text-orange-600 flex items-center justify-center rounded-xl mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Create an Account</h3>
                <p class="text-sm text-gray-500">Sign up as a candidate or company and create your profile to access recruitment features</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 flex items-center justify-center rounded-xl mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Find Opportunities</h3>
                <p class="text-sm text-gray-500">Candidates can browse job offers, while companies can discover qualified profiles</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-purple-50 text-green-600 flex items-center justify-center rounded-xl mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l2 2h5a2 2 0 012 2v7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Apply &amp; Manage</h3>
                <p class="text-sm text-gray-500">Candidates can upload their CV, get AI-powered analysis, and apply for jobs, while companies can publish and manage their offers</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-purple-50 text-amber-500 flex items-center justify-center rounded-xl mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Connect &amp; Recruit</h3>
                <p class="text-sm text-gray-500"> Track applications, review candidate profiles, and manage the recruitment process easily</p>
            </div>
        </div>
    </section>


<footer id="contact" class="bg-[#0e0b12] text-gray-400 py-16 px-6">
  <div class="max-w-6xl mx-auto space-y-12">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 text-sm">

      <div class="space-y-4">
        <a href="#home" class="inline-flex items-center gap-2 group">
          <span class="text-white font-black text-xl">Job<span class="text-purple-400">Portal</span></span>
        </a>
        <p class="leading-relaxed max-w-sm text-gray-500">
          Connecting candidates with companies through a secure and intelligent recruitment platform
        </p>

        <div class="flex gap-2 pt-2">
          <a href="https://www.linkedin.com/in/sofyane-habbouch-14b2893b1/" target="_blank" aria-label="LinkedIn" class="w-8 h-8 flex items-center justify-center bg-white/5 text-purple-300 rounded hover:bg-white/10 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
          </a>
          <a href="mailto:sofyane.habbouch.92@edu.uiz.ac.ma" target="_blank" aria-label="Email" class="w-8 h-8 flex items-center justify-center bg-white/5 text-purple-300 rounded hover:bg-white/10 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </a>
          <a href="https://github.com/SofyaneHB" aria-label="GitHub" target="_blank" rel="noopener noreferrer" class="w-8 h-8 flex items-center justify-center bg-white/5 text-purple-300 rounded hover:bg-white/10 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2A10 10 0 0 0 2 12c0 4.42 2.87 8.17 6.84 9.5.5.08.66-.23.66-.5v-1.69c-2.77.6-3.36-1.34-3.36-1.34-.46-1.16-1.11-1.47-1.11-1.47-.9-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.9 1.52 2.34 1.07 2.91.83.1-.65.35-1.09.63-1.35-2.22-.25-4.55-1.11-4.55-4.92 0-1.11.38-2 1.03-2.71-.1-.25-.45-1.29.1-2.64 0 0 .84-.27 2.75 1.02.79-.22 1.65-.33 2.5-.33.85 0 1.71.11 2.5.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.35.2 2.39.1 2.64.65.71 1.03 1.6 1.03 2.71 0 3.82-2.34 4.66-4.57 4.91.36.31.69.92.69 1.85V21c0 .27.16.59.67.5C19.14 20.16 22 16.42 22 12A10 10 0 0 0 12 2z"/></svg>
          </a>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-6">
        <nav class="flex flex-col gap-3" aria-label="Candidates links">
          <h4 class="text-white font-bold tracking-wide">Candidates</h4>
          <a href="../jobs/index.php" class="hover:text-white transition-colors">Find Jobs</a>
          <a href="../candidate/applications.php" class="hover:text-white transition-colors">My Applications</a>
          <a href="../jobs/index.php" class="hover:text-white transition-colors">Upload CV</a>
          <a href="../jobs/index.php" class="hover:text-white transition-colors">AI CV Analysis</a>
        </nav>

        <nav class="flex flex-col gap-3" aria-label="Companies links">
          <h4 class="text-white font-bold tracking-wide">Companies</h4>
          <a href="../company/dashboard.php" class="hover:text-white transition-colors">Dashboard</a>
          <a href="../company/dashboard.php" class="hover:text-white transition-colors">Publish Jobs</a>
          <a href="../company/dashboard.php" class="hover:text-white transition-colors">Manage Jobs</a>
          <a href="../company/dashboard.php" class="hover:text-white transition-colors">Candidates</a>
        </nav>
      </div>

    </div>

    <div class="pt-8 border-t border-gray-900 text-center text-xs text-gray-600">
      <p>&copy; 2026 JobPortal - Developed by <a class="text-indigo-400 hover:underline cursor-pointer">Sofyane_HB</a></p>
    </div>

  </div>
</footer>

</main>

</body>
</html>