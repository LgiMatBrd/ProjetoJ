<?php

/*
 * 
 */

define('ROOT_DIR', dirname(dirname(__FILE__)));

ob_start();

require '../lib/pdfgenerator/fpdf.php';
require '../config/psl-config.php';
require '../config/db_connect.php';
require '../config/global.php';
//set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_DIR . '/lib/phpmailer');
//require 'class.phpmailer.php';
require ROOT_DIR.'/lib/phpmailer/class.phpmailer.php';

set_time_limit(60);

class PDF extends FPDF
{
    private $footerPos;             // Posição do rodapé em relação à borda inferior
    private $pageBorder;            // Posição da borda da página
    protected $recuoEsquerda;       // Recuo a partir da margem esquerda
    protected $recuoDireita;        // Recuo a partir da margem direita
    protected $hHeader;             // Posição Y após o header
    //protected $acceptPageBreak;     // (Boolean) Controla a quebra automática de página
    public $fatorWord;              // (Boolean) Valor que é necessário somar à entrelinhas para se aproximar ao Word
    public $customArea;             // Região salva para uso posterior array(tipo, page, y, x, w, h)
    protected $marcador_numerico;   // Salva o marcador numérico do título (faz a contagem de títulos)
    
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        $this->fatorWord = 0.215; // Valor a ser somado à entrelinhas para se aproximar ao Word
        $this->hHeader = 0;
        $this->customArea = array();
        $this->marcador_numerico = 1;
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
    // Cria uma área editável posteriormente
    function AddCustomArea($tipo, $page = NULL, $y = NULL, $x = NULL, $w = 0, $h = 0)
    {
        if ($page === NULL) $page = $this->page;
        if ($y === NULL) $y = $this->y;
        if ($x === NULL) $x = $this->x;
        $this->customArea[] = array($tipo, $page, $y, $x, $w, $h);
    }
    // Recua para uma área editável definida anteriormente
    function GotoCustomArea($tipo, $shiftOut = true)
    {
        static $tipoAnterior, $countAnterior, $i, $customs, $keys;
        if ($tipoAnterior != $tipo)
        {
            $i = -1;
        }
        $i++;
        if ($tipoAnterior != $tipo || $countAnterior != count($this->customArea))
        {
            $customs = array();
            $keys = array();
            foreach ($this->customArea as $key => $custom)
            {
                if ($custom[0] == $tipo)
                {
                    $customs[] = $custom;
                    $keys[] = $key;
                }
            }
        }
        $tipoAnterior = $tipo;
        if ($i >= count($customs))
            return false;
        $this->page = $customs[$i][1];
        $this->y = $customs[$i][2];
        $this->x = $customs[$i][3];
        
        if ($shiftOut)
            unset($this->customArea[$keys[$i]]);
        $countAnterior = count($this->customArea);
        
        return [
            'y' => $customs[$i][2],
            'x' => $customs[$i][3],
            'w' => $customs[$i][4],
            'h' => $customs[$i][5]
                ];
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
        $this->SetFont('','B');
        $this->SetX($this->recuoEsquerda);
        $this->Cell($this->GetCellWidth(),$this->GetCellHeight(3.5),$this->marcador_numerico++.'. '.utf8_decode($str),0,1);
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
    function PrintRow($wcols, $row, $entrelinhas, $border=0, $align='J', $fill=false, $minh=0)
    {
        $lx = $this->x;
        $this->_row_decodeUTF8($row);
        
        $qtdmulticells = array();
        $rowh = $this->_countRowLinhas($row, $qtdmulticells, $wcols);
        
        $h = $this->GetCellHeight($rowh*($entrelinhas+$this->fatorWord));
        if ($h < $minh)
        {
            $rowh = floor($minh / $this->GetCellHeight($entrelinhas, 1));
            $h = $this->GetCellHeight($rowh*($entrelinhas+$this->fatorWord));
        }
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
            
            if ($txt === '{{custom}}')
            {
                if ($resto < $h)
                    $this->AddCustomArea('celula', $this->page, $this->y, $this->x, $w, $resto);
                else
                    $this->AddCustomArea('celula', $this->page, $this->y, $this->x, $w, $h);
            }
            
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
            if ($txt === '{{custom}}')
            {
                $this->AddCustomArea('celula', $this->page, $this->y, $this->x, $w, $h);
            }
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
        if ($txt !== '{{custom}}')
            parent::MultiCell($w, $lh, $txt, '', $align);
        
        
        $this->SetXY($ax + $w, $ay);
        return $ly;
    }
    // Simple table
    function BasicTable($data, $maxw, $header = NULL, $minh = 0, $align = NULL)
    {
        //$maxw = array(17,30,21,27,28,15,21,31);
        $this->x = ($this->w - array_sum($maxw))/2;
        if ($align == NULL)
            $align = array('C','J');
        else
        {
            if (!is_array($align))
            {
                $align = [$align, $align];
            }
        }
        if ($header != NULL)
        {
            // Header
            $cStyle = $this->FontStyle;
            $this->SetFont('', 'B');
            $this->PrintRow($maxw, $header, 1.0, 1, $align[0], false, $minh);
            $this->SetFont('', $cStyle);
        }
        
        // Data
        foreach($data as $row)
        {
            $this->PrintRow($maxw, $row, 1.0, 1, $align[1], false, $minh);
        }
    }
    
    function PrintGraph($ySteps, $h, $xTexts, $valores, $cores, $espacamento = 10, $drawcolor = 150)
    {
        $borda = 7;
        $textoLinha = 1;
        $cDrawColor = $this->DrawColor;
        $this->SetDrawColor($drawcolor);
        if (!empty($xTexts))
            $lh = $this->FontSize + 4;
        else
            $lh = 0;
        if ($this->y+$h > $this->PageBreakTrigger)
            $this->AddPage();
        
        $this->Rect($this->recuoEsquerda, $this->y, $this->w - $this->recuoDireita - $this->recuoEsquerda, $h);
        if ($lh && $lh > $borda)
            $h = $h - $borda - $lh;
        else
            $h = $h - $borda*2;
        
        $maxY = $ySteps * ceil(max($valores)/$ySteps);
        if (!$maxY) $maxY = 1;
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
        $cx2 = $cx;
        $cols = count($valores);
        //$mWidth = ($this->w - $cx - 5*$cols - $this->rMargin) / $cols;
        $mWidth = ($this->w - $cx - $espacamento*$cols + $espacamento/2 - $this->recuoDireita - $borda) / $cols;
        $hUnity = $hSteps / $ySteps;
        
        for ($i = 0; $i < $cols; $i++)
        {
            $ch = $valores[$i]*$hUnity;
            $this->SetFillColor($cores[$i]['r'],$cores[$i]['g'],$cores[$i]['b']);
            $this->Rect($cx, $cy - $ch, $mWidth, $ch, 'F');
            if (!empty($xTexts))
            {
                $xTextW = $this->GetStringWidth(utf8_decode($xTexts[$i]));
                $this->x = $cx + ($mWidth - $xTextW)/2;
                $this->y = $cy;
                $this->Cell($xTextW, $lh, utf8_decode($xTexts[$i]));
            }
            $cx += $mWidth + $espacamento;
        }
        
        $this->x = $cx;
        $this->y = $cy+$borda+$lh;
        $this->DrawColor = $cDrawColor;
        if($this->page>0)
            $this->_out($this->DrawColor);
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
        $this->Image('../assets/report_images/logo.jpg',12,12,$lCelulaLogo-4,$alturaCelulaLogo-4);
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

function elaboraRelatorio($cliente, $vistoria, $itensVistoriados, $itensTotal, $itensAprovados, $itensReprovados)
{
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
$xTexts = [];
    $data_criacao = new DateTime($vistoria['data_criacao'], new DateTimeZone('UTC'));
    $data_criacao = $data_criacao->getTimestamp();
    
    $txtObjetivo = 
<<< EOT
Apresentar um relatório sobre a inspeção realizada em lingas de corrente e cintas de elevação para a empresa {$cliente['nome']}.
EOT;
    $dataStr = strftime('%B', $data_criacao);
    $dataStr2 = strftime('%Y', $data_criacao);
    $txtDesc = 
<<< EOT
A inspeção foi realizada no mês {$dataStr} de {$dataStr2}, dentro das instalações fabris da empresa na unidade de {$vistoria['nome']}.

Os critérios utilizados para a inspeção das lingas foram a inspeção visual e a checagem dimensional do desgaste da corrente, utilizando-se gabaritos de inspeção, onde, através de um sistema "passa/não-passa" é possível determinar se a corrente está ou não em condições de uso. Os gabaritos possuem arestas de inspeção cujas dimensões possuem a medida do item analisado mais a tolerância permitida para desgaste em correntes, de acordo com a norma (NBR ISO 3076:2005).

O gabarito foi utilizado seguindo o esquema descrito na Tabela 1 a seguir.
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
        ['{{custom}}', 'Inspeção do alongamento interno: Deve-se tentar inserir a lingüeta do gabarito no vão entre os elos da corrente. A corrente deve ser reprovada caso a lingüeta consiga entrar no vão.'],
        ['{{custom}}', 'Inspeção do diâmetro da corrente: Deve-se tentar inserir o canal menor do gabarito ao redor do elo da corrente (no lado oposto à solda). A corrente seve ser reprovada caso o canal se encaixe na corrente.'],
        ['{{custom}}', 'Inspeção do alongamento externo: deve-se tentar inserir o canal maior do gabarito por fora de um elo de corrente no sentido longitudinal. Caso o canal maior do gabarito não se encaixe, a corrente deve ser reprovada.']
    ];
    $pdf->BasicTable($data, array(45, 82.5), NULL, 40);
    $pdf->Ln();
    
    
    $pdf->PrintTitulo('INSPEÇÃO');
    $pdf->PrintTexto($hCell, 'Data da Inspeção: ', 'B');
    $dataStr = strftime('%A',$data_criacao);
    if ($dataStr === 'segunda' || $dataStr === 'terça' || $dataStr === 'quarta' || $dataStr === 'quinta' || $dataStr === 'sexta')
        $dataStr .= '-feira';        
    $pdf->PrintTexto($hCell, strftime('%d de %B de %Y, %Hh%M, ',$data_criacao).$dataStr.'.');
    $pdf->Ln();
    $pdf->PrintTexto($hCell, 'Período: ', 'B');
    $pdf->PrintTexto($hCell, 'xxx');
    $pdf->Ln();
    $pdf->PrintTexto($hCell, 'Quantidade de itens inspecionados: ', 'B');
    $pdf->PrintTexto($hCell, $itensTotal);
    $pdf->Ln();
    $pdf->PrintTexto($hCell, 'Setores: ', 'B');
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
    $pdf->SetFont('','',10);
    $pdf->BasicTable($data, array(17,30,21,27,28,15,21,31), $header, 8, 'C');
    $pdf->Ln();

    $pdf->SetFont('Calibri','B',12);
    $pdf->MultiCell($wCell, $hCell,
<<< EOT
A partir dos {$itensTotal} itens inspecionados podemos afirmar que {$itensReprovados} itens apresentaram algum problema como alongamentos, desgastes, amassamento, fora dos padrões exigidos em normas, etc. em XX??? itens o material pode ser recuperado com a colocação de placas de identificação de carga e a substituição de um elo de sustentação conforme descrito nos itens abaixo, mas na sua grande maioria eles devem ser substituídos por itens que se enquadrem as normas vigentes e {$itensAprovados} item está apto a continuar a trabalhar por atender todos os requisitos exigidos em normas.
EOT
    );

    foreach ($itensVistoriados as $item)
    {
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        if (!empty($item['placa_rastreabilidade_seyconel']))
            $pdf->PrintTexto($hCell, "{$item['placa_rastreabilidade_seyconel']} - {$item['descricao']}", 'B');
        else
            $pdf->PrintTexto ($hCell, $item['descricao'], 'B');
        $pdf->Ln(2*$hCell);

        $pdf->PrintTexto($hCell, 'Capacidade de carga: '.'xxx', 'B');
        $pdf->Ln();
        $pdf->PrintTexto($hCell, 'Setor: '.$item['setor'], 'B');
        $pdf->Ln();
        // Fotos
        if ($item['item_aprovado'])
            $pdf->MultiCell($wCell, $hCell, '( X ) APROVADO  (   ) REPROVADO', 1, 0, 'C');
        else
            $pdf->MultiCell($wCell, $hCell, '(   ) APROVADO  ( X ) REPROVADO', 1, 0, 'C');
        $pdf->Ln();
        $pdf->PrintTexto($hCell, 'Resultado: ', 'B');
        $pdf->PrintTexto($hCell, $item['observacao']);
        $pdf->Ln();
    }

    $pdf->AddPage();
    $pdf->SetFont('Calibri', 'B', 12);
    $pdf->PrintTitulo('RESULTADO DA INSPEÇÃO');

    $header = array('INSPECIONADAS (PÇ)','APROVADAS (PÇ)','REPROVADAS (PÇ)');
    $data = [
        [$itensTotal, $itensAprovados, $itensReprovados]
    ];
    $pdf->BasicTable($data, array(57.8, 49.4, 49.1), $header, 10, 'C');
    $pdf->Ln();

    $valores = [$itensTotal, $itensAprovados, $itensReprovados];
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
    $step = (int)round(max($valores)/8);
    if (!$step) $step = 1;
    $pdf->PrintGraph($step, 75, array('INSPECIONADAS (PÇ)','APROVADAS (PÇ)','REPROVADAS (PÇ)'), $valores, $cores);
    $pdf->Ln();
    
    $header = array('Podem ser recuperadas (PÇ)', 'Devem ser descartadas (PÇ)');
    $data = [
        ['x', 'x']
    ];
    $pdf->BasicTable($data, array(70.2, 85.1), $header, 10, 'C');
    $pdf->Ln();

    $valores = [7, 17];
    $cores = [
        [
            'r' => 255,
            'g' => 255,
            'b' => 0
        ],
        [
            'r' => 255,
            'g' => 0,
            'b' => 0
        ]
    ];
    $step = (int)round(max($valores)/8);
    if (!$step) $step = 1;
    $pdf->PrintGraph($step, 75, array('Podem ser recuperadas (PÇ)', 'Devem ser descartadas (PÇ)'), $valores, $cores, 30);
    $pdf->Ln();

    $pdf->AddPage();
    $pdf->SetFont('', 'B');
    $pdf->MultiCell($wCell, $hCell, 'O setor de inspeções técnicas se coloca a disposição sobre dúvidas referentes a inspeção realizada.');
    $pdf->Ln(50);
    $pdf->Image('../assets/report_images/signature.png', 84.8, $pdf->GetY(), 95.1, 64.7);
    
    $pdf->AddCustomArea('back');
    $myCustom = $pdf->GotoCustomArea('celula');
    $myCx = $myCustom['x'] + ($myCustom['w'] - 26.7)/2;
    $myCy = $myCustom['y'] + ($myCustom['h'] - 35.4)/2;
    $pdf->Image('../assets/report_images/tabela1-1.jpg', $myCx, $myCy, 26.7, 35.4);
    $myCustom = $pdf->GotoCustomArea('celula');
    $myCx = $myCustom['x'] + ($myCustom['w'] - 32.2)/2;
    $myCy = $myCustom['y'] + ($myCustom['h'] - 35.4)/2;
    $pdf->Image('../assets/report_images/tabela1-2.jpg', $myCx, $myCy, 32.2, 35.4);
    $myCustom = $pdf->GotoCustomArea('celula');
    $myCx = $myCustom['x'] + ($myCustom['w'] - 40.1)/2;
    $myCy = $myCustom['y'] + ($myCustom['h'] - 32.1)/2;
    $pdf->Image('../assets/report_images/tabela1-3.jpg', $myCx, $myCy, 40.1, 32.1);
    $pdf->GotoCustomArea('back');
    
    $meuPdf = $pdf->Output('S');
    
    $email = new PHPMailer();
    $email->setLanguage('pt_br');
    $email->CharSet = 'UTF-8';
    //$email->From      = $fromEmail;
    $email->From      = FROM_EMAIL;
    $email->FromName  = FROM_NAME;
    $email->Subject   = "$cliente[nome] - $vistoria[nome]";
    $email->Body      = <<<EOT
Segue em anexo o relatório gerado automaticamente pelo sistema App SeyService.
            
Relatório referente à empresa: {$cliente['nome']}
Vistoria no setor: {$vistoria['nome']}

(Email enviado automaticamente)

EOT;
    $email->AddAddress( SEND_EMAIL );
    $email->addStringAttachment($meuPdf, "Relatorio $cliente[nome].pdf", 'base64', 'application/pdf');
    $email->Send();
    
    unset($meuPdf);
    $conn = new mysqli(HOST, USER, PASSWORD, DATABASE);
    $conn->set_charset('utf8');
    $conn->query('UPDATE `vistorias` SET `relatorio`=b\'0\' WHERE id='.$conn->escape_string($vistoria['id']));
    $conn->close();
}

$dbsVist = [
            'itemAces',
            'itemDies',
            'itemEctu',
            'itemGael',
            'itemLema',
            'itemLila',
            'itemLinc'
        ];

$resul1 = $mysqli->query('SELECT id, id_cliente, nome, data_criacao FROM `vistorias` WHERE relatorio = 0');
$qtdRelatorios = 0;
while ($row1 = $resul1->fetch_assoc())
{
    $resul2 = $mysqli->query('SELECT nome FROM `clientes` WHERE id = '.$mysqli->escape_string($row1['id_cliente']));
    $cliente = $resul2->fetch_assoc();
    $itensVistoriados = array();
    $itensTotal = 0;
    $itensAprovados = 0;
    $itensReprovados = 0;
    foreach ($dbsVist as $myDB)
    {
        $resul3 = $mysqli->query("SELECT * FROM `{$myDB}` WHERE id_vistoria = ".$mysqli->escape_string($row1['id']));
        while ($item = $resul3->fetch_assoc())
        {
            $itensVistoriados[$itensTotal] = $item;
            $itensVistoriados[$itensTotal]['nome'] = $myDB;
            $itensTotal++;
            if ($item['item_aprovado'])
                $itensAprovados++;
            else
                $itensReprovados++;
        }
    }
    $vistoria = $row1;
    elaboraRelatorio($cliente, $vistoria, $itensVistoriados, $itensTotal, $itensAprovados, $itensReprovados);
    $qtdRelatorios++;
}

$resposta = [
        'status' => 'ok',
        'h2' => 'Relatórios emitidos!',
        'msg' => $qtdRelatorios.' relatório(s) enviado(s) com sucesso!'
    ];


$out1 = ob_get_contents();
ob_end_clean();

if (!empty($out1))
{
    $resposta = [
        'status' => 'error',
        'h2' => 'Erro na emissão!',
        'msg' => $out1
    ];
}

header('Content-Type: application/json; charset=utf-8');
//echo $html_entity_decode(json_encode($arr));

//echo json_encode('oi', JSON_FORCE_OBJECT);
echo json_encode((object)$resposta);



/*$xTexts = [];
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
*/