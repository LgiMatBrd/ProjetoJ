/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

--
-- Estrutura da tabela `appToken`
--

CREATE TABLE IF NOT EXISTS `appToken` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` int(10) UNSIGNED ZEROFILL NOT NULL COMMENT 'ID do respectivo usuario na tabela users',
  `token` varchar(40) NOT NULL COMMENT 'Token de acesso enviado para o App',
  `dataLogin` datetime NOT NULL COMMENT 'Data do login que gerou este token',
  `ultimoAcesso` datetime NOT NULL COMMENT 'Data em que este token foi utilizado pela utlima vez',
  `descricao` varchar(50) NOT NULL COMMENT 'Descricao do dispositivo dada pelo usuario no momento do login',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tokens de acesso as funcoes web';

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes`
--

CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` int(10) UNSIGNED NOT NULL COMMENT 'ID do usuário que criou o cliente',
  `nome` varchar(150) NOT NULL COMMENT 'Nome do cliente',
  `data_criacao` datetime NOT NULL COMMENT 'Data e hora UTC em que a row foi criada',
  `modificado` datetime NOT NULL COMMENT 'Data e hora UTC em que a row foi modificada',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `itemAces`
--

CREATE TABLE IF NOT EXISTS `itemAces` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vistoria` int(10) UNSIGNED NOT NULL,
  `data_criacao` datetime NOT NULL,
  `modificado` datetime NOT NULL,
  `fotos64` text NOT NULL,
  `setor` varchar(150) NOT NULL DEFAULT '',
  `descricao` varchar(150) NOT NULL DEFAULT '',
  `capacidade` varchar(150) NOT NULL DEFAULT '',
  `identificacao` bit(1) NOT NULL DEFAULT b'0',
  `deformacao` bit(1) NOT NULL DEFAULT b'0',
  `alongamento` bit(1) NOT NULL DEFAULT b'0',
  `travas` bit(1) NOT NULL DEFAULT b'0',
  `observacao` varchar(150) NOT NULL DEFAULT '',
  `placa_rastreabilidade_seyconel` varchar(150) NOT NULL DEFAULT '',
  `item_aprovado` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Acessórios  (Ganchos, Cadeados, olhais, Manilhas) (NR-11/NBR 13545/NBR 16798)';

-- --------------------------------------------------------

--
-- Estrutura da tabela `itemDies`
--

CREATE TABLE IF NOT EXISTS `itemDies` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vistoria` int(10) UNSIGNED NOT NULL,
  `data_criacao` datetime NOT NULL,
  `modificado` datetime NOT NULL,
  `setor` varchar(150) NOT NULL DEFAULT '',
  `descricao` varchar(150) NOT NULL DEFAULT '',
  `capacidade` varchar(150) NOT NULL DEFAULT '',
  `desenho_tecnico` bit(1) NOT NULL DEFAULT b'0',
  `medidas_batem_com_desenho` bit(1) NOT NULL DEFAULT b'0',
  `deformacoes` bit(1) NOT NULL DEFAULT b'0',
  `olhal_em_bom_estado` bit(1) NOT NULL DEFAULT b'0',
  `observacao` varchar(150) NOT NULL DEFAULT '',
  `placa_rastreabilidade_seyconel` varchar(150) NOT NULL DEFAULT '',
  `item_aprovado` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Dispositivos Especiais: (NR 11)';

-- --------------------------------------------------------

--
-- Estrutura da tabela `itemEctu`
--

CREATE TABLE IF NOT EXISTS `itemEctu` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vistoria` int(10) UNSIGNED NOT NULL,
  `data_criacao` datetime NOT NULL,
  `modificado` datetime NOT NULL,
  `setor` varchar(150) NOT NULL DEFAULT '',
  `comprimento` varchar(150) NOT NULL DEFAULT '',
  `descricao` varchar(150) NOT NULL DEFAULT '',
  `ramal` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `capacidade` varchar(150) NOT NULL DEFAULT '',
  `danos_olhais` bit(1) NOT NULL DEFAULT b'0',
  `danos_costura_corpo` bit(1) NOT NULL DEFAULT b'0',
  `danos_costura_principal` bit(1) NOT NULL DEFAULT b'0',
  `cortes` bit(1) NOT NULL DEFAULT b'0',
  `abrasao` bit(1) NOT NULL DEFAULT b'0',
  `elemento_inicial` varchar(150) NOT NULL DEFAULT '',
  `elemento_de_ligacao` varchar(150) NOT NULL DEFAULT '',
  `elemento_final` varchar(150) NOT NULL DEFAULT '',
  `etiqueta_identificacao` bit(1) NOT NULL DEFAULT b'0',
  `observacao` varchar(150) NOT NULL DEFAULT '',
  `placa_rastreabilidade_seyconel` varchar(150) NOT NULL DEFAULT '',
  `item_aprovado` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Eslingas, cintas planas e tubulares. (NR-11 NBR 15637 1 e 2)';

-- --------------------------------------------------------

--
-- Estrutura da tabela `itemGael`
--

CREATE TABLE IF NOT EXISTS `itemGael` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vistoria` int(10) UNSIGNED NOT NULL,
  `data_criacao` datetime NOT NULL,
  `modificado` datetime NOT NULL,
  `setor` varchar(150) NOT NULL DEFAULT '',
  `descricao` varchar(150) NOT NULL DEFAULT '',
  `capacidade` varchar(150) NOT NULL DEFAULT '',
  `deformacao` bit(1) NOT NULL DEFAULT b'0',
  `came_danificado` bit(1) NOT NULL DEFAULT b'0',
  `olhal_danificado` bit(1) NOT NULL DEFAULT b'0',
  `trava_danificada` bit(1) NOT NULL DEFAULT b'0',
  `pinos_danificado` bit(1) NOT NULL DEFAULT b'0',
  `observacao` varchar(150) NOT NULL DEFAULT '',
  `placa_rastreabilidade_seyconel` varchar(150) NOT NULL DEFAULT '',
  `item_aprovado` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Garras de elevação  (NR-11)';

-- --------------------------------------------------------

--
-- Estrutura da tabela `itemLema`
--

CREATE TABLE IF NOT EXISTS `itemLema` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vistoria` int(10) UNSIGNED NOT NULL,
  `data_criacao` datetime NOT NULL,
  `modificado` datetime NOT NULL,
  `setor` varchar(150) NOT NULL DEFAULT '',
  `descricao` varchar(150) NOT NULL DEFAULT '',
  `capacidade` varchar(150) NOT NULL DEFAULT '',
  `olhal_danificado` bit(1) NOT NULL DEFAULT b'0',
  `exrt_ext_danificada` bit(1) NOT NULL DEFAULT b'0',
  `alavanca_danificada` bit(1) NOT NULL DEFAULT b'0',
  `base_inferior_danificada` bit(1) NOT NULL DEFAULT b'0',
  `observacao` varchar(150) NOT NULL DEFAULT '',
  `placa_rastreabilidade_seyconel` varchar(150) NOT NULL DEFAULT '',
  `item_aprovado` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Levantador magnético  (NR 11)';

-- --------------------------------------------------------

--
-- Estrutura da tabela `itemLila`
--

CREATE TABLE IF NOT EXISTS `itemLila` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vistoria` int(10) UNSIGNED NOT NULL,
  `data_criacao` datetime NOT NULL,
  `modificado` datetime NOT NULL,
  `setor` varchar(150) NOT NULL DEFAULT '',
  `descricao` varchar(150) NOT NULL DEFAULT '',
  `capacidade` varchar(150) NOT NULL DEFAULT '',
  `comprimento` varchar(150) NOT NULL DEFAULT '',
  `diametro` varchar(150) NOT NULL DEFAULT '',
  `ramal` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `elemento_inicial` varchar(150) NOT NULL DEFAULT '',
  `elemento_de_ligacao` varchar(150) NOT NULL DEFAULT '',
  `elemento_final` varchar(150) NOT NULL DEFAULT '',
  `identificacao_de_carga_legivel` bit(1) NOT NULL DEFAULT b'0',
  `arames_rompidos` bit(1) NOT NULL DEFAULT b'0',
  `rupturas_de_pernas` bit(1) NOT NULL DEFAULT b'0',
  `amassados` bit(1) NOT NULL DEFAULT b'0',
  `deformacao` bit(1) NOT NULL DEFAULT b'0',
  `desgastes_excessivos` bit(1) NOT NULL DEFAULT b'0',
  `danos_por_calor` bit(1) NOT NULL DEFAULT b'0',
  `reducao_de_elasticidade` bit(1) NOT NULL DEFAULT b'0',
  `observacao` varchar(150) NOT NULL DEFAULT '',
  `placa_rastreabilidade_seyconel` varchar(150) NOT NULL DEFAULT '',
  `item_aprovado` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Lingas e Laços de cabos de aço';

-- --------------------------------------------------------

--
-- Estrutura da tabela `itemLinc`
--

CREATE TABLE IF NOT EXISTS `itemLinc` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_vistoria` int(10) UNSIGNED NOT NULL,
  `data_criacao` datetime NOT NULL,
  `modificado` datetime NOT NULL,
  `setor` varchar(150) NOT NULL DEFAULT '',
  `comprimento` varchar(150) NOT NULL DEFAULT '',
  `descricao` varchar(150) NOT NULL DEFAULT '',
  `ramal` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `elemento_inicial` varchar(150) NOT NULL DEFAULT '',
  `elemento_de_ligacao` varchar(150) NOT NULL DEFAULT '',
  `elemento_final` varchar(150) NOT NULL DEFAULT '',
  `alongamento_interno` bit(1) NOT NULL DEFAULT b'0',
  `alongamento_externo` bit(1) NOT NULL DEFAULT b'0',
  `diametro_nominal` bit(1) NOT NULL DEFAULT b'0',
  `deformacao` bit(1) NOT NULL DEFAULT b'0',
  `trincas` bit(1) NOT NULL DEFAULT b'0',
  `placa_identificacao` bit(1) NOT NULL DEFAULT b'0',
  `observacao` varchar(150) NOT NULL DEFAULT '',
  `placa_rastreabilidade_seyconel` varchar(150) NOT NULL DEFAULT '',
  `item_aprovado` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='Linga de corrente (NR-11/NBR 15516 1 e 2/NBR ISO 3076/NBR ISO 1834)';

-- --------------------------------------------------------

--
-- Estrutura da tabela `login_attempts`
--

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `userid` int(10) UNSIGNED ZEROFILL NOT NULL COMMENT 'ID de usuario que tentou logar',
  `datetime` datetime NOT NULL COMMENT 'Data e hora da tentativa'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Registros de tentativas fracassadas de login';

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT 'Usuario utilizado para efetuar o login',
  `pass` varchar(128) NOT NULL COMMENT 'Senha do usuario',
  `salt` varchar(128) NOT NULL COMMENT 'Dados aleatorios usados para aumentar a seguranca e personaizar a senha',
  `email` varchar(50) NOT NULL COMMENT 'Email do usuario',
  `create_time` datetime NOT NULL COMMENT 'Data e hora UTC que a conta foi criada',
  `atributos` smallint(5) UNSIGNED DEFAULT '0' COMMENT 'Tipos de permissoes (atributos) que o usuario possui',
  `pNome` varchar(25) NOT NULL COMMENT 'Primeiro nome',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Relaciona todos os usuarios autorizados a logar no sistema';

-- --------------------------------------------------------

--
-- Estrutura da tabela `vistorias`
--

CREATE TABLE IF NOT EXISTS `vistorias` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_cliente` int(10) UNSIGNED NOT NULL COMMENT 'ID do respectivo cliente',
  `id_user` int(10) UNSIGNED NOT NULL COMMENT 'ID do usuário que criou a vistoria',
  `nome` varchar(150) NOT NULL COMMENT 'Nome da vistoria',
  `data_criacao` datetime NOT NULL COMMENT 'Data e hora UTC em que a row foi criada',
  `modificado` datetime NOT NULL COMMENT 'Data e hora UTC em que a row foi modificada',
  `relatorio` bit(1) NOT NULL DEFAULT b'0' COMMENT 'O relatório mais atual já foi gerado? (0 - Não / 1 - Sim)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='Armazena as vistorias realizadas';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
