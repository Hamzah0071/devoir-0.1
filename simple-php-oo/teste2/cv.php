<?php
// On simule une classe parente vide pour que votre "extends" fonctionne
class HtmlRenderer {
    // Logique de base générique (optionnelle)
}

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

    // Méthode pour générer le rendu HTML du CV
    public function genererHtml(): string
    {
        $html = "<div style='font-family: sans-serif; max-width: 700px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>";
        
        // Affichage de la photo si elle existe
        if ($this->photoBase64) {
            $html .= "<img src='data:image/jpeg;base64,{$this->photoBase64}' style='width:120px; height:120px; float:right; border-radius:50%; object-fit:cover;'>";
        }

        $html .= "<h1>" . htmlspecialchars($this->prenom . " " . $this->nom) . "</h1>";
        $html .= "<h2 style='color: #4A90E2;'>" . htmlspecialchars($this->titre) . "</h2>";
        $html .= "<p><strong>Contact :</strong> " . nl2br(htmlspecialchars($this->contact)) . "</p>";
        $html .= "<hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>";

        $html .= "<h3>Expérience</h3><p>" . nl2br(htmlspecialchars($this->experience)) . "</p>";
        $html .= "<h3>Compétences</h3><p>" . nl2br(htmlspecialchars($this->competences)) . "</p>";
        $html .= "<h3>Loisirs</h3><p>" . nl2br(htmlspecialchars($this->loisirs)) . "</p>";
        
        $html .= "</div>";
        return $html;
    }
}