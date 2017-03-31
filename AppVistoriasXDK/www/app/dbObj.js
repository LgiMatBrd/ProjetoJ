// Define e inicializa os objetos a serem usados na DB através de construtores

// Objeto de cliente
function Cliente()
{
    this.id = 0;
    this.nome = '';
    this.data_criacao = '';
} 

// Objeto da vistoria
function Vistoria()
{
    this.id = 0;
    this.nome = '';
    this.id_dono = '';
    this.data_criacao = '';
} 

// Objeto da vistoria
function itemVitoriado()
{
    this.id = 0;
    this.nome = '';
    this.id_dono = '';
    this.id_vistorias_pai = '';
    this.item = '';
    this.setor = '';
    this.data_criacao = '';
} 