# RBB Automação - Site institucional

Estrutura pronta para publicação em hospedagem tradicional, incluindo HostGator.

## Arquivos principais
- `index.html` -> página principal (Home, Sobre Nós, Serviços)
- `projetos.html` -> página em manutenção
- `treinamentos.html` -> página em manutenção
- `fale-conosco.html` -> página de contato
- `trabalhe-aqui.html` -> página de recrutamento
- `send-contact.php` -> processamento do formulário de contato
- `send-careers.php` -> processamento do formulário Trabalhe Conosco
- `obrigado.html` -> página de retorno após envio

## Dados já configurados
- E-mail comercial exibido: `contato@rbbautomacao.com.br`
- E-mail de recebimento dos formulários: `contato@rbbautomacao.com.br`
- Telefone/WhatsApp: `+55 21 99386-0628`
- LinkedIn: `https://www.linkedin.com/in/leandro-santos-10831b141/`
- Instagram: `https://www.instagram.com/accounts/onetap/`
- WhatsApp link: `https://wa.me/21993860628`

## Como publicar na HostGator
1. Compacte ou envie todos os arquivos desta pasta para `public_html`.
2. Mantenha a estrutura das pastas `assets`.
3. Verifique se o PHP está habilitado no plano.
4. Faça um teste real dos formulários após a publicação.

## Atenção sobre os formulários
Os formulários usam `mail()` do PHP. Em alguns ambientes isso pode exigir ajuste no servidor. Se preferir mais confiabilidade depois, o recomendado é trocar por SMTP autenticado.

## Personalizações rápidas
- Textos: edite os arquivos `.html`
- Cores e layout: edite os arquivos em `assets/css`
- Comportamentos: edite `assets/js/main.js`
- E-mail de destino dos formulários: altere `send-contact.php` e `send-careers.php`
