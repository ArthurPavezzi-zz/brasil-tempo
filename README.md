# brasil-tempo
App de previsão do tempo do Brasil criado com Laravel, Vue e Tailwind.

Os dados provêm de 3 APIs:
- Os dados de busca vêm da API do [Algolia Places](https://community.algolia.com/places/)
- O código do município pesquisado vem da [API de localidades](https://servicodados.ibge.gov.br/api/docs/localidades) do IBGE
- Os dados metereológicos vêm da [API de previsão do tempo](https://portal.inmet.gov.br/manual/manual-de-uso-da-api-de-previs%C3%A3o) do [INMET - Instituto Nacional de Metereologia](https://portal.inmet.gov.br/) 

## Criação do ambiente

- Criação do projeto
```
composer create-project --prefer-dist laravel/laravel new-project
```

- Instalação do Vue
```
composer require laravel/ui
php artisan ui vue
npm install
```

- Instalação do Tailwind
```
npm install tailwindcss
```
Depois, siga as instruções do [site oficial](https://tailwindcss.com/docs/installation).

- Instalação das demais dependências
```
npm install moment
```
