<?php
class CVParser {
    public static function extractText(string $filePath, string $mimeType): string {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === "pdf") return self::extractPdfText($filePath);
        if ($ext === "docx") return self::extractDocxText($filePath);
        if ($ext === "txt") return file_get_contents($filePath) ?: "";
        return "";
    }

    private static function extractPdfText(string $filePath): string {
        // Méthode 1: pdftotext avec layout
        $text = self::runPdftotext($filePath);
        if (strlen(trim($text)) > 300) return $text;

        // Méthode 2: pdf2txt.py (Python - lit TOUS les PDF)
        $text = self::runPdf2txt($filePath);
        if (strlen(trim($text)) > 300) return $text;

        // Méthode 3: Extraction brute des strings
        return self::extractStrings($filePath);
    }

    private static function runPdftotext(string $filePath): string {
        $output = []; $ret = 0;
        exec("/usr/bin/pdftotext -layout " . escapeshellarg($filePath) . " - 2>/dev/null", $output, $ret);
        if ($ret === 0) {
            $text = implode("\n", $output);
            // Vérifie que c'est pas du LaTeX
            if (strpos($text, 'pdfTeX') === false && strpos($text, 'LaTeX') === false) {
                return $text;
            }
        }
        return "";
    }

    private static function runPdf2txt(string $filePath): string {
        $output = []; $ret = 0;
        exec("python3 -m pdfminer.high_level.extract_text " . escapeshellarg($filePath) . " 2>/dev/null", $output, $ret);
        if ($ret === 0) {
            return implode("\n", $output);
        }
        // Essaie avec pdf2txt.py
        exec("pdf2txt.py " . escapeshellarg($filePath) . " 2>/dev/null", $output, $ret);
        if ($ret === 0) {
            return implode("\n", $output);
        }
        return "";
    }

    private static function extractStrings(string $filePath): string {
        $content = file_get_contents($filePath);
        if (!$content) return "";

        // Supprime les streams binaires
        $clean = preg_replace('/stream\s+.*?\s+endstream/s', ' ', $content);

        // Extrait les textes entre parenthèses (PDF operators)
        preg_match_all('/\(([^)]{3,})\)/s', $clean, $matches);

        $texts = [];
        foreach ($matches[1] as $match) {
            // Ignore les codes internes
            if (preg_match('/^[0-9\s\.\-]+$/', $match)) continue;
            if (strpos($match, 'pdfTeX') !== false) continue;
            if (strpos($match, 'D:20') === 0) continue;
            if (strlen($match) < 3) continue;

            // Décode les échappements PDF
            $match = str_replace(
                ['\\n', '\\r', '\\t', '\\\\', '\\(', '\\)', '\\#', '\\$', '\\%', '\\&'],
                ["\n", "\r", "\t", '\\', '(', ')', '#', '$', '%', '&'],
                $match
            );
            $texts[] = $match;
        }

        return implode(' ', $texts);
    }

    private static function extractDocxText(string $filePath): string {
        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            $xml = $zip->getFromName("word/document.xml");
            $zip->close();
            if ($xml) {
                $clean = str_replace(['</w:p>', '</w:r>', '<w:tab/>'], ' ', $xml);
                return strip_tags($clean);
            }
        }
        return "";
    }

    public static function validateFile(array $file): array {
        $errors = [];
        $allowed = ["pdf", "docx", "txt"];
        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) $errors[] = "Format non supporté.";
        if ($file["size"] > 5 * 1024 * 1024) $errors[] = "Fichier trop volumineux.";
        return $errors;
    }
}