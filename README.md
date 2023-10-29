

# Fade\Router

O Fade\Router é um sistema de roteamento orientado a objetos desenvolvido em PHP 8, criado com o propósito de ser uma solução versátil para toda a comunidade PHP. Nosso objetivo é simplificar o processo de inicialização de uma aplicação PHP, eliminando a necessidade de criar sistemas de roteamento complexos baseados em arrays a cada nova aplicação.

## Objetivo

Em vez de perder tempo e energia criando um sistema de roteamento do zero a cada projeto, o Fade\Router foi projetado para tornar o processo de roteamento de uma aplicação PHP simples, rápido e robusto.

## Recursos Principais

Com este pacote, você pode:

1. **Separar Rotas por Métodos HTTP:** Organize suas rotas de acordo com os principais métodos HTTP, como GET, PUT, POST e DELETE.

2. **Nomear Rotas:** Atribua nomes significativos às suas rotas para facilitar a referência em seu código e recupere facilmente em suas views.

3. **Utilizar Middlewares:** Implemente middlewares para adicionar funcionalidades intermediárias às suas rotas.

4. **Criar Grupos de Rotas:** Agrupe rotas para:

   4.1. Prefixar URLs de rotas.

   4.2. Prefixar nomes de rotas.

   4.3. Aplicar middlewares a um grupo de rotas.

5. **Gerenciar Rotas de Erro:** Defina ações separadas e facilmente configuráveis para lidar com erros do tipo Not Found (404) e Internal Server Error (500).

6. **Registro de Log de Exceções:** Registre todas as exceções lançadas durante o roteamento ou não tratadas pelos controladores, ajudando na depuração e no monitoramento de erros.

## Documentação

### Configurações iniciais

O Fade\Router é um sistema de roteamento simples e direto, como pode ser visto abaixo. Primeiramente, você deve instalar o pacote usando o comando `composer require fade\router`. Em seguida, crie um arquivo .htaccess com as seguintes diretivas:

```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*)$ /index.php?url=$1 [QSA,L]
```

Depois, basta instanciar um objeto do tipo Router e definir suas rotas com suas respectivas configurações.

### Exemplo Genérico de Uso do Fade\Router

```php
<?php

use Venancio\Fade\Core\Router;
use MyApplication\Controllers\HomeController;
use MyApplication\Controllers\UserController;
use MyApplication\Controllers\NotFound;
use MyApplication\Controllers\InternalServerError;
use MyApplication\Middlewares\AdminMiddleware;
use MyApplication\Middlewares\EspecialUserMiddleware;

$router = new Router();
$router->get('/', [HomeController::class, 'index'])->name('home.index');

// Middleware único
$router->middleware([EspecialUserMiddleware::class])->post('/user', [UserController::class, 'store'])->name('user.store');

// Grupos de rotas
$router->group(['prefix' => 'admin', 'name' => 'admin.', 'middleware' => [AdminMiddleware::class]], function () use ($router){
        $router->put('/user/{id}', [UserController::class, 'update'])->name('user.update');
        $router->delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
});

// Ações de fallback
$router->fallbackNotFound(NotFound::class, 'report');
$router->fallbackInternalServerError(InternalServerError::class, 'report');

$router->dispatch();
```

### Criando a primeira rota

Para criar a primeira rota de sua aplicação com Fade\Router, basta instanciar um objeto do tipo `Venancio\Fade\Core\Router` e, em seguida, definir qual método HTTP será utilizado, seguido da URI e da ação, que será um array tendo no primeiro índice a classe do controlador e no segundo índice o método do controlador a ser acionado.

```php
$router = new Router();
$router->get('/', [HomeController::class, 'index']);
```

Como mencionado anteriormente, o Fade\Router oferece tratamento diferenciado para erros 404 e 500. Portanto, antes de realizar o despacho do $router, é necessário definir as duas rotas de fallback. Em cada método, é esperada a classe controladora seguida do método que será acionado. Isso permite definir, por exemplo, uma view ou alguma ação específica para erros do tipo 400 ou 500.

```php
$router->fallbackNotFound(NotFound::class, 'report'); // 400
$router->fallbackInternalServerError(InternalServerError::class, 'report'); // 500
```

Em seguida, basta chamar o método `dispatch()` para iniciar o roteamento da aplicação.

```php
$router->dispatch();
```
Não definir as actions de fallback lançará exceptions do tipo:

 1.  ```Venancio\Fade\Core\Exceptions\FallbackNotFoundControllerUndefined```
 2. ```Venancio\Fade\Core\Exceptions\FallbackNotFoundMethodUndefined```
 3. ```Venancio\Fade\Core\Exceptions\FallbackInternalServerErrorControllerUndefined```
 4. ```Venancio\Fade\Core\Exceptions\FallbackInternalServerErrorMethodUndefined```.

### Rotas com parâmetros dinâmicos
Podemos trabalhar com rotas dinâmicas utilizando Fade\Router, para isso basta indicar o parâmetro dinâmico por meio da sintaxe ```{param}``` :
````php
$router->get('/user/{id}', [UserController::class, 'show']);
````
Para receber esse parâmetro dinamicamente na controller da rota, basta indicar em seu método que ali será recebido um parâmetro:
````php
class UserController
{
	public function show(string|int $id):void
	{
		echo $id;
	}
}
````
Caso possua mais de um parâmetro dinâmico, basta inserir a mesma quantidade de parâmetros para recebimento no controller

### Trabalhando com rotas nomeadas
No momento da criação de nossas rotas podemos definir um determinado nome para a rota, que poderá ser usado posteriormente, ao longo da aplicação, para referência. **O método ```name()``` sempre deve ser o último no encadeamento de métodos**.


```php
$router = new Router();
$router->get('/user/{id}', [UserController::class, 'show'])->name('user.show');
```


Após sua definição, essa rota poderá ser chamada em qualquer lugar da aplicação, utilizando a própria classe ```Venancio\Fade\Core\Router``` por meio do método estático  ``` getNamedRoute()```. Esse método espera por dois parâmetros (sendo o último opcional): o nome da rota e, quando necessário, um array com os parâmetros necessários para a rota:
```php
<a href="<?= \Venancio\Fade\Core\Router::getNamedRoute('user.show', [$idUser]) ?>"/>
```
A tentativa de atribuir um mesmo nome para duas rotas lançará uma exception do tipo  ```Venancio\Fade\Core\Exceptions\DuplicateNamedRoute``` enquanto uma tentativa de acessar uma rota por meio de um nome inexistente lançará uma exception do tipo ```Venancio\Fade\Core\Exceptions\UndefinedNamedRoute```.

A tentativa de passar mais ou menos parâmetros que o necessário para uma rota nomeada lançará uma exception do tipo ```Venancio\Fade\Core\Exceptions\InsufficientArgumentsForTheRoute```

### Rotas PUT e DELETE 
Como, de forma nativa, navegadores não suportam o uso dos métodos PUT e DELETE, para que o roteamento ocorra de maneira adequada é necessário, sempre que desejar enviar uma requisição como PUT ou DELETE fornecer um formulário de método POST um input do tipo hidden de nome _method com o tipo do método HTTP em questão.

Visando facilitar o seu uso, Fade\Router possui métodos estáticos em sua classe que já fazem esse serviço, bastando apenas invocar cada um, respectivamente, ```methodPUT()``` e ```methodDELETE()```

```php
    <form action="<?= \Venancio\Fade\Core\Router::getNamedRoute('admin.user.update', [$idUser])   ?>">
        <?=  \Venancio\Fade\Core\Router::methodPUT() ?>
    </form>
```

```php
    <form action="<?= \Venancio\Fade\Core\Router::getNamedRoute('admin.user.destroy', [$idUser])   ?>">
        <?=  \Venancio\Fade\Core\Router::methodDELETE() ?>
    </form>
```


## Contribuições

Agradecemos a contribuição de qualquer membro da comunidade PHP interessado em aprimorar o Fade\Router. Sinta-se à vontade para abrir problemas ou enviar solicitações de pull em nosso repositório no GitHub.

## Licença

Este pacote é licenciado sob a [inserir nome da licença] License. Consulte o arquivo LICENSE para obter detalhes.

## Autores

Desenvolvido com paixão por [seu nome ou nome da equipe].

Dúvidas ou Sugestões? Entre em contato em [seu endereço de e-mail ou site].

**O Fade\Router - Simplificando o Roteamento em PHP para Você!**
