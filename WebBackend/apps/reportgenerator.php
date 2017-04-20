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
    protected $hHeader;             // Posição Y após o header
    //protected $acceptPageBreak;     // (Boolean) Controla a quebra automática de página
    public $fatorWord;              // (Boolean) Valor que é necessário somar à entrelinhas para se aproximar ao Word
    
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        $this->fatorWord = 0.215; // Valor a ser somado à entrelinhas para se aproximar ao Word
        $this->hHeader = 0;
        parent::__construct($orientation, $unit, $size);
    }

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
    function GetCellHeight($entrelinha, $ftWord = 0)
    {
        //return round(($this->FontSize*($entrelinha + 0.18)), 0, PHP_ROUND_HALF_UP);
        if ($ftWord)
            return ($this->FontSize*($entrelinha+$this->fatorWord));
        return ($this->FontSize*($entrelinha));
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
    protected function _row_decodeUTF8(&$row)
    {
        foreach ($row as $key => $str)
            $row[$key] = utf8_decode($str);
    }
    
    // Calcula a altura da célula para a linha a ser impressa na tabela
    protected function _countRowLinhas($row, &$qtdmulticells, $maxw)
    {
        $h = 0;
        
        // Calcula a quantidade de linhas com quebra automatica de linhas ou 
        // quebra de linha explícita
	if(!isset($this->CurrentFont))
		$this->Error('No font has been set');
	$cw = &$this->CurrentFont['cw'];
        foreach ($row as $key => $txt)
        {
            $w = $maxw[$key];
            if($w==0)
                    $w = $this->w-$this->rMargin-$this->x;
            $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
            $s = str_replace("\r",'',$txt);
            $nb = strlen($s);
            if($nb>0 && $s[$nb-1]=="\n")
                    $nb--;

            $sep = -1; // Posição do último caractere de espaço
            $i = 0; // Posição atual do caractere
            $j = 0; //Posição do caractere anterior
            $l = 0; // Comprimento da linha atual
            $ns = 0; // Quantidade de caracteres de espaço na linha
            $nl = 1; // Quantidade de linhas
            while($i<$nb)
            {
                    // Get next character
                    $c = $s[$i];
                    if($c=="\n")
                    {
                            // Explicit line break
                            $i++;
                            $sep = -1;
                            $j = $i;
                            $l = 0;
                            $ns = 0;
                            $nl++;
                            continue;
                    }
                    if($c==' ')
                    {
                            $sep = $i;
                            $ns++;
                    }
                    $l += $cw[$c]; // Adiciona a largura do caractere atual ao comprimento da linha
                    if($l>$wmax)
                    {
                            // Automatic line break
                            if($sep==-1)
                            {
                                    if($i==$j)
                                            $i++;
                            }
                            else
                            {
                                    $i = $sep+1;
                            }
                            $sep = -1;
                            $j = $i;
                            $l = 0;
                            $ns = 0;
                            $nl++;
                    }
                    else
                            $i++;
            }
            $qtdmulticells[$key] = $nl;
            if ($nl > $h)
                $h = $nl;
        }
        
        return $h;
    }
    
    // Imprime uma linha inteira da tabela, celula por celula
    function PrintRow($wcols, $row, $entrelinhas, $border=0, $align='J', $fill=false)
    {
        $lx = $this->x;
        $this->_row_decodeUTF8($row);
        
        $qtdmulticells = array();
        $rowh = $this->_countRowLinhas($row, $qtdmulticells, $wcols);
        
        $h = $this->GetCellHeight($rowh*($entrelinhas+$this->fatorWord));
        $sp = $this->page;
        $ep = 1;
        $ly = 0;
        foreach ($row as $key => $txt)
        {
            $this->page = $sp;
            $ly = $this->_printCell($wcols[$key], $h, $qtdmulticells[$key], $rowh, $entrelinhas, $txt, $border, $align, $fill);
            
            if ($this->page > $ep)
            {
                $ep = $this->page;
                //$ly = $cy;
            }
        }
        $this->page = $ep;
        $this->y = $ly;
        $this->x = $lx;
        //$this->Ln();
        
    }
    
    protected function _printCell($w, $h, $cellh, $rowh, $entrelinhas, $txt, $border=0, $align='J', $fill=false)
    {
        $ax = $this->GetX(); // Coordenadas atuais
        $ay = $this->GetY();
        $lh = $this->GetCellHeight($entrelinhas,1);
        $p = $this->page;
        
        // Checa se a célula cabe no restante da página
        if ($ay+$h > $this->PageBreakTrigger)
        {
            // Não cabe no resto da página. Calcula o posicionamento vertical com base no restante a ser impresso.
            // 1. Calcula quantas linhas cabem.
            $resto = $this->PageBreakTrigger - $ay;
            $qtdlinhas = floor($resto/$lh);
            // 2. Calcula a posição vertical
            if ($qtdlinhas > $cellh)
            
                $ypos = $ay + (($resto - $cellh*$lh)/2);
            else
                $ypos = $ay;
            
            // Desenha um pedaço da borda na página atual
            $rrh = $rowh;
            while ($rrh > 0)
            {
                $this->x = $ax;
                $resto = $this->PageBreakTrigger - $this->y;
                $qtdlinhas = floor($resto/$lh);
                if ($qtdlinhas == 0)
                {
                    $this->AddPage();
                    continue;
                }
                if ($qtdlinhas < $rrh)
                {
                    $semih = $qtdlinhas*$lh;
                    $rrh -= $qtdlinhas;
                }
                else
                {
                    $semih = $rrh*$lh;
                    $rrh = 0;
                }
                $this->Cell($w, $semih, '', $border, 1, '', $fill);
                if ($rrh > 0)
                    $this->AddPage();
            }
            
            $this->page = $p;
        }
        else
        {
            $this->Cell($w, $h, '', $border, 1, '', $fill);
            if ($cellh != $rowh)
                // Calcula a posição vertical do texto
                $ypos = $ay + ((($rowh - $cellh)/2)*$lh);
            else
                $ypos = $ay;
        }
        $ly = $this->y;
         
        //$this->Cell($w, $h, '', $border, 0, '', $fill);
        
        
        $this->SetXY($ax, $ypos);
        
        parent::MultiCell($w, $lh, $txt, '', $align);
        
        $this->SetXY($ax + $w, $ay);
        return $ly;
    }
    // Simple table
    function BasicTable($data, $maxw, $header = NULL)
    {
        //$maxw = array(17,30,21,27,28,15,21,31);
        if ($header != NULL)
        {
            // Header
            $this->PrintRow($maxw, $header, 1.0, 1, 'C');
        }
        
        // Data
        foreach($data as $row)
        {
            $this->PrintRow($maxw, $row, 1.0, 1, 'J');
        }
    }
    
    function PrintGraph($ySteps, $h, $xTexts, $valores, $cores, $espacamento = 10)
    {
        $borda = 7;
        $textoLinha = 1;
        
        if ($this->y+$h > $this->PageBreakTrigger)
            $this->AddPage();
        
        $this->Rect($this->recuoEsquerda, $this->y, $this->w - $this->recuoDireita - $this->recuoEsquerda, $h);
        $h -= $borda*2;
        
        $maxY = $ySteps * ceil(max($valores)/$ySteps);
        $inter = $maxY/$ySteps;
        $tmp = (string)$maxY;
        $wc = $this->GetStringWidth($tmp);
        //$cw = &$this->CurrentFont['cw'];
        /*for ($i = 0; $tmp[$i]; $i++)
        {
            $wc += $cw[$tmp[$i]];
        }*/
        
        $hSteps = $h/$inter;
        $cy = $this->GetY()+$borda;
        $cx = $this->recuoEsquerda+$borda;
        $this->y += $h+$borda;
        
        for ($i = 0; $i <= $inter; $i++)
        {
            $this->x = $cx;
            $this->Cell($wc, 0, $ySteps*$i, 0, 0, 'R');
            $this->x += $textoLinha;
            $this->Line($this->x, $this->y, $this->w - $this->recuoDireita - $borda, $this->y);
            $this->y -= $hSteps;
        }
        
        $cy = $cy + $h;
        $cx = $cx + $wc + $textoLinha + $espacamento/2; // 1 (referente ao espaço entre texto e linha) + 5 (espaço a partir da linha)
        $cols = count($valores);
        //$mWidth = ($this->w - $cx - 5*$cols - $this->rMargin) / $cols;
        $mWidth = ($this->w - $cx - $espacamento*$cols + $espacamento/2 - $this->recuoDireita - $borda) / $cols;
        $hUnity = $hSteps / $ySteps;
        
        for ($i = 0; $i < $cols; $i++)
        {
            $ch = $valores[$i]*$hUnity;
            $this->SetFillColor($cores[$i]['r'],$cores[$i]['g'],$cores[$i]['b']);
            $this->Rect($cx, $cy - $ch, $mWidth, $ch, 'F');
            $cx += $mWidth + $espacamento;
        }
        $this->x = $cx;
        $this->y = $cy+$borda;
    }
    
    function AddPage($orientation = '', $size = '', $rotation = 0) {
        if ($this->page == count($this->pages))
            parent::AddPage($orientation, $size, $rotation);
        else
        {
            $this->page++;
            $this->x = $this->lMargin;
            if ($this->hHeader)
                $this->y = $this->hHeader;
            else
                $this->y = $this->tMargin;
        }
    }
    
     /*function Ln($h = null) {
        // Line feed; default value is the last cell height
	$this->x = $this->lMargin;
	if($h===null)
		$this->y += $this->lasth;
	else
        {
            $rh = $h;
            $hln = $this->y + $h;
            if ($hln > $this->PageBreakTrigger)
            {
                $hp = $this->PageBreakTrigger - $this->tMargin - $this->hHeader;
                $rhln = $hln;
                while ($rhln >= $hp)
                {
                    $this->AddPage();
                    $rhln -= $hp;
                }
                $this->y = $this->hHeader + $rhln;
            }
        }
    }*/
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
        $this->hHeader = $this->y;
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


$txtObjetivo = <<< EOT
Apresentar um relatório sobre a inspeção realizada em lingas de corrente e cintas de elevação para a empresa {$cliente['nome']}.
EOT;
$txtDesc = <<< EOT
A inspeção foi realizada no mês {$vistoria['data_criacao']} de {$vistoria['data_criacao']}, dentro das instalações fabris da empresa na unidade de {$vistoria['nome']}.

Os critérios utilizados para a inspeção das lingas foram a inspeção visual e a checagem dimensional do desgaste da corrente, utilizando-se gabaritos de inspeção, onde, através de um sistema “passa/não-passa” é possível determinar se a corrente está ou não em condições de uso. Os gabaritos possuem arestas de inspeção cujas dimensões possuem a medida do item analisado mais a tolerância permitida para desgaste em correntes, de acordo com a norma (NBR ISO 3076:2005).

O gabarito foi utilizado seguindo o esquema descrito na Tabela 1 a seguir.
EOT;
$txtDesc2 = <<< EOT
O critério utilizado para a inspeção das eslingas e cintas de elevação foi a inspeção visual, onde foi checado o desgaste do tecido, rastreamento e ruptura dos filamentos de acordo com a (NBR 15637). Para os dispositivos e pega chapas checamos todos os itens de cada dispositivos tirando medidas e a situação física de cada item seguindo as normas (DIN EM 287 -1 e EN 13155).
EOT;
$tabela = <<< EOT
299;.XXX;.Linga;.20;.6800;.1;.NÃO;.Identificação de carga ilegível, corrente muito.
300;.XXX;.Linga;.20;.4000;.2;.NÃO;.Alongamento no diâmetro nominal, sem identificação de.
312;.XXX;.Linga;.16;.3000;.2;.NÃO;.Identificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
312;.XXX;.Linga;.16;.3000;.2;.Identificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaIdentificação de carga ilegível, alongamentaadjksldjakldjkladhawiojaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaokdhsajkdakjxjbsakjegiwqhdaskhaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa;.NÃO
300;.XXX;.Linga;.20;.4000;.2;.NÃO;.Alongamento no diâmetro nominal, sem identificação de.
EOT;


/* Gera o conteúdo das páginas */
$wCell = $pdf->GetCellWidth(); // Calcula a largura máxima da célula
$pdf->AddPage();
$pdf->SetFont('Calibri','B',11);
$hCell = $pdf->GetCellHeight($entrelinhas, 1); // Calcula a altura da celula com o fator Word

$pdf->PrintTitulo('OBJETIVO');
$pdf->MultiCell($wCell,$hCell,$txtObjetivo);
$pdf->PrintTitulo('DESCRIÇÃO DA INSPEÇÃO');
$pdf->MultiCell($wCell, $hCell, $txtDesc);
$data = [
    ['abc', 'Inspeção do alongamento interno: Deve-se tentar inserir a lingüeta do gabarito no vão entre os elos da corrente. A corrente deve ser reprovada caso a lingüeta consiga entrar no vão.'],
    ['def', 'Inspeção do diâmetro da corrente: Deve-se tentar inserir o canal menor do gabarito ao redor do elo da corrente (no lado oposto à solda). A corrente seve ser reprovada caso o canal se encaixe na corrente.'],
    ['ghi', 'Inspeção do alongamento externo: deve-se tentar inserir o canal maior do gabarito por fora de um elo de corrente no sentido longitudinal. Caso o canal maior do gabarito não se encaixe, a corrente deve ser reprovada.']
];
$pdf->BasicTable($data, array(45, 82.5));



$pdf->PrintTitulo('INSPEÇÃO');
$pdf->PrintText($hCell, 'Data da Inspeção: ', 'B');
$pdf->PrintTexto($hCell, $vistoria['data_criacao']);
$pdf->Ln();
$pdf->PrintText($hCell, 'Período: ', 'B');
$pdf->PrintTexto($hCell, 'xxx');
$pdf->Ln();
$pdf->PrintText($hCell, 'Quantidade de itens inspecionados: ', 'B');
$pdf->PrintTexto($hCell, $itensTotal);
$pdf->Ln();
$pdf->PrintText($hCell, 'Setores: ', 'B');
$pdf->PrintTexto($hCell, 'xxx');
$pdf->Ln();

$pdf->PrintTitulo('Lista dos Itens Inspecionados');
$pdf->MultiCell($wCell,$hCell,'A Tabela 2 mostra a seguir os equipamentos inspecionados agrupados por setor.');
$header = array('Nº','RASTREAMENTO/ SETOR','Material','CORRENTE [mm] CINTA [t]','COMPRIMENTO DA LINGA/CINTA','RAMAIS','APROVADA','MOTIVO');
$data = array();
foreach ($itensVistoriados as $item)
{
    $index = count($data);
    $data[] = array();
    $data[$index][] = (empty($item['placa_rastreabilidade_seyconel']))?  '-' : $item['placa_rastreabilidade_seyconel'];
    $data[$index][] = (empty($item['setor']))?  '-' : $item['setor'];
    switch($item['nome'])
    {
        case 'itemAces':
            $material = 'Acessório';
            break;
        case 'itemDies':
            $material = 'Dispositivo';
            break;
        case 'itemEctu':
            $material = 'Eslinga';
            break;
        case 'itemGael':
            $material = 'Gael';
            break;
        case 'itemLema':
            $material = 'Lema';
            break;
        case 'itemLila':
            $material = 'Lila';
            break;
        case 'itemLinc':
            $material = 'Linga';
            break;
        default:
            $material = '-';
    }
    $data[$index][] = $material;
    $data[$index][] = (isset($item['elemento_inicial']))? $item['elemento_inicial'] : (isset($item['capacidade']))? $item['capacidade'] : '-';
    $data[$index][] = (isset($item['comprimento']))? $item['comprimento'] : '-';
    $data[$index][] = (isset($item['ramal']))? $item['ramal'] : '-';
    $data[$index][] = ($item['item_aprovado'])? 'SIM' : 'NÃO';
    $data[$index][] = (isset($item['observacao']))? $item['observacao'] : '-';
}
$pdf->BasicTable($data, array(), $header);
$pdf->Ln();

$pdf->SetFont('Calibri','B',12);
$pdf->MultiCell($wCell, $hCell, <<< EOT
A partir dos {$itensTotal} itens inspecionados podemos afirmar que {$itensReprovados} itens apresentaram algum problema como alongamentos, desgastes, amassamento, fora dos padrões exigidos em normas, etc. em XX??? itens o material pode ser recuperado com a colocação de placas de identificação de carga e a substituição de um elo de sustentação conforme descrito nos itens abaixo, mas na sua grande maioria eles devem ser substituídos por itens que se enquadrem as normas vigentes e {$aprovados} item está apto a continuar a trabalhar por atender todos os requisitos exigidos em normas.
EOT
);




$xTexts = [];
//$valores = [13, 10, 0, 1, 5];
$valores = [13, 6, 20];
$cores = [
    [
        'r' => 255,
        'g' => 0,
        'b' => 0
    ],
    [
        'r' => 0,
        'g' => 255,
        'b' => 0
    ],
    [
        'r' => 0,
        'g' => 0,
        'b' => 255
    ]
];
$pdf->SetDrawColor(150);
$pdf->PrintGraph(3, 70, $xTexts, $valores, $cores);
$pdf->Ln();
$pdf->SetDrawColor(0);
$pdf->PrintTexto($hCell, 'Data da inspeção: ', 'B');
$pdf->PrintTexto($hCell, 'XXX');
$pdf->Ln();

// Column headings
$header = array('Nº','RASTREAMENTO/ SETOR','Material','CORRENTE [mm] CINTA [t]','COMPRIMENTO DA LINGA/CINTA','RAMAIS','APROVADA','MOTIVO');
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
