<?php
include("include/dbcommon.php");
$fecha1=$_GET['fecha1'];
$fecha2=$_GET['fecha2'];
$fecha=date('Y-m-d');
$path='/var/www/html/repojmas/libs/fpdf185/fpdf.php';
require_once($path);
class PDF extends FPDF
   {
   // Page header
   function Header() {
      $this->SetLeftMargin(3);   
      $fecha=utf8_decode(strftime("%d-%b-%Y", strtotime($_GET['fecha'])));   
      $inicio=strftime("%a %d-%b-%Y", strtotime($_GET['fecha1']));
      $termina=strftime("%a %d-%b-%Y", strtotime($_GET['fecha2']));      
      // Logo
      $this->SetFillColor(223,223,223);
      $this->Image('../rezagos/images/poliza1.png',10,10,40);
      $this->Image('../rezagos/images/poliza2.png',60,10,40);      
      $this->SetTextColor(0,0,0);
      $this->SetFont('Arial','B',10);

      $this->SetFont('Arial','',9);
	  $this->Cell(101,4,"",0,0,'C');      
	  $this->ln();
	  $this->Cell(101,4,"",0,0,'C');            
	  $this->Cell(140,4,$inicio.' al '.$termina,0,0,'C');
	  $this->ln();
	  $this->Cell(101,4,"",0,0,'C');            
      $this->SetFont('Arial','B',9);
	  $this->ln(12);	  
      $this->SetFont('Arial','B',11);
      $this->SetLeftMargin(20);      
	  $this->Cell(0,4,utf8_decode("Póliza para el pago limpieza de las cajas de los medidores"),0,0,'C');	  
      $this->SetFont('Arial','B',8);	  
	  $this->ln(8);
      $this->SetLeftMargin(55);	  
      //$this->SetFillColor(180, 196, 231);
	  $this->Cell(10,5,"Num.",1,0,'L',true);
      $this->Cell(25,5,'Fecha asignada',1,0,'C',true);
      $this->Cell(25,5,'Fecha limpieza',1,0,'C',true);
      $this->Cell(18,5,'Cantidad',1,0,'C',true);
      $this->ln();
   }

   function Footer(){
      $this->SetLeftMargin(0);
      $this->SetY(-5);      
      $tiempo=date('Y-m-d H:i:s');
      // Print centered page number
      $this->Cell(0,5,'Hoja: '.$this->PageNo().'/{nb}'.' '.$tiempo,0,0,'C');
   }
}
$pdf = new PDF('P','mm');
$pdf->AliasNbPages();
$pdf->SetFont('Arial','',6);
$pdf->AddPage();
$pdf->SetLeftMargin(55);
$pdf->SetFillColor(180, 196, 231);
$sql="
SELECT
	date_format(fecha,'%Y-%m-%d') as fecha,
	date_format(fecha_limpio,'%Y-%m-%d') as fecha_limpio,
	count(*) as cajas
FROM
	en_proceso
WHERE
    status='Limpio' and fecha_limpio between '{$fecha1}' and '{$fecha2}'";
GROUP BY
    date_format(fecha,'%Y-%m-%d'),date_format(fecha_limpio,'%Y-%m-%d')
ORDER BY
    fecha_limpio
";

$rs=DB::Exec($sql);
$n=1;
while ($row=$rs->fetchAssoc()){
echo $row['fecha']."***"";
exit();
   $pdf->Cell(10,3,$n,'LT',0,'R');
   $pdf->Cell(25,3,$row['fecha']);
   $n++;
}
$pdf->Output();
?>