<?php
require('fpdf/fpdf.php');

// Récupération des données depuis l'URL
$nom = $_GET['nom'] ?? '';
$prenom = $_GET['prenom'] ?? '';
$salaire_base = $_GET['salaire_base'] ?? 0;
$primes = $_GET['primes'] ?? 0;
$heures_supp = $_GET['heures_supp'] ?? 0;
$retenues = $_GET['retenues'] ?? 0;
$salaire_net = $_GET['salaire_net'] ?? 0;

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,utf8_decode("Fiche de Paie"),0,1,'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',10);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Corps du document
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,"Nom : $nom",0,1);
$pdf->Cell(0,10,"Prénom : $prenom",0,1);
$pdf->Ln(5);

// Tableau des détails du salaire
$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,10,'Détails',1);
$pdf->Cell(90,10,'Montants (DH)',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
$pdf->Cell(90,10,'Salaire de base',1);
$pdf->Cell(90,10,number_format($salaire_base,2),1);
$pdf->Ln();

$pdf->Cell(90,10,'Primes (performance, anciennete)',1);
$pdf->Cell(90,10,number_format($primes,2),1);
$pdf->Ln();

$pdf->Cell(90,10,'Heures supplémentaires',1);
$pdf->Cell(90,10,number_format($heures_supp,2),1);
$pdf->Ln();

$pdf->Cell(90,10,'Retenues (impôts, absences)',1);
$pdf->Cell(90,10,'-'.number_format($retenues,2),1);
$pdf->Ln();

$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,10,'Salaire Net à Payer',1);
$pdf->Cell(90,10,number_format($salaire_net,2),1);

$pdf->Output('I', "fiche_paie_{$nom}_{$prenom}.pdf");
exit;
