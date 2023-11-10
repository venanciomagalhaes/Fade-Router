


# Fade\Router (Português - BR)

``composer require fade/router``

O Fade\Router é um sistema de roteamento orientado a objetos desenvolvido em PHP 7.4, criado com o propósito de ser uma solução versátil para toda a comunidade PHP. Nosso objetivo é simplificar o processo de inicialização de uma aplicação PHP, eliminando a necessidade de criar sistemas de roteamento complexos baseados em arrays a cada nova aplicação.

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
	public function show($id):void
	{
		echo $id;
	}
}
````
Caso possua mais de um parâmetro dinâmico, basta inserir a mesma quantidade de parâmetros para recebimento no controller.  No seu controlador, utilize a superglobal ```$_REQUEST``` para obter o corpo da sua requisição. Esta superglobal é processada durante o roteamento para possibilitar essa operação.

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
Como, de forma nativa os navegadores não suportam o uso dos métodos PUT e DELETE, para que o roteamento ocorra de maneira adequada é necessário, sempre que desejar enviar uma requisição como PUT ou DELETE, fornecer por meio de um formulário de método POST um input do tipo hidden de nome _method com o tipo do método HTTP em questão.

Visando facilitar o seu uso, Fade\Router possui métodos estáticos em sua classe que já fazem esse serviço, bastando apenas invocar cada um, respectivamente, ```methodPUT()``` e ```methodDELETE()```

```php
    <form method="POST" action="<?= \Venancio\Fade\Core\Router::getNamedRoute('admin.user.update', [$idUser])   ?>">
        <?=  \Venancio\Fade\Core\Router::methodPUT() ?>
    </form>
```

```php
    <form method="POST" action="<?= \Venancio\Fade\Core\Router::getNamedRoute('admin.user.destroy', [$idUser])   ?>">
        <?=  \Venancio\Fade\Core\Router::methodDELETE() ?>
	<input type="submit" value="DELETE">
    </form>
```

### Middlewares

O middleware é uma camada intermediária de software que atua entre a requisição do cliente e a resposta do servidor. O principal objetivo do middleware é processar e intermediar as requisições HTTP, executando ações ou verificações específicas antes que essas requisições alcancem os controladores da aplicação. Alguns exemplos comuns de tarefas realizadas por middlewares incluem autenticação de usuários, autorização, registro de log, manipulação de cookies, tratamento de exceções, entre outros.

Para utilizar middlewares em uma rota com Fade\Router é muito simples: basta utilizar o método ```middleware()``` passando como parâmetro um array com as classes de middleware que deverão ser executadas. **O método middleware deve sempre ser o primeiro do encadeamento de métodos**.

````php
$router->middleware([AuthMiddleware::class, EspecialUserMiddleware::class])->post('/user', [UserController::class, 'store'])->name('user.store');
````
As classes de middleware devem obrigatóriamente implementar a interface ```Venancio\Fade\Core\Interfaces\Middleware``` e a trait ```Venancio\Fade\Core\Traits\ParamsMiddleware```. Caso a implementação não seja realizada, será lançada uma exception do tipo ```Venancio\Fade\Core\Exceptions\InvalidTypeMiddleware```.

Toda a lógica do middleware deverá ser chamada no método ```handle()``` de sua classe. **Por padrão todos os middlewares repassam a requisição adiante, a menos que você faça algo para que isso seja impedido**. Ou seja, para que a requisição não chegue ao controller você deverá implementar a partir do método handle sua própria lógica, como no exemplo:

````php
class AuthMiddleware implements Middleware
{
    use ParamsMiddleware;

    public function handle():void
    {
	// insira aqui uma lógica para obter autenticacao
        if(!$auth){
           header('Location: /login ');
        }
    }
}
````
Além disso, em rotas que possuem parâmetros dinâmicos esses parâmetros podem ser recuperados dentro do middleware utilizando a propriedade ```$this->params```, que retornará um array com os parâmetros dinâmicos na ordem de fornecimento.

### Definindo um grupo de rotas
Muitas vezes desejamos que um conjunto de rotas compartilhem de uma mesma configuração: seja um prefixo para as rotas, um prefixo para os nomes das rotas ou mesmo middlewares em comuns. Para isso, com Fade\Router podemos facilmente definir um grupo de rotas que vai compartilhar de determinada configuração. Para isso, basta invocar o método ```group()``` que espera dois parâmetros: um array associativo de configuração - que pode possuir as chaves ```prefix``` (para prefixo de rotas), ```name``` (para prefixo de nome de rotas) e ```middleware``` (para compartilharem um middleware em comum) - e uma função anônima onde as rotas do grupo poderão ser definidas individualmente:

````php
$router->group(['prefix' => 'admin', 'name' => 'admin.', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function () use ($router){
        $router->put('/user/{id}', [UserController::class, 'update'])->name('user.update');  // rota: PUT admin/user/{id} | name : admin.user.update
        $router->delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy'); // rota: DELETE admin/user/{id} | name : admin.user.destroy
});
````
Dentro de um group podemos definir outros groups, assim como podemos definir middlewares individuais para rotas dentro do grupo.


### Rotas de gerenciamento de erros (404 e 500)

Como mencionado anteriormente, o Fade\Router oferece tratamento diferenciado para erros 404 (not found) e 500 (internal server error). Portanto, antes de realizar o despacho do $router, é necessário definir as duas rotas de fallback. Em cada método, é esperada a classe controladora seguida do método que será acionado na ocorrência do erro. Isso permite definir, por exemplo, uma view ou alguma ação específica para erros do tipo 400 ou 500.

```php
$router->fallbackNotFound(NotFound::class, 'report'); // 400
$router->fallbackInternalServerError(InternalServerError::class, 'report'); // 500
```

Além disso, Fade\Router possui um tratamento especial que permite que um usuário seja facilmente redirecionado para a action de 404 (not found). Para isso, basta em sua aplicação lançar uma exception não tratada do tipo ```Venancio\Fade\Core\Exceptions\NotFound``` e então a action de fallbackNotFound será acionada.

### Logs
Eventuais exceptions relacionadas as configurações de Fade\Router bem como as demais exceptions não tratadas na aplicação possuem registro de log em ```logs/fade/router.log```. Em casos de dúvidas, verificar o log será um bom começo para o debug da aplicação.

### Tests
Fade\Router possui mais de 38 testes que podem ser verificados e acompanhados executando  ```composer require phpunit/phpunit --dev``` e ```vendor/bin/phpunit vendor/fade/router/tests/ --testdox --colors```.

## Contribuições

Agradecemos a contribuição de qualquer membro da comunidade PHP interessado em aprimorar o Fade\Router. Sinta-se à vontade para abrir problemas ou enviar solicitações de pull em nosso repositório no GitHub.

## Licença

Este pacote é licenciado sob a [MIT](https://github.com/venanciomagalhaes/Fade-Router/blob/main/LICENSE) License. Consulte o arquivo LICENSE para obter detalhes.
/
## Autores

Desenvolvido com paixão por [Venâncio Magalhães](https://www.linkedin.com/in/deividsonvm/).

Dúvidas ou Sugestões? Entre em contato.
