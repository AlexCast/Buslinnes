<?php
require_once __DIR__ . '/src/base_de_datos.php';
require_once __DIR__ . '/vendor/fpdf/fpdf.php';

class PDF extends FPDF
{
    // Encabezado personalizado
    function Header()
    {
        // Usa el logo convertido a PNG
        $this->Image(__DIR__ . '/assets/img/logomorado.png', 10, 8, 20);
        // Título
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(128, 89, 212); // #362163ff
        $this->Cell(0, 10, 'Reporte Mensual - Buslinnes', 0, 1, 'C');
        // Línea decorativa
        $this->SetDrawColor(128, 89, 212);
        $this->Line(10, 35, 200, 35); // Línea más abajo (Y=35)
        $this->Ln(15); // Espacio extra debajo del encabezado
    }

    // Pie de página personalizado
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(128, 89, 212);
        $this->Cell(0, 10, 'Buslinnes © 2025 | Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

$mes = $_GET['mes'] ?? date('m');
$anio = $_GET['anio'] ?? date('Y');

// Consulta de datos
$stmt = $base_de_datos->prepare("SELECT COUNT(*) FROM tab_usuarios WHERE EXTRACT(MONTH FROM fec_insert) = ? AND EXTRACT(YEAR FROM fec_insert) = ? AND fec_delete IS NULL");
$stmt->execute([$mes, $anio]);
$usuarios = $stmt->fetchColumn();

$stmt = $base_de_datos->query("SELECT COUNT(*) FROM tab_buses WHERE fec_delete IS NULL");
$buses = $stmt->fetchColumn();

$stmt = $base_de_datos->query("SELECT COUNT(*) FROM tab_conductores WHERE fec_delete IS NULL");
$conductores = $stmt->fetchColumn();

$stmt = $base_de_datos->query("SELECT COUNT(*) FROM tab_pasajeros WHERE fec_delete IS NULL");
$pasajeros = $stmt->fetchColumn();

$stmt = $base_de_datos->query("SELECT COUNT(*) FROM tab_rutas WHERE fec_delete IS NULL");
$rutas = $stmt->fetchColumn();

// Crear PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(37, 33, 44); // #25212c

// Subtítulo
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(128, 89, 212);
$pdf->Cell(0, 10, "Datos del mes: $mes/$anio", 0, 1, 'L');
$pdf->Ln(5);

// Tabla de datos
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(37, 33, 44);
$pdf->SetFillColor(230, 230, 250); // Fondo suave
$pdf->SetDrawColor(128, 89, 212);

$pdf->Cell(70, 10, 'Indicador', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Cantidad', 1, 1, 'C', true);

$pdf->Cell(70, 10, 'Usuarios registrados este mes', 1, 0, 'L');
$pdf->Cell(40, 10, $usuarios, 1, 1, 'C');

$pdf->Cell(70, 10, 'Buses existentes', 1, 0, 'L');
$pdf->Cell(40, 10, $buses, 1, 1, 'C');

$pdf->Cell(70, 10, 'Conductores existentes', 1, 0, 'L');
$pdf->Cell(40, 10, $conductores, 1, 1, 'C');

$pdf->Cell(70, 10, 'Pasajeros existentes', 1, 0, 'L');
$pdf->Cell(40, 10, $pasajeros, 1, 1, 'C');

$pdf->Cell(70, 10, 'Rutas existentes', 1, 0, 'L');
$pdf->Cell(40, 10, $rutas, 1, 1, 'C');

$pdf->Ln(10);

// Mensaje final
$pdf->SetFont('Arial', 'I', 11);
$pdf->SetTextColor(128, 89, 212);
$pdf->Cell(0, 10, 'Reporte generado automáticamente por Buslinnes.', 0, 1, 'C');

$pdf->Output('D', "reporte_mensual_$mes-$anio.pdf");
