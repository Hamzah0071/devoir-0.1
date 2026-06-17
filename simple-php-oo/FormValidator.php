<?php
// ══════════════════════════════════════════════════════════════════
//  CLASSE PARENT : Validator (base de validation générique)
// ══════════════════════════════════════════════════════════════════

abstract class Validator
{
    protected array $errors  = [];
    protected array $data    = [];

    // Règle générique : champ obligatoire
    protected function required(string $champ, string $label): void
    {
        if (empty($this->data[$champ])) {
            $this->errors[] = "$label est obligatoire.";
        }
    }

    // Règle générique : longueur maximale
    protected function maxLength(string $champ, int $max, string $label): void
    {
        if (isset($this->data[$champ]) && strlen($this->data[$champ]) > $max) {
            $this->errors[] = "$label ne doit pas dépasser $max caractères.";
        }
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): string
    {
        return $this->errors[0] ?? '';
    }

    // Méthode abstraite : chaque enfant définit SES propres règles
    abstract public function validate(): bool;
}


// ══════════════════════════════════════════════════════════════════
//  CLASSE ENFANT : CvFormValidator (règles spécifiques au CV)
// ══════════════════════════════════════════════════════════════════

class CvFormValidator extends Validator
{
    public function __construct(array $postData)
    {
        // Nettoyer toutes les données dès la construction
        foreach ($postData as $key => $value) {
            $this->data[$key] = trim($value);
        }
    }

    // Implémentation des règles propres au formulaire CV
    public function validate(): bool
    {
        $this->required('prenom', 'Le prénom');
        $this->required('nom',    'Le nom');
        $this->required('titre',  'Le titre / poste visé');

        $this->maxLength('prenom',      50,  'Le prénom');
        $this->maxLength('nom',         50,  'Le nom');
        $this->maxLength('titre',       100, 'Le titre');
        $this->maxLength('contact',     150, 'Le contact');
        $this->maxLength('experience',  2000,'L\'expérience');
        $this->maxLength('competences', 1000,'Les compétences');
        $this->maxLength('loisirs',     500, 'Les loisirs');

        return !$this->hasErrors();
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function get(string $champ): string
    {
        return $this->data[$champ] ?? '';
    }
}
