// Define e inicializa os objetos a serem usados na DB através de construtores

// Objeto de cliente
function Cliente()
{
    this.id = 0; // ID da row no DB local
    this.idext = 0; // ID da row no DB externo (obtido na sincronização)
    this.nome = ''; // Nome do Cliente
    this.data_criacao = 0; // Timestamp UTC da criação
    this.modificado = 0; // Timestamp UTC da última modificação
} 

// Objeto da vistoria
function Vistoria()
{
    this.id = 0;
    this.idext = 0;
    this.nome = '';
    this.id_cliente = 0;
    this.data_criacao = 0;
    this.modificado = 0;
} 

// Objeto do item vistoriado
function itemVitoriado()
{
    this.id = 0;
    this.idext = 0;
    this.id_vistoria = 0;
    this.data_criacao = 0;
    this.modificado = 0;

    this.fotos64 = [];
    this.dados = {};
}

// Objeto para salvar dados recentes
function Recent()
{
    this.key = -1;
    this.idext = 0;
}

// Permite a clonagem de uma row da DB
function dbClone(obj)
{
    var copy;

    // Handle the 3 simple types, and null or undefined
    if (null == obj || "object" != typeof obj) return obj;

    // Handle Cliente
    if (obj instanceof Cliente) {
        copy = new Cliente();
        copy.id = obj.id;
        copy.idext = obj.idext;
        copy.nome = obj.nome;
        copy.data_criacao = obj.data_criacao;
        copy.modificado = obj.modificado;
        return copy;
    }

    // Handle Vistoria
    if (obj instanceof Vistoria) {
        copy = new Vistoria();
        copy.id = obj.id;
        copy.idext = obj.idext;
        copy.nome = obj.nome;
        copy.id_cliente = obj.id_cliente;
        copy.data_criacao = obj.data_criacao;
        copy.modificado = obj.modificado;    
        return copy;
    }

    // Handle itemVitoriado
    if (obj instanceof itemVitoriado) {
        copy = new itemVitoriado();
        copy.id = obj.id;
        copy.idext = obj.idext;
        copy.id_vistoria = obj.id_vistoria;
        copy.data_criacao = obj.data_criacao;
        copy.modificado = obj.modificado;
        copy.fotos64 = obj.fotos64;
        copy.dados = obj.dados;
        return copy;
    }
    
    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (var i = 0, len = obj.length; i < len; i++) {
            copy[i] = dbClone(obj[i]);
        }
        return copy;
    }
    
    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = dbClone(obj[attr]);
        }
        return copy;
    }
    throw new Error("Não foi possível clonar o objeto! Formato não suportado.");
}