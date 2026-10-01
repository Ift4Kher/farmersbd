<?php
// ============================================================
// FarmersBD — Secure File Upload Service
// ============================================================

class UploadService {

    private array  $allowedTypes;
    private int    $maxSize;
    private string $uploadBase;

    public function __construct() {
        $this->allowedTypes = ALLOWED_IMAGE_TYPES;
        $this->maxSize      = UPLOAD_MAX_SIZE;
        $this->uploadBase   = UPLOAD_BASE_DIR;
    }

    /**
     * Handle a file upload.
     *
     * @param  array  $file      $_FILES['field']
     * @param  string $subdir    Subdirectory under /uploads/ e.g. 'products'
     * @param  int    $maxSize   Override max size in bytes (0 = use default)
     * @return array  ['success' => bool, 'filename' => string|null, 'error' => string|null]
     */
    public function upload(array $file, string $subdir, int $maxSize = 0): array {
        $maxSize = $maxSize > 0 ? $maxSize : $this->maxSize;

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'filename' => null, 'error' => $this->upload_error_msg($file['error'])];
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'filename' => null, 
                    'error' => 'ফাইলের আকার ' . format_bytes($maxSize) . '-এর বেশি হতে পারবে না।'];
        }

        // Validate using getimagesize (checks actual content, not just extension)
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'filename' => null, 'error' => 'অবৈধ ইমেজ ফাইল।'];
        }

        // Validate MIME type
        $mime = $imageInfo['mime'] ?? '';
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes, true)) {
            return ['success' => false, 'filename' => null, 
                    'error' => 'শুধুমাত্র JPG, PNG, WEBP ফরম্যাট অনুমোদিত।'];
        }

        // Validate extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedTypes, true)) {
            return ['success' => false, 'filename' => null, 
                    'error' => 'অনুমোদিত ফরম্যাট: ' . implode(', ', $this->allowedTypes)];
        }

        // Validate image dimensions
        [$width, $height] = $imageInfo;
        if ($width > MAX_IMAGE_WIDTH || $height > MAX_IMAGE_HEIGHT) {
            return ['success' => false, 'filename' => null,
                    'error' => "ছবির আকার সর্বোচ্চ " . MAX_IMAGE_WIDTH . "×" . MAX_IMAGE_HEIGHT . " পিক্সেল হতে পারবে।"];
        }

        // Generate a safe random filename
        $safeExt  = $this->mime_to_ext($mime);
        $filename = bin2hex(random_bytes(16)) . '.' . $safeExt;

        $destDir = rtrim($this->uploadBase, '/') . '/' . trim($subdir, '/') . '/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $destPath = $destDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            error_log("[UploadService] move_uploaded_file failed: {$destPath}");
            return ['success' => false, 'filename' => null, 'error' => 'ফাইল সংরক্ষণ ব্যর্থ হয়েছে।'];
        }

        return ['success' => true, 'filename' => $filename, 'error' => null];
    }

    /**
     * Delete an uploaded file safely.
     */
    public function delete(string $subdir, ?string $filename): void {
        if (empty($filename)) return;
        $path = rtrim($this->uploadBase, '/') . '/' . trim($subdir, '/') . '/' . basename($filename);
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Alias for upload()
     */
    public function upload_image(array $file, string $subdir, int $maxSize = 0): array {
        return $this->upload($file, $subdir, $maxSize);
    }

    /**
     * Alias for delete() supporting both ($filename, $subdir) and ($subdir, $filename) signatures.
     */
    public function delete_image($arg1, $arg2 = null): void {
        if (empty($arg1) && empty($arg2)) return;
        if (is_string($arg1) && is_string($arg2)) {
            if (in_array($arg1, ['products', 'blogs', 'blog', 'diseases', 'fish', 'banners', 'categories'], true)) {
                $this->delete($arg1, $arg2);
            } else {
                $this->delete($arg2, $arg1);
            }
        } elseif (is_string($arg1)) {
            $this->delete('', $arg1);
        }
    }

    private function mime_to_ext(string $mime): string {
        switch ($mime) {
            case 'image/jpeg': return 'jpg';
            case 'image/png':  return 'png';
            case 'image/webp': return 'webp';
            case 'image/gif':  return 'gif';
            default:           return 'jpg';
        }
    }

    private function upload_error_msg(int $code): string {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'ফাইলের আকার অনুমোদিত সীমা অতিক্রম করেছে।';
            case UPLOAD_ERR_PARTIAL:
                return 'ফাইল আংশিকভাবে আপলোড হয়েছে।';
            case UPLOAD_ERR_NO_FILE:
                return 'কোনো ফাইল নির্বাচন করা হয়নি।';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'টেম্পোরারি ফোল্ডার পাওয়া যায়নি।';
            case UPLOAD_ERR_CANT_WRITE:
                return 'ফাইল লেখা যাচ্ছে না।';
            default:
                return 'অজানা আপলোড ত্রুটি।';
        }
    }
}
