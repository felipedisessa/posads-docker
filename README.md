# Exercicio Pratico - Docker na AWS (EC2 + Git + Persistencia)

Projeto da atividade pratica com Apache + PHP + MySQL no mesmo container, reutilizando a imagem da Aula 07 (`felipetimds/contatos-apache-php-mysql:1.0`) no `Dockerfile` e trazendo os arquivos da aplicacao via Git durante o build.

## Arquitetura

- Apache para servir a aplicacao PHP.
- PHP para o formulario de cadastro de contatos.
- MySQL no mesmo container para armazenar os dados.
- Persistencia aplicada somente ao diretorio `/var/lib/mysql`.
- Arquivos da aplicacao embutidos na imagem por meio de `git clone`, sem volume para PHP/SQL.
- Reaproveitamento da imagem da Aula 07 em um estagio `aula07_base`, mantendo fidelidade ao enunciado e removendo a persistencia da aplicacao no runtime final.

## Repositorio Git utilizado

- GitHub: [https://github.com/felipedisessa/posads-docker](https://github.com/felipedisessa/posads-docker)

## Arquivos obrigatorios

- `cadastro_contatos.php`
- `banco_contatos.sql`
- `Dockerfile`

## Aplicacao

- Cadastro de nome e telefone.
- Validacao do telefone no formato `(xx) x xxxx-xxxx`.
- Listagem dos contatos ja cadastrados.

## Build da imagem

```bash
docker build \
  --build-arg BASE_IMAGE=felipetimds/contatos-apache-php-mysql:1.0 \
  -t felipetimds/contatos-apache-php-mysql:2.0 .
```

## Publicacao no Docker Hub

```bash
docker login
docker push felipetimds/contatos-apache-php-mysql:2.0
```

Repositorio base no Docker Hub:

- [https://hub.docker.com/r/felipetimds/contatos-apache-php-mysql](https://hub.docker.com/r/felipetimds/contatos-apache-php-mysql)

## Execucao na EC2 Ubuntu

Abra a porta `80` no Security Group da instancia e instale o Docker na EC2 Ubuntu usando o repositorio oficial da Docker.

Comandos de instalacao do Docker na EC2 Ubuntu, conforme a documentacao oficial da Docker:

```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo systemctl enable docker
sudo systemctl start docker
```

### Comando completo `docker run`

```bash
docker run -d \
  -p 80:80 \
  -v /home/ubuntu/mysql_data:/var/lib/mysql \
  --name meu_container \
  felipetimds/contatos-apache-php-mysql:2.0
```

## URL de acesso na EC2

- Acesso esperado: `http://IP_PUBLICO_DA_EC2/cadastro_contatos.php`
- Se desejar manter exatamente o enunciado, tambem pode acessar `http://IP_PUBLICO_DA_EC2`

## Persistencia do MySQL

- A persistencia foi aplicada somente ao MySQL com `-v /home/ubuntu/mysql_data:/var/lib/mysql`.
- Os arquivos `cadastro_contatos.php` e `banco_contatos.sql` ficam dentro da imagem e sao copiados para `/var/www/html` durante o build.
- Nao ha volume para os arquivos PHP/SQL, atendendo a mudanca pedida na atividade.
- O banco usado pela aplicacao e `agenda`, com usuario `agenda_user` e senha `agenda123`, herdando o padrao da imagem da Aula 07.

## Passo a passo sugerido para entrega

1. Fazer `git add .`, `git commit` e `git push` para atualizar o repositiorio GitHub.
2. Fazer o `docker build` da imagem com a base da Aula 07.
3. Fazer `docker push` da imagem `felipetimds/contatos-apache-php-mysql:2.0`.
4. Na EC2 Ubuntu, executar o `docker run` acima.
5. Testar no navegador usando o IP publico da instancia.

## Observacao importante

O `Dockerfile` reutiliza a imagem `felipetimds/contatos-apache-php-mysql:1.0` em um estagio inicial. Essa escolha foi feita porque a imagem original publica `/var/www/html` como volume; por isso, o runtime final foi remontado para cumprir o requisito novo da atividade, que pede persistencia apenas do MySQL.
