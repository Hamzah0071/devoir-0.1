<?php
// ══════════════════════════════════════════════════════════════════
//  CLASSE PARENT : FileUploader (gestion générique d'upload)
// ══════════════════════════════════════════════════════════════════

abstract class FileUploader
{
    protected ?string $error    = null;
    protected int     $maxSize  = 5 * 1024 * 1024; // 5 Mo par défaut
    protected array   $allowed  = [];

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    // Vérifie si un fichier a bien été envoyé sans erreur
    protected function fileReceived(array $file): bool
    {
        return isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
    }

    // Vérifie la taille du fichier
    protected function checkSize(array $file): bool
    {
        if ($file['size'] > $this->maxSize) {
            $mo = $this->maxSize / (1024 * 1024);
            $this->error = "Le fichier ne doit pas dépasser {$mo} Mo.";
            return false;
        }
        return true;
    }

    // Vérifie le type MIME réel (pas l'extension déclarée)
    protected function checkMime(array $file): bool
    {
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowed, true)) {
            $list        = implode(', ', $this->allowed);
            $this->error = "Type de fichier non autorisé. Acceptés : $list";
            return false;
        }
        return true;
    }

    // Méthode abstraite : chaque enfant décide quoi faire du fichier
    abstract public function process(array $file): bool;
}


// ══════════════════════════════════════════════════════════════════
//  CLASSE ENFANT : PhotoUploader (photo CV en base64)
// ══════════════════════════════════════════════════════════════════

class PhotoUploader extends FileUploader
{
    private ?string $base64 = null;

    public function __construct()
    {
        $this->maxSize = 5 * 1024 * 1024;
        $this->allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    }

    public function process(array $file): bool
    {
        // Pas de photo envoyée = pas une erreur, juste optionnel
        if (!$this->fileReceived($file)) {
            return true;
        }

        if (!$this->checkSize($file) || !$this->checkMime($file)) {
            return false;
        }

        // Encoder en base64 pour intégration directe dans le HTML
        $finfo         = new finfo(FILEINFO_MIME_TYPE);
        $mimeType      = $finfo->file($file['tmp_name']);
        $imageData     = file_get_contents($file['tmp_name']);
        $this->base64  = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

        return true;
    }

    public function getBase64(): ?string
    {
        return $this->base64;
    }

    public function hasPhoto(): bool
    {
        return $this->base64 !== null;
    }
}
