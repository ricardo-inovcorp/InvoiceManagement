<?php

require __DIR__.'/../vendor/autoload.php';

// Carregar o ambiente
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Importar a fachada DB
use Illuminate\Support\Facades\DB;

// Obter conexão com o banco de dados
$connection = DB::connection();
$pdo = $connection->getPdo();

// Nome do arquivo de backup
$backupPath = __DIR__ . '/../backups';
if (!file_exists($backupPath)) {
    mkdir($backupPath, 0755, true);
}

$backupFile = $backupPath . '/invoice_management_backup_' . date('Y-m-d_His') . '.sql';
$file = fopen($backupFile, 'w');

// Obter todas as tabelas do banco de dados
$tables = [];
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

// Escrever cabeçalho do arquivo SQL
fwrite($file, "-- Database Backup for invoice_management\n");
fwrite($file, "-- Generated on " . date('Y-m-d H:i:s') . "\n\n");
fwrite($file, "SET FOREIGN_KEY_CHECKS=0;\n\n");

// Fazer backup de cada tabela
foreach ($tables as $table) {
    // Estrutura da tabela
    fwrite($file, "-- Table structure for table `$table`\n");
    fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");
    
    $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    fwrite($file, $createTable['Create Table'] . ";\n\n");
    
    // Dados da tabela
    $rows = $pdo->query("SELECT * FROM `$table`");
    $rowCount = $rows->rowCount();
    
    if ($rowCount > 0) {
        fwrite($file, "-- Data for table `$table`\n");
        fwrite($file, "INSERT INTO `$table` VALUES\n");
        
        $i = 0;
        while ($row = $rows->fetch(PDO::FETCH_NUM)) {
            $i++;
            $values = array_map(function($value) use ($pdo) {
                if ($value === null) {
                    return 'NULL';
                }
                return $pdo->quote($value);
            }, $row);
            
            $line = "(" . implode(", ", $values) . ")";
            if ($i < $rowCount) {
                $line .= ",";
            }
            fwrite($file, $line . "\n");
        }
        fwrite($file, ";\n\n");
    }
}

fwrite($file, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($file);

echo "Backup realizado com sucesso em: $backupFile\n"; 