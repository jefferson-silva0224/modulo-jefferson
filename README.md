# Módulo de Ordens de Serviço — Perfex CRM

## 1. Visão geral

Este projeto é um módulo personalizado desenvolvido para o **Perfex CRM 3.4.1**.

O módulo foi criado para controlar as **Ordens de Serviço (OS)** de uma empresa que trabalha com serviços de climatização e ar-condicionado.

A necessidade principal era substituir o controle de serviços que era feito de forma manual por um controle dentro do Perfex CRM.

O módulo permite cadastrar, acompanhar e finalizar Ordens de Serviço, mantendo informações como:

* Empresa
* Técnico responsável
* Data prevista
* Data realizada
* Status
* Observação
* Valor
* Aviso de vencimento

Além disso, existem regras de permissão para definir o que cada usuário pode visualizar ou alterar.

---

# 2. Objetivo do módulo

O objetivo principal é garantir que a equipe consiga acompanhar os serviços sem depender de informações registradas em papel.

Antes do módulo, existia o risco de:

* Uma Ordem de Serviço ser perdida;
* Uma informação não chegar à administração;
* Um serviço ficar atrasado sem que ninguém percebesse;
* Uma Ordem concluída ser alterada posteriormente;
* Usuários visualizarem informações que não deveriam;
* O valor do serviço ficar disponível para todos os usuários.

O módulo foi criado para centralizar essas informações e aplicar regras automaticamente.

---

# 3. Ambiente utilizado

O módulo foi desenvolvido para:

```text
Perfex CRM: 3.4.1
Linguagem principal: PHP
Framework utilizado pelo Perfex: CodeIgniter
Banco de dados: MySQL
Frontend: HTML + CSS + JavaScript
Versionamento: Git / GitHub
```

O módulo é instalado dentro do Perfex através da área de módulos.

---

# 4. Nome do módulo

O nome utilizado para o módulo é:

```text
ordens_servico
```

A pasta principal do projeto é:

```text
ordens_servico/
```

O nome da pasta é importante porque é utilizado pelo Perfex para localizar o módulo.

---

# 5. Estrutura atual do projeto

A estrutura principal é:

```text
ordens_servico/
│
├── controllers/
│   └── Ordens_servico.php
│
├── views/
│   └── ordens_servico/
│       ├── manage.php
│       └── form.php
│
└── ordens_servico.php
```

Cada arquivo possui uma responsabilidade específica.

---

# 6. Responsabilidade de cada arquivo

## 6.1 `ordens_servico.php`

É o arquivo principal do módulo.

Ele é responsável por registrar o módulo no Perfex e definir as informações necessárias para que o sistema reconheça a extensão.

Não deve ser tratado como o arquivo principal da lógica das Ordens.

A maior parte da lógica está no controller.

---

## 6.2 `controllers/Ordens_servico.php`

Este é o principal arquivo de lógica do módulo.

Ele controla:

* Criação de OS;
* Edição de OS;
* Exclusão;
* Listagem;
* Validação das datas;
* Definição automática do status;
* Verificação de permissões;
* Bloqueio de edição de OS concluída;
* Busca das empresas;
* Busca dos técnicos;
* Salvamento no banco.

Sempre que uma regra de negócio for alterada, este é um dos primeiros arquivos que deve ser verificado.

---

## 6.3 `views/ordens_servico/form.php`

É responsável pelo formulário de:

* Nova Ordem de Serviço;
* Edição de Ordem de Serviço.

É onde aparecem os campos:

```text
Empresa
Técnico
Data prevista
Data realizada
Status
Observação
Valor
```

Também existem regras JavaScript nesse arquivo.

Por exemplo, quando o usuário informa uma data realizada, o JavaScript altera visualmente o status para `Concluída`.

Porém, a regra verdadeira também está no controller.

Isso é importante:

> O JavaScript serve para melhorar o comportamento da tela, mas não deve ser considerado a única proteção das regras de negócio.

---

## 6.4 `views/ordens_servico/manage.php`

É responsável pela tela de listagem das Ordens de Serviço.

A tabela apresenta:

```text
ID
Empresa
Técnico
Data prevista
Data realizada
Status
Observação
Valor
Aviso
Ações
```

Também existem regras de apresentação nesse arquivo.

Por exemplo:

* Formatação das datas;
* Limitação visual das observações;
* Botão `Ver observação`;
* Modal com a observação completa;
* Exibição dos avisos.

---

# 7. Fluxo de uma Ordem de Serviço

O funcionamento esperado de uma OS é:

```text
Criar OS
   ↓
Selecionar empresa
   ↓
Selecionar técnico
   ↓
Definir data prevista
   ↓
Definir status
   ↓
Adicionar observação
   ↓
Informar valor, se permitido
   ↓
Salvar
   ↓
Acompanhar na listagem
   ↓
Realizar o serviço
   ↓
Informar data realizada
   ↓
Status passa para Concluída
   ↓
OS fica bloqueada para usuários comuns
```

Esse fluxo representa a lógica principal do módulo.

---

# 8. Campos da Ordem de Serviço

## Empresa

Representa o cliente para o qual o serviço será realizado.

A empresa é selecionada através dos clientes já cadastrados no Perfex.

O módulo não cria um cadastro separado de empresas.

---

## Técnico

Representa o funcionário responsável pelo serviço.

Os técnicos são buscados entre os funcionários cadastrados no Perfex de acordo com os cargos definidos para o módulo.

---

## Data prevista

Representa o dia planejado para o serviço.

Regra:

> A data prevista não pode ser anterior à data atual.

No formulário existe uma restrição HTML usando `min`.

Além disso, existe validação no controller durante a edição.

---

## Data realizada

Representa o dia em que o serviço realmente foi executado.

Regra:

> A data realizada não pode ser futura.

Ela pode ser igual ou anterior à data prevista.

Exemplo válido:

```text
Data prevista: 08/10/2026
Data realizada: 06/10/2026
```

Isso significa que o serviço foi realizado antes do planejado.

---

## Status

Atualmente existem três opções:

```text
Pendente
Em andamento
Concluída
```

Porém, existe uma regra especial para `Concluída`.

Se uma OS possui `data_realizada`, o status obrigatoriamente deve ser:

```text
Concluída
```

---

## Observação

Campo utilizado para registrar informações sobre o serviço.

Pode conter, por exemplo:

* O que foi realizado;
* Problemas encontrados;
* Peças utilizadas;
* Informações deixadas pelo técnico;
* Outras informações importantes.

---

## Valor

Representa o valor financeiro do serviço.

O campo possui controle de permissão.

Usuários sem permissão para visualizar valores não devem receber esse campo no formulário.

---

# 9. Regra mais importante: Data realizada

Esta é uma das principais regras de negócio do módulo.

Quando:

```text
data_realizada != vazio
```

o status deve ser:

```text
Concluída
```

Isso é aplicado em dois lugares.

## 9.1 No formulário

O JavaScript detecta quando a data é preenchida.

Exemplo:

```javascript
if (dataRealizada.value !== '') {
    status.value = 'Concluída';
    status.disabled = true;
}
```

Assim, o usuário percebe imediatamente que a Ordem será concluída.

---

## 9.2 No controller

A regra também é aplicada no PHP.

Isso é necessário porque um usuário pode enviar uma requisição sem utilizar corretamente o JavaScript.

A lógica é:

```php
if ($data_realizada !== null) {
    $status = 'Concluída';
}
```

Portanto:

> Nunca remover a validação do controller apenas porque o JavaScript já faz a mesma coisa.

A validação do controller é a proteção real da regra.

---

# 10. Regra de data realizada futura

Não é permitido informar uma data realizada posterior ao dia atual.

A comparação é feita no controller.

Conceito:

```text
Data realizada > Hoje
        ↓
      ERRO
```

Exemplo:

```text
Hoje: 07/10/2026

Data realizada: 08/10/2026
```

Resultado:

```text
Data inválida.
```

O formulário também utiliza:

```html
max="data atual"
```

para impedir a seleção de datas futuras diretamente na interface.

---

# 11. Regra de data prevista

A data prevista deve representar uma data atual ou futura.

Exemplo:

```text
Hoje: 07/10/2026

Data prevista: 05/10/2026
```

Não permitido.

Já:

```text
Data prevista: 10/10/2026
```

é permitido.

O formulário utiliza:

```html
min="data atual"
```

e o controller também realiza validação durante a edição.

---

# 12. Regra de Ordem concluída

Quando a OS está concluída, ela não deve continuar sendo alterada normalmente.

A regra atual é:

```text
Status = Concluída
        ↓
Usuário comum
        ↓
Não pode editar
```

O administrador pode editar.

Essa verificação é feita no controller.

Portanto, se for necessário modificar essa regra no futuro, deve-se verificar:

```text
controllers/Ordens_servico.php
```

e principalmente a função:

```php
edit($id)
```

---

# 13. Permissões

O módulo possui permissões específicas.

As principais são:

```text
view
create
edit
delete
manage_schedule
manage_status
add_observation
view_value
```

## `view`

Permite visualizar as Ordens de Serviço.

## `create`

Permite criar novas Ordens.

## `edit`

Permite editar uma Ordem.

## `delete`

Permite excluir uma Ordem.

## `manage_schedule`

Controla informações relacionadas à agenda, principalmente:

* Técnico;
* Data prevista.

## `manage_status`

Controla a alteração manual do status.

## `add_observation`

Controla o preenchimento/alteração da observação.

## `view_value`

Controla o acesso ao valor financeiro.

---

# 14. Perfis pensados para o projeto

## Sandra

Administradora.

Possui acesso completo.

Pode:

* Visualizar OS;
* Criar;
* Editar;
* Alterar status;
* Alterar agenda;
* Adicionar observação;
* Visualizar valor;
* Alterar valor;
* Alterar uma OS concluída.

---

## Dani

Assistente administrativa.

Possui acesso às informações administrativas necessárias, mas o valor deve permanecer protegido.

A ideia é que ela possa trabalhar com a OS sem visualizar o valor financeiro.

---

## Jorge

Encarregado.

Possui acesso relacionado ao acompanhamento e organização dos serviços.

---

## Técnicos

Possuem acesso restrito às informações necessárias para execução do serviço.

---

# 15. Empresas e técnicos no Perfex

Uma decisão importante do projeto foi utilizar estruturas que já existem no Perfex.

## Empresa

É buscada através dos clientes cadastrados no Perfex.

Não foi criado um segundo cadastro independente de empresas.

## Técnico

É buscado através dos funcionários cadastrados no Perfex.

Isso evita duplicar informações dentro do sistema.

---

# 16. Banco de dados da Ordem

A Ordem de Serviço utiliza a tabela criada para o módulo.

O nome esperado é baseado no prefixo do banco do Perfex:

```php
db_prefix() . 'ordens_servico'
```

Isso é importante.

Não deve ser colocado diretamente:

```text
tblordens_servico
```

porque o Perfex pode utilizar outro prefixo.

O correto é sempre utilizar:

```php
db_prefix()
```

---

# 17. Informações armazenadas

A estrutura utilizada pela OS trabalha com informações equivalentes a:

```text
id
empresa
tecnico
data_prevista
data_realizada
status
observacao
valor
datecreated
```

A estrutura deve ser verificada antes de qualquer alteração no banco.

Não alterar o nome de um campo sem verificar primeiro todos os arquivos que utilizam esse campo.

---

# 18. Como o controller funciona

## `index()`

É executado quando a página principal do módulo é aberta.

Sua responsabilidade é:

1. Buscar as Ordens;
2. Ordenar pela data prevista;
3. Enviar os dados para `manage.php`.

---

## `create()`

É executado para criar uma nova OS.

Fluxo:

```text
Abrir formulário
      ↓
Selecionar empresa
      ↓
Selecionar técnico
      ↓
Informar datas
      ↓
Informar status
      ↓
Informar observação
      ↓
Informar valor
      ↓
Enviar formulário
      ↓
Validar dados
      ↓
Inserir no banco
```

A função também força o status para `Concluída` caso exista uma data realizada.

---

## `edit($id)`

É responsável por editar uma OS existente.

Primeiro busca a Ordem pelo ID.

Depois verifica se ela pode ser editada.

Uma das verificações mais importantes é:

```text
OS concluída?
       ↓
Sim
       ↓
Usuário é administrador?
       ↓
Não → acesso negado
```

Depois disso são feitas as alterações permitidas de acordo com as permissões.

---

## `delete($id)`

Responsável pela exclusão da Ordem.

Essa função deve continuar protegida por permissão.

---

# 19. Formulário e proteção contra erro 419

Durante o desenvolvimento ocorreu o erro:

```text
419 Page Expired!
```

O problema apareceu ao salvar alterações no formulário.

A causa estava relacionada à forma como o formulário estava sendo enviado.

O formulário utilizava inicialmente:

```html
<form method="post">
```

Foi alterado para utilizar:

```php
form_open()
```

e:

```php
form_close()
```

Isso permite que o CodeIgniter/Perfex trabalhe corretamente com o token de segurança do formulário.

Portanto, ao alterar o formulário, não remover:

```php
echo form_open(...)
```

e:

```php
echo form_close()
```

sem entender o impacto.

---

# 20. JavaScript do formulário

O `form.php` possui JavaScript para controlar o status.

A lógica principal é:

```text
Usuário informa Data realizada
             ↓
JavaScript identifica
             ↓
Status = Concluída
             ↓
Campo de status fica bloqueado
```

Quando a data é removida:

```text
Data realizada vazia
        ↓
Status volta a ficar disponível
```

Essa é uma regra de interface.

A regra definitiva continua no controller.

---

# 21. Tela de listagem

A tela `manage.php` mostra todas as Ordens.

As datas são convertidas para o formato:

```text
dd/mm/aaaa
```

Exemplo:

```text
08/10/2026
```

Isso foi feito apenas para apresentação.

No banco, a data continua sendo armazenada no formato utilizado pelo MySQL.

---

# 22. Observação na listagem

Observações grandes não devem ocupar toda a largura da tabela.

Por isso, quando a observação ultrapassa determinado tamanho, o sistema apresenta apenas uma parte:

```text
Texto da observação...
```

e mostra:

```text
Ver observação
```

Ao clicar, abre uma janela com o texto completo.

Isso foi feito para preservar a organização da tabela.

---

# 23. Aviso de vencimento

A coluna `Aviso` serve para chamar atenção para Ordens que precisam de acompanhamento.

A lógica considera a data prevista e o estado da Ordem.

Entre as situações apresentadas estão:

```text
Vencida
Próxima do vencimento
-
```

O objetivo não é substituir o status.

São informações diferentes:

```text
Status → situação da Ordem
Aviso  → atenção relacionada à data
```

Por exemplo:

```text
Status: Pendente
Aviso: Próxima do vencimento
```

significa que a Ordem ainda não foi concluída e sua data está próxima.

---

# 24. Diferença entre Status e Aviso

Esta diferença deve ser mantida durante futuras alterações.

### Status

Indica o andamento do serviço.

```text
Pendente
Em andamento
Concluída
```

### Aviso

Indica a situação da data.

```text
Vencida
Próxima do vencimento
Normal/-
```

Uma OS pode estar:

```text
Pendente + Próxima do vencimento
```

ou:

```text
Concluída + -
```

São informações independentes.

---

# 25. Problemas encontrados durante o desenvolvimento

## 25.1 Observação deixando a tabela muito larga

Problema:

```text
Observação muito grande
        ↓
Tabela desorganizada
```

Solução:

* Limitar o texto na tabela;
* Criar botão `Ver observação`;
* Mostrar o conteúdo completo em modal.

---

## 25.2 Aviso invadindo a coluna Ações

Problema:

```text
Próxima do vencimento
```

era cortado ou invadia a coluna seguinte.

Foi necessário ajustar a quebra de linha e o comportamento visual do campo.

---

## 25.3 Data realizada mantendo status Pendente

Foi identificado que uma OS podia possuir:

```text
Data realizada preenchida
Status: Pendente
```

Isso não deveria acontecer.

Foi criada a regra:

```text
Data realizada preenchida
        ↓
Status = Concluída
```

A regra foi aplicada no formulário e no controller.

---

## 25.4 Data realizada futura

Foi identificado que era necessário impedir:

```text
Data realizada > hoje
```

Foi adicionada validação no formulário e no controller.

---

## 25.5 Erro 419

O formulário apresentou:

```text
419 Page Expired
```

A solução foi substituir o formulário HTML comum por:

```php
form_open()
```

e:

```php
form_close()
```

Depois disso, o salvamento voltou a funcionar.

---

# 26. Cuidados importantes para manutenção

Antes de modificar o módulo, deve-se considerar as seguintes regras.

## Não remover a validação do controller

Mesmo que exista uma validação em JavaScript ou HTML, ela não substitui a validação no PHP.

---

## Não considerar o JavaScript como segurança

O JavaScript pode ser alterado ou ignorado pelo usuário.

Regras importantes devem existir no controller.

---

## Não remover `db_prefix()`

Ao trabalhar com tabelas do Perfex, manter:

```php
db_prefix()
```

para evitar problemas caso o prefixo do banco seja diferente.

---

## Cuidado com o campo técnico

O formulário seleciona um funcionário do Perfex, mas a Ordem possui a informação do técnico armazenada de acordo com a estrutura atual do módulo.

Antes de modificar essa parte, verificar:

```text
form.php
Ordens_servico.php
tabela ordens_servico
```

Os três precisam continuar trabalhando com a mesma estrutura.

---

## Cuidado com OS concluída

Não remover a proteção de edição de OS concluída sem uma decisão sobre a regra de negócio.

Atualmente:

```text
Concluída
+
Usuário comum
=
Sem edição
```

---

# 27. Como testar uma alteração

Depois de alterar o código, não testar apenas se a página abre.

É necessário testar o fluxo completo.

### Teste 1 — Criar OS

Criar uma nova Ordem preenchendo:

```text
Empresa
Técnico
Data prevista
Status
Observação
Valor
```

Verificar se foi salva.

---

### Teste 2 — Data prevista

Tentar colocar uma data anterior a hoje.

Resultado esperado:

```text
Não permitir.
```

---

### Teste 3 — Data realizada

Colocar uma data igual ou anterior a hoje.

Resultado esperado:

```text
Status = Concluída
```

---

### Teste 4 — Data realizada futura

Tentar colocar uma data futura.

Resultado esperado:

```text
Não permitir.
```

---

### Teste 5 — Ordem concluída

Entrar com um usuário comum e tentar editar uma OS concluída.

Resultado esperado:

```text
Acesso negado.
```

Entrar como administrador.

Resultado esperado:

```text
Pode editar.
```

---

### Teste 6 — Valor

Entrar com um usuário que não possui `view_value`.

Resultado esperado:

```text
Campo Valor não aparece.
```

Entrar com usuário autorizado.

Resultado:

```text
Campo Valor aparece.
```

---

### Teste 7 — Observação

Adicionar uma observação grande.

Resultado esperado:

```text
Tabela continua organizada.
Botão "Ver observação" aparece.
```

---

### Teste 8 — Salvamento

Alterar qualquer campo e clicar em:

```text
Salvar
```

Resultado esperado:

```text
Alteração salva normalmente.
```

Se aparecer:

```text
419 Page Expired
```

verificar primeiro o uso de `form_open()` e `form_close()` no `form.php`.

---

# 28. Instalação do módulo

O módulo pode ser instalado pelo próprio Perfex.

O processo utilizado foi:

```text
Criar/alterar módulo
       ↓
Compactar pasta ordens_servico
       ↓
Gerar arquivo .zip
       ↓
Perfex
       ↓
Administração
       ↓
Módulos
       ↓
Upload Module
       ↓
Selecionar .zip
       ↓
Enviar
```

O Perfex confirmou o upload com sucesso.

---

# 29. Desenvolvimento local e Git

O projeto está separado do repositório utilizado para as atividades anteriores.

A pasta atual do módulo é:

```text
C:\Users\Usuario\Documents\modulos perfex\ordens_servico
```

Foi criado um repositório específico para esse projeto.

A intenção é manter o histórico do desenvolvimento do módulo separado dos demais trabalhos.

---

# 30. Como publicar alterações no Git

Depois de uma alteração, o fluxo esperado é:

```powershell
git status
```

Verificar os arquivos modificados.

Depois:

```powershell
git add .
```

Adicionar as alterações.

Depois:

```powershell
git commit -m "Descrição da alteração"
```

Criar o commit.

E finalmente:

```powershell
git push origin main
```

Enviar para o GitHub.

---

# 31. Antes de fazer um commit

É recomendado verificar:

```powershell
git status
```

e confirmar que apenas os arquivos esperados foram modificados.

Também é importante evitar colocar arquivos desnecessários no repositório.

Principalmente:

```text
.git
arquivos temporários
arquivos de configuração com senhas
backups
arquivos gerados automaticamente
```

---

# 32. Estado atual do projeto

Atualmente o módulo possui:

* Cadastro de Ordens de Serviço;
* Edição;
* Exclusão;
* Listagem;
* Empresas vindas dos clientes do Perfex;
* Técnicos vindos dos funcionários do Perfex;
* Data prevista;
* Data realizada;
* Status;
* Observação;
* Valor;
* Controle de permissões;
* Avisos de vencimento;
* Bloqueio de edição de OS concluída;
* Validação de datas;
* Status automático após data realizada;
* Formulário protegido contra o problema de sessão/token que causava erro 419;
* Formatação brasileira das datas;
* Modal para observações longas.

---

# 33. Decisões importantes do projeto

As seguintes decisões fazem parte da lógica atual e devem ser conhecidas antes de modificar o módulo:

### 1. Empresas

Não existe um cadastro separado criado pelo módulo.

São utilizados os clientes já existentes no Perfex.

### 2. Técnicos

São utilizados os funcionários cadastrados no Perfex.

### 3. Data realizada

Não pode ser futura.

### 4. Data realizada preenchida

Sempre significa:

```text
Concluída
```

### 5. OS concluída

Usuários comuns não podem alterar.

### 6. Valor

Possui controle de acesso próprio.

### 7. Observação

Pode ser grande, mas a listagem não deve ficar desorganizada.

### 8. Aviso

É independente do status.

### 9. Regras importantes

Devem existir no controller, não somente no JavaScript.

---

# 34. Como pensar no módulo para futuras alterações

Antes de adicionar uma funcionalidade, identificar onde ela pertence.

### Se for uma regra de negócio:

Verificar:

```text
controllers/Ordens_servico.php
```

### Se for aparência ou organização da lista:

Verificar:

```text
views/ordens_servico/manage.php
```

### Se for um campo ou comportamento do formulário:

Verificar:

```text
views/ordens_servico/form.php
```

### Se for uma regra relacionada a permissões:

Verificar:

```text
ordens_servico.php
controllers/Ordens_servico.php
Perfex → permissões do módulo
```

### Se for alteração de estrutura do banco:

Verificar primeiro:

```text
controller
form.php
manage.php
estrutura da tabela
```

Não alterar somente um desses pontos.

---

# 35. Exemplo de manutenção

Supondo que no futuro seja necessário adicionar um campo:

```text
Fotos do serviço
```

Não basta colocar um campo no `form.php`.

Seria necessário verificar:

```text
1. Banco de dados
2. Controller
3. Formulário
4. Listagem, se necessário
5. Permissões, se necessário
6. Validação
7. Testes
```

O mesmo raciocínio deve ser usado para qualquer novo campo ou regra.

---

# 36. Melhorias futuras possíveis

O módulo já possui a estrutura principal funcionando, mas algumas funcionalidades podem ser adicionadas futuramente.

Possíveis melhorias:

* Upload de fotos do serviço;
* Anexar arquivos à Ordem;
* Áudio enviado pelo técnico;
* Histórico de alterações;
* Notificações automáticas;
* Dashboard de Ordens atrasadas;
* Filtros por técnico;
* Filtros por empresa;
* Filtros por status;
* Filtros por período;
* Relatórios;
* Histórico de quem alterou cada informação;
* Notificações por e-mail;
* Integração com outras áreas do Perfex.

Essas funcionalidades não fazem parte da versão atual e devem ser tratadas como melhorias futuras.

---

# 37. Resumo para o próximo desenvolvedor

Se você acabou de entrar no projeto, o fluxo principal é:

```text
Perfex CRM
     ↓
Módulo ordens_servico
     ↓
Ordens de Serviço
     ↓
Controller
     ↓
Banco de dados
     ↓
Views
```

Os três arquivos principais para entender primeiro são:

```text
controllers/Ordens_servico.php
views/ordens_servico/form.php
views/ordens_servico/manage.php
```

A regra mais importante é:

```text
Data realizada preenchida
          ↓
Status = Concluída
          ↓
Usuário comum não pode editar
```

E as principais validações de data são:

```text
Data prevista >= hoje
Data realizada <= hoje
```

Se for necessário alterar alguma dessas regras, deve-se modificar a validação no **controller** e, quando necessário, também o comportamento visual do `form.php`.

---

# 38. Conclusão

O módulo foi desenvolvido para transformar o controle manual de serviços em um processo organizado dentro do Perfex CRM.

A estrutura foi construída pensando não apenas na criação das Ordens, mas também em:

* Controle de acesso;
* Segurança;
* Validação;
* Acompanhamento de prazos;
* Organização das informações;
* Conclusão do serviço;
* Manutenção futura.

A documentação deve ser utilizada junto com o código.

Quando uma alteração for realizada, o desenvolvedor deve verificar se ela afeta alguma das regras descritas neste documento.

O principal objetivo é manter o comportamento do sistema consistente:

```text
Criar
  ↓
Acompanhar
  ↓
Realizar serviço
  ↓
Registrar data realizada
  ↓
Concluir
  ↓
Bloquear alterações comuns
```

Esse é o fluxo principal que orienta o funcionamento atual do módulo.

### Filtros e pesquisa de Ordens de Serviço

Foi adicionada uma área de filtros na tela principal de Ordens de Serviço para facilitar a localização de registros.

Antes, a tela mostrava todas as ordens cadastradas. Com a nova funcionalidade, é possível pesquisar somente as ordens que atendem aos critérios selecionados.

Os filtros disponíveis são:

* **Empresa:** permite visualizar as ordens de uma empresa específica.
* **Técnico:** permite visualizar as ordens atribuídas a um determinado técnico.
* **Status:** permite filtrar por Pendente, Em andamento ou Concluída.
* **Aviso:** permite pesquisar ordens Atrasadas ou Próximas do vencimento.
* **Valor mínimo:** mostra somente ordens com valor igual ou superior ao informado.
* **Valor máximo:** mostra somente ordens com valor igual ou inferior ao informado.
* **Data prevista inicial e final:** permite definir um período para a data prevista.
* **Data realizada inicial e final:** permite definir um período para a data em que o serviço foi realizado.

Os filtros podem ser utilizados individualmente ou combinados.

Por exemplo, é possível pesquisar:

* Todas as ordens da empresa **Centro Calçados**.
* Todas as ordens com status **Concluída**.
* Todas as ordens de um determinado técnico.
* Todas as ordens **Atrasadas**.
* Ordens de uma empresa específica que estejam **Concluídas**.
* Ordens dentro de determinado período e com uma faixa de valor específica.

Após preencher os filtros, o botão **Pesquisar** aplica os critérios selecionados.

O botão **Limpar filtros** remove os critérios de pesquisa e retorna para a visualização de todas as ordens.

### Funcionamento técnico dos filtros

Os filtros são enviados através do método **GET** para a página principal do módulo.

O processamento é realizado no método `index()` do arquivo:

`controllers/Ordens_servico.php`

O método recebe os parâmetros enviados pela tela, verifica quais filtros foram preenchidos e adiciona as condições correspondentes à consulta no banco de dados.

A consulta é montada somente depois que os dados necessários para empresa e técnico são obtidos. Isso evita conflitos no Query Builder do CodeIgniter.

A filtragem de empresa e técnico utiliza os registros existentes no Perfex:

* Empresas são obtidas da tabela de clientes do Perfex.
* Técnicos são obtidos dos usuários/funcionários cadastrados no Perfex.

Como a tabela de Ordens de Serviço atualmente armazena o **nome da empresa** e o **nome do técnico**, o sistema primeiro identifica o nome correspondente ao registro selecionado e depois utiliza esse nome na pesquisa das ordens.

### Arquivos envolvidos

A funcionalidade de filtros envolve principalmente dois arquivos:

**Controller:**

`controllers/Ordens_servico.php`

Responsável por receber os filtros, montar a consulta e retornar somente as ordens que correspondem aos critérios.

**View:**

`views/ordens_servico/manage.php`

Responsável por apresentar os campos de filtro, os botões **Pesquisar** e **Limpar filtros** e manter os valores selecionados pelo usuário.

### Problema encontrado durante a implementação

Durante a implementação dos filtros ocorreu um erro de SQL semelhante a:

`You have an error in your SQL syntax ... near 'WHERE empresa = ...'`

O problema aconteceu porque o Query Builder do CodeIgniter estava sendo reutilizado para consultar informações de empresa/técnico enquanto a consulta principal das Ordens de Serviço estava sendo montada.

Isso fez com que partes da primeira consulta fossem reaproveitadas indevidamente na consulta seguinte, resultando em um `WHERE` sem a estrutura correta da consulta.

A solução foi buscar primeiro os dados necessários de empresa e técnico e, somente depois, iniciar uma nova consulta para a tabela de Ordens de Serviço utilizando:

`$this->db->from(db_prefix().'ordens_servico');`

Dessa forma, a consulta principal começa limpa e os filtros são adicionados corretamente.

### Resultado

Com essa alteração, a tela de Ordens de Serviço passou a permitir pesquisas mais específicas sem alterar os registros existentes.

A funcionalidade também facilita a utilização do sistema quando houver uma quantidade maior de Ordens de Serviço cadastradas, pois o usuário não precisa procurar manualmente em toda a lista.


 ## Migração dos dados de 2026
8.1 Objetivo e escopo

A migração teve como origem a planilha CONTROLE_OS_2026.xlsx.

O escopo foi definido para importar somente a aba 2026, sem importar dados da aba 2025.

O volume de referência do processo foi de 1.806 ordens de serviço. O usuário confirmou que a migração foi concluída com sucesso.

A migração não deve ser executada novamente apenas para corrigir avisos, filtros ou regras de apresentação.

8.2 Regras de mapeamento
Empresas

Os nomes das empresas na planilha foram relacionados aos clientes cadastrados no Perfex. O objetivo foi gravar o identificador correto da empresa em vez de criar cadastros duplicados.

Quando for necessário auditar um registro, confira se o empresa_id da OS corresponde ao cliente esperado.

Técnicos

As variações reconhecidas de José Carlos Ribeiro — incluindo formas como “Zé” e “J. Carlos” — foram mapeadas para:

tecnico_id = 8

Esse mapeamento foi uma regra específica da migração. Antes de reutilizá-lo em outro conjunto de dados, confirme que o funcionário de ID 8 continua sendo a pessoa correta.

Os valores “a definir” e as células de técnico em branco foram tratados como:

tecnico_id = NULL

Não foi inventado um técnico para preencher os campos ausentes.

Datas

As datas foram normalizadas para o formato aceito pelo banco:

YYYY-MM-DD

Por exemplo, uma data exibida como 18/10/2026 na planilha deve ser armazenada como 2026-10-18.

Essa conversão deve respeitar o significado original da data e não deve inventar valores para células vazias.

Status

Os valores da planilha foram normalizados para os estados correspondentes do módulo.

As regras documentadas incluem:

realizada, realizado, feito e ok: equivalentes a concluído;

agendada, remarcada e pendente: equivalentes a pendente;

cancelada e cancelado: equivalentes a cancelado;

status vazio: aberto.

A implementação real e os valores efetivamente gravados devem ser conferidos no banco quando uma auditoria exigir precisão registro a registro.

8.3 Conversão dos valores financeiros

A coluna de valor apresentava tanto números nativos quanto números armazenados como texto.

O usuário confirmou a conversão de 377 valores claramente monetários, preservando os valores originais para conferência e separando os casos duvidosos e os quatro valores negativos para revisão.

Os textos não monetários, como:

a combinar;

ver c/ Sandra;

s/ valor;

não deveriam ser convertidos em números inventados. O tratamento adotado foi manter o valor numérico como NULL e preservar a informação textual na observação para conferência.

Os quatro valores negativos foram sinalizados para revisão, não descartados automaticamente. Um valor negativo pode representar uma correção, um estorno ou um erro de origem; é necessário validar o contexto antes de decidir.

8.4 Reconciliação dos totais

Durante a preparação, os totais de referência foram:

Descrição

	

Total




Soma das células originalmente numéricas

	

R$ 587.615,57




Soma adicional dos 377 valores textuais convertidos

	

R$ 237.289,82




Total normalizado de referência

	

R$ 824.905,39

A diferença ocorre porque os valores armazenados como texto não eram considerados na soma original das células numéricas.

Esses números são referências de auditoria do processo de preparação. Se for necessária uma reconciliação financeira formal, o próximo desenvolvedor deve comparar os dados originais, o relatório de validação e os valores efetivamente gravados no banco.

8.5 Duplicidades

Foram identificados sete pares de linhas que ficaram idênticas após a normalização. Esses registros foram mantidos, em vez de serem removidos automaticamente.

Essa foi uma decisão deliberada: duas linhas iguais não provam, por si só, que uma delas seja indevida. Elas podem representar ocorrências legítimas.

Não elimine esses registros sem validar o conteúdo original e o significado operacional de cada OS.

8.6 Arquivos de apoio da migração

Durante a preparação foram identificados os seguintes arquivos:

CONTROLE_OS_2026.xlsx — planilha de origem;

INSTRUCOES_importacao_OS_2026.txt — instruções e regras da importação;

importacao_ordens_servico_2026.sql — nome de referência para o script SQL de importação;

relatorio_validacao_importacao_os_2026.xlsx — relatório de conferência;

conferencia_ordens_servico_2026.csv — dados normalizados para conferência;

revisoes_importacao_os_2026.csv — casos que exigiam atenção.

A existência e a localização desses arquivos devem ser verificadas na pasta de trabalho ou na biblioteca do projeto. Eles não precisam estar instalados no servidor para o módulo funcionar.

8.7 Risco de reexecutar a importação

O script de importação foi descrito como um script de inserção que não apagava nem atualizava as OS existentes.

Isso significa que executar o mesmo script novamente pode inserir registros duplicados.

Regra obrigatória de manutenção: não reexecute a importação de 2026 para resolver problemas de código ou de interface.

Se houver uma suspeita de erro nos dados, primeiro faça uma auditoria por consulta SELECT, compare os registros com a planilha original e defina uma correção específica. Qualquer atualização ou exclusão em massa deve ser precedida de backup e revisão.

9. Histórico dos problemas e correções

Esta seção registra os principais problemas encontrados durante o desenvolvimento e a razão das soluções adotadas.

9.1 Inclusão do status Cancelado

Problema: o conjunto de status disponível não contemplava adequadamente o cancelamento de uma OS.

Solução: o status Cancelado foi incluído no módulo e confirmado como funcional pelo usuário.

Regra resultante: uma OS cancelada não deve ser classificada como atrasada nem como próxima do vencimento.

Pontos a revisar se o módulo mudar:

opções de status do formulário;

validação de status no controller;

gravação na criação e na edição;

apresentação na listagem;

filtros;

regras de aviso;

restrições de edição e exclusão.

9.2 OS concluída aparecendo como atrasada

Sintoma: algumas OS concluídas exibiam o aviso Atrasada.

Causa: as comparações de status não reconheciam todas as variantes de capitalização e acentuação utilizadas nos dados importados.

Solução: foram ajustadas verificações para reconhecer variantes de concluído e cancelado.

Lição técnica: o banco pode conter valores historicamente válidos, mas escritos em formatos diferentes. A regra de negócio não deve depender de uma única grafia literal sem normalização.

9.3 Filtro Dentro do prazo retornando OS próxima do vencimento

Sintoma: o filtro de OS dentro do prazo retornava registros que apareciam com o aviso Próxima do vencimento.

Causa: os critérios do filtro e da classificação visual não estavam completamente alinhados.

Solução definida: padronizar o prazo de proximidade em cinco dias e fazer o controller e a view utilizarem as mesmas fronteiras temporais.

Critério esperado:

hoje até hoje + 5 dias: próxima do vencimento;

depois de hoje + 5 dias: dentro do prazo;

antes de hoje: atrasada.

OS concluídas, canceladas, realizadas ou sem data prevista devem ser excluídas dos filtros de risco, conforme as regras desta documentação.

9.4 Status Em andamento permanecendo no filtro

Sintoma: a opção ainda aparecia no filtro de status depois de ter sido removida de outro local.

Solução: remover a opção do seletor da listagem.

Cuidados: se houver registros antigos com esse valor, a compatibilidade de exibição pode ser mantida separadamente. Não reintroduza o status no formulário sem uma decisão de negócio.

9.5 Nomes de técnicos e empresas não se atualizando nas OS antigas

Problema: nomes gravados somente como texto podem ficar desatualizados depois de uma alteração no cadastro mestre.

Solução: usar empresa_id e tecnico_id para relacionar a OS aos registros do Perfex.

Resultado esperado: quando o nome for exibido a partir do relacionamento pelo ID, a OS passa a refletir o nome atual cadastrado no Perfex.

9.6 Erros de estrutura do banco

Foram encontrados erros relacionados a tentativa de criar novamente uma coluna existente e a incompatibilidade de collation entre textos.

Esses problemas reforçam duas regras:

conferir a estrutura real do banco antes de executar alterações;

verificar a compatibilidade de collations ao fazer comparações entre colunas textuais.

Não se deve tentar resolver um erro de banco alterando todas as colunas ou todos os registros sem identificar primeiro a origem exata.

10. Procedimento seguro para futuras alterações

Esta seção é especialmente importante para o desenvolvedor que assumir o projeto.

10.1 Antes de modificar o código

Acesse o módulo instalado no ambiente correto.

Confira quais versões dos arquivos estão efetivamente em produção.

Leia o controller e as views antes de alterar a lógica.

Faça uma cópia de segurança dos arquivos que serão modificados.

Se a alteração envolver o banco, faça backup do banco.

Identifique todas as partes que dependem da regra que será alterada.

Registre a mudança e os testes necessários.

Não se deve presumir que um arquivo baixado anteriormente seja idêntico ao que está instalado no servidor.

10.2 Se for alterar os avisos de vencimento

Uma alteração na quantidade de dias deve ser aplicada em conjunto na view e no controller.

Por exemplo, se a empresa decidir futuramente adotar sete dias em vez de cinco, será necessário revisar:

a lógica que exibe Atrasada;

a lógica que exibe Próxima do vencimento;

a condição do filtro atrasada;

a condição do filtro proxima;

a condição do filtro dentro_prazo;

a exclusão de OS concluídas, canceladas e realizadas;

o tratamento de datas vazias.

Depois, teste pelo menos as datas de hoje, hoje + 5 dias, hoje + 6 dias e a nova fronteira.

Não altere somente o texto de um aviso ou somente o valor usado em uma consulta SQL. Isso pode fazer a interface e os filtros discordarem novamente.

10.3 Se for adicionar um novo status

A inclusão de um status não se limita a acrescentar uma opção ao formulário. O desenvolvedor precisa revisar:

lista de status disponíveis;

validação no controller;

criação e edição;

transições automáticas;

exibição na tabela;

filtros de status;

filtros e avisos de vencimento;

permissões de edição e exclusão;

compatibilidade com registros já existentes;

documentação.

Também deve decidir se o novo status é operacional ou terminal. Um status terminal, como Concluída ou Cancelado, não deve receber alertas de atraso conforme as regras atuais.

10.4 Se for alterar as permissões

Revise a definição da permissão no arquivo principal e todas as verificações no controller e na interface.

O teste deve incluir tanto o uso normal da tela quanto uma tentativa de acesso direto à URL da ação. O objetivo é garantir que um usuário sem permissão não consiga realizar uma operação por fora da interface.

10.5 Se for alterar o banco de dados

Utilize db_prefix() nas consultas PHP.

Inspecione as colunas e os índices existentes.

Evite scripts que falhem ou causem duplicidade quando executados duas vezes.

Não remova campos legados sem verificar suas dependências.

Não execute novamente o script histórico de importação.

Para correções em massa, faça primeiro um SELECT que mostre exatamente os registros afetados.

Guarde o SQL utilizado e o resultado da validação.

Sempre que uma alteração puder afetar os registros existentes, a primeira opção deve ser testar em homologação, não diretamente em produção.

11. Roteiro de diagnóstico
Problema: erro 500 ao abrir Ordens de Serviço

Verifique:

se o arquivo foi copiado integralmente;

se o controller está no diretório correto;

se o nome do arquivo e o nome da classe correspondem à convenção do módulo;

se houve mistura de arquivos de versões diferentes;

se há erro de sintaxe PHP;

se o log do Perfex/PHP mostra uma exceção ou erro SQL.

A validação de sintaxe pode ser feita com:

php -l caminho/para/manage.php
php -l caminho/para/Ordens_servico.php

O resultado esperado é No syntax errors detected.

Esse teste não garante que a consulta SQL funcionará nem que a tela se comportará corretamente.

Problema: OS concluída aparece como atrasada

Verifique:

o valor exato do status armazenado;

se a data realizada está preenchida;

se a view reconhece as variantes de concluído;

se o controller exclui status concluídos/cancelados dos filtros de aviso;

se os arquivos instalados correspondem à mesma versão de correção.

Problema: o filtro Dentro do prazo traz OS próxima do vencimento

Confira a data prevista e compare-a com a data atual do servidor.

Depois, revise o cálculo de limite no controller e a classificação na view. A regra deve ser idêntica nos dois pontos: até cinco dias, inclusive, a OS é próxima do vencimento; a partir de seis dias, entra em dentro do prazo.

Problema: o nome do técnico não é atualizado

Confira se:

tecnico_id está preenchido;

o ID aponta para o funcionário correto;

o funcionário continua cadastrado;

a view utiliza o nome resolvido pelo ID;

a tela não está exibindo somente o campo textual legado tecnico.

A mesma lógica se aplica ao relacionamento com empresas por empresa_id.

Problema: o técnico não vê uma OS

Confira:

o tecnico_id registrado na OS;

o ID do funcionário conectado;

o status ativo do funcionário;

o papel cadastrado;

a condição de acesso do controller;

a regra de compatibilidade para registros antigos sem tecnico_id.

Problema: o filtro de status não retorna uma OS importada

Compare o valor armazenado com as opções permitidas no filtro. Observe acentuação e capitalização. Se necessário, ajuste o tratamento de variantes no controller, sem alterar os dados em massa antes de uma auditoria.

12. Plano de testes de regressão

Qualquer alteração no módulo deve ser validada para garantir que uma correção pontual não prejudique funcionalidades que já estavam funcionando.

12.1 Testes de status e avisos

Considere a data atual do ambiente como referência.

Caso de teste

	

Resultado esperado




OS ativa com data prevista ontem

	

Aviso Atrasada.




OS ativa com data prevista hoje

	

Aviso Próxima do vencimento.




OS ativa com data prevista daqui a 3 dias

	

Aviso Próxima do vencimento.




OS ativa com data prevista daqui a 5 dias

	

Aviso Próxima do vencimento.




OS ativa com data prevista daqui a 6 dias

	

Sem aviso de risco; deve entrar no filtro Dentro do prazo.




OS sem data prevista

	

Sem aviso; não entra nos filtros de risco.




OS concluída com status Concluída

	

Sem aviso de risco.




OS concluída com status concluído

	

Sem aviso de risco.




OS cancelada com status Cancelado

	

Sem aviso de risco.




OS cancelada com status cancelado

	

Sem aviso de risco.




OS com data realizada preenchida

	

Não deve aparecer nos filtros de risco.

O resultado da coluna de aviso deve ser coerente com o filtro aplicado.

12.2 Testes de funcionalidades
A listagem abre sem erro.
Criar OS funciona.
Editar OS funciona conforme as permissões.
Excluir OS funciona conforme as permissões.
A empresa é selecionada entre os clientes ativos.
O técnico é selecionado entre os funcionários ativos e papéis permitidos.
A mudança do nome no cadastro mestre é refletida quando a OS usa o ID.
Os status válidos são Aberto, Pendente, Concluída e Cancelado.
Em andamento não aparece como opção de status.
A data realizada no futuro é recusada.
Os filtros de empresa, técnico, status e datas funcionam.
O filtro Atrasada não inclui OS concluídas ou canceladas.
O filtro Próxima do vencimento respeita a janela de cinco dias.
O filtro Dentro do prazo não inclui OS atrasadas ou próximas do vencimento.
Usuários sem permissão não conseguem contornar as regras acessando URLs diretamente.
A versão instalada passou pela validação de sintaxe.
A migração não foi executada novamente.

Sempre que possível, os testes de criação, edição, exclusão e alteração de status devem ser feitos em ambiente de homologação ou com registros de teste controlados.

13. Melhorias técnicas recomendadas para o futuro

As recomendações a seguir são melhorias possíveis, não funcionalidades que devem ser consideradas já implementadas.

13.1 Centralizar a normalização de status

Atualmente, as verificações de status podem estar distribuídas entre controller e view.

Uma melhoria seria criar métodos auxiliares para:

normalizar o status;

identificar se uma OS está concluída;

identificar se uma OS está cancelada;

identificar se uma OS deve receber aviso;

determinar a categoria do aviso.

Isso reduz o risco de a listagem e os filtros implementarem regras diferentes.

13.2 Centralizar a regra de vencimento

A classificação de atraso e proximidade pode ser extraída para uma função reutilizável, desde que a arquitetura e a versão do Perfex permitam essa organização sem efeitos colaterais.

O objetivo é que a lógica não seja duplicada em diferentes arquivos. Uma refatoração dessa natureza deve ser feita em uma alteração separada, com testes, e não misturada a uma correção urgente.

13.3 Criar testes automatizados

Os casos de fronteira dos cinco dias podem ser automatizados para evitar regressões.

Os testes mais importantes são:

hoje;

hoje + 5 dias;

hoje + 6 dias;

ontem;

data vazia;

OS concluída;

OS cancelada;

OS realizada.

Isso é particularmente útil porque pequenos ajustes em datas e condições SQL podem produzir erros que só aparecem em dias específicos.

13.4 Padronizar os valores de status

No futuro, pode ser útil definir uma representação interna única para cada status e separar essa representação do rótulo exibido ao usuário.

Essa mudança deve ser planejada com cuidado, pois existem dados importados e registros históricos. Não se deve fazer uma atualização geral no banco sem identificar todas as dependências.

13.5 Registrar o histórico de alterações

Para evoluir o projeto com segurança, mantenha um registro de:

data da alteração;

arquivos modificados;

motivo;

regra de negócio afetada;

testes realizados;

resultado da validação;

eventual script SQL executado.

O uso de Git, com commits pequenos e descritivos, é recomendável para rastrear as mudanças e facilitar reversões.

14. Histórico do projeto

Data

	

Evento




Setembro–outubro de 2026

	

Desenvolvimento e ajustes do módulo de Ordens de Serviço para Perfex CRM 3.4.1.




Durante o desenvolvimento

	

Integração das empresas e técnicos com os cadastros nativos do Perfex, utilizando IDs.




Antes da migração

	

Preparação e validação da importação da aba 2026 da planilha.




16/10/2026

	

Migração das OS de 2026 confirmada pelo usuário como concluída com sucesso.




16–18/10/2026

	

Inclusão e confirmação funcional do status Cancelado.




18/10/2026

	

Correções para que OS concluídas/canceladas não recebam avisos incorretos.




18/10/2026

	

Ajustes no filtro Dentro do prazo e definição de cinco dias para o aviso Próxima do vencimento.

15. Resumo para o desenvolvedor que assumir o projeto

O módulo de Ordens de Serviço é uma extensão personalizada do Perfex CRM 3.4.1. Ele mantém os dados operacionais em uma tabela própria e utiliza os clientes e funcionários do Perfex como cadastros mestres.

Os IDs empresa_id e tecnico_id são importantes para manter os relacionamentos corretos. Não substitua essa abordagem por comparação de nomes sem uma justificativa específica.

A migração importou somente a aba de 2026, com 1.806 OS como volume de referência. Os 377 valores textuais identificados como monetários foram convertidos após confirmação, os textos não monetários foram preservados para conferência, quatro valores negativos foram separados para revisão e sete pares de linhas duplicadas após normalização foram mantidos. A migração foi confirmada como concluída e não deve ser repetida para corrigir problemas da interface.

Os status vigentes são Aberto, Pendente, Concluída e Cancelado. Os dados importados podem apresentar diferenças de caixa e acentuação, por isso as verificações do código precisam reconhecer as variantes relevantes.

A regra de avisos vigente é:

data anterior a hoje: Atrasada;

data de hoje até hoje + 5 dias: Próxima do vencimento;

data posterior a hoje + 5 dias: entra no filtro Dentro do prazo;

OS concluídas, canceladas, realizadas ou sem data prevista: não devem receber avisos de risco.

Antes de qualquer modificação, confira os arquivos efetivamente instalados, faça backup, revise controller e view em conjunto e execute os testes de regressão. A validação de sintaxe PHP é necessária, mas não substitui a validação funcional dentro do Perfex.

Regra final de manutenção: preservar o que já funciona, mudar uma regra de cada vez, testar o comportamento real e documentar cada alteração para que o próximo desenvolvedor consiga continuar o projeto sem precisar reconstruir suas decisões técnicas do zero.