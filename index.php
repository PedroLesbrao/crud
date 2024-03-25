<!-- CRUD: Create (criar), Read (ler), Update (atualizar) e Delete (Apagar) -->
<?php
    //Instanciando a classe de conexão mysqli
    //Parâmetros: ip do servidor,usuario,senha,nome banco
    //localhost ou 127.0.0.1
    $obj_mysqli = new mysqli("localhost","root","","tutocrudphp");
    //verificar se não houve algum erro na conexão
    //A seta significa a execução de um método da classe instanciada
    if($obj_mysqli->connect_errno){
        echo "Ocorreu um erro de conexão com o banco de dados";
        //encerra a execução do script
        exit;
    }
    //configurar o formato padrão
    //utf8 representa caracteres padrão UNICODE compatível com o ASCII;
    mysqli_set_charset($obj_mysqli,'utf8');

    //Como vamos utilizar o mesmo formulário para tudo, vamos colocar
    //as variáveis como globais
    $id     = -1;
    $nome   = "";
    $email  = "";
    $cidade = "";
    $uf     = "";


    //Validando a existência dos dados. A função isset retorna
    // TRUE se a variável existir
    //As variáveis $_POST somente existirão depois que o usuário
    //clicar no botão.
    if(isset($_POST["nome"]) && isset($_POST["email"]) &&
       isset($_POST["cidade"]) && isset($_POST["uf"])){
        //verificando se o nome não está vazio
        if(empty($_POST["nome"])){
            $erro = "O preenchimento do campo NOME é obrigatório.";
        }else if(empty($_POST["email"])){
            $erro = "O preenchimento do campo E-MAIL é obrigatório.";
        }else{
            //Realizando o cadastro
            $id     = $_POST["id"];
            $nome   = $_POST["nome"];
            $email  = $_POST["email"];
            $cidade = $_POST["cidade"];
            $uf     = $_POST["uf"];

            //Se o id for igual a -1, significa que queremos realizar um cadastro
            if($id==-1){
                //Preparando o MySQL para a execução da query (linha de comando
                //do sql). Statement
                $sql = "INSERT INTO cliente (nome,email,cidade,uf) VALUES (?,?,?,?)";
                $stmt =  $obj_mysqli->prepare($sql);
                //Preparando as variáveis para serem passadas como parâmetro para query.
                //ssss = String,String,String,String  (i-inteiro,s-string,d-double/float,b-objeto)
                $stmt->bind_param('ssss',$nome,$email,$cidade,$uf);
                //executar a query
                if(!$stmt->execute()){
                    $erro = $stmt->error;
                }else{
                    header("Location:index.php");
                    exit;
                }
            //Se o id for maior ou igual a 1, significa alteração de dados
            }else if(is_numeric($id) && $id>=1){
                //Em algumas literaturas, encontramos os campos circundados com crase
                //mas, ela é opcional para o mysqli. Abaixo vemos como seria com crase.
                $sql = "UPDATE `cliente` SET `nome`=?, `email`=?,
                       `cidade`=?, `uf`=? WHERE id=?";
                $stmt = $obj_mysqli->prepare($sql);
                $stmt->bind_param('ssssi',$nome,$email,$cidade,$uf,$id);
                if(!$stmt->execute()){
                    $erro = $stmt->error;
                }else{
                    header("Location:index.php");
                    exit;
                }
            }else{
                $erro = "Número Inválido";
            }
        }
    }else if(isset($_GET["id"]) && is_numeric($_GET["id"])){
        $id = $_GET["id"];
        $sql = "SELECT * FROM cliente WHERE id=?";
        $stmt = $obj_mysqli->prepare($sql);
        $stmt->bind_param('i',$id);
        $stmt->execute();
        //O retorno da pesquisa será colocado numa variável
        $result=$stmt->get_result();
        //Converte o resultado num array
        $aux = $result->fetch_assoc();
        //Atribuimos às variáves o resultado da busca
        $nome   = $aux["nome"];
        $email  = $aux["email"];
        $cidade = $aux["cidade"];
        $uf     = $aux["uf"];
        //fechando o statement
        $stmt->close();
    }
?>


<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Introdução ao PHP</title>
        <link type="text/css" rel="stylesheet" href="estilo.css">
    </head>
    <body>
        <?php
            if(isset($erro)){
                echo "<div style='color:#F00'>".$erro."</div><br><br>";
            }else if(isset($sucesso)){
                echo "<div style='color:#00F'>".$sucesso."</div><br><br>";
            }
        ?>
        <h1>CT Desenvolvimento de Sistemas - Back-End</h1>
        <div id="container">
            <!-- $_SERVER traz diversas informações dentro de um array.
                 PHP_SELF é o nome do arquivo que está sendo executado. Neste caso
                 é a própria página index.php
                 https://www.php.net/manual/pt_BR/reserved.variables.server.php -->
            <form method="POST" action="<?=$_SERVER["PHP_SELF"]?>">
                Nome: <input type="text" name="nome" placeholder="Qual seu nome?" size=100 value="<?=$nome?>"/><br>
                E-mail: <input type="text" name="email" placeholder="Qual seu e-mail?" size=100 value="<?=$email?>"/><br>
                Cidade: <input type="text" name="cidade" placeholder="Qual sua cidade?" size=100 value="<?=$cidade?>"/><br>
                Estado: <input type="text" name="uf" placeholder="UF" size=2 maxlength=2 value="<?=$uf?>"/><br><br>
                <input type="hidden" value="<?=$id?>" name="id">
                <button type="submit"><?=($id==-1)?"CADASTRAR":"SALVAR"?></button>
            </form>
            <br><br>
            <table width="400px" border="1" cellspacing="0">
                <tr>
                    <td><strong>ID</strong></td>
                    <td><strong>NOME</strong></td>
                    <td><strong>EMAIL</strong></td>
                    <td><strong>CIDADE</strong></td>
                    <td><strong>UF</strong></td>
                    <td><strong>#</strong></td>
                </tr>
                <?php
                    $result = $obj_mysqli->query("SELECT * FROM cliente");
                    //fetch_assoc converte em array
                    while($aux = $result->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>".$aux["id"]."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                        echo "<td>".$aux["nome"]."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                        echo "<td>".$aux["email"]."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                        echo "<td>".$aux["cidade"]."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                        echo "<td>".$aux["uf"]."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                        echo "<td><a href='".$_SERVER["PHP_SELF"]."?id=".$aux["id"].
                             "'>Editar</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
                        echo "</tr>";
                    }
                ?>

            </table>
        </div>
    </body>
</html>
