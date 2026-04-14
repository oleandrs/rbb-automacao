# RBB Automacao - Site institucional

Estrutura pronta para publicacao em hospedagem tradicional.

## Arquivos principais
- `index.html` -> pagina principal (Home, Sobre Nos, Servicos)
- `projetos.html` -> pagina em manutencao
- `treinamentos.html` -> pagina em manutencao
- `fale-conosco.html` -> pagina de contato
- `trabalhe-aqui.html` -> pagina de recrutamento
- `send-contact.php` -> processamento do formulario de contato (SMTP autenticado)
- `send-careers.php` -> processamento do formulario Trabalhe Conosco (SMTP autenticado)
- `smtp-mailer.php` -> cliente SMTP compartilhado
- `obrigado.html` -> pagina de retorno apos envio

## Dados configurados
- E-mail comercial exibido: `contato@rbbautomacao.com.br`
- E-mail de destino dos formularios: `contato@rbbautomacao.com.br`
- Telefone/WhatsApp: `+55 21 99386-0628`
- LinkedIn: `https://www.linkedin.com/in/leandro-santos-10831b141/`
- Instagram: `https://www.instagram.com/accounts/onetap/`
- WhatsApp link: `https://wa.me/21993860628`

## Publicacao
1. Envie os arquivos para `public_html`.
2. Mantenha a estrutura da pasta `assets`.
3. Garanta que o PHP esteja habilitado.
4. Configure SMTP no servidor antes de testar os formularios.

## Configuracao SMTP
Os formularios `send-contact.php` e `send-careers.php` usam SMTP autenticado.

Opcao 1 (recomendada): variaveis de ambiente
- `SMTP_HOST`
- `SMTP_PORT` (ex.: `587`)
- `SMTP_USERNAME`
- `SMTP_PASSWORD`
- `SMTP_ENCRYPTION` (`tls`, `ssl` ou `none`)
- `SMTP_FROM_EMAIL` (opcional; padrao = `SMTP_USERNAME`)
- `SMTP_FROM_NAME` (opcional)
- `SMTP_TIMEOUT` (opcional; padrao = `15`)

Opcao 2: arquivo local fora do Git
1. Copie `smtp-config.local.example.php` para `smtp-config.local.php`.
2. Preencha os dados SMTP no arquivo local.
3. Nao commite `smtp-config.local.php` (ja listado no `.gitignore`).

## Personalizacoes rapidas
- Textos: edite os arquivos `.html`
- Cores e layout: edite os arquivos em `assets/css`
- Comportamentos: edite `assets/js/main.js`
- Destino dos formularios: altere `send-contact.php` e `send-careers.php`

