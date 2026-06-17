<?php
// ══════════════════════════════════════════════════════════════════
//  CLASSE PARENT : HtmlRenderer (rendu HTML générique)
// ══════════════════════════════════════════════════════════════════

abstract class HtmlRenderer
{
    protected array $vars = [];

    // Assigner une variable de template
    public function assign(string $key, mixed $value): void
    {
        $this->vars[$key] = $value;
    }

    // Sécuriser une valeur pour affichage HTML
    protected function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    // Convertir un texte multiligne en <li> HTML
    protected function lignesEnListe(string $texte, string $classeVide = 'cv-empty'): string
    {
        $lignes = array_filter(
            array_map('trim', explode("\n", $texte))
        );

        if (empty($lignes)) {
            return '<li class="' . $classeVide . '">—</li>';
        }

        $html = '';
        foreach ($lignes as $ligne) {
            $html .= '            <li>' . $this->e($ligne) . '</li>' . "\n";
        }

        return $html;
    }

    // Méthode abstraite : chaque enfant génère son propre HTML
    abstract public function render(): string;
}


// ══════════════════════════════════════════════════════════════════
//  CLASSE ENFANT : CvRenderer (génère le HTML du CV complet)
// ══════════════════════════════════════════════════════════════════

class CvRenderer extends HtmlRenderer
{
    private string  $prenom;
    private string  $nom;
    private string  $titre;
    private string  $contact;
    private string  $experience;
    private string  $competences;
    private string  $loisirs;
    private ?string $photoBase64;

    public function __construct(array $data, ?string $photoBase64 = null)
    {
        $this->prenom      = $data['prenom']      ?? '';
        $this->nom         = $data['nom']         ?? '';
        $this->titre       = $data['titre']       ?? '';
        $this->contact     = $data['contact']     ?? '';
        $this->experience  = $data['experience']  ?? '';
        $this->competences = $data['competences'] ?? '';
        $this->loisirs     = $data['loisirs']     ?? '';
        $this->photoBase64 = $photoBase64;
    }

    public function getFullName(): string
    {
        return strtoupper($this->prenom . ' ' . $this->nom);
    }

    // Génère le bloc photo ou le placeholder
    private function renderPhoto(): string
    {
        if ($this->photoBase64) {
            return sprintf(
                '<img src="%s" alt="Photo de %s" class="cv-photo">',
                $this->photoBase64,
                $this->e($this->prenom)
            );
        }

        return '
            <div class="cv-photo-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8 a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>';
    }

    // Génère le bloc contact si renseigné
    private function renderContact(): string
    {
        if (empty($this->contact)) return '';

        return sprintf('
            <p class="cv-contact">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2
                             19.79 19.79 0 0 1-8.63-3.07
                             A19.5 19.5 0 0 1 4.11 13
                             a19.79 19.79 0 0 1-3.07-8.67
                             A2 2 0 0 1 3 2.18h3
                             a2 2 0 0 1 2 1.72
                             c.127.96.361 1.903.7 2.81
                             a2 2 0 0 1-.45 2.11L7.09 9.91
                             a16 16 0 0 0 6 6l1.27-1.27
                             a2 2 0 0 1 2.11-.45
                             c.907.339 1.85.573 2.81.7
                             A2 2 0 0 1 21 16.92z"/>
                </svg>
                %s
            </p>', $this->e($this->contact));
    }

    // Méthode principale : génère tout le HTML du CV
    public function render(): string
    {
        return '
        <div class="cv-document">

            <!-- En-tête -->
            <div class="cv-header">
                <div class="cv-photo-wrap">
                    ' . $this->renderPhoto() . '
                </div>
                <div class="cv-identity">
                    <h1 class="cv-name">' . $this->e($this->getFullName()) . '</h1>
                    <p class="cv-titre">' . $this->e($this->titre) . '</p>
                    ' . $this->renderContact() . '
                </div>
            </div>

            <!-- Corps -->
            <div class="cv-body">

                <aside class="cv-aside">
                    <section class="cv-section">
                        <h2 class="cv-section-title">Compétences</h2>
                        <ul class="cv-list">
                            ' . $this->lignesEnListe($this->competences) . '
                        </ul>
                    </section>
                    <section class="cv-section">
                        <h2 class="cv-section-title">Loisirs</h2>
                        <ul class="cv-list">
                            ' . $this->lignesEnListe($this->loisirs) . '
                        </ul>
                    </section>
                </aside>

                <div class="cv-main-col">
                    <section class="cv-section">
                        <h2 class="cv-section-title">Expérience</h2>
                        <ul class="cv-list cv-list-experience">
                            ' . $this->lignesEnListe($this->experience) . '
                        </ul>
                    </section>
                </div>

            </div>
        </div>';
    }
}
