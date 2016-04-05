## Procolo-CeIMBe
Projeto criado com o HTR Firebird Framework 2.3
Sistema de protocolo de documentos usado na Divisão de Obtenção e Divisão de Execução Financeira do CeIMBe (Centro de Intendência da Marinha em Belém)

Este repositório contém apenas os arquivos da regra de negócio da aplicação.

### Instalação
Para rodar a aplicação, é necessário ter o servidor HTTP Apache 2 instalado e configurado com os módulos de `rewrite`

Para habilitar o módulo no Apache basta esta linha:
```bash
      sudo a2enmod rewrite
```

Agora abra o arquivo de configuração:
```bash
      sudo gedit  /etc/apache2/sites-available/default
```

Procure no seu arquivo a entrada **AllowOverride None**, no meu caso estava na linha 11.

Altere esse valor para **AllowOverride All** .
Salve o arquivo e reinicie o Apache.
```bash
      sudo /etc/init.d/apache2 restart
```

Após habilitar o módulo, copie o diretório `protocolo` para `/var/www/html/` e o diretório `app.protoclo` para `/var/www/`

### Acesso
Para acessar a aplicação a aplicação basta digitar o endereço no navegador
**http://www.seudominio.com.br/protocolo**

### Créditos
Esta aplicação foi desenvolvida por [Bruno Monteiro](bruno.monteirodg@gmail.com) com o [HTR Firebird Framework](https://github.com/br-monteiro/HTR-Firebird-Framework) versão 2.3 sob a licença GNU GENERAL PUBLIC LICENSE - Version 3.

### LAUS DEO
