<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'cosplayin:backup {--file= : Nama file backup (opsional)}';

    protected $description = 'Backup database MySQL CosplayIn menggunakan mysqldump';

    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            $this->error('Perintah backup ini hanya dikonfigurasi untuk MySQL.');

            return self::FAILURE;
        }

        $config = config('database.connections.mysql');
        $directory = config('cosplayin.backup.path');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $this->option('file')
            ?: 'cosplayin-'.now()->format('Ymd-His').'.sql';

        $path = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;

        $command = [
            'mysqldump',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            $config['database'],
        ];

        // Password dikirim lewat environment, bukan argumen CLI.
        $process = new Process($command, null, [
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);

        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Backup gagal: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        file_put_contents($path, $process->getOutput());

        $this->info('Backup berhasil dibuat: '.$path);
        $this->line('TODO: interval backup otomatis belum ditentukan. Konfigurasi: DB_BACKUP_INTERVAL_HOURS.');

        return self::SUCCESS;
    }
}
