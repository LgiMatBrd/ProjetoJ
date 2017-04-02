<?php

/*
 * 
 */

define('ROOT_DIR', dirname(dirname(__FILE__)));

require '../lib/pdfgenerator/fpdf.php';
require '../config/psl-config.php';
require '../config/db_connect.php';

class PDF extends FPDF
{
    private $footerPos;             // Posição do rodapé em relação à borda inferior
    private $pageBorder;            // Posição da borda da página
    protected $recuoEsquerda;       // Recuo a partir da margem esquerda
    protected $recuoDireita;        // Recuo a partir da margem direita


    // Desenha a borda da página
    private function _pageBorder()
    {
        $this->SetLineWidth(0.177);
        $this->SetDrawColor(22, 53, 92);
        $this->Rect($this->pageBorder, $this->pageBorder, ($this->w - $this->pageBorder*2),
                ($this->h - $this->pageBorder*2));
                
    }
    // Posiciona a borda da página
    function SetPageBorderPos($width)
    {
        $this->pageBorder = $width;
    }
    // Posiciona o local do rodapé em relação à borda inferior
    function SetFooterTextPos($pos)
    {
        $this->footerPos = $pos;
    }
    // Configura o recuo a partir da margem esquerda
    function SetRecuoEsquerda($valor)
    {
        $this->recuoEsquerda = $valor + $this->GetLMargin();
    }
    // Configura o recuo a partir da margem direita
    function SetRecuoDireita($valor)
    {
        $this->recuoDireita = $valor + $this->GetRMargin();
    }
    // Retorna o valor configurado de margem esquerda
    function GetLMargin()
    {
        return $this->lMargin;
    }
    // Retorna o valor configurado de margem esquerda
    function GetRMargin()
    {
        return $this->rMargin;
    }
    // Calcula a altura das células baseado na entrelinhas
    function GetCellHeight($entrelinha)
    {
        //return round(($this->FontSize*($entrelinha + 0.18)), 0, PHP_ROUND_HALF_UP);
        return ($this->FontSize*($entrelinha+0.215));
    }
    // Calcula a largura das células baseado nos recuos das margens
    function GetCellWidth()
    {
        return $this->GetPageWidth() - $this->recuoDireita - $this->recuoEsquerda;
    }
    // MultiCell com posicionamento na horizontal com base no recuo
    function MultiCell($w, $h, $txt, $comRecuo = 1, $border = 0, $align = 'J', $fill = false)
    {
        if ($comRecuo === 1)
            $this->SetX($this->recuoEsquerda);
        parent::MultiCell($w, $h, utf8_decode($txt), $border, $align, $fill);
    }
    // Imprime uma célula de título com marcador numérico automático
    function PrintTitulo($str)
    {
        static $marcador_numerico = 1;
        $this->SetFont('','B');
        $this->SetX($this->recuoEsquerda);
        $this->Cell($this->GetCellWidth(),$this->GetCellHeight(3.5),$marcador_numerico++.'. '.utf8_decode($str),0,1);
        $this->SetFont('');
    }
    // Imprime uma célula com a largura exata do texto a ser mostrado
    function PrintTexto($h, $str, $style = '')
    {
        if ($this->x == $this->lMargin)
            $this->SetX($this->recuoEsquerda);
        $str = utf8_decode($str);
        $this->SetFont('', $style);
        $this->Cell($this->GetStringWidth($str),$h, $str);
    }
    // Load data
    function LoadData($file)
    {
        // Read file lines
        $lines = explode("\n", $file);
        $data = array();
        foreach($lines as $line)
            $data[] = explode(';.',trim($line));
        return $data;
    }
    // Decodifica todas as colunas da row
    function _row_decodeUTF8(&$row)
    {
        foreach ($row as $key => $str)
            $row[$key] = utf8_decode($str);
            //var_dump($str);
    }
    
    
    
    
    
    
    
    // Calcula a altura da célula para a linha a ser impressa na tabela
    function _countRowLinhas($row, &$qtdmulticells, $maxw)
    {
        $h = 0;
        foreach($row as $key => $col)
        {
            $qtdmulticells[$key] = 1;
            //$col = utf8_decode($col);
            $ws = $this->GetStringWidth($col);
            $w = $ws;
            while ($w > $maxw[$key])
            {
                $qtdmulticells[$key]++;
                $w -= $maxw[$key];
            }
            $qtdlinhas = $qtdmulticells[$key]+substr_count($col, "\n");
            if ($qtdlinhas > $h)
                $h = $qtdlinhas;
        }
        return $h;
    }
    
    
    
    
    
    
    
    
    // Imprime uma linha inteira da tabela, celula por celula
    function PrintRow($wcols, $row, $entrelinhas, $border=0, $align='J', $fill=false)
    {
        $this->_row_decodeUTF8($row);
        
        $qtdmulticells = array();
        $rowh = $this->_countRowLinhas($row, $qtdmulticells, $wcols);
        
        $h = $this->GetCellHeight($rowh*$entrelinhas);
        
        foreach ($row as $key => $txt)
        {
            $this->_printCell($wcols[$key], $h, $qtdmulticells[$key], $rowh, $entrelinhas, $txt, $border, $align, $fill);
            
        }
        $this->Ln($h);
    }
    
    function _printCell($w, $h, $cellh, $rowh, $entrelinhas, $txt, $border=0, $align='J', $fill=false)
    {
        $ax = $this->GetX(); // Coordenadas atuais
        $ay = $this->GetY();
        
        $this->Cell($w, $h, '', $border, 0, '', $fill);
        if ($cellh != $rowh)
            // Calcula a posição vertical do texto
            $ypos = $ay + $this->GetCellHeight( (($rowh - $cellh)/2)*$entrelinhas - 0.215);
        else
            $ypos = $ay;
        
        $this->SetXY($ax, $ypos);
        
        parent::MultiCell($w, $this->GetCellHeight($entrelinhas), $txt, '', $align);
        
        $this->SetXY($ax + $w, $ay);
    }
    // Simple table
    function BasicTable($header, $data)
    {
        $maxw = array(17,30,21,27,28,15,21,31);
        // Header
        
        $this->PrintRow($maxw, $header, 1.0, 1, 'C');
        
        // Data
        foreach($data as $row)
        {
            $this->PrintRow($maxw, $row, 1.0, 1, 'J');
        }
    }
    // Page header
    function Header()
    {
        /* Configuráveis para alterar o layout */
        $alturaCelulaLogo = 12; // Apenas valores pares!
        $metadeAltura = $alturaCelulaLogo/2;
        $lCelulaLogo = 24; // Largura da celula da logomarca
        $lCelulaSecundaria = 20; // Largura das celulas secundárias
        $this->SetLineWidth(0.265); // Espessura da linha
        $this->SetDrawColor(30, 72, 124); //Cor da linha
        $this->SetTextColor(30, 72, 124); //Cor do texto
        $font = 'Calibri';
        
        /* Não configuráveis */
        $lCelulaPrincipal = $this->w - $lCelulaLogo - 2*$lCelulaSecundaria - $this->lMargin - $this->rMargin;
        $this->Image('logo.jpg',12,12,$lCelulaLogo-4,$alturaCelulaLogo-4);
        $this->Cell($lCelulaLogo,$alturaCelulaLogo,'',1,0);
        $fimCelulaImagem = $this->GetX();
        $this->SetFont($font,'B',11);
        $this->Cell($lCelulaPrincipal,$metadeAltura, utf8_decode('RELATÓRIO DE INSPEÇÃO - DEPARTAMENTO DE ENGENHARIA'),1,0,'C');
        $this->Cell($lCelulaSecundaria,$metadeAltura, utf8_decode('Nº DOC:'),1,0,'L');
        $this->Cell($lCelulaSecundaria,$metadeAltura, utf8_decode('RI-0101'),1,1,'C');
        $this->SetX($fimCelulaImagem);
        $this->SetFont($font,'',11);
        $this->Cell($lCelulaPrincipal,$metadeAltura, utf8_decode('Acessórios para movimentação de cargas ( Below the Hook)'),1,0,'C');
        $this->SetFont($font,'B',11);
        $this->Cell($lCelulaSecundaria,$metadeAltura, utf8_decode('DATA:'),1,0,'L');
        $this->Cell($lCelulaSecundaria,$metadeAltura, date('d/m/y'),1,0,'C');
        
        // Line break
        //$this->Ln($this->GetCellHeight(1.5)+round($this->FontSize));
        $this->Ln($this->GetCellHeight(2.5));
        // Borda da página
        $this->_pageBorder();
    }
    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-$this->footerPos);
        // Arial italic 8
        $this->SetFont('Calibri','',11);
        // Cor do texto de rodapé
        $this->SetTextColor(83, 140, 211);
        // Page number
        $this->Cell(0,13, utf8_decode('Página ').$this->PageNo().' de {nb}',0,0,'C');
    }
}

/* Declara o objeto da classe PDF e realiza as configurações globais */
$pdf = new PDF();
$pdf->AddFont('Calibri');                   // Adiciona a fonte Calibri Regular
$pdf->AddFont('Calibri', 'B');              // Adiciona a fonte Calibri Negrito
$pdf->SetCompression(true);                 // Ativa a compressão do arquivo PDF
$pdf->AliasNbPages();                       // Ativa o apelido '{nb}' para n. de páginas 
$pdf->SetFooterTextPos(23);                 // Posiciona o rodapé a 2.3 cm da borda
$pdf->SetPageBorderPos(8);                  // Posiciona a margem a 8 mm da borda
$pdf->SetFont('Calibri','B',11);            // Ativa a fonte Calibri, negrito, 11pt
$pdf->SetRecuoEsquerda(13);                 // Configura o recuo a partir da esquerda
$pdf->SetRecuoDireita(13);                  // Configura o recuo a partir da direita
$entrelinhas = 1.5;                         // Entrelinhas de 1.5x


$lorem = file_get_contents('lorem.txt');
$txtObjetivo = <<< EOT
Apresentar um relatório sobre a inspeção realizada em lingas de corrente e cintas de elevação para a empresa XXXXXXXXXXXXXXXXXXX.
EOT;
$txtDesc = <<< EOT
A inspeção foi realizada no mês XXXXX de XXXXX, dentro das instalações fabris da empresa na unidade de XXXXXXX.

Os critérios utilizados para a inspeção das lingas foram a inspeção visual e a checagem dimensional do desgaste da corrente, utilizando-se gabaritos de inspeção, onde, através de um sistema "passa/não-passa"  é possível determinar se a corrente está ou não em condições de uso. Os gabaritos possuem arestas de inspeção cujas dimensões possuem a medida do item analisado mais a tolerância permitida para desgaste em correntes, de acordo com a norma (NBR ISO 3076:2005).

O gabarito foi utilizado seguindo o esquema descrito na Tabela 1 a seguir.
EOT;
$tabela = <<< EOT
299;.XXX;.Linga;.20;.6800;.1;.NÃO;.Identificação de carga ilegível, corrente muito.
300;.XXX;.Linga;.20;.4000;.2;.NÃO;.Alongamento no diâmetro nominal , sem identificação de.
312;.XXX;.Linga;.16;.3000;.2;.NÃO;.Identificação de carga ilegível, alongamentaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
EOT;


/* Gera o conteúdo das páginas */
$wCell = $pdf->GetCellWidth(); // Calcula a largura máxima da célula
$pdf->AddPage();
$pdf->SetFont('Calibri','B',11);
$hCell = $pdf->GetCellHeight($entrelinhas); // Calcula a altura da celula

$pdf->PrintTitulo('OBJETIVO');
$pdf->MultiCell($wCell,$hCell,$txtObjetivo);
$pdf->PrintTitulo('DESCRIÇÃO DA INSPEÇÃO');
$pdf->MultiCell($wCell, $hCell, $txtDesc);
$pdf->PrintTitulo('INPEÇÃO');
$pdf->PrintTexto($hCell, 'Data da inspeção: ', 'B');
$pdf->PrintTexto($hCell, 'XXX');
$pdf->Ln();

// Column headings
$header = array('Nº','RASTREAMENTO/ SETOR','Material','CORRENTE[mm] CINTA  [t]','COMPRIMENTO DA LINGA/CINTA','RAMAIS','APROVADA','MOTIVO');
// Data loading
$data = $pdf->LoadData($tabela);
$pdf->BasicTable($header, $data);

$pdf->PrintTitulo('TEXTO DE EXEMPLO: LOREM IPSUM');
$pdf->MultiCell($wCell,$hCell,$lorem);
$pdf->Cell(10,10,'Hello Worldddddddddd!');
$pdf->Ln();
$pdf->Cell(0,10,'Hello Worldddddddddd!');
//for($i=1;$i<=40;$i++)
//    $pdf->Cell(0,10,'Printing line number '.$i,0,1);
$pdf->Output();
