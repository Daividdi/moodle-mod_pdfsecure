# Moodle PDF Secure Viewer (mod_pdfsecure)

![Moodle Version](https://img.shields.io/badge/Moodle-3.9+-orange.svg)
![License](https://img.shields.io/badge/License-GPLv3-blue.svg)

**Author:** Daividdi

---

## 🇺🇸 English Description

**PDF Secure Viewer** is a custom Moodle Activity Module designed to display PDF files with an aggressive layer of Digital Rights Management (DRM) directly in the browser, without requiring third-party APIs or external software.

Built on top of Mozilla's PDF.js, this plugin ensures that your educational materials are heavily protected against unauthorized distribution and data leaks.

### 🛡️ Key Security Features:
* **Psychological DRM (Dynamic Watermark):** Automatically stamps the logged-in user's full name, current date, and time repeatedly across the document. Identifies the source of any physical photo taken from the screen.
* **Anti-Download & Anti-Print:** Natively destroys the download and print buttons from the PDF.js interface.
* **Global Print Blackout:** Uses strict CSS `@media print` rules to render a blank page if the user attempts to force browser printing (Ctrl+P).
* **Smart Anti-Snipping Tool (Focus-Loss Blur):** The document instantly disappears (opacity: 0) if the browser window loses focus (e.g., when the user tries to open the Windows Snipping Tool or switches to another application).
* **Keyboard & Mouse Shield:** Disables right-click (context menu), text selection, and shortcut keys (Ctrl+P, Ctrl+S) globally on the viewing page.
* **Fullscreen & Presentation Mode Block:** Disables PDF.js presentation mode and blocks the F11 key to prevent users from expanding the PDF and bypassing the watermark layer.

### ⚙️ Installation
1. Clone or download this repository.
2. Extract the folder and rename it to `pdfsecure`.
3. Place the `pdfsecure` folder into your Moodle's `/mod/` directory (`yourmoodle/mod/pdfsecure`).
4. Log in to your Moodle site as an admin and proceed with the standard plugin upgrade process.

---

## 🇧🇷 Descrição em Português

**PDF Secure Viewer** é um Módulo de Atividade customizado para o Moodle, desenvolvido para exibir arquivos PDF com uma camada agressiva de Gestão de Direitos Digitais (DRM) diretamente no navegador, sem a necessidade de APIs de terceiros ou softwares externos.

Construído sobre o PDF.js da Mozilla, este plugin garante que seus materiais educacionais sejam fortemente protegidos contra distribuição não autorizada e vazamento de dados (Data Leaks).

### 🛡️ Principais Funcionalidades de Segurança:
* **DRM Psicológico (Marca D'água Dinâmica):** Estampa automaticamente o nome completo do usuário logado, data e hora atuais repetidamente sobre o documento. Identifica a origem de qualquer foto física tirada da tela.
* **Anti-Download e Anti-Impressão:** Destrói nativamente os botões de baixar e imprimir da interface do PDF.js.
* **Apagão Global de Impressão:** Utiliza regras rígidas de CSS `@media print` para renderizar uma página em branco caso o usuário tente forçar a impressão pelo navegador (Ctrl+P).
* **Anti-Captura Inteligente (Blur por Perda de Foco):** O documento desaparece instantaneamente (opacidade: 0) se a janela do navegador perder o foco (ex: quando o aluno tenta abrir a Ferramenta de Captura do Windows ou muda de aplicativo).
* **Escudo de Teclado e Mouse:** Desabilita o clique com o botão direito (menu de contexto), a seleção de texto e as teclas de atalho (Ctrl+P, Ctrl+S) globalmente na página de visualização.
* **Bloqueio de Tela Cheia e Modo Apresentação:** Desativa o modo de apresentação do PDF.js e bloqueia a tecla F11 para impedir que os usuários expandam o PDF e contornem a camada da marca d'água.

### ⚙️ Instalação
1. Clone ou baixe este repositório.
2. Extraia a pasta e renomeie-a para `pdfsecure`.
3. Coloque a pasta `pdfsecure` dentro do diretório `/mod/` do seu Moodle (`seumoodle/mod/pdfsecure`).
4. Faça login no Moodle como administrador e prossiga com o processo padrão de atualização de plugins.
