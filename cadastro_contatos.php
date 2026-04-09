<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USER') ?: 'agenda_user';
$dbPass = getenv('DB_PASSWORD') ?: 'agenda123';
$dbName = getenv('DB_NAME') ?: 'agenda';
$mensagem = '';
$erro = '';
$telefoneRegex = '/^\(\d{2}\) \d \d{4}-\d{4}$/';

try {
    $conexao = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $conexao->set_charset('utf8mb4');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim((string) filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW));
        $telefone = trim((string) filter_input(INPUT_POST, 'telefone', FILTER_UNSAFE_RAW));

        if ($nome === '') {
            $erro = 'Informe o nome do contato.';
        } elseif (!preg_match($telefoneRegex, $telefone)) {
            $erro = 'Informe o telefone no formato (xx) x xxxx-xxxx.';
        } else {
            $stmt = $conexao->prepare('INSERT INTO contatos (nome, telefone) VALUES (?, ?)');
            $stmt->bind_param('ss', $nome, $telefone);
            $stmt->execute();
            $stmt->close();
            $mensagem = 'Contato cadastrado com sucesso.';
        }
    }

    $resultado = $conexao->query('SELECT id, nome, telefone, criado_em FROM contatos ORDER BY id DESC');
    $contatos = $resultado->fetch_all(MYSQLI_ASSOC);
    $resultado->close();
    $conexao->close();
} catch (Throwable $exception) {
    $erro = 'Nao foi possivel acessar o MySQL. Verifique se o container iniciou corretamente.';
    $contatos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Contatos</title>
    <style>
        :root {
            color-scheme: light;
            --fundo: #f3f4f6;
            --painel: #ffffff;
            --borda: #d1d5db;
            --texto: #111827;
            --sucesso: #166534;
            --erro: #991b1b;
            --destaque: #0f766e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #e5e7eb 0%, #f8fafc 100%);
            color: var(--texto);
        }

        .container {
            width: min(960px, calc(100% - 32px));
            margin: 40px auto;
            display: grid;
            gap: 24px;
        }

        .painel {
            background: var(--painel);
            border: 1px solid var(--borda);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        h1, h2 {
            margin-top: 0;
        }

        form {
            display: grid;
            gap: 16px;
        }

        label {
            display: grid;
            gap: 8px;
            font-weight: 600;
        }

        input {
            width: 100%;
            border: 1px solid var(--borda);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 1rem;
        }

        button {
            width: fit-content;
            border: 0;
            border-radius: 10px;
            padding: 12px 20px;
            background: var(--destaque);
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }

        .mensagem,
        .erro {
            padding: 12px 14px;
            border-radius: 10px;
            font-weight: 600;
        }

        .mensagem {
            background: #dcfce7;
            color: var(--sucesso);
        }

        .erro {
            background: #fee2e2;
            color: var(--erro);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        thead {
            background: #f9fafb;
        }

        .ajuda {
            margin: 0;
            color: #4b5563;
        }

        @media (max-width: 640px) {
            .container {
                width: min(100% - 20px, 960px);
                margin: 20px auto;
            }

            .painel {
                padding: 18px;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tbody tr {
                border: 1px solid var(--borda);
                border-radius: 12px;
                margin-bottom: 12px;
                padding: 8px;
            }

            td {
                border: 0;
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <section class="painel">
            <h1>Cadastro de Contatos</h1>
            <p class="ajuda">Preencha nome e telefone no formato (xx) x xxxx-xxxx.</p>

            <?php if ($mensagem !== ''): ?>
                <p class="mensagem"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if ($erro !== ''): ?>
                <p class="erro"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form method="post" action="">
                <label for="nome">
                    Nome
                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        maxlength="120"
                        required
                        placeholder="Digite o nome do contato"
                    >
                </label>

                <label for="telefone">
                    Telefone
                    <input
                        type="text"
                        id="telefone"
                        name="telefone"
                        maxlength="16"
                        inputmode="numeric"
                        pattern="\(\d{2}\) \d \d{4}-\d{4}"
                        required
                        placeholder="(11) 9 1234-5678"
                    >
                </label>

                <button type="submit">Salvar contato</button>
            </form>
        </section>

        <section class="painel">
            <h2>Contatos cadastrados</h2>
            <?php if (count($contatos) === 0): ?>
                <p class="ajuda">Nenhum contato cadastrado ate o momento.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>Data de cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contatos as $contato): ?>
                            <tr>
                                <td><?= (int) $contato['id'] ?></td>
                                <td><?= htmlspecialchars($contato['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($contato['telefone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($contato['criado_em'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <script>
        const campoTelefone = document.getElementById('telefone');

        campoTelefone.addEventListener('input', (event) => {
            const numeros = event.target.value.replace(/\D/g, '').slice(0, 11);
            let formatado = '';

            if (numeros.length > 0) {
                formatado = `(${numeros.slice(0, 2)}`;
            }

            if (numeros.length >= 3) {
                formatado += `) ${numeros.slice(2, 3)}`;
            }

            if (numeros.length >= 4) {
                formatado += ` ${numeros.slice(3, 7)}`;
            }

            if (numeros.length >= 8) {
                formatado += `-${numeros.slice(7, 11)}`;
            }

            event.target.value = formatado;
        });
    </script>
</body>
</html>
