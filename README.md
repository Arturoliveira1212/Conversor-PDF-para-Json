# Conversor PDF para Json

Este projeto é uma aplicação PHP para extrair e processar informações de um arquivo PDF e gerar relatórios em formato JSON.

## Requisitos

- PHP 7.4 ou superior
- Composer (para gerenciar dependências)
- Extensão PHP `mbstring` (para manipulação de strings multibyte)
- Extensão PHP `curl` (para requisições HTTP, se necessário)
- Composer: [Instalação](https://getcomposer.org/download/)

## Execução

Para instalar as dependências necessárias basta rodar:

```
composer install
```

Para realizar a conversão, basta executar o script main:

```
php main.php
```

Ao executar o script main, um arquivo `dados.json` será gerado.