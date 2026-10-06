<?php
        $servidor="localhost";
        $usuario="root";
        $senha="";
        $dbname="nexus network";

        $conn=mysqli_connect($servidor, $usuario, $senha, $dbname);

        if (!$conn) {
            die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
        }

        // Garante que acentos sejam gravados/lidos corretamente
        mysqli_set_charset($conn, "utf8mb4");
?>