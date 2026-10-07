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
