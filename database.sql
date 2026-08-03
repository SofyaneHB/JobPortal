-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 01, 2026 at 04:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `JobPortal`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `cv_path` varchar(500) DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `candidate_id`, `status`, `cv_path`, `applied_at`) VALUES
(1, 8, 6, 'pending', NULL, '2026-06-25 09:01:47'),
(2, 9, 6, 'accepted', NULL, '2026-06-25 19:59:24'),
(3, 9, 37, 'pending', NULL, '2026-07-15 18:29:04'),
(4, 9, 38, 'pending', NULL, '2026-07-15 18:29:09'),
(5, 9, 50, 'pending', NULL, '2026-07-15 18:31:15'),
(6, 9, 51, 'pending', NULL, '2026-07-15 18:31:19'),
(7, 9, 63, 'pending', NULL, '2026-07-15 18:32:50'),
(8, 9, 64, 'pending', NULL, '2026-07-15 18:32:54'),
(10, 9, 132, 'pending', NULL, '2026-07-24 13:48:37'),
(11, 6, 6, 'pending', NULL, '2026-07-24 14:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `logo`, `description`, `user_id`, `created_at`) VALUES
(2, 'JobTech', 'https://img.magnific.com/free-vector/abstract-company-logo_53876-120501.jpg?t=st=1785580529~exp=1785584129~hmac=1c1f0a89401e99873c9ef6c779100a66e41a076f2cea52652668b999ae3398f0&amp;w=2000', '', 7, '2026-06-24 20:29:10'),
(4, 'Company Playwright Company', NULL, NULL, 88, '2026-07-15 19:24:26'),
(5, 'Company Playwright Company', NULL, NULL, 125, '2026-07-21 10:04:12'),
(6, 'Company Playwright Company', NULL, NULL, 131, '2026-07-24 00:29:44');

-- --------------------------------------------------------

--
-- Table structure for table `cv_analyses`
--

CREATE TABLE `cv_analyses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cv_path` varchar(255) NOT NULL,
  `extracted_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extracted_skills`)),
  `raw_text` text DEFAULT NULL,
  `recommendations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recommendations`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(100) NOT NULL,
  `type` enum('full-time','part-time','remote','internship') DEFAULT 'full-time',
  `salary` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `experience_level` enum('Junior','Mid-Level','Senior') DEFAULT NULL,
  `required_language` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `experience` varchar(255) DEFAULT NULL,
  `tech_stack` varchar(255) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `Tasks` text DEFAULT NULL,
  `status` enum('active','paused','closed') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `company_id`, `title`, `description`, `location`, `type`, `salary`, `created_at`, `category`, `experience_level`, `required_language`, `skills`, `company_logo`, `education`, `experience`, `tech_stack`, `requirements`, `Tasks`, `status`) VALUES
(1, 2, 'NextJs Dev', 'nous besoin d;un dev', 'Agadir', 'full-time', '9000', '2026-06-25 06:08:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active'),
(3, 2, 'Laravel Developer', 'Nous sommes en train d&#039;ouvrir une posiotion ...', 'Ouled Teima', 'remote', '8000-10000', '2026-06-25 07:20:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active'),
(4, 2, 'TailwindCSS', 'CDI', 'Ouled Teima', 'internship', '20000-299999', '2026-06-25 07:31:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active'),
(5, 2, 'TypeScript Dev', 'Valide', 'Taroudant', 'full-time', '9000-10000', '2026-06-25 07:43:24', NULL, NULL, NULL, NULL, NULL, 'Bac+5', '+2 ans experience', NULL, NULL, NULL, 'active'),
(6, 2, 'VueJS', 'postule', 'Agadir', 'full-time', '20000-299999', '2026-06-25 08:16:54', NULL, NULL, NULL, NULL, NULL, 'Bac+5', '+3 ans experience', 'PHP, JS, ANGULAR', 'Agile Github', NULL, 'active'),
(8, 2, 'DJANGO', 'POSTULE', 'Taroudant', 'full-time', '9300', '2026-06-25 08:37:32', NULL, NULL, NULL, NULL, NULL, 'Bac+5', '+2 ans experience', 'PHP, JS, ANGULAR', 'Agile Github', 'TESTE LE CODE', 'closed'),
(9, 2, 'Developpeur PHP', 'PHP', 'Ouled Teima', 'full-time', '8000-10000', '2026-06-25 18:46:57', NULL, NULL, NULL, NULL, NULL, 'Bac+5', '+1 ans experience', 'PHP, Laravel, HTML, CSS, Wordpress', 'Agile Github', 'analyse de besoin', 'closed');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'status_change',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `link`, `is_read`, `created_at`) VALUES
(1, 6, 'Le statut de votre candidature pour « DJANGO » a été mis à jour.', 'status_change', '../candidate/my_applications.php', 1, '2026-06-25 11:39:41'),
(2, 6, 'Félicitations ! Votre candidature pour « Developpeur PHP » a été acceptée.', 'status_change', '../candidate/my_applications.php', 1, '2026-06-25 20:00:32'),
(3, 7, 'Nouvelle candidature reçue de « Candidate apply-endpoint » pour le poste « Developpeur PHP ».', 'new_application', 'applicants.php', 0, '2026-07-15 18:29:04'),
(4, 7, 'Nouvelle candidature reçue de « Candidate apply-endpoint » pour le poste « Developpeur PHP ».', 'new_application', 'applicants.php', 0, '2026-07-15 18:31:15'),
(5, 7, 'Nouvelle candidature reçue de « Candidate apply-endpoint » pour le poste « Developpeur PHP ».', 'new_application', 'applicants.php', 1, '2026-07-15 18:32:50'),
(6, 7, 'Nouvelle candidature reçue de « sofyane_HB2004_ » pour le poste « NestJS ».', 'new_application', 'applicants.php', 0, '2026-07-24 14:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('candidate','company','admin') DEFAULT 'candidate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `google_id`, `phone`, `address`, `country`, `skills`, `created_at`, `role`) VALUES
(1, 'sofyane_HB', 'admin@jobportal.com', '$2y$10$LsTlm5P3OZolnDjMROP60O1RiGXyCaogBctBwPzHiFT.af.5.SpcO', NULL, '+212 643-592670', 'Ouled Teima. Hay Kamal Eddine II', 'Morocco', 'php,js.ts,tsx', '2026-06-24 17:32:04', 'admin'),
(6, 'sofyane_HB2004_', 'sofyane2004@gmail.com', '$2y$10$iBAgYwjgivDr45ipTj1tmukTpX..2l8jfiz8t5H4fLZRA4crNnQRm', NULL, '0643592670', 'Ouled Teima. Hay Kamal Eddine III', 'Morocco', 'PHP,JS', '2026-06-24 20:28:33', 'candidate'),
(7, 'sofyane_HB2026', 'sofyane2026@gmail.com', '$2y$10$0K1iuLWyaJkU6E8X5icyreivkIkgNtG92poG25.kyhN30CxBqyiwG', NULL, NULL, NULL, NULL, NULL, '2026-06-24 20:28:59', 'company'),
(19, 'sofyaneHR', 'sofyanehbhb24@gmail.com', '$2y$10$s8uSr3KMro5LenRcqtkpseA5PRv958IvXLusXwC0srFlj5.wweMha', NULL, NULL, NULL, NULL, NULL, '2026-06-26 18:18:47', 'candidate'),
(21, 'SofyaneHB', 'sofianhabbouch625@gmail.com', '$2y$10$gSwk4Ov2VylOpRC2yb3CcufqXgkuyQE4/GdyEYubj90QRcDDc0jLC', NULL, NULL, NULL, NULL, NULL, '2026-06-26 18:29:50', 'candidate'),
(23, 'Test User Playwright', 'test_1784129809196@mail.com', '$2y$10$8odHrzd75ZBf0vYTrPq3JeHayGvwAXdsJzvKwfl1X/ly7XPtiZfV6', NULL, NULL, NULL, NULL, NULL, '2026-07-15 15:36:53', 'candidate'),
(24, 'Candidat Test', 'cand_1784130788002@test.com', '$2y$10$T/YRrC8mbONX1lgqAHuMDe1RT4.3wEsCkBDTLZbFFPkqJ0ig8UOIG', NULL, NULL, NULL, NULL, NULL, '2026-07-15 15:53:09', 'candidate'),
(25, 'Company Test', 'comp_1784130788000@test.com', '$2y$10$IgG6iXY1pVdi9vP7I1Y7yuDWczBfMMIrbAh6J3fPRohFX1P0Ehh76', NULL, NULL, NULL, NULL, NULL, '2026-07-15 15:53:09', 'candidate'),
(26, 'Company Test', 'comp_1784132289743@test.com', '$2y$10$ogVox7VEydxEuaRaVdXq5eiCLQ6G3plkk.y4QM9GxFnmpZHRA6Qfu', NULL, NULL, NULL, NULL, NULL, '2026-07-15 16:18:12', 'candidate'),
(27, 'Candidat Test', 'cand_1784132290455@test.com', '$2y$10$KI3pktvs60UzeGgRV6VpguXDASmQZfkZb89483FDqJt8fdBhTcnmi', NULL, NULL, NULL, NULL, NULL, '2026-07-15 16:18:13', 'candidate'),
(28, 'Cand Test', 'cand_1784133231221@test.com', '$2y$10$/RfkpOH0Zyf.y0MT21iyse7T7YQfz/hAVVo5sSln6NWOm51O/xAFi', NULL, NULL, NULL, NULL, NULL, '2026-07-15 16:33:53', 'candidate'),
(29, 'Cand Test', 'cand_1784133685169@test.com', '$2y$10$gSkT9WMXIiJHCotIPl7vFeT7O3ibdZ0VSd8tyYEdHCPInTIeBnWZu', NULL, NULL, NULL, NULL, NULL, '2026-07-15 16:41:29', 'candidate'),
(30, 'Candidate dashboard', 'cand_dashboard_1784140099960_412603@test.local', '$2y$10$zz4T6B.o6KARsA8IQFCj/OjsfYMyG9PTozDpgb9MBlQxDzhonNZIG', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:28:21', 'candidate'),
(31, 'Candidate applications', 'cand_applications_1784140107449_623508@test.local', '$2y$10$IwM2zDPGFT6kW4B75XRct.xmgwAsHTVXj4a..9SZk.sEy/z9VdMEK', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:28:28', 'candidate'),
(32, 'Candidate profile', 'cand_profile_1784140114641_192235@test.local', '$2y$10$VfGY72NkjWbEZmYM4fwvAODgXQ516XFTOeMP1we755pcC5SuC1q9q', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:28:35', 'candidate'),
(33, 'Candidate Updated', 'cand_update-profile_1784140127376_284707@test.local', '$2y$10$H8XDjeIWZ27wKpiGTeuEx.nQ5YxUttS56XzMOcJGloeqPkeznGlV6', NULL, '0612345678', 'Casablanca, Maroc', 'Maroc', 'PHP, JavaScript, Playwright', '2026-07-15 18:28:48', 'candidate'),
(34, 'Candidate jobs-list', 'cand_jobs-list_1784140131647_380185@test.local', '$2y$10$wwdCYS2NNdwc6z/buwlb4Oj2jOiTV03S2q7PvDDfpgSD7bfGQMR9m', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:28:52', 'candidate'),
(35, 'Candidate jobs-search', 'cand_jobs-search_1784140134896_943325@test.local', '$2y$10$xIRHIfzHeXs8Q7bJKiIn7OPLc36LnvMfIqRd6G/kYFoC02YtFnOjO', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:28:55', 'candidate'),
(36, 'Candidate job-details', 'cand_job-details_1784140138035_535490@test.local', '$2y$10$5dtBqQaEWPAD2MIMP50kKOT6SKIGtiuVkaNBFjogD69FPWHWIBEtS', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:28:59', 'candidate'),
(37, 'Candidate apply-endpoint', 'cand_apply-endpoint_1784140142296_735384@test.local', '$2y$10$BKaZonGNSbcqP3PnHSQf3upQqk8/YO4PoCFTNu0r/HHWccBsZaO2.', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:29:03', 'candidate'),
(38, 'Candidate apply-form', 'cand_apply-form_1784140145988_771366@test.local', '$2y$10$ZsH8esYOj1.muDfXrJnHHeyEgrPXTYIB/wclVWfP7YT.kRPN9WJ9u', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:29:06', 'candidate'),
(39, 'Candidate analyze-form', 'cand_analyze-form_1784140150538_169888@test.local', '$2y$10$iRoTo.ghySsq8Aayrkg.3.7KDOyrdMOZct718ihxwbYT7J.VGcdny', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:29:11', 'candidate'),
(40, 'Candidate ajax-unread', 'cand_ajax-unread_1784140155870_763772@test.local', '$2y$10$CycoGST.DW8waJL9Q9UYne.zV0eEjGBwBK.o7Cq9CrvVSTb69WuZi', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:29:16', 'candidate'),
(41, 'Candidate ajax-notifications', 'cand_ajax-notifications_1784140158329_813295@test.local', '$2y$10$Txadeldbo7wMA70Vd15opu/V2X3.ei3Xc4G7z/SdI4kwyTx0rTSBS', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:29:19', 'candidate'),
(42, 'Candidate ajax-mark-read', 'cand_ajax-mark-read_1784140160601_767595@test.local', '$2y$10$OL3FvMpzvyAPcLs6Wqk0l.0VLdakIGzs/mnxNsqFLPUVDqUISKwGK', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:29:21', 'candidate'),
(43, 'Candidate dashboard', 'cand_dashboard_1784140249563_65888@test.local', '$2y$10$hSUEzrIKgkBMnl5Rv9PWI.wcgylStTdrDQh0TI5EV6xaiGqZJvIYW', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:30:50', 'candidate'),
(44, 'Candidate applications', 'cand_applications_1784140253371_361192@test.local', '$2y$10$H/l5oI5EGtwxZojHscyAGu9uURQyXHtPTejo4QSc02dJYUnNxZ7ta', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:30:54', 'candidate'),
(45, 'Candidate profile', 'cand_profile_1784140256730_221119@test.local', '$2y$10$kgixLwd5bHPWhFRHMtqL9.8bt2PnZMjO3TO4CopB7GdC2byzUbBEy', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:30:57', 'candidate'),
(46, 'Candidate Updated', 'cand_update-profile_1784140259721_406428@test.local', '$2y$10$zIx6AFeI3zvn4AwEO0soJ.lC7zKqgr8lCUaUlLSsG2r9Sm/I27N1C', NULL, '0612345678', 'Casablanca, Maroc', 'Maroc', 'PHP, JavaScript, Playwright', '2026-07-15 18:31:00', 'candidate'),
(47, 'Candidate jobs-list', 'cand_jobs-list_1784140263676_400912@test.local', '$2y$10$Ty9PxoxqDBzDq1LtPJ.LUup1Wnx0D.5zOgB3YjxhR/OqLwfFXWH.C', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:04', 'candidate'),
(48, 'Candidate jobs-search', 'cand_jobs-search_1784140266966_617660@test.local', '$2y$10$MKI5To7DIiip/O3nzXifR.nZOeudN.Xy9YA9vz8b7rj8gmuI5S5z6', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:07', 'candidate'),
(49, 'Candidate job-details', 'cand_job-details_1784140269662_869774@test.local', '$2y$10$dN9ANibwxTd/EueHi4t/Me5QcEk.Gl.NHeuy0qCPH21MRb494uLuq', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:10', 'candidate'),
(50, 'Candidate apply-endpoint', 'cand_apply-endpoint_1784140272940_512506@test.local', '$2y$10$Ih/X.qDgjjHhnHYkofoLueVMMu02QjnjUjv2gzQrRIuV9hA3ZCRX2', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:13', 'candidate'),
(51, 'Candidate apply-form', 'cand_apply-form_1784140276196_93646@test.local', '$2y$10$9j8bgypPGqF8hOrogXW37.hmXNE2gV.u3/sUYFCj.w/CECY8THUrC', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:17', 'candidate'),
(52, 'Candidate analyze-form', 'cand_analyze-form_1784140280026_962240@test.local', '$2y$10$SbtfxrzMCzdQephbPc1FU.Caz4aI0q0GZmHPrUrq29BEzCChSFTcK', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:21', 'candidate'),
(53, 'Candidate ajax-unread', 'cand_ajax-unread_1784140284805_738394@test.local', '$2y$10$VcDw8ah9W.X2.9n8RSbiRO97Tdew20s/oB.ZwQi0tNcm2HGjgYV/W', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:25', 'candidate'),
(54, 'Candidate ajax-notifications', 'cand_ajax-notifications_1784140286935_628497@test.local', '$2y$10$0PSpNB9hqtVF/YUFauDI6OcNZBT6Q8EVa4P8rAED8f6FNFZXi2okK', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:27', 'candidate'),
(55, 'Candidate ajax-mark-read', 'cand_ajax-mark-read_1784140288568_285572@test.local', '$2y$10$B71rmASlBVtrxDb9MzC8ROq23BfS3I8Wvi1GC.28RhLg8sGr.rBAa', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:29', 'candidate'),
(56, 'Candidate dashboard', 'cand_dashboard_1784140347930_187102@test.local', '$2y$10$QHW3hwr9PbH6hO9aaUAI/.S2fJrQFks46MHAfZYV5BY7PIuspyqke', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:28', 'candidate'),
(57, 'Candidate applications', 'cand_applications_1784140351193_580437@test.local', '$2y$10$czH538ylR8pZOiEkDx/HB.cA5lpuFz05eP2uFNoBUcMvA2ZnVs.b.', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:31', 'candidate'),
(58, 'Candidate profile', 'cand_profile_1784140353856_313576@test.local', '$2y$10$vtWXYOESB3j4Sf9CLWkzoeu7mLdeeM0.XOh4/0ZKHwhCWuhXYBD4m', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:34', 'candidate'),
(59, 'Candidate Updated', 'cand_update-profile_1784140356524_540034@test.local', '$2y$10$ruXyvcO.zzkh9D1jeOVPgum6qU6DPVCo/GGjLUD3z6Gsw7mcBRZem', NULL, '0612345678', 'Casablanca, Maroc', 'Maroc', 'PHP, JavaScript, Playwright', '2026-07-15 18:32:37', 'candidate'),
(60, 'Candidate jobs-list', 'cand_jobs-list_1784140360181_66322@test.local', '$2y$10$R58FWmYR1./FWbUYfKXnF.3bGeKbERB8AdcnBAYEUHSqFb6COULaW', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:40', 'candidate'),
(61, 'Candidate jobs-search', 'cand_jobs-search_1784140362638_241256@test.local', '$2y$10$/Z4wjaQzOMYX6ZPqv57i6ehUaBVQGp8zXYYs03sk3JU5FajEUqmDq', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:43', 'candidate'),
(62, 'Candidate job-details', 'cand_job-details_1784140365264_892486@test.local', '$2y$10$jIASW14p7Xot31wIBPyeeOBPWqdFL5tgy4xNGBUz46C3Jit9AzZMq', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:46', 'candidate'),
(63, 'Candidate apply-endpoint', 'cand_apply-endpoint_1784140368563_394611@test.local', '$2y$10$qSQH6CdP1Rz58Weky6QMFO9OLpHjrd8wMO67LZxSGaOe2MvC9hRLG', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:49', 'candidate'),
(64, 'Candidate apply-form', 'cand_apply-form_1784140371714_897464@test.local', '$2y$10$vg3W45wK/GitlqXSzmXWsupT6i7dDQX3R4.vpPrbFd9GfaDdFhdBi', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:52', 'candidate'),
(65, 'Candidate analyze-form', 'cand_analyze-form_1784140375299_149229@test.local', '$2y$10$Qdq/i.ISDKL0UTcy0Sva3.7nvc4pg.76.9uPrqNTaLnQqyEkUbwsW', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:32:56', 'candidate'),
(66, 'Candidate ajax-unread', 'cand_ajax-unread_1784140380010_391826@test.local', '$2y$10$kl5EaNsACwUnh6.P3oj43Opn2aZIggL1SFG9qjDfF3hLxZEuPtCZm', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:33:00', 'candidate'),
(67, 'Candidate ajax-notifications', 'cand_ajax-notifications_1784140382179_665435@test.local', '$2y$10$AH8RUTwxzro3r90mPZ.MfOIAN0a/P8ih4Mp7YBbkFhtUphVe0NNJi', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:33:02', 'candidate'),
(68, 'Candidate ajax-mark-read', 'cand_ajax-mark-read_1784140383890_664963@test.local', '$2y$10$XG3mFO4NU7ca0E6nnPtMjesnDYgP7aTm1tP8Zor8Ap../A6o/Ju8O', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:33:04', 'candidate'),
(69, 'Candidate dashboard', 'cand_dashboard_1784140711912_246058@test.local', '$2y$10$FgIb8Ez4AeXSggKu3PVKUOirpBYp2bn.Z5g/g1C3o.siuTyiSM7ta', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:38:33', 'candidate'),
(70, 'Candidate applications', 'cand_applications_1784140715243_870029@test.local', '$2y$10$38c.fsOf2BerCWh9CngLNepcR0XTnWduyJCtxJIsncH8r0k/jeuF6', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:38:36', 'candidate'),
(71, 'Candidate profile', 'cand_profile_1784140718168_822883@test.local', '$2y$10$adDSMRrZjn3nTzJvtZ9KJOrO7Ccr.NhVdZk6rwKH6AKJB.QyJyPUa', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:38:38', 'candidate'),
(72, 'Candidate Updated', 'cand_update-profile_1784140720799_938847@test.local', '$2y$10$MzWeBylJAOo.afSNuvcHZ.YZGZNPT1HoTChCun48fO3nugtQB9k.G', NULL, '0612345678', 'Casablanca, Maroc', 'Maroc', 'PHP, JavaScript, Playwright', '2026-07-15 18:38:41', 'candidate'),
(73, 'Candidate jobs-list', 'cand_jobs-list_1784140724139_259055@test.local', '$2y$10$n2RP/RYiUUy/n84rpQo9sew.xeQKKHu6wdFTnw7Mlyan2hKA551ji', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:38:44', 'candidate'),
(74, 'Candidate ajax-unread', 'cand_ajax-unread_1784140726541_541841@test.local', '$2y$10$dvPWo9GtfXGzBZmYkEviiepxpTDvK.gpfGA.B.DrhGFdPPfJsnRHm', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:38:47', 'candidate'),
(75, 'Candidate Dashboard', 'cand_dashboard_1784141094515@test.local', '$2y$10$8jWYbJ5/OzNPkvYGOWdGvePGbXo3XM8xIloSQelem7zUZUVYvuYNO', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:44:55', 'candidate'),
(76, 'Candidate Applications', 'cand_applications_1784141098666@test.local', '$2y$10$U38cOzlsdNIFozZMO1Apyu1sbzQ0TzRCu8UXxcb9/aVB49/4UvkkC', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:44:59', 'candidate'),
(77, 'Candidate Profile', 'cand_profile_1784141102465@test.local', '$2y$10$Bwovsc3y8j/3rym0eQHtQer5.koJlv9elFCiRkWCuxF84OEIC.o6O', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:45:03', 'candidate'),
(78, 'Candidate Updated', 'cand_update_1784141105965@test.local', '$2y$10$xvLnAMDpY72NZoxUu2sfcu2punRJYXC3SuHtTmVc9xODp6e/az9xG', NULL, '0612345678', 'Casablanca, Maroc', 'Maroc', 'PHP, JavaScript, Playwright', '2026-07-15 18:45:07', 'candidate'),
(79, 'Candidate Jobs', 'cand_jobs_1784141111065@test.local', '$2y$10$wwxPBOCfWOvxHy3m1d1O7OAIm9btv9g.ntEH0NkAs7Ia96xnFImQq', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:45:12', 'candidate'),
(80, 'Candidate API', 'cand_api_1784141114906@test.local', '$2y$10$nY/maheu2c/W/kpq1fwppOrXBvRDCd4mMKGyJ4wGrnuaYM3EJXPtu', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:45:15', 'candidate'),
(81, 'Candidate Dashboard', 'cand_dashboard_1784141204436@test.local', '$2y$10$PqW3okNsb1zj3wmLWVJxW.ZfjAUeT31uHKArtphrqor/IbsG025my', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:46:45', 'candidate'),
(82, 'Candidate Applications', 'cand_applications_1784141208353@test.local', '$2y$10$nQHeo0EsucOiSNQs1HEaFObAeYCnlMM35PlNdHD275OTBC8KF9e1e', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:46:49', 'candidate'),
(83, 'Candidate Profile', 'cand_profile_1784141212228@test.local', '$2y$10$zmFZmkkQ9svTEF1cuD9hHeyc2bnqqVxBt2Xi3zg20CoLb6l4nUnB6', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:46:53', 'candidate'),
(84, 'Candidate Updated', 'cand_update_1784141216067@test.local', '$2y$10$KrW1fsFO6zqCPlPO.02yLuUOBzcy2OrOT4pZ3NEvYd1SUIkdaSvoW', NULL, '0612345678', 'Casablanca, Maroc', 'Maroc', 'PHP, JavaScript, Playwright', '2026-07-15 18:46:57', 'candidate'),
(85, 'Candidate Jobs', 'cand_jobs_1784141220281@test.local', '$2y$10$jvcbY2EHhzJM.frNjYq4RemHggcyaMqkVizgVHCkX0cRx2waxoxRW', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:47:01', 'candidate'),
(86, 'Candidate API', 'cand_api_1784141223793@test.local', '$2y$10$OKEVpYZ1deOOxyy3BVglQuInumnTLP/W7CFLA7w3I6CIJyHXZ7y46', NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:47:04', 'candidate'),
(87, 'Company Playwright', 'company_1784142431690@test.com', '$2y$10$oHYOe7KvH0ViXZyGL8eDmOSNmaMYPTh1ZkZPImss/ANAbVJhS5TFi', NULL, NULL, NULL, NULL, NULL, '2026-07-15 19:07:12', 'candidate'),
(88, 'Company Playwright', 'company_1784143463822@test.com', '$2y$10$qA9CxssNq8qDF0LUk.AvluNrlXZeWUGoki6IYz.loPoo.LkFXEO.q', NULL, NULL, NULL, NULL, NULL, '2026-07-15 19:24:25', 'company'),
(93, 'Candidat Test', 'cand_1784145364195@test.com', '$2y$10$qjh2mPNOtL6M/w6uHwBtcu9Vm.SuV5WqUF6lt2NZC4MudGaCEcnQW', NULL, NULL, NULL, NULL, NULL, '2026-07-15 19:56:06', 'candidate'),
(94, 'Company Test', 'comp_1784145368002@test.com', '$2y$10$KSDSiwUVp5yMWR9yojaKAOwi3dWzgewrTnbQc2.WtD06WWKWyGWdG', NULL, NULL, NULL, NULL, NULL, '2026-07-15 19:56:09', 'company'),
(95, 'Candidat Test', 'cand_1784145430260@test.com', '$2y$10$zCygmYJbDKuAyCMMAXV9CexgUXoU6D4lf69Bks6JRTr564bke22V2', NULL, NULL, NULL, NULL, NULL, '2026-07-15 19:57:11', 'candidate'),
(96, 'Company Test', 'comp_1784145432587@test.com', '$2y$10$Et5mJFTR.spMJlh6nVohv.bsVLEhjCu9r.LCPX/XsNmPEjAzoiR.K', NULL, NULL, NULL, NULL, NULL, '2026-07-15 19:57:13', 'company'),
(97, 'Candidat Test', 'cand_1784145503981@test.com', '$2y$10$o.1/ntMna3zB4cAaNtyb9O8cJumT3iAIPly3BlmFjMw3rXHLqs5PC', NULL, NULL, NULL, NULL, NULL, '2026-07-15 19:58:25', 'candidate'),
(98, 'Company Test', 'comp_1784145505894@test.com', '$2y$10$/8IwjSbIw7bFUyschGl8VeABHtfGWP6InGpdmwNsXaMxc4vMbHXTK', NULL, NULL, NULL, NULL, NULL, '2026-07-15 19:58:26', 'company'),
(101, 'Test User Playwright', 'test_1784146767994_5646@mail.com', '$2y$10$zSxk.rW.iBRoKGojXPkMCO7VYJYjtKjtyz5QQtFyawV.luVJGsdX2', NULL, NULL, NULL, NULL, NULL, '2026-07-15 20:19:29', 'candidate'),
(102, 'Wrong Password User', 'test_1784146774515_6077@mail.com', '$2y$10$tBv.eaygwg2xlCo2uTv/5.uk4XwY3VRc780GuUyFe22JFwLUazLOO', NULL, NULL, NULL, NULL, NULL, '2026-07-15 20:19:35', 'candidate'),
(103, 'Test User', 'test_1784146867011@mail.com', '$2y$10$BKBg77nmKb8e47yzZW4J.eXsdp2eLP8sQi7FZJ8nXk3R7HcP8lQWy', NULL, NULL, NULL, NULL, NULL, '2026-07-15 20:21:08', 'candidate'),
(104, 'Wrong Password User', 'test_1784146871532@mail.com', '$2y$10$ru0hFv7iWs/Uax9tHTag0.v7iQtpMGJfzDfb0/QiKTuAwGy3IhgMG', NULL, NULL, NULL, NULL, NULL, '2026-07-15 20:21:12', 'candidate'),
(105, 'Test User Playwright', 'playwright_test@mail.com', '$2y$10$Dk2L7VgDyepoZsFZ7FZrrOWtP.6G7nNwj6/508/.ikbJEUtMYfWN6', NULL, NULL, NULL, NULL, NULL, '2026-07-15 20:24:23', 'candidate'),
(106, 'Test User', 'test_1784147394536@mail.com', '$2y$10$VxNqvYCiCD5RuilCbLGr6ezJvow0LiygmOYEckJQ.367KEjoPwY7S', NULL, NULL, NULL, NULL, NULL, '2026-07-15 20:29:55', 'candidate'),
(107, 'Wrong Password User', 'test_1784147399293@mail.com', '$2y$10$79Inrbxjvk9PSDFwuNwlLefh0rt2qpRd1tXtNI7TFWwCzKR5OZoDC', NULL, NULL, NULL, NULL, NULL, '2026-07-15 20:30:00', 'candidate'),
(113, 'Candidat Test', 'cand_1784627973990@test.com', '$2y$10$uGMTBtpHoCD9W1FXHmBbUelBfwVR3U8WeARlYNSmz/XaKhG/e9hoa', NULL, NULL, NULL, NULL, NULL, '2026-07-21 09:59:35', 'candidate'),
(114, 'Company Test', 'comp_1784627975776@test.com', '$2y$10$NE1j/dLdayF57hCh4OKbJuzQTGBY/nJ8AQykR7RgfXddvKYjn3HDu', NULL, NULL, NULL, NULL, NULL, '2026-07-21 09:59:36', 'company'),
(115, 'Candidat Test', 'cand_1784627996458@test.com', '$2y$10$96jIVbiASXNEph7tx/Ros.dDbGxBxCLQbw6hW5CNyWF2oDYTkXZhq', NULL, NULL, NULL, NULL, NULL, '2026-07-21 09:59:57', 'candidate'),
(116, 'Company Test', 'comp_1784627998161@test.com', '$2y$10$U5y07Yzgp8mq8wGg2ZmjCOLrF3sty0W1jnH2cYuTfkGLisrgeUHXW', NULL, NULL, NULL, NULL, NULL, '2026-07-21 09:59:59', 'company'),
(117, 'Test User', 'test_1784628108665@mail.com', '$2y$10$YcvA17mVXKHJoZlD17nyFu7lo9mjrGiTGYEknZQB2LW.3bhiXgW7a', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:01:49', 'candidate'),
(118, 'Wrong Password User', 'test_1784628113319@mail.com', '$2y$10$c.iRqbFrGJH1TOqLFti.5.wS/pXYn6mpxzqOIl1SuecnM4eIC2f/K', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:01:54', 'candidate'),
(119, 'Candidate Dashboard', 'cand_dashboard_1784628200180@test.local', '$2y$10$0Wu32hu3tuY11Lby/Qp9gubP3SsUfNIi0dyIRfjUnZbVjaCPVTYv6', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:03:21', 'candidate'),
(120, 'Candidate Applications', 'cand_applications_1784628203496@test.local', '$2y$10$MOnztCwshfaD2FA4uPn7uu7QYB1UIyMOr8r/Df/gkv6RWoKqjJdx2', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:03:24', 'candidate'),
(121, 'Candidate Profile', 'cand_profile_1784628206697@test.local', '$2y$10$814DA6z7xJlHRmJVt.bZ8.ebu/DyP80XHFZBrtuUYuEEEmzmavLjm', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:03:27', 'candidate'),
(122, 'Candidate Updated', 'cand_update_1784628209432@test.local', '$2y$10$Sta3UhtfFa1FY/.PK6dbmuPGg9agU06NG07rVREP9btyTT7vUl8/m', NULL, '0612345678', 'Casablanca, Maroc', 'Maroc', 'PHP, JavaScript, Playwright', '2026-07-21 10:03:30', 'candidate'),
(123, 'Candidate Jobs', 'cand_jobs_1784628212652@test.local', '$2y$10$xp9D45SdFzFdiqbW6B/.zOakm5Yrg.l3CgGnVASG9.6igbMn2Ra9G', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:03:33', 'candidate'),
(124, 'Candidate API', 'cand_api_1784628215289@test.local', '$2y$10$YyACXCbszv9VrH6ejjMtdOvCY.0hilxVC3Pl0xumVcgleDbHo6hr.', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:03:36', 'candidate'),
(125, 'Company Playwright', 'company_1784628250652@test.com', '$2y$10$9q9DDqA.FzUtSm4Hn39B5ec2E3Ef2mCueYfPNkuu3QdUswgkBj.O2', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:04:11', 'company'),
(127, 'Candidat Test', 'cand_1784628441664@test.com', '$2y$10$MP4ZC9OmN1QaoQGGUbZEDeulUt61H2gXlpNG0ZKcigzVTGRL9iLwm', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:07:22', 'candidate'),
(128, 'Company Test', 'comp_1784628443414@test.com', '$2y$10$3acK3Al7g3R8oEPUlCSmc.0hTBd6Wkk0LI8d4X4k/lKqZgyaMjwLG', NULL, NULL, NULL, NULL, NULL, '2026-07-21 10:07:24', 'company'),
(129, 'Candidat Test', 'cand_1784851527630@test.com', '$2y$10$ksf2NqAPXdYq/PpKIMI92.MDQgMGE8dfu0VpeLkGHFVy92PpUjoZO', NULL, NULL, NULL, NULL, NULL, '2026-07-24 00:05:29', 'candidate'),
(130, 'Company Test', 'comp_1784851529817@test.com', '$2y$10$NxD14GIdG29CSh9HZDbQIur7qjnrkdGq4M0eUby6VaM7bRlMuZNh2', NULL, NULL, NULL, NULL, NULL, '2026-07-24 00:05:30', 'company'),
(131, 'Company Playwright', 'company_1784852983029@test.com', '$2y$10$Z5XFHeoISMSBrFEX5yE3duBKsqz3.DKr0qlIuWrbGPwmU0dlTbUya', NULL, NULL, NULL, NULL, NULL, '2026-07-24 00:29:44', 'company'),
(132, 'SofyaneHB', 'sofyanehab1@gmail.com', '$2y$10$Al8rYbq/14hGGWmBrdfcxezxkSOmlS2iBGwaq0be6vAJPOddwHekS', NULL, NULL, NULL, NULL, NULL, '2026-07-24 13:48:10', 'candidate');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_app_job` (`job_id`),
  ADD KEY `fk_app_user` (`candidate_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `cv_analyses`
--
ALTER TABLE `cv_analyses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jobs_company` (`company_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cv_analyses`
--
ALTER TABLE `cv_analyses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_user` FOREIGN KEY (`candidate_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `fk_company_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cv_analyses`
--
ALTER TABLE `cv_analyses`
  ADD CONSTRAINT `cv_analyses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_jobs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
